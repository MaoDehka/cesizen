-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.12';
UPDATE `tincidents` SET `date_modif`=`date_create` WHERE `date_modif`='0000-00-00 00:00:00';
CREATE TABLE `tauth_attempts` ( `id` INT(10) NOT NULL AUTO_INCREMENT , `date` DATETIME NOT NULL , `ip` VARCHAR(40) NOT NULL , `attempts` INT(10) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `trights` ADD `ticket_billable` INT(1) NOT NULL COMMENT 'Affiche le champ facturable sur le ticket, dans la liste des tickets, et dans la barre utilisateur' AFTER `ticket_criticality_service_limit`;
ALTER TABLE `tincidents` ADD `billable` INT(1) NOT NULL AFTER `date_modif`;
ALTER TABLE `tparameters` ADD `imap_from_adr_service` INT NOT NULL AFTER `imap_mailbox_service`;