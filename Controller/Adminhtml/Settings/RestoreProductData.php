<?php

namespace Straker\EasyTranslationPlatform\Controller\Adminhtml\Settings;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;


class RestoreProductData extends Action
{

    protected $_messageManager;
    protected $_resourceConnection;
    protected $_connection;

    protected $_productTables = array(
        'catalog_product_entity_varchar',
        'catalog_product_entity_text',
        'catalog_category_entity_varchar',
        'catalog_category_entity_text'
    );

    const BACKUP_TABLE_SUFFIX = '_back';

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ManagerInterface $messageManager
    )
    {
        $this->_messageManager = $messageManager;
        $this->_resourceConnection = $resourceConnection;

        return parent::__construct($context);
    }

    private function _executeRestore(){

        $result = array('Success'=> true, 'Message' => '');

        try{

            if( !isset($this->_connection) ){
                $this->_connection = $this->_resourceConnection->getConnection();
            }

            foreach( $this->_productTables as $tableName ){
                $backupTableName = $this->_getBackupTableName( $tableName );

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

                $this->_messageManager->addErrorMessage( 'There is no backup data to restore.' );
                return;

            }

            $result = $this->_executeRestore();

            if( !$result['Success'] ){
                $this->_messageManager->addErrorMessage( $result['Message'] );
            }else{
                $this->_messageManager->addSuccessMessage( 'product and category entity tables has been restored!' );
            }

        }catch (Exception $e ){
            $this->messageManager->addErrorMessage( $e->getMessage() );
        }

        return;
    }

    private function _hasBackupData(){

        $result = true;

        if( !isset($this->_connection) ){
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        foreach ( $this->_productTables as $tableName ){

            $backupTableName = $this->_getBackupTableName( $tableName );

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

    private function _getBackupTableName( $tableName ){
        return $tableName.self::BACKUP_TABLE_SUFFIX;
    }


    private function _truncateProductData()
    {
        if( !isset($this->_connection) ){
            $this->_connection = $this->_resourceConnection->getConnection();
        }

        foreach ( $this->_productTables as $tableName ){
            if($this->_connection->isTableExists( $tableName )){
                $this->_connection->truncateTable( $tableName );
            }
        }
    }

}