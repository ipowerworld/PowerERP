<?php
/* Copyright (C) 2023 Serge inack <inack3serge@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    immobilisations/lib/immobilisations.lib.php
 * \ingroup immobilisations
 * \brief   Library files with common functions for Immobilisations
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function immobilisationsAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("immobilisations@immobilisations");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/immobilisations/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	
	$head[$h][0] = dol_buildpath("/immobilisations/admin/categories_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields_(catégories)");
	$head[$h][2] = 'categories_extrafields';
	$h++;
	
	$head[$h][0] = dol_buildpath("/immobilisations/admin/immobilisations_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields_(immobilisations)");
	$head[$h][2] = 'immobilisations_extrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/immobilisations/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	$head[$h][0] = dol_buildpath("/immobilisations/admin/document.php", 1);
	$head[$h][1] = 'Document';
	$head[$h][2] = 'document';
	$h++;

	
	complete_head_from_modules($conf, $langs, null, $head, $h, 'immobilisations@immobilisations');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'immobilisations@immobilisations', 'remove');

	return $head;
}
