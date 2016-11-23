<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerProducts');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('product_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id');

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
//        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
//        $jobProductQuery = 'select a.`version`, a.`product_id` from `'.$prefix.'straker_job_product` as a
//                            left join `'.$prefix.'straker_job` as b on a.`job_id`=b.`id`
//                            where b.`store_id` = '.$store->getId().' and a.`version` =1
//                            GROUP BY a.`product_id`';

        //join straker job product table to get version for each product
//        $collection->getSelect()->joinLeft(
//
//          new Zend_Db_Expr('('.$jobProductQuery.')'),
//          'e.entity_id = t.product_id',
//          array('version')
//
//        );
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Product_Collection $strakerJobProductCollection */
        $strakerJobProductCollection = Mage::getModel('strakertranslations_easytranslationplatform/job_product')->getCollection();
        $strakerJobProductCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->joinLeft(
                ['b' => $collection->getTable('strakertranslations_easytranslationplatform/job')],
                '`main_table`.`job_id` = `b`.`id`',
                []
            )->where(
                '`b`.`store_id` = ?', $store->getId()
            )->where(
                '`main_table`.`version` = ?', 1
            )->group(
                'main_table.product_id'
            )->columns(
                ['version' => 'version', 'product_id' => 'product_id']
            );
        $jobProductQuery = $strakerJobProductCollection->getSelect();
        $collection->getSelect()->joinLeft(
            $jobProductQuery,
            'e.entity_id = t.product_id',
            array('version')
        );

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
//
//            if ($column->getId() == 'version') {
//                $this->getCollection()->joinField('version',
//                    'straker_job_product',
//                    'version',
//                    'product_id=entity_id',
//                    null,
//                    'left');
//            }

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
        ));
        if (in_array('name',$this->getAttrArray())) {
            $this->addColumn('name',
                array(
                    'header' => Mage::helper('catalog')->__('Name to Translate'),
                    'index' => 'name',
                ));
        }
        else{
            $this->addColumn('name',
                array(
                    'header' => Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                ));
        }
        foreach ($this->getAttrArray() as $attr){
            if ($attr!='name') {
                $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(4, $attr);
                $this->addColumn($attr,
                    array(
                        'header' => Mage::helper('catalog')->__($attrModel->getFrontendLabel()).' To Translate',
                        'index' => $attr,
                    ));
            }
        }

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));
        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
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

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites',
                array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _versionFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        if ($value == 'Translated' ){
            $this->getCollection()->getSelect()->where('t.version is not null');

        } elseif ($value == 'Not Translated'){
            $this->getCollection()->getSelect()->where('t.version is null');
        }

        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('add', array(
             'label'=> Mage::helper('catalog')->__('Add to Confirm Page'),
             'url'  => $this->getUrl('*/*/addtoconfirm'),
             'selected' => 1
        ));
        $this->getMassactionBlock()->setTemplate('straker/new/products/massaction.phtml');

        //todo: refine this
        $hiddenParams = '<input type="hidden" name="store" value="'.$this->_getStore()->getId().'" />';
        $hiddenParams .= '<input type="hidden" name="attr" value="'.$this->getAttr().'" />';
        $this->getMassactionBlock()->setHiddenParams($hiddenParams);


        Mage::dispatchEvent('adminhtml_strakertranslation_new_products_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/new', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getAttrArray(){
        return explode(',', $this->getAttr());
    }
}
