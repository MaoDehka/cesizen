-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.7';
ALTER TABLE `tparameters` ADD `mail_link_redirect_url` VARCHAR(200) NOT NULL AFTER `mail_link`;
ALTER TABLE `trights` ADD `ticket_place_mandatory` INT(1) NOT NULL COMMENT 'Oblige la saisie du champ lieu sur le ticket' AFTER `ticket_place`;