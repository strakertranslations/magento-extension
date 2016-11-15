<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Exception;
use Magento\Backup\Controller\Adminhtml\Index;
use Magento\Framework\Backup\Exception\NotEnoughFreeSpace;
use Magento\Framework\Backup\Exception\NotEnoughPermissions;
use Magento\Framework\DataObject;

class CreateBackup extends Index
{
    /**
     * Create backup action
     *
     * @return void|\Magento\Backend\App\Action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('adminhtml/system_config/edit',  ['section' => 'demonstration']);
        }

        $response = new DataObject();

        /**
         * @var \Magento\Backup\Helper\Data $helper
         */
        $helper = $this->_objectManager->get('Magento\Backup\Helper\Data');

        $backupManager = null;

        try {
            $type = $this->getRequest()->getParam('type');

            $backupManager = $this->_backupFactory->create(
                $type
            )->setBackupExtension(
                $helper->getExtensionByType($type)
            )->setTime(
                time()
            )->setBackupsDir(
                $helper->getBackupsDir()
            );
//            $backupManager->setName('straker ' . $this->getRequest()->getParam('backup_name'));
            $backupManager->setName($this->getRequest()->getParam('backup_name'));
            $this->_coreRegistry->register('backup_manager', $backupManager);
            $successMessage = $helper->getCreateSuccessMessageByType($type);
            $backupManager->create();
            $this->messageManager->addSuccessMessage($successMessage);
            $response->setRedirectUrl($this->getUrl('adminhtml/system_config/edit', ['section' => 'demonstration']));
        } catch (NotEnoughFreeSpace $e) {
            $errorMessage = __('You need more free space to create a backup.');
        } catch (NotEnoughPermissions $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info($e->getMessage());
            $errorMessage = __('You need more permissions to create a backup.');
        } catch (Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info($e->getMessage());
            $errorMessage = __('We can\'t create the backup right now.');
        }

        if (!empty($errorMessage)) {
            $response->setError($errorMessage);
            $backupManager->setErrorMessage($errorMessage);
        }

        $this->getResponse()->representJson($response->toJson());
    }
}
