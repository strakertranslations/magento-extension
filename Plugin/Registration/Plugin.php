<?php

namespace Straker\EasyTranslationPlatform\Plugin\Registration;

use Straker\EasyTranslationPlatform\Controller\Adminhtml\Jobs\NewAction;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Backend\Model\View\Factory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config;
use Closure;


class Plugin
{
    public function __construct(
        ConfigHelper $configHelper,
        UrlInterface $url,
        CacheInterface $cache
    ) {
        $this->_configHelper = $configHelper;
        $this->_url = $url;
        $this->_cache = $cache;
    }

    public function aroundDispatch(
        NewAction $subject,
        Closure $proceed,
        RequestInterface $request
    )
    {

        $this->_cache->clean(Config::CACHE_TAG);

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