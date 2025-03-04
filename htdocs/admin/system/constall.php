<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2009      Regis Houssin        <regis.houssin@inodbox.com>
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
 *		\file 		htdocs/admin/system/constall.php
 *		\brief      Page to show all PowerERP setup (config file and database constants)
 */

// Load PowerERP environment
require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("install", "user", "admin"));


if (!$user->admin) {
	accessforbidden();
}


/*
 * View
 */

llxHeader();

print load_fiche_titre($langs->trans("SummaryConst"), '', 'title_setup');


print load_fiche_titre($langs->trans("ConfigurationFile").' ('.$conffiletoshowshort.')');
// Parameters in conf.php file (when a parameter start with ?, it is shown only if defined)
$configfileparameters = array(
							'powererp_main_url_root',
							'powererp_main_url_root_alt',
							'powererp_main_document_root',
							'powererp_main_document_root_alt',
							'powererp_main_data_root',
							'separator',
							'powererp_main_db_host',
							'powererp_main_db_port',
							'powererp_main_db_name',
							'powererp_main_db_type',
							'powererp_main_db_user',
							'powererp_main_db_pass',
							'powererp_main_db_character_set',
							'powererp_main_db_collation',
							'?powererp_main_db_prefix',
							'separator',
							'powererp_main_authentication',
							'separator',
							'?powererp_main_auth_ldap_login_attribute',
							'?powererp_main_auth_ldap_host',
							'?powererp_main_auth_ldap_port',
							'?powererp_main_auth_ldap_version',
							'?powererp_main_auth_ldap_dn',
							'?powererp_main_auth_ldap_admin_login',
							'?powererp_main_auth_ldap_admin_pass',
							'?powererp_main_auth_ldap_debug',
							'separator',
							'?powererp_lib_FPDF_PATH',
							'?powererp_lib_TCPDF_PATH',
							'?powererp_lib_FPDI_PATH',
							'?powererp_lib_TCPDI_PATH',
							'?powererp_lib_NUSOAP_PATH',
							'?powererp_lib_GEOIP_PATH',
							'?powererp_lib_ODTPHP_PATH',
							'?powererp_lib_ODTPHP_PATHTOPCLZIP',
							'?powererp_js_CKEDITOR',
							'?powererp_js_JQUERY',
							'?powererp_js_JQUERY_UI',
							'?powererp_font_DOL_DEFAULT_TTF',
							'?powererp_font_DOL_DEFAULT_TTF_BOLD',
							'separator',
							'?powererp_mailing_limit_sendbyweb',
							'?powererp_mailing_limit_sendbycli',
							'?powererp_mailing_limit_sendbyday',
							'?powererp_strict_mode'
						);
$configfilelib = array(
//					'separator',
					$langs->trans("URLRoot"),
					$langs->trans("URLRoot").' (alt)',
					$langs->trans("DocumentRootServer"),
					$langs->trans("DocumentRootServer").' (alt)',
					$langs->trans("DataRootServer"),
					'separator',
					$langs->trans("DatabaseServer"),
					$langs->trans("DatabasePort"),
					$langs->trans("DatabaseName"),
					$langs->trans("DriverType"),
					$langs->trans("DatabaseUser"),
					$langs->trans("DatabasePassword"),
					$langs->trans("DBStoringCharset"),
					$langs->trans("DBSortingCharset"),
					$langs->trans("Prefix"),
					'separator',
					$langs->trans("AuthenticationMode"),
					'separator',
					'powererp_main_auth_ldap_login_attribute',
					'powererp_main_auth_ldap_host',
					'powererp_main_auth_ldap_port',
					'powererp_main_auth_ldap_version',
					'powererp_main_auth_ldap_dn',
					'powererp_main_auth_ldap_admin_login',
					'powererp_main_auth_ldap_admin_pass',
					'powererp_main_auth_ldap_debug',
					'separator',
					'powererp_lib_TCPDF_PATH',
					'powererp_lib_FPDI_PATH',
					'powererp_lib_NUSOAP_PATH',
					'powererp_lib_GEOIP_PATH',
					'powererp_lib_ODTPHP_PATH',
					'powererp_lib_ODTPHP_PATHTOPCLZIP',
					'powererp_js_CKEDITOR',
					'powererp_js_JQUERY',
					'powererp_js_JQUERY_UI',
					'powererp_font_DOL_DEFAULT_TTF',
					'powererp_font_DOL_DEFAULT_TTF_BOLD',
					'separator',
					'Limit nb of email sent by page',
					'Strict mode is on/off'
					);

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td width="280">'.$langs->trans("Label").'</td>';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '</tr>'."\n";
$i = 0;
foreach ($configfileparameters as $key) {
	$ignore = 0;

	if ($key == 'powererp_main_url_root_alt' && empty(${$key})) {
		$ignore = 1;
	}
	if ($key == 'powererp_main_document_root_alt' && empty(${$key})) {
		$ignore = 1;
	}

	if (empty($ignore)) {
		$newkey = preg_replace('/^\?/', '', $key);

		if (preg_match('/^\?/', $key) && empty(${$newkey})) {
			$i++;
			continue; // We discard parametes starting with ?
		}

		if ($newkey == 'separator' && $lastkeyshown == 'separator') {
			$i++;
			continue;
		}

		print '<tr class="oddeven">';
		if ($newkey == 'separator') {
			print '<td colspan="3">&nbsp;</td>';
		} else {
			// Label
			print "<td>".$configfilelib[$i].'</td>';
			// Key
			print '<td>'.$newkey.'</td>';
			// Value
			print "<td>";
			if ($newkey == 'powererp_main_db_pass') {
				print preg_replace('/./i', '*', ${$newkey});
			} elseif ($newkey == 'powererp_main_url_root' && preg_match('/__auto__/', ${$newkey})) {
				print ${$newkey}.' => '.constant('DOL_MAIN_URL_ROOT');
			} else {
				print ${$newkey};
			}
			if ($newkey == 'powererp_main_url_root' && ${$newkey} != DOL_MAIN_URL_ROOT) {
				print ' (currently overwritten by autodetected value: '.DOL_MAIN_URL_ROOT.')';
			}
			print "</td>";
		}
		print "</tr>\n";
		$lastkeyshown = $newkey;
	}
	$i++;
}
print '</table>';
print '<br>';



// Parameters in database
print load_fiche_titre($langs->trans("Database"));
print '<table class="noborder">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
if (!isModEnabled('multicompany') || !$user->entity) {
	print '<td>'.$langs->trans("Entity").'</td>'; // If superadmin or multicompany disabled
}
print "</tr>\n";

$sql = "SELECT";
$sql .= " rowid";
$sql .= ", ".$db->decrypt('name')." as name";
$sql .= ", ".$db->decrypt('value')." as value";
$sql .= ", type";
$sql .= ", note";
$sql .= ", entity";
$sql .= " FROM ".MAIN_DB_PREFIX."const";
if (!isModEnabled('multicompany')) {
	// If no multicompany mode, admins can see global and their constantes
	$sql .= " WHERE entity IN (0,".$conf->entity.")";
} else {
	// If multicompany mode, superadmin (user->entity=0) can see everything, admin are limited to their entities.
	if ($user->entity) {
		$sql .= " WHERE entity IN (".$db->sanitize($user->entity.",".$conf->entity).")";
	}
}
$sql .= " ORDER BY entity, name ASC";
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);
	$i = 0;

	while ($i < $num) {
		$obj = $db->fetch_object($resql);

		print '<tr class="oddeven">';
		print '<td>'.$obj->name.'</td>'."\n";
		print '<td>'.$obj->value.'</td>'."\n";
		if (!isModEnabled('multicompany') || !$user->entity) {
			print '<td>'.$obj->entity.'</td>'."\n"; // If superadmin or multicompany disabled
		}
		print "</tr>\n";

		$i++;
	}
}

print '</table>';

// End of page
llxFooter();
$db->close();
