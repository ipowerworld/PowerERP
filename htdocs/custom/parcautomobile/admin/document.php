<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    parcautomobile/admin/about.php
 * \ingroup parcautomobile
 * \brief   About page of module ParcAutomobile.
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
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once '../lib/parcautomobile.lib.php';

// Translations
$langs->loadLangs(array("errors", "admin", "parcautomobile@parcautomobile"));

// Access control
// if (!$user->rights->parcautomobile->configuration->read) {
//     accessforbidden();
// }

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "ParcAutomobileAbout";

function getImmatriculation($vehicule_id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE rowid = " . $vehicule_id;
    $resql = $db->query($sql);
    $vehicule = $resql->fetch_object();
    return $vehicule->immatriculation;
}
function getChauffeur($chauffeur_id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_chauffeur WHERE rowid = " . $chauffeur_id;
    $resql = $db->query($sql);
    $chauffeur = $resql->fetch_object();
    return $chauffeur->ref;
}
function getConteneur($conteneur_id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_conteneur WHERE rowid = " . $conteneur_id;
    $resql = $db->query($sql);
    $conteneur = $resql->fetch_object();
    return [$conteneur->ref, $conteneur->type];
}
function getBooking($booking_id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_booking WHERE rowid = " . $booking_id;
    $resql = $db->query($sql);
    $booking = $resql->fetch_object();
    return $booking->ref;
}
function getVolumeCharge($transport_id)
{
    global $db;
    $sql = "SELECT COUNT(quantite) AS qty FROM " . MAIN_DB_PREFIX . "parcautomobile_transport_produit WHERE transport = " . $transport_id;
    $resql = $db->query($sql);
    $transport = $resql->fetch_object();
    return $transport->qty;
}



llxHeader('', 'A propos de Transit', $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre('A propos de Transit', $linkback, 'title_setup');

// Configuration header
$head = parcautomobileAdminPrepareHead();
print dol_get_fiche_head($head, 'document', $langs->trans($page_name), 0, 'parcautomobile@parcautomobile');


require_once '../PHPExcel/Classes/PHPExcel.php';

// Créer un nouvel objet PHPExcel
$excel = new PHPExcel();

// Créer une feuille de calcul active
$sheet = $excel->getActiveSheet();

print   '<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
            <tbody>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title">
                        <div class="titre inline-block">Exporter le document recapitulatif des activités de transport</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <form method="POST">
            <table class="border centpercent tableforfield">
                <tbody>
                    <tr>
                        <td class="titlefield">Date de debut</td>
                        <td class="valuefield"><input type="date" name="date_debut"></td>
                    </tr>
                    <tr>
                        <td class="titlefield">Date de fin</td>
                        <td class="valuefield"><input type="date" name="date_fin"></td>
                    </tr>
                </tbody>
            </table><br>
            <input type="submit" class="butAction" name="PrintExcellActivities" value="telecharger">
        </form>';

if (isset($_POST['PrintExcellActivities'])) {
    if (!empty($_POST['date_debut']) && !empty($_POST['date_fin'])) {
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status != 0 AND date_creation BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
    } else {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status != 0";
    }
    $resql = $db->query($sql);
    if ($resql->num_rows > 0) {
        $sheet->setCellValueByColumnAndRow(1, 2, "Numéro d'ordre");
        $sheet->setCellValueByColumnAndRow(2, 2, "Date d'arrivée du vehicule au port");
        $sheet->setCellValueByColumnAndRow(3, 2, "Immatriculation");
        $sheet->setCellValueByColumnAndRow(4, 2, "Volume embarqué");
        $sheet->setCellValueByColumnAndRow(5, 2, "Cout du carburant");
        $sheet->setCellValueByColumnAndRow(6, 2, "Taxe sur poids");
        $sheet->setCellValueByColumnAndRow(7, 2, "Frais de voyage");
        $sheet->setCellValueByColumnAndRow(8, 2, "Frais de penalité");
        $sheet->setCellValueByColumnAndRow(9, 2, "Nom du chauffeur");
        $sheet->setCellValueByColumnAndRow(10, 2, "Conteneur");
        $sheet->setCellValueByColumnAndRow(11, 2, "Booking");
        $sheet->setCellValueByColumnAndRow(12, 2, "taille");

        $i = 1;
        $rowIndex = 3;
        while ($transport = $resql->fetch_object()) {
            $columnIndex = 1;

            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, $i);
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->date_arrivee_bateau) ? "" : $transport->date_arrivee_bateau);
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, getImmatriculation($transport->vehicule));
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, getVolumeCharge($transport->rowid) . " m3");
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->prix_carburant) ? "" : $transport->prix_carburant . " FCFA");
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->taxe_poids) ? "" : $transport->taxe_poids . " FCFA");
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->frais_voyage) ? "" : $transport->frais_voyage . " FCFA");
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->penalite) ? "" : $transport->penalite . " FCFA");
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, getChauffeur($transport->chauffeur));
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->conteneur) ? "" : getConteneur($transport->conteneur)[0]);
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, getBooking($transport->booking));
            $sheet->setCellValueByColumnAndRow($columnIndex++, $rowIndex, is_null($transport->conteneur) ? "" : getConteneur($transport->conteneur)[1] == 0 ? "20'" : "40'");

            $i++;
            $rowIndex++;
        }

        $tempFileName = 'RECAPITULATIF_ACTIVITE_TRANSPORT_ROEM_SARL.xlsx';
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        // supprimer l'acien fichier si il existe et enregistrer le fichier Excel sur le serveur
        if (file_exists('./' . $tempFileName)) unlink('./' . $tempFileName);

        $writer->save($tempFileName);

        // Télécharger automatiquement le fichier via JavaScript
        echo '<script>window.location = "' . $tempFileName . '";</script>';
    } else {
        setEventMessages("Aucune activité pendant cette période", "", "warnings");
    }
}

// Page end
llxFooter();
$db->close();
