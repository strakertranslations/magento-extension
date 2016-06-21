<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Language;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Locale\ListsInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        Config $config,
        StrakerAPIInterface $strakerAPIInterface,
        RequestInterface $requestInterface
    )
    {
        $this->_request = $requestInterface;
        $this->_config = $config;
        $this->_StrakerAPI = $strakerAPIInterface;

        parent::__construct($context);
    }


    public function execute()
    {
        echo '<pre>';
        print_r($this->_request->getParams('POST'));
        echo '</pre>';

        exit;

//        $oRegistration = $this->_StrakerAPI->callRegister($data);
//
//        $this->_StrakerAPI->saveAccessToken($oRegistration->access_token);
//
//        $this->_StrakerAPI->saveAppKey($oRegistration->application_key);
//
//        $url = $this->_url->getUrl("EasyTranslationPlatform/Store/Index/");
//
//        $resultRedirect = $this->resultRedirectFactory->create();
//
//        $resultRedirect->setUrl($url);
//
//        return $resultRedirect;
    }
}