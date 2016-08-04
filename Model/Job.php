<?php

namespace Straker\EasyTranslationPlatform\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Straker\EasyTranslationPlatform\Model\JobStatusFactory;
use Straker\EasyTranslationPlatform\Model\JobTypeFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\AttributeTranslation\CollectionFactory as AttributeTranslationCollectionFactory;

class Job extends \Magento\Framework\Model\AbstractModel implements JobInterface, IdentityInterface
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

    protected $configHelper ;
    protected $_productCollectionFactory;
    protected $_attributeTranslationCollectionFactory;
    protected $_entities = [];
    protected $_entityIds = [];
    protected $_entityCount;
    protected $_jobStatusFactory;
    protected $_jobTypeFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ProductCollectionFactory $productCollectionFactory,
        AttributeTranslationCollectionFactory $attributeTranslationCollectionFactory,
        JobStatusFactory $jobStatusFactory,
        JobTypeFactory $jobTypeFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_attributeTranslationCollectionFactory = $attributeTranslationCollectionFactory;
        $this->_jobStatusFactory = $jobStatusFactory;
        $this->_jobTypeFactory = $jobTypeFactory;
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

    function updateStatus( $jobData ){
        switch (strtolower( $jobData->status)){
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
                $this->setData('job_status_id', JobStatus::JOB_STATUS_COMPLETED )->save();
                //TODO: downloading file and ready to publish
                break;
        }
    }

    public function getJobStatus(){
        return $this->_jobStatusFactory->create()->load($this->getJobStatusId())->getStatusName();
    }

    public function getJobType(){
        return $this->_jobTypeFactory->create()->load($this->getJobTypeId())->getTypeName();
    }
}
