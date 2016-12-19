<?php

namespace Straker\EasyTranslationPlatform\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;

class ConfigHelper extends AbstractHelper
{
    protected $_scopeFactory;
    protected $_directoryList;
    protected $_urlFactory;
    protected $_productMetadata;
    protected $_deployConfig;

    public function __construct(
        Context $context,
        ScopedFactory $scopedFactory,
        UrlFactory $urlFactory,
        DirectoryList $directoryList,
        ProductMetadataInterface $productMetadata,
        DeploymentConfig $deployConfig
    ) {

        $this->_scopeFactory = $scopedFactory;
        $this->_directoryList = $directoryList;
        $this->_urlFactory = $urlFactory;
        $this->_productMetadata = $productMetadata;
        $this->_deployConfig = $deployConfig;
        parent::__construct($context);
    }

    public function getConfig($req)
    {
        return $this->scopeConfig->getValue('straker' . $req);
    }

    public function getAccessToken()
    {
        return $this->scopeConfig->getValue('straker/general/access_token', 'default', '') ? $this->scopeConfig->getValue('straker/general/access_token', 'default', '') : false ;
    }

    public function getApplicationKey()
    {
        return $this->scopeConfig->getValue('straker/general/application_key', 'default', '') ? $this->scopeConfig->getValue('straker/general/application_key', 'default', '') : false ;
    }

    /**
     * @return string or null  current version of the website hard-coded in config.xml
     */
    public function getVersion()
    {
        return $this->scopeConfig->getValue('straker/general/version');
    }

    /**
     * @param string $domain: domain ('' or 'my_account_domain')
     * @param string $version: site verison (live, uat and dev)
     * @return mixed|string
     */
    protected function _getSiteDomain($domain = '', $version = '')
    {
        $siteVersion = empty($version) ? $this->getVersion() : $version;
        if (empty($siteVersion)) {
            $siteVersion = 'live';
        }

        if(strcasecmp($domain, 'my_account_domain') === 0){
            return $this->scopeConfig->getValue('straker/general/my_account_domain/'. $siteVersion);
        }

        if(!empty($version)){
            return $this->scopeConfig->getValue('straker/general/domain/' . $version);
        }

        if ($this->isSandboxMode()) {
            $siteDomain = $this->scopeConfig->getValue('straker/general/domain/sandbox');
        }else{
            $siteDomain = $this->scopeConfig->getValue('straker/general/domain/'. $siteVersion);
            if (empty($siteDomain)) {
                $siteDomain = 'https://app.strakertranslations.com';
            }
        }

        return rtrim($siteDomain, '/');
    }

    public function getRegisterUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/register');
    }

    public function getLanguagesUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/languages');
    }

    public function getTranslateUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/translate');
    }

    public function getQuoteUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/quote');
    }

    public function getPaymentUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/payment');
    }

    public function getCountriesUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/countries');
    }

    public function getSupportUrl()
    {
        return $this->_getSiteDomain().'/'.$this->scopeConfig->getValue('straker/general/api_url/support');
    }

    public function getPaymentPageUrl()
    {
        return $this->_getSiteDomain('my_account_domain').'/'.$this->scopeConfig->getValue('straker/general/api_url/payment_page');
    }

    public function getMyAccountUrl()
    {
        return $this->_getSiteDomain('my_account_domain').'/user/login';
    }

    public function getDbBackupUrl(){
        return $this->_getSiteDomain('', 'uat', 'backup') . '/' . $this->scopeConfig->getValue('straker/general/api_url/backup');
    }

    public function getDbRestoreUrl(){
        return $this->_getSiteDomain('', 'uat', 'restore') . '/' . $this->scopeConfig->getValue('straker/general/api_url/restore');
    }

    public function getStoreSetup($storeId)
    {
        $collection = $this->_scopeFactory->create(
            ['scope' => ScopeInterface::SCOPE_STORES, 'scopeId' => $storeId ]
        );

        $dbStoreConfig = [];
        foreach ($collection as $item) {
            $dbStoreConfig[$item->getPath()] = $item->getValue();
        }

        $source_store = array_key_exists('straker/general/source_store', $dbStoreConfig) ? $dbStoreConfig['straker/general/source_store'] : false;
        $source_language = array_key_exists('straker/general/source_language', $dbStoreConfig) ? $dbStoreConfig['straker/general/source_language'] :  false;
        $destination_language = array_key_exists('straker/general/destination_language', $dbStoreConfig) ? $dbStoreConfig['straker/general/destination_language'] :  false;

        return ($source_store && $source_language && $destination_language) ? true : false;
    }

    public function getDefaultAttributes()
    {
        $return = $this->scopeConfig->getValue('straker_config/attribute/product_default', 'default', 0);
        return  empty($return) ? [] : explode(',', $return);
    }

    public function getCustomAttributes()
    {
        $return = $this->scopeConfig->getValue('straker_config/attribute/product_custom', 'default', 0);
        return  empty($return) ? [] : explode(',', $return);
    }

    public function getCategoryAttributes()
    {
        $return =  $this->scopeConfig->getValue('straker_config/attribute/category', 'default', 0);
        return  empty($return) ? [] : explode(',', $return);
    }

    public function getStoreInfo($storeId)
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
    public function getStoreViewLanguage($storeId = null)
    {
        if (!empty($storeId)) {
            return $this->scopeConfig->getValue('straker/general/source_language', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->getValue('general/locale/code');
        }
    }

    public function getSourceStore($storeId = null)
    {
        return $this->scopeConfig->getValue('straker/general/source_store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getOriginalXMLFilePath()
    {
        return $this->getJobsFilePath().DIRECTORY_SEPARATOR.'original';
    }

    public function getTranslatedXMLFilePath()
    {
        return $this->getJobsFilePath().DIRECTORY_SEPARATOR.'translated';
    }

    public function getDataFilePath()
    {
        return $this->getStrakerPath(). DIRECTORY_SEPARATOR . 'data';
    }

    public function getJobsFilePath()
    {
        return $this->getStrakerPath().DIRECTORY_SEPARATOR.'jobs';
    }

    public function getStrakerPath()
    {
        return $this->_directoryList->getPath('var').DIRECTORY_SEPARATOR.'straker';
    }

    public function isSandboxMode()
    {
        return !$this->scopeConfig->getValue('straker_config/env/site_mode');
    }

    public function getSandboxMessage()
    {
        return
            '<p><b>' . __('Sandbox Mode Enabled') . '</b></p><p>'
            . __(
                'Thank you for installing our plugin. We have enabled the Sandbox testing mode for you. Jobs you create while this is enabled will not be received by Straker Translations, 
                and content will not be translated by a human - rather it will only be sample text. To change the Sandbox Mode, go to <a href="'
                . $this->_urlFactory->create()->getUrl('adminhtml/system_config/edit', ['section' => 'straker_config'])
                . '">Configuration</a>'
            )
            . '</p>';
    }

    public function getMagentoVersion(){
        return $this->_productMetadata->getVersion();
    }

    public function getTestingStoreViewCode()
    {
        return 'straker_testing_storeview';
    }

    public function getDbName(){
        return $this->_deployConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
            '/' . ConfigOptionsListConstants::KEY_NAME
        );
    }

    public function getCreateTestStoreViewMessage(){
        return
            '<p>'
            . __(
                'Cannot found testing store view, please <a href="'
                . $this->_urlFactory->create()->getUrl('adminhtml/system_config/edit', ['section' => 'straker_config'])
                . '">Create Testing Store View.</a>'
            )
            . '</p>';
    }
}
