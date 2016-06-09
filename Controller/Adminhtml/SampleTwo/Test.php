<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\SampleTwo;

use \GuzzleHttp\Client;
use Magento\Store\Model\StoreManagerInterface;
use Straker\EasyTranslationPlatform\Api\Data\EasyTranslationPlatformInterface;
use Magento\Framework\App\Action\Context;

class Test extends \Magento\Framework\App\Action\Action
{

    protected $_storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        EasyTranslationPlatformInterface $easyTranslationPlatformInterface
    )
    {
        $this->_easyTranslationModel = $easyTranslationPlatformInterface;
        $this->_storeManager = $storeManagerInterface;
        return parent::__construct($context);
    }

    public function execute()
    {
        $a = [];

        foreach($this->_easyTranslationModel->getLocaleOptions() as $key => $value)
        {
            $a[$value['value']] = $value['label'];
        }

        echo '<pre>';
        print_r($a);
        echo '</pre>';
        exit;
    }
}