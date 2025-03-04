<?php
/* Copyright (C) 2005-2009  Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2009-2016  Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2011       Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2017      Ferran Marcet       	 <fmarcet@2byte.es>
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
 *	\file       htdocs/fichinter/info.php
 *	\ingroup    fichinter
 *	\brief      Page d'affichage des infos d'une fiche d'intervention
 */

// Load PowerERP environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/fichinter.lib.php';
if (isModEnabled('project')) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array('companies', 'interventions'));

$socid = 0;
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');

// Security check
if ($user->socid) {
	$socid = $user->socid;
}
$result = restrictedArea($user, 'ficheinter', $id, 'fichinter');

$object = new Fichinter($db);

if (!$object->fetch($id, $ref) > 0) {
	dol_print_error($db);
	exit;
}


/*
 *	View
 */

$form = new Form($db);

llxHeader('', $langs->trans("Intervention"));

$object->fetch_thirdparty();
$object->info($object->id);

$head = fichinter_prepare_head($object);
print dol_get_fiche_head($head, 'info', $langs->trans('InterventionCard'), -1, 'intervention');

// Intervention card
$linkback = '<a href="'.DOL_URL_ROOT.'/fichinter/list.php?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';


$morehtmlref = '<div class="refidno">';
// Ref customer
//$morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
//$morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
// Thirdparty
$morehtmlref .= $langs->trans('ThirdParty').' : '.$object->thirdparty->getNomUrl(1);
// Project
if (isModEnabled('project')) {
	$langs->load("projects");
	$morehtmlref .= '<br>'.$langs->trans('Project').' ';
	if ($user->rights->commande->creer) {
		if ($action != 'classify') {
			//$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
			$morehtmlref .= ' : ';
		}
		if ($action == 'classify') {
			//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
			$morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
			$morehtmlref .= '<input type="hidden" name="action" value="classin">';
			$morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
			$morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
			$morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
			$morehtmlref .= '</form>';
		} else {
			$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1, '', 'maxwidth300');
		}
	} else {
		if (!empty($object->fk_project)) {
			$proj = new Project($db);
			$proj->fetch($object->fk_project);
			$morehtmlref .= ' : '.$proj->getNomUrl(1);
			if ($proj->title) {
				$morehtmlref .= ' - '.$proj->title;
			}
		} else {
			$morehtmlref .= '';
		}
	}
}
$morehtmlref .= '</div>';

dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';

print '<br>';

print '<table width="100%"><tr><td>';
dol_print_object_info($object);
print '</td></tr></table>';

print '</div>';

print dol_get_fiche_end();

llxFooter();
$db->close();
