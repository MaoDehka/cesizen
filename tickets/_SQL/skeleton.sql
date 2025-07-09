-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 14 juin 2024 à 14:50
-- Version du serveur : 11.4.2-MariaDB
-- Version de PHP : 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `skelton`
--

-- --------------------------------------------------------

--
-- Structure de la table `tagencies`
--

DROP TABLE IF EXISTS `tagencies`;
CREATE TABLE IF NOT EXISTS `tagencies` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `ldap_guid` varchar(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tagencies`
--

INSERT INTO `tagencies` (`id`, `name`, `mail`, `ldap_guid`, `disable`) VALUES
(0, 'Aucune', '', '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tassets`
--

DROP TABLE IF EXISTS `tassets`;
CREATE TABLE IF NOT EXISTS `tassets` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `sn_internal` varchar(128) NOT NULL,
  `sn_manufacturer` varchar(128) NOT NULL,
  `sn_indent` varchar(128) NOT NULL,
  `netbios` varchar(128) NOT NULL,
  `description` varchar(500) NOT NULL,
  `type` int(5) NOT NULL,
  `manufacturer` int(5) NOT NULL,
  `model` int(5) NOT NULL,
  `user` int(5) NOT NULL,
  `state` int(5) NOT NULL,
  `department` int(5) NOT NULL,
  `date_install` date NOT NULL,
  `date_stock` date NOT NULL,
  `date_standbye` date NOT NULL,
  `date_recycle` date NOT NULL,
  `date_end_warranty` date NOT NULL,
  `date_last_ping` date NOT NULL,
  `location` int(5) NOT NULL,
  `socket` varchar(50) NOT NULL,
  `technician` int(5) NOT NULL,
  `maintenance` int(10) NOT NULL,
  `virtualization` int(1) NOT NULL,
  `net_scan` int(1) NOT NULL DEFAULT 1,
  `discover_net_scan` int(1) NOT NULL,
  `discover_import_csv` int(1) NOT NULL,
  `uuid` varchar(128) NOT NULL,
  `entra_id` varchar(256) NOT NULL,
  `entra_id_tenant_id` varchar(256) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `manufacturer` (`manufacturer`),
  KEY `model` (`model`),
  KEY `user` (`user`),
  KEY `state` (`state`),
  KEY `department` (`department`),
  KEY `location` (`location`),
  KEY `technician` (`technician`),
  KEY `maintenance` (`maintenance`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets`
--

INSERT INTO `tassets` (`id`, `sn_internal`, `sn_manufacturer`, `sn_indent`, `netbios`, `description`, `type`, `manufacturer`, `model`, `user`, `state`, `department`, `date_install`, `date_stock`, `date_standbye`, `date_recycle`, `date_end_warranty`, `date_last_ping`, `location`, `socket`, `technician`, `maintenance`, `virtualization`, `net_scan`, `discover_net_scan`, `discover_import_csv`, `uuid`, `entra_id`, `entra_id_tenant_id`, `disable`) VALUES
(0, '', '', '', 'Aucun', '', 0, 0, 0, 0, 0, 0, '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', 0, '', 0, 0, 0, 1, 0, 0, '', '', '', 1);

-- --------------------------------------------------------

--
-- Structure de la table `tassets_iface`
--

DROP TABLE IF EXISTS `tassets_iface`;
CREATE TABLE IF NOT EXISTS `tassets_iface` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `role_id` int(5) NOT NULL,
  `asset_id` int(10) NOT NULL,
  `netbios` varchar(200) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `mac` varchar(20) NOT NULL,
  `date_ping_ok` datetime NOT NULL,
  `date_ping_ko` datetime NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tassets_iface_role`
--

DROP TABLE IF EXISTS `tassets_iface_role`;
CREATE TABLE IF NOT EXISTS `tassets_iface_role` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets_iface_role`
--

INSERT INTO `tassets_iface_role` (`id`, `name`, `disable`) VALUES
(1, 'LAN', 0),
(2, 'WIFI', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tassets_location`
--

DROP TABLE IF EXISTS `tassets_location`;
CREATE TABLE IF NOT EXISTS `tassets_location` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets_location`
--

INSERT INTO `tassets_location` (`id`, `name`, `disable`) VALUES
(0, 'Aucune', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tassets_manufacturer`
--

DROP TABLE IF EXISTS `tassets_manufacturer`;
CREATE TABLE IF NOT EXISTS `tassets_manufacturer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets_manufacturer`
--

INSERT INTO `tassets_manufacturer` (`id`, `name`) VALUES
(1, 'Dell');

-- --------------------------------------------------------

--
-- Structure de la table `tassets_model`
--

DROP TABLE IF EXISTS `tassets_model`;
CREATE TABLE IF NOT EXISTS `tassets_model` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` int(5) NOT NULL,
  `manufacturer` int(5) NOT NULL,
  `image` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `ip` int(1) NOT NULL,
  `wifi` int(1) NOT NULL,
  `warranty` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `manufacturer` (`manufacturer`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets_model`
--

INSERT INTO `tassets_model` (`id`, `type`, `manufacturer`, `image`, `name`, `ip`, `wifi`, `warranty`) VALUES
(1, 1, 1, '3020.jpg', 'Optiplex 3020', 1, 0, 3);

-- --------------------------------------------------------

--
-- Structure de la table `tassets_network`
--

DROP TABLE IF EXISTS `tassets_network`;
CREATE TABLE IF NOT EXISTS `tassets_network` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `network` varchar(45) NOT NULL,
  `netmask` varchar(45) NOT NULL,
  `scan` int(1) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tassets_state`
--

DROP TABLE IF EXISTS `tassets_state`;
CREATE TABLE IF NOT EXISTS `tassets_state` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `order` int(3) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(50) NOT NULL,
  `block_ip_search` int(1) NOT NULL,
  `disable` int(1) NOT NULL,
  `display` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets_state`
--

INSERT INTO `tassets_state` (`id`, `order`, `name`, `description`, `block_ip_search`, `disable`, `display`) VALUES
(1, 1, 'Stock', 'Équipement en stock', 0, 0, 'badge text-75 border-l-3 brc-black-tp8 bgc-info text-white'),
(2, 2, 'Installé', 'Équipement installé en production', 1, 0, 'badge text-75 border-l-3 brc-black-tp8 bgc-success text-white'),
(3, 3, 'Standbye', 'Équipement de coté', 0, 0, 'badge text-75 border-l-3 brc-black-tp8 bgc-warning text-white'),
(4, 4, 'Recyclé', 'Équipement recyclé, jeté', 0, 0, 'badge text-75 border-l-3 brc-black-tp8 bgc-dark text-white');

-- --------------------------------------------------------

--
-- Structure de la table `tassets_thread`
--

DROP TABLE IF EXISTS `tassets_thread`;
CREATE TABLE IF NOT EXISTS `tassets_thread` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset` int(10) NOT NULL,
  `text` varchar(5000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `asset` (`asset`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tassets_type`
--

DROP TABLE IF EXISTS `tassets_type`;
CREATE TABLE IF NOT EXISTS `tassets_type` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `virtualization` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tassets_type`
--

INSERT INTO `tassets_type` (`id`, `name`, `virtualization`) VALUES
(1, 'PC', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tattachments`
--

DROP TABLE IF EXISTS `tattachments`;
CREATE TABLE IF NOT EXISTS `tattachments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) NOT NULL,
  `ticket_id` int(10) NOT NULL,
  `procedure_id` int(10) NOT NULL,
  `asset_id` int(10) NOT NULL,
  `storage_filename` varchar(255) NOT NULL,
  `real_filename` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `procedure_id` (`procedure_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tauth_attempts`
--

DROP TABLE IF EXISTS `tauth_attempts`;
CREATE TABLE IF NOT EXISTS `tauth_attempts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `ip` varchar(40) NOT NULL,
  `attempts` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tavailability`
--

DROP TABLE IF EXISTS `tavailability`;
CREATE TABLE IF NOT EXISTS `tavailability` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `category` int(5) NOT NULL,
  `subcat` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `subcat` (`subcat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tavailability_dep`
--

DROP TABLE IF EXISTS `tavailability_dep`;
CREATE TABLE IF NOT EXISTS `tavailability_dep` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `category` int(5) NOT NULL,
  `subcat` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `subcat` (`subcat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tavailability_target`
--

DROP TABLE IF EXISTS `tavailability_target`;
CREATE TABLE IF NOT EXISTS `tavailability_target` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `subcat` int(5) NOT NULL,
  `target` float NOT NULL,
  `year` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `subcat` (`subcat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tcategory`
--

DROP TABLE IF EXISTS `tcategory`;
CREATE TABLE IF NOT EXISTS `tcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `service` int(5) NOT NULL,
  `technician` int(10) NOT NULL,
  `technician_group` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `technician` (`technician`),
  KEY `technician_group` (`technician_group`),
  KEY `service` (`service`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tcategory`
--

INSERT INTO `tcategory` (`id`, `number`, `name`, `service`, `technician`, `technician_group`) VALUES
(0, 0, 'Aucune', 0, 0, 0),
(1, 0, 'Application', 0, 0, 0),
(2, 0, 'Materiel', 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `tcompany`
--

DROP TABLE IF EXISTS `tcompany`;
CREATE TABLE IF NOT EXISTS `tcompany` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `address` varchar(256) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(100) NOT NULL,
  `legal_status` varchar(256) NOT NULL,
  `SIRET` varchar(20) NOT NULL,
  `TVA` varchar(20) NOT NULL,
  `limit_ticket_number` int(5) NOT NULL DEFAULT 0,
  `limit_ticket_days` int(5) NOT NULL,
  `limit_ticket_date_start` date NOT NULL,
  `limit_hour_number` int(5) NOT NULL,
  `limit_hour_days` int(5) NOT NULL,
  `limit_hour_date_start` date NOT NULL,
  `comment` varchar(512) NOT NULL,
  `information_message` varchar(2048) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tcompany`
--

INSERT INTO `tcompany` (`id`, `name`, `address`, `zip`, `city`, `country`, `legal_status`, `SIRET`, `TVA`, `limit_ticket_number`, `limit_ticket_days`, `limit_ticket_date_start`, `limit_hour_number`, `limit_hour_days`, `limit_hour_date_start`, `comment`, `information_message`, `disable`) VALUES
(0, 'Aucune', '', '', '', '', '', '', '', 0, 0, '0000-00-00', 0, 0, '0000-00-00', '', '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tcriticality`
--

DROP TABLE IF EXISTS `tcriticality`;
CREATE TABLE IF NOT EXISTS `tcriticality` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `number` int(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(10) NOT NULL,
  `service` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service` (`service`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tcriticality`
--

INSERT INTO `tcriticality` (`id`, `number`, `name`, `color`, `service`) VALUES
(0, 0, 'Aucune', '#B0B0B0', 0),
(1, 0, 'Critique', '#d15b47', 0),
(2, 1, 'Grave', '#f89406', 0),
(3, 2, 'Moyenne', '#f8c806', 0),
(4, 3, 'Basse', '#82af6f', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tentra_tenant`
--

DROP TABLE IF EXISTS `tentra_tenant`;
CREATE TABLE IF NOT EXISTS `tentra_tenant` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `tenant_name` varchar(512) NOT NULL,
  `tenant_id` varchar(128) NOT NULL,
  `client_id` varchar(128) NOT NULL,
  `client_secret` varchar(128) NOT NULL,
  `group_filter` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tevents`
--

DROP TABLE IF EXISTS `tevents`;
CREATE TABLE IF NOT EXISTS `tevents` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `technician` int(10) NOT NULL,
  `incident` int(10) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `allday` varchar(10) NOT NULL,
  `type` int(1) NOT NULL,
  `title` varchar(150) NOT NULL,
  `classname` varchar(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `technician` (`technician`),
  KEY `incident` (`incident`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tgroups`
--

DROP TABLE IF EXISTS `tgroups`;
CREATE TABLE IF NOT EXISTS `tgroups` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` int(1) NOT NULL,
  `service` int(5) NOT NULL,
  `disable` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service` (`service`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tgroups`
--

INSERT INTO `tgroups` (`id`, `name`, `type`, `service`, `disable`) VALUES
(0, 'Aucun', 0, 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `tgroups_assoc`
--

DROP TABLE IF EXISTS `tgroups_assoc`;
CREATE TABLE IF NOT EXISTS `tgroups_assoc` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `group` int(5) NOT NULL,
  `user` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group` (`group`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tincidents`
--

DROP TABLE IF EXISTS `tincidents`;
CREATE TABLE IF NOT EXISTS `tincidents` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` int(3) NOT NULL DEFAULT 0,
  `type_answer` int(10) NOT NULL,
  `technician` int(10) NOT NULL,
  `t_group` int(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  `user` int(10) NOT NULL,
  `observer1` int(10) NOT NULL,
  `observer2` int(10) NOT NULL,
  `observer3` int(10) NOT NULL,
  `u_group` int(5) NOT NULL,
  `u_service` int(5) NOT NULL,
  `u_agency` int(5) NOT NULL,
  `sender_service` int(5) NOT NULL,
  `date_create` datetime NOT NULL,
  `date_hope` date NOT NULL,
  `date_res` datetime NOT NULL,
  `date_modif` datetime NOT NULL,
  `billable` int(1) NOT NULL,
  `state` int(1) NOT NULL,
  `user_validation` int(1) NOT NULL,
  `user_validation_date` date NOT NULL,
  `priority` int(2) NOT NULL,
  `criticality` int(2) NOT NULL,
  `img1` varchar(500) NOT NULL,
  `img2` varchar(500) NOT NULL,
  `img3` varchar(500) NOT NULL,
  `img4` varchar(500) NOT NULL,
  `img5` varchar(500) NOT NULL,
  `time` int(10) NOT NULL,
  `time_hope` int(10) NOT NULL,
  `creator` int(3) NOT NULL,
  `category` int(3) NOT NULL,
  `subcat` int(3) NOT NULL,
  `techread` int(1) NOT NULL DEFAULT 1,
  `techread_date` datetime NOT NULL,
  `userread` int(1) NOT NULL DEFAULT 1,
  `template` int(1) NOT NULL,
  `disable` int(1) NOT NULL,
  `notify` int(1) NOT NULL,
  `place` int(5) NOT NULL,
  `asset_id` int(8) NOT NULL,
  `start_availability` datetime NOT NULL,
  `end_availability` datetime NOT NULL,
  `availability_planned` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `technician` (`technician`),
  KEY `user` (`user`),
  KEY `u_service` (`u_service`),
  KEY `category` (`category`),
  KEY `subcat` (`subcat`),
  KEY `u_agency` (`u_agency`),
  KEY `priority` (`priority`),
  KEY `criticality` (`criticality`),
  KEY `sender_service` (`sender_service`),
  KEY `type` (`type`),
  KEY `t_group` (`t_group`),
  KEY `u_group` (`u_group`),
  KEY `creator` (`creator`),
  KEY `place` (`place`),
  KEY `asset_id` (`asset_id`),
  KEY `type_answer` (`type_answer`),
  KEY `observer1` (`observer1`),
  KEY `observer2` (`observer2`),
  KEY `observer3` (`observer3`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tlogs`
--

DROP TABLE IF EXISTS `tlogs`;
CREATE TABLE IF NOT EXISTS `tlogs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `message` varchar(1024) NOT NULL,
  `user` int(10) NOT NULL,
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tmails`
--

DROP TABLE IF EXISTS `tmails`;
CREATE TABLE IF NOT EXISTS `tmails` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `incident` int(10) NOT NULL,
  `open` int(1) NOT NULL,
  `close` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `incident` (`incident`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tparameters`
--

DROP TABLE IF EXISTS `tparameters`;
CREATE TABLE IF NOT EXISTS `tparameters` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `company` varchar(100) DEFAULT NULL,
  `server_url` varchar(200) DEFAULT NULL,
  `server_private_key` varchar(40) NOT NULL,
  `server_timezone` varchar(100) NOT NULL,
  `server_language` varchar(10) NOT NULL,
  `server_date_install` date NOT NULL,
  `server_proxy_url` varchar(200) NOT NULL,
  `restrict_ip` varchar(255) NOT NULL,
  `log` int(1) NOT NULL,
  `version` varchar(10) NOT NULL,
  `last_version` varchar(16) NOT NULL,
  `system_error` int(1) NOT NULL,
  `system_warning` int(1) NOT NULL,
  `update_menu` int(1) NOT NULL DEFAULT 1,
  `update_channel` varchar(10) NOT NULL,
  `telemetry` int(11) NOT NULL,
  `timeout` int(5) NOT NULL,
  `cron_daily` date NOT NULL,
  `cron_monthly` int(2) NOT NULL,
  `maxline` int(4) NOT NULL,
  `mail` int(1) NOT NULL,
  `mail_smtp` varchar(100) DEFAULT NULL,
  `mail_smtp_class` varchar(15) NOT NULL DEFAULT 'isSMTP()',
  `mail_port` int(4) NOT NULL,
  `mail_ssl_check` int(1) NOT NULL,
  `mail_auth` varchar(10) DEFAULT NULL,
  `mail_auth_type` varchar(32) NOT NULL,
  `mail_oauth_client_id` varchar(128) NOT NULL,
  `mail_oauth_tenant_id` varchar(128) NOT NULL,
  `mail_oauth_client_secret` varchar(128) NOT NULL,
  `mail_oauth_refresh_token` mediumtext NOT NULL,
  `mail_secure` varchar(10) DEFAULT NULL,
  `mail_username` varchar(150) DEFAULT NULL,
  `mail_password` varchar(150) DEFAULT NULL,
  `mail_txt` varchar(300) NOT NULL,
  `mail_txt_end` varchar(500) NOT NULL,
  `mail_cc` varchar(150) NOT NULL,
  `mail_cc_tech` int(1) NOT NULL,
  `mail_cci` int(1) NOT NULL,
  `mail_from_name` varchar(60) NOT NULL,
  `mail_from_adr` varchar(200) NOT NULL,
  `mail_reply` varchar(16) NOT NULL,
  `mail_auto` int(1) NOT NULL,
  `mail_auto_user_modify` int(1) NOT NULL DEFAULT 0,
  `mail_auto_user_newticket` int(1) NOT NULL,
  `mail_auto_tech_modify` int(1) NOT NULL DEFAULT 0,
  `mail_auto_tech_attribution` int(1) NOT NULL,
  `mail_auto_type` int(1) NOT NULL,
  `mail_newticket` int(1) NOT NULL,
  `mail_newticket_address` varchar(200) NOT NULL,
  `mail_template` varchar(50) NOT NULL,
  `mail_color_title` varchar(6) NOT NULL,
  `mail_color_bg` varchar(6) NOT NULL,
  `mail_color_text` varchar(6) NOT NULL,
  `mail_link` int(1) NOT NULL,
  `mail_link_redirect_url` varchar(200) NOT NULL,
  `mail_order` int(1) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `user_advanced` int(1) NOT NULL,
  `user_register` int(1) NOT NULL,
  `user_limit_ticket` int(1) NOT NULL DEFAULT 0,
  `user_company_view` int(1) DEFAULT 0,
  `user_agency` int(1) NOT NULL,
  `user_limit_service` int(1) NOT NULL,
  `user_disable_attempt` int(1) NOT NULL,
  `user_disable_attempt_number` int(2) NOT NULL,
  `user_password_policy` int(1) NOT NULL,
  `user_password_policy_min_lenght` int(2) NOT NULL,
  `user_password_policy_special_char` int(1) NOT NULL,
  `user_password_policy_min_maj` int(1) NOT NULL,
  `user_password_policy_expiration` int(1) NOT NULL,
  `user_forgot_pwd` int(1) NOT NULL,
  `user_admin_ip` int(1) NOT NULL,
  `time_display_msg` int(5) NOT NULL,
  `auto_refresh` int(5) NOT NULL,
  `login_state` varchar(10) NOT NULL,
  `default_skin` varchar(10) NOT NULL,
  `login_message` int(1) NOT NULL,
  `login_message_info` mediumtext NOT NULL,
  `login_message_warning` mediumtext NOT NULL,
  `login_message_alert` mediumtext NOT NULL,
  `login_background` varchar(128) NOT NULL,
  `admin_message_alert` varchar(256) NOT NULL,
  `company_message` int(1) NOT NULL,
  `notify` int(1) NOT NULL,
  `ldap` int(1) NOT NULL,
  `ldap_auth` int(1) NOT NULL,
  `ldap_sso` int(1) NOT NULL,
  `ldap_type` int(1) NOT NULL,
  `ldap_service` int(1) NOT NULL,
  `ldap_service_url` varchar(500) NOT NULL,
  `ldap_agency` int(1) NOT NULL,
  `ldap_agency_url` varchar(500) NOT NULL,
  `ldap_server` varchar(100) NOT NULL,
  `ldap_port` int(5) NOT NULL,
  `ldap_domain` varchar(200) NOT NULL,
  `ldap_url` varchar(2000) NOT NULL,
  `ldap_login_field` varchar(20) NOT NULL,
  `ldap_user` varchar(100) NOT NULL,
  `ldap_password` varchar(150) NOT NULL,
  `ldap_disable_user` int(1) NOT NULL,
  `azure_ad` int(1) NOT NULL,
  `azure_ad_tenant_number` int(5) NOT NULL,
  `azure_ad_client_id` varchar(128) NOT NULL,
  `azure_ad_name` varchar(128) NOT NULL,
  `azure_ad_tenant_id` varchar(128) NOT NULL,
  `azure_ad_client_secret` varchar(128) NOT NULL,
  `azure_ad_group_filter` varchar(1024) NOT NULL,
  `azure_ad_name_2` varchar(128) NOT NULL,
  `azure_ad_client_id_2` varchar(128) NOT NULL,
  `azure_ad_tenant_id_2` varchar(128) NOT NULL,
  `azure_ad_client_secret_2` varchar(128) NOT NULL,
  `azure_ad_group_filter_2` varchar(1024) NOT NULL,
  `azure_ad_login_field` varchar(32) NOT NULL,
  `azure_ad_disable_user` int(1) NOT NULL,
  `azure_ad_sso` int(1) NOT NULL,
  `azure_ad_sso_hide_login` int(1) NOT NULL,
  `ocs` int(1) NOT NULL,
  `ocs_server_url` varchar(256) NOT NULL,
  `planning` int(1) NOT NULL,
  `planning_ics` int(1) NOT NULL,
  `debug` int(1) NOT NULL,
  `imap` int(1) NOT NULL,
  `imap_server` varchar(50) NOT NULL,
  `imap_auth_type` varchar(24) NOT NULL,
  `imap_port` varchar(50) NOT NULL,
  `imap_ssl_check` int(1) NOT NULL,
  `imap_user` varchar(50) NOT NULL,
  `imap_password` varchar(150) NOT NULL,
  `imap_date_create` varchar(32) NOT NULL,
  `imap_oauth_client_id` varchar(128) NOT NULL,
  `imap_oauth_tenant_id` varchar(128) NOT NULL,
  `imap_oauth_client_secret` varchar(128) NOT NULL,
  `imap_oauth_refresh_token` mediumtext NOT NULL,
  `imap_reply` int(1) NOT NULL,
  `imap_inbox` varchar(20) NOT NULL,
  `imap_blacklist` varchar(1000) NOT NULL,
  `imap_post_treatment` varchar(100) NOT NULL,
  `imap_post_treatment_folder` varchar(100) NOT NULL,
  `imap_mailbox_service` int(1) NOT NULL,
  `imap_from_adr_service` int(11) NOT NULL,
  `imap_auto_create_user` int(1) NOT NULL,
  `api` int(1) NOT NULL,
  `api_client_ip` varchar(512) NOT NULL,
  `api_key` varchar(1024) NOT NULL,
  `order` varchar(100) NOT NULL,
  `procedure` int(1) NOT NULL,
  `survey` int(1) NOT NULL,
  `survey_mail_text` varchar(500) NOT NULL,
  `survey_ticket_state` int(2) NOT NULL,
  `survey_auto_close_ticket` int(1) NOT NULL,
  `project` int(1) NOT NULL,
  `ticket_places` int(1) NOT NULL,
  `ticket_type` int(1) NOT NULL,
  `ticket_observer` int(1) NOT NULL,
  `ticket_default_state` int(1) NOT NULL,
  `ticket_autoclose` int(1) NOT NULL,
  `ticket_autoclose_delay` int(3) NOT NULL,
  `ticket_autoclose_state` int(1) NOT NULL,
  `user_validation` int(1) NOT NULL,
  `user_validation_delay` int(3) NOT NULL,
  `user_validation_perimeter` varchar(10) NOT NULL,
  `ticket_cat_auto_attribute` int(1) NOT NULL,
  `ticket_time_response_element` int(1) NOT NULL,
  `ticket_open_message` int(1) NOT NULL,
  `ticket_open_message_text` mediumtext NOT NULL,
  `ticket_recurrent_create` int(1) NOT NULL,
  `availability` int(1) NOT NULL,
  `availability_all_cat` int(1) NOT NULL,
  `availability_dep` int(1) NOT NULL,
  `availability_condition_type` varchar(20) NOT NULL,
  `availability_condition_value` int(4) NOT NULL,
  `asset` int(1) NOT NULL,
  `asset_ip` int(1) NOT NULL,
  `asset_warranty` int(1) NOT NULL,
  `asset_vnc_link` int(1) NOT NULL,
  `meta_state` int(1) NOT NULL,
  `company_limit_ticket` int(1) NOT NULL DEFAULT 0,
  `company_limit_hour` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=DYNAMIC;

--
-- Déchargement des données de la table `tparameters`
--

INSERT INTO `tparameters` (`id`, `company`, `server_url`, `server_private_key`, `server_timezone`, `server_language`, `server_date_install`, `server_proxy_url`, `restrict_ip`, `log`, `version`, `last_version`, `system_error`, `system_warning`, `update_menu`, `update_channel`, `telemetry`, `timeout`, `cron_daily`, `cron_monthly`, `maxline`, `mail`, `mail_smtp`, `mail_smtp_class`, `mail_port`, `mail_ssl_check`, `mail_auth`, `mail_auth_type`, `mail_oauth_client_id`, `mail_oauth_tenant_id`, `mail_oauth_client_secret`, `mail_oauth_refresh_token`, `mail_secure`, `mail_username`, `mail_password`, `mail_txt`, `mail_txt_end`, `mail_cc`, `mail_cc_tech`, `mail_cci`, `mail_from_name`, `mail_from_adr`, `mail_reply`, `mail_auto`, `mail_auto_user_modify`, `mail_auto_user_newticket`, `mail_auto_tech_modify`, `mail_auto_tech_attribution`, `mail_auto_type`, `mail_newticket`, `mail_newticket_address`, `mail_template`, `mail_color_title`, `mail_color_bg`, `mail_color_text`, `mail_link`, `mail_link_redirect_url`, `mail_order`, `logo`, `user_advanced`, `user_register`, `user_limit_ticket`, `user_company_view`, `user_agency`, `user_limit_service`, `user_disable_attempt`, `user_disable_attempt_number`, `user_password_policy`, `user_password_policy_min_lenght`, `user_password_policy_special_char`, `user_password_policy_min_maj`, `user_password_policy_expiration`, `user_forgot_pwd`, `user_admin_ip`, `time_display_msg`, `auto_refresh`, `login_state`, `default_skin`, `login_message`, `login_message_info`, `login_message_warning`, `login_message_alert`, `login_background`, `admin_message_alert`, `company_message`, `notify`, `ldap`, `ldap_auth`, `ldap_sso`, `ldap_type`, `ldap_service`, `ldap_service_url`, `ldap_agency`, `ldap_agency_url`, `ldap_server`, `ldap_port`, `ldap_domain`, `ldap_url`, `ldap_login_field`, `ldap_user`, `ldap_password`, `ldap_disable_user`, `azure_ad`, `azure_ad_tenant_number`, `azure_ad_client_id`, `azure_ad_name`, `azure_ad_tenant_id`, `azure_ad_client_secret`, `azure_ad_group_filter`, `azure_ad_name_2`, `azure_ad_client_id_2`, `azure_ad_tenant_id_2`, `azure_ad_client_secret_2`, `azure_ad_group_filter_2`, `azure_ad_login_field`, `azure_ad_disable_user`, `azure_ad_sso`, `azure_ad_sso_hide_login`, `ocs`, `ocs_server_url`, `planning`, `planning_ics`, `debug`, `imap`, `imap_server`, `imap_auth_type`, `imap_port`, `imap_ssl_check`, `imap_user`, `imap_password`, `imap_date_create`, `imap_oauth_client_id`, `imap_oauth_tenant_id`, `imap_oauth_client_secret`, `imap_oauth_refresh_token`, `imap_reply`, `imap_inbox`, `imap_blacklist`, `imap_post_treatment`, `imap_post_treatment_folder`, `imap_mailbox_service`, `imap_from_adr_service`, `imap_auto_create_user`, `api`, `api_client_ip`, `api_key`, `order`, `procedure`, `survey`, `survey_mail_text`, `survey_ticket_state`, `survey_auto_close_ticket`, `project`, `ticket_places`, `ticket_type`, `ticket_observer`, `ticket_default_state`, `ticket_autoclose`, `ticket_autoclose_delay`, `ticket_autoclose_state`, `user_validation`, `user_validation_delay`, `user_validation_perimeter`, `ticket_cat_auto_attribute`, `ticket_time_response_element`, `ticket_open_message`, `ticket_open_message_text`, `ticket_recurrent_create`, `availability`, `availability_all_cat`, `availability_dep`, `availability_condition_type`, `availability_condition_value`, `asset`, `asset_ip`, `asset_warranty`, `asset_vnc_link`, `meta_state`, `company_limit_ticket`, `company_limit_hour`) VALUES
(1, 'Société', 'http://gestsup', '', '', 'fr_FR', '0000-00-00', '', '', 1, '3.2.50', '', 0, 0, 1, 'stable', 0, 24, '2020-12-16', 12, 10, 0, '', 'isSMTP()', 25, 1, '0', 'login', '', '', '', '', '0', '', '', 'Bonjour, <br />Vous avez fait la demande suivante auprès du support :', '', '', 1, 0, 'Support exemple', '', 'sender', 0, 0, 0, 0, 0, 0, 0, '', 'default.htm', '4AA0DF', 'F8F8F8', '8492A6', 1, '', 0, '', 0, 0, 0, 0, 0, 0, 0, 5, 0, 0, 0, 0, 0, 0, 0, 250, 0, '1', '', 0, '', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, '', 0, '', 'localhost', 389, 'exemple.fr', 'cn=users', 'SamAcountName', '', '', 1, 0, 1, '', '', '', '', '', '', '', '', '', '', 'UserPrincipalName', 0, 0, 0, 0, '', 1, 0, 0, 0, '', '', '', 0, '', '', 'date_mail', '', '', '', '', 1, 'INBOX', '', '', '', 0, 0, 0, 0, '127.0.0.1', '', 'tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_create', 0, 0, 'Dans le cadre de l’amélioration de notre support merci de répondre au sondage suivant:', 0, 0, 0, 0, 0, 0, 5, 0, 0, 0, 0, 0, '', 0, 0, 0, '', 0, 0, 1, 0, 'criticality', 0, 0, 1, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `tparameters_imap_multi_mailbox`
--

DROP TABLE IF EXISTS `tparameters_imap_multi_mailbox`;
CREATE TABLE IF NOT EXISTS `tparameters_imap_multi_mailbox` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `mailbox_service_auth_type` varchar(24) NOT NULL,
  `mail` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `mailbox_service_oauth_client_id` varchar(128) NOT NULL,
  `mailbox_service_oauth_tenant_id` varchar(128) NOT NULL,
  `mailbox_service_oauth_client_secret` varchar(128) NOT NULL,
  `mailbox_service_oauth_refresh_token` mediumtext NOT NULL,
  `service_id` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tparameters_user_validation_exclusion`
--

DROP TABLE IF EXISTS `tparameters_user_validation_exclusion`;
CREATE TABLE IF NOT EXISTS `tparameters_user_validation_exclusion` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `category` int(5) NOT NULL,
  `subcat` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `subcat` (`subcat`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tplaces`
--

DROP TABLE IF EXISTS `tplaces`;
CREATE TABLE IF NOT EXISTS `tplaces` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tplaces`
--

INSERT INTO `tplaces` (`id`, `name`) VALUES
(0, 'Aucun');

-- --------------------------------------------------------

--
-- Structure de la table `tplugins`
--

DROP TABLE IF EXISTS `tplugins`;
CREATE TABLE IF NOT EXISTS `tplugins` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` varchar(254) NOT NULL,
  `icon` varchar(64) NOT NULL,
  `version` varchar(10) NOT NULL,
  `enable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tplugins`
--

INSERT INTO `tplugins` (`id`, `name`, `label`, `description`, `icon`, `version`, `enable`) VALUES
(1, 'availability', 'Disponibilité', 'Active le suivi des catégories afin de produire des statistiques de disponibilité', 'clock', '1.1', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tpriority`
--

DROP TABLE IF EXISTS `tpriority`;
CREATE TABLE IF NOT EXISTS `tpriority` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `number` int(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(15) NOT NULL,
  `service` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service` (`service`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tpriority`
--

INSERT INTO `tpriority` (`id`, `number`, `name`, `color`, `service`) VALUES
(0, 0, 'Aucune', '#B0B0B0', 0),
(1, 0, 'Urgent', '#d15b47', 0),
(2, 1, 'Très haute', '#f89406', 0),
(3, 2, 'Haute', '#f8c806', 0),
(4, 3, 'Moyenne', '#e7ef20', 0),
(5, 4, 'Basse', '#c2c921', 0),
(6, 5, 'Très basse', '#82af6f', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tprocedures`
--

DROP TABLE IF EXISTS `tprocedures`;
CREATE TABLE IF NOT EXISTS `tprocedures` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `category` int(5) NOT NULL,
  `subcat` int(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `text` mediumtext NOT NULL,
  `file1` varchar(30) NOT NULL,
  `company_id` int(5) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `subcat` (`subcat`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tprofiles`
--

DROP TABLE IF EXISTS `tprofiles`;
CREATE TABLE IF NOT EXISTS `tprofiles` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `level` int(10) NOT NULL,
  `img` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tprofiles`
--

INSERT INTO `tprofiles` (`id`, `name`, `level`, `img`) VALUES
(1, 'technicien', 0, 'technician.png'),
(2, 'utilisateur avec pouvoir', 1, 'poweruser.png'),
(3, 'utilisateur', 2, 'user.png'),
(4, 'superviseur', 3, 'supervisor.png'),
(5, 'administrateur', 4, 'admin.png');

-- --------------------------------------------------------

--
-- Structure de la table `tprojects`
--

DROP TABLE IF EXISTS `tprojects`;
CREATE TABLE IF NOT EXISTS `tprojects` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tprojects_task`
--

DROP TABLE IF EXISTS `tprojects_task`;
CREATE TABLE IF NOT EXISTS `tprojects_task` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `number` int(3) NOT NULL,
  `project_id` int(5) NOT NULL,
  `ticket_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `trights`
--

DROP TABLE IF EXISTS `trights`;
CREATE TABLE IF NOT EXISTS `trights` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `profile` int(5) NOT NULL,
  `search` int(1) NOT NULL COMMENT 'Affiche le champ de recherche',
  `task_checkbox` int(1) NOT NULL COMMENT 'Autorise les actions sur la sélection de plusieurs lignes dans la liste des tickets et des équipements',
  `procedure` int(1) NOT NULL COMMENT 'Affiche le menu procédure',
  `procedure_add` int(1) NOT NULL COMMENT 'Droit d''ajouter des procédures',
  `procedure_delete` int(1) NOT NULL COMMENT 'Droit de supprimer des procédures',
  `procedure_modify` int(1) NOT NULL COMMENT 'Modification des procédures',
  `procedure_company` int(1) NOT NULL COMMENT 'Affiche le champ société sur une procédure',
  `procedure_list_company_only` int(1) NOT NULL COMMENT 'Affiche uniquement les procédures de la société rattachée à l''utilisateur',
  `stat` int(1) NOT NULL COMMENT 'Affiche le menu Statistiques',
  `stat_ticket_time_by_states` int(1) NOT NULL COMMENT 'Affiche le tableau de répartition des temps par status dans les statistiques des tickets',
  `planning` int(1) NOT NULL COMMENT 'Affiche le menu Planning',
  `planning_tech_color` int(1) NOT NULL COMMENT 'Affiche la couleur du technicien dans le calendrier',
  `planning_direct_event` int(1) NOT NULL COMMENT 'Permets l''ajout d''évènement en cliquant sur le calendrier',
  `project` int(1) NOT NULL COMMENT 'Affiche le menu projet',
  `contract` int(1) NOT NULL COMMENT 'Affiche le menu contrat',
  `availability` int(1) NOT NULL COMMENT 'Affiche le menu Disponibilité',
  `asset` int(1) NOT NULL COMMENT 'Affiche le menu équipement',
  `asset_net_scan` int(1) NOT NULL COMMENT 'Affiche le bouton de désactivation du scan réseau pour cet équipement',
  `asset_delete` int(1) NOT NULL COMMENT 'Droit de suppression des équipements',
  `asset_virtualization_disp` int(1) NOT NULL COMMENT 'Affiche le champ équipement virtuel',
  `asset_location_disp` int(1) NOT NULL COMMENT 'Affiche le champ localisation sur un équipement',
  `asset_list_department_only` int(1) NOT NULL COMMENT 'Affiche uniquement les équipements du service auquel est rattaché l''utilisateur',
  `asset_list_company_only` int(1) NOT NULL COMMENT 'Affiche uniquement les équipements de la société rattachée à l''utilisateur',
  `asset_list_view_only` int(1) NOT NULL COMMENT 'Affiche uniquement la liste des équipements, sans droit d''éditer une fiche',
  `asset_list_col_location` int(1) NOT NULL COMMENT 'Affiche la colonne localisation dans la liste des tickets',
  `asset_list_col_sn_manufacturer` int(1) NOT NULL COMMENT 'Affiche la colonne numéro de série fabricant',
  `admin` int(1) NOT NULL COMMENT 'Affiche le menu Administration',
  `admin_groups` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Groupes uniquement',
  `admin_lists` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Listes uniquement',
  `admin_lists_category` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Listes > Catégories',
  `admin_lists_subcat` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Listes > Sous-catégories',
  `admin_lists_criticality` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Listes > Criticités',
  `admin_lists_priority` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Listes > Priorité',
  `admin_lists_type` int(1) NOT NULL COMMENT 'Affiche le menu Administration > Listes > Types des tickets',
  `admin_user_profile` int(1) NOT NULL COMMENT 'Droit de modification de profil des utilisateurs',
  `admin_user_view` int(1) NOT NULL COMMENT 'Droit de modification des vues des utilisateurs',
  `admin_backup` int(1) NOT NULL COMMENT 'Affiche menu sauvegarde',
  `dashboard_service_only` int(1) NOT NULL COMMENT 'Affiche uniquement les tickets du ou des services auquel est rattaché l''utilisateur',
  `dashboard_agency_only` int(1) NOT NULL COMMENT 'Affiche uniquement les tickets des agences auxquelles est rattaché l''utilisateur',
  `dashboard_firstname` int(1) NOT NULL COMMENT 'Affiche le prénom dans la colonne demandeur et technicien dans la liste des tickets',
  `dashboard_col_user_service` int(1) NOT NULL COMMENT 'Affiche la colonne service du demandeur dans la liste des tickets',
  `dashboard_col_service` int(1) NOT NULL COMMENT 'Affiche la colonne service dans la liste des tickets',
  `dashboard_col_agency` int(1) NOT NULL COMMENT 'Affiche la colonne agence dans la liste des tickets',
  `dashboard_col_company` int(1) NOT NULL COMMENT 'Affiche la colonne société dans la liste des tickets',
  `dashboard_col_type` int(1) NOT NULL COMMENT 'Affiche la colonne type dans la liste des tickets',
  `dashboard_col_category` int(1) NOT NULL COMMENT 'Affiche la colonne catégorie dans la liste des tickets',
  `dashboard_col_subcat` int(1) NOT NULL COMMENT 'Affiche la colonne sous-catégorie dans la liste des tickets',
  `dashboard_col_asset` int(1) NOT NULL COMMENT 'Affiche la colonne équipement dans la liste des tickets',
  `dashboard_col_criticality` int(1) NOT NULL COMMENT 'Affiche la colonne criticité dans la liste des tickets',
  `dashboard_col_priority` int(1) NOT NULL COMMENT 'Affiche la colonne priorité dans la liste des tickets',
  `dashboard_col_date_create` int(1) NOT NULL COMMENT 'Affiche la colonne date de création dans la liste des tickets',
  `dashboard_col_date_create_hour` int(1) NOT NULL COMMENT 'Affiche l''heure de création du ticket dans la colonne date de création, sur la liste des tickets',
  `dashboard_col_date_hope` int(1) NOT NULL COMMENT 'Affiche la colonne date de résolution estimée dans la liste des tickets',
  `dashboard_col_date_res` int(1) NOT NULL COMMENT 'Affiche la colonne date de résolution dans la liste des tickets',
  `dashboard_col_date_modif` int(11) NOT NULL COMMENT 'Affiche la colonne date de dernière modification dans la liste des tickets',
  `dashboard_col_time` int(1) NOT NULL COMMENT 'Affiche la colonne temps passé dans la liste des tickets',
  `userbar` int(1) NOT NULL COMMENT 'Affiche les propriétés étendues de la barre utilisateur',
  `side` int(1) NOT NULL COMMENT 'Affiche la colonne de gauche',
  `side_open_ticket` int(1) NOT NULL COMMENT 'Affiche le bouton Ouvrir un nouveau ticket',
  `side_asset_create` int(1) NOT NULL COMMENT 'Affiche le bouton ajouter équipement',
  `side_asset_all_state` int(11) NOT NULL COMMENT 'Affiche tous les états des équipements dans le menu de gauche',
  `side_your` int(1) NOT NULL COMMENT 'Affiche la section vos tickets',
  `side_your_not_read` int(1) NOT NULL COMMENT 'Affiche vos tickets non lus',
  `side_your_not_attribute` int(1) NOT NULL COMMENT 'Affiche les tickets non attribués',
  `side_your_meta` int(1) NOT NULL COMMENT 'Affiche le meta état à traiter personnel',
  `side_your_tech_group` int(1) NOT NULL COMMENT 'Affiche les tickets associés à un groupe de technicien dans lequel vous êtes présent',
  `side_your_observer` int(1) NOT NULL COMMENT 'Affiche les tickets sur lesquels vous êtes observateur, dans la section Vos tickets',
  `side_all` int(1) NOT NULL COMMENT 'Affiche la section tous les tickets',
  `side_all_wait` int(1) NOT NULL COMMENT 'Affiche la vue nouveaux tickets dans tous les tickets',
  `side_all_meta` int(1) NOT NULL COMMENT 'Affiche le meta état à traiter pour tous les techniciens',
  `side_all_service_disp` int(1) NOT NULL COMMENT 'Affiche tous les tickets associés aux services de l''utilisateur connecté',
  `side_all_service_edit` int(1) NOT NULL COMMENT 'Permet de modifier tous les tickets associés aux services de l''utilisateur connecté',
  `side_all_agency_disp` int(1) NOT NULL COMMENT 'Affiche tous les tickets associés aux agences de l''utilisateur connecté',
  `side_all_agency_edit` int(1) NOT NULL COMMENT 'Permet de modifier tous les tickets associés aux agences de l''utilisateur connecté',
  `side_company` int(1) NOT NULL DEFAULT 0 COMMENT 'Affiche la section tous les tickets de ma société',
  `side_view` int(1) NOT NULL COMMENT 'Affiche les vues personnelles',
  `ticket_next` int(1) NOT NULL COMMENT 'Affiche les flèches ticket suivant et précédent',
  `ticket_print` int(1) NOT NULL COMMENT 'Impression des tickets',
  `ticket_fusion` int(1) NOT NULL COMMENT 'Affiche le bouton fusion sur le ticket',
  `ticket_template` int(1) NOT NULL COMMENT 'Affiche le bouton modèle de ticket',
  `ticket_calendar` int(1) NOT NULL COMMENT 'Planifier un ticket',
  `ticket_event` int(1) NOT NULL COMMENT 'Créer un rappel de ticket',
  `ticket_save` int(1) NOT NULL COMMENT 'Sauvegarde de ticket',
  `ticket_type` int(1) NOT NULL COMMENT 'Modification du type dans le ticket',
  `ticket_type_disp` int(1) NOT NULL COMMENT 'Affiche le champ type dans le ticket',
  `ticket_type_service_limit` int(1) NOT NULL COMMENT 'Affiche uniquement les types associés au service',
  `ticket_type_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ type dans le ticket',
  `ticket_type_answer_disp` int(1) NOT NULL COMMENT 'Affiche le champ type de réponse sur le ticket',
  `ticket_service` int(1) NOT NULL COMMENT 'Modification du service dans le ticket',
  `ticket_service_disp` int(1) NOT NULL COMMENT 'Affiche le champ service dans le ticket',
  `ticket_service_mandatory` int(11) NOT NULL COMMENT 'Oblige la saisie du champ service',
  `ticket_user` int(1) NOT NULL COMMENT 'Modification du demandeur',
  `ticket_user_disp` int(1) NOT NULL COMMENT 'Affiche le champ utilisateur dans le ticket',
  `ticket_user_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ Demandeur',
  `ticket_user_actions` int(1) NOT NULL COMMENT 'Affiche les boutons actions pour le demandeur',
  `ticket_user_company` int(1) NOT NULL COMMENT 'Affiche le nom de la société de l''utilisateur dans la liste des utilisateurs sur un ticket',
  `ticket_user_mail` int(1) NOT NULL COMMENT 'Affiche le mail de l''utilisateur dans la liste des demandeurs sur un ticket',
  `ticket_user_mobile` int(1) NOT NULL COMMENT 'Affiche le portable de l''utilisateur dans la liste des demandeurs sur un ticket',
  `ticket_user_info_mobile` int(1) NOT NULL COMMENT 'Affiche les informations demandeur sur mobile',
  `ticket_observer` int(1) NOT NULL COMMENT 'Modification du champ observateur sur le ticket',
  `ticket_observer_disp` int(1) NOT NULL COMMENT 'Affichage du champ observateur sur le ticket',
  `ticket_tech` int(1) NOT NULL COMMENT 'Modification du technicien',
  `ticket_tech_disp` int(1) NOT NULL COMMENT 'Affiche le champ technicien dans le ticket',
  `ticket_tech_service_lock` int(1) NOT NULL COMMENT 'Bloque la modification du champ technicien si la limite par service est activée et qu''il ouvre un ticket pour un autre service ',
  `ticket_tech_mandatory` int(11) NOT NULL COMMENT 'Oblige la saisie du champ technicien',
  `ticket_tech_admin` int(1) NOT NULL COMMENT 'Affiche les administrateurs dans la liste des techniciens sur un ticket.',
  `ticket_tech_super` int(1) NOT NULL COMMENT 'Affiche les superviseurs dans la liste des techniciens sur un ticket',
  `ticket_asset` int(1) NOT NULL COMMENT 'Modification de l''équipement sur un ticket',
  `ticket_asset_disp` int(1) NOT NULL COMMENT 'Affiche le champ équipement dans le ticket',
  `ticket_asset_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ équipement',
  `ticket_asset_user_only` int(1) NOT NULL COMMENT 'Affiche uniquement les équipements associés au demandeur',
  `ticket_asset_type` int(1) NOT NULL COMMENT 'Affiche une nouvelle liste déroulante permettant de filtrer les équipements par type d''équipement',
  `ticket_asset_model` int(1) NOT NULL COMMENT 'Affiche la liste des modèles sur le champ équipement',
  `ticket_cat` int(1) NOT NULL COMMENT 'Modification des catégories',
  `ticket_cat_disp` int(1) NOT NULL COMMENT 'Affiche le champ catégorie dans le ticket',
  `ticket_cat_actions` int(1) NOT NULL COMMENT 'Affiche les boutons actions pour les catégories',
  `ticket_cat_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ catégorie',
  `ticket_cat_service_only` int(1) NOT NULL COMMENT 'Active le cloisonnement des catégories en fonction d''un service',
  `ticket_subcat_disp` int(1) NOT NULL COMMENT 'Affiche le champ sous-catégorie',
  `ticket_agency` int(1) NOT NULL COMMENT 'Affiche le champ agence dans le ticket',
  `ticket_agency_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ agence',
  `ticket_sender_service_disp` int(1) NOT NULL COMMENT 'Affiche le champ service du demandeur dans le ticket',
  `ticket_place` int(1) NOT NULL COMMENT 'Modification du lieu',
  `ticket_place_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ lieu sur le ticket',
  `ticket_title` int(1) NOT NULL COMMENT 'Modification du titre dans le ticket',
  `ticket_title_disp` int(1) NOT NULL COMMENT 'Affiche le champ titre dans le ticket',
  `ticket_title_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ titre',
  `ticket_description` int(1) NOT NULL COMMENT 'Modification de la description',
  `ticket_description_disp` int(1) NOT NULL COMMENT 'Affiche le champ description dans le ticket',
  `ticket_description_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie de la description',
  `ticket_description_insert_image` int(1) NOT NULL COMMENT 'Affiche le bouton insérer image sur le champ description',
  `ticket_resolution_disp` int(1) NOT NULL COMMENT 'Affiche le champ résolution dans le ticket',
  `ticket_resolution_insert_image` int(1) NOT NULL COMMENT 'Affiche le bouton insérer image sur le champ résolution',
  `ticket_resolution_text_only` int(1) NOT NULL COMMENT 'Affiche uniquement les événements de type texte dans la résolution',
  `ticket_attachment` int(1) NOT NULL COMMENT 'Ajouter des pièces jointes',
  `ticket_attachment_delete` int(1) NOT NULL COMMENT 'Autorise la suppression de pièce jointe sur un ticket',
  `ticket_date_create` int(1) NOT NULL COMMENT 'Modification de la date de création',
  `ticket_date_create_disp` int(1) NOT NULL COMMENT 'Affiche le champ date de création dans le ticket',
  `ticket_date_hope` int(1) NOT NULL COMMENT 'Modification de la date de résolution estimée',
  `ticket_date_hope_disp` int(1) NOT NULL COMMENT 'Affiche le champ date de résolution estimée dans le ticket',
  `ticket_date_hope_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ date de résolution estimée',
  `ticket_date_res` int(1) NOT NULL COMMENT 'Modification de la date de résolution dans le ticket',
  `ticket_date_res_disp` int(1) NOT NULL COMMENT 'Affiche le champ date de résolution dans le ticket',
  `ticket_time` int(1) NOT NULL COMMENT 'Modification du temps passé par ticket',
  `ticket_time_disp` int(1) NOT NULL COMMENT 'Affiche le champ temps passé dans le ticket',
  `ticket_time_hope` int(1) NOT NULL COMMENT 'Modification du temps estimé passé par ticket',
  `ticket_time_hope_disp` int(1) NOT NULL COMMENT 'Affiche le champ temps estimé dans le ticket',
  `ticket_priority` int(1) NOT NULL COMMENT 'Modification de la priorité dans le ticket',
  `ticket_priority_disp` int(1) NOT NULL COMMENT 'Affiche le champ priorité dans le ticket',
  `ticket_priority_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ priorité',
  `ticket_priority_service_limit` int(1) NOT NULL COMMENT 'Affiche uniquement les priorités associées au service',
  `ticket_criticality` int(1) NOT NULL COMMENT 'Modification de la criticité dans le ticket',
  `ticket_criticality_disp` int(1) NOT NULL COMMENT 'Affiche le champ criticité dans le ticket',
  `ticket_criticality_mandatory` int(1) NOT NULL COMMENT 'Oblige la saisie du champ criticité',
  `ticket_criticality_service_limit` int(1) NOT NULL COMMENT 'Affiche uniquement les criticités associées au service',
  `ticket_billable` int(1) NOT NULL COMMENT 'Affiche le champ facturable sur le ticket, dans la liste des tickets, et dans la barre utilisateur',
  `ticket_state` int(1) NOT NULL COMMENT 'Modification du champ état dans le ticket',
  `ticket_state_disp` int(1) NOT NULL COMMENT 'Affiche le champ état dans le ticket',
  `ticket_user_validation` int(1) NOT NULL COMMENT 'Affiche le champ validation demandeur sur les tickets',
  `ticket_availability` int(1) NOT NULL COMMENT 'Modification de la partie disponibilité',
  `ticket_availability_disp` int(1) NOT NULL COMMENT 'Affiche la partie disponibilité',
  `ticket_delete` int(1) NOT NULL COMMENT 'Droit de suppression de tickets',
  `ticket_close` int(1) NOT NULL COMMENT 'Affiche le bouton de clôture dans le ticket',
  `ticket_reopen` int(1) NOT NULL COMMENT 'Affiche le bouton de ré-ouverture sur un ticket résolu',
  `ticket_thread_add` int(1) NOT NULL COMMENT 'Ajouter des réponses',
  `ticket_thread_delete` int(1) NOT NULL COMMENT 'Suppression de ses résolutions',
  `ticket_thread_delete_all` int(11) NOT NULL COMMENT 'Suppression de toutes les résolutions',
  `ticket_thread_edit` int(1) NOT NULL COMMENT 'Modification de ses résolutions',
  `ticket_thread_edit_all` int(1) NOT NULL COMMENT 'Modification de toutes les résolutions',
  `ticket_thread_post` int(1) NOT NULL COMMENT 'Droit de répondre dans les tickets',
  `ticket_thread_private` int(1) NOT NULL COMMENT 'Autorise à passer le message en privé',
  `ticket_thread_private_button` int(1) NOT NULL COMMENT 'Affiche un bouton pour ajouter un message en privé',
  `ticket_save_close` int(1) NOT NULL COMMENT 'Affiche le bouton enregistrer et fermer dans le ticket',
  `ticket_send_mail` int(1) NOT NULL COMMENT 'Affiche le bouton envoyer un mail dans le ticket',
  `ticket_cancel` int(1) NOT NULL COMMENT 'Affiche le bouton annuler dans le ticket',
  `ticket_new_type` int(1) NOT NULL COMMENT 'Modification du type pour les nouveaux tickets',
  `ticket_new_type_disp` int(1) NOT NULL COMMENT 'Affiche le champ type pour les nouveaux tickets',
  `ticket_new_service` int(1) NOT NULL COMMENT 'Modification du service pour les nouveaux tickets',
  `ticket_new_service_disp` int(1) NOT NULL COMMENT 'Affiche le champ service pour les nouveaux tickets',
  `ticket_new_user` int(1) NOT NULL COMMENT 'Modification du demandeur pour les nouveaux tickets',
  `ticket_new_user_disp` int(1) NOT NULL COMMENT 'Affiche le champ demandeur pour les nouveaux tickets',
  `ticket_new_tech_disp` int(1) NOT NULL COMMENT 'Affiche le champ technicien pour les nouveaux tickets',
  `ticket_new_asset_disp` int(1) NOT NULL COMMENT 'Affiche le champ équipement pour les nouveaux tickets',
  `ticket_new_cat` int(1) NOT NULL COMMENT 'Modification de la catégorie pour les nouveaux tickets',
  `ticket_new_cat_disp` int(1) NOT NULL COMMENT 'Affiche le champ catégorie pour les nouveaux tickets',
  `ticket_new_resolution_disp` int(1) NOT NULL COMMENT 'Affiche le champ résolution pour les nouveaux tickets',
  `ticket_new_send` int(1) NOT NULL COMMENT 'Affiche le bouton envoyer pour les nouveaux tickets',
  `ticket_new_save` int(1) NOT NULL COMMENT 'Affiche le bouton sauvegarder sur les nouveaux tickets',
  `user_profil_company` int(1) NOT NULL DEFAULT 2 COMMENT 'Modification de la société sur la fiche utilisateur',
  `user_profil_service` int(1) NOT NULL COMMENT 'Modification du service sur la fiche de l''utilisateur',
  `user_profil_agency` int(1) NOT NULL COMMENT 'Modification de l''agence sur la fiche de l''utilisateur',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `trights`
--

INSERT INTO `trights` (`id`, `profile`, `search`, `task_checkbox`, `procedure`, `procedure_add`, `procedure_delete`, `procedure_modify`, `procedure_company`, `procedure_list_company_only`, `stat`, `stat_ticket_time_by_states`, `planning`, `planning_tech_color`, `planning_direct_event`, `project`, `contract`, `availability`, `asset`, `asset_net_scan`, `asset_delete`, `asset_virtualization_disp`, `asset_location_disp`, `asset_list_department_only`, `asset_list_company_only`, `asset_list_view_only`, `asset_list_col_location`, `asset_list_col_sn_manufacturer`, `admin`, `admin_groups`, `admin_lists`, `admin_lists_category`, `admin_lists_subcat`, `admin_lists_criticality`, `admin_lists_priority`, `admin_lists_type`, `admin_user_profile`, `admin_user_view`, `admin_backup`, `dashboard_service_only`, `dashboard_agency_only`, `dashboard_firstname`, `dashboard_col_user_service`, `dashboard_col_service`, `dashboard_col_agency`, `dashboard_col_company`, `dashboard_col_type`, `dashboard_col_category`, `dashboard_col_subcat`, `dashboard_col_asset`, `dashboard_col_criticality`, `dashboard_col_priority`, `dashboard_col_date_create`, `dashboard_col_date_create_hour`, `dashboard_col_date_hope`, `dashboard_col_date_res`, `dashboard_col_date_modif`, `dashboard_col_time`, `userbar`, `side`, `side_open_ticket`, `side_asset_create`, `side_asset_all_state`, `side_your`, `side_your_not_read`, `side_your_not_attribute`, `side_your_meta`, `side_your_tech_group`, `side_your_observer`, `side_all`, `side_all_wait`, `side_all_meta`, `side_all_service_disp`, `side_all_service_edit`, `side_all_agency_disp`, `side_all_agency_edit`, `side_company`, `side_view`, `ticket_next`, `ticket_print`, `ticket_fusion`, `ticket_template`, `ticket_calendar`, `ticket_event`, `ticket_save`, `ticket_type`, `ticket_type_disp`, `ticket_type_service_limit`, `ticket_type_mandatory`, `ticket_type_answer_disp`, `ticket_service`, `ticket_service_disp`, `ticket_service_mandatory`, `ticket_user`, `ticket_user_disp`, `ticket_user_mandatory`, `ticket_user_actions`, `ticket_user_company`, `ticket_user_mail`, `ticket_user_mobile`, `ticket_user_info_mobile`, `ticket_observer`, `ticket_observer_disp`, `ticket_tech`, `ticket_tech_disp`, `ticket_tech_service_lock`, `ticket_tech_mandatory`, `ticket_tech_admin`, `ticket_tech_super`, `ticket_asset`, `ticket_asset_disp`, `ticket_asset_mandatory`, `ticket_asset_user_only`, `ticket_asset_type`, `ticket_asset_model`, `ticket_cat`, `ticket_cat_disp`, `ticket_cat_actions`, `ticket_cat_mandatory`, `ticket_cat_service_only`, `ticket_subcat_disp`, `ticket_agency`, `ticket_agency_mandatory`, `ticket_sender_service_disp`, `ticket_place`, `ticket_place_mandatory`, `ticket_title`, `ticket_title_disp`, `ticket_title_mandatory`, `ticket_description`, `ticket_description_disp`, `ticket_description_mandatory`, `ticket_description_insert_image`, `ticket_resolution_disp`, `ticket_resolution_insert_image`, `ticket_resolution_text_only`, `ticket_attachment`, `ticket_attachment_delete`, `ticket_date_create`, `ticket_date_create_disp`, `ticket_date_hope`, `ticket_date_hope_disp`, `ticket_date_hope_mandatory`, `ticket_date_res`, `ticket_date_res_disp`, `ticket_time`, `ticket_time_disp`, `ticket_time_hope`, `ticket_time_hope_disp`, `ticket_priority`, `ticket_priority_disp`, `ticket_priority_mandatory`, `ticket_priority_service_limit`, `ticket_criticality`, `ticket_criticality_disp`, `ticket_criticality_mandatory`, `ticket_criticality_service_limit`, `ticket_billable`, `ticket_state`, `ticket_state_disp`, `ticket_user_validation`, `ticket_availability`, `ticket_availability_disp`, `ticket_delete`, `ticket_close`, `ticket_reopen`, `ticket_thread_add`, `ticket_thread_delete`, `ticket_thread_delete_all`, `ticket_thread_edit`, `ticket_thread_edit_all`, `ticket_thread_post`, `ticket_thread_private`, `ticket_thread_private_button`, `ticket_save_close`, `ticket_send_mail`, `ticket_cancel`, `ticket_new_type`, `ticket_new_type_disp`, `ticket_new_service`, `ticket_new_service_disp`, `ticket_new_user`, `ticket_new_user_disp`, `ticket_new_tech_disp`, `ticket_new_asset_disp`, `ticket_new_cat`, `ticket_new_cat_disp`, `ticket_new_resolution_disp`, `ticket_new_send`, `ticket_new_save`, `user_profil_company`, `user_profil_service`, `user_profil_agency`) VALUES
(1, 0, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 2, 0, 2, 2, 0, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 0, 2, 2, 2, 2, 0, 0, 0, 0, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0, 0, 2, 2, 2, 0, 0, 0, 0, 0, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 0, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 2, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 2, 2, 0, 2, 2, 0, 0, 0, 2, 0, 2, 2, 0, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 0, 2, 0, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 2, 2, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 2),
(2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 0, 2, 2, 2, 2, 0, 0, 0, 0, 0, 2, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 2, 0, 2, 2, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 2, 0, 0, 2, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 2, 0, 2, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 2, 0, 0, 2, 0, 2, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 0, 0, 0, 2, 2, 0, 2, 0, 2, 0, 0),
(3, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 0, 2, 2, 2, 2, 0, 0, 0, 0, 0, 2, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 2, 2, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 2, 0, 0, 2, 2, 0, 0, 0, 0, 0, 0, 2, 0, 0, 2, 0, 2, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 2, 0, 0, 0, 0, 2, 0, 0, 0, 0, 2, 0, 0, 2, 0, 0, 0, 0, 0, 2, 2, 0, 2, 0, 2, 0, 0),
(4, 3, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 2, 0, 2, 2, 0, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 0, 2, 2, 2, 2, 0, 0, 0, 0, 0, 2, 2, 2, 2, 2, 0, 0, 2, 0, 0, 2, 0, 2, 0, 0, 0, 0, 0, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 0, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 2, 2, 0, 0, 2, 0, 0, 0, 0, 2, 0, 0, 2, 2, 2, 0, 2, 2, 0, 0, 0, 2, 0, 2, 2, 0, 2, 2, 0, 2, 2, 2, 0, 2, 0, 2, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 0, 2, 0, 2, 2, 0, 2, 2, 2, 0, 0, 2, 0, 0, 2, 0, 2, 2, 0, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 2),
(5, 4, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 2, 0, 2, 2, 1, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, 2, 2, 0, 2, 2, 2, 2, 0, 0, 0, 0, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0, 0, 2, 2, 2, 0, 0, 0, 0, 0, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 0, 2, 2, 0, 2, 0, 0, 0, 0, 0, 0, 2, 2, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 2, 2, 2, 0, 2, 2, 0, 0, 0, 2, 0, 2, 2, 0, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 0, 2, 0, 2, 2, 2, 2, 2, 2, 0, 0, 2, 0, 2, 2, 2, 2, 2, 0, 2, 2, 2, 2, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 0, 2, 2, 2, 2);

-- --------------------------------------------------------

--
-- Structure de la table `tservices`
--

DROP TABLE IF EXISTS `tservices`;
CREATE TABLE IF NOT EXISTS `tservices` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ldap_guid` varchar(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tservices`
--

INSERT INTO `tservices` (`id`, `name`, `ldap_guid`, `disable`) VALUES
(0, 'Aucun', '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tstates`
--

DROP TABLE IF EXISTS `tstates`;
CREATE TABLE IF NOT EXISTS `tstates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `mail_object` varchar(200) NOT NULL,
  `display` varchar(100) NOT NULL,
  `icon` varchar(512) NOT NULL,
  `meta` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tstates`
--

INSERT INTO `tstates` (`id`, `number`, `name`, `description`, `mail_object`, `display`, `icon`, `meta`) VALUES
(1, 2, 'Attente PEC', 'tickets en attente de prise en charge', 'Notification d\'ouverture', 'badge text-75 border-l-3 brc-black-tp8 bgc-primary text-white', 'fa-hourglass-start', 1),
(2, 3, 'En cours', 'tickets en cours de traitement', 'Notification', 'badge text-75 border-l-3 brc-black-tp8 bgc-warning text-white', 'fa-bars-progress', 1),
(3, 5, 'Résolu', 'tickets résolus', 'Notification de clôture', 'badge text-75 border-l-3 brc-black-tp8 bgc-success text-white', 'fa-check', 0),
(4, 6, 'Rejeté', 'tickets rejetés', 'Notification de rejet', 'badge text-75 border-l-3 brc-black-tp8 bgc-dark text-white', 'fa-xmark', 0),
(5, 1, 'Non attribué', 'tickets pas encore associés à un technicien', 'Notification de déclaration', 'badge text-75 border-l-3 brc-black-tp8 bgc-danger text-white', 'fa-user', 0),
(6, 4, 'Attente retour', 'tickets en attente d\'éléments de la part du demandeur', 'Notification d\'attente de retour ', 'badge text-75 border-l-3 brc-black-tp8 bgc-pink text-white', 'fa-reply', 1);

-- --------------------------------------------------------

--
-- Structure de la table `tsubcat`
--

DROP TABLE IF EXISTS `tsubcat`;
CREATE TABLE IF NOT EXISTS `tsubcat` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cat` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `technician` int(10) NOT NULL,
  `technician_group` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `technician` (`technician`),
  KEY `technician_group` (`technician_group`),
  KEY `cat` (`cat`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tsubcat`
--

INSERT INTO `tsubcat` (`id`, `cat`, `name`, `technician`, `technician_group`) VALUES
(0, 0, 'Aucune', 0, 0),
(1, 1, 'Office', 0, 0),
(2, 2, 'PC', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `tsurvey_answers`
--

DROP TABLE IF EXISTS `tsurvey_answers`;
CREATE TABLE IF NOT EXISTS `tsurvey_answers` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `ticket_id` int(10) NOT NULL,
  `question_id` int(5) NOT NULL,
  `answer` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tsurvey_questions`
--

DROP TABLE IF EXISTS `tsurvey_questions`;
CREATE TABLE IF NOT EXISTS `tsurvey_questions` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `number` int(5) NOT NULL,
  `type` int(5) NOT NULL COMMENT '1=yes/no,2=text,3=select,4=scale',
  `text` varchar(250) NOT NULL,
  `scale` int(2) NOT NULL,
  `select_1` varchar(100) NOT NULL,
  `select_2` varchar(100) NOT NULL,
  `select_3` varchar(100) NOT NULL,
  `select_4` varchar(100) NOT NULL,
  `select_5` varchar(100) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ttemplates`
--

DROP TABLE IF EXISTS `ttemplates`;
CREATE TABLE IF NOT EXISTS `ttemplates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `incident` int(10) NOT NULL,
  `date_start` date NOT NULL,
  `frequency` varchar(32) NOT NULL,
  `last_execution_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `incident` (`incident`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tthreads`
--

DROP TABLE IF EXISTS `tthreads`;
CREATE TABLE IF NOT EXISTS `tthreads` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ticket` int(10) NOT NULL,
  `date` datetime NOT NULL,
  `author` int(10) NOT NULL,
  `text` mediumtext NOT NULL,
  `type` int(1) NOT NULL,
  `tech1` int(5) NOT NULL,
  `tech2` int(5) NOT NULL,
  `group1` int(5) NOT NULL,
  `group2` int(5) NOT NULL,
  `user` int(5) NOT NULL,
  `state` int(1) NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT 0,
  `dest_mail` varchar(150) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket` (`ticket`),
  KEY `author` (`author`),
  KEY `tech1` (`tech1`),
  KEY `tech2` (`tech2`),
  KEY `group1` (`group1`),
  KEY `group2` (`group2`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ttime`
--

DROP TABLE IF EXISTS `ttime`;
CREATE TABLE IF NOT EXISTS `ttime` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `min` int(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `ttime`
--

INSERT INTO `ttime` (`id`, `min`, `name`) VALUES
(1, 1, '1m'),
(2, 5, '5m'),
(3, 10, '10m'),
(4, 30, '30m'),
(5, 60, '1h'),
(6, 180, '3h'),
(7, 300, '5h'),
(8, 480, '1j'),
(9, 960, '2j'),
(10, 2400, '1s');

-- --------------------------------------------------------

--
-- Structure de la table `ttoken`
--

DROP TABLE IF EXISTS `ttoken`;
CREATE TABLE IF NOT EXISTS `ttoken` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `token` varchar(64) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ticket_id` int(10) NOT NULL,
  `procedure_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  KEY `procedure_id` (`procedure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ttypes`
--

DROP TABLE IF EXISTS `ttypes`;
CREATE TABLE IF NOT EXISTS `ttypes` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `service` int(5) NOT NULL,
  `user_validation` int(1) NOT NULL,
  `mail` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service` (`service`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `ttypes`
--

INSERT INTO `ttypes` (`id`, `name`, `service`, `user_validation`, `mail`) VALUES
(0, 'Aucun', 0, 0, ''),
(1, 'Demande', 0, 0, ''),
(2, 'Incident', 0, 0, '');

-- --------------------------------------------------------

--
-- Structure de la table `ttypes_answer`
--

DROP TABLE IF EXISTS `ttypes_answer`;
CREATE TABLE IF NOT EXISTS `ttypes_answer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `disable` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `ttypes_answer`
--

INSERT INTO `ttypes_answer` (`id`, `name`, `disable`) VALUES
(0, 'Aucune', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tusers`
--

DROP TABLE IF EXISTS `tusers`;
CREATE TABLE IF NOT EXISTS `tusers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `profile` int(10) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `fax` varchar(100) NOT NULL,
  `function` varchar(100) NOT NULL,
  `company` int(5) NOT NULL,
  `address1` varchar(100) NOT NULL,
  `address2` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `city` varchar(100) NOT NULL,
  `custom1` varchar(100) NOT NULL,
  `custom2` varchar(100) NOT NULL,
  `disable` int(1) NOT NULL,
  `chgpwd` int(1) NOT NULL,
  `last_login` datetime NOT NULL,
  `last_pwd_chg` date NOT NULL,
  `auth_attempt` int(2) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `skin` varchar(10) NOT NULL,
  `default_ticket_state` varchar(10) NOT NULL,
  `dashboard_ticket_order` varchar(200) NOT NULL,
  `limit_ticket_number` int(5) NOT NULL,
  `limit_ticket_days` int(5) NOT NULL,
  `limit_ticket_date_start` date NOT NULL,
  `language` varchar(10) NOT NULL DEFAULT 'fr_FR',
  `ldap_guid` varchar(50) NOT NULL,
  `ldap_sid` varchar(64) NOT NULL,
  `azure_ad_id` varchar(64) NOT NULL,
  `azure_ad_tenant_id` varchar(128) NOT NULL,
  `planning_color` varchar(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `company` (`company`),
  KEY `profile` (`profile`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `tusers`
--

INSERT INTO `tusers` (`id`, `login`, `password`, `salt`, `firstname`, `lastname`, `profile`, `mail`, `phone`, `mobile`, `fax`, `function`, `company`, `address1`, `address2`, `zip`, `city`, `custom1`, `custom2`, `disable`, `chgpwd`, `last_login`, `last_pwd_chg`, `auth_attempt`, `ip`, `skin`, `default_ticket_state`, `dashboard_ticket_order`, `limit_ticket_number`, `limit_ticket_days`, `limit_ticket_date_start`, `language`, `ldap_guid`, `ldap_sid`, `azure_ad_id`, `azure_ad_tenant_id`, `planning_color`) VALUES
(0, 'aucun', '', '', '', 'Aucun', 2, '', '', '', '', '', 0, '', '', '', '', '', '', 1, 0, '2016-10-21 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '2016-10-21', 'fr_FR', '', '', '', '', ''),
(1, 'admin', '$2y$10$B1QeMLjUsMUyIL6uTGnpYOQoY.a9Sq.7/01y6DcxiVU/vbgS3Jbla', 'salt', 'admin', '', 4, '', '06 09 56 89 45', '', '0', '', 0, '', '', '0', '', '', '', 0, 1, '0000-00-00 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '0000-00-00', 'fr_FR', '', '', '', '', ''),
(2, 'user', '$2y$10$IyYLTCVl4EeMHALAq9KoX.aci8MigMCpSxjlVY97u9d1CYt.8lec6', 'salt', 'user', '', 2, '', '', '', '0', '', 0, '', '', '0', '', '', '', 0, 1, '0000-00-00 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '0000-00-00', 'fr_FR', '', '', '', '', ''),
(3, 'poweruser', '$2y$10$TF7YnhwQoBM8kiIBTR.FaOpGn5ZzYHJIHGPQdvwCbk9r/n1ANio5i', 'salt', 'poweruser', '', 1, '', '', '', '0', '', 0, '', '', '0', '', '', '', 0, 1, '0000-00-00 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '0000-00-00', 'fr_FR', '', '', '', '', ''),
(4, 'super', '$2y$10$erzI5AMjA0d5QxjZAL9nRu.PQudBpsIBrap/wBg.RH7gjcJbMev9a', 'salt', 'supervisor', '', 3, '', '', '', '0', '', 0, '', '', '0', '', '', '', 0, 1, '0000-00-00 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '0000-00-00', 'fr_FR', '', '', '', '', ''),
(5, 'tech', '$2y$10$7jnBeZwjB68tBwU.ROwHP.aeBVZYMSSJiG6XSIBePwKXgokH5sC9q', 'salt', 'tech', '', 0, '', '', '', '0', '', 0, '', '', '0', '', '', '', 0, 1, '0000-00-00 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '0000-00-00', 'fr_FR', '', '', '', '', ''),
(6, 'delete_user_gs', '', '', 'Utilisateur', 'Supprimé', 2, '', '', '', '', '', 0, '', '', '', '', '', '', 1, 0, '0000-00-00 00:00:00', '0000-00-00', 0, '', '', '', '', 0, 0, '0000-00-00', 'fr_FR', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `tusers_agencies`
--

DROP TABLE IF EXISTS `tusers_agencies`;
CREATE TABLE IF NOT EXISTS `tusers_agencies` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `agency_id` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `agency_id` (`agency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tusers_ip`
--

DROP TABLE IF EXISTS `tusers_ip`;
CREATE TABLE IF NOT EXISTS `tusers_ip` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `ip` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tusers_services`
--

DROP TABLE IF EXISTS `tusers_services`;
CREATE TABLE IF NOT EXISTS `tusers_services` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `service_id` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tusers_tech`
--

DROP TABLE IF EXISTS `tusers_tech`;
CREATE TABLE IF NOT EXISTS `tusers_tech` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `user` int(10) NOT NULL,
  `user_group` int(5) NOT NULL,
  `tech` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `tech` (`tech`),
  KEY `user_group` (`user_group`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tviews`
--

DROP TABLE IF EXISTS `tviews`;
CREATE TABLE IF NOT EXISTS `tviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` int(5) NOT NULL,
  `subcat` int(5) NOT NULL,
  `technician` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `subcat` (`subcat`),
  KEY `technician` (`technician`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
COMMIT;