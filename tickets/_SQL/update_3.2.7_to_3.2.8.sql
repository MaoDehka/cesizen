-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.8';
ALTER TABLE `tlogs` CHANGE `message` `message` VARCHAR(512) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

CREATE TABLE IF NOT EXISTS `tplugins` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `label` varchar(128) NOT NULL,
  `icon` varchar(64) NOT NULL,
  `version` varchar(10) NOT NULL,
  `enable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `tplugins` ADD `description` VARCHAR(254) NOT NULL AFTER `label`;

INSERT INTO `tplugins` (`name`, `label`, `icon`, `version`) VALUES ('availability','Disponibilité','clock','1.1');
UPDATE `tplugins` SET `enable`='1' WHERE `name`='availability' AND (SELECT `availability` FROM `tparameters`) = 1;
UPDATE `tplugins` SET `description`='Active le suivi des catégories afin de produire des statistiques de disponibilité' WHERE `name`='availability';
ALTER TABLE `tlogs` CHANGE `message` `message` VARCHAR(1024) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;