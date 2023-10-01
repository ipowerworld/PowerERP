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
 *   	\file       transport_card.php
 *		\ingroup    parcautomobile
 *		\brief      Page to create/edit/view transport
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
dol_include_once('/parcautomobile/class/transport.class.php');
dol_include_once('/parcautomobile/lib/parcautomobile_transport.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("parcautomobile@parcautomobile", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'transportcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Transport($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->parcautomobile->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('transportcard', 'globalcard')); // Note that conf->hooks_modules contains array

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
	$permissiontoread = $user->rights->parcautomobile->transport->read;
	$permissiontobegin = $user->rights->parcautomobile->transport->begin;
	$permissiontofinish = $user->rights->parcautomobile->transport->finish;
	$permissiontoadd = $user->rights->parcautomobile->transport->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->parcautomobile->transport->delete;
	$permissionnote = $user->rights->parcautomobile->transport->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->parcautomobile->transport->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->parcautomobile->multidir_output[isset($object->entity) ? $object->entity : 1] . '/transport';

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

	$backurlforlist = dol_buildpath('/parcautomobile/transport_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/parcautomobile/transport_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'PARCAUTOMOBILE_TRANSPORT_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'PARCAUTOMOBILE_TRANSPORT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_TRANSPORT_TO';
	$trackid = 'transport' . $object->id;
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

$title = $langs->trans("Transport");
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

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Transport")), '', 'object_' . $object->picto);

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
	print load_fiche_titre($langs->trans("Transport"), '', 'object_' . $object->picto);

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

	$head = transportPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Transport"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteTransport'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
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
	$linkback = '<a href="' . dol_buildpath('/parcautomobile/transport_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

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

	// Actions

	if (isset($_POST['st_action'])) {
		if ($_POST['st_action'] == 'create_conteneur') {
			$object->postCont($object->id, $_POST['conteneur']);
			echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
		}
		if ($_POST['st_action'] == 'create_prod') {
			$object->addProd($_POST['produit'], $_POST['qte']);
			echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";

			// Si on check la contenance maximale du conteneur
			// if ($object->addProd($_POST['produit'], $_POST['qte'])) {
			// 	echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
			// } else {
			// 	setEventMessages("Le volume du conteneur est depassé", "", "warnings");
			// }
		}
	}
	if (isset($_GET['idDelete'])) {
		$object->deleteProd($_GET['idDelete']);
	}


	if (isset($_POST['edit_frais'])) {
		if (!empty($_POST['cout_carburant'])) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_transport SET prix_carburant = " . $_POST['cout_carburant'] . " WHERE rowid = " . $object->id;
			$db->query($sql);
		}
		if (!empty($_POST['taxe_poids'])) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_transport SET taxe_poids = " . $_POST['taxe_poids'] . " WHERE rowid = " . $object->id;
			$db->query($sql);
		}
		if (!empty($_POST['frais_voyage'])) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_transport SET frais_voyage = " . $_POST['frais_voyage'] . " WHERE rowid = " . $object->id;
			$db->query($sql);
		}
		if (!empty($_POST['frais_penalite'])) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_transport SET penalite = " . $_POST['frais_penalite'] . " WHERE rowid = " . $object->id;
			$db->query($sql);
		}
		echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
	}

	print '<script src="https://kit.fontawesome.com/0bb31d8f48.js" crossorigin="anonymous"></script>';
	$it = $object->getConteneurTrans($object->id);
	$conteneur = $object->getConteneur($object->booking);

	// Si le transport est conteneurise
	if ($object->type == 0) {
		print '<div>
			<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
				<tbody>
					<tr class="titre">
						<td class="nobordernopadding valignmiddle col-title">
							<div class="titre inline-block">CONTENEUR</div>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
						<tbody>
							<tr class="liste_titre">
								<td class="left">Réference du conteneur</td>';
		if ($object->status == $object::STATUS_DRAFT) {
			print				'<td class="center">Actions</td>';
		} else {
			print '				<td></td>';
		}
		print '				</tr>';
		if (!$it || isset($_GET['changeCont'])) {
			if (!isset($_GET['changeCont'])) {
				print '
							<tr>
								<td class="impair" colspan="7"><span class="opacitymedium">Aucun conteneur booké</span></td>
							</tr>';
			}
			print '	<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="POST">
								<input type="hidden" name="st_action" value="create_conteneur" />
								<tr class="oddeven">
									<td>
										<select class="select" name="conteneur" required>
											<option value="">Selectionner le conteneur</option>';
			$i = 0;
			while ($i < count($conteneur)) {

				print '					<option value="' . $conteneur[$i]->conteneur . '">' . $conteneur[$i]->refCont . '</option>';
				$i++;
			}
			print '						</select>
									</td>
									<td class="center">';
			if (isset($_GET['changeCont'])) {
				print '
						<button class="butAction">Modifier</button>
						<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">Annuler</a>';
			} else {
				print '<button class="butAction">Charger</button>';
			}


			print '					</td>
								</tr>
							</form>';
		} else {
			print '			<input type="hidden" name="st_action" value="create_conteneur" />
								<tr class="oddeven">
									<td><a href="conteneur_card.php?id=' . $it->conteneur . '"><img src="./img/object_conteneur.png" class="paddingright classfortooltip small">' . $it->refCont . '</a></td>';
			if ($object->status == $object::STATUS_DRAFT) {
				print '<td class="center"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&changeCont=1">Modifier</a></td>';
			} else {
				print '<td></td>';
			}
			print '				</tr>';
		}

		print '</tbody></table>';
	}

	// Empotage  des produits

	$produit = $object->getProdTransport();

	print '<div>
			<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
				<tbody>
					<tr class="titre">
						<td class="nobordernopadding valignmiddle col-title">
							<div class="titre inline-block">EMPOTAGE</div>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
				<tbody>
					<tr class="liste_titre">
						<td class="left">Produit</td>
						<td class="left">Volume (m<sup>3</sup>)</td>';
	if ($object->status == $object::STATUS_DRAFT) {
		print '			<td class="center">Actions</td>';
	} else {
		print '			<td></td>';
	}
	print '			</tr>';
	if (empty($produit)) {
		print '
					<tr>
						<td class="impair" colspan="7"><span class="opacitymedium">Aucun produit empoté</span></td>
					</tr>';
	} else {
		$n = 0;
		while ($n < count($produit)) {
			print '	<tr class="oddeven"> 
						<td class="left"><a href="../../product/card.php?id=' . $produit[$n]->produit . '"><span class="fas fa-cube valignmiddle widthpictotitle pictotitle" style=" color: #a69944;"></span>' . $produit[$n]->RefProd . '</a></td>
						<td class="left">' . $produit[$n]->quantite . '</td>';
			if ($object->status == $object::STATUS_DRAFT) {
				print '	<td class="center"><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&idDelete=' . $produit[$n]->rowid . '"><i class="fa fa-trash"</a></td>';
			} else {
				print '	<td></td>';
			}
			print '	</tr>';
			$n++;
		}
	}


	// Add record
	if ($object->status == $object::STATUS_DRAFT) {
		$productCommande = $object->getProductCommande($object->commande);

		print '
			<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="POST">
				<tr class="oddeven"> 
					<td class="left">
						<input type="hidden" name="st_action" value="create_prod">
						<select class="select" name="produit" required>
							<option value="">Selectionner un produit</option>';
		$m = 0;
		while ($m < count($productCommande)) {
			print '<option value="' . $productCommande[$m]->rowid . '">' . $productCommande[$m]->ref . '</option>';
			$m++;
		}

		print '			</select>
					</td>
					<td class="left">
						<input name="qte" type="number" required />
					</td>
					<td class="left"><button class="butAction">Ajouter</button></td>
				</tr>
			</form>';
	}



	print '		</tbody>
			</table></div>';


	// FRAIS DE TRANSPORT

	print '	<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
				<tbody>
					<tr class="titre">
						<td class="nobordernopadding valignmiddle col-title">
							<div class="titre inline-block">FRAIS DE TRANSPORT</div>
						</td>
					</tr>
				</tbody>
			</table>';

	if (isset($_GET['edit_frais'])) {
		print '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '" method="POST">
				<input type="hidden" name="edit_frais" />
				<table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
					<tbody>
						<tr class="liste_titre">
							<td class="left">Intitulé</td>
							<td class="left">Cout (XAF)</td>
						</tr>	
						<tr class="oddeven">
							<td class="left">Cout du carburant</td>
							<td class="left"><input type="number" name="cout_carburant" value="' . $object->prix_carburant . '" min="0"></td>
						</tr>
						<tr class="oddeven">
							<td class="left">Taxe sur poids</td>
							<td class="left"><input type="number" name="taxe_poids" value="' . $object->taxe_poids . '" min="0"></td>
						</tr>
						<tr class="oddeven">
							<td class="left">Frais de voyage</td>
							<td class="left"><input type="number" name="frais_voyage" value="' . $object->frais_voyage . '" min="0"></td>
						</tr>
						<tr class="oddeven">
							<td class="left">Frais de penalité</td>
							<td class="left"><input type="number" name="frais_penalite" value="' . $object->penalite . '" min="0"></td>
						</tr>
					</tbody>
				</table>
				<br>
				<button class="butAction">Enregistrer</button>
			</form>';
	} else {
		print ' <table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
					<tbody>
						<tr class="liste_titre">
							<td class="left">Intitulé</td>
							<td class="left">Cout (XAF)</td>
						</tr>	
						<tr class="oddeven">
							<td class="left">Cout du carburant</td>
							<td class="left">' . $object->prix_carburant . '</td>
						</tr>
						<tr class="oddeven">
							<td class="left">Taxe sur poids</td>
							<td class="left">' . $object->taxe_poids . '</td>
						</tr>
						<tr class="oddeven">
							<td class="left">Frais de voyage</td>
							<td class="left">' . $object->frais_voyage . '</td>
						</tr>
						<tr class="oddeven">
							<td class="left">Frais de penalité</td>
							<td class="left">' . $object->penalite . '</td>
						</tr>
					</tbody>
				</table><br>
				<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&edit_frais=1" class="butAction">Modifier</a>';
	}





	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

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



	// Buttons for actions
	if ($action == 'confirm_load') {
		$object->status = $object::STATUS_LOAD;
		$object->loaded($user);
		echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
	}

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
			// 	print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init&token='.newToken().'#formmailbeforetitle');
			// }

			// Back to draft
			// if ($object->status == $object::STATUS_VALIDATED) {
			// 	// print dolGetButtonAction('Terminer le transport', '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_termine&confirm=yes&token=' . newToken(), '', 1);
			// }

			if ($object->status == $object::STATUS_LOAD) {
				print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontofinish || $permissiontobegin);
				print dolGetButtonAction('Terminer le transport', '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes&token=' . newToken(), '', $permissiontofinish);
			}



			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction('Entamer le transport', '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_load&confirm=yes&token=' . newToken(), '', $permissiontobegin);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}
			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);

			// Clone
			// print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid)?'&socid='.$object->socid:'').'&action=clone&token='.newToken(), '', $permissiontoadd);

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

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		// if ($includedocgeneration) {
		// 	$objref = dol_sanitizeFileName($object->ref);
		// 	$relativepath = $objref . '/' . $objref . '.pdf';
		// 	$filedir = $conf->parcautomobile->dir_output . '/' . $object->element . '/' . $objref;
		// 	$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		// 	$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
		// 	$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
		// 	print $formfile->showdocuments('parcautomobile:Transport', $object->element . '/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		// }

		// // Show links to link elements
		// $linktoelem = $form->showLinkToObjectBlock($object, null, array('transport'));
		// $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfleft" style="display: flex; flex-wrap: wrap; width: 100%; gap: 100px;">';

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-list-alt imgforviewmode', dol_buildpath('/parcautomobile/transport_agenda.php', 1) . '?id=' . $object->id);

		// List of actions on element
		// include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		// $formactions = new FormActions($db);
		// $somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		print '</div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'transport';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->parcautomobile->dir_output;
	$trackid = 'transport' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
