-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.29';

ALTER TABLE `tparameters` ADD `imap_auth_type` VARCHAR(24) NOT NULL AFTER `imap_server`;
ALTER TABLE `tparameters` ADD `imap_oauth_client_id` VARCHAR(128) NOT NULL AFTER `imap_password`;
ALTER TABLE `tparameters` ADD `imap_oauth_tenant_id` VARCHAR(128) NOT NULL AFTER `imap_oauth_client_id`;
ALTER TABLE `tparameters` ADD `imap_oauth_client_secret` VARCHAR(128) NOT NULL AFTER `imap_oauth_tenant_id`;
ALTER TABLE `tparameters` ADD `imap_oauth_refresh_token` VARCHAR(1024) NOT NULL AFTER `imap_oauth_client_secret`;
ALTER TABLE `tparameters` CHANGE `imap_oauth_refresh_token` `imap_oauth_refresh_token` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` CHANGE `mail_oauth_refresh_token` `mail_oauth_refresh_token` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;