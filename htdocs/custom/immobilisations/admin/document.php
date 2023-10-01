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
require_once '../lib/immobilisations.lib.php';

// Translations
$langs->loadLangs(array("errors", "admin", "immobilisations@immobilisations"));

// Access control


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
$page_name = "ImmobilisationsSetup";

llxHeader('', 'A propos d\'immobilisations', $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre('Immobilisation Document', $linkback, 'title_setup');

// Configuration header
$head = immobilisationsAdminPrepareHead();
print dol_get_fiche_head($head, 'document', $langs->trans($page_name), 0, 'immobilisations@immobilisations');


require_once '../PHPExcel/Classes/PHPExcel.php';
dol_include_once('/immobilisations/class/immobilisations.class.php');


// Créer un nouvel objet PHPExcel
$excel = new PHPExcel();
$objectImmo = new Immobilisations($db);

// Créer une feuille de calcul active
$sheet = $excel->getActiveSheet();

print   '<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
            <tbody>
                <tr class="titre">
                    <td class="nobordernopadding valignmiddle col-title">
                        <div class="titre inline-block">Exporter le document du tableau de synthèse d\'immobilisation</div>
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
    $immoIncorporellesBrut = 0;
    $immoIncorporellesAmmortissement = 0;
    $ImmoIncorporellesNet = 0;
    $categories = [];
    $immobilisations = [];
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_categories WHERE status != 0 AND family = 3";
    $resql = $db->query($sql);
    if ($resql) {
        while ($item = $db->fetch_object($resql)) {
            array_push($categories, $item);
        }
        if (count($categories) > 0) {
            for ($i = 0; $i < count($categories); $i++) {
                $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE (status = 3 OR status = 5) AND fk_categorie = " . $categories[$i]->rowid;
                if (!empty($_POST['date_debut']) && !empty($_POST['date_fin'])) {
                    $date_debut = $_POST['date_debut'];
                    $date_fin = $_POST['date_fin'];
                    $sql1 .= " AND date_creation BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
                }
                $resql1 = $db->query($sql1);
                if ($resql1) {
                    while ($item = $db->fetch_object($resql1)) {
                        array_push($immobilisations, $item);
                    }
                }
            }
        }

        if (count($immobilisations) > 0) {
            for ($i = 0; $i < count($immobilisations); $i++) {
                $pourcentageImmobilise = $objectImmo->getPourcentageConsumption($immobilisations[$i]->rowid, $immobilisations[$i]->fk_categorie);
                $immoIncorporellesBrut += $immobilisations[$i]->amount_ht;
                $immoIncorporellesAmmortissement += ($immobilisations[$i]->amount_ht * $pourcentageImmobilise) / 100;
                $ImmoIncorporellesNet += $immobilisations[$i]->amount_ht - (($immobilisations[$i]->amount_ht * $pourcentageImmobilise) / 100);
            }
        }
    }

    $immoCorporellesBrut = 0;
    $immoCorporellesAmmortissement = 0;
    $ImmoCorporellesNet = 0;
    $categories = [];
    $immobilisations = [];
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_categories WHERE status != 0 AND family = 1";
    $resql = $db->query($sql);
    if ($resql) {
        while ($item = $db->fetch_object($resql)) {
            array_push($categories, $item);
        }
        if (count($categories) > 0) {
            for ($i = 0; $i < count($categories); $i++) {
                $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE (status = 3 OR status = 5) AND fk_categorie = " . $categories[$i]->rowid;
                if (!empty($_POST['date_debut']) && !empty($_POST['date_fin'])) {
                    $date_debut = $_POST['date_debut'];
                    $date_fin = $_POST['date_fin'];
                    $sql1 .= " AND date_creation BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
                }
                $resql1 = $db->query($sql1);
                if ($resql1) {
                    while ($item = $db->fetch_object($resql1)) {
                        array_push($immobilisations, $item);
                    }
                }
            }
        }
        if (count($immobilisations) > 0) {
            for ($i = 0; $i < count($immobilisations); $i++) {
                $pourcentageImmobilise = $objectImmo->getPourcentageConsumption($immobilisations[$i]->rowid, $immobilisations[$i]->fk_categorie);
                $immoCorporellesBrut += $immobilisations[$i]->amount_ht;
                $immoCorporellesAmmortissement += ($immobilisations[$i]->amount_ht * $pourcentageImmobilise) / 100;
                $ImmoCorporellesNet += $immobilisations[$i]->amount_ht - (($immobilisations[$i]->amount_ht * $pourcentageImmobilise) / 100);
            }
        }
    }


    $immoFinanciereBrut = 0;
    $immoFinanciereAmmortissement = 0;
    $ImmoFinanciereNet = 0;
    $categories = [];
    $immobilisations = [];
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_categories WHERE status != 0 AND family = 2";
    $resql = $db->query($sql);
    if ($resql) {
        while ($item = $db->fetch_object($resql)) {
            array_push($categories, $item);
        }
        if (count($categories) > 0) {
            for ($i = 0; $i < count($categories); $i++) {
                $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE (status = 3 OR status = 5) AND fk_categorie = " . $categories[$i]->rowid;
                if (!empty($_POST['date_debut']) && !empty($_POST['date_fin'])) {
                    $date_debut = $_POST['date_debut'];
                    $date_fin = $_POST['date_fin'];
                    $sql1 .= " AND date_creation BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
                }
                $resql1 = $db->query($sql1);
                if ($resql1) {
                    while ($item = $db->fetch_object($resql1)) {
                        array_push($immobilisations, $item);
                    }
                }
            }
        }
        if (count($immobilisations) > 0) {
            for ($i = 0; $i < count($immobilisations); $i++) {
                $pourcentageImmobilise = $objectImmo->getPourcentageConsumption($immobilisations[$i]->rowid, $immobilisations[$i]->fk_categorie);
                $immoFinanciereBrut += $immobilisations[$i]->amount_ht;
                $immoFinanciereAmmortissement += ($immobilisations[$i]->amount_ht * $pourcentageImmobilise) / 100;
                $ImmoFinanciereNet += $immobilisations[$i]->amount_ht - (($immobilisations[$i]->amount_ht * $pourcentageImmobilise) / 100);
            }
        }
    }

    $resql = $db->query($sql);
    $sheet->setCellValueByColumnAndRow(1, 1, "Brut");
    $sheet->setCellValueByColumnAndRow(2, 1, "Amortissement");
    $sheet->setCellValueByColumnAndRow(3, 1, "Net");

    $sheet->setCellValueByColumnAndRow(0, 2, "Immobilisations incorporelles");
    $sheet->setCellValueByColumnAndRow(1, 2, $immoIncorporellesBrut . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(2, 2, $immoIncorporellesAmmortissement . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(3, 2, $ImmoIncorporellesNet . ' ' . $conf->currency);

    $sheet->setCellValueByColumnAndRow(0, 3, "Immobilisations corporelles");
    $sheet->setCellValueByColumnAndRow(1, 3, $immoCorporellesBrut . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(2, 3, $immoCorporellesAmmortissement . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(3, 3, $ImmoCorporellesNet . ' ' . $conf->currency);

    $sheet->setCellValueByColumnAndRow(0, 4, "Immobilisations financières");
    $sheet->setCellValueByColumnAndRow(1, 4, $immoFinanciereBrut . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(2, 4, $immoFinanciereAmmortissement . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(3, 4, $ImmoFinanciereNet . ' ' . $conf->currency);

    $totalBrut = $immoIncorporellesBrut + $immoCorporellesBrut + $immoFinanciereBrut;
    $totalAmortissement = $immoIncorporellesAmmortissement + $immoCorporellesAmmortissement + $immoFinanciereAmmortissement;
    $totalNet = $ImmoIncorporellesNet + $ImmoCorporellesNet + $ImmoFinanciereNet;

    $sheet->setCellValueByColumnAndRow(0, 5, "ACTIF IMMOBILISE");
    $sheet->setCellValueByColumnAndRow(1, 5, $totalBrut . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(2, 5, $totalAmortissement . ' ' . $conf->currency);
    $sheet->setCellValueByColumnAndRow(3, 5, $totalNet . ' ' . $conf->currency);

    $tempFileName = 'RECAPITULATIF_IMMOBILISATIONS.xlsx';
    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    // supprimer l'acien fichier si il existe et enregistrer le fichier Excel sur le serveur
    if (file_exists('./' . $tempFileName)) unlink('./' . $tempFileName);

    $writer->save($tempFileName);

    // Télécharger automatiquement le fichier via JavaScript
    echo '<script>window.location = "' . $tempFileName . '";</script>';
}

// Page end
llxFooter();
$db->close();
