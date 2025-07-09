-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.30';

ALTER TABLE `tparameters_imap_multi_mailbox` ADD `mailbox_service_auth_type` VARCHAR(24) NOT NULL AFTER `id`;
ALTER TABLE `tparameters_imap_multi_mailbox` ADD `mailbox_service_oauth_client_id` VARCHAR(128) NOT NULL AFTER `password`;
ALTER TABLE `tparameters_imap_multi_mailbox` ADD `mailbox_service_oauth_tenant_id` VARCHAR(128) NOT NULL AFTER `mailbox_service_oauth_client_id`;
ALTER TABLE `tparameters_imap_multi_mailbox` ADD `mailbox_service_oauth_client_secret` VARCHAR(128) NOT NULL AFTER `mailbox_service_oauth_tenant_id`;
ALTER TABLE `tparameters_imap_multi_mailbox` ADD `mailbox_service_oauth_refresh_token` VARCHAR(128) NOT NULL AFTER `mailbox_service_oauth_client_secret`;
ALTER TABLE `tparameters_imap_multi_mailbox` CHANGE `mailbox_service_oauth_refresh_token` `mailbox_service_oauth_refresh_token` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `tparameters` ADD `mail_reply` VARCHAR(16) NOT NULL AFTER `mail_from_adr`;
UPDATE `tparameters` SET `mail_reply`='sender';