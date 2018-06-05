<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$jobTypeCollection = Mage::getResourceModel('strakertranslations_easytranslationplatform/job_type_collection');
$jobTypes = $jobTypeCollection->getData();
$jobTypeCount = $jobTypeCollection->getSize();

if ( $jobTypeCount < 5 ) {
    $checkData = array(
        array( 'type_id' => 5, 'type_name' => 'CMS Page' ),
        array( 'type_id' => 6, 'type_name' => 'CMS Block' )
    );

    $data = array();

    foreach($checkData as $d) {
        if (!in_array($d, $jobTypes)) {
            $data[] = $d;
        }
    }

    if(count($data)) {
        $installer->getConnection()->insertMultiple($installer->getTable('straker_job_type'), $data);
    }
}

