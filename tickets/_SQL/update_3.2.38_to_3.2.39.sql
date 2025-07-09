-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.39';

ALTER TABLE `trights` ADD `ticket_user_info_mobile` INT(1) NOT NULL COMMENT 'Affiche les informations demandeur sur mobile' AFTER `ticket_user_company`;
ALTER TABLE `trights` ADD `ticket_asset_user_only` INT(1) NOT NULL COMMENT 'Affiche uniquement les équipements associés au demandeur' AFTER `ticket_asset_mandatory`;

UPDATE `trights` SET `ticket_asset_user_only`='2' WHERE `profile`='1' OR `profile`='2' OR `profile`='3';

ALTER TABLE `tassets` CHANGE `netbios` `netbios` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tassets` CHANGE `sn_internal` `sn_internal` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tassets` CHANGE `sn_manufacturer` `sn_manufacturer` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tassets` CHANGE `sn_indent` `sn_indent` VARCHAR(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;