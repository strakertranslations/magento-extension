<?php

class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('strakerJobAttributeGrid');
        $this->setSaveParametersInSession(true);
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
        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));

        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_attribute')->getCollection()
            ->addFieldToFilter('main_table.job_id', $job->getId());

        $collection->getSelect()->joinLeft(
            array('ea' => $prefix.'eav_attribute'),
            'ea.attribute_id = main_table.attribute_id',
            array('attribute_code' => 'attribute_code')
        );
        $collection->getSelect()->joinLeft(
            array('translate' => $prefix.'straker_attribute_translate'),
            'translate.attribute_id = main_table.attribute_id AND translate.job_id = '.$job->getId(),
            array('original' => 'original', 'translate' => 'translate')
        );


//echo $collection->getSelect();
//die();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('attribute_id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute ID'),
            'align' => 'left',
            'index' => 'attribute_id',
        ));

        $this->addColumn('attribute_code', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Attribute Code'),
            'align' => 'left',
            'index' => 'attribute_code',
        ));


        $this->addColumn('label_original', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Label - Source'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_AttributeOriginalLabel',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        $this->addColumn('option_original', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Option - Source'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_AttributeOriginalOption',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        $this->addColumn('label_translate', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Label - Target'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_AttributeTranslateLabel',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        $this->addColumn('option_translate', array(
            'header'=>Mage::helper('strakertranslations_easytranslationplatform')->__('Option - Target'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_AttributeTranslateOption',
            'align' => 'center',
            'index' => false,
            'sortable'=> false,
            'filter'    => false,
        ));

        if ($this->getStatusId() == '4'){
            $this->addColumn('version', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Published'),
                'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Version',
                'align' => 'center',
                'index' => false,
                'filter'    => false,
            ));
        }

//        $this->addColumn('view_backend', array(
//            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('View Backend'),
//            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend',
//            'align' => 'center',
//            'index' => false,
//            'filter'    => false,
//        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
//        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return '';
    }

    protected function _prepareMassaction()
    {
        if ($this->getStatusId() == '4') {

            $this->setMassactionIdField('attribute_id');
            $this->getMassactionBlock()->setFormFieldName('attribute');

            $this->getMassactionBlock()->addItem('add', array(
                'label' => Mage::helper('catalog')->__('Publish Translation'),
                'url' => $this->getUrl('*/*/applyTranslation'),
                'selected' => 1
            ));

            $this->getMassactionBlock()->setTemplate('straker/job/attribute/massaction.phtml');

            $hiddenParams = '<input type="hidden" name="job_id" value="' . $this->getRequest()->getParam('job_id') . '" />';
            $this->getMassactionBlock()->setHiddenParams($hiddenParams);


//            Mage::dispatchEvent('adminhtml_strakertranslation_job_product_grid_prepare_massaction', array('block' => $this));
            return $this;
        }
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
}
