<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Setup\StoreLanguage;

use Magento\Backend\App\Action\Context;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Locale\ListsInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magento\Backend\App\Action
{

    public function __construct(
        Context $context,
        Config $config,
        StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->_config = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Straker_EasyTranslationPlatform::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {

            $websiteFactory = $this->_objectManager->get('\Magento\Store\Model\storeFactory');

            $new_store = $websiteFactory->create();

            $new_store->setName($data['name']);

            $new_store->setCode(strtolower(str_replace('_','',$data['general_locale_code'])));

            $new_store->setisActive('0');

            $new_store->setWebsiteId($this->_storeManager->getWebsite()->getId());

            $new_store->setGroupId($this->_storeManager->getGroup()->getId());

            try {

                $new_store->save();

                $this->_config->SaveConfig('general/locale/code',$data['general_locale_code'],'stores',$new_store->getId());

                $resultRedirect->setUrl($this->_url->getUrl("EasyTranslationPlatform/Setup_LanguagePairs/Index/"));

                return $resultRedirect;


            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addError($e->getMessage());

            } catch (\RuntimeException $e) {

                $this->messageManager->addError($e->getMessage());

            } catch (\Exception $e) {

                $this->messageManager->addException($e, __('Something went wrong while saving the store.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit');
        }

        return $resultRedirect->setPath('*/*/');
    }
}