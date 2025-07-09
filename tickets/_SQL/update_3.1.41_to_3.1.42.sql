-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.42";
ALTER TABLE `ttoken` CHANGE `ticket_id` `ticket_id` INT(10) NOT NULL;
ALTER TABLE `tplaces` CHANGE `name` `name` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` ADD `update_menu` INT(1) NOT NULL DEFAULT '1' AFTER `version`;
ALTER TABLE `tparameters` ADD `user_password_policy` INT(1) NOT NULL AFTER `user_disable_attempt_number`;
ALTER TABLE `tparameters` ADD `user_password_policy_min_lenght` INT(2) NOT NULL AFTER `user_password_policy`;
ALTER TABLE `tparameters` ADD `user_password_policy_special_char` INT(1) NOT NULL AFTER `user_password_policy_min_lenght`;
ALTER TABLE `tparameters` ADD `user_password_policy_min_maj` INT(1) NOT NULL AFTER `user_password_policy_special_char`;
ALTER TABLE `tparameters` ADD `user_password_policy_expiration` INT(1) NOT NULL AFTER `user_password_policy_min_maj`;
ALTER TABLE `tusers` ADD `last_pwd_chg` DATE NOT NULL AFTER `last_login`;