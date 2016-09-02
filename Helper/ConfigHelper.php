<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;

class ConfigHelper extends AbstractHelper
{
    protected $_scopeFactory;
    protected $_directoryList;

    public function __construct(
        Context $context,
        ScopedFactory $scopedFactory,
        DirectoryList $directoryList
    )
    {
        $this->_scopeFactory = $scopedFactory;
        $this->_directoryList = $directoryList;
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

    public function getApplicationKey()
    {
        return $this->scopeConfig->getValue('straker/general/application_key','default','') ? $this->scopeConfig->getValue('straker/general/application_key','default','') : false ;
    }

    /**
     * @return string or null  current version of the website hard-coded in config.xml
     */
    public function getVersion()
    {
        return $this->scopeConfig->getValue('straker/general/version');
    }

    /**
     * @param string $domain
     * @return string
     */
    protected function _getSiteDomain( $domain = '' ){
        $siteVersion = $this->getVersion();
        if( empty($siteVersion) ){
            $siteVersion = 'live';
        }
        $siteDomain = $this->scopeConfig->getValue('straker/general/'. (empty($domain) ? 'domain' : 'my_account_domain') .'/'. $siteVersion);
        if( empty($siteDomain) ){
            $siteDomain = 'https://app.strakertranslations.com';
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
        return $this->_getSiteDomain( 'my_account_domain' ).'/'.$this->scopeConfig->getValue('straker/general/api_url/payment_page');
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
        $return = $this->scopeConfig->getValue('straker_attribute/settings/default');
        return  empty( $return ) ? [] : explode(',', $return);
    }

    public function getCustomAttributes(){
        $return = $this->scopeConfig->getValue('straker_attribute/settings/custom');
        return  empty( $return ) ? [] : explode(',', $return);
    }

    public function getCategoryAttributes(){
        $return =  $this->scopeConfig->getValue('straker_attribute/settings/category');
        return  empty( $return ) ? [] : explode(',', $return);
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
//        var_dump( $dbStoreConfig);exit();
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

    public function getOriginalXMLFilePath(){
        return $this->getDataFilePath().DIRECTORY_SEPARATOR.'Original';
    }

    public function getTranslatedXMLFilePath(){
        return $this->getDataFilePath().DIRECTORY_SEPARATOR.'Translated';
    }

    public function getDataFilePath(){
        return $this->_directoryList->getPath('app').DIRECTORY_SEPARATOR.'code/Straker/EasyTranslationPlatform/Api/Data';
    }

}