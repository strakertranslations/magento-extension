<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_New_Cms_Block_Confirm_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerCmsBlocksConfirm');
        $this->setDefaultSort('identifier');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('cms_block_confirm_filter');
        //        $this->setFilterVisibility(false);
    }

    protected function _prepareLayout()
    {
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/block')->getCollection()->addFieldToFilter('block_id', $this->getCmsBlock());
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'    => Mage::helper('cms')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
            'filter'    => false,
        ));

        $this->addColumn('identifier', array(
            'header'    => Mage::helper('cms')->__('Identifier'),
            'align'     => 'left',
            'index'     => 'identifier',
            'filter'    => false,
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
//                'filter_condition_callback'
//                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('cms')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            ),
            'filter'    => false,
        ));

        $this->addColumn('creation_time', array(
            'header'    => Mage::helper('cms')->__('Date Created'),
            'index'     => 'creation_time',
            'type'      => 'datetime',
            'filter'    => false,
        ));

        $this->addColumn('update_time', array(
            'header'    => Mage::helper('cms')->__('Last Modified'),
            'index'     => 'update_time',
            'type'      => 'datetime',
            'filter'    => false,
        ));

        $this->addColumn(
            'action',
            [
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getBlockId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Remove'),
                        'url'     => [
                            'base'=>'*/*/removeFromCart'
                        ],
                        'field'   => 'block_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'block_id'
            ]
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

    public function getGridUrl()
    {
        return $this->getUrl('*/*/ConfirmGrid', array('_current'=>true));
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
}
