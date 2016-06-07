<?php

namespace Tym17\AdminSample\Controller\Adminhtml\Store;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Locale\ListsInterface;
use Magento\Config\Model\ResourceModel\Config;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        ListsInterface $listInterface,
        Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->_listInterface = $listInterface;
        $this->_config = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tym17_AdminSample::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {

            $websiteFactory = $this->_objectManager->get('\Magento\Store\Model\storeFactory');

            $new_store = $websiteFactory->create();

            $new_store->setName($data['name']);

            $new_store->setCode($data['code']);

            $new_store->setisActive($data['is_active']);

            $new_store->setWebsiteId($this->_storeManager->getStore()->getId());

            $new_store->setGroupId($this->_storeManager->getGroup()->getId());

            try {

                $new_store->save();

                $this->_config->SaveConfig('general/locale/code',$data['general_locale_code'],'stores',$new_store->getId());

                $this->messageManager->addSuccess(__('Store Saved.'));

                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                return $resultRedirect->setPath('*/*/');

            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addError($e->getMessage());

            } catch (\RuntimeException $e) {

                $this->messageManager->addError($e->getMessage());

            } catch (\Exception $e) {

                $this->messageManager->addException($e, __('Something went wrong while saving the store.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit', ['post_id' => $this->getRequest()->getParam('post_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}