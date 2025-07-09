-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.38";
ALTER TABLE `ttoken` CHANGE `id` `id` INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tparameters` ADD `project` INT(1) NOT NULL AFTER `survey_auto_close_ticket`;

DROP TABLE IF EXISTS `tprojects`;
CREATE TABLE IF NOT EXISTS `tprojects` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `tprojects_task`;
CREATE TABLE IF NOT EXISTS `tprojects_task` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `number` int(3) NOT NULL,
  `project_id` int(5) NOT NULL,
  `ticket_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `trights` ADD `project` INT(1) NOT NULL COMMENT 'Affiche le menu projet' AFTER `planning`;
UPDATE `trights` SET `project`=2 WHERE id='5' OR id='1' OR id='4';

UPDATE `tevents` SET `allday` = 'false' WHERE `allday`='[object Ob';