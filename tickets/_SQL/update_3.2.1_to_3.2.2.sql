-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.2.2";
ALTER TABLE `trights` ADD `dashboard_col_time` INT(1) NOT NULL COMMENT 'Affiche la colonne temps passé dans la liste des tickets' AFTER `dashboard_col_date_res`;
DELETE FROM `tusers_services` WHERE `service_id` NOT IN (SELECT `id` FROM `tservices`);