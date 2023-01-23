DROP TABLE IF EXISTS `task`;
ALTER TABLE `account` ADD `check_interval` int(10) unsigned NOT NULL DEFAULT 10 AFTER `last_check` ;