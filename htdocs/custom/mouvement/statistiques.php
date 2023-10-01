<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       mouvement/mouvementindex.php
 *	\ingroup    mouvement
 *	\brief      Home page of mouvement top menu
 */

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

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("mouvement@mouvement"));

$action = GETPOST('action', 'aZ09');


// Security check
// if (! $user->rights->mouvement->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
    $action = '';
    $socid = $user->socid;
}

$max = 5;
$now = dol_now();

// **************************
include_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';

$product = [];
$mouvement = [];
$users = [];

if (isset($_POST['date_debut'])) {
    $date_debut = new DateTime($_POST['date_debut']);
    $date_fin = new DateTime($_POST['date_fin']);
    if ($date_debut > $date_fin) {
        setEventMessages("Veuillez entrer des dates valides", '', 'errors');
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "mouvement_mouvement_pour_attribution WHERE status = 1";
    } else {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "mouvement_mouvement_pour_attribution WHERE status = 1 AND date_creation BETWEEN '" . date_format($date_debut, 'Y-m-d') . "' AND '" . date_format($date_fin, 'Y-m-d') . "'";
    }
} else {
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "mouvement_mouvement_pour_attribution WHERE status = 1";
}

$resql = $db->query($sql);
while ($item = $db->fetch_object($resql)) {
    array_push($mouvement, $item);
}

$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "product";
$resql = $db->query($sql);
while ($item = $db->fetch_object($resql)) {
    array_push($product, $item);
}

$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "user";
$resql = $db->query($sql);
while ($item = $db->fetch_object($resql)) {
    array_push($users, $item);
}

// 1

$i = 0;
$total1 = 0;

if (count($mouvement) <= 0) {
    $produitAttribution = [];
    setEventMessages("Aucun mouvement effectué durant cette periode", '', 'mesgs');
} else {
    while ($i < count($product)) {
        $sql = "SELECT SUM(qty) AS qty FROM " . MAIN_DB_PREFIX . "mouvement_mouvements_line WHERE fk_product = " . $product[$i]->rowid . " AND (fk_mouvement = " . $mouvement[0]->rowid . "";
        $j = 1;
        while ($j < count($mouvement)) {
            $sql .= " OR fk_mouvement = " . $mouvement[$j]->rowid . "";
            $j++;
        }
        $sql .= ")";
        $resql = $db->query($sql);
        $item = $db->fetch_object($resql);
        $produitAttribution[] = array(($product[$i]->label), round($item->qty));
        $total1 += round($item->qty);
        $i++;
    }
}


// 2

$i = 0;
$total2 = 0;
if (count($mouvement) <= 0) {
    $userAttribution = [];
} else {
    while ($i < count($users)) {
        $sql = "SELECT SUM(qty) AS qty FROM " . MAIN_DB_PREFIX . "mouvement_mouvements_line WHERE attributed = " . $users[$i]->rowid . " AND (fk_mouvement = " . $mouvement[0]->rowid . "";
        $j = 1;
        while ($j < count($mouvement)) {
            $sql .= " OR fk_mouvement = " . $mouvement[$j]->rowid . "";
            $j++;
        }
        $sql .= ")";
        $resql = $db->query($sql);
        $item = $db->fetch_object($resql);
        $userAttribution[] = array(($users[$i]->lastname), round($item->qty));
        $total2 += round($item->qty);
        $i++;
    }
}

/*
 * Actions
 */



llxHeader("", 'Statistiques');


print ' <div class="fichecenter">
            <table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
                <tbody>
                    <tr class="titre">
                        <td class="nobordernopadding valignmiddle col-title">
                            <div class="titre inline-block"><img src="./img/object_statistique.png" class="paddingright classfortooltip smaller">STATISTIQUES</div>
                        </td>
                    </tr>
                </tbody>
                </table>
            <table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
            <form action="' . $_SERVER["PHP_SELF"] . '" method="POST">
                <tbody>
                    <tr class="liste_titre">
                        <td class="left">';
if (!isset($_POST['date_debut'])) {
    print '                     Date de debut &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_debut" type="date" required />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                Date de fin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_fin" type="date" required />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
} else {
    print '                     Date de debut &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_debut" type="date" required value="' . date_format($date_debut, 'Y-m-d') . '"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                Date de fin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="date_fin" type="date" required value="' . date_format($date_fin, 'Y-m-d') . '"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
}

print '                         <button class="butAction">VALIDER</button>
                        </td>
                    </tr>
                </tbody>
            </form>
        <table>';

////////////////////// PRODUITS LES PLUS ATTRIBUES


print ' <table class="noborder" style="width: 400px; margin-right: 50px;">
            <tbody>
                <tr class="liste_titre" >
                    <th>DEGREE D\'ATTRIBUTION DES PRODUITS</th>
                </tr>
                <tr>
                    <td>';

$dolgraph = new DolGraph();
$dolgraph->SetData($produitAttribution);
$dolgraph->setShowLegend(2);
$dolgraph->setShowPercent(0);
$dolgraph->SetType(array('pie'));
$dolgraph->SetWidth('500');
$dolgraph->draw('jojo1');
print $dolgraph->show($total1 ? 0 : 1);
print '             </td>
                </tr>
            </tbody>
        </table>';


////////////////////// PRODUITS LE PLUS ATTRIBUES

print ' <table class="noborder" style="width: 400px;">
            <tbody>
                <tr class="liste_titre">
                    <th>DEGRE D\'ATTRIBUTION AUX UTILISATEURS</th>
                </tr>
                <tr>
                    <td>';

$dolgraph1 = new DolGraph();
$dolgraph1->SetData($userAttribution);
$dolgraph1->setShowLegend(0);
$dolgraph1->setShowPercent(1);
$dolgraph1->SetType(array('bars'));
$dolgraph1->SetWidth('500');
$dolgraph1->draw('jojo2');
print $dolgraph1->show($total2 ? 0 : 1);
print '             </td>
                </tr>
            </tbody>
        </table>';

print '</div>';
// End of page
llxFooter();
$db->close();

?>

<style>
    .fichecenter {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }
</style>
