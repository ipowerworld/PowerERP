<?php
/* Copyright (C) ---Put here your own copyright and developer email---
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
 * \file    lib/parcautomobile_vehicule.lib.php
 * \ingroup parcautomobile
 * \brief   Library files with common functions for Vehicule
 */

/**
 * Prepare array of tabs for Vehicule
 *
 * @param	Vehicule	$object		Vehicule
 * @return 	array					Array of tabs
 */
function vehiculePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("parcautomobile@parcautomobile");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/parcautomobile/vehicule_card.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
		$nbNote = 0;
		if (!empty($object->note_private)) {
			$nbNote++;
		}
		if (!empty($object->note_public)) {
			$nbNote++;
		}
		$head[$h][0] = dol_buildpath('/parcautomobile/vehicule_note.php', 1) . '?id=' . $object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) {
			$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">' . $nbNote . '</span>' : '');
		}
		$head[$h][2] = 'note';
		$h++;
	}

	$head[$h][0] = dol_buildpath("/parcautomobile/paper/carte_grise.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Paper");
	$head[$h][2] = 'paper';
	$h++;

	require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
	$upload_dir = $conf->parcautomobile->dir_output . "/vehicule/" . dol_sanitizeFileName($object->ref);
	$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
	$nbLinks = Link::count($db, $object->element, $object->id);
	$head[$h][0] = dol_buildpath("/parcautomobile/vehicule_document.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans('Documents');
	if (($nbFiles + $nbLinks) > 0) {
		$head[$h][1] .= '<span class="badge marginleftonlyshort">' . ($nbFiles + $nbLinks) . '</span>';
	}
	$head[$h][2] = 'document';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/vehicule_agenda.php", 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Events");
	$head[$h][2] = 'agenda';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@parcautomobile:/parcautomobile/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@parcautomobile:/parcautomobile/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'vehicule@parcautomobile');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'vehicule@parcautomobile', 'remove');

	return $head;
}

function PaperVehiculePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("parcautomobile@parcautomobile");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/parcautomobile/paper/carte_grise.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Carte grise';
	$head[$h][2] = 'carte_grise';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/paper/assurance.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Assurance';
	$head[$h][2] = 'assurance';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/paper/vignette.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Vignette';
	$head[$h][2] = 'vignette';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/paper/visite_technique.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Visite technique';
	$head[$h][2] = 'visite_technique';
	$h++;

	$head[$h][0] = dol_buildpath("/parcautomobile/paper/carte_disque.php", 1) . '?id=' . $object->id;
	$head[$h][1] = 'Carte de disque';
	$head[$h][2] = 'carte_disque';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'parcautomobile@parcautomobile');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'parcautomobile@parcautomobile', 'remove');

	return $head;
}
