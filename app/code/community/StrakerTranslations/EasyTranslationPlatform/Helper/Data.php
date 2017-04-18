<?php
class StrakerTranslations_EasyTranslationPlatform_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getAppKey($store = null){
        return Mage::getStoreConfig('straker/general/application_key') ? Mage::getStoreConfig('straker/general/application_key') : false;
    }

    public function getAccessToken($store = null){
        return Mage::getStoreConfig('straker/general/access_token') ? Mage::getStoreConfig('straker/general/access_token') : false;
    }

    public function saveStoreSetup($storeId, $source, $from, $to){
        Mage::getModel('core/config')->saveConfig('straker/general/source', $source, 'stores', $storeId);
        Mage::getModel('core/config')->saveConfig('straker/general/from', $from, 'stores', $storeId);
        Mage::getModel('core/config')->saveConfig('straker/general/to', $to, 'stores', $storeId);
    }

    public function getStoreSetup($storeId){
        $result['source'] = Mage::getStoreConfig('straker/general/source', $storeId);
        $result['from'] = Mage::getStoreConfig('straker/general/from', $storeId);
        $result['to'] = Mage::getStoreConfig('straker/general/to', $storeId);
        return ($result['source'] && $result['from'] && $result['to']) ? $result : false;
    }

    public function renderNewJobHeading($storeId){
        $destinationStore = Mage::getModel('core/store')->load($storeId);
        $destinationText = $destinationStore->getFrontendName().' ('.$destinationStore->getName().')';

        $config = $this->getStoreSetup($storeId);
        $sourceStore = Mage::getModel('core/store')->load($config['source']);
        $sourceText = $sourceStore->getFrontendName().' ('.$sourceStore->getName().')';

        return $sourceText . ' > ' . $destinationText;
    }

    public function getCmsCreatedMessage(){
        $message = '';
        if (Mage::registry('cms_block')){
            $strakerCmsBlockModel = Mage::getModel('strakertranslations_easytranslationplatform/job_cms_block')->load(Mage::registry('cms_block')->getId(), 'new_entity_id');
            if($strakerCmsBlockModel->getId()) {
                $message = '<div id="straker-cms-title-message">Created by<span id="straker-icon">Straker Translations</span>';
                $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($strakerCmsBlockModel->getJobId());
                if ($job->getTl()) {
                    $message.= ', Target Language: '.$job->getTl();
                }
                $message .= '</div>';
            }
        }
        elseif(Mage::registry('cms_page')) {
            $strakerCmsPageModel = Mage::getModel('strakertranslations_easytranslationplatform/job_cms_page')->load(Mage::registry('cms_page')->getId(), 'new_entity_id');
            if($strakerCmsPageModel->getId()){
                $message = '<div id="straker-cms-title-message">Created by<span id="straker-icon">Straker Translations</span>';
                $job = Mage::getModel('strakertranslations_easytranslationplatform/job')->load($strakerCmsPageModel->getJobId());
                if ($job->getTl()) {
                    $message.= ', Target Language: '.$job->getTl();
                }
                $message .= '</div>';
            }
        }
        return $message;
    }

    public function isSandboxMode(){
//        return (Mage::getStoreConfig('straker/general/site_mode') === 0);
        return false;
    }

    public function getSandboxMessage(){
        return
            '<p>
                <h1>Sandbox Mode Enabled.</h1>
             </p>
             <p>
                Thank you for installing our plugin. We have enabled the Sandbox testing mode for you. Jobs you create while this is enabled
                will not be received by Straker Translations, and content will not be translated by a human - rather it will only be sample 
                text. To change the Sandbox Mode, go to 
                    <a href="'. Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', ['section' => 'straker']).'">Straker Configuration</a>
             </p>';
    }

    public function checkSiteMode(){
        if($this->isSandboxMode()){
            Mage::getSingleton('adminhtml/session')->addNotice($this->getSandboxMessage());
        }
    }

    public function clearSiteMode(){
        return Mage::getModel('core/config')->deleteConfig('straker/general/site_mode', 'default', 0);
    }

    public function getDataFilePath()
    {
        return $this->getStrakerPath(). DIRECTORY_SEPARATOR . 'data';
    }

    public function getStrakerPath()
    {
        return Mage::getBaseDir('var').DIRECTORY_SEPARATOR.'straker';
    }
}