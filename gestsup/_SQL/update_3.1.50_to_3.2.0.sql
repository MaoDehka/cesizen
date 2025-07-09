-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.2.0";
UPDATE `tparameters` SET `time_display_msg`='0';
UPDATE `tstates` SET `display`="badge badge-sm badge-primary arrowed-in" WHERE id=1;
UPDATE `tstates` SET `display`="badge badge-sm badge-warning arrowed-in" WHERE id=2;
UPDATE `tstates` SET `display`="badge badge-sm badge-success arrowed arrowed-right arrowed-left" WHERE id=3;
UPDATE `tstates` SET `display`="badge badge-sm badge-dark arrowed arrowed-right arrowed-left" WHERE id=4;
UPDATE `tstates` SET `display`="badge badge-sm badge-danger arrowed-in arrowed-right arrowed-left" WHERE id=5;
UPDATE `tstates` SET `display`="badge badge-sm badge-secondary arrowed arrowed-right arrowed-left" WHERE id=6;
UPDATE `tstates` SET `display`='badge badge-sm badge-danger arrowed-in arrowed-right arrowed-left' WHERE `display`='label label-sm label-important arrowed-in arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge badge-sm badge-warning arrowed-in' WHERE `display`='label label-sm label-warning arrowed-in';
UPDATE `tstates` SET `display`='badge badge-sm badge-success arrowed arrowed-right arrowed-left' WHERE `display`='label label-sm label-success arrowed arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge badge-sm badge-primary arrowed arrowed-right arrowed-left' WHERE `display`='label label-sm label-primary arrowed arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge badge-sm badge-light arrowed arrowed-right arrowed-left' WHERE `display`='label label-sm label-light arrowed arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge badge-sm badge-inverse arrowed arrowed-right arrowed-left' WHERE `display`='label label-sm label-inverse arrowed arrowed-right arrowed-left';
UPDATE `tstates` SET `display`='badge badge-sm badge-important arrowed-in arrowed-right arrowed-left' WHERE `display`='label label-sm label-important arrowed-in arrowed-right rrowed-left';
UPDATE `tstates` SET `display`='badge badge-sm badge-primary arrowed-in' WHERE `display`='label label-sm label-info arrowed-in';
UPDATE `tstates` SET `display`='badge badge-sm badge-secondary arrowed arrowed-right arrowed-left' WHERE `display`='label label-sm label-pink arrowed arrowed-right arrowed-left';

UPDATE `tevents` SET `classname`='badge-success' WHERE classname='label-success';
UPDATE `tevents` SET `classname`='badge-warning' WHERE classname='label-warning';
UPDATE `tevents` SET `classname`='badge-primary' WHERE classname='';

UPDATE `tassets_state` SET `display`="badge badge-sm badge-info arrowed-in" WHERE id=1;
UPDATE `tassets_state` SET `display`="badge badge-sm badge-success arrowed arrowed-right arrowed-leftn" WHERE id=2;
UPDATE `tassets_state` SET `display`="badge badge-sm badge-warning arrowed-in arrowed-right arrowed-in arrowed-left" WHERE id=3;
UPDATE `tassets_state` SET `display`="badge badge-sm badge-dark arrowed arrowed-right arrowed-left" WHERE id=4;

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

UPDATE `ttypes_answer` SET id='0' WHERE name='Aucune';

INSERT INTO `tusers` (`login`,`firstname`,`lastname`,`profile`,`disable`) VALUES ('delete_user_gs','Utilisateur','Supprimé','2','1');

ALTER TABLE `tstates` ADD `meta` INT NOT NULL AFTER `display`;
UPDATE `tstates` SET `meta`='1' WHERE `id`='1' OR `id`='2' OR `id`='6';

ALTER TABLE `tcompany` ADD `SIRET` VARCHAR(20) NOT NULL AFTER `country`;
ALTER TABLE `tcompany` ADD `TVA` VARCHAR(20) NOT NULL AFTER `SIRET`;

ALTER TABLE `tparameters` ADD `company_limit_hour` INT(1) NOT NULL AFTER `company_limit_ticket`;
ALTER TABLE `tcompany` ADD `limit_hour_number` INT(5) NOT NULL AFTER `limit_ticket_date_start`;
ALTER TABLE `tcompany` ADD `limit_hour_days` INT(5) NOT NULL AFTER `limit_hour_number`;
ALTER TABLE `tcompany` ADD `limit_hour_date_start` DATE NOT NULL AFTER `limit_hour_days`;
UPDATE `tusers` SET `company`='0' WHERE `company` NOT IN (SELECT `id` FROM `tcompany`);