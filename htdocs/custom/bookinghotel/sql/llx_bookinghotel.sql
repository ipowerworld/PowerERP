﻿-- CREATE TABLE IF NOT EXISTS `llx_bookinghotel_historique` (
--   `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
--   `reservation_id` int(11) NOT NULL,
--   `user` int(11) NOT NULL,
--   `created_at` datetime NOT NULL,
--   `action` varchar(11) NOT NULL DEFAULT 'edit',
--   `chambre` varchar(50) NOT NULL,
--   `client` int(11) DEFAULT NULL,
--   `debut` date DEFAULT NULL,
--   `fin` date DEFAULT NULL,
--   `reservation_etat` int(11) DEFAULT NULL,
--   `to_centrale` varchar(100) DEFAULT NULL,
--   `notes` text,
--   `chambre_category` varchar(50) NOT NULL,
-- );
-- CREATE TABLE IF NOT EXISTS `llx_bookinghotel_etat` (
--   `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
--   `label` varchar(100) NOT NULL,
--   `color` varchar(15) DEFAULT NULL
-- );
-- CREATE TABLE IF NOT EXISTS `llx_bookinghotel` (
--   `rowid` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
--   `chambre` varchar(50) NOT NULL,
--   `client` int(11) DEFAULT NULL,
--   `debut` date DEFAULT NULL,
--   `fin` date DEFAULT NULL,
--   `reservation_etat` int(11) DEFAULT NULL,
--   `to_centrale` varchar(100) DEFAULT NULL,
--   `notes` text,
--   `chambre_category` varchar(50) NOT NULL,
--   `fk_proposition` int(11) DEFAULT NULL,
-- );
