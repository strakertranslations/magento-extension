<?php

namespace Straker\EasyTranslationPlatform\Model;

use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Straker\EasyTranslationPlatform\Helper\ImportHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory                               as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection\Factory                             as CategoryCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory                                      as PageCollectionFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory                                     as BlockCollectionFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory  as AttributeTranslationCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;

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
    protected $_categoryCollectionFactory;
    protected $_pageCollectionFactory;
    protected $_blockCollectionFactory;
    protected $_attributeTranslationCollectionFactory;
    protected $_productRepository;
    protected $_categoryRepository;
    protected $_pageRepository;
    protected $_blockRepository;

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
        CategoryCollectionFactory $categoryCollectionFactory,
        PageCollectionFactory $pageCollectionFactory,
        BlockCollectionFactory $blockCollectionFactory,
        AttributeTranslationCollectionFactory $attributeTranslationCollectionFactory,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        PageRepositoryInterface $pageRepository,
        BlockRepositoryInterface $blockRepository,
        JobStatusFactory $jobStatusFactory,
        JobTypeFactory $jobTypeFactory,
        ImportHelper $importHelper,
        StrakerAPI $strakerAPI,
        Logger $logger
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_pageCollectionFactory = $pageCollectionFactory;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_attributeTranslationCollectionFactory = $attributeTranslationCollectionFactory;
        $this->_jobStatusFactory = $jobStatusFactory;
        $this->_jobTypeFactory = $jobTypeFactory;
        $this->_importHelper = $importHelper;
        $this->_strakerApi = $strakerAPI;
        $this->_logger = $logger;
        $this->_productRepository = $productRepository;
        $this->_categoryRepository = $categoryRepository;
        $this->_pageRepository = $pageRepository;
        $this->_blockRepository = $blockRepository;
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

    /**
     * @param int $type, either JobType::JOB_TYPE_PRODUCT (default) or (JobType::JOB_TYPE_CATEGORY)
     * @return array
     */
    public function getEntities( $type = JobType::JOB_TYPE_PRODUCT ){
        $this->_loadEntities( $type );
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


    /**
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    public function getPageCollection(){
        $this->getAttributeTranslationEntityArray();
        $collection = $this->_pageCollectionFactory->create()
            ->addFieldToFilter('page_id', ['in' => $this->_entityIds]);
        return $collection;
    }

    /**
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    public function getBlockCollection(){
        $this->getAttributeTranslationEntityArray();
        $collection = $this->_blockCollectionFactory->create()
            ->addFieldToFilter('main_table.block_id', ['in' => $this->_entityIds]);
        return $collection;
    }

    /**
     * @return \Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\Collection $collection
     */
    public function getCategoryCollection(){
        $collection = $this->_getAttributeTranslationEntityCollection();
//        $collection = $this->_categoryCollectionFactory->create()
//            ->addFieldToFilter('entity_id', ['in'=> $this->_entityIds]);
        return $collection;
    }

    public function getAttributeTranslationEntityArray(){
        /** @var \Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\Collection $collection  */
        $collection = $this->_getAttributeTranslationEntityCollection();
        foreach ($collection->getData() as $item){
            array_push($this->_entityIds, $item['entity_id']);
        }
        return $this->_entityIds;
    }

    private function _getAttributeTranslationEntityCollection(){
        /** @var \Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\Collection $collection  */
        $collection = $this->_attributeTranslationCollectionFactory->create()
            ->distinct(true)
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter( 'job_id', [ 'eq' => $this->getId()] );
        return $collection;
    }

    protected function _loadEntities( $type = JobType::JOB_TYPE_PRODUCT )
    {
        $this->_entities = [];
        $this->_entityCount = 0;

        if( $type == JobType::JOB_TYPE_CATEGORY ){
            foreach ($this->getCategoryCollection() as $category) {
                $this->_entities[$category->getEntityId()] = $category;
                $this->_entityCount++;
            }

        }else{
            foreach ($this->getProductCollection() as $product) {
                $this->_entities[$product->getEntityId()] = $product;
                $this->_entityCount++;
            }
        }
    }

    public function updateStatus( $jobData ){
        $return = [ 'isSuccess' => true, 'Message' => ''];
        switch (strtolower( $jobData->status) ){
            case 'queued':
                if( empty( $this->getData('job_number')) && !empty($jobData->tj_number) ){
                    $this->setData('job_number', $jobData->tj_number )->save();
                }

                if( empty($this->getData('job_number')) ){
                    return false;
                }

                if( !empty($jobData->quotation) && strcasecmp( $jobData->quotation, 'ready') === 0){
                    $this->setData('job_status_id', JobStatus::JOB_STATUS_READY )
                        ->save();
                }else{
                    $this->setData('job_status_id', JobStatus::JOB_STATUS_QUEUED )
                        ->save();
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
                        $fileNameArray = $this->_renameTranslatedFileName( $filePath, $jobData->source_file );
                        $fileFullName = implode(DIRECTORY_SEPARATOR, $fileNameArray);
                        $result = true;

                        if( !file_exists( $fileFullName )){
                            $result = file_put_contents( $fileFullName, $fileContent );
                        }

                        if($result == false ){
                            $return['isSuccess'] = false;
                            $return['Message'] = __('Failed to write content to ' . $fileFullName);
                            $this->_logger->addError( $return['Message'] );
                        }else{
                            //TODO save new filename to database
                            $this->setData('download_url', $downloadUrl)
                                ->setData('translated_file', $fileNameArray['name'])->save();
                            $this->_importHelper->create( $this->getId() )
                                ->parseTranslatedFile()
                                ->saveData();
                            if(empty( $this->getData('job_number') ) && $this->_importHelper->configHelper->isSandboxMode() ){
                                $jobKey = $this->getJobKey();
                                $testJobNumber = $this->getId();
                                if(!empty( $jobKey )){
                                    $testJobNumber = $this->getTestJobNumberByJobKey($this->getJobKey());
                                }
                                $this->setData('job_number', 'Test Job '. $testJobNumber);
                            }
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
//        $suffix = date('Y-m-d H:i',time());
        $suffix = '';
        return [ 'path' => $filePath, 'name' => $fileName.'_'. $suffix  .'.xml'];
    }

    public function getEntityName( $entityId = '1' ){
        $title = '';
        switch ($this->getJobTypeId()){
            case JobType::JOB_TYPE_PRODUCT:
                $title = $this->_productRepository->getById( $entityId, false, $this->getSourceStoreId() )->getName();
                break;
            case JobType::JOB_TYPE_CATEGORY:
                $title = $this->_categoryRepository->get( $entityId, $this->getSourceStoreId())->getName();
                break;
            case JobType::JOB_TYPE_PAGE:
                $title = $this->_pageRepository->getById( $entityId )->getTitle();
                break;
            case Jobtype::JOB_TYPE_BLOCK:
                $title = $this->_blockRepository->getById( $entityId )->getTitle();
                break;
        }
        return $title;
    }

    public function getTestJobNumberByJobKey( $jobKey ){
        $data = $this->getResourceCollection()
            ->distinct(true)
            ->addFieldToSelect('job_number')
            ->addFieldToFilter('job_number', ['neq'=> null ])
            ->addFieldToFilter('is_test_job', ['eq'=> 1 ])
            ->addFieldToFilter('job_key', ['neq'=> $jobKey ]);

        return count($data) + 1;
    }
}
