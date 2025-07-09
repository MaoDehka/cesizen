-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.18';

ALTER TABLE `tusers_tech` ADD `user_group` INT(5) NOT NULL AFTER `user`;
ALTER TABLE `tusers_tech` ADD INDEX(`user_group`);

UPDATE `tstates` SET `name`='Attente retour' WHERE `name`='Attente Retour';