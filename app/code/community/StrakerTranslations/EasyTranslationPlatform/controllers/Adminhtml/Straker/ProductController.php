<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_ProductController extends Mage_Adminhtml_Controller_Action{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/straker/job');
    }

    protected function _initAction()
    {
        $this->checkSiteMode();

        $this
            ->loadLayout()
            ->_setActiveMenu('straker/job')
        ;

        return $this;
    }

    protected function _initNewAction()
    {
        $this->checkSiteMode();

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

    public function newAction(){
        $params = $this->getRequest()->getParams();
        if (empty($params['store'])) {
            $this->_redirect('*/straker_new');
        }
        elseif (empty($params['attr'])) {
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_products_attribute','strakertranslations_easytranslationplatform_new_products_attribute',array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
        else{
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_products','strakertranslations_easytranslationplatform_new_products',array('setup_store_id' => $params['store'], 'attr' => $params['attr'])))
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
        if(!empty($data['attr']) && !empty($data['store']) && !empty($data['product'])){
            Mage::getSingleton('adminhtml/session')
                ->setData('straker_new_attr', $data['attr'])
                ->setData('straker_new_store', $data['store'])
                ->setData('straker_new_product', $data['product'])
            ;
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_products_confirm', 'strakertranslations_easytranslationplatform_new_products_confirm', array('store' => $data['store'], 'attr' => $data['attr'], 'product' => $data['product'])))
                ->renderLayout();
        }
        else {
            if(array_key_exists('key', $data)){
                unset($data['key']);
            }
            $this->_redirect('*/*/new/', $data);
        }
    }

    public function submitJobAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store'] && $data['product']){
            /** @var  $jobModel StrakerTranslations_EasyTranslationPlatform_Model_Job */
            $jobModel = Mage::getModel('strakertranslations_easytranslationplatform/job');
            try {
                $jobModel->checkAndCreateFolder();
            }
            catch (Mage_Core_Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/', array(
                    'store' => $data['store'],
                    'attr' => $data['attr']
                ));
                return;
            }
            $attributeCodes = array_unique(explode(',', $data['attr']));
            $attr_ids = [];
            foreach($attributeCodes as $attributeCode){
                $attr_ids[] = $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode)->getAttributeId();
            }
            $helper = Mage::helper('strakertranslations_easytranslationplatform');
            $storeSetup = $helper->getStoreSetup($data['store']);
            $jobModel->setStoreId($data['store']);
            $jobModel->setSl($storeSetup['from']);
            $jobModel->setTl($storeSetup['to']);
            $jobModel->setToken('Token');
            $jobModel->submitProducts($attr_ids, explode(',',$data['product']));
            if ($jobModel->getLastStatus()) {
                Mage::getSingleton('adminhtml/session')
                    ->setData('straker_new_attr', '')
                    ->setData('straker_new_store', '')
                    ->setData('straker_new_product', '')
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
        /** @var  $job StrakerTranslations_EasyTranslationPlatform_Model_Job */
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);

        if (!$job->getId()){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/straker_job/');
            return;
        }
        $productIds = $this->getRequest()->getParam('product');
        if(!empty($productIds)) {
            if ($job->applyTranslation($productIds)) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been applied.');
                $this->_redirect('*/straker_product/',array('job_id' => $jobId));
                return;
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Error when applying translation.');
                $this->_redirect('*/straker_product/',array('job_id' => $jobId));
                return;
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError('Please select products to apply the translation.');
            $this->_redirect('*/straker_product/',array('job_id' => $jobId));
            return;
        }

    }

    private function checkSiteMode(){
        /** @var $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $helper->checkSiteMode();
    }

    public function removeFromCartAction(){
        $entityId = $this->getRequest()->getParam('entity_id');
        if(!empty($entityId)){
            $entityId = $this->getRequest()->getParam('entity_id');
        }
        $productIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_product');
        if(!is_array($productIds)){
            $productIds = explode(',', trim($productIds, ','));
        }
        if( ($key =  array_search($entityId, $productIds)) !== false ){
            unset($productIds[$key]);
        }
        Mage::getSingleton('adminhtml/session')->setData('straker_new_product', $productIds);
        $this->_redirect('*/*/addToConfirm');
    }

    public function gridAction()
    {
        $data = $this->_getParamArray();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock(
                    'strakertranslations_easytranslationplatform/adminhtml_new_products_grid',
                    'strakertranslations_easytranslationplatform_new_products_grid',
                    [
                        'store' => $data['store'], 'attr' => $data['attr'], 'product' => $data['product']
                    ]
                )->toHtml()
        );
    }

    public function confirmGridAction()
    {
        $data = $this->_getParamArray();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock(
                    'strakertranslations_easytranslationplatform/adminhtml_new_products_confirm_grid',
                    'strakertranslations_easytranslationplatform_new_products_confirm_grid',
                    [
                        'store' => $data['store'], 'attr' => $data['attr'], 'product' => $data['product']
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
                    'strakertranslations_easytranslationplatform/adminhtml_job_product_grid',
                    'strakertranslations_easytranslationplatform_job_product_grid',
                    [
                        'job_id' => $jobId
                    ]
                )
                ->setStatusId($statusId)
                ->toHtml()
        );
    }

    private function _getParamArray(){
        $data = $this->getRequest()->getParams();
        $data['attr'] = !empty($data['attr']) ? $data['attr'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_attr');
        $data['store'] = !empty($data['store']) ? $data['store'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_store');
        $data['product'] = !empty($data['product']) ? $data['product'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_product');
        return $data;
    }
}