<?php
/* Copyright (C) 2023 SuperAdmin
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
 * \file    parcautomobile/lib/parcautomobile.lib.php
 * \ingroup parcautomobile
 * \brief   Library files with common functions for ParcAutomobile
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function parcautomobileAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("parcautomobile@parcautomobile");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/parcautomobile/admin/setup.php", 1);
	$head[$h][1] = 'Paramètre';
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/admin/document.php", 1);
	$head[$h][1] = 'Exporter les documents';
	$head[$h][2] = 'document';
	$h++;


	complete_head_from_modules($conf, $langs, null, $head, $h, 'parcautomobile@parcautomobile');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'parcautomobile@parcautomobile', 'remove');

	return $head;
}

function parcautomobileNotifPrepareHead()
{
	global $langs, $conf;

	$langs->load("parcautomobile@parcautomobile");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/parcautomobile/delais.php", 1);
	$head[$h][1] = 'Délais';
	$head[$h][2] = 'delais';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/depannage.php", 1);
	$head[$h][1] = 'Dépannage & Pannes';
	$head[$h][2] = 'depannage';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'parcautomobile@parcautomobile');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'parcautomobile@parcautomobile', 'remove');

	return $head;
}
