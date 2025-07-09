-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.33';
ALTER TABLE `tparameters` ADD `telemetry` INT NOT NULL AFTER `update_channel`;
ALTER TABLE `trights` ADD `ticket_subcat_disp` INT(1) NOT NULL COMMENT 'Affiche le champ sous-catégorie' AFTER `ticket_cat_service_only`;
UPDATE `trights` SET `ticket_subcat_disp`='2';