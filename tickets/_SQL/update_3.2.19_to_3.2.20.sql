-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.20';
ALTER TABLE `tviews` ADD `technician` INT(10) NOT NULL AFTER `subcat`;
ALTER TABLE `tviews` ADD INDEX(`technician`);
ALTER TABLE `tviews` ADD INDEX(`uid`);