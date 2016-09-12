<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Straker\EasyTranslationPlatform\Helper\ConfigHelper;
use Straker\EasyTranslationPlatform\Logger\Logger;
use Straker\EasyTranslationPlatform\Helper\Data;

class BackupProductData extends Action
{

    protected $_messageManager;
    protected $_resourceConnection;
    protected $_connection;
    protected $_logger;
    protected $_dataHelper;
    protected $_configHelper;
    protected $_config;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ManagerInterface $messageManager,
        Logger $logger,
        Data $dataHelper,
        ConfigHelper $configHelper,
        DeploymentConfig $config
    )
    {
        $this->_messageManager = $messageManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_logger = $logger;
        $this->_dataHelper = $dataHelper;
        $this->_configHelper = $configHelper;
        $this->_config = $config;
        return parent::__construct($context);
    }

    private function _executeBackup(){

        $result = [ 'Success' => false, 'Message' => ''];
        $isNoError = true;

        if( !isset($this->_connection) ){
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        try{
            foreach ($this->_dataHelper->getMagentoDataTableArray() as $productTableName ){
                $memTable = '';

                $productTableName = $this->_connection->getTableName($productTableName);
                if( $this->_connection->isTableExists( $productTableName )){
                    $backupTableName = $this->_dataHelper->getBackupTableNames( $productTableName );

                    //if new table exists with data, it should be truncated. Otherwise, create it.
                    if( $this->_connection->isTableExists( $backupTableName )){
                        $this->_connection->truncateTable( $backupTableName );
                    }else{
                        //create a Table instance in memory, the structure is same as product table
                        $memTable = $this->_connection->createTableByDdl($productTableName, $backupTableName);
                        //create table in database and return a boolean value by comparing with the no error code
                        $isNoError = ( $this->_connection->createTable($memTable)->errorCode() === \Zend_Db::ERR_NONE );
                    }

                    if( $isNoError ){
                        if( $memTable instanceof Table){
                            foreach ($memTable->getForeignKeys() as $foreignKey){
                                $this->_connection->dropForeignKey($backupTableName, $foreignKey['FK_NAME']);
                            }
                        }

                        //generating sql statement for insert into ... select
                        $sql = $this->_connection->insertFromSelect(
                            $this->_connection
                                ->select()
                                ->from( $productTableName ),
                            $backupTableName
                        );

                        $return = $this->_connection->query( $sql );

                        if( $return->errorCode() === \Zend_Db::ERR_NONE ){
                            $result['Success'] = true;
                        }else{
                            $result['Message'] = join("|", $return->errorInfo()) ;
                            $this->_messageManager->addErrorMessage( $result['Message']);
                            break;
                        }

                    }else{
                        $result['Message'] = 'Failed to create backup table.';
                        $this->_messageManager->addErrorMessage( $result['Message']);
                        break;
                    }
                }

            }
        }catch(Exception $e){
            $result['Message'] = $e->getMessage();
            throw $e;
        }

        return $result;

    }

    public function execute()
    {
//        if( !isset($this->_connection) ){
//            $this->_connection = $this->_resourceConnection->getConnection();
//        }
//
//        $db = $this->_config->getConfigData('db');
//
//        $backupFilePath = $this->_configHelper->getDataFilePath() . DIRECTORY_SEPARATOR  . 'Backup';
//        if(!file_exists($backupFilePath)){
//            mkdir($backupFilePath, 0777, true);
//        }
//
//        foreach ($this->_dataHelper->getMagentoDataTableArray() as $rawTableName){
//            $tableName = $this-$this->_connection->getTableName($rawTableName);
//            $backupFile = $backupFilePath . DIRECTORY_SEPARATOR . $rawTableName.'.sql';
//            $query = 'SELECT * INTO OUTFILE '. $backupFile .' FROM ' . $tableName;
//            var_dump($query);
//            $this->_connection->query($query);
//        }
//
//        $backupFile = $backupFilePath . DIRECTORY_SEPARATOR . $db['connection']['default']['dbname'].'.sql';
//        $command = "mysqldump --opt -h " . $db['connection']['default']['host'] . " -u " .$db['connection']['default']['username'] . " -p ". $db['connection']['default']['username']  . " " . $db['connection']['default']['dbname']  . " | gzip > " . $backupFile;
//        $return = system($command);
//        var_dump($return);
        try{
            $this->_connection = $this->_resourceConnection->getConnection();
            $result = $this->_executeBackup();

            if( $result['Success'] ){
                $info = __('Translatable tables has been duplicated.');
                $this->_logger->info( $info );
                $this->_messageManager->addSuccessMessage( $info );
            }else{
                $message = __( $result['Message'] );
                $this->_logger->error( $message );
                $this->_messageManager->addErrorMessage( $message );
            }
        }catch (Exception $e ){
            $result['Success'] = false;
            $result['Message'] = __( $e->getMessage() );
            $this->_logger->error( $result['Message'] );
            $this->messageManager->addErrorMessage( $result['Message'] );
        }

        return;
    }
}