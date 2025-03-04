<?php
/* Copyright (C) 2006		Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2006-2021	Regis Houssin		<regis.houssin@inodbox.com>
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
 *       \file       htdocs/adherents/ldap.php
 *       \ingroup    ldap member
 *       \brief      Page fiche LDAP adherent
 */

// Load PowerERP environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/member.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/ldap.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/ldap.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';

// Load translation files required by the page
$langs->loadLangs(array("companies", "members", "ldap", "admin"));

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alphanohtml');
$action = GETPOST('action', 'aZ09');

// Protection
$socid = 0;
if ($user->socid > 0) {
	$socid = $user->socid;
}

$object = new Adherent($db);

// Fetch object
if ($id > 0 || !empty($ref)) {
	// Load member
	$result = $object->fetch($id, $ref);

	// Define variables to know what current user can do on users
	$canadduser = (!empty($user->admin) || !empty($user->rights->user->user->creer));
	// Define variables to know what current user can do on properties of user linked to edited member
	if ($object->user_id) {
		// $User is the user who edits, $object->user_id is the id of the related user in the edited member
		$caneditfielduser = ((($user->id == $object->user_id) && !empty($user->rights->user->self->creer))
			|| (($user->id != $object->user_id) && !empty($user->rights->user->user->creer)));
		$caneditpassworduser = ((($user->id == $object->user_id) && $user->rights->user->self->password)
			|| (($user->id != $object->user_id) && !empty($user->rights->user->user->password)));
	}
}

// Define variables to determine what the current user can do on the members
$canaddmember = $user->hasRight('adherent', 'creer');
// Define variables to determine what the current user can do on the properties of a member
if ($id) {
	$caneditfieldmember = $user->hasRight('adherent', 'creer');
}

// Security check
$result = restrictedArea($user, 'adherent', $object->id, '', '', 'socid', 'rowid', 0);


/*
 * Actions
 */

if ($action == 'powererp2ldap') {
	$ldap = new Ldap();
	$result = $ldap->connect_bind();

	if ($result > 0) {
		$info = $object->_load_ldap_info();
		$dn = $object->_load_ldap_dn($info);
		$olddn = $dn; // We can say that old dn = dn as we force synchro

		$result = $ldap->update($dn, $info, $user, $olddn);
	}

	if ($result >= 0) {
		setEventMessages($langs->trans("MemberSynchronized"), null, 'mesgs');
	} else {
		setEventMessages($ldap->error, $ldap->errors, 'errors');
	}
}



/*
 *	View
 */

$form = new Form($db);

llxHeader('', $langs->trans("Member"), 'EN:Module_Foundations|FR:Module_Adh&eacute;rents|ES:M&oacute;dulo_Miembros');

$head = member_prepare_head($object);

print dol_get_fiche_head($head, 'ldap', $langs->trans("Member"), 0, 'user');

$linkback = '<a href="'.DOL_URL_ROOT.'/adherents/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

dol_banner_tab($object, 'rowid', $linkback);

print '<div class="fichecenter">';

print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';

// Login
print '<tr><td class="titlefield">'.$langs->trans("Login").' / '.$langs->trans("Id").'</td><td class="valeur">'.$object->login.'&nbsp;</td></tr>';

// If there is a link to password not crypted, we show value in database here so we can compare because it is shown nowhere else
if (!empty($conf->global->LDAP_MEMBER_FIELD_PASSWORD)) {
	print '<tr><td>'.$langs->trans("LDAPFieldPasswordNotCrypted").'</td>';
	print '<td class="valeur">'.$object->pass.'</td>';
	print "</tr>\n";
}

$adht = new AdherentType($db);
$adht->fetch($object->typeid);

// Type
print '<tr><td>'.$langs->trans("Type").'</td><td class="valeur">'.$adht->getNomUrl(1)."</td></tr>\n";

// LDAP DN
print '<tr><td>LDAP '.$langs->trans("LDAPMemberDn").'</td><td class="valeur">'.getDolGlobalString('LDAP_MEMBER_DN')."</td></tr>\n";

// LDAP Cle
print '<tr><td>LDAP '.$langs->trans("LDAPNamingAttribute").'</td><td class="valeur">'.getDolGlobalString('LDAP_KEY_MEMBERS')."</td></tr>\n";

// LDAP Server
print '<tr><td>LDAP '.$langs->trans("Type").'</td><td class="valeur">'.getDolGlobalString('LDAP_SERVER_TYPE')."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("Version").'</td><td class="valeur">'.getDolGlobalString('LDAP_SERVER_PROTOCOLVERSION')."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPPrimaryServer").'</td><td class="valeur">'.getDolGlobalString('LDAP_SERVER_HOST')."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPSecondaryServer").'</td><td class="valeur">'.getDolGlobalString('LDAP_SERVER_HOST_SLAVE')."</td></tr>\n";
print '<tr><td>LDAP '.$langs->trans("LDAPServerPort").'</td><td class="valeur">'.getDolGlobalString('LDAP_SERVER_PORT')."</td></tr>\n";

print '</table>';

print '</div>';

print dol_get_fiche_end();

/*
 * Action bar
 */
print '<div class="tabsAction">';

if (!empty($conf->global->LDAP_MEMBER_ACTIVE) && getDolGlobalString('LDAP_MEMBER_ACTIVE') != Ldap::SYNCHRO_LDAP_TO_POWERERP) {
	print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=powererp2ldap">'.$langs->trans("ForceSynchronize").'</a></div>';
}

print "</div>\n";

if (!empty($conf->global->LDAP_MEMBER_ACTIVE) && getDolGlobalString('LDAP_MEMBER_ACTIVE') != Ldap::SYNCHRO_LDAP_TO_POWERERP) {
	print "<br>\n";
}



// Affichage attributs LDAP
print load_fiche_titre($langs->trans("LDAPInformationsForThisMember"));

print '<table width="100%" class="noborder">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("LDAPAttributes").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>';

// Lecture LDAP
$ldap = new Ldap();
$result = $ldap->connect_bind();
if ($result > 0) {
	$info = $object->_load_ldap_info();
	$dn = $object->_load_ldap_dn($info, 1);
	$search = "(".$object->_load_ldap_dn($info, 2).")";

	if (empty($dn)) {
		$langs->load("errors");
		print '<tr class="oddeven"><td colspan="2"><span class="error">'.$langs->trans("ErrorModuleSetupNotComplete", $langs->transnoentitiesnoconv("Member")).'</span></td></tr>';
	} else {
		$records = $ldap->getAttribute($dn, $search);

		//print_r($records);

		// Show tree
		if (((!is_numeric($records)) || $records != 0) && (!isset($records['count']) || $records['count'] > 0)) {
			if (!is_array($records)) {
				print '<tr class="oddeven"><td colspan="2"><span class="error">'.$langs->trans("ErrorFailedToReadLDAP").'</span></td></tr>';
			} else {
				$result = show_ldap_content($records, 0, $records['count'], true);
			}
		} else {
			print '<tr class="oddeven"><td colspan="2">'.$langs->trans("LDAPRecordNotFound").' (dn='.dol_escape_htmltag($dn).' - search='.dol_escape_htmltag($search).')</td></tr>';
		}
	}

	$ldap->unbind();
} else {
	setEventMessages($ldap->error, $ldap->errors, 'errors');
}


print '</table>';

// End of page
llxFooter();
$db->close();
