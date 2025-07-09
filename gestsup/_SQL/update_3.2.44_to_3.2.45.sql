-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.45';

ALTER TABLE `trights` ADD `ticket_user_mandatory` INT(1) NOT NULL COMMENT 'Oblige la saisie du champ Demandeur' AFTER `ticket_user_disp`;

ALTER TABLE `trights` ADD `ticket_user_mail` INT(1) NOT NULL COMMENT 'Affiche le mail de l\'utilisateur dans la liste des demandeurs sur un ticket' AFTER `ticket_user_company`;
ALTER TABLE `trights` ADD `ticket_user_mobile` INT(1) NOT NULL COMMENT 'Affiche le portable de l\'utilisateur dans la liste des demandeurs sur un ticket' AFTER `ticket_user_mail`;

ALTER TABLE `tparameters` ADD `mail_cc_tech` INT(1) NOT NULL AFTER `mail_cc`;
UPDATE `tparameters` SET `mail_cc_tech`='1';