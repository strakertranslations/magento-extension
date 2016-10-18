<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Rollback extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::backup';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Straker\EasyTranslationPlatform\Model\DbFactory
     */
    protected $_dbFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Straker\EasyTranslationPlatform\Model\BackupFactory
     */
    protected $_backupModelFactory;

    /**
     * @var \Magento\Framework\App\MaintenanceMode
     */
    protected $maintenanceMode;


    /**
     * Rollback constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Straker\EasyTranslationPlatform\Model\DbFactory $dbFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Straker\EasyTranslationPlatform\Model\BackupFactory $backupModelFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Straker\EasyTranslationPlatform\Model\DbFactory $dbFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Straker\EasyTranslationPlatform\Model\BackupFactory $backupModelFactory,
        \Magento\Framework\App\MaintenanceMode $maintenanceMode
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_dbFactory = $dbFactory;
        $this->_fileFactory = $fileFactory;
        $this->_backupModelFactory = $backupModelFactory;
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct($context);
    }
    /**
     * Rollback Action
     *
     * @return void|\Magento\Backend\App\Action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if (!$this->_objectManager->get('Magento\Backup\Helper\Data')->isRollbackAllowed()) {
            $this->_forward('denied');
        }

        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('adminhtml/system_config/edit',  ['section' => 'demonstration']);
        }

        /** @var \Magento\Backup\Helper\Data $helper */
        $helper = $this->_objectManager->get('Magento\Backup\Helper\Data');
        $response = new \Magento\Framework\DataObject();

        try {
            /* @var $backup \Magento\Backup\Model\Backup */
            $backup = $this->_backupModelFactory->create(
                $this->getRequest()->getParam('time'),
                $this->getRequest()->getParam('type')
            );

            if (!$backup->getTime() || !$backup->exists()) {
                return $this->_redirect('adminhtml/system_config/edit',  ['section' => 'demonstration']);
            }

            if (!$backup->getTime()) {
                throw new \Magento\Framework\Backup\Exception\CantLoadSnapshot(__('Can\'t load snapshot archive'));
            }

            $type = $backup->getType();

            $backupManager = $this->_dbFactory->create(
                $type
            )->setBackupExtension(
                $helper->getExtensionByType($type)
            )->setTime(
                $backup->getTime()
            )->setBackupsDir(
                $helper->getBackupsDir()
            )->setName(
                $backup->getName(),
                false
            )->setResourceModel(
                $this->_objectManager->create('Magento\Backup\Model\ResourceModel\Db')
            );

            $this->_coreRegistry->register('backup_manager', $backupManager);

            $passwordValid = $this->_objectManager->create(
                'Magento\Backup\Model\Backup'
            )->validateUserPassword(
                $this->getRequest()->getParam('password')
            );

            if (!$passwordValid) {
                $response->setError(__('Please correct the password.'));
                $backupManager->setErrorMessage(__('Please correct the password.'));
                return $this->getResponse()->representJson($response->toJson());
            }

            if ($this->getRequest()->getParam('maintenance_mode')) {
                if (!$this->maintenanceMode->set(true)) {
                    $response->setError(
                        __(
                            'You need more permissions to activate maintenance mode right now.'
                        ) . ' ' . __(
                            'To complete the rollback, please deselect '
                            . '"Put store into maintenance mode" or update your permissions.'
                        )
                    );
                    $backupManager->setErrorMessage(
                        __('Something went wrong while putting your store into maintenance mode.')
                    );
                    return $this->getResponse()->representJson($response->toJson());
                }
            }

            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                /** @var Filesystem $filesystem */
                $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
                $backupManager->setRootDir($filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath())
                    ->addIgnorePaths($helper->getRollbackIgnorePaths());

                if ($this->getRequest()->getParam('use_ftp', false)) {
                    $backupManager->setUseFtp(
                        $this->getRequest()->getParam('ftp_host', ''),
                        $this->getRequest()->getParam('ftp_user', ''),
                        $this->getRequest()->getParam('ftp_pass', ''),
                        $this->getRequest()->getParam('ftp_path', '')
                    );
                }
            }

            $backupManager->rollback();

            $helper->invalidateCache();

            $adminSession = $this->_getSession();
            $adminSession->destroy();

            $response->setRedirectUrl($this->getUrl('*'));
        } catch (\Magento\Framework\Backup\Exception\CantLoadSnapshot $e) {
            $errorMsg = __('We can\'t find the backup file.');
        } catch (\Magento\Framework\Backup\Exception\FtpConnectionFailed $e) {
            $errorMsg = __('We can\'t connect to the FTP right now.');
        } catch (\Magento\Framework\Backup\Exception\FtpValidationFailed $e) {
            $errorMsg = __('Failed to validate FTP.');
        } catch (\Magento\Framework\Backup\Exception\NotEnoughPermissions $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info($e->getMessage());
            $errorMsg = __('You need more permissions to perform a rollback.');
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info($e->getMessage());
            $errorMsg = __('Failed to rollback.');
        }

        if (!empty($errorMsg)) {
            $response->setError($errorMsg);
            $backupManager->setErrorMessage($errorMsg);
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $this->maintenanceMode->set(false);
        }

        $this->getResponse()->representJson($response->toJson());
    }
}