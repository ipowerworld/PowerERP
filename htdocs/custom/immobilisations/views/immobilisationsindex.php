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
 *	\file       immobilisations/immobilisationsindex.php
 *	\ingroup    immobilisations
 *	\brief      Home page of immobilisations top menu
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


// Load translation files required by the page
$langs->loadLangs(array("immobilisations@immobilisations"));

$action = GETPOST('action', 'aZ09');


// Security check
if (!$user->rights->immobilisations->immobilisations->statistic) accessforbidden();

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


include_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';
dol_include_once('/immobilisations/class/immobilisations.class.php');

$objectImmo = new Immobilisations($db);


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


/*
 * Actions
 */

$nom_immo_corporelles_en_cours = 0;
$nom_immo_incorporelles_en_cours = 0;
$nom_immo_financiere_en_cours = 0;
$nom_immo_corporelles_cloture = 0;
$nom_immo_incorporelles_cloture = 0;
$nom_immo_financiere_cloture = 0;
$nombreCorporelle = 0;
$nombreIncorporelle = 0;
$nombreFinanciere = 0;
$total1 = 0;
$total2 = 0;
$total3 = 0;


$cat_corporelle = [];
$cat_incorporelle = [];
$cat_financiere = [];

$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_categories WHERE status = 1 AND family = 1";
$resql = $db->query($sql);
if ($resql->num_rows > 0) {
	while ($item = $db->fetch_object($resql)) {
		array_push($cat_corporelle, $item);
	}
}
$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_categories WHERE status = 1 AND family = 3";
$resql = $db->query($sql);
if ($resql->num_rows > 0) {
	while ($item = $db->fetch_object($resql)) {
		array_push($cat_incorporelle, $item);
	}
}
$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immobilisations_categories WHERE status = 1 AND family = 2";
$resql = $db->query($sql);
if ($resql->num_rows > 0) {
	while ($item = $db->fetch_object($resql)) {
		array_push($cat_financiere, $item);
	}
}

if (count($cat_corporelle) > 0) {
	$i = 0;
	while ($i < count($cat_corporelle)) {
		$sql1 = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE status = 3 AND fk_categorie = " . $cat_corporelle[$i]->rowid;
		$resql1 = $db->query($sql1);
		if ($resql1->num_rows > 0) {
			$item = $db->fetch_object($resql1);
			$nom_immo_corporelles_en_cours += $item->total;
		}
		$i++;
	}
	$i = 0;
	while ($i < count($cat_corporelle)) {
		$sql1 = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE status = 5 AND fk_categorie = " . $cat_corporelle[$i]->rowid;
		$resql1 = $db->query($sql1);
		if ($resql1->num_rows > 0) {
			$item = $db->fetch_object($resql1);
			$nom_immo_corporelles_cloture += $item->total;
		}
		$i++;
	}
}

if (count($cat_incorporelle) > 0) {
	$i = 0;
	while ($i < count($cat_incorporelle)) {
		$sql1 = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE status = 3 AND fk_categorie = " . $cat_incorporelle[$i]->rowid;
		$resql1 = $db->query($sql1);
		if ($resql1->num_rows > 0) {
			$item = $db->fetch_object($resql1);
			$nom_immo_incorporelles_en_cours += $item->total;
		}
		$i++;
	}
	$i = 0;
	while ($i < count($cat_incorporelle)) {
		$sql1 = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE status = 5 AND fk_categorie = " . $cat_incorporelle[$i]->rowid;
		$resql1 = $db->query($sql1);
		if ($resql1->num_rows > 0) {
			$item = $db->fetch_object($resql1);
			$nom_immo_incorporelles_cloture += $item->total;
		}
		$i++;
	}
}

if (count($cat_financiere) > 0) {
	$i = 0;
	while ($i < count($cat_financiere)) {
		$sql1 = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE status = 3 AND fk_categorie = " . $cat_financiere[$i]->rowid;
		$resql1 = $db->query($sql1);
		if ($resql1->num_rows > 0) {
			$item = $db->fetch_object($resql1);
			$nom_immo_financiere_en_cours += $item->total;
		}
		$i++;
	}
	$i = 0;
	while ($i < count($cat_financiere)) {
		$sql1 = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE status = 5 AND fk_categorie = " . $cat_financiere[$i]->rowid;
		$resql1 = $db->query($sql1);
		if ($resql1->num_rows > 0) {
			$item = $db->fetch_object($resql1);
			$nom_immo_financiere_cloture += $item->total;
		}
		$i++;
	}
}

// Courbe evolution

for ($i = 6; $i > -1; $i--) {
	$Today = new DateTime("now");
	$st_date1 = $Today->sub(new DateInterval('P' . $i . 'M'));
	$month = $st_date1->format('m');
	$year = $st_date1->format('Y');
	$month_title = $st_date1->format('M');
	$j = 0;
	while ($j < count($cat_corporelle)) {
		$sql = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE (status = 3 OR status = 5) AND MONTH(date_creation) = " . $month . " AND YEAR(date_creation) = " . $year . " AND fk_categorie = " . $cat_corporelle[$j]->rowid;
		$resql = $db->query($sql);
		if ($resql && $resql->num_rows > 0) {
			while ($item = $db->fetch_object($resql)) {
				$nombreCorporelle += $item->total;
			}
		}
		$j++;
	}

	$EvolutionCorporelle[] = array($month_title, $nombreCorporelle);
	$total1 += $nombreCorporelle;
}

for ($i = 6; $i > -1; $i--) {
	$Today = new DateTime("now");
	$st_date1 = $Today->sub(new DateInterval('P' . $i . 'M'));
	$month = $st_date1->format('m');
	$year = $st_date1->format('Y');
	$month_title = $st_date1->format('M');
	$j = 0;
	while ($j < count($cat_incorporelle)) {
		$sql = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE (status = 3 OR status = 5) AND MONTH(date_creation) = " . $month . " AND YEAR(date_creation) = " . $year . " AND fk_categorie = " . $cat_incorporelle[$j]->rowid;
		$resql = $db->query($sql);
		if ($resql && $resql->num_rows > 0) {
			while ($item = $db->fetch_object($resql)) {
				$nombreIncorporelle += $item->total;
			}
		}
		$j++;
	}

	$EvolutionIncorporelle[] = array($month_title, $nombreIncorporelle);
	$total2 += $nombreIncorporelle;
}

for ($i = 6; $i > -1; $i--) {
	$Today = new DateTime("now");
	$st_date1 = $Today->sub(new DateInterval('P' . $i . 'M'));
	$month = $st_date1->format('m');
	$year = $st_date1->format('Y');
	$month_title = $st_date1->format('M');
	$j = 0;
	while ($j < count($cat_financiere)) {
		$sql = "SELECT COUNT(*) AS total FROM " . MAIN_DB_PREFIX . "immobilisations_immobilisations WHERE (status = 3 OR status = 5) AND MONTH(date_creation) = " . $month . " AND YEAR(date_creation) = " . $year . " AND fk_categorie = " . $cat_financiere[$j]->rowid;
		$resql = $db->query($sql);
		if ($resql && $resql->num_rows > 0) {
			while ($item = $db->fetch_object($resql)) {
				$nombreFinanciere += $item->total;
			}
		}
		$j++;
	}

	$EvolutionFinanciere[] = array($month_title, $nombreFinanciere);
	$total3 += $nombreFinanciere;
}

/*
 * View
 */

llxHeader("", $langs->trans("ImmobilisationsDashboard"));

print load_fiche_titre($langs->trans("ImmobilisationsDashboard"), '', 'object_statistique.png@immobilisations');

print '<div class="fichecenter">';
print '<div class="opened-dash-board-wrap">';
print '<div class="box-flex-container">';



//  IMMOBILISATIONS CORPORELLES

print ' <div class="box-flex-item">';

print ' <div class="box boxdraggable" id="boxto_11" style="padding-top:3%">';
print '		<table summary="boxtable11" width="100%" class="noborder boxtable">';
print '			<tr class="liste_titre box_titre">';
print '				<td colspan="1">';
print '					<div class="tdoverflowmax400 maxwidth250onsmartphone float">Immobilisations corporelles</div>
						<div class="nocellnopadd boxclose floatright nowraponall"></div>';
print '				</td>';
print '			</tr>';
print '		</table>';
print ' </div>';
print ' <div class="box-flex-item-with-margin" style="padding-bottom:10%">';
print ' 	<div class="info-box ">';
print '			<span class="info-box-icon bg-infobox-contrat">
					<i class="fa fa-building"></i>
				</span>';
print ' 		<div class="info-box-content">';
print ' 			<div class="info-box-lines">';
print ' 		<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('encours') . '</span>
							<span class="classfortooltip opacitymedium">' . $nom_immo_corporelles_en_cours . '</span>
				</div>';
print ' 		<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('cloture') . '</span>
							<span class="classfortooltip opacitymedium">' . $nom_immo_corporelles_cloture . '</span>
						</div>
				</div>';
print '			<div class="info-box-line">
					<span class="marginrightonly">' . $langs->trans('amountbrut') . '</span>
					<span class="classfortooltip opacitymedium">' . $immoCorporellesBrut . ' ' . $conf->currency . '</span>
				</div>';
print '			<div class="info-box-line">
					<span class="marginrightonly">' . $langs->trans('amountnet') . '</span>
					<span class="classfortooltip opacitymedium">' . $ImmoCorporellesNet . ' ' . $conf->currency . '</span>
				</div>';
print '			<div class="info-box-line">
					<span class="marginrightonly">' . $langs->trans('amountimmo') . '</span>
					<span class="classfortooltip opacitymedium">' . $immoCorporellesAmmortissement . ' ' . $conf->currency . '</span>
				</div>';
print '			<div class="info-box-line">
					<span class="marginrightonly">' . $langs->trans('pourcentAmmorti') . '</span>
					<span class="classfortooltip opacitymedium">';
print $immoCorporellesBrut == 0 ? '0' : (int)(($immoCorporellesAmmortissement * 100) / $immoCorporellesBrut);
print ' %</span>
				</div>';
print '			</div>';
print '		</div>';
print ' </div>';



print '</div>';


//  IMMOBILISATIONS INCORPORELLES

print ' <div class="box-flex-item">';

print ' 	<div class="box boxdraggable" id="boxto_11" style="padding-top:3%">';
print '			<table summary="boxtable11" width="100%" class="noborder boxtable">';
print '				<tr class="liste_titre box_titre">';
print '					<td colspan="1">';
print '						<div class="tdoverflowmax400 maxwidth250onsmartphone float">Immobilisations incorporelles</div>
							<div class="nocellnopadd boxclose floatright nowraponall"></div>';
print '					</td>';
print '				</tr>';
print '			</table>';
print '		</div>';
print ' 	<div class="box-flex-item-with-margin" style="padding-bottom:10%">';
print '			<div class="info-box">';
print '				<span class="info-box-icon bg-infobox-contrat">
						<i class="fa fa-file"></i>
					</span>';
print '				<div class="info-box-content">';
print '					<div class="info-box-lines">';
print '						<div class="info-box-line">
								<span class="marginrightonly">' . $langs->trans('encours') . '</span>
								<span class="classfortooltip opacitymedium">' . $nom_immo_incorporelles_en_cours . '</span>
							</div>';
print '						<div class="info-box-line">
								<span class="marginrightonly">' . $langs->trans('cloture') . '</span>
								<span class="classfortooltip opacitymedium">' . $nom_immo_incorporelles_cloture . '</span>
							</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('amountbrut') . '</span>
							<span class="classfortooltip opacitymedium">' . $immoIncorporellesBrut . ' ' . $conf->currency . '</span>
						</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('amountnet') . '</span>
							<span class="classfortooltip opacitymedium">' . $ImmoIncorporellesNet . ' ' . $conf->currency . '</span>
						</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('amountimmo') . '</span>
							<span class="classfortooltip opacitymedium">' . $immoIncorporellesAmmortissement . ' ' . $conf->currency . '</span>
						</div>';
print '			<div class="info-box-line">
						<span class="marginrightonly">' . $langs->trans('pourcentAmmorti') . '</span>
						<span class="classfortooltip opacitymedium">';
print $immoIncorporellesBrut == 0 ? '0' : (int)(($immoIncorporellesAmmortissement * 100) / $immoIncorporellesBrut);
print ' %</span>
					</div>';
print '					</div>';
print '				</div>';
print '			</div>';
print '		</div>';
print ' </div>';


//  IMMOBILISATIONS FINANCIERES

print ' <div class="box-flex-item">';

print ' 	<div class="box boxdraggable" id="boxto_11" style="padding-top:3%">';
print '			<table summary="boxtable11" width="100%" class="noborder boxtable">';
print '				<tr class="liste_titre box_titre">';
print '					<td colspan="1">';
print '						<div class="tdoverflowmax400 maxwidth250onsmartphone float">Immobilisations financières</div>
							<div class="nocellnopadd boxclose floatright nowraponall"></div>';
print '					</td>';
print '				</tr>';
print '			</table>';
print '		</div>';
print '		<div class="box-flex-item-with-margin">';
print '			<div class="info-box">';
print '				<span class="info-box-icon bg-infobox-contrat">
						<i class="fa fa-dollar"></i>
					</span>';
print '				<div class="info-box-content">';
print '				<div class="info-box-lines">';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('encours') . '</span>
							<span class="classfortooltip opacitymedium">' . $nom_immo_financiere_en_cours . '</span>
						</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('cloture') . '</span>
							<span class="classfortooltip opacitymedium">' . $nom_immo_financiere_cloture . '</span>
						</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('amountbrut') . '</span>
							<span class="classfortooltip opacitymedium">' . $immoFinanciereBrut . ' ' . $conf->currency . '</span>
						</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('amountnet') . '</span>
							<span class="classfortooltip opacitymedium">' . $ImmoFinanciereNet . ' ' . $conf->currency . '</span>
						</div>';
print '					<div class="info-box-line">
							<span class="marginrightonly">' . $langs->trans('amountimmo') . '</span>
							<span class="classfortooltip opacitymedium">' . $immoFinanciereAmmortissement . ' ' . $conf->currency . '</span>
						</div>';
print '			<div class="info-box-line">
						<span class="marginrightonly">' . $langs->trans('pourcentAmmorti') . '</span>
						<span class="classfortooltip opacitymedium">';
print $immoFinanciereBrut == 0 ? '0' : (int)(($immoFinanciereAmmortissement * 100) / $immoFinanciereBrut);
print ' %</span>
					</div>';
print '				</div>';
print '			</div>';
print '		</div>';
print ' </div>';
print '</div>';


// COURBE D'EVOLUTION CORPORELLE

print ' <div class="box-flex-item">';
print ' 	<div class="box boxdraggable" id="boxto_11" style="padding-top:3%">';
print '			<table summary="boxtable11" width="100%" class="noborder boxtable">';
print '				<tr class="liste_titre box_titre">';
print '					<td colspan="1">';
print '						<div class="tdoverflowmax400 maxwidth250onsmartphone float">Evolution d\'immobilisation corporelle</div>
							<div class="nocellnopadd boxclose floatright nowraponall"></div>';
print '					</td>';
print '				</tr>';
print '			</table>';
print '		</div>';
print '		<div class="box-flex-item-with-margin" style="padding-bottom:10%">';
print '			<div class="info-box ">';
$dolgraph1 = new DolGraph();
$dolgraph1->SetData($EvolutionCorporelle);
$dolgraph1->setShowLegend(0);
$dolgraph1->setShowPercent(1);
$dolgraph1->SetType(array('linesnopoint'));
$dolgraph1->SetWidth('200');
$dolgraph1->SetYLabel("Nombre d'immobilsation");
$dolgraph1->draw('jojo4');
print $dolgraph1->show($total1 ? 0 : 1);
print '</div></div></div>';


// COURBE D'EVOLUTION INCORPORELLE

print ' <div class="box-flex-item">';
print ' 	<div class="box boxdraggable" id="boxto_11" style="padding-top:3%">';
print '			<table summary="boxtable11" width="100%" class="noborder boxtable">';
print '				<tr class="liste_titre box_titre">';
print '					<td colspan="1">';
print '						<div class="tdoverflowmax400 maxwidth250onsmartphone float">Evolution d\'immobilisation incorporelle</div>
							<div class="nocellnopadd boxclose floatright nowraponall"></div>';
print '					</td>';
print '				</tr>';
print '			</table>';
print '		</div>';
print '		<div class="box-flex-item-with-margin" style="padding-bottom:10%">';
print '			<div class="info-box ">';
$dolgraph2 = new DolGraph();
$dolgraph2->SetData($EvolutionIncorporelle);
$dolgraph2->setShowLegend(0);
$dolgraph2->setShowPercent(1);
$dolgraph2->SetType(array('linesnopoint'));
$dolgraph2->SetWidth('200');
$dolgraph2->SetYLabel("Nombre d'immobilsation");
$dolgraph2->draw('jojo5');
print $dolgraph2->show($total2 ? 0 : 1);
print '</div></div></div>';


// COURBE D'EVOLUTION INCORPORELLE

print ' <div class="box-flex-item">';
print ' 	<div class="box boxdraggable" id="boxto_11" style="padding-top:3%">';
print '			<table summary="boxtable11" width="100%" class="noborder boxtable">';
print '				<tr class="liste_titre box_titre">';
print '					<td colspan="1">';
print '						<div class="tdoverflowmax400 maxwidth250onsmartphone float">Evolution d\'immobilisation financière</div>
							<div class="nocellnopadd boxclose floatright nowraponall"></div>';
print '					</td>';
print '				</tr>';
print '			</table>';
print '		</div>';
print '		<div class="box-flex-item-with-margin" style="padding-bottom:10%">';
print '			<div class="info-box ">';
$dolgraph3 = new DolGraph();
$dolgraph3->SetData($EvolutionFinanciere);
$dolgraph3->setShowLegend(0);
$dolgraph3->setShowPercent(1);
$dolgraph3->SetType(array('linesnopoint'));
$dolgraph3->SetWidth('200');
$dolgraph3->SetYLabel("Nombre d'immobilsation");
$dolgraph3->draw('jojo6');
print $dolgraph3->show($total3 ? 0 : 1);
print '</div></div></div>';


print '</div>';
print '</div>';
print '</div>';


// End of page
llxFooter();
$db->close();

?>

<style>
	.info-box-line {
		margin-bottom: 5px;
	}

	.info-box {
		height: 160px !important;
	}

	.info-box-icon {
		display: flex;
		flex-direction: column;
		align-items: center !important;
		justify-content: center !important;
		height: 100% !important;
	}

	.info-box-lines {
		display: flex;
		flex-direction: column;
		align-items: flex-start !important;
		justify-content: center !important;
	}
</style>
