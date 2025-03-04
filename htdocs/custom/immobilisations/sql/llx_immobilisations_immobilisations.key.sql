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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_rowid (rowid);
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_ref (ref);
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_fk_product (fk_product);
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_fk_Project (fk_Project);
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_fk_categorie (fk_categorie);
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_status (status);
ALTER TABLE llx_immobilisations_immobilisations ADD INDEX idx_immobilisations_immobilisations_approvment (approvment);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_immobilisations_immobilisations ADD UNIQUE INDEX uk_immobilisations_immobilisations_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_immobilisations_immobilisations ADD CONSTRAINT llx_immobilisations_immobilisations_fk_field FOREIGN KEY (fk_field) REFERENCES llx_immobilisations_myotherobject(rowid);

