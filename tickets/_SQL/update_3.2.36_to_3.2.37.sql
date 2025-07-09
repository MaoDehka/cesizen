-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.37';

ALTER TABLE `tparameters` ADD `planning_ics` INT(1) NOT NULL AFTER `planning`;
ALTER TABLE `tparameters` ADD `server_proxy_url` VARCHAR(200) NOT NULL AFTER `server_date_install`;