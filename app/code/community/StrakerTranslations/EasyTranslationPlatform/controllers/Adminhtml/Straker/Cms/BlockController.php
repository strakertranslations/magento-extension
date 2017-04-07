<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_Cms_BlockController extends Mage_Adminhtml_Controller_Action{
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
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_block_attribute','strakertranslations_easytranslationplatform_new_cms_block_attribute',array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
        elseif (empty($params['cms_block'])) {
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_block','strakertranslations_easytranslationplatform_new_cms_block',array('setup_store_id' => $params['store'], 'attr' => $params['attr'])))
                ->renderLayout();
        }
        else{
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_block_confirm','strakertranslations_easytranslationplatform_new_cms_block_confirm',array('store' => $params['store'], 'cms_block' => $params['cms_block'], 'attr' => $params['attr'])))
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

    public function addtoconfirmAction(){
        $data = $this->_getParamArray();
        if(!empty($data['attr']) && !empty($data['store']) && !empty($data['cms_block'])){
            Mage::getSingleton('adminhtml/session')
                ->setData('straker_new_attr', $data['attr'])
                ->setData('straker_new_store', $data['store'])
                ->setData('straker_new_cms_block', $data['cms_block'])
            ;
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_cms_block_confirm', 'strakertranslations_easytranslationplatform_new_cms_block_confirm', array('store' => $data['store'], 'cms_block' => $data['cms_block'], 'attr' => $data['attr'])))
                ->renderLayout();
        }
        else {
            if(array_key_exists('key', $data)){
                unset($data['key']);
            }
            $this->_redirect('*/*/new/', $data);
        }
    }

    public function submitjobAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store'] && $data['cms_block']){
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
            $jobModel->submitCmsBlock(explode(',',$data['cms_block']),explode(',', $data['attr']));
            if ($jobModel->getLastStatus()) {
                Mage::getSingleton('adminhtml/session')
                    ->setData('straker_new_store', '')
                    ->setData('straker_new_cms_block', '')
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

    public function copyAllAction(){
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

    public function applyTranslationAction(){
        $jobId = $this->getRequest()->getParam('job_id');
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);

        if (!$job->getId()){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/straker_job/');
            return;
        }
        $blockIds = $this->getRequest()->getParam('block_id');
        if(!empty($blockIds)) {
            if ($job->applyTranslation($blockIds)) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been applied.');
                $this->_redirect('*/straker_cms_block/',array('job_id' => $jobId));
                return;
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Error when applying translation.');
                $this->_redirect('*/straker_cms_block/',array('job_id' => $jobId));
                return;
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError('Please select products to apply the translation.');
            $this->_redirect('*/straker_cms_block/',array('job_id' => $jobId));
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
                    'strakertranslations_easytranslationplatform/adminhtml_new_cms_block_grid',
                    'strakertranslations_easytranslationplatform_new_cms_block_grid',
                    [
                        'store' => $params['store'], 'cms_block' => $params['cms_block'], 'attr' => $params['attr']
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
                    'strakertranslations_easytranslationplatform/adminhtml_new_cms_block_confirm_grid',
                    'strakertranslations_easytranslationplatform_new_cms_block_confirm_grid',
                    [
                        'store' => $params['store'], 'cms_block' => $params['cms_block'], 'attr' => $params['attr']
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
                    'strakertranslations_easytranslationplatform/adminhtml_job_cms_block_grid',
                    'strakertranslations_easytranslationplatform_job_cms_block_grid',
                    [
                        'job_id' => $jobId
                    ]
                )
                ->setStatusId($statusId)
                ->toHtml()
        );
    }

    public function removeFromCartAction(){
        $entityId = 0;
        if(!empty($this->getRequest()->getParam('block_id'))){
            $entityId = $this->getRequest()->getParam('block_id');
        }
        $blockIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_cms_block');
        if(!is_array($blockIds)){
            $blockIds = explode(',', trim($blockIds, ','));
        }
        if( ($key =  array_search($entityId, $blockIds)) !== false ){
            unset($blockIds[$key]);
        }
        Mage::getSingleton('adminhtml/session')->setData('straker_new_cms_block', $blockIds);
        $this->_redirect('*/*/addtoconfirm');
    }

    private function _getParamArray(){
        $data = $this->getRequest()->getParams();
        $data['store'] = !empty($data['store']) ? $data['store'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_store');
        $data['attr'] = !empty($data['attr']) ? $data['attr'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_attr');
        $data['cms_block'] = !empty($data['cms_block']) ? $data['cms_block'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_cms_block');
        return $data;
    }
}