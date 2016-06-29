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

            try {

                $id = $this->_storeManager->getStore()->getId();

                $this->_config->SaveConfig('straker/general/destination_store',$data['destination_store'],'stores',$id);

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