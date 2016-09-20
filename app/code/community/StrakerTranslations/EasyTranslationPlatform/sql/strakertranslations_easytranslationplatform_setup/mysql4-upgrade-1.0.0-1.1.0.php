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
  REPLACE INTO `straker_job_type` (`type_id`, `type_name`)
VALUES (4,'Attribute');

CREATE TABLE IF NOT EXISTS `straker_job_attribute` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `translate_lable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `translate_option` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `job_id` int(11) unsigned NOT NULL,
  `version` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `straker_job_attribute_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `straker_job_attribute_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `straker_attribute_translate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) unsigned NOT NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `original` text,
  `translate` text,
  `backup` text,
  `is_imported` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `straker_attribute_translate_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `straker_attribute_translate_ibfk_4` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

 ");
} catch (Exception $e) {
  Mage::logException($e);
}

$installer->endSetup();
