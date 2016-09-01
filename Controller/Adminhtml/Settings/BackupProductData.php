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

class BackupProductData extends Action
{

    protected $_messageManager;
    protected $_resourceConnection;
    protected $_connection;
    protected $_logger;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ManagerInterface $messageManager,
        Logger $logger,
        Data $dataHelper
    )
    {
        $this->_messageManager = $messageManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_logger = $logger;
        $this->_dataHelper = $dataHelper;

        return parent::__construct($context);
    }

    private function _executeBackup(){

        $result = [ 'Success' => false, 'Message' => ''];
        $isNoError = true;

        try{

            if( !isset($this->_connection) ){
                $this->_connection = $this->_resourceConnection->getConnection();
            }

            foreach ($this->_dataHelper->getMagentoDataTableArray() as $productTableName ){
                $productTableName = $this->_connection->getTableName($productTableName);
                if( $this->_connection->isTableExists( $productTableName )){
                    $backupTableName = $this->_dataHelper->getBackupTableNames( $productTableName );

                    //if new table exists with data, it should be truncated. Otherwise, create it.
                    if( $this->_connection->isTableExists( $backupTableName )){
                        $this->_connection->truncateTable( $backupTableName );
                    }else{
                        //create a Table instance in memory, the structure is same as product table
                        $table = $this->_connection->createTableByDdl($productTableName, $backupTableName);
                        //create table in database and return a boolean value by comparing with the no error code
                        $isNoError = ( $this->_connection->createTable($table)->errorCode() === \Zend_Db::ERR_NONE );
                    }

                    if( $isNoError ){
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
            throw new Exception( $result['Message'] );
        }

        return $result;

    }

    public function execute()
    {

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