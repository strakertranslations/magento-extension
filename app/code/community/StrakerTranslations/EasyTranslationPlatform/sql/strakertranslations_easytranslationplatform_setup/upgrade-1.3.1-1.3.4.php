<?php

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$connection = $installer->getConnection();

/**
 * Add new field to 'cataloginventory/stock_item'
 */
$installer->getConnection()
    ->addColumn(
        $installer->getTable('straker_job'),
        'is_test_job',
        [
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length' => 1,
            'comment' => 'Is Test Job',
            'nullable' => false,
            'default' => 0
        ]
    );
