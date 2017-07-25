<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_CategoryController extends Mage_Adminhtml_Controller_Action{

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
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_title($this->__('Create New Job'));
        return $this;
    }

    public function newAction(){
        $params = $this->getRequest()->getParams();
        if (empty($params['store'])) {
            $this->_redirect('*/straker_new');
        }
        elseif (empty($params['attr'])) {
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_category_attribute', 'strakertranslations_easytranslationplatform_new_categories_attribute', array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
        else{
            $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_category_tree', 'strakertranslations_easytranslationplatform_new_category_tree', array('setup_store_id' => $params['store'], 'attr' => $params['attr'])))
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

    public function confirmCategoryAction(){
        $data = $this->_getParamArray();
        //format category
//        $categoryIds = array_filter(array_unique(explode(',', $data['category'])));
        $categoryIds = empty($data['category']) ? [] : array_unique(explode(',', $data['category']));
        if(!empty($data['attr']) && !empty($data['store']) && !empty($categoryIds)){
            Mage::getSingleton('adminhtml/session')
                ->setData('straker_new_attr', $data['attr'])
                ->setData('straker_new_store', $data['store'])
                ->setData('straker_new_category', $categoryIds)
            ;
            return $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_category_confirm', 'strakertranslations_easytranslationplatform_new_category_confirm', array('store' => $data['store'], 'attr' => $data['attr'], 'category' => $categoryIds)))
                ->renderLayout();
        }
        else{
            $this->_redirect('*/*/new', array('attr'=>$data['attr'],'store'=>$data['store']));
        }
    }

    public function submitJobAction()
    {
        $data = $this->getRequest()->getParams();
        if($data['attr'] && $data['store'] && $data['category']){
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
            foreach( $attributeCodes as $attributeCode){
                $attr_ids[] = $attribute = Mage::getSingleton('eav/config')
                  ->getAttribute(Mage_Catalog_Model_Category::ENTITY, $attributeCode)->getAttributeId();
            }
            $helper = Mage::helper('strakertranslations_easytranslationplatform');
            $storeSetup = $helper->getStoreSetup($data['store']);
            $jobModel->setStoreId($data['store']);
            $jobModel->setSl($storeSetup['from']);
            $jobModel->setTl($storeSetup['to']);
            $jobModel->setToken('Token');
            $jobModel->submitCategories($attr_ids, explode(',',$data['category']));
            if ($jobModel->getLastStatus()) {
                Mage::getSingleton('adminhtml/session')
                    ->setData('straker_new_attr', '')
                    ->setData('straker_new_store', '')
                    ->setData('straker_new_category', '')
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

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_category_tree')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
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

    public function publishAllAction(){
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
        $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($jobId);

        if (!$job->getId()){
            Mage::getSingleton('adminhtml/session')->addError('Job ID missing');
            $this->_redirect('*/straker_job/');
            return;
        }
        $categoryIds = $this->getRequest()->getParam('category');
        if(!empty($categoryIds)) {
            if ($job->applyTranslation($categoryIds)) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been applied.');
                $this->_redirect('*/straker_category/',array('job_id' => $jobId));
                return;
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Error when applying translation.');
                $this->_redirect('*/straker_category/',array('job_id' => $jobId));
                return;
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError('Please select categorys to apply the translation.');
            $this->_redirect('*/straker_category/',array('job_id' => $jobId));
            return;
        }

    }

    private function checkSiteMode(){
        /** @var $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $helper->checkSiteMode();
    }

    public function confirmGridAction(){
        $data = $this->_getParamArray();
        $categoryIds = array_unique($data['category']);
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this
                ->getLayout()
                ->createBlock(
                    'strakertranslations_easytranslationplatform/adminhtml_new_category_confirm_grid',
                    'strakertranslations_easytranslationplatform_new_category_confirm_grid',
                    [
                        'store' => $data['store'], 'attr' => $data['attr'], 'category' => $categoryIds
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
                    'strakertranslations_easytranslationplatform/adminhtml_job_category_grid',
                    'strakertranslations_easytranslationplatform_job_category_grid',
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
        $data['category'] = !empty($data['category']) ? trim($data['category'],',') : Mage::getSingleton('adminhtml/session')->getData('straker_new_category');
        return $data;
    }

    public function removeFromCartAction(){
        $entityId = $this->getRequest()->getParam('entity_id');
        if(!empty($entityId)){
            $entityId = $this->getRequest()->getParam('entity_id');
        }
        $categoryIds = Mage::getSingleton('adminhtml/session')->getData('straker_new_category');
        if(!is_array($categoryIds)){
            $categoryIds = explode(',', trim($categoryIds, ','));
        }
        if( ($key =  array_search($entityId, $categoryIds)) !== false ){
            unset($categoryIds[$key]);
        }
        if(is_array($categoryIds)){
            $categoryIds = implode(',', $categoryIds);
        }
        Mage::getSingleton('adminhtml/session')->setData('straker_new_category', $categoryIds);
        $this->_redirect('*/*/confirmCategory');
    }

}