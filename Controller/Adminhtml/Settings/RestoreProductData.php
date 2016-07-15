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
    )
    {
        $this->_messageManager = $messageManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_logger = $logger;
        $this->_dataHelper = $dataHelper;

        return parent::__construct($context);
    }

    private function _executeRestore(){

        $result = array('Success'=> true, 'Message' => '');

        try{

            if( !isset($this->_connection) ){
                $this->_connection = $this->_resourceConnection->getConnection();
            }

            foreach( $this->_dataHelper->getProductTableArray() as $tableName ){
                $backupTableName = $this->_dataHelper->getBackupTableNames( $tableName );

                if( $this->_connection->isTableExists( $tableName )
                    && $this->_connection->isTableExists( $backupTableName )){

                    //generating sql statement of 'insert into ... select ...'
                    $sql = $this->_connection->insertFromSelect(
                        $this->_connection->select()
                            ->from( $backupTableName ),
                        $tableName
                    );

                    $return = $this->_connection->query( $sql );

                    if( $return->errorCode() !== \Zend_Db::ERR_NONE ){
                        $result['Success'] = false;
                        $result['Message'] = join("|", $return->errorInfo());
                        break;
                    }
                }
            }
        }catch(Exception $e){
            $result['Success'] = false;
            $result['Message'] = $e->getMessage();
            throw new Exception( $result['Message'] );
        }

        return $result;

    }


    public function execute()
    {
        try{

            if($this->_hasBackupData()){
                $this->_truncateProductData();
            }else{
                $message = __( 'There is no backup data to restore.' );
                $this->_logger->error( $message );
                $this->_messageManager->addErrorMessage( $message );
                return;

            }

            $result = $this->_executeRestore();

            if( $result['Success'] ){
                $message = __( 'Product and category entity tables has been restored!' );
                $this->_logger->info( $message );
                $this->_messageManager->addSuccessMessage( $message );
            }else{
                $message = __( $result['Message'] );
                $this->_logger->error( $message );
                $this->_messageManager->addErrorMessage( $message );
            }

        }catch (Exception $e ){
            $message = __( $e->getMessage() );
            $this->_logger->error( $message );
            $this->messageManager->addErrorMessage( $message );
        }

        return;
    }

    private function _hasBackupData(){

        $result = true;

        if( !isset($this->_connection) ){
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        foreach ( $this->_dataHelper->getProductTableArray() as $tableName ){

            $backupTableName = $this->_dataHelper->getBackupTableNames( $tableName );

            if( $this->_connection->isTableExists( $backupTableName ) ){
                $sql = $this->_connection->select()
                    ->from(
                        $backupTableName,
                        array('COUNT(*) AS RowCount')
                    );

                $rows = $this->_connection->fetchAll( $sql );

                if( $rows[0]['RowCount'] <= 0){
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }

    private function _truncateProductData()
    {
        if( !isset($this->_connection) ){
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        foreach ( $this->_dataHelper->getProductTableArray() as $tableName ){
            if($this->_connection->isTableExists( $tableName )){
                $this->_connection->truncateTable( $tableName );
            }
        }
    }

}