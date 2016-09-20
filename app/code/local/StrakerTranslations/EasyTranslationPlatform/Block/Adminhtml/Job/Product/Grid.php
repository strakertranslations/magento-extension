<?php

class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('strakerJobProductGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {

        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));
        $jobAttributes =  Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')->getCollection()->addFieldToFilter('job_id', $job->getId());
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job_product')->getCollection()->addFieldToFilter('main_table.job_id', $job->getId());

        //loop through this job's attributes and join them to the collection
        foreach($jobAttributes as $jobAttribute){
            $attributeCode = Mage::getModel('eav/entity_attribute')->load($jobAttribute->getAttributeId())->getAttributeCode();
            $collection->getSelect()->joinLeft(
                array($attributeCode => 'straker_product_translate'),
                $attributeCode.'.product_id = main_table.product_id AND '.$attributeCode.'.attribute_id = '.$jobAttribute->getAttributeId(). ' AND '
.$attributeCode.'.job_id = '.$job->getId(),
                array($attributeCode.'_original' => 'original', $attributeCode.'_translate' => 'translate')
            );
        }
//echo $collection->getSelect()->__toString();
//die();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
//        $this->addColumn('id', array(
//            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('ID'),
//            'align' => 'right',
//            'width' => '50px',
//            'index' => 'id',
//        ));

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
        $jobAttributes =  Mage::getModel('strakertranslations_easytranslationplatform/product_attributes')->getCollection()->addFieldToFilter('job_id', $job->getId());

        //loop through this job's attributes and add columns to the grid
        foreach($jobAttributes as $jobAttribute){
            $attributeCode = Mage::getModel('eav/entity_attribute')->load($jobAttribute->getAttributeId())->getAttributeCode();
            $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode(4, $attributeCode);
            $this->addColumn($attributeCode.'_original', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s Original', $attrModel->getFrontendLabel()),
                'align' => 'left',
                'index' => $attributeCode.'_original',
            ));

            $this->addColumn($attributeCode.'_translate', array(
                'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('%s Translate', $attrModel->getFrontendLabel()),
                'align' => 'left',
                'index' => $attributeCode.'_translate',
            ));
        }

        $this->addColumn('view_frontend', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('View Frontend'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Frontend',
            'align' => 'center',
            'index' => false,
            'filter'    => false,
        ));

        $this->addColumn('view_backend', array(
            'header' => Mage::helper('strakertranslations_easytranslationplatform')->__('View Backend'),
            'renderer' => 'StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Template_Grid_Renderer_Backend',
            'align' => 'center',
            'index' => false,
            'filter'    => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
//        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return '';
    }

}
