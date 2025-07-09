-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.24';
ALTER TABLE `tparameters` CHANGE `mail_oauth_refresh_token` `mail_oauth_refresh_token` VARCHAR(1024) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

UPDATE `tassets_location` SET `id`='0' WHERE `name`='Aucune';
ALTER TABLE `tparameters` ADD `login_message_warning` MEDIUMTEXT NOT NULL AFTER `login_message_info`;