<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\CacheInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Controller\Result\Json;
use \Straker\EasyTranslationPlatform\Model\Setup;


class ResetAccount extends \Magento\Framework\App\Action\Action
{

    protected $_messageManager;
    protected $_storeManager;
    protected $_storeCache;
    protected $_resultJson;
    protected $_strakerSetup;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        CacheInterface $storeCache,
        Setup $strakerSetup
    )
    {
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_storeCache = $storeCache;
        $this->_resultJson = $resultJson;
        $this->_strakerSetup = $strakerSetup;

        return parent::__construct($context);
    }


    public function execute()
    {
        $result['Success'] = true;

        try {
            $this->_strakerSetup->saveAccessToken('');
            $this->_strakerSetup->saveAppKey('');

            $data = ['first_name' => '', 'last_name' => '', 'email' => '', 'url' => ''];
            $this->_strakerSetup->saveClientData( $data );

            foreach ($this->_storeManager->getStores($withDefault = false) as $store) {
                $this->_strakerSetup->saveStoreSetup($store->getId(),'','','');
            }

            $this->_storeCache->clean(\Magento\Framework\App\Config::CACHE_TAG);

            $this->_messageManager->addSuccess(__('Straker Settings has been cleared.'));

        } catch (Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            $result['Success'] = false;
        }

        return $this->_resultJson->setData($result);
    }

}