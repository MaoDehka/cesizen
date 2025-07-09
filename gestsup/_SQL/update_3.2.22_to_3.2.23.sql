-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.23';
ALTER TABLE `tparameters` ADD `mail_oauth_client_id` VARCHAR(128) NOT NULL AFTER `mail_auth`;
ALTER TABLE `tparameters` ADD `mail_oauth_client_secret` VARCHAR(128) NOT NULL AFTER `mail_oauth_client_id`;
ALTER TABLE `tparameters` ADD `mail_oauth_refresh_token` VARCHAR(512) NOT NULL AFTER `mail_oauth_client_secret`;
ALTER TABLE `tparameters` ADD `mail_auth_type` VARCHAR(32) NOT NULL AFTER `mail_auth`;
UPDATE `tparameters` SET `mail_auth_type`='login';
UPDATE `tassets_location` SET `id`='0' WHERE `name`='Aucune';