<?php

namespace Straker\EasyTranslationPlatform\Helper;

class ConfigHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param  string param
     */
    public function getConfig($req)
    {
        return $this->scopeConfig->getValue('straker' . $req);
    }

    public function getAccessToken()
    {
        return $this->scopeConfig->getValue('straker/general/access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('straker/general/access_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : false ;
    }
}
