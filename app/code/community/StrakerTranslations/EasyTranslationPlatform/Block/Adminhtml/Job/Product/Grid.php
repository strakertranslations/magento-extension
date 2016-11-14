<?php

class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('strakerJobProductGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareLayout()
    {
        $this->setChild('dispute_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Feedback'),
                    'onclick' => 'disputeForm.show(\'' . $this->getRequest()->getParam('job_id') . '\')',
                    'class' => 'feedback'
                ))
        );

        $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/catalog_product_grid', 'product.grid'));
        return parent::_prepareLayout();
    }

    protected function _prepareCollection()
    {
//        $prefix = Mage::getConfig()->getTablePrefix()->__toString();
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));
        $jobAttributes = Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')->getCollection()->addFieldToFilter('job_id', $job->getId());

        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Product_Collection $collection */
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_product')->getCollection()->addFieldToFilter('main_table.job_id', $job->getId());

        //loop through this job's attributes and join them to the collection
        foreach ($jobAttributes as $jobAttribute) {
            $attributeCode = Mage::getModel('eav/entity_attribute')->load($jobAttribute->getAttributeId())->getAttributeCode();
            $collection->getSelect()->joinLeft(
                array($attributeCode => $collection->getTable('strakertranslations_easytranslationplatform/product_translate')),
                $attributeCode . '.product_id = main_table.product_id 
                AND ' . $attributeCode . '.attribute_id = ' . $jobAttribute->getAttributeId() . ' 
                AND ' . $attributeCode . '.job_id = ' . $job->getId(),
                array($attributeCode . '_original' => 'original', $attributeCode . '_translate' => 'translate')
            );
        }
//echo $collection->getSelect()->__toString();
//die();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('product_id', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Product ID'),
            'align' => 'left',
            'index' => 'product_id',
        ));

//        $this->addColumn('job_id', array(
//            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Job ID'),
//            'align' => 'left',
//            'index' => 'job_id',
//        ));

        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));
        $jobAttributes = Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')->getCollection()->addFieldToFilter('job_id', $job->getId());

        //loop through this job's attributes and add columns to the grid
        foreach ($jobAttributes as $jobAttribute) {
            $attributeCode = Mage::getModel('eav/entity_attribute')->load($jobAttribute->getAttributeId())->getAttributeCode();
            $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(4, $attributeCode);
            $this->addColumn($attributeCode . '_original', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s - Source', $attrModel->getFrontendLabel()),
                'align' => 'left',
                'index' => $attributeCode . '_original',
            ));

            $this->addColumn($attributeCode . '_translate', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s - Target', $attrModel->getFrontendLabel()),
                'align' => 'left',
                'index' => $attributeCode . '_translate',
            ));
        }

        if ($this->getStatusId() == '4') {
            $this->addColumn('version', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('Published'),
                'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Version',
                'align' => 'center',
                'index' => false,
                'filter' => false,
            ));
        }

        $this->addColumn('view_frontend', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('View Frontend'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Frontend',
            'align' => 'center',
            'index' => false,
            'filter' => false,
        ));

        $this->addColumn('view_backend', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('View Backend'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend',
            'align' => 'center',
            'index' => false,
            'filter' => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
//        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return '';
    }

    protected function _prepareMassaction()
    {
        if ($this->getStatusId() == '4') {

            $this->setMassactionIdField('product_id');
            $this->getMassactionBlock()->setFormFieldName('product');

            $this->getMassactionBlock()->addItem('add', array(
                'label' => Mage::helper('catalog')->__('Publish Translation'),
                'url' => $this->getUrl('*/*/applyTranslation'),
                'selected' => 1
            ));
            $this->getMassactionBlock()->setTemplate('straker/job/product/massaction.phtml');

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
        if ($this->getFilterVisibility()) {
            $html .= $this->getResetFilterButtonHtml();
            $html .= $this->getSearchButtonHtml();
        }
        return $html;
    }
}
