<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory as JobCollectionFactory;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $productCollectionFactory;
    protected $jobFactory;
    protected $sourceStoreId;
    protected $_configHelper;



    public function __construct(
        Context $context,
        Data $backendHelper,
        JobFactory $jobFactory,
        CollectionFactory $productCollectionFactory,
        ConfigHelper $configHelper,
        JobCollectionFactory $jobCollectionFactory,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->jobFactory = $jobFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_configHelper = $configHelper;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->sourceStoreId = $this->getRequest()->getParam('source_store_id');
        $this->targetStoreId = $this->getRequest()->getParam('target_store_id');
    }

//    /**
//     * add Column Filter To Collection
//     */
//    protected function _addColumnFilterToCollection($column)
//    {
//        if ($column->getId() == 'in_product') {
//            $productIds = $this->_getSelectedProducts();
//
//            if (empty($productIds)) {
//                $productIds = 0;
//            }
//            if ($column->getFilter()->getValue()) {
//                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
//            } else {
//                if ($productIds) {
//                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
//                }
//            }
//        } else {
//            parent::_addColumnFilterToCollection($column);
//        }
//
//        return $this;
//    }


    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();

        $strakerJobs = $this->resourceConnection->getTableName('straker_job');
        $strakerTrans = $this->resourceConnection->getTableName('straker_attribute_translation');

        $collection->getSelect()->columns(
            'if(stTrans.translated_value IS NULL," ",stTrans.translated_value) as Is Translated'
        )->joinLeft(
            ['stTrans'=>$strakerTrans],
            'e.entity_id=stTrans.entity_id',
            []
        );

        $collection->getSelect()->columns(
            'stJob.*'
        )->joinLeft(
            ['stJob'=>$strakerJobs],
            'stTrans.job_id=stJob.job_id and stJob.target_store_id='.$this->targetStoreId.' and stJob.job_type_id=1',
            []
        )->group('e.entity_id');

        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');

        $collection->setStore($this->sourceStoreId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'in_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->_getSelectedProducts()
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
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
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
                'width' => '50px',
            ]
        );

        $this->addColumn(
            'translated_value',
            [
                'header' => __('Translated'),
                'index' => 'Is Translated',
                'width' => '50px',
                'type' => 'boolean'
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('job_products');

        if ($products) {
            return $products;
        }

        return [];
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
