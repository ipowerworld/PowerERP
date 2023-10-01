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


CREATE TABLE llx_immobilisations_immobilisations(
	-- BEGIN MODULEBUILDER FIELDS
	fk_fournisseur integer NOT NULL, 
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(50) DEFAULT '(IMMO)' NOT NULL, 
	label varchar(255), 
	fk_product varchar(100) NOT NULL, 
	fk_Project integer NOT NULL, 
	fk_categorie integer NOT NULL, 
	amount_ht double, 
	amount_vat double, 
	description text, 
	note_public text, 
	note_private text, 
	date_creation date NOT NULL, 
	date_consommation date, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL, 
	fk_user_trans integer NOT NULL, 
	fk_user_attrib integer, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	status integer NOT NULL, 
	approvment integer NOT NULL, 
	pourcentage_account double
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

