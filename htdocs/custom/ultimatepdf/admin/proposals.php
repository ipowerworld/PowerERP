<?php
/* Copyright (C) 2011 Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2019	Philippe Grand	<philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       ultimatepdf/admin/proposals.php
 *  \ingroup    ultimatepdf
 *  \brief      Page d'administration/configuration du module ultimatepdf
 */

// Load PowerERP environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php")) $res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

global $db, $langs, $user, $conf;

// Libraries
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once("../lib/ultimatepdf.lib.php");

// Translations
$langs->loadLangs(array("admin", "propal", "ultimatepdf@ultimatepdf"));

// Security check
if (! $user->rights->ultimatepdf->config) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Action
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (powererp_set_const($db, $code, 1, 'chaine', 0, '', $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if (preg_match('/del_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (powererp_del_const($db, $code, $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

/*
 * View
 */

$form = new Form($db);
$wikihelp='EN:Module_Ultimatepdf_EN#Commercial_proposals_tab|FR:Module_Ultimatepdf_FR#Onglet_Propositions_commerciales';
$page_name = "UltimatepdfSetup";
llxHeader('', $langs->trans($page_name), $wikihelp);

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'ultimatepdf@ultimatepdf');

// Configuration header
$head = ultimatepdf_prepare_head();
dol_fiche_head($head, 'proposals', $langs->trans("ModuleSetup"), 0, "ultimatepdf@ultimatepdf");

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetUpHeader").'</em></b>';
print '</div>';

// Addresses area
load_fiche_titre($langs->trans("PDFAddressForging"),'','').'<br>';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// add also details for contact address.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowAlsoTargetDetails").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS');
}
else
{
	if($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add also details for client address.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowAlsoClientDetails").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PDF_PROPAL_ADDALSOCLIENTDETAILS');
}
else
{
	if($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOCLIENTDETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PDF_PROPAL_ADDALSOCLIENTDETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOCLIENTDETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PDF_PROPAL_ADDALSOCLIENTDETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide details from source within address block.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("HideSourceDetails").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PDF_PROPAL_DISABLESOURCEDETAILS');
}
else
{
	if($conf->global->ULTIMATE_PDF_PROPAL_DISABLESOURCEDETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PDF_PROPAL_DISABLESOURCEDETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PDF_PROPAL_DISABLESOURCEDETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PDF_PROPAL_DISABLESOURCEDETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// hide TVA intra within address.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("HideTvaIntraWithinAddress").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_TVAINTRA_NOT_IN_PROPAL_ADDRESS');
}
else
{
	if($conf->global->ULTIMATE_TVAINTRA_NOT_IN_PROPAL_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_TVAINTRA_NOT_IN_PROPAL_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_TVAINTRA_NOT_IN_PROPAL_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_TVAINTRA_NOT_IN_PROPAL_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

/*
 * Formulaire parametres divers
 */

load_fiche_titre($langs->trans("UltimatepdfMiscellaneous"),'','').'<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// Add list of your banks within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL');
}
else
{
	if($conf->global->BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_BANK_ASK_PAYMENT_BANK_DURING_PROPOSAL">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Add Product barcode under product's description within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowProductsBarcodeInsidePropalUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_PROPOSALS_WITH_PRODUCTS_BARCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_PRODUCTS_BARCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_PRODUCTS_BARCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_PRODUCTS_BARCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_PRODUCTS_BARCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add barcode at top within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultBarcodeAtTopInsidePropalUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_BARCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_BARCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_BARCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_BARCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_BARCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add QRcode at top within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultQRcodeAtTopInsidePropalUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_QRCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_QRCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_QRCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_QRCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_TOP_QRCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add my comp QRcode at top within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultMycompQRcodeAtTopInsidePropalUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MYCOMP_QRCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MYCOMP_QRCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MYCOMP_QRCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MYCOMP_QRCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MYCOMP_QRCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display project reference.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("DisplayRefProjectInsideProposalsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PROPOSALS_PDF_SHOW_PROJECT');
}
else
{
	if($conf->global->ULTIMATE_PROPOSALS_PDF_SHOW_PROJECT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PROPOSALS_PDF_SHOW_PROJECT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PROPOSALS_PDF_SHOW_PROJECT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PROPOSALS_PDF_SHOW_PROJECT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// do not repeat header.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("DoNotRepeatHeadInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PROPOSALS_PDF_DONOTREPEAT_HEAD');
}
else
{
	if($conf->global->ULTIMATE_PROPOSALS_PDF_DONOTREPEAT_HEAD == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PROPOSALS_PDF_DONOTREPEAT_HEAD">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PROPOSALS_PDF_DONOTREPEAT_HEAD == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PROPOSALS_PDF_DONOTREPEAT_HEAD">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetCoreBloc").'</em></b>';
print '</div>';
print '</td></tr>';

load_fiche_titre($langs->trans("UltimatepdfSpecificProposals"),'','').'<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// Add line between products lines
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultDashBetweenLinesInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PROPAL_PDF_DASH_BETWEEN_LINES');
}
else
{
	if($conf->global->ULTIMATE_PROPAL_PDF_DASH_BETWEEN_LINES == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PROPAL_PDF_DASH_BETWEEN_LINES">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PROPAL_PDF_DASH_BETWEEN_LINES == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PROPAL_PDF_DASH_BETWEEN_LINES">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display fold mark.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultFoldMarkInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_DISPLAY_PROPOSALS_FOLD_MARK');
}
else
{
	if($conf->global->MAIN_DISPLAY_PROPOSALS_FOLD_MARK == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_DISPLAY_PROPOSALS_FOLD_MARK">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_DISPLAY_PROPOSALS_FOLD_MARK == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_DISPLAY_PROPOSALS_FOLD_MARK">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display sale representative signature within proposal note .
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultSaleRepSignatureInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_ADD_SALE_REP_SIGNATURE_IN_PROPAL_NOTE');
}
else
{
	if($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_PROPAL_NOTE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_ADD_SALE_REP_SIGNATURE_IN_PROPAL_NOTE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_PROPAL_NOTE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_ADD_SALE_REP_SIGNATURE_IN_PROPAL_NOTE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display Case followed by sale representative within proposal note .
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultCaseFollowedByInsideProposal").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_ADD_CREATOR_IN_NOTE');
}
else
{
	if($conf->global->MAIN_ADD_CREATOR_IN_NOTE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_ADD_CREATOR_IN_NOTE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_ADD_CREATOR_IN_NOTE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_ADD_CREATOR_IN_NOTE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display column line number
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultColumnLineNumberInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_PROPOSALS_WITH_LINE_NUMBER');
}
else
{
	if($conf->global->ULTIMATE_PROPOSALS_WITH_LINE_NUMBER == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PROPOSALS_WITH_LINE_NUMBER">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PROPOSALS_WITH_LINE_NUMBER == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PROPOSALS_WITH_LINE_NUMBER">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add photos within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultPhotosInsidePropalUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display subprice column 
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateProposalsWithPriceUht").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_PROPOSALS_WITH_PRICEUHT');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PRICEUHT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_PROPOSALS_WITH_PRICEUHT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PRICEUHT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_PROPOSALS_WITH_PRICEUHT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display discount column 
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateProposalsWithDiscount").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_PROPOSALS_WITH_DISCOUNT');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_DISCOUNT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_PROPOSALS_WITH_DISCOUNT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_DISCOUNT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_PROPOSALS_WITH_DISCOUNT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display PuAfter column 
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateProposalsWithPuAfter").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_PROPOSALS_WITH_PUAFTER');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PUAFTER == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_PROPOSALS_WITH_PUAFTER">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PUAFTER == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_PROPOSALS_WITH_PUAFTER">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display Qty column 
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateProposalsWithQty").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_PROPOSALS_WITH_QTY');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_QTY == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_PROPOSALS_WITH_QTY">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_QTY == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_PROPOSALS_WITH_QTY">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display weight column 
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateProposalsWithWeightColumn").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_PROPOSALS_WITH_WEIGHT_COLUMN');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_WEIGHT_COLUMN == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_PROPOSALS_WITH_WEIGHT_COLUMN">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_WEIGHT_COLUMN == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_PROPOSALS_WITH_WEIGHT_COLUMN">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetFooterBloc").'</em></b>';
print '</div>';
print '</td></tr>';

load_fiche_titre($langs->trans("UltimatepdfSpecificProposals"),'','').'<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// display agreement bloc
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultAgreementBlockInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_DISPLAY_PROPOSAL_AGREEMENT_BLOCK');
}
else
{
	if($conf->global->ULTIMATEPDF_DISPLAY_PROPOSAL_AGREEMENT_BLOCK == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_DISPLAY_PROPOSAL_AGREEMENT_BLOCK">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_DISPLAY_PROPOSAL_AGREEMENT_BLOCK == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_DISPLAY_PROPOSAL_AGREEMENT_BLOCK">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add Responsable signature within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("AddResponsableSignatureInsideProposalsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_ADD_RESPONSABLE_SIGNATURE_IN_PROPOSAL');
}
else
{
	if($conf->global->ULTIMATEPDF_ADD_RESPONSABLE_SIGNATURE_IN_PROPOSAL == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_ADD_RESPONSABLE_SIGNATURE_IN_PROPOSAL">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_ADD_RESPONSABLE_SIGNATURE_IN_PROPOSAL == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_ADD_RESPONSABLE_SIGNATURE_IN_PROPOSAL">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display electronic signature within agreement bloc
print '<tr class="oddeven"><td>';
$tmp=$langs->trans("ShowByDefaultElectronicSignatureWithinAgreementBlock");
$helpcontent=$langs->trans("HelpShowByDefaultElectronicSignatureWithinAgreementBlock");
print $form->textwithpicto($tmp, $helpcontent, 1, 'help');
print '</td><td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_DISPLAY_PROPOSAL_ELECTRONIC_SIGNATURE');
}
else
{
	if($conf->global->ULTIMATEPDF_DISPLAY_PROPOSAL_ELECTRONIC_SIGNATURE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_DISPLAY_PROPOSAL_ELECTRONIC_SIGNATURE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_DISPLAY_PROPOSAL_ELECTRONIC_SIGNATURE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_DISPLAY_PROPOSAL_ELECTRONIC_SIGNATURE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add "Auto-liquidation régime de la sous-traitance" within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultAutoLiquidationInsideProposalsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_PROPOSALS_WITH_AUTO_LIQUIDATION');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_AUTO_LIQUIDATION == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_AUTO_LIQUIDATION">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_AUTO_LIQUIDATION == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_AUTO_LIQUIDATION">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add merge of pdf files from products within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowMergedPdfFromProductsInsideProposalsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('PRODUIT_PDF_MERGE_PROPAL');
}
else
{
	if($conf->global->PRODUIT_PDF_MERGE_PROPAL == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_PRODUIT_PDF_MERGE_PROPAL">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->PRODUIT_PDF_MERGE_PROPAL == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_PRODUIT_PDF_MERGE_PROPAL">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add merge of pdf files within proposals.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultMergePdfInsideProposalsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

// Footer
llxFooter();
// Close database handler
$db->close();
?>
