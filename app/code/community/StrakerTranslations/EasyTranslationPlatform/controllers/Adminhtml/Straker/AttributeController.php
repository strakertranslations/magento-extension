<?php
Class StrakerTranslations_EasyTranslationPlatform_Adminhtml_Straker_AttributeController extends Mage_Adminhtml_Controller_Action{
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

    }

    public function newAction(){
        $params = $this->getRequest()->getParams();
        if (empty($params['store'])) {
            $this->_redirect('*/straker_new');
            return;
        }
        else{
            return $this->_initNewAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_attribute','strakertranslations_easytranslationplatform_new_attribute',array('setup_store_id' => $params['store'])))
                ->renderLayout();
        }
    }

    public function addtoconfirmAction(){
        $data = $this->getRequest()->getParams();
        if(empty($data['attribute']) && empty($data['option'])){
            $data['attribute'] =  Mage::getSingleton('adminhtml/session')->getData('straker_new_attribute');
            $data['option'] = Mage::getSingleton('adminhtml/session')->getData('straker_new_option');
        }
        $data['store'] = !empty($data['store']) ? $data['store'] : Mage::getSingleton('adminhtml/session')->getData('straker_new_store');

        if( $data['store'] && ($data['attribute'] || $data['option']) ){
            Mage::getSingleton('adminhtml/session')
                ->setData('straker_new_attribute', $data['attribute'])
                ->setData('straker_new_option', $data['option'])
                ->setData('straker_new_store', $data['store'])
            ;
            return $this->_initAction()
                ->_addContent(Mage::getSingleton('core/layout')->createBlock('strakertranslations_easytranslationplatform/adminhtml_new_attribute_confirm', 'strakertranslations_easytranslationplatform_new_attribute_confirm', array('store' => $data['store'], 'attribute' => $data['attribute']?$data['attribute']:array(), 'option' => $data['option']?$data['option']:array())))
                ->renderLayout();
        }
        else {
            $this->_redirect('*/straker_new/', $data);
        }
    }

    public function submitjobAction(){
        $data = $this->getRequest()->getParams();
        if( $data['store'] && isset($data['attribute']) && isset($data['option']) ){
            $attribute = !empty($data['attribute'])?explode(',', $data['attribute']):array();
            $option = !empty($data['option'])?explode(',', $data['option']):array();
            //prepare attribute array in nested
            foreach( array_unique( array_merge(
                $attribute,
                $option
            ) ) as $attributeId) {

                $attributeData[$attributeId]['label'] = in_array($attributeId, $attribute, true) ?  1 : 0;
                $attributeData[$attributeId]['option'] = in_array($attributeId, $option, true) ?  1 : 0;

            }





            //todo: insert model submit job from here
        //    var_dump($attributeData); die();

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
            $jobModel->setToken('Token');
            $jobModel->submitAttributes($attributeData);
            if ($jobModel->getLastStatus()) {
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
        $attributeIds = $this->getRequest()->getParam('attribute');
        if(!empty($attributeIds)) {
            if ($job->applyTranslation($attributeIds)) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Translation has been applied.');
                $this->_redirect('*/straker_attribute/',array('job_id' => $jobId));
                return;
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Error when applying translation.');
                $this->_redirect('*/straker_attribute/',array('job_id' => $jobId));
                return;
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError('Please select attributes to apply the translation.');
            $this->_redirect('*/straker_attribute/',array('job_id' => $jobId));
            return;
        }

    }

    private function checkSiteMode(){
        /** @var $helper StrakerTranslations_EasyTranslationPlatform_Helper_Data */
        $helper = Mage::helper('strakertranslations_easytranslationplatform');
        $helper->checkSiteMode();
    }

}