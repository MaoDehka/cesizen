-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.2.4";
ALTER TABLE `trights` ADD `dashboard_firstname` INT(1) NOT NULL COMMENT 'Affiche le prénom dans la colonne demandeur et technicien dans la liste des tickets' AFTER `dashboard_agency_only`;
ALTER TABLE `tincidents` ADD `userread` INT(1) NOT NULL DEFAULT '1' AFTER `techread_date`;