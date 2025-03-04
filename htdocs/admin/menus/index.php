<?php
/* Copyright (C) 2007      Patrick Raguin       <patrick.raguin@gmail.com>
 * Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2009-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2019      Frédéric France      <frederic.france@netlogic.fr>
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
 *  \file       htdocs/admin/menus/index.php
 *  \ingroup    core
 *  \brief      Index page for menu editor
 */

// Load PowerERP environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/treeview.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("other", "admin"));

if (!$user->admin) {
	accessforbidden();
}

$dirstandard = array();
$dirsmartphone = array();
$dirmenus = array_merge(array("/core/menus/"), (array) $conf->modules_parts['menus']);
foreach ($dirmenus as $dirmenu) {
	$dirstandard[] = $dirmenu.'standard';
	$dirsmartphone[] = $dirmenu.'smartphone';
}

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');

$menu_handler_top = $conf->global->MAIN_MENU_STANDARD;
$menu_handler_smartphone = $conf->global->MAIN_MENU_SMARTPHONE;
$menu_handler_top = preg_replace('/(_backoffice\.php|_menu\.php)/i', '', $menu_handler_top);
$menu_handler_top = preg_replace('/(_frontoffice\.php|_menu\.php)/i', '', $menu_handler_top);
$menu_handler_smartphone = preg_replace('/(_backoffice\.php|_menu\.php)/i', '', $menu_handler_smartphone);
$menu_handler_smartphone = preg_replace('/(_frontoffice\.php|_menu\.php)/i', '', $menu_handler_smartphone);

$menu_handler = $menu_handler_top;

if (GETPOST("handler_origine")) {
	$menu_handler = GETPOST("handler_origine");
}
if (GETPOST("menu_handler")) {
	$menu_handler = GETPOST("menu_handler");
}

$menu_handler_to_search = preg_replace('/(_backoffice|_frontoffice|_menu)?(\.php)?/i', '', $menu_handler);


/*
 * Actions
 */

if ($action == 'up') {
	$current = array();
	$previous = array();

	// Get current position
	$sql = "SELECT m.rowid, m.position, m.type, m.fk_menu";
	$sql .= " FROM ".MAIN_DB_PREFIX."menu as m";
	$sql .= " WHERE m.rowid = ".GETPOST("menuId", "int");
	dol_syslog("admin/menus/index.php ".$sql);
	$result = $db->query($sql);
	$num = $db->num_rows($result);
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($result);
		$current['rowid'] = $obj->rowid;
		$current['order'] = $obj->position;
		$current['type'] = $obj->type;
		$current['fk_menu'] = $obj->fk_menu;
		$i++;
	}

	// Menu before
	$sql = "SELECT m.rowid, m.position";
	$sql .= " FROM ".MAIN_DB_PREFIX."menu as m";
	$sql .= " WHERE (m.position < ".($current['order'])." OR (m.position = ".($current['order'])." AND rowid < ".GETPOST("menuId", "int")."))";
	$sql .= " AND m.menu_handler='".$db->escape($menu_handler_to_search)."'";
	$sql .= " AND m.entity = ".$conf->entity;
	$sql .= " AND m.type = '".$db->escape($current['type'])."'";
	$sql .= " AND m.fk_menu = '".$db->escape($current['fk_menu'])."'";
	$sql .= " ORDER BY m.position, m.rowid";
	dol_syslog("admin/menus/index.php ".$sql);
	$result = $db->query($sql);
	$num = $db->num_rows($result);
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($result);
		$previous['rowid'] = $obj->rowid;
		$previous['order'] = $obj->position;
		$i++;
	}

	$sql = "UPDATE ".MAIN_DB_PREFIX."menu as m";
	$sql .= " SET m.position = ".((int) $previous['order']);
	$sql .= " WHERE m.rowid = ".((int) $current['rowid']); // Up the selected entry
	dol_syslog("admin/menus/index.php ".$sql);
	$db->query($sql);
	$sql = "UPDATE ".MAIN_DB_PREFIX."menu as m";
	$sql .= " SET m.position = ".((int) ($current['order'] != $previous['order'] ? $current['order'] : $current['order'] + 1));
	$sql .= " WHERE m.rowid = ".((int) $previous['rowid']); // Descend celui du dessus
	dol_syslog("admin/menus/index.php ".$sql);
	$db->query($sql);
} elseif ($action == 'down') {
	$current = array();
	$next = array();

	// Get current position
	$sql = "SELECT m.rowid, m.position, m.type, m.fk_menu";
	$sql .= " FROM ".MAIN_DB_PREFIX."menu as m";
	$sql .= " WHERE m.rowid = ".GETPOST("menuId", "int");
	dol_syslog("admin/menus/index.php ".$sql);
	$result = $db->query($sql);
	$num = $db->num_rows($result);
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($result);
		$current['rowid'] = $obj->rowid;
		$current['order'] = $obj->position;
		$current['type'] = $obj->type;
		$current['fk_menu'] = $obj->fk_menu;
		$i++;
	}

	// Menu after
	$sql = "SELECT m.rowid, m.position";
	$sql .= " FROM ".MAIN_DB_PREFIX."menu as m";
	$sql .= " WHERE (m.position > ".($current['order'])." OR (m.position = ".($current['order'])." AND rowid > ".GETPOST("menuId", "int")."))";
	$sql .= " AND m.menu_handler='".$db->escape($menu_handler_to_search)."'";
	$sql .= " AND m.entity = ".$conf->entity;
	$sql .= " AND m.type = '".$db->escape($current['type'])."'";
	$sql .= " AND m.fk_menu = '".$db->escape($current['fk_menu'])."'";
	$sql .= " ORDER BY m.position, m.rowid";
	dol_syslog("admin/menus/index.php ".$sql);
	$result = $db->query($sql);
	$num = $db->num_rows($result);
	$i = 0;
	while ($i < $num) {
		$obj = $db->fetch_object($result);
		$next['rowid'] = $obj->rowid;
		$next['order'] = $obj->position;
		$i++;
	}

	$sql = "UPDATE ".MAIN_DB_PREFIX."menu as m";
	$sql .= " SET m.position = ".((int) ($current['order'] != $next['order'] ? $next['order'] : $current['order'] + 1)); // Down the selected entry
	$sql .= " WHERE m.rowid = ".((int) $current['rowid']);
	dol_syslog("admin/menus/index.php ".$sql);
	$db->query($sql);
	$sql = "UPDATE ".MAIN_DB_PREFIX."menu as m"; // Up the next entry
	$sql .= " SET m.position = ".((int) $current['order']);
	$sql .= " WHERE m.rowid = ".((int) $next['rowid']);
	dol_syslog("admin/menus/index.php ".$sql);
	$db->query($sql);
} elseif ($action == 'confirm_delete' && $confirm == 'yes') {
	$db->begin();

	$sql = "DELETE FROM ".MAIN_DB_PREFIX."menu";
	$sql .= " WHERE rowid = ".GETPOST('menuId', 'int');
	$resql = $db->query($sql);
	if ($resql) {
		$db->commit();

		setEventMessages($langs->trans("MenuDeleted"), null, 'mesgs');

		header("Location: ".DOL_URL_ROOT.'/admin/menus/index.php?menu_handler='.$menu_handler);
		exit;
	} else {
		$db->rollback();

		$reload = 0;
		$action = '';
	}
}


/*
 * View
 */

$form = new Form($db);
$formadmin = new FormAdmin($db);

$arrayofjs = array('/includes/jquery/plugins/jquerytreeview/jquery.treeview.js', '/includes/jquery/plugins/jquerytreeview/lib/jquery.cookie.js');
$arrayofcss = array('/includes/jquery/plugins/jquerytreeview/jquery.treeview.css');

llxHeader('', $langs->trans("Menus"), '', '', 0, 0, $arrayofjs, $arrayofcss);


print load_fiche_titre($langs->trans("Menus"), '', 'title_setup');


$h = 0;

$head[$h][0] = DOL_URL_ROOT."/admin/menus.php";
$head[$h][1] = $langs->trans("MenuHandlers");
$head[$h][2] = 'handler';
$h++;

$head[$h][0] = DOL_URL_ROOT."/admin/menus/index.php";
$head[$h][1] = $langs->trans("MenuAdmin");
$head[$h][2] = 'editor';
$h++;

print dol_get_fiche_head($head, 'editor', '', -1);

print '<span class="opacitymedium">'.$langs->trans("MenusEditorDesc")."</span><br>\n";
print "<br>\n";


// Confirmation for remove menu entry
if ($action == 'delete') {
	$sql = "SELECT m.titre as title";
	$sql .= " FROM ".MAIN_DB_PREFIX."menu as m";
	$sql .= " WHERE m.rowid = ".GETPOST('menuId', 'int');
	$result = $db->query($sql);
	$obj = $db->fetch_object($result);

	print $form->formconfirm("index.php?menu_handler=".$menu_handler."&menuId=".GETPOST('menuId', 'int'), $langs->trans("DeleteMenu"), $langs->trans("ConfirmDeleteMenu", $obj->title), "confirm_delete");
}

$newcardbutton = '';
if ($user->admin) {
	$newcardbutton .= dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/admin/menus/edit.php?menuId=0&action=create&menu_handler='.urlencode($menu_handler).'&backtopage='.urlencode($_SERVER['PHP_SELF']));
}

print '<form name="newmenu" class="nocellnopadd" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" action="change_menu_handler">';
print $langs->trans("MenuHandler").': ';
$formadmin->select_menu_families($menu_handler.(preg_match('/_menu/', $menu_handler) ? '' : '_menu'), 'menu_handler', array_merge($dirstandard, $dirsmartphone));
print ' &nbsp; <input type="submit" class="button" value="'.$langs->trans("Refresh").'">';

print '<div class="floatright">';
print $newcardbutton;
print '</div>';

print '</form>';

print '<br>';

print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("TreeMenuPersonalized").'</td>';
print '<td class="right"><div id="iddivjstreecontrol"><a href="#">'.img_picto('', 'folder', 'class="paddingright"').$langs->trans("UndoExpandAll").'</a>';
print ' | <a href="#">'.img_picto('', 'folder-open', 'class="paddingright"').$langs->trans("ExpandAll").'</a></div></td>';
print '</tr>';

print '<tr>';
print '<td colspan="2">';

// ARBORESCENCE

$rangLast = 0;
$idLast = -1;
if ($conf->use_javascript_ajax) {
	/*-------------------- MAIN -----------------------
	tableau des elements de l'arbre:
	c'est un tableau a 2 dimensions.
	Une ligne represente un element : data[$x]
	chaque ligne est decomposee en 3 donnees:
	  - l'index de l'élément
	  - l'index de l'élément parent
	  - la chaine a afficher
	ie: data[]= array (index, index parent, chaine )
	*/

	//il faut d'abord declarer un element racine de l'arbre

	$data[] = array('rowid'=>0, 'fk_menu'=>-1, 'title'=>"racine", 'mainmenu'=>'', 'leftmenu'=>'', 'fk_mainmenu'=>'', 'fk_leftmenu'=>'');

	//puis tous les elements enfants

	$sql = "SELECT m.rowid, m.titre, m.langs, m.mainmenu, m.leftmenu, m.fk_menu, m.fk_mainmenu, m.fk_leftmenu, m.position, m.module";
	$sql .= " FROM ".MAIN_DB_PREFIX."menu as m";
	$sql .= " WHERE menu_handler = '".$db->escape($menu_handler_to_search)."'";
	$sql .= " AND entity = ".$conf->entity;
	//$sql.= " AND fk_menu >= 0";
	$sql .= " ORDER BY m.position, m.rowid"; // Order is position then rowid (because we need a sort criteria when position is same)

	$res  = $db->query($sql);
	if ($res) {
		$num = $db->num_rows($res);

		$i = 1;
		while ($menu = $db->fetch_array($res)) {
			if (!empty($menu['langs'])) {
				$langs->load($menu['langs']);
			}
			$titre = $langs->trans($menu['titre']);

			$entry = '<table class="nobordernopadding centpercent"><tr><td>';
			$entry .= '<strong> &nbsp; <a href="edit.php?menu_handler='.$menu_handler_to_search.'&action=edit&token='.newToken().'&menuId='.$menu['rowid'].'">'.$titre.'</a></strong>';
			$entry .= '</td><td class="right">';
			$entry .= '<a class="editfielda marginleftonly marginrightonly" href="edit.php?menu_handler='.$menu_handler_to_search.'&action=edit&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_edit('default', 0, 'class="menuEdit" id="edit'.$menu['rowid'].'"').'</a> ';
			$entry .= '<a class="marginleftonly marginrightonly" href="edit.php?menu_handler='.$menu_handler_to_search.'&action=create&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_edit_add('default').'</a> ';
			$entry .= '<a class="marginleftonly marginrightonly" href="index.php?menu_handler='.$menu_handler_to_search.'&action=delete&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_delete('default').'</a> ';
			$entry .= '&nbsp; ';
			$entry .= '<a class="marginleftonly marginrightonly" href="index.php?menu_handler='.$menu_handler_to_search.'&action=up&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_picto("Up", "1uparrow").'</a><a href="index.php?menu_handler='.$menu_handler_to_search.'&action=down&menuId='.$menu['rowid'].'">'.img_picto("Down", "1downarrow").'</a>';
			$entry .= '</td></tr></table>';

			$buttons = '<a class="editfielda marginleftonly marginrightonly" href="edit.php?menu_handler='.$menu_handler_to_search.'&action=edit&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_edit('default', 0, 'class="menuEdit" id="edit'.$menu['rowid'].'"').'</a> ';
			$buttons .= '<a class="marginleftonly marginrightonly" href="edit.php?menu_handler='.$menu_handler_to_search.'&action=create&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_edit_add('default').'</a> ';
			$buttons .= '<a class="marginleftonly marginrightonly" href="index.php?menu_handler='.$menu_handler_to_search.'&action=delete&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_delete('default').'</a> ';
			$buttons .= '&nbsp; ';
			$buttons .= '<a class="marginleftonly marginrightonly" href="index.php?menu_handler='.$menu_handler_to_search.'&action=up&token='.newToken().'&menuId='.$menu['rowid'].'">'.img_picto("Up", "1uparrow").'</a><a href="index.php?menu_handler='.$menu_handler_to_search.'&action=down&menuId='.$menu['rowid'].'">'.img_picto("Down", "1downarrow").'</a>';

			$data[] = array(
				'rowid'=>$menu['rowid'],
				'module'=>$menu['module'],
				'fk_menu'=>$menu['fk_menu'],
				'title'=>$titre,
				'mainmenu'=>$menu['mainmenu'],
				'leftmenu'=>$menu['leftmenu'],
				'fk_mainmenu'=>$menu['fk_mainmenu'],
				'fk_leftmenu'=>$menu['fk_leftmenu'],
				'position'=>$menu['position'],
				'entry'=>$entry,
				'buttons'=>$buttons
			);
			$i++;
		}
	}

	global $tree_recur_alreadyadded; // This var was def into tree_recur

	//var_dump($data);

	// Appelle de la fonction recursive (ammorce) avec recherche depuis la racine.
	//tree_recur($data, $data[0], 0, 'iddivjstree', 0, 1);  // use this to get info on name and foreign keys of menu entry
	tree_recur($data, $data[0], 0, 'iddivjstree', 0, 0); // $data[0] is virtual record 'racine'


	print '</td>';

	print '</tr>';

	print '</table>';


	// Process remaining records (records that are not linked to root by any path)
	$remainingdata = array();
	foreach ($data as $datar) {
		if (empty($datar['rowid']) || !empty($tree_recur_alreadyadded[$datar['rowid']])) {
			continue;
		}
		$remainingdata[] = $datar;
	}

	if (count($remainingdata)) {
		print '<table class="noborder centpercent">';

		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("NotTopTreeMenuPersonalized").'</td>';
		print '<td class="right"></td>';
		print '</tr>';

		print '<tr>';
		print '<td colspan="2">';
		foreach ($remainingdata as $datar) {
			$father = array('rowid'=>$datar['rowid'], 'title'=>"???", 'mainmenu'=>$datar['fk_mainmenu'], 'leftmenu'=>$datar['fk_leftmenu'], 'fk_mainmenu'=>'', 'fk_leftmenu'=>'');
			//print 'Start with rowid='.$datar['rowid'].' mainmenu='.$father ['mainmenu'].' leftmenu='.$father ['leftmenu'].'<br>'."\n";
			tree_recur($data, $father, 0, 'iddivjstree'.$datar['rowid'], 1, 1);
		}

		print '</td>';

		print '</tr>';

		print '</table>';
	}

	print '</div>';
} else {
	$langs->load("errors");
	setEventMessages($langs->trans("ErrorFeatureNeedJavascript"), null, 'errors');
}

print '<br>';

// End of page
llxFooter();
$db->close();
