<?php

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$connection = $installer->getConnection();

$tables = [
    'straker_job_cmsblock',
    'straker_job_cmspage'
];

foreach($tables as $table){
    $tableName = $installer->getTable($table);
    if ($connection->isTableExists($tableName) === true){
        $connection->addColumn( $tableName, 'title',
            [
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Title',
                'nullable' => true,
                'default' => null
            ]
        );
        $connection->addColumn( $tableName, 'identifier',
            [
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length' => 100,
                'comment' => 'Identifier',
                'nullable' => true,
                'default' => null
            ]
        );
    }
}

$installer->endSetup();