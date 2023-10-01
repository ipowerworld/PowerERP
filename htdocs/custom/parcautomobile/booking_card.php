<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *   	\file       booking_card.php
 *		\ingroup    parcautomobile
 *		\brief      Page to create/edit/view booking
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $powererp_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

// Load powererp environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
dol_include_once('/parcautomobile/class/booking.class.php');
dol_include_once('/parcautomobile/lib/parcautomobile_booking.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("parcautomobile@parcautomobile", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'bookingcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Booking($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->parcautomobile->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('bookingcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha')) {
		$search[$key] = GETPOST('search_' . $key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->parcautomobile->booking->read;
	$permissiontovalidate = $user->rights->parcautomobile->booking->validate;
	$permissiontofinish = $user->rights->parcautomobile->booking->finish;
	$permissiontoadd = $user->rights->parcautomobile->booking->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->parcautomobile->booking->delete;
	$permissionnote = $user->rights->parcautomobile->booking->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->parcautomobile->booking->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->parcautomobile->multidir_output[isset($object->entity) ? $object->entity : 1] . '/booking';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->parcautomobile->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/parcautomobile/booking_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/parcautomobile/booking_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'PARCAUTOMOBILE_BOOKING_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'PARCAUTOMOBILE_BOOKING_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_BOOKING_TO';
	$trackid = 'booking' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Booking");
$help_url = '';
llxHeader('', $title, $help_url);

// Example : Adding jquery code
// print '<script type="text/javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Booking")), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>' . "\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Booking"), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">' . "\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = bookingPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Booking"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteBooking'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx') {
		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/parcautomobile/booking_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled)) {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd) {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref .= '<input type="hidden" name="action" value="classin">';
	 $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref .= '</form>';
	 } else {
	 $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	 }
	 } else {
	 if (! empty($object->fk_project)) {
	 $proj = new Project($db);
	 $proj->fetch($object->fk_project);
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">' . "\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';


	//********** CONTENEUR

	print '<script src="https://kit.fontawesome.com/0bb31d8f48.js" crossorigin="anonymous"></script>';
	$All_book = $object->getConteneurofBooking($id);
	$All_conteneur_libre = $object->getConteneur();


	if (isset($_POST['st_action'])) {
		if ($_POST['st_action'] == 'create') {
			$object->AddConteneur($object->id, $_POST['conteneur'], $_POST['date'], $_POST['date_sortie_vide']);
			echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
		}
		if ($_POST['st_action'] == 'edit') {
			echo $_POST['date'];
			$object->updateConteneur($_POST['id'], $_POST['conteneur'], $_POST['date'], $_POST['date_sortie_vide']);
			echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
		}
	}

	if (isset($_GET['idDelete'])) {
		$object->deleteConteneur($_GET['idDelete']);
		echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
	}

	print '
			<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
				<tbody>
					<tr class="titre">
						<td class="nobordernopadding valignmiddle col-title">
							<div class="titre inline-block">CONTENEUR</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="div-table-responsive-no-min">';

	if ($object->status == $object::STATUS_DRAFT) {
		print '	<table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
					<tbody>
						<tr class="liste_titre">
							<td class="left">Réference</td>
							<td class="center">Date échéance</td>
							<td class="center">Date de sortie vide</td>
							<td class="center">Actions</td>
						</tr>';
		if (empty($All_book)) {
			print '
			<tr>
				<td class="impair" colspan="7"><span class="opacitymedium">Aucun conteneur booké</span></td>
			</tr>			
		';
		} else {
			//  List and edit of records
			$i = 0;
			while ($i < count($All_book)) {
				$date = new DateTime($All_book[$i]->date_echeance);
				$date_vide = new DateTime($All_book[$i]->date_sortie_vide);
				if (isset($_GET['idEdit']) && $_GET['idEdit'] == $All_book[$i]->rowid) {
					// 	edit record
					print '	<form action=' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . ' method="POST">
							<input type="hidden" name="st_action" value="edit" />
							<input type="hidden" name="id" value="' . $All_book[$i]->rowid . '" />
								<tr class="oddeven">
									<td class="left">
										<select name="conteneur" class="select" required>
											<option value="' . $All_book[$i]->conteneur . '">' . $object->getRefConteneur($All_book[$i]->conteneur) . '</option>';
					$k = 0;
					while ($k < count($All_conteneur_libre)) {
						print '<option value="' . $All_conteneur_libre[$k]->rowid . '">' . $All_conteneur_libre[$k]->ref . '</option>';
						$k++;
					}
					print '				</select>
									</td>
									<td class="center">
										<input type="date" name="date" required value="' . $All_book[$i]->date_echeance . '"/>
									</td>
									<td class="center">
										<input type="date" name="date_sortie_vide" required value="' . $All_book[$i]->date_sortie_vide . '"/>
									</td>
									<td style="display:flex; height: fit-content;" class="center"><button class="butAction">Modifier</button><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" class="butActionDelete">Annuler</a></td>
								</tr>
							</form>';
				} else {
					// List record
					print '	<tr class="oddeven">
						<td class="left"><a href="conteneur_card.php?id=' . $All_book[$i]->rowid . '&save_lastsearch_values=1"><img src="./img/object_conteneur.png" class="paddingright classfortooltip small"> ' . $object->getRefConteneur($All_book[$i]->conteneur) . '</a></td>
						<td class="center">' . date_format($date, 'd M Y') . '</td>
						<td class="center">' . date_format($date_vide, 'd M Y') . '</td>
						<td class="center"><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&idEdit=' . $All_book[$i]->rowid . '" title="Modifier"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&idDelete=' . $All_book[$i]->conteneur . '" title="Supprimer"><i class="fa fa-trash"></i></a></td>
					</tr>';
				}
				$i++;
			}
		}

		// new record
		print '
		<form action=' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . ' method="POST">
			<input type="hidden" name="st_action" value="create" />
			<tr class="oddeven">	
				<td class="center">
					<select name="conteneur" class="select" required>
						<option value="">Selectionner le conteneur</option>';
		$j = 0;
		while ($j < count($All_conteneur_libre)) {
			print '<option value="' . $All_conteneur_libre[$j]->rowid . '">' . $All_conteneur_libre[$j]->ref . '</option>';
			$j++;
		}
		print '	</select>
				</td>
				<td class="center">	
					<input type="date" name="date" required/>
				</td>
				<td class="center">	
					<input type="date" name="date_sortie_vide" required/>
				</td>
				<td class="center"><button class="butAction">Ajouter</button></td>
			</tr>
		</form>';
	} else {
		print '	<table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
					<tbody>
						<tr class="liste_titre">
							<td class="left">Réference</td>
							<td class="center">Date échéance</td>
							<td class="center">Date de sortie vide</td>
						</tr>';
		if (empty($All_book)) {
			print '
						<tr>
							<td class="impair" colspan="7"><span class="opacitymedium">Aucun conteneur booké</span></td>
						</tr>';
		} else {
			$l = 0;
			while ($l < count($All_book)) {
				$date = new DateTimeImmutable($All_book[$l]->date_echeance);
				$date_sortie_vide = new DateTimeImmutable($All_book[$l]->date_sortie_vide);
				print '	<tr class="oddeven">
						<td class="left"><a href="conteneur_card.php?id=' . $All_book[$l]->rowid . '&save_lastsearch_values=1"><img src="./img/object_conteneur.png" class="paddingright classfortooltip small"> ' . $object->getRefConteneur($All_book[$l]->conteneur) . '</a></td>
						<td class="center">' . date_format($date, 'd M Y') . '</td>
						<td class="center">' . date_format($date_sortie_vide, 'd M Y') . '</td>
					</tr>';
				$l++;
			}
		}
	}

	print '</tbody></table></div></div></div>';

	print '<div class="clearboth"></div><br><br>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '' : '#line_' . GETPOST('lineid', 'int')) . '" method="POST">
		<input type="hidden" name="token" value="' . newToken() . '">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id . '">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	if ($action == 'confirm_load') {
		$object->status = $object::STATUS_LOAD;
		$object->loaded($user);
		echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
	}

	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Send
			// if (empty($user->socid)) {
			// 	print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init&token=' . newToken() . '#formmailbeforetitle');
			// }

			// Back to draft
			// if ($object->status == $object::STATUS_VALIDATED) {
			// 	print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontoadd);
			// }

			if ($object->status == $object::STATUS_LOAD) {
				print dolGetButtonAction('Retour en approbation', '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontovalidate || $permissiontofinish);
				print dolGetButtonAction('Terminer', '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes&token=' . newToken(), '', $permissiontofinish);
			}
			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);


			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_load&confirm=yes&token=' . newToken(), '', $permissiontovalidate);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Clone
			// print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . (!empty($object->socid) ? '&socid=' . $object->socid : '') . '&action=clone&token=' . newToken(), '', $permissiontoadd);

			/*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Enable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Re-Open'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete&token=' . newToken(), '', $permissiontodelete);
		}
		print '</div>' . "\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	$transport = $object->getTransport();
	function getRefArret($id)
	{
		global $db;
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_arret WHERE rowid = " . $id;
		$resql = $db->query($sql);
		$item = $db->fetch_object($resql);
		return $item->ref;
	}

	function getTitleTransport($transport)
	{
		global $db;
		$title = "";

		// Si le transport est conteneurisee
		if ($transport->type == 0 and $transport->conteneur != null) {
			$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_conteneur WHERE rowid = " . $transport->conteneur;
			$resql = $db->query($sql);
			$item = $db->fetch_object($resql);
			$title .= "Numero conteneur : " . $item->ref . "\n";
		}

		// chauffeur
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_chauffeur WHERE rowid = " . $transport->chauffeur;
		$resql = $db->query($sql);
		$item = $db->fetch_object($resql);
		$title .= "Chauffeur : " . $item->ref . "\n";

		// vehicule
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE rowid = " . $transport->vehicule;
		$resql = $db->query($sql);
		$item = $db->fetch_object($resql);
		$title .= "Vehicule : " . $item->ref;

		return $title;
	}

	if ($action != 'presend') {
		print '	<div class="fichecenter">
					<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
						<tbody>
							<tr class="titre" >
								<td class="nobordernopadding valignmiddle col-title">
									<div class="titre inline-block">TRANSPORT DU BOOKING</div>';
		if (count($transport) > 3) {
			print '<button class="butAction small" id="plus">VOIR PLUS</button>';
			print '<button class="butAction small" id="moins" style="opacity:0; width: 0;">VOIR MOINS</button>';
		}
		print '					</td>
							</tr>
						</tbody>
					</table>
					<table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
						<tbody>';

		if (empty($transport)) {
			print '			<tr>
								<td class="impair" colspan="7"><span class="opacitymedium">Aucun voyage effectué pour ce booking</span></td>
							</tr>';
		} else {
			print '<div id="st-container" style="overflow: hidden; height: 410px;">';
			$i = 0;
			while ($i < count($transport)) {
				$class_veh = '';
				$class_cont = 'end';
				$date2 = new DateTimeImmutable($transport[$i]->date_depart);
				$date3 = new DateTimeImmutable($transport[$i]->date_arrive);
				$title = getTitleTransport($transport[$i]);
				if ($transport[$i]->status == 2) {
					$class_veh = 'roule';
					$class_cont = 'center';
				}

				print '	<a href="./transport_card.php?id=' . $transport[$i]->rowid . '" class="oddeven st-row" title="' . $title . '">
							<div  class="position-container">
								<img src="./img/object_map_marker.png"  class="map-marker"/>
								<div>
									' . getRefArret($transport[$i]->lieu_depart) . ' <br> ' . date_format($date2, 'd M Y') . ' à ' . date_format($date2, 'H:s') . '
								</div>
							</div>
							<div class="voyage-container">
								<div class="voiture-container" style="justify-content:' . $class_cont . ';">';
				if ($transport[$i]->type == 0) {
					print '<img src="./img/object_camion_conteneurise.png" class="' . $class_veh . '"/>';
				} else {
					print '<img src="./img/object_camion_conventionnel.png" class="' . $class_veh . '"/>';
				}

				print '			</div>
								<div class="route"></div>
							</div>
							<div class="position-container">
								<img src="./img/object_map_marker.png"  class="map-marker"/>
								<div>
								' . getRefArret($transport[$i]->lieu_arrive) . ' <br> ' . date_format($date3, 'd M Y') . ' à ' . date_format($date3, 'H:s') . '
								</div>
							</div>
						</a>';
				$i++;
			}
			print '			</div>';
		}

		print '			</tbody>
					</table>
				</div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'booking';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->parcautomobile->dir_output;
	$trackid = 'booking' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();

?>

<style>
	.position-container {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 20px;
		gap: 10px;
		width: 20%;
	}

	.position-container img {
		width: 30px;
		height: 45px;
	}

	.position-container div {
		text-align: center;
		font-size: 15px;
		font-weight: 600;
		/* font-weight: 500; */
		color: #263C5C;
	}

	.st-row {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: center;
	}

	.voyage-container {
		width: 60%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}

	.voiture-container {
		height: 80%;
		width: 100%;
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	.voiture-container img {
		height: 100px;
		width: auto;

	}

	.route {
		height: 3px;
		border-radius: 5px;
		width: 100%;
		background-color: black;
		transform: translateY(-3px);
	}

	.roule {
		animation-name: roule;
		animation-iteration-count: infinite;
		animation-duration: 0.5s;
	}

	@keyframes roule {
		0% {
			transform: translateX(0px);
		}

		50% {
			transform: translateX(5px);
		}
	}
</style>

<script>
	const butPlus = document.getElementById("plus");
	const butMoins = document.getElementById("moins");
	const container = document.getElementById("st-container");

	butMoins.addEventListener("click", () => {
		butMoins.style.width = "0";
		butMoins.style.opacity = "0";
		butPlus.style.width = "100px";
		butPlus.style.opacity = "1";
		container.style.height = "410px";
	})
	butPlus.addEventListener("click", () => {
		butPlus.style.width = "0";
		butPlus.style.opacity = "0";
		butMoins.style.width = "100px";
		butMoins.style.opacity = "1";
		container.style.height = "fit-content";
	})
</script>