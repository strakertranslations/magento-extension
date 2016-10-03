<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\App\CacheInterface;
use \Magento\Framework\Controller\Result\Json;
use \Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use \Straker\EasyTranslationPlatform\Model\Setup;
use \Straker\EasyTranslationPlatform\Logger\Logger;


class ResetStore extends Action
{

    protected $_messageManager;
    protected $_storeCache;
    protected $_resultJson;
    protected $_configHelper;
    protected $_strakerSetup;
    protected $_logger;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        ManagerInterface $messageManager,
        CacheInterface $storeCache,
        ConfigHelper $configHelper,
        Setup $strakerSetup,
        Logger $logger
    )
    {
        $this->_messageManager = $messageManager;
        $this->_storeCache = $storeCache;
        $this->_resultJson = $resultJson;
        $this->_configHelper = $configHelper;
        $this->_strakerSetup = $strakerSetup;
        $this->_logger = $logger;

        return parent::__construct($context);
    }


    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');

        if( isset( $storeId ) && is_numeric( $storeId ) ){
            if($this->_configHelper->getStoreSetup( $storeId ) ){
                //remove all applied translations from database
                //$this->_strakerSetup->clearTranslations( $storeId );
                $this->_strakerSetup->saveStoreSetup($storeId, '', '', '');
                $message = __('Language settings has been reset.');
                $this->_messageManager->addSuccess( $message );
                $this->_logger->info( $message );
                $this->_storeCache->clean(Config::CACHE_TAG);
            }else{
                $message = __('There is a error in store configuration.');
                $this->_messageManager->addError( $message );
                $this->_logger->error( $message );
            }
        }else{
            $message = __('Store code is not valid.');
            $this->_messageManager->addError( $message );
            $this->_logger->error( $message );
        }

        return;
    }

}