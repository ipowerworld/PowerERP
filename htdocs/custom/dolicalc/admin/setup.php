<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2017 AXeL <anass_denna@hotmail.fr>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/listexportimport.php
 * 	\ingroup	listexportimport
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// PowerERP environment
$mod_path = "";
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
    $mod_path = "/custom";
}

global $db, $conf, $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/dolicalc.lib.php';

// Translations
$langs->load("dolicalc@dolicalc");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (powererp_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

else if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (powererp_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

/*
 * View
 */
$page_name = "Setup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = DoliCalcAdminPrepareHead();
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module514000Name"),
    0,
    "dolicalc@dolicalc"
);

// Setup page goes here
print $langs->trans('NoSetupAvailable');

dol_fiche_end();

llxFooter();

$db->close();