<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job_Cms_Page extends Mage_Adminhtml_Block_Widget_Container{

    protected $_job;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/job/cms/page.phtml');
    }

    protected function _prepareLayout()
    {
        $jobId = $this->getRequest()->getParam('job_id');
        $jobStatus = $this->getJob()->getStatusId();
        if ( $jobStatus == '4'){
            $this->_addButton('publish', array(
                'label'   => Mage::helper('catalog')->__('Publish All Translations'),
                'onclick' => "setLocation('{$this->getUrl('*/*/copyAll',array('job_id'=>$jobId))}');",
                'class'   => 'task'
            ));
        }

        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_job_cms_page_grid', 'job_cms_page.grid'));
        $this->getChild('grid')->setStatusId($jobStatus);
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    public function getJob(){
        if(!$this->_job) {
            $jobId = $this->getRequest()->getParam('job_id');
            $this->_job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);
        }
        return $this->_job;
    }
}