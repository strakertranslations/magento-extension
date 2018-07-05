<?php
/**
 * Created by PhpStorm.
 * User: stevenyang
 * Date: 18/09/15
 * Time: 9:46 AM
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$rawTableName = 'straker_cms_block_attributes';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) !== true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            array(
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ),
            'ID'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            array(
                'nullable' => false,
                'unsigned' => true,
            ),
            'User ID'
        )->addColumn(
            'column_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array(
                'default' => null
            ),
            'Column Name'
        )->addIndex(
            $installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey(
            $installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id',
            $installer->getTable('straker_job'),
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Cms Block Attribute Table');;
    $connection->createTable($table);
}

$installer->endSetup();
