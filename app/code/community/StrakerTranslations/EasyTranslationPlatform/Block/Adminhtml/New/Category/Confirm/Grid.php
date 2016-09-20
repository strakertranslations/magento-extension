<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Category_Confirm_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerCategories');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('category_filter');
        $this->setTemplate('straker/new/category/confirm/grid.phtml');
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
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('path')
            ->addAttributeToSelect('name');
        $collection->addAttributeToFilter('entity_id', array('in' => $this->getCategory()));

        foreach ($this->getAttrArray() as $attr){
            $collection->addAttributeToSelect($attr);
        }

        //join straker job product table to get version for each product
        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $collection->getSelect()->joinLeft(
            $prefix.'straker_job_category',
            $prefix.'straker_job_category.category_id = e.entity_id',
            'version'
        );

        $this->setCollection($collection);

        parent::_prepareCollection();
//        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

//    protected function _addColumnFilterToCollection($column)
//    {
//
//        if ($column->getId() == 'version') {
//            $this->getCollection()->joinField('version',
//                'straker_job_category',
//                'version',
//                'category_id=entity_id',
//                null,
//                'left');
//        }
//
//    }

    protected function _prepareColumns()
    {

        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
                'filter' => false,
                'sortable'  => false
            ));

        if (in_array('name',$this->getAttrArray())) {
            $this->addColumn('name',
                array(
                    'header' => Mage::helper('catalog')->__('Name to Translate'),
                    'index' => 'name',
                    'filter' => false,
                    'sortable'  => false
                ));
        }
        else{
            $this->addColumn('name',
                array(
                    'header' => Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                    'filter' => false,
                    'sortable'  => false
                ));
        }

        $this->addColumn('path',
            array(
                'header' => Mage::helper('catalog')->__('Path'),
                'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Path',
                'index' => 'path',
                'filter' => false,
                'sortable'  => false
            ));

        foreach ($this->getAttrArray() as $attr){
            if ($attr != 'name' && $attr != '') {
                $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(3, $attr);
                $this->addColumn($attr,
                    array(
                        'header' => Mage::helper('catalog')->__($attrModel->getFrontendLabel()) .' to Translate',
                        'index' => $attr,
                        'filter' => false,
                        'sortable'  => false
                    ));
            }
        }

        $this->addColumn('version',
            array(
                'header'=> Mage::helper('catalog')->__('Translated'),
                'width' => '70px',
                'index' => 'version',
                'type'  => 'options',
                'filter' => false,
                'sortable'  => false,
                'options' => array(
                    1    => Mage::helper('catalog')->__('Translated'),
                    ''   => Mage::helper('catalog')->__('Not Translated')
                )
            ));


        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/confirmCategory', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getAttrArray(){
        return explode(',', $this->getAttr());
    }
}
