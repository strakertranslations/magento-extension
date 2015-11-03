<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_NewController extends Mage_Adminhtml_Controller_Action{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/straker/new');
    }

    protected function _initAction()
    {
        $this
            ->loadLayout()
            ->_setActiveMenu('straker/new')
        ;
        $this->_title($this->__('Create New Job'));
        return $this;
    }

    public function indexAction(){
        $params = $this->getRequest()->getParams();
var_dump($params);
        if (Mage::helper('strakertranslations_easytranslationplatform')->getAppKey() === false || Mage::helper('strakertranslations_easytranslationplatform')->getAccessToken() === false){
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_register'))
                ->renderLayout();
        }
        elseif (empty($params['store'])){
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_selectstore'))
                ->renderLayout();
        }
        elseif ( Mage::helper('strakertranslations_easytranslationplatform')->getStoreSetup($params['store']) === false
    ){
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_setupstore', 'strakertranslations_easytranslationplatform_new_setupstore', array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
        elseif (empty($params['attr'])) {
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_attribute','strakertranslations_easytranslationplatform_new_attribute',array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
        else{
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_products','strakertranslations_easytranslationplatform_new_products',array('setup_store_id' => $params['store'], 'attr' => $params['attr'])))
                ->renderLayout();
        }
    }

    public function registerAction(){

        $data = $this->getRequest()->getPost();
        if (array_key_exists('form_key', $data)){
            unset($data['form_key']);
        }
        if ($data['first_name'] && $data['last_name'] && $data['email'] && $data['terms']) {
            if ($data['company'] == ''){
                $data['company'] = $data['first_name'] .' '. $data['last_name'];
            }
            $apiModel = Mage::getModel('strakertranslations_easytranslationplatform/api');
            $response = $apiModel->callRegister($data);
            if($response->access_token && $response->application_key) {
                $apiModel->saveAccessToken($response->access_token);
                $apiModel->saveAppKey($response->application_key);
                Mage::getSingleton('adminhtml/session')->addSuccess('Registration success.');
            }
            elseif($response->magentoMessage){
                Mage::getSingleton('adminhtml/session')->addError($response->magentoMessage);
            }
            else{
                Mage::getSingleton('adminhtml/session')->addError('Registration unsuccessful.');
            }
        }
        $this->_redirect('*/*/');
    }

    public function selectstoreAction(){
        $store = $this->getRequest()->getParam('store');
        if($store){
            $this->_redirect('*/*/', array('store' => $store));
        }
        else {
            $this->_redirect('*/*/');
        }
    }

    public function setupstoreAction(){
        $data = $this->getRequest()->getParams();
        if ($data['store'] && $data['source'] && $data['from'] && $data['to']) {
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('adminhtml/session');
            try {
                //save the source, language from and to in stystem config
                $helper = Mage::helper('strakertranslations_easytranslationplatform');

                if ($helper->saveStoreSetup($data['store'], $data['source'], $data['from'], $data['to']) !== false ){
                    $this->_redirect('*/*/', array('store' => $data['store']));
                    return;
                }
                else {
                    Mage::throwException(Mage::helper('strakertranslations_easytranslationplatform')->__('Unable to save store configurations.'));
                }
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function attributeAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store']){
            $this->_redirect('*/*/', array('store' => $data['store'], 'attr' => implode(",",array_keys($data['attr']))));
        }
        else {
            $this->_redirect('*/*/');
        }
    }

    public function addtoconfirmAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store'] && $data['product']){
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_confirm', 'strakertranslations_easytranslationplatform_new_confirm', array('store' => $data['store'], 'attr' => $data['attr'], 'product' => $data['product'])))
                ->renderLayout();
        }
        else {
            $this->_redirect('*/*/', $data);
        }
    }

    public function submitjobAction(){
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store'] && $data['product']){
            //todo: refine attribute code and id procedure
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
            foreach(explode(',', $data['attr']) as $attributeCode){
                $attr_ids[] = $attribute = Mage::getSingleton('eav/config')
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode)->getAttributeId();
            }
            $helper = Mage::helper('strakertranslations_easytranslationplatform');
            $storeSetup = $helper->getStoreSetup($data['store']);
            $jobModel->setStoreId($data['store']);
            $jobModel->setSl($storeSetup['from']);
            $jobModel->setTl($storeSetup['to']);
            $jobModel->setTitle('Title');
            $jobModel->setToken('Token');
            $jobModel->submitProducts($attr_ids, explode(',',$data['product']));
            if ($jobModel->getLastStatus()) {
                Mage::getSingleton('adminhtml/session')->addSuccess('New job created');
                $this->_redirect('*/adminhtml_job/');
            }
            else{
                Mage::getSingleton('adminhtml/session')->addError($jobModel->getLastMessage());
                $this->_redirect('*/*/');
            }
        }
        else {
            $this->_redirect('*/*/', $data);
        }
    }

    public function clearSettingsAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        try {
            $apiModel = Mage::getModel('strakertranslations_easytranslationplatform/api');

            $apiModel->saveAccessToken('');
            $apiModel->saveAppKey('');

            $helper = Mage::helper('strakertranslations_easytranslationplatform');
            foreach (Mage::app()->getStores() as $store) {
                $helper->saveStoreSetup($store->getId(), '', '', '');
            }
            $session->addSuccess('Straker Settings has been cleared.');

        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('adminhtml/system_config/edit/section/straker');
        return;
    }
}
