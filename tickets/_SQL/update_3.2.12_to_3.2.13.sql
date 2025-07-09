-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.13';
ALTER TABLE `trights` CHANGE `userbar` `userbar` INT(1) NOT NULL COMMENT 'Affiche les propriétés étendues de la barre utilisateur';
ALTER TABLE `trights` CHANGE `ticket_description_insert_image` `ticket_description_insert_image` INT(1) NOT NULL COMMENT 'Affiche le bouton insérer image sur le champ description';
ALTER TABLE `trights` CHANGE `ticket_resolution_insert_image` `ticket_resolution_insert_image` INT(1) NOT NULL COMMENT 'Affiche le bouton insérer image sur le champ résolution';
ALTER TABLE `trights` CHANGE `ticket_resolution_disp` `ticket_resolution_disp` INT(1) NOT NULL COMMENT 'Affiche le champ résolution dans le ticket';