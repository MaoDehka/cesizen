-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.48';
ALTER TABLE `tparameters` ADD `login_background` VARCHAR(128) NOT NULL AFTER `login_message_alert`;