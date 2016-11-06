<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\TestingStoreView;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Straker\EasyTranslationPlatform\Api\Data\StrakerAPIInterface;
use Straker\EasyTranslationPlatform\Api\Data\SetupInterface;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{

    protected $_config;
    protected $_reinitConfig;
    protected $_strakerAPI;
    protected $_setup;
    protected $_logger;
    protected $_configHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_storeModel;

    public function __construct(
        Context $context,
        Config $config,
        ReinitableConfigInterface $reinitableConfigInterface,
        StrakerAPIInterface $strakerAPIInterface,
        SetupInterface $setupInterface,
        Logger $logger,
        StoreFactory $storeFactory,
        StoreManagerInterface $storeManager,
        ConfigHelper $configHelper,
        GroupFactory $groupFactory
    ) {

        $this->_config = $config;
        $this->_reinitConfig = $reinitableConfigInterface;
        $this->_strakerAPI = $strakerAPIInterface;
        $this->_setup = $setupInterface;
        $this->_logger = $logger;
        $this->_storeModel = $storeFactory->create();
        $this->_storeManager = $storeManager;
        $this->_configHelper = $configHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        //if testing store exists redirect to new job page
        $testingStore = $this->_storeModel->load($this->_configHelper->getTestingStoreViewCode());
        if (!empty($testingStore->getId())) {
            $this->_setup->setSiteMode(SetupInterface::SITE_MODE_SANDBOX);
            $resultRedirect->setPath('*/Jobs/New');
            return $resultRedirect;
        }
        //create testing store
        $data = $this->getRequest()->getParams();
        if ($data) {
            try {
                if (key_exists('store_view_name', $data) && !empty($data['store_view_name'])) {
                    //create a store view
                    $this->_storeModel->setName($data['store_view_name']);
                    $this->_storeModel->setId(null);
                    $this->_storeModel->setIsActive(true);
                    $this->_storeModel->setCode($this->_configHelper->getTestingStoreViewCode());
                    $currentWebsite = $this->_storeManager->getWebsite();
                    $defaultGroupId = $currentWebsite->getDefaultGroupId();
                    $this->_storeModel->setStoreGroupId($defaultGroupId);
                    $this->_storeModel->setWebsiteId($currentWebsite->getId());
                    $this->_storeModel->save();
                    $this->_storeManager->reinitStores();
                    $this->_eventManager->dispatch('store_add', ['store' => $this->_storeModel]);
                    //switch on sandbox mode
                    $this->_setup->setSiteMode(SetupInterface::SITE_MODE_SANDBOX);
                } else {
                    //switch off sandbox mode
                    $this->_setup->setSiteMode(SetupInterface::SITE_MODE_LIVE);
                }
                $resultRedirect->setPath('*/Jobs/New');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_logger->error('error' . __FILE__ . ' ' . __LINE__ . '', [$e]);
                $this->messageManager->addSuccessMessage('There was an error registering your details');
            } catch (\RuntimeException $e) {
                $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, [$e]);
                $this->messageManager->addSuccessMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error('error' . __FILE__ . ' ' . __LINE__, [$e]);
                $this->messageManager->addExceptionMessage($e, __('There was an error registering your details'));
            }
        }
        $resultRedirect->setPath('/*/index/');
        return $resultRedirect;
    }
}
