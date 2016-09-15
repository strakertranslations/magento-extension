<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use \Exception;
use Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\CacheInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Controller\Result\Json;
use \Straker\EasyTranslationPlatform\Model\Setup;
use \Straker\EasyTranslationPlatform\Logger\Logger;


class ResetAccount extends Action
{

    protected $_messageManager;
    protected $_storeManager;
    protected $_storeCache;
    protected $_resultJson;
    protected $_strakerSetup;
    protected $_logger;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        CacheInterface $storeCache,
        Setup $strakerSetup,
        Logger $logger
    )
    {
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_storeCache = $storeCache;
        $this->_resultJson = $resultJson;
        $this->_strakerSetup = $strakerSetup;
        $this->_logger = $logger;

        return parent::__construct($context);
    }


    public function execute()
    {
        $result['Success'] = true;

        try {
            $this->_strakerSetup->saveAccessToken('');
            $this->_strakerSetup->saveAppKey('');
            $this->_strakerSetup->deleteSandboxSetting();

            //clear translations for all stores
            $this->_strakerSetup->clearTranslations();
            //clear all translation jobs
            $this->_strakerSetup->clearStrakerData();

            $data = ['first_name' => '', 'last_name' => '', 'email' => '', 'url' => ''];
            $this->_strakerSetup->saveClientData( $data );

            foreach ($this->_storeManager->getStores() as $store) {
                $this->_strakerSetup->saveStoreSetup($store->getId(),'','','');
            }

            $this->_storeCache->clean(Config::CACHE_TAG);
            $this->_messageManager->addSuccess( __('Straker Settings has been cleared.'));
            $this->_logger->info( __('Straker Settings has been cleared.') );

        } catch (Exception $e) {
            $message = __( $e->getMessage() );
            $this->_messageManager->addError( $message );
            $this->_logger->error( $message );
            $result['Success'] = false;
        }

        return $this->_resultJson->setData( $result );
    }
}