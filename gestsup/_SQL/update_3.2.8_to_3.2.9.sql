-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.9';
ALTER TABLE `tparameters` ADD `server_language` VARCHAR(10) NOT NULL AFTER `server_timezone`;
UPDATE `tparameters` SET `server_language`='fr_FR';
ALTER TABLE `tparameters` ROW_FORMAT=DYNAMIC;
ALTER TABLE `tparameters` CHANGE `api_key` `api_client_ip` VARCHAR(512) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
UPDATE `tsubcat` SET `name`='Aucune' WHERE `id`='0';
UPDATE `tcategory` SET `name`='Aucune' WHERE `id`='0';
ALTER TABLE `tparameters` ADD `api_key` VARCHAR(128) NOT NULL AFTER `api_client_ip`;