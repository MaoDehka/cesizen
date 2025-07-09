-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.2.5";
ALTER TABLE `tparameters` ADD `default_skin` VARCHAR(10) NOT NULL AFTER `login_state`;
ALTER TABLE `trights` CHANGE `task_checkbox` `task_checkbox` INT(1) NOT NULL COMMENT 'Autorise les actions sur la sélection de plusieurs lignes dans la liste des tickets et des équipements';
ALTER TABLE `tparameters` ADD `user_validation` INT(1) NOT NULL AFTER `ticket_autoclose_state`;
ALTER TABLE `tparameters` ADD `user_validation_delay` INT(3) NOT NULL AFTER `user_validation`;
ALTER TABLE `tparameters` ADD `user_validation_perimeter` VARCHAR(10) NOT NULL AFTER `user_validation_delay`;
CREATE TABLE IF NOT EXISTS `tparameters_user_validation_exclusion` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `category` int(5) NOT NULL,
  `subcat` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `tparameters_user_validation_exclusion` ADD INDEX(`category`);
ALTER TABLE `tparameters_user_validation_exclusion` ADD INDEX(`subcat`);
ALTER TABLE `ttypes` ADD `user_validation` INT(1) NOT NULL AFTER `service`;
ALTER TABLE `tincidents` ADD `user_validation` INT(1) NOT NULL AFTER `state`;
ALTER TABLE `trights` ADD `ticket_user_validation` INT(1) NOT NULL COMMENT 'Affiche le champ validation demandeur sur les tickets' AFTER `ticket_state_disp`;
UPDATE `trights` SET `ticket_user_validation`='2' WHERE `profile`='0' OR `profile`='4';
ALTER TABLE `tincidents` ADD `user_validation_date` DATE NOT NULL AFTER `user_validation`;
ALTER TABLE `trights` ADD `ticket_reopen` INT(1) NOT NULL COMMENT 'Affiche le bouton de ré-ouverture sur un ticket résolu' AFTER `ticket_close`;
UPDATE `tparameters` SET cron_daily=CURDATE();
ALTER TABLE `tparameters` ADD `cron_monthly` INT(2) NOT NULL AFTER `cron_daily`;
UPDATE `tparameters` SET `cron_monthly`=MONTH(CURRENT_DATE());