<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerProducts');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('straker_product_filter');
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

    protected function _prepareCollection()
    {
        $targetStore = $this->_getStore();
        $sourceStore = $this->_getSourceStore();
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('type_id');

        $attrArray = $this->getAttrArray();
        array_push($attrArray, 'status');
        array_push($attrArray, 'visibility');
        array_unique($attrArray);

        if ($sourceStore->getId()) {
            $collection->addStoreFilter($sourceStore);
            foreach ($attrArray as $attr){
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

        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Product_Collection $strakerJobProductCollection */
        $strakerJobProductCollection = Mage::getModel('strakertranslations_easytranslationplatform/job_product')->getCollection();
        $strakerJobProductCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->joinLeft(
                array('b' => $collection->getTable('strakertranslations_easytranslationplatform/job')),
                '`main_table`.`job_id` = `b`.`id`',
                array()
            )->where(
                '`b`.`store_id` = ?', $targetStore->getId()
            )->where(
                '`main_table`.`version` = ?', 1
            )->group(
                'main_table.product_id'
            )->columns(
                array('version' => 'version', 'product_id' => 'product_id')
            );

        //join straker job product table to get version for each product
        $jobProductQuery = $strakerJobProductCollection->getSelect();
        $collection->getSelect()->joinLeft(
            $jobProductQuery,
            'e.entity_id = t.product_id',
            array('version')
        );

        $this->setCollection($collection);
        parent::_prepareCollection();

        $this->getCollection()->addWebsiteNamesToResult();
//        var_dump($collection->getSelect()->__toString());
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
            )
        );
        if (in_array('name', $this->getAttrArray())) {
            $this->addColumn(
                'name',
                array(
                    'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s to Translate', Mage::helper('strakertranslations_easytranslationplatform')->__('Name')),
                    'index' => 'name',
                )
            );
        }
        else{
            $this->addColumn(
                'name',
                array(
                    'header' => Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                )
            );
        }

        // required by IMA-159
        $this->addColumn(
            'sku',
            array(
                'header' => Mage::helper('catalog')->__('Sku'),
                'index' => 'sku'
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
            )
        );

        foreach ($this->getAttrArray() as $attr){
            if ($attr!='name') {
                $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attr);
                $this->addColumn(
                    $attr,
                    array(
                        'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s to Translate', $attrModel->getStoreLabel($this->_getStore())),
                        'index' => $attr,
                    )
                );
            }
        }

        $this->addColumn(
            'type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            )
        );
        $this->addColumn(
            'visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
            )
        );

        $this->addColumn(
            'status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            )
        );

        $this->addColumn(
            'version',
            array(
                'header'=> Mage::helper('catalog')->__('Translated'),
                'width' => '70px',
                'index' => 'version',
                'type'  => 'options',
                'options' => array(
                    'Translated'   => Mage::helper('catalog')->__('Translated'),
                    'Not Translated'   => Mage::helper('catalog')->__('Not Translated')
                ),
                'renderer'  => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Translated',
                'filter_condition_callback' => array($this, '_versionFilter'),
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                )
            );
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites',
                array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                    'filter_condition_callback' => array($this, '_websiteFilter'),
                ));
        }

        return parent::_prepareColumns();
    }

    protected function _versionFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if ($value == 'Translated'){
            $collection->getSelect()->where('t.version is not null');
        } elseif ($value == 'Not Translated'){
            $collection->getSelect()->where('t.version is null');
        }

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem(
            'add', array(
                'label'=> Mage::helper('catalog')->__('Add to Confirm Page'),
                'url'  => $this->getUrl('*/*/addToConfirm'),
                'selected' => 1
            )
        );

        $this->getMassactionBlock()->setTemplate('straker/new/products/massaction.phtml');

        //todo: refine this
        $hiddenParams = '<input type="hidden" name="store" value="' . $this->_getStore()->getId() . '" />';
        $hiddenParams .= '<input type="hidden" name="attr" value="' . $this->getAttr() . '" />';
        $hiddenParams .= '<input type="hidden" name="source_store_id" value="' . $this->_getSourceStore()->getId() .'" />';
        $this->getMassactionBlock()->setHiddenParams($hiddenParams);

        Mage::dispatchEvent('adminhtml_strakertranslation_new_products_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getAttrArray()
    {
        return explode(',', $this->getAttr());
    }

    public function getMassactionBlockJsObjName()
    {
        return $this->getMassactionBlock()->getJsObjectName(); // TODO: Change the autogenerated stub
    }

    public function getSelectedIds()
    {
        $selectedIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_product');
        return empty($selectedIds) ? array() : $selectedIds;
    }

    protected function _websiteFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $store = Mage::app()->getWebsite($value);
        $collection->addStoreFilter($value);

        return $this;
    }
}
