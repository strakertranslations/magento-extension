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

// $tables = [
//     'straker_actionlog',
//     'straker_cms_block_attributes',
//     'straker_cms_block_translate',
//     'straker_cms_page_attributes',
//     'straker_cms_page_translate',
//     'straker_job_cmsblock',
//     'straker_job_cmspage'
// ];
//
// $prefix = Mage::getConfig()->getTablePrefix();
//
//
// //format of returned foreign key
// //[
// //    'FK_NAME',
// //    'SCHEMA_NAME',
// //    'TABLE_NAME',
// //    'COLUMN_NAME',
// //    'REF_SHEMA_NAME',
// //    'REF_TABLE_NAME',
// //    'REF_COLUMN_NAME',
// //    'ON_DELETE',
// //    'ON_UPDATE'
// //];
//
// foreach ($tables as $table){
//     $tableName = $installer->getTable($table);
//     $fks = $connection->getForeignKeys($tableName);
//     if(strcasecmp($table, 'straker_actionlog') === 0){
//         $fk = reset($fks);
//         $connection->dropForeignKey($tableName, $fk['FK_NAME']);
//         $connection->addForeignKey(
//             $installer->getFkName($table, 'user_id', 'admin_user', 'user_id'),
//             $tableName,
//             'user_id',
//             $installer->getTable('admin_user'),
//             'user_id',
//             Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
//         );
//     }else{
//         foreach ($fks as $fk){
//
//             if(strcasecmp(substr($fk['REF_TABLE_NAME'], 0, strlen($prefix)), $prefix) !== 0){
//                 $connection->dropForeignKey($tableName, $fk['FK_NAME']);
//                 $connection->addForeignKey(
//                     $installer->getFkName($table, $fk['COLUMN_NAME'], $fk['REF_TABLE_NAME'], $fk['REF_COLUMN_NAME']),
//                     $tableName,
//                     $fk['COLUMN_NAME'],
//                     $installer->getTable($fk['REF_TABLE_NAME']),
//                     $fk['REF_COLUMN_NAME'],
//                     Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
//                 );
//             }
//         }
//     }
//
// }
