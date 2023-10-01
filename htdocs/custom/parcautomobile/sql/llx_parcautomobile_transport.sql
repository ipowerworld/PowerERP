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


CREATE TABLE llx_parcautomobile_transport(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL, 
	vehicule integer NOT NULL, 
	type integer NOT NULL, 
	booking integer NOT NULL, 
	lieu_depart integer NOT NULL, 
	lieu_arrive integer NOT NULL, 
	date_depart datetime NOT NULL, 
	date_arrive datetime NOT NULL, 
	kilometrage double, 
	consommation double, 
	prix_carburant double, 
	taxe_poids double, 
	frais_voyage double, 
	chauffeur integer NOT NULL, 
	commande integer NOT NULL, 
	conteneur integer, 
	date_papier datetime, 
	date_arrivee_bateau datetime, 
	nombre_place integer, 
	penalite double
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

CREATE TABLE llx_parcautomobile_transport_produit(
  rowid int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  transport int NOT NULL,
  produit int DEFAULT NULL,
  quantite int DEFAULT NULL
) ENGINE=InnoDB;
