<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Confirm_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerProducts');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(false);
        $this->setVarNameFilter('product_filter');
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
                    'header' => Mage::helper('catalog')->__('Name to translate'),
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

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/addToConfirm', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getAttrArray(){
        return explode(',', $this->getAttr());
    }
}
