<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\LanguagePairs;

use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;

use Magento\Framework\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{

    public function __construct(
        Context $context,
        StrakerAPIInterface $strakerAPIInterface
    )
    {

        parent::__construct($context);

        $this->_StrakerAPI = $strakerAPIInterface;
    }


    public function execute()
    {

        echo '<pre>';
        print_r($this->getRequest()->getParams());
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