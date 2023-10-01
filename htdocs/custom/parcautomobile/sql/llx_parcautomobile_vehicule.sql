-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_parcautomobile_vehicule(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	description text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL, 
	roues integer NOT NULL, 
	vitesse double NOT NULL, 
	immatriculation varchar(20) NOT NULL, 
	marque varchar(20) NOT NULL, 
	etat varchar(20) DEFAULT '0' NOT NULL, 
	disponibilite varchar(20) DEFAULT '0' NOT NULL, 
	extincteur varchar(5) NOT NULL, 
	type_carburant varchar(20) NOT NULL, 
	tiers integer, 
	place integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb; 


CREATE TABLE IF NOT EXISTS `llx_parcautomobile_assurance` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `ref` varchar(20) NOT NULL,
  `id_vehicule` int NOT NULL,
  `company` varchar(20) NOT NULL,
  `category` varchar(30) NOT NULL,
  `date_souscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_limite` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `llx_parcautomobile_cartedisque` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `id_vehicule` int NOT NULL,
  `type` varchar(20) NOT NULL,
  `rapport` varchar(255) NOT NULL,
  `etat` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `llx_parcautomobile_cartegrise` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `id_vehicule` int NOT NULL,
  `immatriculation` varchar(20) NOT NULL,
  `poids` double NOT NULL,
  `titulaire` text NOT NULL,
  `charge_max` varchar(20) NOT NULL,
  `energie` varchar(20) NOT NULL,
  `place` int NOT NULL,
  `taxe` varchar(20) NOT NULL,
  `date_limite` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_immatriculation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `vin` varchar(20) NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `llx_parcautomobile_vignette` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `ref` varchar(20) NOT NULL,
  `id_vehicule` int NOT NULL,
  `company` varchar(20) NOT NULL,
  `year` year NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `llx_parcautomobile_visitetechnique` (
  `rowid` int NOT NULL AUTO_INCREMENT,
  `ref` varchar(20) NOT NULL,
  `id_vehicule` int NOT NULL,
  `company` varchar(20) NOT NULL,
  `rapport` varchar(255) NOT NULL,
  `date_evaluation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_limite` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB;
