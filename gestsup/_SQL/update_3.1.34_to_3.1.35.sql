-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.35";

UPDATE `tcompany` SET id='0' WHERE name='Aucune';
ALTER TABLE `tparameters` CHANGE `ldap_password` `ldap_password` VARCHAR(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` CHANGE `imap_password` `imap_password` VARCHAR(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;