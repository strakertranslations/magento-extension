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

$query = "
CREATE TABLE IF NOT EXISTS `" . $prefix . "straker_cms_block_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `column_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" . $prefix . "straker_cms_block_attributes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  IF NOT EXISTS `" . $prefix . "straker_cms_block_translate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) unsigned NOT NULL,
  `cms_block_id` int(11) DEFAULT NULL,
  `column_name` varchar(255) NOT NULL DEFAULT '',
  `original` text,
  `translate` text,
  `backup` text,
  `is_imported` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `job_cms_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" . $prefix . "straker_cms_block_translate_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE  IF NOT EXISTS `" . $prefix . "straker_cms_page_attributes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `column_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" . $prefix . "straker_cms_page_attributes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE  IF NOT EXISTS `" . $prefix . "straker_cms_page_translate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) unsigned NOT NULL,
  `cms_page_id` int(11) DEFAULT NULL,
  `column_name` varchar(255) NOT NULL DEFAULT '',
  `original` text,
  `translate` text,
  `backup` text,
  `is_imported` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `job_cms_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `" . $prefix . "straker_cms_page_translate_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE  IF NOT EXISTS `" . $prefix . "straker_job_cmsblock` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `block_id` smallint(6) NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `version` tinyint(3) DEFAULT NULL,
  `origin` text,
  `new_entity_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `block_id` (`block_id`),
  KEY `new_entity_id` (`new_entity_id`),
  CONSTRAINT `" . $prefix . "straker_job_cmsblock_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" . $prefix . "straker_job_cmsblock_ibfk_2` FOREIGN KEY (`block_id`) REFERENCES `cms_block` (`block_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE  IF NOT EXISTS `" . $prefix . "straker_job_cmspage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` smallint(6) NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `version` tinyint(3) DEFAULT NULL,
  `origin` text,
  `new_entity_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `page_id` (`page_id`),
  KEY `new_entity_id` (`new_entity_id`),
  CONSTRAINT `" . $prefix . "straker_job_cmspage_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `straker_job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `" . $prefix . "straker_job_cmspage_ibfk_3` FOREIGN KEY (`page_id`) REFERENCES `cms_page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `" . $prefix . "straker_job_type`;

CREATE TABLE `" . $prefix . "straker_job_type` (
  `type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `" . $prefix . "straker_job_type` (`type_id`, `type_name`)
VALUES
	(1,'Product'),
	(3,'Category'),
	(4,'Attribute'),
	(5,'CMS Page'),
	(6,'CMS Block');

";
$installer->run($query);

$installer->endSetup();
