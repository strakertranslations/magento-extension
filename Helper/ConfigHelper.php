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
}
