<?php

namespace Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Straker\EasyTranslationPlatform\Model\PageCollection;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Model\JobFactory;

class Pages extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $pageCollection;
    protected $jobFactory;
    protected $sourceStoreId;

    protected $_configHelper;



    public function __construct(
        Context $context,
        Data $backendHelper,
        JobFactory $jobFactory,
        PageCollection $pageCollection,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->jobFactory = $jobFactory;
        $this->pageCollection = $pageCollection;
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
        $this->setId('pagesGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->sourceStoreId = $this->getRequest()->getParam('source_store_id');
        $this->targetStoreId = $this->getRequest()->getParam('target_store_id');
    }


    protected function _prepareCollection()
    {
        $collection = $this->pageCollection;

        $collection->is_translated($this->targetStoreId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'in_page',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_page',
                'align' => 'center',
                'index' => 'page_id'
            ]
        );

        $this->addColumn(
            'page_id',
            [
                'header' => __('Page ID'),
                'type' => 'number',
                'index' => 'page_id',
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
                'width' => '50px',
            ]
        );

        $this->addColumn(
            'is_translated',
            [
                'header' => __('Translated'),
                'index' => 'is_translated',
                'width' => '50px',
                'type'=>'options',
                'options'=>['1'=>'Yes','0'=>'No'],
                'filter_index'=>'stTrans.translated_value',
                'renderer' => 'Straker\EasyTranslationPlatform\Block\Adminhtml\Job\Edit\Grid\Renderer\TranslatedValue',
                'filter_condition_callback' => [$this, 'filterName']
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/pagesgrid', ['_current' => true]);
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

    function filterName($collection, $column)
    {
        $condition = $column->getFilter()->getCondition();
        $collection->getSelect()->having('`b.is_imported` LIKE ' . reset($condition));
        return $this;
    }
}
