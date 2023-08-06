-- ===================================================================
-- Copyright (C) 2014		Charles-Fr BENKE		<charles.fr@benke.fr>
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

CREATE TABLE llx_factorydet (
  rowid 				integer NOT NULL AUTO_INCREMENT,
  fk_factory 			integer NOT NULL DEFAULT 0,
  fk_product 			integer NOT NULL DEFAULT 0,
  qty_unit 				double DEFAULT NULL,	-- quantit� unitaire de produit dans la composition
  qty_planned 			double DEFAULT NULL,	-- quantit� total pr�vu d'etre utilis�
  qty_used 				double DEFAULT NULL,	-- quantit� finalement utilis�
  qty_deleted 			double DEFAULT NULL,
  pmp 					double(24,8) DEFAULT 0,
  price 				double(24,8) DEFAULT 0,
  fk_mvtstockplanned	integer NOT NULL DEFAULT 0, -- pour m�moriser le mouvement de stock pr�visionnel (et ne plus le faire)
  fk_mvtstockused		integer NOT NULL DEFAULT 0, -- pour m�moriser le mouvement de stock r�el (et ne plus le faire)
  fk_mvtstockother		integer NOT NULL DEFAULT 0, -- pour m�moriser le mouvement de stock autre (detruit ou retour stock)
  note_public			text,
  ordercomponent		integer NOT NULL DEFAULT 0,		-- l'ordre d'affichage des composants
  globalqty 			integer NOT NULL DEFAULT 0,		-- La quantit� est � prendre au d�tail ou au global
  description			text,         					-- description

  PRIMARY KEY (rowid),
  UNIQUE KEY uk_factorydet (fk_factory,fk_product)
 
) ENGINE=InnoDB ;
