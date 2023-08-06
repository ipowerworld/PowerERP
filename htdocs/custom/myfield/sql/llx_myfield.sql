-- ===================================================================
-- Copyright (C) 2015-2018	Charlene Benke	<charlie@patas-monkey.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================

create table llx_myfield
(
	rowid			integer AUTO_INCREMENT PRIMARY KEY, -- cl� principale
	label			varchar(50) NOT NULL,				-- Libell� du champ � cacher ou son identifiant LANG
	context			varchar(255) NULL  DEFAULT NULL,	-- context au sens hook du champs � enlever. une virgule de s�paration si plusieurs
	author			varchar(50) NULL DEFAULT NULL,		-- auteur du champs
	active			integer NULL DEFAULT NULL,			-- visible = 0, cach� = 1, invisible = 2
	typefield		integer NULL DEFAULT NULL,			-- champs classique = 0 ou null, onglet = 1, menu = 2
	compulsory		integer NULL DEFAULT NULL,			-- fct natif = 0, obligatoire = 1
	sizefield		integer NULL DEFAULT NULL, 			-- largeur de la zone de saisie
	movefield		integer NULL DEFAULT NULL, 			-- d�placement du champ
	formatfield		varchar(255) NULL DEFAULT NULL, 
	color			varchar(6) NULL DEFAULT NULL,		-- couleur de fond (si visible ou cach�
	replacement		varchar(255) NULL  DEFAULT NULL,	-- Text qui en remplace un autre
	initvalue		text NULL DEFAULT NULL,				-- valeur par d�faut
	tooltipinfo		text NULL DEFAULT NULL,				-- ajoute un tooltip au champ
	querydisplay	text NULL DEFAULT NULL				-- requete sql permettant d'afficher ou non le champ, onglet
)ENGINE=innodb;