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
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'User ID'
        )->addColumn(
            'column_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
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


$rawTableName = 'straker_cms_block_translate';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'User ID'
        )->addColumn(
            'cms_block_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'default' => null
            ],
            'Cms Block ID'
        )->addColumn(
            'column_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'nullable' => false,
                'default' => ''
            ],
            'Column Name'
        )->addColumn(
            'original', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Original'
        )->addColumn(
            'translate', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Translate'
        )->addColumn(
            'backup', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Backup'
        )->addColumn(
            'is_imported', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0
            ],
            'Is Imported'
        )->addColumn(
            'job_cms_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'default' => null
            ],
            'Job Cms ID'
        )->addIndex(
            $installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey(
            $installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id',
            $installer->getTable('straker_job'),
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Cms Block Translate Table');;
    $connection->createTable($table);
}

$rawTableName = 'straker_cms_page_attributes';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'User ID'
        )->addColumn(
            'column_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
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
        )->setComment('Straker Cms Block Translate Table');;
    $connection->createTable($table);
}

$rawTableName = 'straker_cms_page_translate';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'User ID'
        )->addColumn(
            'cms_page_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'default' => null
            ],
            'Cms Page ID'
        )->addColumn(
            'column_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'nullable' => false,
                'default' => ''
            ],
            'Column Name'
        )->addColumn(
            'original', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Original'
        )->addColumn(
            'translate', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Translate'
        )->addColumn(
            'backup', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Backup'
        )->addColumn(
            'is_imported', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0
            ],
            'Is Imported'
        )->addColumn(
            'job_cms_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'default' => null
            ],
            'Job Cms ID'
        )->addIndex(
            $installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey(
            $installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id',
            $installer->getTable('straker_job'),
            'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Cms Block Translate Table');;
    $connection->createTable($table);
}


$rawTableName = 'straker_job_cmsblock';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'block_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6,
            [
                'nullable' => false
            ],
            'Block Id'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'version', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6,
            [
                'default' => null
            ],
            'Version'
        )->addColumn(
            'origin', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Origin'
        )->addColumn(
            'new_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'default' => null
            ],
            'New Entity ID'
        )->addIndex($installer->getIdxName($rawTableName, 'block_id'),
            'block_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'block_id', 'cms_block', 'block_id'),
            'block_id', $installer->getTable('cms_block'), 'block_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Cms Block Job Table');

    $connection->createTable($table);
}


$rawTableName = 'straker_job_cmspage';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'ID'
        )->addColumn(
            'page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6,
            [
                'nullable' => false
            ],
            'Page Id'
        )->addColumn(
            'job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'version', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6,
            [
                'default' => null
            ],
            'Version'
        )->addColumn(
            'origin', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Origin'
        )->addColumn(
            'new_entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'default' => null
            ],
            'New Entity ID'
        )->addIndex($installer->getIdxName($rawTableName, 'page_id'),
            'page_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'page_id', 'cms_page', 'page_id'),
            'page_id', $installer->getTable('cms_page'), 'page_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Cms Page Job Table');

    $connection->createTable($table);
}

$installer->endSetup();
