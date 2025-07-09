-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.44";
ALTER TABLE `trights` ADD `admin_backup` INT(1) NOT NULL COMMENT 'Affiche menu sauvegarde' AFTER `admin_user_view`;
UPDATE `trights` SET `admin_backup`=2 WHERE profile=4;
ALTER TABLE `tparameters` CHANGE `imap_blacklist` `imap_blacklist` VARCHAR(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `tparameters` ADD `ldap_login_field` VARCHAR(20) NOT NULL AFTER `ldap_url`;
UPDATE `tparameters` SET `ldap_login_field`='SamAcountName';