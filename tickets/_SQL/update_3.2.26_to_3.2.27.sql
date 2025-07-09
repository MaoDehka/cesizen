-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.27';

ALTER TABLE `trights` ADD `contract` INT(1) NOT NULL COMMENT 'Affiche le menu contrat' AFTER `project`;
UPDATE `trights` SET `contract`=1 WHERE `profile`=4;

ALTER TABLE `tparameters` ADD `ticket_time_response_element` INT(1) NOT NULL AFTER `ticket_cat_auto_attribute`;
ALTER TABLE `tthreads` ADD `time` INT(10) NOT NULL AFTER `dest_mail`;