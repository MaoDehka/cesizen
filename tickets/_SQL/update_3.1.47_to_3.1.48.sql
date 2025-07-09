-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.48";

ALTER TABLE `tincidents` DROP INDEX `title`;
UPDATE `tassets` SET id='0' WHERE netbios='Aucun';

ALTER TABLE `trights` ADD `ticket_priority_service_limit` INT(1) NOT NULL COMMENT 'Affiche uniquement les priorités associées au service' AFTER `ticket_priority_mandatory`;
UPDATE `trights` SET ticket_priority_service_limit='2';
ALTER TABLE `trights` ADD `ticket_criticality_service_limit` INT(1) NOT NULL COMMENT 'Affiche uniquement les criticités associées au service' AFTER `ticket_criticality_mandatory`;
UPDATE `trights` SET ticket_criticality_service_limit='2';

CREATE TABLE IF NOT EXISTS `ttypes_answer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT INTO `ttypes_answer` (`id`, `name`, `disable`) VALUES (NULL, 'Aucune', '0');
UPDATE `ttypes_answer` SET `id`='0' WHERE `name`='Aucun';

ALTER TABLE `trights` ADD `ticket_type_answer_disp` INT(1) NOT NULL COMMENT 'Affiche le champ type de réponse sur le ticket' AFTER `ticket_type_mandatory`;
ALTER TABLE `tincidents` ADD `type_answer` INT(10) NOT NULL AFTER `type`;
ALTER TABLE `tincidents` ADD INDEX(`type_answer`);

ALTER TABLE `tcompany` CHANGE `name` `name` VARCHAR(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;