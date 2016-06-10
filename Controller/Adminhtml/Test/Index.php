<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Test;

use \GuzzleHttp\Client;
use Magento\Store\Model\StoreManagerInterface;
use Straker\EasyTranslationPlatform\Api\Data\EasyTranslationPlatformInterface;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        EasyTranslationPlatformInterface $easyTranslationPlatformInterface,
        StrakerAPIInterface $strakerAPIInterface
    )
    {
        $this->_easyTranslationModel = $easyTranslationPlatformInterface;
        $this->_storeManager = $storeManagerInterface;
        $this->_strakerAPI = $strakerAPIInterface;
        return parent::__construct($context);
    }

    public function execute()
    {
        $aCountries = [];

        foreach($this->_strakerAPI->getCountries() as $key => $value)
        {
            $aCountries[$value->code] = $value->name;
        }

        echo '<pre>';

       print_r($aCountries);

        echo '</pre>';

        exit;
    }
}