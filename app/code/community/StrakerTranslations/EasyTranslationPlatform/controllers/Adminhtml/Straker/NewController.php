<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_NewController extends Mage_Adminhtml_Controller_Action{
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

    public function accountAction(){
        $this->_redirectUrl("https://myaccount.strakertranslations.com/");
    }

    public function termsAction(){
        $this->_redirectUrl("https://www.strakertranslations.com/about-us/terms-and-conditions-of-service.cfm");
    }

    public function indexAction(){
        $params = $this->getRequest()->getParams();
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
        else{
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_type','strakertranslations_easytranslationplatform_new_type',array('setup_store_id' => $params['store'])))
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
                Mage::app()->getCacheInstance()->cleanType('config');
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
                    Mage::app()->getCacheInstance()->cleanType('config');
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
            Mage::app()->getCacheInstance()->cleanType('config');
            $session->addSuccess('Straker Settings has been cleared.');

        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('adminhtml/system_config/edit/section/straker');
        return;
    }

    public function copyProdcutTableAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        try {

            $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

            $sql = 'CREATE TABLE catalog_product_entity_varchar_back AS SELECT * FROM catalog_product_entity_varchar;';

            $writeConnection->query($sql);

            $sql = 'CREATE TABLE catalog_product_entity_text_back AS SELECT * FROM catalog_product_entity_text;';

            $writeConnection->query($sql);

            $sql = 'CREATE TABLE catalog_category_entity_varchar_back AS SELECT * FROM catalog_category_entity_varchar;';

            $writeConnection->query($sql);

            $sql = 'CREATE TABLE catalog_category_entity_text_back AS SELECT * FROM catalog_category_entity_text;';

            $writeConnection->query($sql);

            $session->addSuccess('product and category entity tables has been duplicated ');

        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('adminhtml/system_config/edit/section/straker');
        return;
    }

    public function restoreProdcutTableAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        try {

            $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

            if ($writeConnection->query("SELECT 1 FROM catalog_product_entity_varchar_back LIMIT 1")){

                $sql = 'DELETE FROM straker_job';
                $writeConnection->query($sql);

            $sql = 'TRUNCATE catalog_product_entity_varchar; INSERT INTO catalog_product_entity_varchar SELECT * FROM catalog_product_entity_varchar_back;';

            $writeConnection->query($sql);

            $sql = 'TRUNCATE catalog_product_entity_text; INSERT INTO catalog_product_entity_text SELECT * FROM catalog_product_entity_text_back;';

            $writeConnection->query($sql);

            $sql = 'TRUNCATE catalog_category_entity_varchar; INSERT INTO catalog_category_entity_varchar SELECT * FROM catalog_category_entity_varchar_back;';

            $writeConnection->query($sql);

            $sql = 'TRUNCATE catalog_category_entity_text; INSERT INTO catalog_category_entity_text SELECT * FROM catalog_category_entity_text_back;';

            $writeConnection->query($sql);

            $session->addSuccess('product and category entity tables has been restored ');
                }

        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
        $this->_redirect('adminhtml/system_config/edit/section/straker');
        return;
    }


    public function resetStoreSettingsAction(){
        $storeId = $this->getRequest()->getParam('store');
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $session = Mage::getSingleton('adminhtml/session');

        if($helper->getStoreSetup($storeId)){
            $helper->saveStoreSetup($storeId, '', '', '');
            $session->addSuccess($this->__('Language settings has been reset.'));
        }
        else{
            $session->addError($this->__('Store code is not valid.'));
        }
        $this->_redirect('adminhtml/system_config/edit/section/straker');
        return;
    }

}