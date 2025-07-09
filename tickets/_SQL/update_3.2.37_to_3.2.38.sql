-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.38';

ALTER TABLE `tparameters` ADD `azure_ad_sso` INT(1) NOT NULL AFTER `azure_ad_disable_user`;
ALTER TABLE `tassets` ADD `uuid` VARCHAR(128) NOT NULL AFTER `discover_import_csv`;
ALTER TABLE `tparameters` ADD `ocs` INT(1) NOT NULL AFTER `azure_ad_sso`;
ALTER TABLE `tparameters` ADD `ocs_server_url` VARCHAR(256) NOT NULL AFTER `ocs`;

ALTER TABLE `tparameters` ROW_FORMAT=DYNAMIC;