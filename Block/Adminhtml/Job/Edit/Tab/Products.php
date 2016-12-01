<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Straker\EasyTranslationPlatform\Model\ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product\VisibilityFactory;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\Job\CollectionFactory as JobCollectionFactory;
use Straker\EasyTranslationPlatform\Model\ResourceModel\Products\Collection;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $productCollectionFactory;
    protected $jobFactory;
    protected $sourceStoreId;
    protected $_configHelper;
    protected $jobCollectionFactory;
    protected $resourceConnection;
    protected $targetStoreId;
    protected $productVisibilityModel;


    public function __construct(
        Context $context,
        Data $backendHelper,
        JobFactory $jobFactory,
        ProductCollectionFactory $productCollectionFactory,
        ConfigHelper $configHelper,
        JobCollectionFactory $jobCollectionFactory,
        ResourceConnection $resourceConnection,
        VisibilityFactory $productVisibilityFactory,
        array $data = []
    ) {
        $this->jobFactory = $jobFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_configHelper = $configHelper;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productVisibilityModel = $productVisibilityFactory->create();
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
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->sourceStoreId = $this->getRequest()->getParam('source_store_id');
        $this->targetStoreId = $this->getRequest()->getParam('target_store_id');
    }

    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('price');
//        $collection->addAttributeToSelect('visibility');

        $collection->setStore($this->sourceStoreId);
        $collection->is_translated($this->targetStoreId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
//
//        $this->addColumn(
//            'in_product',
//            [
//                'header_css_class' => 'a-center',
//                'type' => 'multiselect',
//                'name' => 'in_product',
//                'align' => 'center',
//                'index' => 'entity_id',
//                'values' => $this->_getSelectedProducts()
//            ]
//        );

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

//        $this->addColumn(
//            'visibility',
//            [
//                'header' => __('Visibility'),
//                'type' => 'options',
//                'options' => $this->productVisibilityModel->getOptionArray(),
//                'index' => 'visibility',
//                'width' => '50px',
//            ]
//        );

        $this->addColumn(
            'is_translated',
            [
                'header' => __('Translated'),
                'index' => 'is_translated',
                'width' => '50px',
                'type'=>'options',
                'options'=>['1'=>'Yes','0'=>'No'],
//                'filter_index'=>'stTrans.translated_value',
                'renderer' => 'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Grid\Renderer\TranslatedValue',
                'filter_condition_callback' => array($this, '_filterCallback'),
                'order_callback' => [$this, '_orderIsTranslated']
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

    function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Straker_EasyTranslationPlatform::job/massaction_extended.phtml');
        $this->getMassactionBlock()->addItem('create', []);

        return $this;
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

    protected function _filterCallback($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();
        $collection->getSelect()->having('`is_translated` = ?', reset($condition));
        return $this;
    }

    //in order to sort and filter on computed columns,
    //1, rewrite _setCollectionOrder.
    //2. implement callback defined in column data, like 'order_callback' => [$this, '_orderIsTranslated'].
    protected function _orderIsTranslated($collection, $column){
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

    protected function _setCollectionOrder($column)
    {
        if ($column->getOrderCallback()) {
            call_user_func($column->getOrderCallback(), $this->getCollection(), $column);
            return $this;
        }
        return parent::_setCollectionOrder($column);
    }

    public function getHiddenInputElementName()
    {
        $serializerBlock = $this->getLayout()->getBlock('products_grid_serializer');
        return empty($serializerBlock) ? 'products' : $serializerBlock->getInputElementName();
    }
}
