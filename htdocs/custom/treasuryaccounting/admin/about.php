<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2019      Open-DSI             <support@open-dsi.fr>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	    \file       htdocs/treasuryaccounting/admin/about.php
 *		\ingroup    treasuryaccounting
 *		\brief      Page about of treasuryaccounting module
 */

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';		// to work if your module directory is into a subdir of root htdocs directory
if (! $res) die("Include of main fails");
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/treasuryaccounting/lib/treasuryaccounting.lib.php');

$langs->load("admin");
$langs->load("treasuryaccounting@treasuryaccounting");
$langs->load("opendsi@treasuryaccounting");

if (!$user->admin) accessforbidden();


/**
 * View
 */

llxHeader();

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("TreasuryAccountingSetup"),$linkback,'title_setup');
print "<br>\n";


$head=treasuryaccounting_admin_prepare_head();

dol_fiche_head($head, 'about', $langs->trans("About"), 0, 'action');

print '<table width="100%"><tr>'."\n";
print '<td width="310px"><img src="../img/opendsi_powererp_preferred_partner.png" /></td>'."\n";
print '<td align="left" valign="top"><p>'.$langs->trans("OpenDsiAboutDesc").'</p></td>'."\n";
print '</tr></table>'."\n";

dol_fiche_end();


llxFooter();

$db->close();
