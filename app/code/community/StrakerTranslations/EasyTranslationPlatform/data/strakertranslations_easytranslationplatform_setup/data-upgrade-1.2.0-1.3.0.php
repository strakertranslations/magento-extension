<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$data = array(
    array( 'type_id' => 5, 'type_name' => 'CMS Page' ),
    array( 'type_id' => 6, 'type_name' => 'CMS Block' )
);

$installer->getConnection()->insertMultiple($installer->getTable('straker_job_type'), $data);

