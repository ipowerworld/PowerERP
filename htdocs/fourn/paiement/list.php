<?php
/* Copyright (C) 2003-2005	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004		Eric Seigne				<eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2020	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2004		Christophe Combelles	<ccomb@free.fr>
 * Copyright (C) 2005		Marc Barilley / Ocebo	<marc@ocebo.com>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2014		Teddy Andreotti			<125155@supinfo.com>
 * Copyright (C) 2015		Marcos García			<marcosgdf@gmail.com>
 * Copyright (C) 2015		Juanjo Menent			<jmenent@2byte.es>
 * Copyright (C) 2017-2021  Alexandre Spangaro		<aspangaro@open-dsi.fr>
 * Copyright (C) 2018-2021	Frédéric France			<frederic.france@netlogic.fr>
 * Copyright (C) 2020		Tobias Sekan			<tobias.sekan@startmail.com>
 * Copyright (C) 2021		Ferran Marcet			<fmarcet@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file		htdocs/fourn/paiement/list.php
*	\ingroup	fournisseur,facture
 *	\brief		Payment list for supplier invoices
 */

// Load PowerERP environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/paiementfourn.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/accountancy/class/accountingjournal.class.php';

// Load translation files required by the page
$langs->loadLangs(array('companies', 'bills', 'banks', 'compta'));

$action = GETPOST('action', 'alpha');
$massaction = GETPOST('massaction', 'alpha');
$optioncss = GETPOST('optioncss', 'alpha');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'vendorpaymentlist';

$socid = GETPOST('socid', 'int');

// Security check
if ($user->socid) $socid = $user->socid;

$search_ref				= GETPOST('search_ref', 'alpha');
$search_date_startday	= GETPOST('search_date_startday', 'int');
$search_date_startmonth	= GETPOST('search_date_startmonth', 'int');
$search_date_startyear	= GETPOST('search_date_startyear', 'int');
$search_date_endday		= GETPOST('search_date_endday', 'int');
$search_date_endmonth	= GETPOST('search_date_endmonth', 'int');
$search_date_endyear	= GETPOST('search_date_endyear', 'int');
$search_date_start		= dol_mktime(0, 0, 0, $search_date_startmonth, $search_date_startday, $search_date_startyear);	// Use tzserver
$search_date_end		= dol_mktime(23, 59, 59, $search_date_endmonth, $search_date_endday, $search_date_endyear);
$search_company			= GETPOST('search_company', 'alpha');
$search_payment_type	= GETPOST('search_payment_type');
$search_cheque_num		= GETPOST('search_cheque_num', 'alpha');
$search_bank_account	= GETPOST('search_bank_account', 'int');
$search_amount			= GETPOST('search_amount', 'alpha'); // alpha because we must be able to search on '< x'

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield				= GETPOST('sortfield', 'aZ09comma');
$sortorder				= GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST('page', 'int');

if (empty($page) || $page == -1) {
	$page = 0; // If $page is not defined, or '' or -1
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (!$sortorder) {
	$sortorder = "DESC";
}
if (!$sortfield) {
	$sortfield = "p.datep";
}

$search_all = trim(GETPOSTISSET("search_all") ? GETPOST("search_all", 'alpha') : GETPOST('sall'));

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	'p.ref'=>"RefPayment",
	's.nom'=>"ThirdParty",
	'p.num_paiement'=>"Numero",
	'p.amount'=>"Amount",
);

$arrayfields = array(
	'p.ref'				=>array('label'=>"RefPayment", 'checked'=>1, 'position'=>10),
	'p.datep'			=>array('label'=>"Date", 'checked'=>1, 'position'=>20),
	's.nom'				=>array('label'=>"ThirdParty", 'checked'=>1, 'position'=>30),
	'c.libelle'			=>array('label'=>"Type", 'checked'=>1, 'position'=>40),
	'p.num_paiement'	=>array('label'=>"Numero", 'checked'=>1, 'position'=>50, 'tooltip'=>"ChequeOrTransferNumber"),
	'ba.label'			=>array('label'=>"Account", 'checked'=>1, 'position'=>60, 'enable'=>(isModEnabled("banque"))),
	'p.amount'			=>array('label'=>"Amount", 'checked'=>1, 'position'=>70),
);
$arrayfields = dol_sort_array($arrayfields, 'position');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('paymentsupplierlist'));
$object = new PaiementFourn($db);

// Security check
if ($user->socid) {
	$socid = $user->socid;
}

// doesn't work :-(
// restrictedArea($user, 'fournisseur');
// doesn't work :-(
// require_once DOL_DOCUMENT_ROOT.'/fourn/class/paiementfourn.class.php';
// $object = new PaiementFourn($db);
// restrictedArea($user, $object->element);
if ((!isModEnabled('fournisseur') && empty($conf->global->MAIN_USE_NEW_SUPPLIERMOD))
	|| (!isModEnabled('supplier_invoice') && !empty($conf->global->MAIN_USE_NEW_SUPPLIERMOD))) {
	accessforbidden();
}
if ((empty($user->rights->fournisseur->facture->lire) && empty($conf->global->MAIN_USE_NEW_SUPPLIERMOD))
	|| (empty($user->rights->supplier_invoice->lire) && !empty($conf->global->MAIN_USE_NEW_SUPPLIERMOD))) {
	accessforbidden();
}


/*
 * Actions
 */

$parameters = array('socid'=>$socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) {	// All tests are required to be compatible with all browsers
		$search_ref = '';
		$search_date_startday = '';
		$search_date_startmonth = '';
		$search_date_startyear = '';
		$search_date_endday = '';
		$search_date_endmonth = '';
		$search_date_endyear = '';
		$search_date_start = '';
		$search_date_end = '';
		$search_company = '';
		$search_payment_type = '';
		$search_cheque_num = '';
		$search_bank_account = '';
		$search_amount = '';
	}
}

/*
 * View
 */

llxHeader('', $langs->trans('ListPayment'));

$form = new Form($db);
$formother = new FormOther($db);
$accountstatic = new Account($db);
$companystatic = new Societe($db);
$paymentfournstatic = new PaiementFourn($db);

$sql = 'SELECT p.rowid, p.ref, p.datep, p.amount as pamount, p.num_paiement';
$sql .= ', s.rowid as socid, s.nom as name, s.email';
$sql .= ', c.code as paiement_type, c.libelle as paiement_libelle';
$sql .= ', ba.rowid as bid, ba.ref as bref, ba.label as blabel, ba.number, ba.account_number as account_number, ba.iban_prefix, ba.bic, ba.currency_code, ba.fk_accountancy_journal as accountancy_journal';
if (empty($user->rights->societe->client->voir)) {
	$sql .= ', sc.fk_soc, sc.fk_user';
}
$sql .= ', SUM(pf.amount)';

$sql .= ' FROM '.MAIN_DB_PREFIX.'paiementfourn AS p';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'paiementfourn_facturefourn AS pf ON p.rowid=pf.fk_paiementfourn';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'facture_fourn AS f ON f.rowid=pf.fk_facturefourn';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement AS c ON p.fk_paiement = c.id';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe AS s ON s.rowid = f.fk_soc';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'bank as b ON p.fk_bank = b.rowid';
$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'bank_account as ba ON b.fk_account = ba.rowid';
if (empty($user->rights->societe->client->voir)) {
	$sql .= ', '.MAIN_DB_PREFIX.'societe_commerciaux as sc';
}

$sql .= ' WHERE f.entity = '.$conf->entity;
if (empty($user->rights->societe->client->voir)) {
	$sql .= ' AND s.rowid = sc.fk_soc AND sc.fk_user = '.((int) $user->id);
}
if ($socid > 0) {
	$sql .= ' AND f.fk_soc = '.((int) $socid);
}
if ($search_ref) {
	$sql .= natural_search('p.ref', $search_ref);
}
if ($search_date_start) {
	$sql .= " AND p.datep >= '" . $db->idate($search_date_start) . "'";
}
if ($search_date_end) {
	$sql .=" AND p.datep <= '" . $db->idate($search_date_end) . "'";
}

if ($search_company) {
	$sql .= natural_search('s.nom', $search_company);
}
if ($search_payment_type != '') {
	$sql .= " AND c.code='".$db->escape($search_payment_type)."'";
}
if ($search_cheque_num != '') {
	$sql .= natural_search('p.num_paiement', $search_cheque_num);
}
if ($search_amount) {
	$sql .= natural_search('p.amount', $search_amount, 1);
}
if ($search_bank_account > 0) {
	$sql .= ' AND b.fk_account = '.((int) $search_bank_account);
}
if ($search_all) {
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
}

// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';

$sql .= ' GROUP BY p.rowid, p.ref, p.datep, p.amount, p.num_paiement, s.rowid, s.nom, s.email, c.code, c.libelle,';
$sql .= ' ba.rowid, ba.ref, ba.label, ba.number, ba.account_number, ba.iban_prefix, ba.bic, ba.currency_code, ba.fk_accountancy_journal';
if (empty($user->rights->societe->client->voir)) {
	$sql .= ', sc.fk_soc, sc.fk_user';
}

$sql .= $db->order($sortfield, $sortorder);

$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
	if (($page * $limit) > $nbtotalofrecords) {		// if total resultset is smaller then paging size (filtering), goto and load page 0
		$page = 0;
		$offset = 0;
	}
}

$sql .= $db->plimit($limit + 1, $offset);

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	llxFooter();
	$db->close();
	exit;
}

$num = $db->num_rows($resql);
$i = 0;

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER['PHP_SELF']) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.urlencode($limit);
}
if ($optioncss != '') {
	$param .= '&optioncss='.urlencode($optioncss);
}

if ($search_ref) {
	$param .= '&search_ref='.urlencode($search_ref);
}
if ($search_date_startday) {
	$param .= '&search_date_startday='.urlencode($search_date_startday);
}
if ($search_date_startmonth) {
	$param .= '&search_date_startmonth='.urlencode($search_date_startmonth);
}
if ($search_date_startyear) {
	$param .= '&search_date_startyear='.urlencode($search_date_startyear);
}
if ($search_date_endday) {
	$param .= '&search_date_endday='.urlencode($search_date_endday);
}
if ($search_date_endmonth) {
	$param .= '&search_date_endmonth='.urlencode($search_date_endmonth);
}
if ($search_date_endyear) {
	$param .= '&search_date_endyear='.urlencode($search_date_endyear);
}
if ($search_company) {
	$param .= '&search_company='.urlencode($search_company);
}
if ($search_payment_type) {
	$param .= '&search_company='.urlencode($search_payment_type);
}
if ($search_cheque_num) {
	$param .= '&search_cheque_num='.urlencode($search_cheque_num);
}
if ($search_amount) {
	$param .= '&search_amount='.urlencode($search_amount);
}

// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
if ($optioncss != '') {
	print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
}
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';

print_barre_liste($langs->trans('SupplierPayments'), $page, $_SERVER['PHP_SELF'], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'supplier_invoice', 0, '', '', $limit, 0, 0, 1);

if ($search_all) {
	foreach ($fieldstosearchall as $key => $val) {
		$fieldstosearchall[$key] = $langs->trans($val);
	}
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>';
}

$moreforfilter = '';

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters); // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
	$moreforfilter .= $hookmanager->resPrint;
} else {
	$moreforfilter = $hookmanager->resPrint;
}

if ($moreforfilter) {
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER['PHP_SELF'] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields
if (!empty($massactionbutton)) {
	$selectedfields .= $form->showCheckAddButtons('checkforselect', 1);
}

print '<div class="div-table-responsive">';
print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : '').'">';

print '<tr class="liste_titre_filter">';

// Filter: Ref
if (!empty($arrayfields['p.ref']['checked'])) {
	print '<td  class="liste_titre left">';
	print '<input class="flat" type="text" size="4" name="search_ref" value="'.dol_escape_htmltag($search_ref).'">';
	print '</td>';
}

// Filter: Date
if (!empty($arrayfields['p.datep']['checked'])) {
	print '<td class="liste_titre center">';
	print '<div class="nowrap">';
	print $form->selectDate($search_date_start ? $search_date_start : -1, 'search_date_start', 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
	print '</div>';
	print '<div class="nowrap">';
	print $form->selectDate($search_date_end ? $search_date_end : -1, 'search_date_end', 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
	print '</div>';
	print '</td>';
}

// Filter: Thirdparty
if (!empty($arrayfields['s.nom']['checked'])) {
	print '<td class="liste_titre">';
	print '<input class="flat" type="text" size="6" name="search_company" value="'.dol_escape_htmltag($search_company).'">';
	print '</td>';
}

// Filter: Payment type
if (!empty($arrayfields['c.libelle']['checked'])) {
	print '<td class="liste_titre">';
	$form->select_types_paiements($search_payment_type, 'search_payment_type', '', 2, 1, 1);
	print '</td>';
}

// Filter: Cheque number (fund transfer)
if (!empty($arrayfields['p.num_paiement']['checked'])) {
	print '<td class="liste_titre">';
	print '<input class="flat" type="text" size="4" name="search_cheque_num" value="'.dol_escape_htmltag($search_cheque_num).'">';
	print '</td>';
}

// Filter: Bank account
if (!empty($arrayfields['ba.label']['checked'])) {
	print '<td class="liste_titre">';
	$form->select_comptes($search_bank_account, 'search_bank_account', 0, '', 1);
	print '</td>';
}

// Filter: Amount
if (!empty($arrayfields['p.amount']['checked'])) {
	print '<td class="liste_titre right">';
	print '<input class="flat" type="text" size="4" name="search_amount" value="'.dol_escape_htmltag($search_amount).'">';
	print '</td>';
}

// Fields from hook
$parameters = array('arrayfields'=>$arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

// Buttons
print '<td class="liste_titre maxwidthsearch">';
print $form->showFilterAndCheckAddButtons(0);
print '</td>';

print '</tr>';

print '<tr class="liste_titre">';
if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER_IN_LIST)) {
	print_liste_field_titre('#', $_SERVER['PHP_SELF'], '', '', $param, '', $sortfield, $sortorder);
}
if (!empty($arrayfields['p.ref']['checked'])) {
	print_liste_field_titre($arrayfields['p.ref']['label'], $_SERVER["PHP_SELF"], 'p.rowid', '', $param, '', $sortfield, $sortorder);
}
if (!empty($arrayfields['p.datep']['checked'])) {
	print_liste_field_titre($arrayfields['p.datep']['label'], $_SERVER["PHP_SELF"], 'p.datep', '', $param, '', $sortfield, $sortorder, 'center ');
}
if (!empty($arrayfields['s.nom']['checked'])) {
	print_liste_field_titre($arrayfields['s.nom']['label'], $_SERVER["PHP_SELF"], 's.nom', '', $param, '', $sortfield, $sortorder);
}
if (!empty($arrayfields['c.libelle']['checked'])) {
	print_liste_field_titre($arrayfields['c.libelle']['label'], $_SERVER["PHP_SELF"], 'c.libelle', '', $param, '', $sortfield, $sortorder);
}
if (!empty($arrayfields['p.num_paiement']['checked'])) {
	print_liste_field_titre($arrayfields['p.num_paiement']['label'], $_SERVER["PHP_SELF"], "p.num_paiement", '', $param, '', $sortfield, $sortorder, '', $arrayfields['p.num_paiement']['tooltip']);
}
if (!empty($arrayfields['ba.label']['checked'])) {
	print_liste_field_titre($arrayfields['ba.label']['label'], $_SERVER["PHP_SELF"], 'ba.label', '', $param, '', $sortfield, $sortorder);
}
if (!empty($arrayfields['p.amount']['checked'])) {
	print_liste_field_titre($arrayfields['p.amount']['label'], $_SERVER["PHP_SELF"], 'p.amount', '', $param, '', $sortfield, $sortorder, 'right ');
}

// Hook fields
$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print_liste_field_titre($selectedfields, $_SERVER['PHP_SELF'], '', '', '', 'align="center"', $sortfield, $sortorder, 'maxwidthsearch ');
print '</tr>';

$checkedCount = 0;
foreach ($arrayfields as $column) {
	if ($column['checked']) {
		$checkedCount++;
	}
}

$i = 0;
$totalarray = array();
while ($i < min($num, $limit)) {
	$objp = $db->fetch_object($resql);

	$paymentfournstatic->id = $objp->rowid;
	$paymentfournstatic->ref = $objp->ref;
	$paymentfournstatic->datepaye = $objp->datep;

	$companystatic->id = $objp->socid;
	$companystatic->name = $objp->name;
	$companystatic->email = $objp->email;

	print '<tr class="oddeven">';

	// No
	if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER_IN_LIST)) {
		print '<td class="nowraponall">'.(($offset * $limit) + $i).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Ref
	if (!empty($arrayfields['p.ref']['checked'])) {
		print '<td class="nowraponall">'.$paymentfournstatic->getNomUrl(1).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Date
	if (!empty($arrayfields['p.datep']['checked'])) {
		$dateformatforpayment = 'dayhour';
		print '<td class="nowrap center">'.dol_print_date($db->jdate($objp->datep), $dateformatforpayment).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Thirdparty
	if (!empty($arrayfields['s.nom']['checked'])) {
		print '<td class="tdoverflowmax125">';
		if ($objp->socid > 0) {
			print $companystatic->getNomUrl(1, '', 24);
		}
		print '</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Pyament type
	if (!empty($arrayfields['c.libelle']['checked'])) {
		$payment_type = $langs->trans("PaymentType".$objp->paiement_type) != ("PaymentType".$objp->paiement_type) ? $langs->trans("PaymentType".$objp->paiement_type) : $objp->paiement_libelle;
		print '<td>'.$payment_type.' '.dol_trunc($objp->num_paiement, 32).'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Cheque number (fund transfer)
	if (!empty($arrayfields['p.num_paiement']['checked'])) {
		print '<td>'.$objp->num_paiement.'</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Bank account
	if (!empty($arrayfields['ba.label']['checked'])) {
		print '<td class="tdoverflowmax125">';
		if ($objp->bid) {
			$accountstatic->id = $objp->bid;
			$accountstatic->ref = $objp->bref;
			$accountstatic->label = $objp->blabel;
			$accountstatic->number = $objp->number;
			$accountstatic->iban = $objp->iban_prefix;
			$accountstatic->bic = $objp->bic;
			$accountstatic->currency_code = $objp->currency_code;
			$accountstatic->account_number = $objp->account_number;

			$accountingjournal = new AccountingJournal($db);
			$accountingjournal->fetch($objp->accountancy_journal);
			$accountstatic->accountancy_journal = $accountingjournal->code;

			print $accountstatic->getNomUrl(1);
		} else {
			print '&nbsp;';
		}
		print '</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}

	// Amount
	if (!empty($arrayfields['p.amount']['checked'])) {
		print '<td class="right"><span class="amount">'.price($objp->pamount).'</span></td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
		$totalarray['pos'][$checkedCount] = 'amount';
		$totalarray['val']['amount'] += $objp->pamount;
	}

	// Buttons
	print '<td></td>';
	if (!$i) {
		$totalarray['nbfield']++;
	}

	print '</tr>';
	$i++;
}

// Show total line
include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';

print '</table>';
print '</div>';
print '</form>';

// End of page
llxFooter();
$db->close();
