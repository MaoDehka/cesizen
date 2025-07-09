-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`='3.2.42';

CREATE TABLE `tusers_ip` (`id` INT(10) NOT NULL AUTO_INCREMENT , `user_id` INT(10) NOT NULL , `ip` VARCHAR(128) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `tusers_ip` ADD INDEX(`user_id`);

ALTER TABLE `tparameters` ADD `user_admin_ip` INT(1) NOT NULL AFTER `user_forgot_pwd`;