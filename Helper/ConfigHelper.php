<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;

class ConfigHelper extends AbstractHelper
{
    protected $_scopeFactory;

    public function __construct(
        Context $context,
        ScopedFactory $scopedFactory
    )
    {
        $this->_scopeFactory = $scopedFactory;
        parent::__construct( $context );
    }

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
        $collection = $this->_scopeFactory->create(
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
    
    public function getDefaultAttributes(){
        return  explode(',', $this->scopeConfig->getValue('straker/attributes/default'));
    }

    public function getCustomAttributes(){
        return  explode(',', $this->scopeConfig->getValue('straker/attributes/custom'));
    }

    public function getStoreInfo( $storeId )
    {
        $collection = $this->_scopeFactory->create(
            ['scope' => ScopeInterface::SCOPE_STORES, 'scopeId' => $storeId]
        );

        $dbStoreConfig = [];

        foreach ($collection as $item) {
            $dbStoreConfig[$item->getPath()] = $item->getValue();
        }

        return $dbStoreConfig;

    }

    /**
     * retrieve language code for the given store, or the default language code if the store id is not provide
     * @param $storeId
     * @return mixed
     */
    public function getStoreViewLanguage( $storeId = null )
    {
        if(!empty( $storeId ) ){

            return $this->scopeConfig->getValue('straker/general/source_language', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId );

        }else{

            return $this->scopeConfig->getValue('general/locale/code' );
        }
    }

    public function getSourceStore($storeId = null)
    {
        return $this->scopeConfig->getValue('straker/general/source_store',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
    }

}