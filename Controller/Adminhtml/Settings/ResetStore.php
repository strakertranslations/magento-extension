<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\CacheInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Controller\Result\Json;
use \Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use \Straker\EasyTranslationPlatform\Model\Setup;


class ResetStore extends \Magento\Framework\App\Action\Action
{

    protected $_messageManager;
    protected $_storeManager;
    protected $_storeCache;
    protected $_resultJson;
    protected $_configHelper;
    protected $_strakerSetup;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        CacheInterface $storeCache,
        ConfigHelper $configHelper,
        Setup $strakerSetup
    )
    {
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_storeCache = $storeCache;
        $this->_resultJson = $resultJson;
        $this->_configHelper = $configHelper;
        $this->_strakerSetup = $strakerSetup;

        return parent::__construct($context);
    }


    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');

        if( isset( $storeId ) && is_numeric( $storeId ) ){

            if($this->_configHelper->getStoreSetup( $storeId ) ){

                $this->_strakerSetup->saveStoreSetup($storeId, '', '', '');
                $this->_messageManager->addSuccess(__('Language settings has been reset.'));
                $this->_storeCache->clean(\Magento\Framework\App\Config::CACHE_TAG);
                return;
            }

        }

        $this->_messageManager->addError(__('Store code is not valid.'));
        return;
    }

}