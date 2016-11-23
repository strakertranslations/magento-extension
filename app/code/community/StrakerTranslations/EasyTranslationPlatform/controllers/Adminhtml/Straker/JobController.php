<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_JobController extends Mage_Adminhtml_Controller_Action{
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

        /** @var $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $helper->checkSiteMode();

        //todo refresh all jobs that is waiting on a quote. This should be refactored into different process.
        /** @var $collection StrakerTranslations_EasyTranslationPlatform_Model_Resource_Job_Collection */
        $collection = Mage::getModel('strakertranslations_easytranslationplatform/job')
            ->getCollection()
            ->addFieldToFilter('status_id', array('lt' => 4));

//        foreach($collection as $job){
//            $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($job->getId());
//            if ( $job->updateTranslation() ){
//                Mage::getSingleton('core/session')->addSuccess($this->__('Job %s has been updated.', $job->getId()));
//            }
//        }
        if ($collection->count() > 1){
            /** @var StrakerTranslations_EasyTranslationPlatform_Model_Job $job */
            $job = Mage::getModel('strakertranslations_easytranslationplatform/job');
            $response = $job->bulkUpdateTranslation();
            if ( $response ) {
                foreach($collection as $jobModel){
                    foreach ($response as $jobResponse) {
                        if ($jobResponse->token == $jobModel->getId()) {
                            $jobModel = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobModel->getId());
                            $jobModel->updateJob($jobResponse);
                        }
                    }
                    if ( $job->updateTranslation() ){

                        Mage::getSingleton('core/session')->addSuccess($this->__('Job %s has been updated.', $job->getId()));
                    }
                }
            }
        }

        $this->_title($this->__('Straker Translations'))
            ->_title($this->__('Manage Jobs'));

        $this->loadLayout();
        $this->renderLayout();
    }

    public function updateJobAction() {

        $data = $this->getRequest()->getParams();
        if($data['job_id']){
            $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load((int) $data['job_id']);

            if(!$job->getJobKey()){
                return false;
            }

            if( $job->updateTranslation() ){
                Mage::getSingleton('core/session')->addSuccess($this->__('Job %s has been updated.', $job->getId()));
            }
            $this->_redirect('*/*/');
            return;

        }

        return false;

    }


    public function disputeAction() {
        $data = $this->getRequest()->getParams();
        if($data['job_id'] && $data['message']){
            $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load((int) $data['job_id']);


            $response = $job->submitSupport(array(
                'title' => 'Dispute for '.$job->getTjNumber(),
                'message' => $data['message']
            ));

            //Send out email

            $mail = new Zend_Mail(); //class for mail
            $mail->setBodyHtml($data['message']); //for sending message containing html code
            $mail->setFrom(Mage::getStoreConfig('trans_email/ident_general/email'), Mage::getStoreConfig('trans_email/ident_general/name'));
            $mail->addTo('processing@strakertranslations.com', 'Straker Support');
            $mail->setSubject('Dispute for '.$job->getTjNumber());
            $msg  ='';
            Mage::log(print_r($mail,true), null , 'debugging.log' , true);
            try {
                if($mail->send())
                {
                    $msg = true;
                }
            }
            catch(Exception $ex) {
                $msg = false;
            }
            if($msg){
                Mage::getSingleton('adminhtml/session')->addSuccess('Feedback has been submitted.');
            }
            else{
                Mage::getSingleton('adminhtml/session')->addError('Unkown error when sending email to processing@strakertranslations.com.');
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError('Required parameters missing when submitting a feedback.');
        }
        $this->_redirect('adminhtml/straker_product/index', array(
            'job_id' => $data['job_id']
        ));
        return;
    }

}