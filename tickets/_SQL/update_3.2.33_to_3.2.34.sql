-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.34';
ALTER TABLE `tcompany` ADD `comment` VARCHAR(512) NOT NULL AFTER `limit_hour_date_start`;
UPDATE `tusers` SET `disable`='1' WHERE `login`='aucun';