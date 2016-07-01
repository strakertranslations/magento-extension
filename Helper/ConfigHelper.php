<?php

namespace Straker\EasyTranslationPlatform\Helper;

class ConfigHelper extends \Magento\Framework\App\Helper\AbstractHelper
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

}
