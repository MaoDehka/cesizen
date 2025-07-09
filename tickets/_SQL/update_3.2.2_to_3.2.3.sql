-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.2.3";
UPDATE `tusers` SET `company`='0' WHERE `company` NOT IN (SELECT `id` FROM `tcompany`);
ALTER TABLE `tlogs` ENGINE=InnoDB;
ALTER TABLE `tusers` CHANGE `phone` `phone` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tusers` CHANGE `mobile` `mobile` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tusers` CHANGE `fax` `fax` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;