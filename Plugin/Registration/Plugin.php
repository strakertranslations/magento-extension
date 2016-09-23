<?php

namespace Straker\EasyTranslationPlatform\Plugin\Registration;

use Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs\NewAction;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
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
        NewAction $subject,
        Closure $proceed,
        RequestInterface $request
    )
    {

        if(!$this->_configHelper->getAccessToken()){

            $resultRedirect = $subject->resultRedirectFactory->create();

            $resultRedirect->setUrl($this->_url->getUrl("*/setup_registration/index/"));

            return $resultRedirect;

        }

        if(empty($this->_configHelper->getDefaultAttributes())){

            $resultRedirect = $subject->resultRedirectFactory->create();

            $resultRedirect->setUrl($this->_url->getUrl("*/setup_productattributes/index/"));

            return $resultRedirect;
        }

        return $proceed($request);

    }
}