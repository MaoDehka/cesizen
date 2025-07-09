-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.11';
ALTER TABLE `tparameters` ADD `login_message` INT(1) NOT NULL AFTER `default_skin`;
ALTER TABLE `tparameters` ADD `login_message_info` VARCHAR(300) NOT NULL AFTER `login_message`;
ALTER TABLE `tparameters` ADD `login_message_alert` VARCHAR(300) NOT NULL AFTER `login_message_info`;
ALTER TABLE `tparameters` CHANGE `login_message_info` `login_message_info` VARCHAR(512) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` CHANGE `login_message_alert` `login_message_alert` VARCHAR(512) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `trights` ADD `ticket_cat_service_only` INT(1) NOT NULL COMMENT 'Active le cloisonnement des catégories en fonction d\'un service' AFTER `ticket_cat_mandatory`;
UPDATE `trights` SET `ticket_cat_service_only`='2';
ALTER TABLE `tincidents` ADD `observer1` INT(10) NOT NULL AFTER `user`;
ALTER TABLE `tincidents` ADD `observer2` INT(10) NOT NULL AFTER `observer1`;
ALTER TABLE `tincidents` ADD `observer3` INT(10) NOT NULL AFTER `observer2`;
ALTER TABLE `tparameters` ADD `ticket_observer` INT(1) NOT NULL AFTER `ticket_type`;
ALTER TABLE `trights` ADD `ticket_observer` INT(1) NOT NULL COMMENT 'Modification du champ observateur sur le ticket' AFTER `ticket_user_company`;
ALTER TABLE `trights` ADD `ticket_observer_disp` INT(1) NOT NULL COMMENT 'Affichage du champ observateur sur le ticket' AFTER `ticket_observer`;
ALTER TABLE `tincidents` ADD INDEX(`observer1`);
ALTER TABLE `tincidents` ADD INDEX(`observer2`);
ALTER TABLE `tincidents` ADD INDEX(`observer3`);
ALTER TABLE `trights` ADD `side_your_observer` INT(1) NOT NULL COMMENT 'Affiche les tickets sur lesquels vous êtes observateur, dans la section Vos tickets' AFTER `side_your_tech_group`;
ALTER TABLE `trights` ADD `ticket_thread_delete_all` INT NOT NULL COMMENT 'Suppression de toutes les résolutions' AFTER `ticket_thread_delete`;
ALTER TABLE `trights` CHANGE `ticket_thread_delete` `ticket_thread_delete` INT(1) NOT NULL COMMENT 'Suppression de ses résolutions';
UPDATE `trights` SET `ticket_thread_delete_all`=`ticket_thread_delete`;
UPDATE `trights` SET `ticket_thread_delete`='0';
ALTER TABLE `trights` ADD `dashboard_col_date_modif` INT NOT NULL COMMENT 'Affiche la colonne date de dernière modification dans la liste des tickets' AFTER `dashboard_col_date_res`;
UPDATE `tincidents` SET `date_modif`=`date_create` WHERE `date_modif`='0000-00-00 00:00:00';