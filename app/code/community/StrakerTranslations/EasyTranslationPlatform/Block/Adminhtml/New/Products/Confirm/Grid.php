<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Products_Confirm_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerProductsConfirm');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_confirm_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    protected function _prepareLayout()
    {
        return $this;
    }
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id');
        $collection->addAttributeToFilter('entity_id', array('in' => $this->getProduct()));

        foreach ($this->getAttrArray() as $attr){
            $collection->addAttributeToSelect($attr);
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
        }
        else {
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {

        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
                'filter' => false,
            ));

        if (in_array('name',$this->getAttrArray())) {
            $this->addColumn('name',
                array(
                    'header' => Mage::helper('catalog')->__('Name to Translate'),
                    'index' => 'name',
                    'filter' => false,
                ));
        }
        else{
            $this->addColumn('name',
                array(
                    'header' => Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                    'filter' => false,
                ));
        }

        // required by IMA-159
        $this->addColumn(
            'sku',
            array(
                'header' => Mage::helper('catalog')->__('Sku'),
                'index' => 'sku',
                'filter' => false
            )
        );

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
                'filter' => false
            ));

        foreach ($this->getAttrArray() as $attr){
            if ($attr!='name') {
                $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(4, $attr);
                $this->addColumn($attr,
                    array(
                        'header' => Mage::helper('catalog')->__($attrModel->getFrontendLabel()) .' to Translate',
                        'index' => $attr,
                        'filter' => false,
                    ));
            }
        }

        $this->addColumn(
            'action',
            [
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getEntityId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Remove'),
                        'url'     => [
                            'base'=>'*/*/removeFromCart'
                        ],
                        'field'   => 'entity_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'entity_id'
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/confirmGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getAttrArray(){
        return explode(',', $this->getAttr());
    }
}
