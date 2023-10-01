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
 *   	\file       immobilisations_card.php
 *		\ingroup    immobilisations
 *		\brief      Page to create/edit/view immobilisations
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
dol_include_once('/immobilisations/class/immobilisations.class.php');
dol_include_once('/immobilisations/class/categories.class.php');
dol_include_once('/immobilisations/lib/immobilisations_immobilisations.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("immobilisations@immobilisations", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'immobilisationscard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Immobilisations($db);
$object_type = new Categories($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->immobilisations->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('immobilisationscard', 'globalcard')); // Note that conf->hooks_modules contains array

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

// field for function dispay js

foreach ($object->fields as $key => $value) {
	$js_array_fields[$key] = 'field_' . $key;
}

// var_dump($js_array_fields);

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 1;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->immobilisations->immobilisations->read;
	$permissiontoadd = $user->rights->immobilisations->immobilisations->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontovalidate = $user->rights->immobilisations->immobilisations->validate;
	$permissiontoputinapproval = $user->rights->immobilisations->immobilisations->inApproval;
	$permissiontoconsumption = $user->rights->immobilisations->immobilisations->consumption;
	$permissiontodelete = $user->rights->immobilisations->immobilisations->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->immobilisations->immobilisations->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->immobilisations->immobilisations->write; // Used by the include of actions_dellink.inc.php
	$permissiontoscrapp = $user->rights->immobilisations->immobilisations->scrapp;
	$permissiontoaccount = $user->rights->immobilisations->immobilisations->account;
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
	$permissiontoscrapp = 1;
	$permissiontoaccount = 1;
}


$upload_dir = $conf->immobilisations->multidir_output[isset($object->entity) ? $object->entity : 1] . '/immobilisations';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->immobilisations->enabled)) accessforbidden();
// Security check
if (!$user->rights->immobilisations->immobilisations->write) accessforbidden();


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

	$backurlforlist = dol_buildpath('/immobilisations/views/immobilisations_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/immobilisations/views/immobilisations_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'immobilisations_IMMOBILISATIONS_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'immobilisations_IMMOBILISATIONS_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_IMMOBILISATIONS_TO';
	$trackid = 'immobilisations' . $object->id;
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
$defaultva = get_default_tva($mysoc, $mysoc);
$immobilisation = $object->fetchAll();
$type_periode = $object->typePeriodicité();



// print $fk_product;die();

// print $object->fetchImmoProduct(1);

$title = $langs->trans("Immobilisations");
$help_url = '';
llxHeader('', $title, $help_url);


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Immobilisations")), '', 'object_' . $object->picto);

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

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT . '/custom/immobilisations/tpl/commonfields_add.tpl.php';

	print '<tr class="field_fk_product" ><td class="titlefieldcreate fieldrequired">Produit</td>'; //style="display : none"
	print '<td class="valuefieldcreate">';
	print '<select id="fk_product" class="flat minwidth200imp  --success widthcentpercentminusx" name="fk_product">';

	print '</select>';
	print '</td>';
	print '</tr>';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	// print $object->fetch_user_approv();

	print '</table>' . "\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("Immobilisations"), '', 'object_' . $object->picto);

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

	include DOL_DOCUMENT_ROOT . '/custom/immobilisations/tpl/commonfields_edit.tpl.php';

	print '<tr class="field_fk_product"><td class="titlefieldcreate fieldrequired">Produit</td>';
	print '<td class="valuefieldcreate">';
	print '<select id="fk_product" class="flat minwidth200imp  --success widthcentpercentminusx" name="fk_product">';
	print '</select>';
	print '</td>';
	print '</tr>';

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// javascript function to calculate tva amount
if ($action == 'create' || $action == 'edit') {
?>
	<script>
		let amount_ht = document.getElementById("amount_ht")
		let amount_vat = document.getElementById("amount_vat")
		let fournisseur = document.getElementById("select2-fk_fournisseur-container");
		let tx_vat = <?= $defaultva ?>;
		let amount_tx_vat = 0

		amount_ht.addEventListener("keyup", function(e) {
			if (amount_ht.value === '' || amount_ht.value === undefined || amount_ht.value == 0) {
				amount_ht.value = 0
				amount_vat.value = 0
			} else {
				amount_tx_vat = ((parseFloat(amount_ht.value) * parseFloat(tx_vat)) / 100)
				amount_vat.value = parseFloat(amount_ht.value) + parseFloat(amount_tx_vat)
			}
		})
	</script>
<?php

}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {

	$res = $object->fetch_optionals();
	$additional = $object_type->fetchOne($object->fk_categorie);

	$head = immobilisationsPrepareHead($object, $permissiontoaccount);
	print dol_get_fiche_head($head, 'card', $langs->trans("Immobilisations"), -1, $object->picto);

	$formconfirm = '';

	// ***********************  Action

	// Confirmation to delete
	if (isset($action) && $action === 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmobilisations'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if (isset($action) && $action === 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if (isset($action) && $action === 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// close asset
	if (isset($action) && $action === 'close') {
		$object->cancelImmobilisation($user, $object);
	}

	// Transfer asset to another person
	if (isset($action) && $action === 'TransferInApproval') {
		$object->putInApprovalImmobilisation($user, $object);
	}

	// Put this asset in the scrapping
	if (isset($action) && $action === 'confirm_scrapping') {
		$object->putInScrappingImmobilisation($user, $object);
	}

	// start the consumption
	if (isset($action) && $action === 'consumption') {
		$object->start_consumption($user, $object);
	}

	// Confirmation to write accountancy
	if (isset($action) && $action === 'writeAccountancy') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('WriteAccountancy'), $langs->trans('QuestionWriteAccountancy'), 'confirmWriteAccountancy', '', 0, 1);
	}

	// write accaountancy
	if (isset($action) && $action === 'confirmWriteAccountancy') {
		$result = $object->writeAccountancy($user);
		if ($result) {
			echo "<script>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
		} else {
			setEventMessages("Erreur lors de l'écriture comptable", [], 'errors');
		}
	}

	// Confirmation of action xxxx
	if ($action == 'scrapping') {
		$formquestion = array();

		$forcecombo = 0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			'text' => $langs->trans("putSrapping"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);

		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('scrapping'), $text, 'confirm_scrapping', $formquestion, 0, 1, 220);
	}


	if ($object->status === $object::STATUS_USED) {

		$periode = $object->fetchLabelParam(1, 'IMMOBILISATIONS_PERIODE');


		$initial_date = $object->date_consommation;
		$day_date = date('d/m/Y');
		$periodicite = $additional->periode;


		// $new_periode = $object->convertDateConsumption($periode->value, $periodicite);


		// var_dump($new_periode);
		// var_dump(dol_print_date($initial_date, 'day'), $day_date, $periodicite, $periode, $new_periode, $type_periode);
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
	$linkback = '<a href="' . dol_buildpath('/immobilisations/views/immobilisations_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';

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
	include DOL_DOCUMENT_ROOT . '/custom/immobilisations/tpl/commonfields_view.tpl.php';


	print '<table border-collapse class="noborder paymenttable">';
	print '<tr class="liste_titre">';
	print '<td colspan="10" class="left" style="width:100%;font-size:18px;font-weight:500px;color:#000">' . $langs->trans("immo_information") . '</td>';
	print '</tr>';

	print '<tr border=1>';
	print '<td colspan="2" class="left">' . $langs->trans("cc_immobilisation") . '</td>';
	print '<td colspan="2" class="left">' . $langs->trans("cc_compte_amortissement") . '</td>';
	print '<td colspan="2" class="left">' . $langs->trans("cc_compte_charges_amortissement") . '</td>';
	print '</tr>';

	print '<tr>';
	print '<td colspan="2" class="left">' . $additional->accountancy_code_asset . '</td>';
	print '<td colspan="2" class="left">' . $additional->accountancy_code_depreciation_asset . '</td>';
	print '<td colspan="2" class="left">' . $additional->accountancy_code_depreciation_expense . '</td>';
	print '</tr>';

	print "</table>";

	print '<table summary="boxtable11" width="100%" class="noborder boxtable">';
	print '<tr class="liste_titre box_titre">';
	print '<p style="text-align:left;font-size:18px;font-weight:300;margin-top:3%">Description</p>';
	print '<p style="text-align:left;font-size:12px;margin-top:1%">' . $object->description . ' </p>';
	print '</tr>';
	print '</table>';


	if (($object->status == $object::STATUS_USED || $object->status == $object::STATUS_TERMINATE) && $permissiontoread) {

		// calcul du pourcentage de consommation
		$pourcentage_consumption = $object->getPourcentageConsumption();
		$pourcentage_accountacy = $object->pourcentage_account;


		print '<table summary="boxtable11" width="100%" class="noborder boxtable">';
		print '<tr class="liste_titre box_titre">';
		print '<td colspan="1">';
		print '<div class="tdoverflowmax400 maxwidth250onsmartphone float">Informations supplémentaires</div>';
		print '<input type="hidden" id="boxlabelentry11" value="Clients dont l\'en-cours autoris&eacute; est d&eacute;pass&eacute;">';
		print '</div>';
		print '</td>';
		print '</tr>';

		print '	<tr>
					<td colspan="10">Amortissement</td>
				</tr>
				<tr>
					<td colspan="10">
						<div class="progress" style="margin-top:10px;border-radius:10px">
							<div class="progress-bar" role="progressbar" style="width: ' . $pourcentage_consumption . '%;border-radius:10px" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">' . $pourcentage_consumption . '% ' . $langs->trans('consumption') . '</div>
						</div>
					</td>
				</tr>';

		print '	<tr>
					<td colspan="10">Ecriture comptable</td>
				</tr>
				<tr>
					<td colspan="10">
						<div class="progress" style="margin-top:10px;border-radius:10px">
							<div class="progress-bar" role="progressbar" style="width: ' . $pourcentage_accountacy . '%;border-radius:10px" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">' . $pourcentage_accountacy . '%</div>
						</div>
					</td>
				</tr>';

		print '</table>';
	}


	// Tranfert de l'immobilisation
	if (isset($action) && ($action === 'transferImmo')) {

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

		include DOL_DOCUMENT_ROOT . '/custom/immobilisations/tpl/commonfields_edit.tpl.php';

		// Other attributes
		include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

		print '</table>';

		print dol_get_fiche_end();

		print $form->buttonsSaveCancel();

		print '</form>';
	}

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';
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

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {

			if ($permissiontoadd || ($permissiontoadd && $permissiontoputinapproval)) {

				// DRAFT
				if ($object->status == $object::STATUS_DRAFT) {
					if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {

						if ($object->approvment == $user->id) {
							print dolGetButtonAction($langs->trans('TransferInApproval'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=TransferInApproval&confirm=yes&token=' . newToken(), '', $permissiontoputinapproval);
						}

						// Modify
						print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=edit&token=' . newToken(), '', $permissiontoadd);

						// Clone
						print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . (!empty($object->socid) ? '&socid=' . $object->socid : '') . '&action=clone&token=' . newToken(), '', $permissiontoadd);

						// Delete (need delete permission, or if draft, just need create/modify permission)
						print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=delete&token=' . newToken(), '', $permissiontodelete);
					}
				}

				// IN_APPROVAL
				if ($permissiontoadd) {
					if ($object->status == $object::STATUS_IN_APPROVAL && isset($action)) {

						if ($action != 'transferImmo') {

							if ($object->fk_user_trans == $user->id) {
								print dolGetButtonAction($langs->trans('Transfer/Attribute'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '	&action=transferImmo&token=' . newToken(), '', $permissiontovalidate);
							}

							// print '<a class="butAction relative_div_" href="" title="'.$langs->trans('Transfer').'">'.$langs->trans('Transfer').'</a>';
							// if($object->fk_user_trans == $id){
							print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=confirm_validate&confirm=yes&token=' . newToken(), '', $permissiontovalidate);

							print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=close&token=' . newToken(), '', $permissiontoadd);
							print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontoadd);
						}
					}
				}

				// VALIDATED
				if ($permissiontoadd) {
					if ($object->status == $object::STATUS_VALIDATED) {

						if ($permissiontoconsumption && $object->fk_user_trans == (int) $user->id) {
							print '<a class="butAction relative_div_" href="' . dol_buildpath('/immobilisations/views/immobilisations_card.php', 1) . '?id=' . $object->id . '&amp;action=consumption&amp;token=' . newToken() . '" title="' . $langs->trans('initConsumption') . '">' . $langs->trans('initConsumption') . '</a>';
						} else {
							print '<a class="butActionRefused relative_div_" href="#" title="' . $langs->trans('initConsumption') . '">' . $langs->trans('initConsumption') . '</a>';
						}

						print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=close&token=' . newToken(), '', $permissiontoadd);
						print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=confirm_setdraft&confirm=yes&token=' . newToken(), '', $permissiontoadd);
					}
				}

				// USED
				if ($permissiontoadd) {

					if ($object->status == $object::STATUS_USED) {
						if ($permissiontoscrapp) {
							print '<a class=" butAction relative_div_" href="' . dol_buildpath('/immobilisations/views/immobilisations_card.php', 1) . '?id=' . $object->id . '&action=scrapping" title="' . $langs->trans('scrapping') . '">' . $langs->trans('scrapping') . '</a>';
						}
					}
				}

				// TERMINATE
				if ($permissiontoadd) {
					if ($object->status == $object::STATUS_TERMINATE) {
						print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=close&token=' . newToken(), '', $permissiontoadd);
					}
				}

				// WRITE ACCOUNTACY
				print dolGetButtonAction($langs->trans('WriteAccountancy'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=writeAccountancy&token=' . newToken(), '', $permissiontoaccount && $conf->accounting->enabled && ($object->status == $object::STATUS_USED || $object->status == $object::STATUS_TERMINATE));
			}
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
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref . '/' . $objref . '.pdf';
			$filedir = $conf->immobilisations->dir_output . '/' . $object->element . '/' . $objref;
			$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('immobilisations:Immobilisations', $object->element . '/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('immobilisations'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright">';

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-list-alt imgforviewmode', dol_buildpath('/immobilisations/views/immobilisations_agenda.php', 1) . '?id=' . $object->id);

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element . '@' . $object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		print '</div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'immobilisations';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->immobilisations->dir_output;
	$trackid = 'immobilisations' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}


// End of page
llxFooter();
$db->close();

?>

<script>
	$(document).ready(function() {
		$('#fk_fournisseur').change(function() {
			var fournisseur = $('#fk_fournisseur').val();

			$.ajax({
				type: 'POST',
				url: '../ajaxsource.php',
				data: {
					fk_commande: fournisseur,
					action: "get_product_fournisseur"
				},
				success: function(data) {
					$('#fk_product').html(data);
				}
			});
		});
	})
</script>
