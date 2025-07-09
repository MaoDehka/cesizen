-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.26';
ALTER TABLE `tcompany` CHANGE `address` `address` VARCHAR(256) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ttoken` ADD `ip` VARCHAR(45) NOT NULL AFTER `user_id`;