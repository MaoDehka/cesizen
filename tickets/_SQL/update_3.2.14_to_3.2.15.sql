-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.15';
UPDATE `tparameters` SET `log`='1';
ALTER TABLE `trights` ADD `ticket_fusion` INT(1) NOT NULL COMMENT 'Affiche le bouton fusion sur le ticket' AFTER `ticket_print`;
UPDATE `trights` SET `ticket_fusion`='2' WHERE `id`='1' OR `id`='5';