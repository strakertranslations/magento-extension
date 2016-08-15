<?php

namespace Straker\EasyTranslationPlatform\Plugin\Registration;

use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Controller\Adminhtml\NewJob\Index;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\View\Factory;
use Closure;


class Plugin
{
    public function __construct(
        ConfigHelper $configHelper,
        UrlInterface $url
    ) {
        $this->_configHelper = $configHelper;
        $this->_url = $url;
    }

    public function aroundDispatch(
        Index $subject,
        Closure $proceed,
        RequestInterface $request
    )
    {

        if(!$this->_configHelper->getAccessToken()) {

            $url = $this->_url->getUrl("EasyTranslationPlatform/Setup_registration/Index/");

            $resultRedirect = $subject->resultRedirectFactory->create();

            $resultRedirect->setUrl($url);

            return $resultRedirect;

        }else{

            return $proceed($request);
        }
    }
}