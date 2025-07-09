-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.2.1";
UPDATE `tparameters` SET `time_display_msg`="250";

UPDATE `tstates` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-primary text-white' WHERE `display`='badge badge-sm badge-primary arrowed-in';
UPDATE `tstates` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-warning text-white' WHERE `display`='badge badge-sm badge-warning arrowed-in';
UPDATE `tstates` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-success text-white' WHERE `display`='badge badge-sm badge-success arrowed arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-dark text-white' WHERE `display`='badge badge-sm badge-dark arrowed arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-danger text-white' WHERE `display`='badge badge-sm badge-danger arrowed-in arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-pink text-white' WHERE `display`='badge badge-sm badge-secondary arrowed arrowed-right arrowed-left';

UPDATE `tassets_state` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-info text-white' WHERE `display`='badge badge-sm badge-info arrowed-in';
UPDATE `tassets_state` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-success text-white' WHERE `display`='badge badge-sm badge-success arrowed arrowed-right arrowed-leftn';
UPDATE `tassets_state` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-success text-white' WHERE `display`='badge badge-sm badge-success arrowed arrowed-right arrowed-left';
UPDATE `tassets_state` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-warning text-white' WHERE `display`='badge badge-sm badge-warning arrowed-in arrowed-right arrowed-in arrowed-left';
UPDATE `tassets_state` SET `display`='badge text-75 border-l-3 brc-black-tp8 bgc-dark text-white' WHERE `display`='badge badge-sm badge-dark arrowed arrowed-right arrowed-left';

ALTER TABLE `tparameters` ADD `log` INT(1) NOT NULL AFTER `restrict_ip`;

CREATE TABLE IF NOT EXISTS `tlogs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `message` varchar(254) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
ALTER TABLE `tlogs` ADD `user` INT(10) NOT NULL AFTER `message`;
ALTER TABLE `tlogs` ADD `ip` VARCHAR(45) NOT NULL AFTER `user`;
ALTER TABLE `tlogs` ADD INDEX(`user`);
ALTER TABLE `ttoken` CHANGE `id` `id` INT(100) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tparameters` CHANGE `imap_blacklist` `imap_blacklist` VARCHAR(1000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;