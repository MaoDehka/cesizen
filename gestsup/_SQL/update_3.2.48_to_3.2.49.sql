-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.49';

ALTER TABLE `tassets_model` ADD INDEX(`type`);
ALTER TABLE `tassets_model` ADD INDEX(`manufacturer`);
ALTER TABLE `tassets_thread` ADD INDEX(`asset`);
ALTER TABLE `ttoken` ADD INDEX(`procedure_id`);
ALTER TABLE `tusers` ADD INDEX(`profile`);

ALTER TABLE `tparameters` ADD `imap_auto_create_user` INT(1) NOT NULL AFTER `imap_from_adr_service`;

ALTER TABLE `tstates` ADD `icon` VARCHAR(512) NOT NULL AFTER `display`;

UPDATE `tstates` SET `icon`='fa-check' WHERE `name`='Résolu';
UPDATE `tstates` SET `icon`='fa-hourglass-start' WHERE `name`='Attente PEC';
UPDATE `tstates` SET `icon`='fa-bars-progress' WHERE `name`='En cours';
UPDATE `tstates` SET `icon`='fa-user' WHERE `name`='Non attribué';
UPDATE `tstates` SET `icon`='fa-reply' WHERE `name`='Attente retour';
UPDATE `tstates` SET `icon`='fa-xmark' WHERE `name`='Rejeté';

ALTER TABLE `tparameters` CHANGE `azure_ad_tenant_number` `azure_ad_tenant_number` INT(5) NOT NULL;

CREATE TABLE `tentra_tenant` (`id` INT(5) NOT NULL , `tenant_name` VARCHAR(512) NOT NULL , `tenant_id` VARCHAR(128) NOT NULL , `client_id` VARCHAR(128) NOT NULL , `client_secret` VARCHAR(128) NOT NULL , `group_filter` VARCHAR(1024) NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `tentra_tenant` CHANGE `id` `id` INT(5) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);
UPDATE `tparameters` SET `azure_ad_tenant_number`=2 WHERE `azure_ad_tenant_id_2`!='';

INSERT INTO `tentra_tenant` (`id`,`tenant_name`,`tenant_id`,`client_id`,`client_secret`,`group_filter`)
  SELECT
    1 AS `id`,
    `azure_ad_name` AS `tenant_name`,
    `azure_ad_tenant_id` AS `tenant_id`,
    `azure_ad_client_id` AS `client_id`,
    `azure_ad_client_secret` AS `client_secret`,
    `azure_ad_group_filter` AS `group_filter`
   FROM `tparameters`;

INSERT INTO `tentra_tenant` (`id`,`tenant_name`,`tenant_id`,`client_id`,`client_secret`,`group_filter`) 
  SELECT
    2 AS `id`,
    `azure_ad_name_2` AS `tenant_name`,
    `azure_ad_tenant_id_2` AS `tenant_id`,
    `azure_ad_client_id_2` AS `client_id`,
    `azure_ad_client_secret_2` AS `client_secret`,
    `azure_ad_group_filter_2` AS `group_filter`
   FROM `tparameters`
WHERE `azure_ad_tenant_id_2`!='';
  
DELETE FROM `tentra_tenant` WHERE `tenant_id`='';

ALTER TABLE `tusers` ADD `azure_ad_tenant_id` VARCHAR(128) NOT NULL AFTER `azure_ad_id`;

ALTER TABLE `tparameters` ADD `azure_ad_sso_hide_login` INT(1) NOT NULL AFTER `azure_ad_sso`;

ALTER TABLE `tparameters` ADD `company_message` INT(1) NOT NULL AFTER `admin_message_alert`;
ALTER TABLE `tcompany` ADD `information_message` VARCHAR(2048) NOT NULL AFTER `comment`;

ALTER TABLE `tassets` ADD `entra_id` VARCHAR(256) NOT NULL AFTER `uuid`;

ALTER TABLE `tassets` ADD `entra_id_tenant_id` VARCHAR(256) NOT NULL AFTER `entra_id`;