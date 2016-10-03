<?php

namespace Straker\EasyTranslationPlatform\Plugin\Registration;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\Controller\Result\RedirectFactory;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config;
use Closure;


class Plugin
{
    private $_redirectFactory;

    public function __construct(
        ConfigHelper $configHelper,
        UrlInterface $url,
        CacheInterface $cache,
        RedirectFactory $redirectFactory
    ) {
        $this->_configHelper = $configHelper;
        $this->_url = $url;
        $this->_cache = $cache;
        $this->_redirectFactory = $redirectFactory;
    }

    public function aroundDispatch(
        AbstractAction $subject,
        Closure $proceed,
        RequestInterface $request
    )
    {

        if(!$this->_configHelper->getAccessToken()){

            $resultRedirect = $this->_redirectFactory->create();

            $resultRedirect->setUrl($this->_url->getUrl("*/setup_registration/index/"));

            return $resultRedirect;

        }

        if(empty($this->_configHelper->getDefaultAttributes())){

            $resultRedirect = $this->_redirectFactory->create();

            $resultRedirect->setUrl($this->_url->getUrl("*/setup_productattributes/index/"));

            return $resultRedirect;
        }

        return $proceed($request);

    }
}