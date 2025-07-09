-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.37";

-- update old events
UPDATE `tevents` SET `classname`='label-warning' WHERE `type`='1' AND `classname`='';
UPDATE `tevents`,`tincidents` SET tevents.`title`=CONCAT('Rappel : ',tincidents.title) WHERE tevents.incident=tincidents.id AND tevents.`title`='' AND tevents.`type`='1';