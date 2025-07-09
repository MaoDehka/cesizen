-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.6';

UPDATE `tparameters` SET `mail_color_title`='3b91d1' WHERE `mail_color_title`='438eb9';
UPDATE `tparameters` SET `mail_color_bg`='f2f5f8' WHERE `mail_color_title`='f5f5f5';
UPDATE `tparameters` SET `mail_color_text`='3b91d1' WHERE `mail_color_text`='438eb9';

ALTER TABLE `tparameters` ADD `system_error` INT(1) NOT NULL AFTER `version`;
ALTER TABLE `tparameters` ADD `api` INT(1) NOT NULL AFTER `imap_mailbox_service`;
ALTER TABLE `tparameters` ADD `api_key` VARCHAR(40) NOT NULL AFTER `api`;