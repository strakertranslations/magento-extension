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
}