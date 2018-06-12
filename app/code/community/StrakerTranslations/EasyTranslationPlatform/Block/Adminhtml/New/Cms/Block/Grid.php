<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Cms_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerCmsBlocks');
        $this->setDefaultSort('block_identifier');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('straker_cmd_block_filter');
    }

    protected function _prepareCollection()
    {
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */
        $collection = Mage::getModel('cms/block')->getCollection();
        $sourceStore = $this->_getSourceStore();

        if ( $sourceStore ) {
            $this->setDefaultFilter(array('store_id' => $sourceStore->getId()));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title', array(
            'header'    => Mage::helper('cms')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
            )
        );

        $this->addColumn(
            'identifier', array(
            'header'    => Mage::helper('cms')->__('Identifier'),
            'align'     => 'left',
            'index'     => 'identifier'
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                => array($this, '_filterStoreCondition'),
                )
            );
        }

        $this->addColumn(
            'is_active', array(
            'header'    => Mage::helper('cms')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            ),
            )
        );

        $this->addColumn(
            'creation_time', array(
            'header'    => Mage::helper('cms')->__('Date Created'),
            'index'     => 'creation_time',
            'type'      => 'datetime',
            )
        );

        $this->addColumn(
            'update_time', array(
            'header'    => Mage::helper('cms')->__('Last Modified'),
            'index'     => 'update_time',
            'type'      => 'datetime',
            )
        );

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('cms_block');

        $this->getMassactionBlock()->addItem(
            'add', array(
            'label'=> Mage::helper('catalog')->__('Add to Confirm Page'),
            'url'  => $this->getUrl('*/*/addToConfirm'),
            'selected' => 1
            )
        );
        $this->getMassactionBlock()->setTemplate('straker/new/cms/block/massaction.phtml');

        //todo: refine this
        $hiddenParams = '<input type="hidden" name="store" value="'.$this->_getStore()->getId().'" />';
        $hiddenParams .= '<input type="hidden" name="attr" value="'.$this->getAttr().'" />';
        $this->getMassactionBlock()->setHiddenParams($hiddenParams);


        Mage::dispatchEvent('adminhtml_strakertranslation_new_products_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getRowUrl($row)
    {
        return '';
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

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getMassactionBlockJsObjName()
    {
        return $this->getMassactionBlock()->getJsObjectName(); // TODO: Change the autogenerated stub
    }

    public function getSelectedIds()
    {
        $selectedIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_cms_block');
        return empty($selectedIds) ? array() : $selectedIds;
    }
}
