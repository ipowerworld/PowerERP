<?php
/* Copyright (C) 2001-2002	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2003		Jean-Louis Bergamo		<jlb@j1b.org>
 * Copyright (C) 2004-2013	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2012		Florian Henry			<florian.henry@open-concept.pro>
 * Copyright (C) 2013		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2013		Florian Henry			<florian.henry@open-concept.pro>
 * Copyright (C) 2015		Claudio Aschieri		<c.aschieri@19.coop>
 * Copyright (C) 2018	   Quentin Vial-Gouteyron   <quentin.vial-gouteyron@atm-consulting.fr>
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/admin/commande_fournisseur_dispatch_extrafields.php
 *		\ingroup    reception
 *		\brief      Page to setup extra fields of reception
 */

// Load PowerERP environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/reception.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';


if (!$user->admin) {
	accessforbidden();
}

$langs->load("admin");
$langs->load("other");
$langs->load("receptions");

$extrafields = new ExtraFields($db);
$form = new Form($db);

// List of supported format
$tmptype2label = ExtraFields::$type2label;
$type2label = array('');
foreach ($tmptype2label as $key => $val) {
	$type2label[$key] = $langs->trans($val);
}

$action = GETPOST('action', 'aZ09');
$attrname = GETPOST('attrname', 'alpha');
$elementtype = 'commande_fournisseur_dispatch'; //Must be the $table_element of the class that manage extrafield

if (!$user->admin) {
	accessforbidden();
}


/*
 * Actions
 */

require DOL_DOCUMENT_ROOT.'/core/actions_extrafields.inc.php';



/*
 * View
 */

$textobject = $langs->transnoentitiesnoconv("Receptions");

llxHeader('', $langs->trans("ReceptionsSetup"));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("ReceptionsSetup"), $linkback, 'title_setup');
print "<br>\n";

$head = reception_admin_prepare_head();

print dol_get_fiche_head($head, 'attributeslines_reception', $langs->trans("Receptions"), -1, 'reception');

require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_view.tpl.php';

print dol_get_fiche_end();


// Creation of an optional field
if ($action == 'create') {
	print "<br>";
	print load_fiche_titre($langs->trans('NewAttribute'));

	require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_add.tpl.php';
}

// Edition of an optional field
if ($action == 'edit' && !empty($attrname)) {
	print "<br>";
	print load_fiche_titre($langs->trans("FieldEdition", $attrname));

	require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_edit.tpl.php';
}

llxFooter();

$db->close();
