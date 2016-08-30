<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;

class Blocks extends Extended
{

    protected $_blockCollectionFactory;
    protected $_jobFactory;
    protected $_sourceStoreId;
    protected $_configHelper;

    public function __construct(
        Context $context,
        Data $backendHelper,
        JobFactory $jobFactory,
        BlockCollectionFactory $blockCollectionFactory,
        ConfigHelper $configHelper,
        array $data = []
    ) {

        $this->_jobFactory = $jobFactory;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_configHelper = $configHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blocksGrid');
//        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('target_store_id')) {
            $store_info = $this->_configHelper->getStoreInfo($this->getRequest()->getParam('target_store_id'));
            $this->_sourceStoreId = $store_info['straker/general/source_store'];
        }
    }

    /**
     * add Column Filter To Collection
     */
//    protected function _addColumnFilterToCollection($column)
//    {
//        if ($column->getId() == 'in_product') {
//            //$productIds = $this->_getSelectedProducts();
//
//            if (empty($productIds)) {
//                $productIds = 0;
//            }
//            if ($column->getFilter()->getValue()) {
//                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
//            } else {
//                if ($productIds) {
//                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
//                }
//            }
//        } else {
//            parent::_addColumnFilterToCollection($column);
//        }
//
//        return $this;
//    }


    protected function _prepareCollection()
    {
        $collection = $this->_blockCollectionFactory->create();
        $collection->addStoreFilter( $this->_sourceStoreId );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'in_block',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_block',
                'align' => 'center',
                'index' => 'block_id',
                'values' => $this->_getSelectedBlocks()
            ]
        );

        $this->addColumn(
            'block_id',
            [
                'header' => __('Block ID'),
                'type' => 'number',
                'index' => 'block_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'class' => 'xxx',
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _getSelectedBlocks()
    {
        $blocks = $this->getRequest()->getPost('job_blocks');
        if (is_array($blocks)) {
            return $blocks;
        }
        return [];
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/blocksgrid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }
}
