<?php

/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$connection = $installer->getConnection();

$tables = array(
    'straker_job_cmsblock',
    'straker_job_cmspage'
);

foreach($tables as $table){
    $tableName = $installer->getTable($table);
    if ($connection->isTableExists($tableName) === true){
        if( !$connection->tableColumnExists($tableName, 'title') ){
            $connection->addColumn(
                $tableName, 'title',
                array(
                    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Title',
                    'nullable' => true,
                    'default' => null
                )
            );
        }

        if( !$connection->tableColumnExists($tableName, 'identifier') ){
            $connection->addColumn(
                $tableName, 'identifier',
                array(
                    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length' => 100,
                    'comment' => 'Identifier',
                    'nullable' => true,
                    'default' => null
                )
            );
        }
    }
}

$installer->endSetup();