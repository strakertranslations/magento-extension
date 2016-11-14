<?php
/**
 * Created by PhpStorm.
 * User: stevenyang
 * Date: 18/09/15
 * Time: 9:46 AM
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$rawTableName = 'straker_job_cmspage';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) == true) {
    $table = $connection
        ->addIndex(
            $tableName,
            $installer->getIdxName($rawTableName, 'new_entity_id'),
            'new_entity_id'
        );
}

$rawTableName = 'straker_job_cmsblock';
$tableName = $installer->getTable($rawTableName);
if ($connection->isTableExists($tableName) == true) {
    $table = $connection
        ->addIndex(
            $tableName,
            $installer->getIdxName($rawTableName, 'new_entity_id'),
            'new_entity_id'
        );
}