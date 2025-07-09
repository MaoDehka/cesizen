-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.35';
ALTER TABLE `tparameters` ADD `system_warning` INT(1) NOT NULL AFTER `system_error`;
UPDATE `tparameters` SET `api_client_ip`='127.0.0.1';

ALTER TABLE `tparameters` CHANGE `api_key` `api_key` VARCHAR(1024) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` ADD `server_date_install` DATE NOT NULL AFTER `server_language`;

ALTER TABLE `tparameters` ADD `ticket_recurrent_create` INT(1) NOT NULL AFTER `ticket_open_message_text`;
ALTER TABLE `ttemplates` ADD `date_start` DATE NOT NULL AFTER `incident`;
ALTER TABLE `ttemplates` ADD `frequency` VARCHAR(32) NOT NULL AFTER `date_start`;
ALTER TABLE `ttemplates` ADD `last_execution_date` DATE NOT NULL AFTER `frequency`;
UPDATE `tusers` SET `disable`='1' WHERE `login`='aucun';