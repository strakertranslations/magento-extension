<?php
/**
 * Created by PhpStorm.
 * User: stevenyang
 * Date: 18/09/15
 * Time: 9:46 AM
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

try {
  $installer->run("

INSERT INTO `straker_job_status` (`status_id`, `status_name`)
VALUES
	(5, 'PUBLISHED');


    ");
} catch (Exception $e) {
  Mage::logException($e);
}

$installer->endSetup();
