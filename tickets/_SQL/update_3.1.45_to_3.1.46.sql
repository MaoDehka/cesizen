-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.46";

ALTER TABLE `tparameters` ADD `user_forgot_pwd` INT(1) NOT NULL AFTER `user_password_policy_expiration`;
ALTER TABLE `ttoken` ADD `user_id` INT(10) NOT NULL AFTER `ticket_id`;

ALTER TABLE `tusers` ADD INDEX(`company`);
ALTER TABLE `tincidents` CHANGE `technician` `technician` INT(10) NOT NULL;
ALTER TABLE `tincidents` CHANGE `type` `type` INT(3) NOT NULL DEFAULT '0';
UPDATE `tincidents` SET user='0' WHERE user='';
ALTER TABLE `tincidents` CHANGE `user` `user` INT(20) NOT NULL;
ALTER TABLE `tincidents` CHANGE `user` `user` INT(10) NOT NULL;
ALTER TABLE `tincidents` DROP INDEX `user`;
ALTER TABLE `tincidents` ADD INDEX(`user`);
ALTER TABLE `tincidents` ADD INDEX(`u_service`);
ALTER TABLE `tincidents` ADD INDEX(`category`);
ALTER TABLE `tincidents` ADD INDEX(`subcat`);
ALTER TABLE `tincidents` ADD INDEX(`u_agency`);
ALTER TABLE `tincidents` ADD INDEX(`priority`);
ALTER TABLE `tincidents` ADD INDEX(`criticality`);
ALTER TABLE `tincidents` ADD INDEX(`sender_service`);
ALTER TABLE `tincidents` ADD INDEX(`type`);
ALTER TABLE `tthreads` ADD INDEX(`author`);
ALTER TABLE `tprojects` ENGINE = InnoDB;
ALTER TABLE `tprojects_task` ENGINE = InnoDB;
ALTER TABLE `tusers` CHANGE `login` `login` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `tparameters` ADD `restrict_ip` VARCHAR(255) NOT NULL AFTER `server_timezone`;