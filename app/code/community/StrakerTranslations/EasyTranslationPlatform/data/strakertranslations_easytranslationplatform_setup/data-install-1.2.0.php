<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$data = array(
    array( 'status_id' => 1, 'status_name' => 'INIT' ),
    array( 'status_id' => 2, 'status_name' => 'QUEUED' ),
    array( 'status_id' => 3, 'status_name' => 'IN_PROGRESS'),
    array( 'status_id' => 4, 'status_name' => 'COMPLETED' ),
    array( 'status_id' => 5, 'status_name' => 'PUBLISHED' ),
);

$installer->getConnection()->insertMultiple($installer->getTable('straker_job_status'), $data);

$data = array(
    array( 'type_id' => 1, 'type_name' => 'Product' ),
    array( 'type_id' => 3, 'type_name' => 'Category' ),
    array( 'type_id' => 4, 'type_name' => 'Attribute' )
);

$installer->getConnection()->insertMultiple($installer->getTable('straker_job_type'), $data);

