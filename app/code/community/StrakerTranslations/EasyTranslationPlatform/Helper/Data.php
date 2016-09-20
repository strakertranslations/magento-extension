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
}