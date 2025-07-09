-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.41";

ALTER TABLE `tparameters` CHANGE `logo` `logo` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` ADD `ticket_autoclose` INT(1) NOT NULL AFTER `ticket_default_state`;
ALTER TABLE `tparameters` ADD `ticket_autoclose_delay` INT(3) NOT NULL AFTER `ticket_autoclose`;
ALTER TABLE `tparameters` ADD `ticket_autoclose_state` INT(1) NOT NULL AFTER `ticket_autoclose_delay`;
ALTER TABLE `tparameters` ADD `cron_daily` DATE NOT NULL AFTER `timeout`;