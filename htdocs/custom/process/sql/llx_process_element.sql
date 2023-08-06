-- ===================================================================
-- Copyright (C) 2015 Charlie Benke <charlie@patas-monkey.com>
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

create table llx_process_element
(
  element			text,						-- nom de l'�l�ment (unique)
  tablename			text,						-- nom de la table de l'�l�ment
  mainmenu			text,						-- nom de du menu principale
  leftmenu			text,						-- nom de du menu secondaire
  langfile			text,						-- fichier de lang de l'�l�ment
  class				text,						-- path de la classe de l'�l�ment
  querysql			text,						-- percentage increase of element
  enabled			integer						-- l'�l�ment est actif

)ENGINE=innodb;
