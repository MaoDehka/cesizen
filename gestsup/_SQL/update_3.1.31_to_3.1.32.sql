-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.32";
ALTER TABLE `tparameters` ADD `mail_auto_tech_attribution` INT(1) NOT NULL AFTER `mail_auto_tech_modify`;