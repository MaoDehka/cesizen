-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.32';
ALTER TABLE `tparameters` ADD `imap_date_create` VARCHAR(32) NOT NULL AFTER `imap_password`;
UPDATE `tparameters` SET `imap_date_create`='date_mail';
ALTER TABLE `trights` ADD `ticket_resolution_text_only` INT(1) NOT NULL COMMENT 'Affiche uniquement les événements de type texte dans la résolution' AFTER `ticket_resolution_insert_image`;

ALTER TABLE `tparameters` ADD `azure_ad` INT(1) NOT NULL AFTER `ldap_disable_user`;
ALTER TABLE `tparameters` ADD `azure_ad_client_id` VARCHAR(128) NOT NULL AFTER `azure_ad`;
ALTER TABLE `tparameters` ADD `azure_ad_tenant_id` VARCHAR(128) NOT NULL AFTER `azure_ad_client_id`;
ALTER TABLE `tparameters` ADD `azure_ad_client_secret` VARCHAR(128) NOT NULL AFTER `azure_ad_tenant_id`;
ALTER TABLE `tusers` ADD `azure_ad_id` VARCHAR(64) NOT NULL AFTER `ldap_guid`;
ALTER TABLE `tusers` ADD `ldap_sid` VARCHAR(64) NOT NULL AFTER `ldap_guid`;
ALTER TABLE `tparameters` ADD `azure_ad_login_field` VARCHAR(32) NOT NULL AFTER `azure_ad_client_secret`;
UPDATE `tparameters` SET `azure_ad_login_field`='UserPrincipalName';
ALTER TABLE `tparameters` ADD `azure_ad_disable_user` INT(1) NOT NULL AFTER `azure_ad_login_field`;