<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$data = [
    [ 'status_id' => 1, 'status_name' => 'INIT' ],
    [ 'status_id' => 2, 'status_name' => 'QUEUED' ],
    [ 'status_id' => 3, 'status_name' => 'IN_PROGRESS'],
    [ 'status_id' => 4, 'status_name' => 'COMPLETED' ],
    [ 'status_id' => 5, 'status_name' => 'PUBLISHED' ],
];

$installer->getConnection()->insertMultiple($installer->getTable('straker_job_status'), $data);

$data = [
    [ 'type_id' => 1, 'type_name' => 'Product' ],
    [ 'type_id' => 3, 'type_name' => 'Category' ],
    [ 'type_id' => 4, 'type_name' => 'Attribute' ]
];

$installer->getConnection()->insertMultiple($installer->getTable('straker_job_type'), $data);

