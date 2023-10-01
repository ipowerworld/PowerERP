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


CREATE TABLE llx_parcautomobile_booking(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	description text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL, 
	date_fermeture_attendue datetime NOT NULL, 
	date_fermeture_reelle datetime, 
	tiers integer, 
	nombre_conteneur integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;


CREATE TABLE llx_parcautomobile_booking_conteneur (
  rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
  booking integer NOT NULL,
  isShow integer NOT NULL DEFAULT 0,
  conteneur integer NOT NULL,
  date_echeance date NOT NULL,
  date_sortie_vide date NOT NULL
) ENGINE=innodb;


