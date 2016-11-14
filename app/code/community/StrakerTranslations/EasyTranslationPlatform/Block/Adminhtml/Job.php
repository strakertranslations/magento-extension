<?php
class StrakerTranslations_EasyTranslationPlatform_Block_Adminhtml_Job extends Mage_Adminhtml_Block_Widget_Container{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('straker/job.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_job_grid', 'job.grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}