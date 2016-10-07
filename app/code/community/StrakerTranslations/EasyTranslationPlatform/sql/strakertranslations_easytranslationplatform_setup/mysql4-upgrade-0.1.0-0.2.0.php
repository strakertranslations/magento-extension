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
INSERT INTO `straker_job_type` (`type_id`, `type_name`)
VALUES (2,'Category');

CREATE TABLE `straker_job_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `version` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `straker_job_category_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `straker_job_category_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `straker_category_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `straker_category_attributes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `straker_category_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `straker_category_translate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `original` text,
  `translate` text,
  `backup` text,
  `is_imported` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `straker_category_translate_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `straker_category_translate_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
} catch (Exception $e) {
  Mage::logException($e);
}

$installer->endSetup();
