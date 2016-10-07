<?php
/**
 * Created by PhpStorm.
 * User: stevenyang
 * Date: 18/09/15
 * Time: 9:46 AM
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

//fix for magento prefix db tabme names
$prefix = Mage::getConfig()->getTablePrefix()->__toString();


$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('straker_job')}
    ADD COLUMN is_test_job TINYINT(1) NOT NULL DEFAULT 0;
");

$installer->endSetup();
