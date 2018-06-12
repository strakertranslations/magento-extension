<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('straker_attribute_filter');
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

    /**
     * Prepare product attributes grid collection object
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        /** @var \Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()->addStoreLabel($this->_getSourceStore()->getId());
        $store = $this->_getStore();

        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Attribute_Collection $strakerJobProductCollection */
        $strakerJobAttributeCollection = Mage::getModel('strakertranslations_easytranslationplatform/job_attribute')->getCollection();
        $strakerJobAttributeCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->joinLeft(
                array('b' => $strakerJobAttributeCollection->getTable('strakertranslations_easytranslationplatform/job')),
                '`main_table`.`job_id` = `b`.`id`',
                array()
            )->where(
                '`b`.`store_id` = ?', $store->getId()
            )->where(
                '`main_table`.`version` = ?', 1
            )->group(
                'main_table.attribute_id'
            )->columns(
                array('version' => 'version', 'attribute_id' => 'attribute_id')
            );

        $jobAttributeQuery = $strakerJobAttributeCollection->getSelect();

        $collection->getSelect()->joinLeft(
            $jobAttributeQuery,
            'main_table.attribute_id = t.attribute_id',
            array('version')
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * Prepare product attributes grid columns
     *
     * @return StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Attribute_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'attribute_code', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code',
            'width' => '22%'
            )
        );

//        $this->addColumn(
//            'frontend_label', array(
//            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Label'),
//            'sortable' => true,
//            'index' => 'frontend_label',
//            'width' => '22%'
//            )
//        );

        $this->addColumn(
            'store_label', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Store Label'),
                'sortable' => true,
                'index' => 'store_label',
                'filter_condition_callback' => array($this, '_storeLabelFilter'),
                'width' => '22%'
            )
        );

        $this->addColumn(
            'translate_options', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Translate Attribute Options'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_TranslateOptions',
            'align' => 'center',
            'index' => 'frontend_input',
            'sortable'=> false,
            'type'    => 'options',
            'options' => array(
                'no' => Mage::helper('catalog')->__('No Options'),
                'select' => Mage::helper('catalog')->__('Has Options')
            ),
            'filter_condition_callback' => array($this, '_optionsFilter'),
            'width' => '22%'
            )
        );

        $this->addColumn(
            'is_visible', array(
            'header'=>Mage::helper('catalog')->__('Visible'),
            'sortable'=>true,
            'index'=>'is_visible_on_front',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
            'width' => '22%'
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
              'Translated'   => Mage::helper('strakertranslations_easytranslationplatform')->__('Translated'),
              'Not Translated'   => Mage::helper('strakertranslations_easytranslationplatform')->__('Not Translated')
            ),
            'renderer'  => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Translated',
            'filter_condition_callback' => array($this, '_versionFilter'),
            )
        );

        return $this;
    }

    protected function _versionFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if ($value == 'Translated'){
            $collection->getSelect()->where('t.version is not null');
           // print $this->getCollection()->getSelect(); exit;
        } elseif ($value == 'Not Translated'){
            $collection->getSelect()->where('t.version is null');
        }

        return $this;
    }

    protected function _storeLabelFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if (trim($value) !== ''){
            $collection->getSelect()->where('IFNULL(`al`.`value`,`main_table`.`frontend_label`) like \'%' . $value . '%\'');
        }

        return $this;
    }

    protected function _optionsFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if (in_array($value, array('select','multiselect'))){
            $collection->getSelect()->where('frontend_input in (?)', array('select','multiselect'));
        } else {
            $collection->getSelect()->where('frontend_input not in (?)', array('select','multiselect'));
        }

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('attribute_id');
        $this->setMassactionIdFilter('main_table.attribute_id');
        $this->getMassactionBlock()->setFormFieldName('attribute');

        $this->getMassactionBlock()->addItem(
            'add', array(
             'label'=> Mage::helper('catalog')->__('Add to Confirm Page'),
             'url'  => $this->getUrl('*/*/addToConfirm'),
             'selected' => 1
            )
        );
        $this->getMassactionBlock()->setTemplate('straker/new/attribute/massaction.phtml');
        $data['attribute'] =  Mage::getSingleton('adminhtml/session')->getData('straker_new_attribute');
        $optionParam = $this->getRequest()->getParam('internal_option');
        if(!empty($optionParam)){
            Mage::getSingleton('adminhtml/session')->setData('straker_new_option', $optionParam);
        }

        $internalOption = Mage::getSingleton('adminhtml/session')->getData('straker_new_option');
        $internalOption = empty($internalOption) ? '' : $internalOption;
        //todo: refine this
//        $hiddenParams = '<input type="hidden" name="store" value="'.$this->getRequest()->getParam('store').'" /><input type="hidden" name="option" value="'.$this->getRequest()->getParam('internal_option').'" />';
        $hiddenParams = '<input type="hidden" name="store" value="'.$this->_getStore()->getId() .'" />';
        $hiddenParams .= '<input type="hidden" name="option" value="'. $internalOption .'" />';
        $hiddenParams .= '<input type="hidden" name="source_store_id" value="'. $this->_getSourceStore()->getId() .'" />';

        $this->getMassactionBlock()->setHiddenParams($hiddenParams);

        Mage::dispatchEvent('adminhtml_strakertranslation_new_products_grid_prepare_massaction', array('block' => $this));
        return $this;

    }

    public function getAttributesWithOption()
    {
        $AttributeIdsWithOption = clone $this->getCollection()->getSelect();
        $AttributeIdsWithOption->reset(Zend_Db_Select::ORDER);
        $AttributeIdsWithOption->reset(Zend_Db_Select::LIMIT_COUNT);
        $AttributeIdsWithOption->reset(Zend_Db_Select::LIMIT_OFFSET);
        $AttributeIdsWithOption->reset(Zend_Db_Select::COLUMNS);
        $AttributeIdsWithOption->reset(Zend_Db_Select::COLUMNS);
        $AttributeIdsWithOption->where('frontend_input in (?)', array('select','multiselect'));
        $AttributeIdsWithOption->columns('attribute_id', 'main_table');
        return  Mage::getSingleton('core/resource')->getConnection('core_read')->fetchCol($AttributeIdsWithOption);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getMassactionBlockJsObjName()
    {
        return $this->getMassactionBlock()->getJsObjectName(); // TODO: Change the autogenerated stub
    }

    public function getSelectedIds()
    {
        $selectedIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_attribute');
        return empty($selectedIds) ? array() : $selectedIds;
    }
}
