-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.40";
ALTER TABLE `tparameters` ADD `user_disable_attempt` INT(1) NOT NULL AFTER `user_limit_service`;
ALTER TABLE `tparameters` ADD `user_disable_attempt_number` INT(2) NOT NULL AFTER `user_disable_attempt`;
UPDATE `tparameters` SET `user_disable_attempt_number`='5';
ALTER TABLE `tusers` ADD `auth_attempt` INT(2) NOT NULL AFTER `last_login`;
ALTER TABLE `tusers` CHANGE `password` `password` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tthreads` ADD `dest_mail` VARCHAR(150) NOT NULL AFTER `private`;
ALTER TABLE `tincidents` CHANGE `id` `id` INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tusers` CHANGE `id` `id` INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tusers_agencies` CHANGE `user_id` `user_id` INT(10) NOT NULL;
ALTER TABLE `tusers_services` CHANGE `user_id` `user_id` INT(10) NOT NULL;