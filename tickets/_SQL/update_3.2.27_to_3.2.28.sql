-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.28';
ALTER TABLE `tparameters` ADD `last_version` VARCHAR(16) NOT NULL AFTER `version`;

ALTER TABLE `tparameters` ADD `mail_auto_type` INT(1) NOT NULL AFTER `mail_auto_tech_attribution`;
ALTER TABLE `ttypes` ADD `mail` VARCHAR(512) NOT NULL AFTER `user_validation`;

ALTER TABLE `trights` ADD `ticket_asset_type` INT(1) NOT NULL COMMENT 'Affiche une nouvelle liste déroulante permettant de filtrer les équipements par type d\'équipement' AFTER `ticket_asset_mandatory`;

ALTER TABLE `tparameters` ADD `ticket_open_message` INT(1) NOT NULL AFTER `ticket_time_response_element`;
ALTER TABLE `tparameters` ADD `ticket_open_message_text` MEDIUMTEXT NOT NULL AFTER `ticket_open_message`;

ALTER TABLE `tparameters` ADD `mail_oauth_tenant_id` VARCHAR(128) NOT NULL AFTER `mail_oauth_client_id`;