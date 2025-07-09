-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.36";
ALTER TABLE `trights` ADD `ticket_description_insert_image` INT(1) NOT NULL COMMENT 'Affiche le bouton inserer image sur le champ description' AFTER `ticket_description_mandatory`;
UPDATE `trights` SET `ticket_description_insert_image`=2;
ALTER TABLE `trights` ADD `ticket_resolution_insert_image` INT(1) NOT NULL COMMENT 'Affiche le bouton inserer image sur le champ résolution' AFTER `ticket_resolution_disp`;
UPDATE `trights` SET `ticket_resolution_insert_image`=2;
ALTER TABLE `tevents` CHANGE `disable` `disable` INT(1) NOT NULL;