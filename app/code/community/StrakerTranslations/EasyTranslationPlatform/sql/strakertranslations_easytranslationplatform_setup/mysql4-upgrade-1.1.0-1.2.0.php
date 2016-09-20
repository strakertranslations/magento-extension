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

if (!empty($prefix)) {

  $installer->startSetup();

  try {
    $query = "    CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_actionlog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `message` text,
  `extra` text,
  `log_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `" .  $prefix . "straker_actionlog_ibfk_1` FOREIGN KEY (`id`) REFERENCES `" .  $prefix . "admin_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_apilog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(255) NOT NULL DEFAULT '',
  `request` text,
  `response` text,
  `timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_job` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` tinyint(3) unsigned NOT NULL,
  `source_store` smallint(5) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `tj_number` varchar(255) DEFAULT NULL,
  `sl` varchar(255) DEFAULT NULL,
  `tl` varchar(255) DEFAULT NULL,
  `job_key` varchar(255) DEFAULT NULL,
  `quote` varchar(255) DEFAULT NULL,
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `work_flow` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `source_file` varchar(255) DEFAULT NULL,
  `download_url` varchar(255) DEFAULT NULL,
  `remote_version` varchar(255) DEFAULT NULL,
  `downloaded_version` varchar(255) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_key` (`job_key`),
  KEY `store_id` (`store_id`),
  KEY `type_id` (`type_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `" .  $prefix . "straker_job_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `" .  $prefix . "core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_job_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `" .  $prefix . "straker_job_type` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_job_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `" .  $prefix . "straker_job_status` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_job_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `version` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" .  $prefix . "straker_job_product_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_job_status` (
  `status_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `status_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO `" .  $prefix . "straker_job_status` (`status_id`, `status_name`)
VALUES
	(1,'INIT'),
	(2,'QUEUED'),
	(3,'IN_PROGRESS'),
	(4,'COMPLETED');

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_job_type` (
  `type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO `" .  $prefix . "straker_job_type` (`type_id`, `type_name`)
VALUES
	(1,'Product');

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_product_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `" .  $prefix . "straker_product_attributes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_product_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `" .  $prefix . "eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_product_translate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `original` text,
  `translate` text,
  `backup` text,
  `is_imported` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `job_id` (`job_id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `" .  $prefix . "straker_product_translate_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_product_translate_ibfk_4` FOREIGN KEY (`attribute_id`) REFERENCES `" .  $prefix . "eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO `" .  $prefix . "straker_job_type` (`type_id`, `type_name`)
VALUES (3,'Category');

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_job_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `version` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" .  $prefix . "straker_job_category_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `" .  $prefix . "catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_job_category_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_category_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `" .  $prefix . "straker_category_attributes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_category_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `" .  $prefix . "eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_category_translate` (
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
  CONSTRAINT `" .  $prefix . "straker_category_translate_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_category_translate_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `" .  $prefix . "eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

REPLACE INTO `" .  $prefix . "straker_job_status` (`status_id`, `status_name`)
VALUES
	(5, 'PUBLISHED');

REPLACE INTO `" .  $prefix . "straker_job_type` (`type_id`, `type_name`)
VALUES (4,'Attribute');

CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_job_attribute` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `translate_lable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `translate_option` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `job_id` int(11) unsigned NOT NULL,
  `version` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" .  $prefix . "straker_job_attribute_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `" .  $prefix . "eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_job_attribute_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `" .  $prefix . "straker_attribute_translate` (
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
  CONSTRAINT `" .  $prefix . "straker_attribute_translate_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `" .  $prefix . "straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" .  $prefix . "straker_attribute_translate_ibfk_4` FOREIGN KEY (`attribute_id`) REFERENCES `" .  $prefix . "eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
";
    $installer->run($query);
  } catch (Exception $e) {
    Mage::logException($e);
  }

  $installer->endSetup();
}