-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.45";
ALTER TABLE `trights` ADD `ticket_type_mandatory` INT(1) NOT NULL COMMENT 'Oblige la saisie du champ type dans le ticket' AFTER `ticket_type_service_limit`;
ALTER TABLE `trights` ADD `ticket_cat_mandatory` INT(1) NOT NULL COMMENT 'Oblige la saisie du champ catégorie' AFTER `ticket_cat_actions`;
ALTER TABLE `tusers` CHANGE `ip` `ip` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tassets_iface` CHANGE `ip` `ip` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tassets_network` CHANGE `network` `network` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tassets_network` CHANGE `netmask` `netmask` VARCHAR(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `tparameters` ADD `mail_template` VARCHAR(50) NOT NULL AFTER `mail_newticket_address`;
UPDATE `tparameters` SET `mail_template`="default.htm";
UPDATE `tparameters` SET `mail_template`="default_place.htm" WHERE ticket_places='1';