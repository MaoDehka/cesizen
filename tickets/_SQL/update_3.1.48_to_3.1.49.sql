-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.49";

CREATE TABLE IF NOT EXISTS `tattachments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) NOT NULL,
  `procedure_id` int(10) NOT NULL,
  `asset_id` int(10) NOT NULL,
  `storage_filename` varchar(255) NOT NULL,
  `real_filename` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `procedure_id` (`procedure_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `tattachments` ADD `uid` VARCHAR(255) NOT NULL AFTER `id`;

INSERT INTO `tattachments` (`ticket_id`, `storage_filename`, `real_filename`)
SELECT 
     `id`,`img1`,`img1`
FROM `tincidents` WHERE `img1`!='';

INSERT INTO `tattachments` (`ticket_id`, `storage_filename`, `real_filename`)
SELECT 
     `id`,`img2`,`img2`
FROM `tincidents` WHERE `img2`!='';

INSERT INTO `tattachments` (`ticket_id`, `storage_filename`, `real_filename`)
SELECT 
     `id`,`img3`,`img3`
FROM `tincidents` WHERE `img3`!='';

INSERT INTO `tattachments` (`ticket_id`, `storage_filename`, `real_filename`)
SELECT 
     `id`,`img4`,`img4`
FROM `tincidents` WHERE `img4`!='';

INSERT INTO `tattachments` (`ticket_id`, `storage_filename`, `real_filename`)
SELECT 
     `id`,`img5`,`img5`
FROM `tincidents` WHERE `img5`!='';

ALTER TABLE `trights` ADD `ticket_attachment_delete` INT(1) NOT NULL COMMENT 'Autorise la suppression de pièce jointe sur un ticket' AFTER `ticket_attachment`;
UPDATE `trights` SET `ticket_attachment_delete`=2 WHERE `id`=1 OR `id`=5;
ALTER TABLE `tparameters` CHANGE `company` `company` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;