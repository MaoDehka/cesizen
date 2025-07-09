-- SQL Update for GestSup !!! If you are not in lastest version, all previous scripts must be passed before !!! ;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET default_storage_engine=INNODB;

-- update GestSup version number
UPDATE `tparameters` SET `version`="3.1.47";
UPDATE `tservices` SET `name` = 'Aucun' WHERE `tservices`.`id` = 0;
ALTER TABLE `tparameters` ADD `ticket_cat_auto_attribute` INT(1) NOT NULL AFTER `ticket_autoclose_state`;

ALTER TABLE `tcategory` ADD `technician` INT(10) NOT NULL AFTER `service`;
ALTER TABLE `tcategory` ADD `technician_group` INT(3) NOT NULL AFTER `technician`;
ALTER TABLE `tcategory` ADD INDEX(`technician`);
ALTER TABLE `tcategory` ADD INDEX(`technician_group`);

ALTER TABLE `tsubcat` ADD `technician` INT(10) NOT NULL AFTER `name`;
ALTER TABLE `tsubcat` ADD `technician_group` INT(3) NOT NULL AFTER `technician`;
ALTER TABLE `tsubcat` ADD INDEX(`technician`);
ALTER TABLE `tsubcat` ADD INDEX(`technician_group`);

INSERT INTO `tgroups` (`id`, `name`, `type`, `service`, `disable`) VALUES (NULL, 'Aucun', '0', '0', '1');
UPDATE `tgroups` SET `id`='0' WHERE `name`='Aucun';

ALTER TABLE `tassets` ADD INDEX(`type`);
ALTER TABLE `tassets` ADD INDEX(`manufacturer`);
ALTER TABLE `tassets` ADD INDEX(`model`);
ALTER TABLE `tassets` ADD INDEX(`user`);
ALTER TABLE `tassets` ADD INDEX(`state`);
ALTER TABLE `tassets` ADD INDEX(`department`);
ALTER TABLE `tassets` ADD INDEX(`location`);
ALTER TABLE `tassets` ADD INDEX(`technician`);
ALTER TABLE `tassets` ADD INDEX(`maintenance`);
ALTER TABLE `tassets_iface` ADD INDEX(`role_id`);

ALTER TABLE `tavailability` ADD INDEX(`category`);
ALTER TABLE `tavailability` ADD INDEX(`subcat`);
ALTER TABLE `tavailability_dep` ADD INDEX(`category`);
ALTER TABLE `tavailability_dep` ADD INDEX(`subcat`);
ALTER TABLE `tavailability_target` ADD INDEX(`subcat`);

ALTER TABLE `tcategory` ADD INDEX(`service`);

ALTER TABLE `tcriticality` ADD INDEX(`service`);

ALTER TABLE `tevents` ADD INDEX(`technician`);
ALTER TABLE `tevents` ADD INDEX(`incident`);

ALTER TABLE `tgroups` ADD INDEX(`service`);
ALTER TABLE `tgroups_assoc` ADD INDEX(`group`);
ALTER TABLE `tgroups_assoc` ADD INDEX(`user`);

ALTER TABLE `tincidents` ADD INDEX(`title`);
ALTER TABLE `tincidents` ADD INDEX(`t_group`);
ALTER TABLE `tincidents` ADD INDEX(`u_group`);
ALTER TABLE `tincidents` ADD INDEX(`creator`);
ALTER TABLE `tincidents` ADD INDEX(`place`);
ALTER TABLE `tincidents` ADD INDEX(`asset_id`);

ALTER TABLE `tmails` ADD INDEX(`incident`);

ALTER TABLE `tparameters_imap_multi_mailbox` ADD INDEX(`service_id`);

ALTER TABLE `tpriority` ADD INDEX(`service`);

ALTER TABLE `tprocedures` ADD INDEX(`category`);
ALTER TABLE `tprocedures` ADD INDEX(`subcat`);
ALTER TABLE `tprocedures` ADD INDEX(`company_id`);

ALTER TABLE `tprojects_task` ADD INDEX(`project_id`);
ALTER TABLE `tprojects_task` ADD INDEX(`ticket_id`);

ALTER TABLE `tsubcat` ADD INDEX(`cat`);

ALTER TABLE `tsurvey_answers` ADD INDEX(`ticket_id`);
ALTER TABLE `tsurvey_answers` ADD INDEX(`question_id`);

ALTER TABLE `ttemplates` ADD INDEX(`incident`);

ALTER TABLE `tthreads` ADD INDEX(`tech1`);
ALTER TABLE `tthreads` ADD INDEX(`tech2`);
ALTER TABLE `tthreads` ADD INDEX(`group1`);
ALTER TABLE `tthreads` ADD INDEX(`group2`);
ALTER TABLE `tthreads` ADD INDEX(`user`);

ALTER TABLE `ttoken` ADD INDEX(`ticket_id`);
ALTER TABLE `ttoken` ADD INDEX(`user_id`);
ALTER TABLE `ttypes` ADD INDEX(`service`);

ALTER TABLE `tusers_agencies` ADD INDEX(`user_id`);
ALTER TABLE `tusers_agencies` ADD INDEX(`agency_id`);

ALTER TABLE `tusers_services` ADD INDEX(`user_id`);
ALTER TABLE `tusers_services` ADD INDEX(`service_id`);

ALTER TABLE `tusers_tech` ADD INDEX(`user`);
ALTER TABLE `tusers_tech` ADD INDEX(`tech`);

ALTER TABLE `tviews` ADD INDEX(`category`);
ALTER TABLE `tviews` ADD INDEX(`subcat`);