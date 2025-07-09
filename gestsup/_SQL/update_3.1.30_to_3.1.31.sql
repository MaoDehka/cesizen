-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.31";

ALTER TABLE `tevents` ADD `title` VARCHAR(150) NOT NULL AFTER `type`;
ALTER TABLE `tevents` ADD `allday` VARCHAR(10) NOT NULL AFTER `date_end`;
ALTER TABLE `tevents` ADD `classname` VARCHAR(50) NOT NULL AFTER `title`;
ALTER TABLE `tusers` ADD `mobile` VARCHAR(30) NOT NULL AFTER `phone`;