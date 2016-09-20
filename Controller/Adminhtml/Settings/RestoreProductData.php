<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Helper\Data;


class RestoreProductData extends Action
{

    protected $_messageManager;
    protected $_resourceConnection;
    protected $_connection;
    protected $_logger;

    /**
     * @var \Straker\EasyTranslationPlatform\Helper\Data
     */
    protected $_dataHelper;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ManagerInterface $messageManager,
        Logger $logger,
        Data $dataHelper
    ) {
        $this->_messageManager = $messageManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_logger = $logger;
        $this->_dataHelper = $dataHelper;

        return parent::__construct($context);
    }

    private function _executeRestore()
    {

        $result = array('Success' => true, 'Message' => '');

        try {

            if (!isset($this->_connection)) {
                $this->_connection = $this->_resourceConnection->getConnection();
            }

            foreach ($this->_dataHelper->getMagentoDataTableArray() as $tableName) {
                $tableName = $this->_resourceConnection->getTableName($tableName);
                $backupTableName = $this->_dataHelper->getBackupTableNames($tableName);

                if ($this->_connection->isTableExists($tableName)
                    && $this->_connection->isTableExists($backupTableName)
                ) {

                    //generating sql statement of 'insert into ... select ...'
                    $sql = $this->_connection->insertFromSelect(
                        $this->_connection->select()
                            ->from($backupTableName),
                        $tableName
                    );

                    $return = $this->_connection->query($sql);

                    if ($return->errorCode() !== \Zend_Db::ERR_NONE) {
                        $result['Success'] = false;
                        $result['Message'] = join("|", $return->errorInfo());
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $result['Success'] = false;
            $result['Message'] = $e->getMessage();
            throw new Exception($result['Message']);
        }

        return $result;

    }


    public function execute()
    {
        try {

            if ($this->_hasBackupData()) {
                $this->_truncateMagentoDataTables();
            } else {
                $message = __('There is no backup data to restore.');
                $this->_logger->error($message);
                $this->_messageManager->addErrorMessage($message);
                return;
            }

            $result = $this->_executeRestore();

            if ($result['Success']) {
                $message = __('Translatable tables has been restored!');
                $this->_logger->info($message);
                $this->_messageManager->addSuccessMessage($message);
            } else {
                $message = __($result['Message']);
                $this->_logger->error($message);
                $this->_messageManager->addErrorMessage($message);
            }

        } catch (Exception $e) {
            $message = __($e->getMessage());
            $this->_logger->error($message);
            $this->messageManager->addErrorMessage($message);
        }

        return;
    }

    private function _hasBackupData()
    {

        $result = true;

        if (!isset($this->_connection)) {
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        foreach ($this->_dataHelper->getMagentoDataTableArray() as $tableName) {

            $backupTableName = $this->_dataHelper->getBackupTableNames( $this->_resourceConnection->getTableName( $tableName ) );

            if( !$this->_connection->isTableExists($backupTableName) ){
                $result = false;
                break;
            }

//            if ($this->_connection->isTableExists($backupTableName)) {
//                $sql = $this->_connection->select()
//                    ->from(
//                        $backupTableName,
//                        array('COUNT(*) AS RowCount')
//                    );
//
//                $rows = $this->_connection->fetchAll($sql);
//
//                if ($rows[0]['RowCount'] <= 0) {
//                    $result = false;
//                    break;
//                }
//            }
        }

        return $result;
    }

    private function _truncateMagentoDataTables()
    {
        if (!isset($this->_connection)) {
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        foreach ($this->_dataHelper->getMagentoDataTableArray() as $rawTableName) {
            if( strcasecmp($rawTableName, 'cms_page') === 0  ||
                strcasecmp($rawTableName, 'cms_block') === 0 ||
                strcasecmp($rawTableName, 'catalog_url_rewrite_product_category') === 0
            ){
                continue;
            }
            $tableName = $this->_resourceConnection->getTableName($rawTableName);
            if ($this->_connection->isTableExists($tableName)) {
                $foreignKeys = $this->_connection->getForeignKeys($tableName);
                foreach ($foreignKeys as $foreignKey) {
                    $this->_connection->dropForeignKey($tableName, $foreignKey['FK_NAME']);
                }

                switch ($rawTableName){
                    case 'cms_page_store':
                        $this->_connection->truncateTable($tableName);
                        $this->_connection->truncateTable($this->_resourceConnection->getTableName('cms_page'));
                        break;
                    case 'cms_block_store':
                        $this->_connection->truncateTable($tableName);
                        $this->_connection->truncateTable($this->_resourceConnection->getTableName('cms_block'));
                        break;
                    case 'url_rewrite':
                        $foreignTable = $this->_resourceConnection->getTableName('catalog_url_rewrite_product_category');
                        $urlForeignKeys = $this->_connection->getForeignKeys($foreignTable);
                        foreach ($urlForeignKeys as $urlForeignKey){
                            $this->_connection->dropForeignKey($foreignTable, $urlForeignKey['FK_NAME']);
                        }
                        $this->_connection->truncateTable($tableName);
                        $this->_connection->truncateTable($foreignTable);
                        foreach ($urlForeignKeys as $urlForeignKey){
                            $this->_connection->addForeignKey(
                                $urlForeignKey['FK_NAME'],
                                $urlForeignKey['TABLE_NAME'],
                                $urlForeignKey['COLUMN_NAME'],
                                $urlForeignKey['REF_TABLE_NAME'],
                                $urlForeignKey['REF_COLUMN_NAME'],
                                $urlForeignKey['ON_DELETE'],
                                false,
                                $urlForeignKey['SCHEMA_NAME'],
                                $urlForeignKey['REF_SHEMA_NAME']
                            );
                        }
                        break;
                    default:
                        $this->_connection->truncateTable($tableName);
                        break;
                }

                foreach ($foreignKeys as $foreignKey) {
                    $this->_connection->addForeignKey(
                        $foreignKey['FK_NAME'],
                        $foreignKey['TABLE_NAME'],
                        $foreignKey['COLUMN_NAME'],
                        $foreignKey['REF_TABLE_NAME'],
                        $foreignKey['REF_COLUMN_NAME'],
                        $foreignKey['ON_DELETE'],
                        false,
                        $foreignKey['SCHEMA_NAME'],
                        $foreignKey['REF_SHEMA_NAME']
                    );
                }
            }
        }
    }

}