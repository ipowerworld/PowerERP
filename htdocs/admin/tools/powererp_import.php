<?php
/* Copyright (C) 2006-2021	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2006-2012	Regis Houssin		<regis.houssin@inodbox.com>
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
 *		\file 		htdocs/admin/tools/powererp_import.php
 *		\ingroup	core
 * 		\brief      Page to import database
 */

if (! defined('CSRFCHECK_WITH_TOKEN')) {
	define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
}

// Load PowerERP environment
require '../../main.inc.php';

// Load translation files required by the page
$langs->loadLangs(array("other", "admin"));

if (!$user->admin) {
	accessforbidden();
}

$radio_dump = GETPOST('radio_dump');
$showpass = GETPOST('showpass');


/*
 * View
 */

$label = $db::LABEL;
$type = $db->type;


$help_url = 'EN:Restores|FR:Restaurations|ES:Restauraciones';
llxHeader('', '', $help_url);

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#mysql_options").<?php echo $radio_dump == 'mysql_options' ? 'show()' : 'hide()'; ?>;
	jQuery("#postgresql_options").<?php echo $radio_dump == 'postgresql_options' ? 'show()' : 'hide()'; ?>;

	jQuery("#radio_dump_mysql").click(function() {
		jQuery("#mysql_options").show();
	});
	jQuery("#radio_dump_postgresql").click(function() {
		jQuery("#postgresql_options").show();
	});
	<?php
	if ($label == 'MySQL') {
		print 'jQuery("#radio_dump_mysql").click();';
	}
	if ($label == 'PostgreSQL') {
		print 'jQuery("#radio_dump_postgresql").click();';
	}
	?>
});
</script>
<?php

print load_fiche_titre($langs->trans("Restore"), '', 'title_setup');

print '<div class="center">';
print $langs->trans("RestoreDesc", DOL_DATA_ROOT);
print '</div>';
print '<br>';

?>
<fieldset>
<legend style="font-size: 3em">1</legend>
<?php
print '<span class="opacitymedium">';
print $langs->trans("RestoreDesc2", DOL_DATA_ROOT).'<br><br>';
print '</span>';
?>
</fieldset>

<br>

<fieldset>
<legend style="font-size: 3em">2</legend>
<?php
print '<span class="opacitymedium">';
print $langs->trans("RestoreDesc3", $powererp_main_db_name).'<br><br>';
print '</span>';
?>

<?php print $langs->trans("DatabaseName").' : <b>'.$powererp_main_db_name.'</b>'; ?><br><br>

<table class="centpercent"><tr><td class="tdtop">

<?php if ($conf->use_javascript_ajax) { ?>
<div id="div_container_exportoptions">
<fieldset id="exportoptions">
	<legend><?php echo $langs->trans("ImportMethod"); ?></legend>
	<?php
	if (in_array($type, array('mysql', 'mysqli'))) {
		?>
	<div class="formelementrow">
		<input type="radio" name="what" value="mysql" id="radio_dump_mysql"<?php echo ($radio_dump == 'mysql_options' ? ' checked' : ''); ?> />
		<label for="radio_dump_mysql">MySQL (mysql)</label>
	</div>
		<?php
	} elseif (in_array($type, array('pgsql'))) {
		?>
	<div class="formelementrow">
		<input type="radio" name="what" value="mysql" id="radio_dump_postgresql"<?php echo ($radio_dump == 'postgresql_options' ? ' checked' : ''); ?> />
		<label for="radio_dump_postgresql">PostgreSQL Restore (pg_restore or psql)</label>
	</div>
		<?php
	} else {
		print 'No method available with database '.$label;
	}
	?>
</fieldset>
</div>
<?php } ?>

</td><td class="tdtop">


<div id="div_container_sub_exportoptions" >
<?php
if (in_array($type, array('mysql', 'mysqli'))) {
	print '<fieldset id="mysql_options">';
	print '<legend>'.$langs->trans('RestoreMySQL').'</legend>';
	print '<div class="formelementrow centpercent">';
	// Parameteres execution
	$command = $db->getPathOfRestore();
	if (preg_match("/\s/", $command)) {
		$command = $command = escapeshellarg($command); // Use quotes on command
	}

	$param = $powererp_main_db_name;
	$param .= " -h ".$powererp_main_db_host;
	if (!empty($powererp_main_db_port)) {
		$param .= " -P ".$powererp_main_db_port;
	}
	$param .= " -u ".$powererp_main_db_user;
	$paramcrypted = $param;
	$paramclear = $param;
	if (!empty($powererp_main_db_pass)) {
		$paramcrypted .= " -p".preg_replace('/./i', '*', $powererp_main_db_pass);
		$paramclear .= " -p".$powererp_main_db_pass;
	}

	echo $langs->trans("ImportMySqlDesc");
	print '<br>';
	print '<textarea rows="1" id="restorecommand" class="centpercent">'.$langs->trans("ImportMySqlCommand", $command, ($showpass ? $paramclear : $paramcrypted)).'</textarea><br>';
	print ajax_autoselect('restorecommand');

	if (empty($_GET["showpass"]) && $powererp_main_db_pass) {
		print '<br><a href="'.$_SERVER["PHP_SELF"].'?showpass=1&amp;radio_dump=mysql_options">'.$langs->trans("UnHidePassword").'</a>';
	}
	//else print '<br><a href="'.$_SERVER["PHP_SELF"].'?showpass=0&amp;radio_dump=mysql_options">'.$langs->trans("HidePassword").'</a>';
	print '</div>';
	print '</fieldset>';
} elseif (in_array($type, array('pgsql'))) {
	print '<fieldset id="postgresql_options">';
	print '<legend>Restore PostgreSQL</legend>';
	print '<div class="formelementrow">';
	// Parameteres execution
	$command = $db->getPathOfRestore();
	if (preg_match("/\s/", $command)) {
		$command = $command = escapeshellarg($command); // Use quotes on command
	}

	$param = " -d ".$powererp_main_db_name;
	$param .= " -h ".$powererp_main_db_host;
	if (!empty($powererp_main_db_port)) {
		$param .= " -p ".$powererp_main_db_port;
	}
	$param .= " -U ".$powererp_main_db_user;
	$paramcrypted = $param;
	$paramclear = $param;
	/*if (!empty($powererp_main_db_pass))
	{
		$paramcrypted.=" -p".preg_replace('/./i','*',$powererp_main_db_pass);
		$paramclear.=" -p".$powererp_main_db_pass;
	}*/
	$paramcrypted .= " -W";
	$paramclear .= " -W";
	// With psql:
	$paramcrypted .= " -f";
	$paramclear .= " -f";

	echo $langs->trans("ImportPostgreSqlDesc");
	print '<br>';
	print '<textarea rows="1" id="restorecommand" class="centpercent">'.$langs->trans("ImportPostgreSqlCommand", $command, ($showpass ? $paramclear : $paramcrypted)).'</textarea><br>';
	print ajax_autoselect('restorecommand');
	//if (empty($_GET["showpass"]) && $powererp_main_db_pass) print '<br><a href="'.$_SERVER["PHP_SELF"].'?showpass=1&amp;radio_dump=postgresql_options">'.$langs->trans("UnHidePassword").'</a>';
	//else print '<br><a href="'.$_SERVER["PHP_SELF"].'?showpass=0&amp;radio_dump=mysql_options">'.$langs->trans("HidePassword").'</a>';
	print '</div>';

	print '<br>';

	print '</fieldset>';
}

print '</div>';


print '</td></tr></table>';
print '</fieldset>';

// End of page
llxFooter();
$db->close();
