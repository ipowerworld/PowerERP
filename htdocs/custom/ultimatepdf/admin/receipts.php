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
 *  \file       ultimatepdf/admin/receipts.php
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
require_once("../lib/ultimatepdf.lib.php");

// Translations
$langs->loadLangs(array("admin", "sendings", "deliveries", "ultimatepdf@ultimatepdf"));

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

$wikihelp='EN:Module_Ultimatepdf_EN#Receipts_tab|FR:Module_Ultimatepdf_FR#Onglet_Exp.C3.A9ditions';
$page_name = "UltimatepdfSetup";
llxHeader('',$langs->trans($page_name), $wikihelp);

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'ultimatepdf@ultimatepdf');

// Configuration header
$head = ultimatepdf_prepare_head();
dol_fiche_head($head, 'receipts', $langs->trans("ModuleSetup"), 0, "ultimatepdf@ultimatepdf");

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetUpHeader").'</em></b>';
print '</div>';

// Addresses
print_fiche_titre($langs->trans("PDFAddressForging"),'','').'<br>';

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
	print ajax_constantonoff('ULTIMATE_PDF_DELIVERY_ADDALSOTARGETDETAILS');
}
else
{
	if($conf->global->ULTIMATE_PDF_DELIVERY_ADDALSOTARGETDETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PDF_DELIVERY_ADDALSOTARGETDETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PDF_DELIVERY_ADDALSOTARGETDETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PDF_DELIVERY_ADDALSOTARGETDETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
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
	print ajax_constantonoff('ULTIMATE_PDF_DELIVERY_ADDALSOCLIENTDETAILS');
}
else
{
	if($conf->global->ULTIMATE_PDF_DELIVERY_ADDALSOCLIENTDETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_PDF_DELIVERY_ADDALSOCLIENTDETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_PDF_DELIVERY_ADDALSOCLIENTDETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_PDF_DELIVERY_ADDALSOCLIENTDETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

/*
 * Formulaire parametres divers
 */
print_fiche_titre($langs->trans("UltimatepdfMiscellaneous"),'','').'<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// Add Product barcode under product's description within deliveries.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowProductsBarcodeInsideDeliveryUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_DELIVERY_WITH_PRODUCTS_BARCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_DELIVERY_WITH_PRODUCTS_BARCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_DELIVERY_WITH_PRODUCTS_BARCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_DELIVERY_WITH_PRODUCTS_BARCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_DELIVERY_WITH_PRODUCTS_BARCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add barcode at top within receipts.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultBarcodeAtTopInsideReceiptsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_BARCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_BARCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_BARCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_BARCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_BARCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add thirdparty QRcode at top within receipts.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultQRcodeAtTopInsideReceiptsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_QRCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_QRCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_QRCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_QRCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_RECEIPTS_WITH_TOP_QRCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add order link QRcode at top within receipts.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultOrderLinkQRcodeAtTopInsideReceiptsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_ORDERLINK_WITH_TOP_QRCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_ORDERLINK_WITH_TOP_QRCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_ORDERLINK_WITH_TOP_QRCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_ORDERLINK_WITH_TOP_QRCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_ORDERLINK_WITH_TOP_QRCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add my comp QRcode at top within receipts.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultMycompQRcodeAtTopInsideReceiptsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_RECEIPTS_WITH_MYCOMP_QRCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_RECEIPTS_WITH_MYCOMP_QRCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_RECEIPTS_WITH_MYCOMP_QRCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_RECEIPTS_WITH_MYCOMP_QRCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_RECEIPTS_WITH_MYCOMP_QRCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
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
	print ajax_constantonoff('ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD');
}
else
{
	if($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetCoreBloc").'</em></b>';
print '</div>';
print '</td></tr>';

/*
 * Formulaire parametres divers
 */

print_fiche_titre($langs->trans("UltimatepdfSpecificReceipts"),'','').'<br>';
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
	print ajax_constantonoff('ULTIMATE_DELIVERY_PDF_DASH_BETWEEN_LINES');
}
else
{
	if($conf->global->ULTIMATE_DELIVERY_PDF_DASH_BETWEEN_LINES == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_DELIVERY_PDF_DASH_BETWEEN_LINES">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_DELIVERY_PDF_DASH_BETWEEN_LINES == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_DELIVERY_PDF_DASH_BETWEEN_LINES">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display sale representative signature within receipts note .
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultSaleRepSignatureInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_ADD_SALE_REP_SIGNATURE_IN_RECEIPT_NOTE');
}
else
{
	if($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_RECEIPT_NOTE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_ADD_SALE_REP_SIGNATURE_IN_RECEIPT_NOTE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_RECEIPT_NOTE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_ADD_SALE_REP_SIGNATURE_IN_RECEIPT_NOTE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
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
	print ajax_constantonoff('ULTIMATE_RECEIPTS_WITH_LINE_NUMBER');
}
else
{
	if($conf->global->ULTIMATE_RECEIPTS_WITH_LINE_NUMBER == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_RECEIPTS_WITH_LINE_NUMBER">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_RECEIPTS_WITH_LINE_NUMBER == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_RECEIPTS_WITH_LINE_NUMBER">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// add photos within receipts.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowByDefaultPhotosInsideReceiptsUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display column line number
/*print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateReceiptsWithWeightVolume").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_RECEIPTS_WITH_WEIGHT_COLUMN');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_WEIGHT_COLUMN == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_RECEIPTS_WITH_WEIGHT_COLUMN">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_WEIGHT_COLUMN == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_RECEIPTS_WITH_WEIGHT_COLUMN">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';*/

// display column with qty_asked
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateShipmentsWithQtyAsked").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_RECEIPTS_WITH_QTYASKED');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_QTYASKED == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_RECEIPTS_WITH_QTYASKED">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_QTYASKED == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_RECEIPTS_WITH_QTYASKED">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display column with qty_shipped
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateReceiptsWithQtyShipped").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_RECEIPTS_WITH_QTYSHIPPED');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_QTYSHIPPED == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_RECEIPTS_WITH_QTYSHIPPED">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_QTYSHIPPED == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_RECEIPTS_WITH_QTYSHIPPED">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// display column with reliquat
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateGenerateReceiptsWithReliquat").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_GENERATE_RECEIPTS_WITH_RELIQUAT');
}
else
{
	if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_RELIQUAT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_GENERATE_RECEIPTS_WITH_RELIQUAT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_RELIQUAT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_GENERATE_RECEIPTS_WITH_RELIQUAT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetFooterBloc").'</em></b>';
print '</div>';
print '</td></tr>';

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
	print ajax_constantonoff('ULTIMATE_DISPLAY_RECEIPTS_AGREEMENT_BLOCK');
}
else
{
	if($conf->global->ULTIMATE_DISPLAY_RECEIPTS_AGREEMENT_BLOCK == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_DISPLAY_RECEIPTS_AGREEMENT_BLOCK">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_DISPLAY_RECEIPTS_AGREEMENT_BLOCK == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_DISPLAY_RECEIPTS_AGREEMENT_BLOCK">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

// Footer
llxFooter();
// Close database handler
$db->close();
?>
