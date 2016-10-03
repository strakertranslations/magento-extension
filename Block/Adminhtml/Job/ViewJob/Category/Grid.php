<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\ViewJob\Category;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelperData;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Category;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\View\Element\Template;
use Straker\EasyTranslationPlatform\Model;
use Straker\EasyTranslationPlatform\Model\JobFactory;

class Grid extends Extended
{
    protected $_jobFactory;
    /** @var \Straker\EasyTranslationPlatform\Model\Job $_job */
    protected $_job;
    protected $_entityId;
    protected $_jobTypeId = Model\JobType::JOB_TYPE_ATTRIBUTE;
    protected $_jobKey;
    protected $_jobId;
    protected $_sourceStoreId;
    protected $_attributeRepository;

    public function __construct(
        Context $context,
        BackendHelperData $backendHelper,
        JobFactory $jobFactory,
        AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
        $this->_jobFactory = $jobFactory;
        $this->_attributeRepository = $attributeRepository;
        parent::__construct($context,$backendHelper, $data);
    }

    public function _construct()
    {
        $requestData = $this->getRequest()->getParams();
        $this->_jobId = $requestData['job_id'];
        $this->_jobKey = $requestData['job_key'];
        $this->_sourceStoreId = $this->getRequest()->getParam('source_store_id');
        $this->_job = $this->_jobFactory->create()->load( $this->_jobId );
        parent::_construct();
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $nameAttribute = $this->_attributeRepository->get(CategoryAttributeInterface::ENTITY_TYPE_CODE, 'name');
        $categoryCollection = $this->_job->getCategoryCollection();

        if( !empty($this->_sourceStoreId) && is_numeric($this->_sourceStoreId)){
            $this->setCollection($categoryCollection->addCategoryName( $this->_sourceStoreId, $nameAttribute->getAttributeId() ));
        }else{
            $this->setCollection($categoryCollection->addCategoryName());
        }

        return parent::_prepareCollection();
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
//        $this->addColumn(
//            'in_category',
//            [
//                'type' => 'checkbox',
//                'name' => 'in_category',
//                'align' => 'center',
//                'index' => 'entity_id'
//            ]
//        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Category ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );

        $this->addColumn(
            'view',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getEntityId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => '*/*/ViewJob',
                            'params' => [
                                'job_id' => $this->_job->getJobId(),
                                'job_type_id' => $this->_jobTypeId,
                                'job_type_referrer' => Model\JobType::JOB_TYPE_CATEGORY,
                                'job_key' => $this->_jobKey,
                                'source_store_id' => $this->_sourceStoreId
                            ]
                        ],
                        'field' => 'entity_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'view',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        return parent::_prepareColumns();
    }


    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/ViewJob',
            [
                'job_id' => $this->_job->getJobId(),
                'job_type_id' => $this->_jobTypeId,
                'entity_id' => $row->getEntityId(),
                'job_type_referrer' => Model\JobType::JOB_TYPE_CATEGORY,
                'job_key' => $this->_jobKey,
                'source_store_id' => $this->_sourceStoreId
            ]
        );
    }
}
