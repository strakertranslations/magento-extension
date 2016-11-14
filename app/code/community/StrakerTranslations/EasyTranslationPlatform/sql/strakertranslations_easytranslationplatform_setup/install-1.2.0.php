<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();

$rawTableName = 'straker_actionlog';
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
            'user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'nullable' => false,
                'unsigned' => true,
            ],
            'User ID'
        )->addColumn(
            'action', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Action'
        )->addColumn(
            'message', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Message'
        )->addColumn(
            'extra', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Extra'
        )->addColumn(
            'log_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            [
                'default' => null
            ],
            'Log Time'
        )->addIndex(
            $installer->getIdxName($rawTableName, 'user_id'),
            'user_id'
        )->addForeignKey(
            $installer->getFkName($rawTableName, 'user_id', 'admin_user', 'user_id'),
            'user_id',
            $installer->getTable('admin_user'),
            'user_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Action Log Table');;
    $connection->createTable($table);
}

$rawTableName = 'straker_apilog';
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
            'method', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'nullable' => false,
                'default' => ''
            ],
            'Method'
        )->addColumn(
            'request', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Request'
        )->addColumn(
            'response', Varien_Db_Ddl_Table::TYPE_TEXT, null,
            [],
            'Response'
        )->addColumn(
            'timestamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            [
                'default' => null
            ],
            'Timestamp'
        )->setComment('Straker Api Log Table');;
    $connection->createTable($table);
}

$rawTableName = 'straker_job';
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
            'type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Type Id'
        )->addColumn(
            'source_store', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Source Store'
        )->addColumn(
            'store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Store Id'
        )->addColumn(
            'title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Title'
        )->addColumn(
            'tj_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Job Number'
        )->addColumn(
            'sl', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Source Language'
        )->addColumn(
            'tl', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Target Language'
        )->addColumn(
            'job_key', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default'   => null
            ],
            'Job Key'
        )->addColumn(
            'quote', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Quote'
        )->addColumn(
            'status_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 1
            ],
            'Status Id'
        )->addColumn(
            'work_flow', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Work Flow'
        )->addColumn(
            'payment_status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Payment Status'
        )->addColumn(
            'source_file', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Source File'
        )->addColumn(
            'download_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Download Url'
        )->addColumn(
            'remote_version', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Remote Version'
        )->addColumn(
            'downloaded_version', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'default' => null
            ],
            'Downloaded Version'
        )->addColumn(
            'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            [
                'default' => null
            ],
            'Created Time'
        )->addColumn(
            'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
            [
                'default' => null
            ],
            'Updated Time'
        )->addIndex($installer->getIdxName($rawTableName, 'job_key'),
            'job_key',
            [
                'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ]
        )->addIndex($installer->getIdxName($rawTableName, 'store_id'),
            'store_id'
        )->addIndex($installer->getIdxName($rawTableName, 'type_id'),
            'type_id'
        )->addIndex($installer->getIdxName($rawTableName, 'status_id'),
            'status_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'store_id', 'core_store', 'store_id'),
            'store_id', $installer->getTable('core_store'), 'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'type_id', 'straker_job_type', 'type_id'),
            'type_id', $installer->getTable('straker_job_type'), 'type_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'status_id', 'straker_job_status', 'status_id'),
            'status_id', $installer->getTable('straker_job_status'), 'status_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Job Table');
    $connection->createTable($table);
}

$rawTableName = 'straker_job_product';
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
            'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Product Id'
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
        )->addIndex($installer->getIdxName($rawTableName, 'product_id'),
            'product_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Product Job Table');
    $connection->createTable($table);
}

$rawTableName = 'straker_job_status';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'status_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Status Id'
        )->addColumn(
            'status_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'nullable' => false,
                'default' => ''
            ],
            'Status Name'
        )->setComment('Straker Job Status Table');
    $connection->createTable($table);
}

$rawTableName = 'straker_job_type';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) != true) {
    $table = $connection
        ->newTable($tableName)
        ->addColumn(
            'type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Type Id'
        )->addColumn(
            'type_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            [
                'nullable' => false,
                'default' => ''
            ],
            'Type Name'
        )->setComment('Straker Job Type Table');
    $connection->createTable($table);
}

$rawTableName = 'straker_product_attributes';
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
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Attribute Id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addIndex($installer->getIdxName($rawTableName, 'attribute_id'),
            'attribute_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Product Attribute Table');
    $connection->createTable($table);
}

$rawTableName = 'straker_product_translate';
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
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Product Id'
        )->addColumn(
            'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Attribute Id'
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
        )->addIndex($installer->getIdxName($rawTableName, 'product_id'),
            'product_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addIndex($installer->getIdxName($rawTableName, 'attribute_id'),
            'attribute_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Product Translation Table');
    $connection->createTable($table);
}

$rawTableName = 'straker_job_category';
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
            'category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Category Id'
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
        )->addIndex($installer->getIdxName($rawTableName, 'category_id'),
            'category_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'category_id', 'catalog_category_entity', 'entity_id'),
            'category_id', $installer->getTable('catalog_category_entity'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Category Job Table');

    $connection->createTable($table);
}

$rawTableName = 'straker_category_attributes';
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
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Attribute Id'
        )->addIndex($installer->getIdxName($rawTableName, 'attribute_id'),
            'attribute_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Category Attribute Table');

    $connection->createTable($table);
}

$rawTableName = 'straker_category_translate';
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
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Category Id'
        )->addColumn(
            'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Attribute Id'
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
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addIndex($installer->getIdxName($rawTableName, 'attribute_id'),
            'attribute_id'
        )->addIndex($installer->getIdxName($rawTableName, 'category_id'),
            'category_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Category Translation Table');

    $connection->createTable($table);
}

$rawTableName = 'straker_job_attribute';
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
            'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Attribute Id'
        )->addColumn(
            'translate_label', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0
            ],
            'Translate Label'
        )->addColumn(
            'translate_option', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0
            ],
            'Translate Option'
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
        )->addIndex($installer->getIdxName($rawTableName, 'attribute_id'),
            'attribute_id'
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Attribute Job Table');

    $connection->createTable($table);
}

$rawTableName = 'straker_attribute_translate';
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
                'unsigned' => true,
                'nullable' => false
            ],
            'Job Id'
        )->addColumn(
            'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5,
            [
                'unsigned' => true,
                'nullable' => false
            ],
            'Attribute Id'
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
        )->addIndex($installer->getIdxName($rawTableName, 'job_id'),
            'job_id'
        )->addIndex($installer->getIdxName($rawTableName, 'attribute_id'),
            'attribute_id'
        )->addForeignKey($installer->getFkName($rawTableName, 'job_id', 'straker_job', 'id'),
            'job_id', $installer->getTable('straker_job'), 'id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey($installer->getFkName($rawTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id', $installer->getTable('eav_attribute'), 'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )->setComment('Straker Attribute Translation Table');

    $connection->createTable($table);
}

$installer->endSetup();