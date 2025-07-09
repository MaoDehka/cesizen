-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.14';

-- update default mail color
UPDATE `tparameters` SET `mail_color_title`='4AA0DF' WHERE `mail_color_title`='3b91d1';
UPDATE `tparameters` SET `mail_color_text`='8492A6' WHERE `mail_color_text`='3b91d1';
UPDATE `tparameters` SET `mail_color_bg`='F8F8F8' WHERE `mail_color_bg`='f5f5f5';

ALTER TABLE `trights` ADD `stat_ticket_time_by_states` INT(1) NOT NULL COMMENT 'Affiche le tableau de répartition des temps par status dans les statistiques des tickets' AFTER `stat`;
UPDATE `trights` SET `stat_ticket_time_by_states`='2' WHERE `id`='5' OR `id`='1';
ALTER TABLE `tparameters` CHANGE `login_message_info` `login_message_info` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` CHANGE `login_message_alert` `login_message_alert` MEDIUMTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;