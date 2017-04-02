<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Cms_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerCmsPages');
        $this->setDefaultSort('identifier');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('straker_cmd_page_filter');
    }

    protected function _prepareCollection()
    {
        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
        $collection = Mage::getModel('cms/page')->getCollection();
        //todo: should add $collection->addStoreFilter(sourceStoreId);
        $collection->setFirstStoreFlag(true);
        //var_dump($collection->getSelect()->__toString());exit;
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'    => Mage::helper('cms')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));

        $this->addColumn('identifier', array(
            'header'    => Mage::helper('cms')->__('URL Key'),
            'align'     => 'left',
            'index'     => 'identifier'
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('cms')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => Mage::getSingleton('cms/page')->getAvailableStatuses()
        ));

        $this->addColumn('creation_time', array(
            'header'    => Mage::helper('cms')->__('Date Created'),
            'index'     => 'creation_time',
            'type'      => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header'    => Mage::helper('cms')->__('Last Modified'),
            'index'     => 'update_time',
            'type'      => 'datetime',
        ));

        $this->addColumn('page_actions', array(
            'header'    => Mage::helper('cms')->__('Action'),
            'width'     => 10,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'adminhtml/cms_page_grid_renderer_action',
//            'renderer'  => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_PageGridAction'

        ));

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
        $this->getMassactionBlock()->setFormFieldName('cms_page');

        $this->getMassactionBlock()->addItem('add', array(
            'label'=> Mage::helper('catalog')->__('Add to Confirm Page'),
            'url'  => $this->getUrl('*/*/addtoconfirm'),
            'selected' => 1
        ));
        $this->getMassactionBlock()->setTemplate('straker/new/cms/page/massaction.phtml');

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

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getMassactionBlockJsObjName()
    {
        return $this->getMassactionBlock()->getJsObjectName(); // TODO: Change the autogenerated stub
    }

    public function getSelectedIds(){
        $selectedIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_cms_page');
        return empty($selectedIds) ? [] : $selectedIds;
    }
}