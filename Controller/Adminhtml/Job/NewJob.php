<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Job;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\Controller\Result\Json;
use \Straker\EasyTranslationPlatform\Model\StrakerAPI;
use \Straker\EasyTranslationPlatform\Model\Setup;


class NewJob extends \Magento\Framework\App\Action\Action
{

    protected $_messageManager;
    protected $_resultJson;
    protected $_strakerAPI;
    protected $_strakerSetup;

    public $resultRedirectFactory;

    public function __construct(
        Context $context,
        Json $resultJson,
        ManagerInterface $messageManager,
        StrakerAPI $strakerAPI,
        Setup $strakerSetup
    )
    {
        $this->_messageManager = $messageManager;
        $this->_resultJson = $resultJson;
        $this->_strakerAPI = $strakerAPI;
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

            /** @var \Magento\Framework\App\ObjectManager $objManager */
            $objManager = ObjectManager::getInstance();

            /** @var \Magento\Store\Model\StoreManagerInterface|\Magento\Store\Model\StoreManager $storeManager */
            $storeManager = $objManager->get('Magento\Store\Model\StoreManagerInterface');

            foreach ($storeManager->getStores($withDefault = false) as $store) {
                $this->_strakerSetup->saveStoreSetup($store->getId(),'','','','');
            }

            /** @var \Magento\Framework\App\CacheInterface $cache */
            $cache = $objManager->get('Magento\Framework\App\CacheInterface');

            $cache->clean(\Magento\Framework\App\Config::CACHE_TAG);

            $this->_messageManager->addSuccess(__('Straker Settings has been cleared.'));

        } catch (Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            $result['Success'] = false;
        }

        return $this->_resultJson->setData($result);
    }

}