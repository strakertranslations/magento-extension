<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Product extends Mage_Adminhtml_Block_Widget_Container{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/job/product.phtml');
    }

    protected function _prepareLayout()
    {
        $jobId = $this->getRequest()->getParam('job_id');
        if (Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId)->getStatusId() == '4'){
            $this->_addButton('dispute', array(
                'label'   => Mage::helper('catalog')->__('Dispute'),
                'onclick' => 'disputeForm.show(\''.$jobId.'\')',
                'class'   => 'task'
            ));
            $this->_addButton('publish', array(
                'label'   => Mage::helper('catalog')->__('Publish Translation'),
                'onclick' => "setLocation('{$this->getUrl('*/*/copyAll',array('job_id'=>$jobId))}');",
                'class'   => 'task'
            ));
        }

        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_job_product_grid', 'job_product.grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}