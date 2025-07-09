-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.41';
ALTER TABLE `trights` ADD `asset_list_col_sn_manufacturer` INT(1) NOT NULL COMMENT 'Affiche la colonne numéro de série fabricant' AFTER `asset_list_col_location`;

ALTER TABLE `tparameters` ADD `azure_ad_name` VARCHAR(128) NOT NULL AFTER `azure_ad_client_id`;
ALTER TABLE `tparameters` ADD `azure_ad_name_2` VARCHAR(128) NOT NULL AFTER `azure_ad_group_filter`;
ALTER TABLE `trights` ADD `ticket_asset_model` INT(1) NOT NULL COMMENT 'Affiche la liste des modèles sur le champ équipement' AFTER `ticket_asset_type`;

OPTIMIZE TABLE `tusers`;
OPTIMIZE TABLE `tincidents`;
OPTIMIZE TABLE `tthreads`;
OPTIMIZE TABLE `tattachments`;
OPTIMIZE TABLE `tcompany`;
OPTIMIZE TABLE `tlogs`;
OPTIMIZE TABLE `tusers_services`;