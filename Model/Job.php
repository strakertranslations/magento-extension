<?php

namespace Straker\EasyTranslationPlatform\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Straker\EasyTranslationPlatform\Helper\ImportHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Model\JobStatusFactory;
use Straker\EasyTranslationPlatform\Model\JobTypeFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollectionFactory;

class Job extends AbstractModel implements JobInterface, IdentityInterface
{
    const ENTITY = 'straker_job';

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'st_products_grid';

    /**
     * @var string
     */
    protected $_cacheTag = 'st_products_grid';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'st_products_grid';

    protected $_productCollectionFactory;
    protected $_attributeTranslationCollectionFactory;
    protected $_entities = [];
    public    $_entityIds = [];
    protected $_entityCount;
    protected $_jobStatusFactory;
    protected $_jobTypeFactory;
    protected $_importHelper;
    protected $_strakerApi;
    protected $_logger;

    public function __construct(
        Context $context,
        Registry $registry,
        ProductCollectionFactory $productCollectionFactory,
        AttributeTranslationCollectionFactory $attributeTranslationCollectionFactory,
        JobStatusFactory $jobStatusFactory,
        JobTypeFactory $jobTypeFactory,
        ImportHelper $importHelper,
        StrakerAPI $strakerAPI,
        Logger $logger,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_attributeTranslationCollectionFactory = $attributeTranslationCollectionFactory;
        $this->_jobStatusFactory = $jobStatusFactory;
        $this->_jobTypeFactory = $jobTypeFactory;
        $this->_importHelper = $importHelper;
        $this->_strakerApi = $strakerAPI;
        $this->_logger = $logger;
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */

    protected function _construct(
    )
    {
        $this->_init('Straker\EasyTranslationPlatform\Model\ResourceModel\Job');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getEntities(){
        $this->_loadEntities();
        return $this->_entities;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection(){
        $this->getAttributeTranslationEntityArray();
        $collection = $this->_productCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in'=> $this->_entityIds]);

//        var_dump($collection->getData());exit();
        return $collection;
    }

    public function getAttributeTranslationEntityArray(){
        /** @var \Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\Collection $collection  */
        $collection = $this->_attributeTranslationCollectionFactory->create()
            ->distinct(true)
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter( 'job_id', [ 'eq' => $this->getId()] );
        foreach ($collection->getData() as $item){
            array_push($this->_entityIds, $item['entity_id']);
        }

        return $this->_entityIds;
    }

    protected function _loadEntities()
    {
        $this->_entities = [];
        $this->_entityCount = 0;
        foreach ($this->getProductCollection() as $product) {
            $this->_entities[$product->getEntityId()] = $product;
            $this->_entityCount++;
        }
    }

    public function updateStatus( $jobData ){
        $return = [ 'isSuccess' => true, 'Message' => ''];
        switch (strtolower( $jobData->status) ){
            case 'queued':
                if( !empty($jobData->quotation) && strcasecmp( $jobData->quotation, 'ready') === 0){
                    $this->setData('job_status_id', JobStatus::JOB_STATUS_READY )
                        ->save();
                }else{
                    $this->setData('job_status_id', JobStatus::JOB_STATUS_QUEUED )
                        ->save();
                }
                if( empty( $this->getData('job_number'))){
                    $this->setData('job_number', $jobData->tj_number )->save();
                }
                break;
            case 'in_progress':
                $this->setData('job_status_id', JobStatus::JOB_STATUS_INPROGRESS )->save();
                break;
            case 'completed':
                if( !empty($jobData->translated_file) && count( $jobData->translated_file)){
                    $downloadUrl = reset($jobData->translated_file)->download_url;
                    if( !empty( $downloadUrl )){
                        $fileContent = $this->_strakerApi->getTranslatedFile( $downloadUrl );
                        $filePath = $this->_importHelper->configHelper->getTranslatedXMLFilePath();
                        if( !file_exists( $filePath )){
                            mkdir( $filePath);
                        }
                        $fileName = $this->_renameTranslatedFileName( $filePath, $jobData->source_file );
                        $result = file_put_contents( implode(DIRECTORY_SEPARATOR, $fileName), $fileContent );
                        if($result == false ){
                            $return['isSuccess'] = false;
                            $return['Message'] = __('Failed to write content to ' . $fileName);
                            $this->_logger->addError( $return['Message'] );
                        }else{
                            //TODO save new filename to database
                            $this->setData('download_url', $downloadUrl)
                                ->setData('translated_file', $fileName['name'])->save();
                            $this->_importHelper->create( $this->getId() )
                                ->parseTranslatedFile()
                                ->saveTranslatedProductData();
                            $this->setData('job_status_id', JobStatus::JOB_STATUS_COMPLETED )->save();
                        }
                    }else{
                        $return['isSuccess'] = false;
                        $return['Message'] = __('Download url is not found for the job ( \'job_key\': \'' .$jobData->job_key . '\').' );
                        $this->_logger->addError( $return['Message'] );
                    }
                }else{
                    $return['isSuccess'] = false;
                    $return['Message'] = __('Download file is not found for the job ( \'job_key\': \'' .$jobData->job_key . '\').');
                    $this->_logger->addError( $return['Message'] );
                }
                break;
            default:
                $return['isSuccess'] = false;
                $return['Message'] = __('Unknown status is found for the job ( \'job_key\': \'' .$jobData->job_key . '\').');
                $this->_logger->addError( $return['Message'] );
                break;
        }
        return $return;
    }

    public function getJobStatus(){
        return $this->_jobStatusFactory->create()->load($this->getJobStatusId())->getStatusName();
    }

    public function getJobType(){
        return $this->_jobTypeFactory->create()->load($this->getJobTypeId())->getTypeName();
    }

    private function _renameTranslatedFileName( $filePath, $originalFileName ){
        $fileName = substr_replace( $originalFileName, '_translated', stripos( $originalFileName, '.xml'));
        $suffix = date('Y-m-d H:i:s',time());
        return [ 'path' => $filePath, 'name' => $fileName.'_'. $suffix  .'.xml'];
    }
}
