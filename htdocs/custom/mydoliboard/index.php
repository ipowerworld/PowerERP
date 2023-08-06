<?php
/* Copyright (C) 2013-2016	Charlene BENKE	<charlie@pats-monkey.com>
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
 *	\file	   htdocs/mydoliboard/index.php
 *	\ingroup	Liste
 *	\brief	  Page liste des tableaux de bord personnalis�es
 */

$res=@include("../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../main.inc.php");		// For "custom" directory

dol_include_once('/mydoliboard/class/mydoliboard.class.php');

$langs->load('mydoliboard@mydoliboard');

if (!$user->rights->mydoliboard->lire) accessforbidden();

llxHeader("", "", $langs->trans("Mydoliboard"));

print_fiche_titre($langs->trans("MydoliboardList"));

$object = new Mydoliboard($db);
$lists = $object->get_all_mydoliboard();

if ($lists != -1) {
	print '<table id="mydoliboard" class="noborder" width="100%">';
	print '<thead>';
	print '<tr class="liste_titre">';
	print '<th>'.$langs->trans("label").'</th>';
	print '<th>'.$langs->trans("menu").'</th>';
	print '<th width=100px align=right>'.$langs->trans("nbBoard").'</th>';
	print '<th width=100px align=right>'.$langs->trans("active").'</th>';
	
	print '</tr>';
	print '</thead>';
	print '<tbody>';
	$var=true;
	foreach ($lists as $list) {
		if ($list->langs)
			foreach (explode(":", $list->langs) as $newlang)
				$langs->load($newlang);
		$var = ! $var;
		print "<tr ".$bc[$var].">\n";
		print "<td><a href='mydoliboard.php?idboard=".$list['rowid']."'>".$list['label']."</a></td>\n";
		print "<td align='left'>".$list['mainmenu']." / ".$list['leftmenu']." / ".$langs->trans($list['titlemenu'])."</td>\n";
		print "<td align='right'>".$list['nbBoard']."</td>\n";
		print "<td align='right'>".yn($list['active'])."</td>\n";
		print "</tr>\n";
	}
	print '</tbody>';
	print "</table>";
} else
	dol_print_error();

/*
 * Boutons actions
 */
print '<br><div class="tabsAction">';

print "</div>";

llxFooter();
$db->close();


if (!empty($conf->global->MAIN_USE_JQUERY_DATATABLES)) {
	print "\n";
	print '<script type="text/javascript">'."\n";
	print 'jQuery(document).ready(function() {'."\n";
	print 'jQuery("#mydoliboard").dataTable( {'."\n";

	print '"bPaginate": true,'."\n";
	print '"bFilter": false,'."\n";
	print '"sPaginationType": "full_numbers",'."\n";
	print '"bJQueryUI": false,'."\n"; 
	print '"oLanguage": {"sUrl": "'.$langs->trans('datatabledict').'" },'."\n";
	print '"iDisplayLength": 25,'."\n";
	print '"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],'."\n";
	print '"bSort": true,'."\n";
	
	print '} );'."\n";
	print '});'."\n";
	print '</script>'."\n";
}