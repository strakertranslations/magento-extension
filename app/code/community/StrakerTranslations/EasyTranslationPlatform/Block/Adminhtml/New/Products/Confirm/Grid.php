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

    protected function _getStore($key = 'store')
    {
        $store = null;

        try {
            $storeId = (Int) $this->getRequest()->getParam($key, Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
            $store = Mage::app()->getStore($storeId);
        }catch(Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $store;
    }

    protected function _getSourceStore(){
        return $this->_getStore('source_store_id');
    }

    protected function _prepareLayout()
    {
        return $this;
    }

    protected function _prepareCollection()
    {
        $targetStore = $this->_getStore();
        $sourceStore = $this->_getSourceStore();
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('type_id');
        $collection->addAttributeToFilter('entity_id', array('in' => $this->getProduct()));

        foreach ($this->getAttrArray() as $attr){
            $collection->addAttributeToSelect($attr);
        }

        if ($sourceStore->getId()) {
            $collection->addStoreFilter($sourceStore);
            foreach ($this->getAttrArray() as $attr){
                $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attr);
                $collection->joinAttribute(
                    $attr,
                    'catalog_product/' . $attr,
                    'entity_id',
                    null,
                    'left',
                    $sourceStore->getId()
                );
            }
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
                $this->getCollection()->joinField(
                    'websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
                'filter' => false,
            )
        );

        if (in_array('name', $this->getAttrArray())) {
            $this->addColumn(
                'name',
                array(
                    'header' => Mage::helper('catalog')->__('Name to Translate'),
                    'index' => 'name',
                    'filter' => false,
                )
            );
        }
        else{
            $this->addColumn(
                'name',
                array(
                    'header' => Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                    'filter' => false,
                )
            );
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

        $this->addColumn(
            'set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
                'filter' => false
            )
        );

        foreach ($this->getAttrArray() as $attr){
            if ($attr!='name') {
                $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(4, $attr);
                $this->addColumn(
                    $attr,
                    array(
                        'header' => Mage::helper('catalog')->__($attrModel->getFrontendLabel()) .' to Translate',
                        'index' => $attr,
                        'filter' => false,
                    )
                );
            }
        }

        $this->addColumn(
            'action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getEntityId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('strakertranslations_easytranslationplatform')->__('Remove'),
                        'url'     => array(
                            'base'=>'*/*/removeFromCart'
                        ),
                        'field'   => 'entity_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'entity_id'
            )
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

    public function getAttrArray()
    {
        return explode(',', $this->getAttr());
    }
}
