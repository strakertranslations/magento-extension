<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Prepare product attributes grid collection object
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();

        $store = $this->_getStore();
        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $jobAttributeQuery = 'select a.`version`,  a.`attribute_id` from `'.$prefix.'straker_job_attribute` as a
                            left join `'.$prefix.'straker_job` as b on a.`job_id`=b.`id`
                            where b.`store_id` ='.$store->getId().' and a.`version` =1
                            GROUP BY a.`attribute_id`';

        //join straker job product table to get version for each product
        $collection->getSelect()->joinLeft(

          new Zend_Db_Expr('('.$jobAttributeQuery.')'),
          'main_table.attribute_id = t.attribute_id',
          array('version')

        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    /**
     * Prepare product attributes grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();


        $this->addColumn('attribute_code', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code',
            'filter'    => false
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Label'),
            'sortable'=>true,
            'index'=>'frontend_label'
        ));

        $this->addColumn('translate_options', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Translate Attribute Options'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_TranslateOptions',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        $this->addColumn('is_visible', array(
            'header'=>Mage::helper('catalog')->__('Visible'),
            'sortable'=>true,
            'index'=>'is_visible_on_front',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        $this->addColumn('version',
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
          ));

        return $this;
    }

    protected function _versionFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if ($value == 'Translated' ){
            $collection->getSelect()->where('t.version is not null');
           // print $this->getCollection()->getSelect(); exit;

        } elseif ($value == 'Not Translated'){
            $collection->getSelect()->where('t.version is null');
        }

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('attribute_id');
        $this->setMassactionIdFilter('main_table.attribute_id');
        $this->getMassactionBlock()->setFormFieldName('attribute');

        $this->getMassactionBlock()->addItem('add', array(
             'label'=> Mage::helper('catalog')->__('Add to Confirm Page'),
             'url'  => $this->getUrl('*/*/addtoconfirm'),
             'selected' => 1
        ));
        $this->getMassactionBlock()->setTemplate('straker/new/attribute/massaction.phtml');

        //todo: refine this
        $hiddenParams = '<input type="hidden" name="store" value="'.$this->getRequest()->getParam('store').'" /><input type="hidden" name="option" value="'.$this->getRequest()->getParam('internal_option').'" />';
        $this->getMassactionBlock()->setHiddenParams($hiddenParams);

        Mage::dispatchEvent('adminhtml_strakertranslation_new_products_grid_prepare_massaction', array('block' => $this));
        return $this;

    }

    public function getAttributesWithOption(){
        $AttributeIdsWithOption = clone $this->getCollection()->getSelect();
        $AttributeIdsWithOption->reset(Zend_Db_Select::ORDER);
        $AttributeIdsWithOption->reset(Zend_Db_Select::LIMIT_COUNT);
        $AttributeIdsWithOption->reset(Zend_Db_Select::LIMIT_OFFSET);
        $AttributeIdsWithOption->reset(Zend_Db_Select::COLUMNS);
        $AttributeIdsWithOption->reset(Zend_Db_Select::COLUMNS);
        $AttributeIdsWithOption->where('frontend_input=?','select');
        $AttributeIdsWithOption->columns('attribute_id', 'main_table');
        return  Mage::getSingleton('core/resource')->getConnection('core_read')->fetchCol($AttributeIdsWithOption);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/new', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }
}
