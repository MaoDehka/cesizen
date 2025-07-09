-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.17';
ALTER TABLE `ttoken` ADD `procedure_id` INT(10) NOT NULL AFTER `ticket_id`;
ALTER TABLE `trights` ADD `planning_tech_color` INT(1) NOT NULL AFTER `planning`;
ALTER TABLE `trights` CHANGE `planning_tech_color` `planning_tech_color` INT(1) NOT NULL COMMENT 'Affiche la couleur du technicien dans le calendrier';
ALTER TABLE `tusers` ADD `planning_color` VARCHAR(7) NOT NULL AFTER `ldap_guid`;
ALTER TABLE `trights` ADD `planning_direct_event` INT(1) NOT NULL COMMENT 'Permets l\'ajout d\'évènement en cliquant sur le calendrier' AFTER `planning_tech_color`;
UPDATE `trights` SET `planning_direct_event`='2';
ALTER TABLE `tparameters` ADD `admin_message_alert` VARCHAR(256) NOT NULL AFTER `login_message_alert`;
UPDATE `tparameters` SET `mail_txt`='Bonjour, <br />Vous avez fait la demande suivante auprès du support :' WHERE `mail_txt`='Bonjour, <br />Vous avez fait la demande suivante auprès du support:';