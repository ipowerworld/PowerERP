-- ===================================================================
-- Copyright (C) 2013 Charles-Fr Benke <charles.fr@benke.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
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

create table llx_process
(
  rowid				integer AUTO_INCREMENT PRIMARY KEY,
  fk_element		integer DEFAULT 0,			-- cl� de rattachement � l'�l�ment / si 0 c'est les parametres...
  element			text ,						-- type de l'�l�ment de l'avancement
  color				text,						-- couleur de l'�l�ment sur l'agenda
  progress			integer	DEFAULT 0,			-- percentage increase of element
  step				integer,					-- avancement du d�roulement de 0 � 9 max
  display			text						-- param�trage d'affichage
)ENGINE=innodb;
