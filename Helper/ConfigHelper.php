<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class ConfigHelper extends AbstractHelper
{

    public function getConfig($req)
    {
        return $this->scopeConfig->getValue('straker' . $req);
    }

    public function getAccessToken()
    {
        return $this->scopeConfig->getValue('straker/general/access_token','default','') ? $this->scopeConfig->getValue('straker/general/access_token','default','') : false ;
    }

    /**
     * @return string or null  current version of the website hard-coded in config.xml
     */
    public function getVersion()
    {
        return $this->scopeConfig->getValue('straker/general/version');
    }

    /**
     * @param $version string param, value would be one of 'dev', 'uat', 'demo' and 'live'
     * @return string or null  a domain of the website.
     */
    protected function _getSiteDomain( ){
        $siteVersion = $this->getVersion();
        if(trim($siteVersion) === ''){
            $siteVersion = 'live';
        }
        $siteDomain = $this->scopeConfig->getValue('straker/general/domain/'. $siteVersion);
        if(trim($siteDomain) === '' ){
            $siteDomain = 'https://live-app.strakertranslations.com';
        }
        return rtrim($siteDomain,'/');
    }

    public function getRegisterUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/register');
    }

    public function getLanguagesUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/languages');
    }

    public function getTranslateUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/translate');
    }

    public function getQuoteUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/quote');
    }

    public function getPaymentUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/payment');
    }

    public function getCountriesUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/countries');
    }

    public function getSupportUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/support');
    }

    public function getPaymentPageUrl(){
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/payment_page');
    }

    public function getStoreSetup( $storeId ){
        $objManager = ObjectManager::getInstance();
        $collectionFactory = $objManager->get('\Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory');

        $collection = $collectionFactory->create(
            ['scope' => ScopeInterface::SCOPE_STORES, 'scopeId' => $storeId ]
        );

        $dbStoreConfig = [];
        foreach ($collection as $item) {
            $dbStoreConfig[$item->getPath()] = $item->getValue();
        }

        $source_store = array_key_exists('straker/general/source_store',$dbStoreConfig ) ? $dbStoreConfig['straker/general/source_store'] : false;
        $source_language = array_key_exists('straker/general/source_language',$dbStoreConfig ) ? $dbStoreConfig['straker/general/source_language'] :  false;
        $destination_language = array_key_exists('straker/general/destination_language',$dbStoreConfig ) ? $dbStoreConfig['straker/general/destination_language'] :  false;

        return ($source_store && $source_language && $destination_language) ? true : false;

    }

}