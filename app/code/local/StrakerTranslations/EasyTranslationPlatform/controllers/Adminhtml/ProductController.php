<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/straker/job');
    }

    protected function _initAction()
    {
        $this
            ->loadLayout()
            ->_setActiveMenu('straker/job')
        ;

        return $this;
    }

    public function indexAction(){

        if (!$this->getRequest()->getParam('job_id')){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/adminhtml_job/');
            return;
        }

        $this->_title($this->__('Straker Translations'))
            ->_title($this->__('Manage Jobs'));

        $this->loadLayout();
        $this->renderLayout();

//        try {
//            $this->_title($this->__('Manage Jobs'));
//            return $this->_initAction()
//                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_job_grid'))
//                ->renderLayout();
//        } catch (Exception $e) {
//            Mage::getSingleton('core/session')->addError($this->__('Error occurred. Please contact service administrator.'));
//            $this->_redirect('adminhtml/dashboard');
//        }
    }
    public function copyAllAction(){
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));

        if (empty($job)){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/adminhtml_job/');
            return;
        }

        if($job->applyTranslation()) {
            Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been copied.');
            $this->_redirect('*/adminhtml_job/');
            return;
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Error when copying translation.');
            $this->_redirect('*/adminhtml_job/');
            return;
        }


    }
}