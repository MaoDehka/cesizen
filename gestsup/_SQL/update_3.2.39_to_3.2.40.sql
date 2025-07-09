-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.40';

ALTER TABLE `tparameters` ADD `azure_ad_tenant_number` INT(1) NOT NULL AFTER `azure_ad`;
UPDATE `tparameters` SET `azure_ad_tenant_number`=1;
ALTER TABLE `tparameters` ADD `azure_ad_client_id_2` VARCHAR(128) NOT NULL AFTER `azure_ad_client_secret`;
ALTER TABLE `tparameters` ADD `azure_ad_tenant_id_2` VARCHAR(128) NOT NULL AFTER `azure_ad_client_id_2`;
ALTER TABLE `tparameters` ADD `azure_ad_client_secret_2` VARCHAR(128) NOT NULL AFTER `azure_ad_tenant_id_2`;
ALTER TABLE `tparameters` ADD `azure_ad_group_filter` VARCHAR(1024) NOT NULL AFTER `azure_ad_client_secret`;
ALTER TABLE `tparameters` ADD `azure_ad_group_filter_2` VARCHAR(1024) NOT NULL AFTER `azure_ad_client_secret_2`;