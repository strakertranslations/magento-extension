<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_Cms_PageController extends Mage_Adminhtml_Controller_Action{
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

    protected function _initNewAction()
    {
        $this
            ->loadLayout()
            ->_setActiveMenu('straker/new')
        ;

        return $this;
    }


    public function indexAction(){

        if (!$this->getRequest()->getParam('job_id')){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/straker_job/');
            return;
        }

        $this->_title($this->__('Straker Translations'))
            ->_title($this->__('Manage Jobs'));

        $this->loadLayout()->_setActiveMenu('straker/job');
        $this->renderLayout();

    }

    public function newAction(){
        $params = $this->getRequest()->getParams();
        if (empty($params['store'])) {
            $this->_redirect('*/straker_new');
        }
        elseif (empty($params['attr'])) {
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_page_attribute','strakertranslations_easytranslationplatform_new_cms_page_attribute',array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
        elseif (empty($params['cms_page'])) {
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_page','strakertranslations_easytranslationplatform_new_cms_page',array('setup_store_id' => $params['store'],  'attr' => $params['attr'])))
                ->renderLayout();
        }
        else{
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_page_confirm','strakertranslations_easytranslationplatform_new_cms_page_confirm',array('store' => $params['store'], 'cms_page' => $params['cms_page'], 'attr' => $params['attr'])))
                ->renderLayout();
        }
    }

    public function attributeAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store']){
            $this->_redirect('*/*/new', array('store' => $data['store'], 'attr' => implode(",",array_keys($data['attr']))));
        }
        else {
            $this->_redirect('*/straker_new/');
        }
    }

    public function addToConfirmAction(){
        $data = $this->_getParamArray();
        if(!empty($data['attr']) && !empty($data['store']) && !empty($data['cms_page'])){
            Mage::getSingleton('adminhtml/session')
                ->setData('straker_new_attr', $data['attr'])
                ->setData('straker_new_store', $data['store'])
                ->setData('straker_new_cms_page', $data['cms_page'])
            ;
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_page_confirm', 'strakertranslations_easytranslationplatform_new_cms_page_confirm', array('store' => $data['store'], 'cms_page' => $data['cms_page'], 'attr' => $data['attr'])))
                ->renderLayout();
        }
        else {
            if(array_key_exists('key', $data)){
                unset($data['key']);
            }
            $this->_redirect('*/*/new', $data);
        }
    }

    public function submitJobAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store'] && $data['cms_page']){
            /** @var  $jobModel StrakerTranslations_EasyTranslationPlatform_Model_Job */
            $jobModel = Mage::getModel('strakertranslations_easytranslationplatform/job');
            try {
                $jobModel->checkAndCreateFolder();
            }
            catch (Mage_Core_Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/', array(
                    'store' => $data['store']
                ));
                return;
            }
            $helper = Mage::helper('strakertranslations_easytranslationplatform');
            $storeSetup = $helper->getStoreSetup($data['store']);
            $jobModel->setStoreId($data['store']);
            $jobModel->setSl($storeSetup['from']);
            $jobModel->setTl($storeSetup['to']);
            $jobModel->setToken('Token');
            $jobModel->submitCmsPage(explode(',',$data['cms_page']),explode(',', $data['attr']));
            if ($jobModel->getLastStatus()) {
                Mage::getSingleton('adminhtml/session')
                    ->setData('straker_new_store', '')
                    ->setData('straker_new_cms_page', '')
                ;
                Mage::getSingleton('adminhtml/session')->addSuccess('New job created');
                $this->_redirect('*/straker_job/');
            }
            else{
                Mage::getSingleton('adminhtml/session')->addError($jobModel->getLastMessage());
                $this->_redirect('*/*/new');
            }
        }
        else {
            $this->_redirect('*/*/new', $data);
        }
    }

    public function publishAllAction(){
        /** @var  $job StrakerTranslations_EasyTranslationPlatform_Model_Job */
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($this->getRequest()->getParam('job_id'));

        if (!$job->getId()){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/straker_job/');
            return;
        }

        if($job->applyTranslation()) {
            Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been applied.');
            $this->_redirect('*/straker_job/');
            return;
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Error when applying translation.');
            $this->_redirect('*/straker_job/');
            return;
        }

    }

    public function publishAction(){
        $jobId = $this->getRequest()->getParam('job_id');
        /** @var StrakerTranslations_EasyTranslationPlatform_Model_Job $job */
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);

        if (!$job->getId()){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/straker_job/');
            return;
        }
        $cmsPageIds = $this->getRequest()->getParam('page_id');
        if(!empty($cmsPageIds)) {
            if ($job->applyTranslation($cmsPageIds)) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been applied.');
                $this->_redirect('*/straker_cms_page/',array('job_id' => $jobId));
                return;
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Error when applying translation.');
                $this->_redirect('*/straker_cms_page/',array('job_id' => $jobId));
                return;
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError('Please select products to apply the translation.');
            $this->_redirect('*/straker_cms_page/',array('job_id' => $jobId));
            return;
        }

    }

    public function gridAction()
    {
        $params = $this->_getParamArray();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock(
                    'strakertranslations_easytranslationplatform/adminhtml_new_cms_page_grid',
                    'strakertranslations_easytranslationplatform_new_cms_page_grid',
                    [
                        'store' => $params['store'], 'cms_page' => $params['cms_page'], 'attr' => $params['attr']
                    ]
                )->toHtml()
        );
    }

    public function confirmGridAction()
    {
        $params = $this->_getParamArray();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock(
                    'strakertranslations_easytranslationplatform/adminhtml_new_cms_page_confirm_grid',
                    'strakertranslations_easytranslationplatform_new_cms_page_confirm_grid',
                    [
                        'store' => $params['store'], 'cms_page' => $params['cms_page'], 'attr' => $params['attr']
                    ]
                )->toHtml()
        );
    }

    public function jobGridAction(){
        $jobId = $this->getRequest()->getParam('job_id');
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);
        $statusId = $job->getStatusId();
//        var_dump($params);exit;
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock(
                    'strakertranslations_easytranslationplatform/adminhtml_job_cms_page_grid',
                    'strakertranslations_easytranslationplatform_job_cms_page_grid',
                    [
                        'job_id' => $jobId
                    ]
                )
                ->setStatusId($statusId)
                ->toHtml()
        );
    }

    public function removeFromCartAction(){
        $entityId = $this->getRequest()->getParam('page_id');
        if(!empty($entityId)){
            $entityId = $this->getRequest()->getParam('page_id');
        }
        $pageIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_cms_page');
        if(!is_array($pageIds)){
            $pageIds = explode(',', trim($pageIds, ','));
        }
        if( ($key =  array_search($entityId, $pageIds)) !== false ){
            unset($pageIds[$key]);
        }
        Mage::getSingleton('adminhtml/session')->setData('straker_new_cms_page', $pageIds);
        $this->_redirect('*/*/addToConfirm');
    }

    private function _getParamArray(){
        $data = $this->getRequest()->getParams();
        $data['store'] = !empty($data['store']) ? $data['store'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_store');
        $data['attr'] = !empty($data['attr']) ? $data['attr'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_attr');
        $data['cms_page'] = !empty($data['cms_page']) ? $data['cms_page'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_cms_page');
        return $data;
    }
}