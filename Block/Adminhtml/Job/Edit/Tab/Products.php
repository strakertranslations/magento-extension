<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;

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
        array $data = []
    ) {
        $this->jobFactory = $jobFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_configHelper = $configHelper;
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
        if ($this->getRequest()->getParam('target_store_id')) {

            $store_info = $this->_configHelper->getStoreInfo($this->getRequest()->getParam('target_store_id'));

            $this->sourceStoreId = $store_info['straker/general/source_store'];
        }
    }

    /**
     * add Column Filter To Collection
     */
//    protected function _addColumnFilterToCollection($column)
//    {
//        if ($column->getId() == 'in_product') {
//            //$productIds = $this->_getSelectedProducts();
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
                'index' => 'entity_id'
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

        return parent::_prepareColumns();
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
