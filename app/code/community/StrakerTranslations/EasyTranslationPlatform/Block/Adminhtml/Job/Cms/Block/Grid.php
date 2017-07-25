<?php

class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Cms_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_attributes;

    public function __construct() {
        parent::__construct();
        $this->setId('strakerJobCmsBlockGrid');
        $this->setUseAjax(true);
        $this->setVarNameFilter('straker_job_cms_block_filter');
    }

    protected function _prepareLayout()
    {
        $this->setChild('dispute_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('catalog')->__('Feedback'),
                    'onclick' => 'disputeForm.show(\''.$this->getRequest()->getParam('job_id').'\')',
                    'class'   => 'feedback'
                ))
        );

        return parent::_prepareLayout();
    }

    protected function _prepareCollection() {
//        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));
        $jobAttributes = Mage::getModel('strakertranslations_easytranslationplatform/cms_block_attributes')->getCollection()->addFieldToFilter('job_id', $job->getId());
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Cms_Block_Collection $collection */
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_cms_block')->getCollection()
            ->addFieldToFilter('main_table.job_id', $job->getId());

//echo $collection->getSelect(); die();
        $collection->getSelect()
            ->joinLeft(
                array('translate' => $collection->getTable('strakertranslations_easytranslationplatform/cms_block_translate')),
                'main_table.id = translate.id
                AND translate.cms_block_id = main_table.block_id AND translate.job_id = '.$job->getId(),
                array()
            );

        //loop through this job's attributes and join them to the collection
        foreach ($jobAttributes as $jobAttribute) {
            $attributeCode = $jobAttribute->getColumnName();
            $collection->getSelect()->joinLeft(
                array($attributeCode => $collection->getTable('strakertranslations_easytranslationplatform/cms_block_translate')),
                $attributeCode . '.cms_block_id = main_table.block_id 
                AND ' . $attributeCode . '.column_name = \'' . $attributeCode . '\' 
                AND ' . $attributeCode . '.job_id = ' . $job->getId(),
                array($attributeCode . '_original' => 'original', $attributeCode . '_translate' => 'translate')
            );
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('ID'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'id'
        ));

        $this->addColumn('block_id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Block ID'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'block_id'
        ));

        $this->addColumn('identifier', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Identifier'),
            'align' => 'center',
            'index' => 'identifier'
        ));

        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));
        $jobAttributes = Mage::getModel('strakertranslations_easytranslationplatform/cms_block_attributes')->getCollection()->addFieldToFilter('job_id', $job->getId());
        //loop through this job's attributes and add columns to the grid
        foreach ($jobAttributes as $jobAttribute) {
            $attributeCode = $jobAttribute->getColumnName();
            if($attributeCode === 'content'){
                $this->addColumn($attributeCode . '_original', array(
                    'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('%s - Source', ucfirst($attributeCode)),
                    'renderer'  => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_TextArea',
                    'align'     => 'left',
                    'index'     => $attributeCode . '_original',
                    'filter'    => false,
                    'sortable'  => false
                ));

                $this->addColumn($attributeCode . '_translate', array(
                    'header'    => Mage::helper('strakertranslations_easytranslationplatform')->__('%s - Target', ucfirst($attributeCode)),
                    'renderer'  => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_TextArea',
                    'align'     => 'left',
                    'index'     => $attributeCode . '_translate',
                    'filter'    => false,
                    'sortable'  => false
                ));
            }else{
                $this->addColumn($attributeCode . '_original', array(
                    'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s - Source', ucfirst($attributeCode)),
                    'align' => 'left',
                    'index' => $attributeCode . '_original',
                    //                'filter_index' => $attributeCode.'.translate',
                    'filter' => false,
                    'sortable'  => false
                ));

                $this->addColumn($attributeCode . '_translate', array(
                    'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s - Target', ucfirst($attributeCode)),
                    'align' => 'left',
                    'index' => $attributeCode . '_translate',
                    //                'filter_index' => $attributeCode.'.translate',
                    'filter' => false,
                    'sortable'  => false
                ));
            }
        }

        if ($this->getStatusId() == '4' || $this->getStatusId() == '5'){
            $this->addColumn('version', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Published'),
                'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Version',
                'align' => 'center',
                'type' => 'options',
                'index' => 'version',
                'filter_condition_callback' => [$this, '_filterVersion'],
                'options' => [
                    '0' => Mage::helper('strakertranslations_easytranslationplatform')->__('Published'),
                    '1' => Mage::helper('strakertranslations_easytranslationplatform')->__('Not Published')
                ],
                'width' => '20%'
            ));
        }

        $this->addColumn('view_backend', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('View Backend'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend',
            'align' => 'center',
            'index' => false,
            'filter' => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
//        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return '';
    }

    protected function _filterVersion($collection, $column)
    {
        if ( ($value = $column->getFilter()->getValue()) === FALSE ) {
            return $this;
        }
        if ($value === '1' ){
            $this->getCollection()->getSelect()->where('`main_table`.`version` IS NULL');
        } else {
            $this->getCollection()->getSelect()->where('`main_table`.`version` IS NOT NULL');
        }
        return $this;
    }

    protected function _prepareMassaction()
    {
        if ($this->getStatusId() == '4') {

            $this->setMassactionIdField('block_id');
            $this->getMassactionBlock()->setFormFieldName('block_id');

            $this->getMassactionBlock()->addItem('add', array(
                'label' => Mage::helper('catalog')->__('Publish Translation'),
                'url' => $this->getUrl('*/*/publish'),
                'selected' => 1
            ));

            $this->getMassactionBlock()->setTemplate('straker/job/cms/page/massaction.phtml');

            $hiddenParams = '<input type="hidden" name="job_id" value="' . $this->getRequest()->getParam('job_id') . '" />';
            $this->getMassactionBlock()->setHiddenParams($hiddenParams);


//            Mage::dispatchEvent('adminhtml_strakertranslation_job_product_grid_prepare_massaction', array('block' => $this));
        }
        return $this;
    }

    protected function _prepareMassactionColumn()
    {
        if ($this->getStatusId() == '4') {
            $columnId = 'massaction';
            $massactionColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
                ->setData(array(
                    'index' => $this->getMassactionIdField(),
                    'use_index' => $this->getMassactionIdField(),
                    'filter_index' => $this->getMassactionIdFilter(),
                    'type' => 'massaction',
                    'name' => $this->getMassactionBlock()->getFormFieldName(),
                    'align' => 'center',
                    'is_system' => true
                ));

            if ($this->getNoFilterMassactionColumn()) {
                $massactionColumn->setData('filter', false);
            }

            $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())
                ->setGrid($this)
                ->setId($columnId);

            $oldColumns = $this->_columns;
            $this->_columns = array();
            $this->_columns[$columnId] = $massactionColumn;
            $this->_columns = array_merge($this->_columns, $oldColumns);
            return $this;
        }
    }

    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getStatusId() == '4') {
            $html .= $this->getChildHtml('dispute_button');
        }
        if($this->getFilterVisibility()){
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }
        return $html;
    }

    protected function getAttributes(){
        if(!$this->_attributes){
            $this->_attributes = Mage::getModel('strakertranslations_easytranslationplatform/cms_block_attributes')->getCollection()->addFieldToFilter('job_id',$this->getRequest()->getParam('job_id'));
        }
        return $this->_attributes;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/jobGrid', array('_current' => true));
    }
}
