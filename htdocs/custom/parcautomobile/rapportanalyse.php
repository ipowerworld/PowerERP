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
 *   	\file       vehicule_card.php
 *		\ingroup    parcautomobile
 *		\brief      Page to create/edit/view vehicule
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
dol_include_once('/parcautomobile/class/vehicule.class.php');
dol_include_once('/parcautomobile/lib/parcautomobile_vehicule.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("parcautomobile@parcautomobile", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'vehiculecard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Vehicule($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->parcautomobile->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('vehiculecard', 'globalcard')); // Note that conf->hooks_modules contains array

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
    $permissiontoread = $user->rights->parcautomobile->statistique->look;
} else {
    $permissiontoread = 1;
}

$upload_dir = $conf->parcautomobile->multidir_output[isset($object->entity) ? $object->entity : 1] . '/vehicule';
$form = new Form($db);

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';

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

    $backurlforlist = dol_buildpath('/parcautomobile/vehicule_list.php', 1);

    if (empty($backtopage) || ($cancel && empty($id))) {
        if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
            if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
                $backtopage = $backurlforlist;
            } else {
                $backtopage = dol_buildpath('/parcautomobile/vehicule_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
            }
        }
    }

    $triggermodname = 'PARCAUTOMOBILE_VEHICULE_MODIFY'; // Name of trigger action code to execute when we modify record

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
    $triggersendname = 'PARCAUTOMOBILE_VEHICULE_SENTBYMAIL';
    $autocopy = 'MAIN_MAIL_AUTOCOPY_VEHICULE_TO';
    $trackid = 'vehicule' . $object->id;
    include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}

include_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';


$title = "Statistique";
$help_url = '';
llxHeader('', $title, $help_url);
$ChauffeurTransport = [];
$VehiculesTransport  = [];
$total4 = 0;
$total5 = 0;


/* custom */

function getFraisPenalite()
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "const WHERE name = 'TRANSIT_DE_FRAIS_PENALITE_PAR_DETENTION_JOURNALIER'";
    $resql = $db->query($sql);
    if ($resql->num_rows != 0) {
        $item = $db->fetch_object($resql);
        return (int) $item->value;
    } else {
        return 5;
    }
}

$vehicule = new Vehicule($db);
$All_vehicules = $vehicule->fetchAllVehicules();
$nombre_veh = count($All_vehicules);

$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE etat = 0";
$resql = $db->query($sql);

$sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE etat = 1";
$resql1 = $db->query($sql1);

$sql2 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE etat = 2";
$resql2 = $db->query($sql2);

$sql3 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE etat = 3";
$resql3 = $db->query($sql3);

$sql4 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE etat = 4";
$resql4 = $db->query($sql4);

$sql5 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE disponibilite = 0";
$resql5 = $db->query($sql5);

$sql6 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE disponibilite = 1";
$resql6 = $db->query($sql6);

if ($nombre_veh == 0) {
    $nombre_veh_neuf = 0;
    $nombre_veh_dispo = 0;
    $nombre_veh_nondispo = 0;
    $nombre_veh_detruite = 0;
    $nombre_veh_bonetat = 0;
    $nombre_veh_endommage = 0;
    $nombre_veh_reparation = 0;
} else {
    $nombre_veh_endommage = ($resql2->num_rows / $nombre_veh) * 100;
    $nombre_veh_detruite = ($resql4->num_rows / $nombre_veh) * 100;
    $nombre_veh_dispo = ($resql5->num_rows / $nombre_veh) * 100;
    $nombre_veh_bonetat = ($resql3->num_rows / $nombre_veh) * 100;
    $nombre_veh_nondispo = ($resql6->num_rows / $nombre_veh) * 100;
    $nombre_veh_neuf = ($resql->num_rows / $nombre_veh) * 100;
    $nombre_veh_reparation = ($resql1->num_rows / $nombre_veh) * 100;
}

$chauffeurs = array();
$ChauffeurData = array();

$sql8 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_chauffeur WHERE status = 1";
$resql8 = $db->query($sql8);
$total3 = 0;
while ($ite = $db->fetch_object($resql8)) {
    array_push($chauffeurs, $ite);
}

$day = 0;
$hours = 0;
$distance = 0;
$nb_place = 0;
$place_pourcentage = 0;

//************ */ Arret stat

$Arret = [];
$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_arret WHERE status = 1";
$resql9 = $db->query($sql);
while ($item = $db->fetch_object($resql9)) {
    array_push($Arret, $item);
}


if (isset($_POST['arret'])) {
    $arret_depart = $_POST['arret_depart'];
    $arret_fin = $_POST['arret_fin'];
    $Transport_arret_data = [];
    if ($arret_depart == $arret_fin) {
        setEventMessages("Veuillez entrer deux arrets differents", "", "warnings");
    } else {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status = 1 and (lieu_depart = " . (int)$arret_depart . " or lieu_depart = " . (int)$arret_fin . ") and (lieu_arrive = " . (int)$arret_depart . " or lieu_arrive = " . (int)$arret_fin . ")";
        $resql = $db->query($sql);
        if ($db->num_rows($resql) > 0) {
            while ($item = $db->fetch_object($resql)) {
                array_push($Transport_arret_data, $item);
            }

            for ($i = 0; $i < count($Transport_arret_data); $i++) {
                $distance += $Transport_arret_data[$i]->kilometrage;
                $debut = new DateTime($Transport_arret_data[$i]->date_depart);
                $fin = new DateTime($Transport_arret_data[$i]->date_arrive);
                $interval = ($debut)->diff($fin);
                $day += $interval->format('%d');
                $hours += $interval->format('%h');
            }

            $distance = $distance / count($Transport_arret_data);
            $day = $day / count($Transport_arret_data);
            $hours = $hours / count($Transport_arret_data);
        } else {
            setEventMessages("Aucun transport effectué entre ces deux points d'arret", "", "warnings");
        }
    }
}

//************ */ Vehicule occupation

if (isset($_POST['vehicule'])) {

    $transport_vehicule = [];
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status = 1 and vehicule = " . $_POST['vehicule'];
    $resql = $db->query($sql);
    while ($item = $db->fetch_object($resql)) {
        array_push($transport_vehicule, $item);
    }

    if ($db->num_rows($resql) > 0) {
        for ($i = 0; $i < count($transport_vehicule); $i++) {
            $nb_place += $transport_vehicule[$i]->nombre_place;
        }
        $nb_place = $nb_place / count($transport_vehicule);
        $vehiculeChoice = $vehicule->fetchInfoVehicules($_POST['vehicule']);
        $place_pourcentage = ($nb_place / $vehiculeChoice->place) * 100;
    } else {
        setEventMessages("Aucun transport n'a été effectué avec ce vehicule", "", "warnings");
    }
}

//************ */ Frequence vehicule

if (isset($_POST['date-debut'])) {
    $date_debut = $_POST['date-debut'];
    $date_fin = $_POST['date-fin'];
    $ab = new DateTime($date_debut);
    $ac = new DateTime($date_fin);
    if ($ac <= $ab) {
        $date_fin = new DateTime("now");
        $date_fin = $date_fin->format('Y-m-d');

        $st_date = new DateTime("now");
        $date_debut = $st_date->sub(new DateInterval('P5M'));
        $date_debut = $date_debut->format('Y-m-d');

        setEventMessages("Veuillez entrer des dates valides", "", "warnings");
    }
} else {
    $date_fin = new DateTime("now");
    $date_fin = $date_fin->format('Y-m-d');

    $st_date = new DateTime("now");
    $date_debut = $st_date->sub(new DateInterval('P5M'));
    $date_debut = $date_debut->format('Y-m-d');
}

for ($i = 0; $i < count($All_vehicules); $i++) {
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE vehicule = " . $All_vehicules[$i]->rowid . " and date_depart > '" . $date_debut . "' and date_arrive < '" . $date_fin . "'";
    $resql = $db->query($sql);
    $VehiculesTransport[] = array($All_vehicules[$i]->ref, $db->num_rows($resql));
    $total4 += $db->num_rows($resql);
}


//************ */ Frequence chauffeur

if (isset($_POST['date-debut-chauffeur'])) {
    $date_debut_chauffeur = $_POST['date-debut-chauffeur'];
    $date_fin_chauffeur = $_POST['date-fin-chauffeur'];
    $ab = new DateTime($date_debut_chauffeur);
    $ac = new DateTime($date_fin_chauffeur);
    if ($ac <= $ab) {
        $date_fin_chauffeur = new DateTime("now");
        $date_fin_chauffeur = $date_fin_chauffeur->format('Y-m-d');

        $st_date = new DateTime("now");
        $date_debut_chauffeur = $st_date->sub(new DateInterval('P5M'));
        $date_debut_chauffeur = $date_debut_chauffeur->format('Y-m-d');
        setEventMessages("Veuillez entrer des dates valides", "", "warnings");
    }
} else {
    $date_fin_chauffeur = new DateTime("now");
    $date_fin_chauffeur = $date_fin_chauffeur->format('Y-m-d');

    $st_date = new DateTime("now");
    $date_debut_chauffeur = $st_date->sub(new DateInterval('P5M'));
    $date_debut_chauffeur = $date_debut_chauffeur->format('Y-m-d');
}

for ($i = 0; $i < count($chauffeurs); $i++) {
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE chauffeur = " . $chauffeurs[$i]->rowid . " and date_depart > '" . $date_debut_chauffeur . "' and date_arrive < '" . $date_fin_chauffeur . "'";
    $resql = $db->query($sql);
    $ChauffeurTransport[] = array($chauffeurs[$i]->ref, $db->num_rows($resql));
    $total3 += $db->num_rows($resql);
}

//************ */ Couts operationnels

$frais_penalite = getFraisPenalite();
$cout = 0;

for ($i = 5; $i > -1; $i--) {
    $Today = new DateTime("now");
    $st_date1 = $Today->sub(new DateInterval('P' . $i . 'M'));
    $month = $st_date1->format('m');
    $year = $st_date1->format('Y');
    $month_title = $st_date1->format('M');

    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE MONTH(date_depart) = " . $month . " and YEAR(date_depart) = " . $year . "";
    $resql = $db->query($sql);
    while ($item = $db->fetch_object($resql)) {
        $cout += $item->prix_carburant + $item->taxe_poids + $item->frais_voyage + $item->penalite;
    }

    $sql2 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_booking WHERE MONTH(date_creation) = " . $month . " and YEAR(date_creation) = " . $year . " and status = 1";
    $resql2 = $db->query($sql2);
    if ($db->num_rows($resql2) > 0) {
        while ($item2 = $db->fetch_object($resql2)) {
            $sql3 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_booking_conteneur WHERE booking = " . $item2->rowid;
            $resql3 = $db->query($sql3);
            while ($item3 = $db->fetch_object($resql3)) {
                $date_echeance = new DateTime($item3->date_echeance);
                $date_remise_conteneur = new DateTime($item3->date_sortie_vide);
                if ($date_remise_conteneur > $date_echeance) {
                    $interval_penalite = ($date_remise_conteneur)->diff($date_echeance);
                    $interval_jours = $interval_penalite->format('%d') + $interval_penalite->format('%m') * 30;
                    $cout += $interval_jours * $frais_penalite;
                }
            }
        }
    }

    $CoutsOperationnels[] = array($month_title, $cout);
    $total5 += $cout;
}


//*************** */ Ponctualite

$Transport_poctualite = [];
$nbTransport = 0;
$pourcent = 0;

function isLate($transport)
{
    global $db;
    if ($transport->type == 0) {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_booking_conteneur WHERE booking = " . $transport->booking . " and conteneur = " . $transport->conteneur;
        $resql = $db->query($sql);
        $item = $db->fetch_object($resql);
        $bc = new DateTime($transport->date_arrive);
        $bd = new DateTime($item->date_echeance);
        if ($bc > $bd) {
            return true;
        }
        return false;
    } else {
        return false;
    }
}

if (isset($_POST['date-debut-ponc'])) {
    $date_debut_ponc = $_POST['date-debut-ponc'];
    $date_fin_ponc = $_POST['date-fin-ponc'];
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status = 1 and date_depart > '" . $date_debut_ponc . "' and date_arrive < '" . $date_fin_ponc . "'";
} else {
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status = 1";
    $date_debut_ponc = new DateTime('now');
    $date_fin_ponc = new DateTime('now');
}
$resql = $db->query($sql);
if ($db->num_rows($resql) > 0) {
    while ($item = $db->fetch_object($resql)) {
        array_push($Transport_poctualite, $item);
        if (!isLate($item)) {
            $nbTransport++;
        }
    }
    $pourcent = ($nbTransport / count($Transport_poctualite)) * 100;
} else {
    $pourcent = 0;
    setEventMessages("Aucun transport n'a été effectué pendant cette période", "", "warnings");
}

print '<h3>&nbsp;STATISTIQUES DE TRANSIT</h3>';

/*
 * View
 *
 * Put here all code to build page
 */

?>

<br><br><br><br>

<div class="ctn-bord">

    <!-- Stat ponctualite -->

    <div class="ponct-cont">
        <div id="nb">
            <div id="countdown"></div>%
        </div><br><br><br><br><br>
        <span><?= $nbTransport ?> transport(s) effectués dans les delais</span>
        <span><?= count($Transport_poctualite) - $nbTransport ?> transport(s) effectués hors delais</span>
        <form action="./rapportanalyse.php" method="POST">
            <th style="display:flex; flex-direction: row; gap: 10px; padding-bottom: 20px;">
                <div>
                    Début &nbsp; <input name="date-debut-ponc" type="date" required>
                </div>
                <div>
                    Fin &nbsp; <input name="date-fin-ponc" type="date" required>
                </div>
                <div>
                    <button class="butAction">Valider</button>
                </div>
            </th>
        </form>
    </div>

    <!-- Stat arret -->
    <?php
    print '


    <div class="arret-cont">
        <div class="arret-cont1" style="background: #8956A1;">
            <img src="./img/stat-arret.png" alt="image" width="40px">
        </div>
        <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
            <input type="hidden" name="arret">
            <div class="cont2">
                <h3>Temps moyen et distance moyenne de transit</h3>
                <div class="arret-select-cont">
                    <div>
                        <span>Départ</span>
                        <select name="arret_depart" required>';
    $i = 0;
    while ($i < count($Arret)) {
        if (isset($_POST['arret']) and $Arret[$i]->rowid == $arret_depart) {
            print '<option value=' . $Arret[$i]->rowid . ' selected="selected">' . $Arret[$i]->ref . '</option>';
        } else {
            print '<option value=' . $Arret[$i]->rowid . '>' . $Arret[$i]->ref . '</option>';
        }
        $i++;
    }

    print '         </select>
                    </div>
                    <div>
                        <span>Fin</span>
                        <select name="arret_fin" required>';
    $i = 0;
    while ($i < count($Arret)) {
        if (isset($_POST['arret']) and $Arret[$i]->rowid == $arret_fin) {
            print '<option value=' . $Arret[$i]->rowid . ' selected="selected">' . $Arret[$i]->ref . '</option>';
        } else {
            print '<option value=' . $Arret[$i]->rowid . '>' . $Arret[$i]->ref . '</option>';
        }
        $i++;
    }
    print '         </select>
                    </div>
                </div>
                <div class="arret-select-cont">
                    <div>
                        <h4>' . $day . ' Jour(s) ' . $hours . ' heure(s)</h4>
                        <span>Temps moyen</span>
                    </div>
                    <div>
                        <h4>' . $distance . ' Km</h4>
                        <span>Distance moyenne</span>
                    </div>
                </div>
                <button class="butAction" style="margin:0;transform: translateY(-5px);">Valider</button>
            </div>
        </form>
    </div>';

    ?>

    <!-- Stat vehicule -->

    <?php

    print '<div class="arret-cont">
            <div class="arret-cont1" style="background: #3C93B7;">
                <img src="./img/stat-vehicule.png" alt="image" width="80px">
            </div>
            <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                <div class="cont2">
                    <h3>Capacité d\'acceuil & Taux d\'occupation moyenne</h3>
                    <div class="arret-select-cont" style="grid-template-columns: 20% 70%; margin-top: 20px;">
                        <span>Véhicule</span>
                        <select name="vehicule" required>';
    $i = 0;
    while ($i < count($All_vehicules)) {
        print '<option value=' . $All_vehicules[$i]->rowid . '>' . $All_vehicules[$i]->ref . '</option>';
        $i++;
    }

    print '             </select>

                    </div>
                    <div class="arret-select-cont" style="grid-template-columns: 40% 60%; width: 90%;">
                        <div>
                            <h4>' . $nb_place . ' Place(s)</h4>
                        </div>
                        <div>
                            <h4>' . $place_pourcentage . ' % d\'occupation</h4>
                        </div>
                    </div>
                    <button class="butAction" style="margin:0;transform: translateY(-5px);">Valider</button> 
                </div>
            </form>
        </div>';

    ////////////////////// premier stat

    print '<div class="flex:1"><div class="div-table-responsive-no-min">
            <table class="noborder centpercent">
            <tbody> 
                <tr class="liste_titre">
                    <th>Moyenne de l\'etat des véhicules</th>
                </tr>
                <tr>
                    <td>';


    $total1 = 0;
    $VehiculesEtat[] = array("Véhicules neufs", round($nombre_veh_neuf));
    $VehiculesEtat[] = array(("Véhicule en bon etat"), round($nombre_veh_bonetat));
    $VehiculesEtat[] = array(("Véhicule endommagés"), round($nombre_veh_endommage));
    $VehiculesEtat[] = array(("Véhicule en reparation"), round($nombre_veh_reparation));
    $VehiculesEtat[] = array(("Véhicule détruit"), round($nombre_veh_detruite));
    $total1 = $nombre_veh_neuf + $nombre_veh_bonetat + $nombre_veh_endommage + $nombre_veh_reparation + $nombre_veh_detruite;

    $dolgraph = new DolGraph();
    $dolgraph->SetData($VehiculesEtat);
    $dolgraph->setShowLegend(2);
    $dolgraph->setShowPercent(0);
    $dolgraph->SetType(array('pie'));
    $dolgraph->SetWidth('540');
    $dolgraph->draw('jojo');
    print $dolgraph->show($total1 ? 0 : 1);
    print ' </td></tr></tbody></table></div></div>';

    //////////////////////// deuxieme stat

    print '<div class="flex:1"><div class="div-table-responsive-no-min">
            <table class="noborder centpercent">
            <tbody> 
                <tr class="liste_titre">
                    <th>Moyenne de la disponibilité des véhicules</th>
                </tr>
                <tr>
                    <td>';
    $total2 = 0;
    $VehiculesDispo[] = array("Véhicules disponibles", round($nombre_veh_dispo));
    $VehiculesDispo[] = array(("Véhicule non disponibles"), round($nombre_veh_nondispo));
    $total2 = $nombre_veh_dispo + $nombre_veh_nondispo;

    $dolgraph2 = new DolGraph();
    $dolgraph2->SetData($VehiculesDispo);
    $dolgraph2->setShowLegend(2);
    $dolgraph2->setShowPercent(0);
    $dolgraph2->SetType(array('pie'));
    $dolgraph2->SetWidth('540');
    $dolgraph2->draw('jojob');
    print $dolgraph2->show($total2 ? 0 : 1);
    print ' </td></tr></tbody></table></div></div>';

    // Stat frequence de service des vehicules

    print '<div class="flex:1"><div class="div-table-responsive-no-min">
     <table class="noborder centpercent">
     <tbody> 
         <tr class="liste_titre">
             <th>Fréquence de service des véhicules &nbsp;&nbsp; (Par defaut durant les 5 derniers mois)</th>
         </tr>
         <tr class="liste_titre">
             <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                 <th style="display:flex; flex-direction: row; gap: 10px; padding-bottom: 20px;">
                     <div>
                         Début &nbsp; <input name="date-debut" type="date" value="' . $date_debut . '" required>
                     </div>
                     <div>
                         Fin &nbsp; <input name="date-fin" type="date" value="' . $date_fin . '" required>
                     </div>
                     <div>
                         <button class="butAction">Valider</button>
                     </div>
                 </th>
             </form>
         </tr>
         <tr>
             <td>';

    $dolgraph4 = new DolGraph();
    $dolgraph4->SetData($VehiculesTransport);
    $dolgraph4->setShowLegend(0);
    $dolgraph4->setShowPercent(1);
    $dolgraph4->SetType(array('bars'));
    $dolgraph4->SetYLabel("Nombre de voyages");
    $dolgraph4->SetWidth('540');
    $dolgraph4->draw('jojo3');
    print $dolgraph4->show($total4 ? 0 : 1);
    print ' </td></tr></tbody></table></div></div>';


    // Stat frequence de chauffeur

    print '<div class="flex:1"><div class="div-table-responsive-no-min">
     <table class="noborder centpercent">
     <tbody> 
         <tr class="liste_titre">
             <th>Fréquence de service des chauffeurs &nbsp;&nbsp; (Par defaut durant les 5 derniers mois)</th>
         </tr>
         <tr class="liste_titre">
             <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                 <th style="display:flex; flex-direction: row; gap: 10px; padding-bottom: 20px;">
                    <div>
                        Début &nbsp; <input name="date-debut-chauffeur" type="date" value="' . $date_debut_chauffeur . '" required>
                    </div>
                    <div>
                        Fin &nbsp; <input name="date-fin-chauffeur" type="date" value="' . $date_fin_chauffeur . '" required>
                    </div>
                    <div>
                        <button class="butAction">Valider</button>
                    </div>
                 </th>
             </form>
         </tr>
         <tr>
             <td>';

    $dolgraph5 = new DolGraph();
    $dolgraph5->SetData($ChauffeurTransport);
    $dolgraph5->setShowLegend(0);
    $dolgraph5->setShowPercent(1);
    $dolgraph5->SetType(array('bars'));
    $dolgraph5->SetYLabel("Nombre de voyages");
    $dolgraph5->SetWidth('540');
    $dolgraph5->draw('jojo5');
    print $dolgraph5->show($total3 ? 0 : 1);
    print ' </td></tr></tbody></table></div></div>';

    // Couts operationnels

    print '<div class="flex:1"><div class="div-table-responsive-no-min">
       <table class="noborder centpercent">
       <tbody> 
           <tr class="liste_titre">
               <th>Couts opérationnels des dépenses des 5 derniers mois</th>
           </tr>
           <tr>
               <td>';

    $dolgraph3 = new DolGraph();
    $dolgraph3->SetData($CoutsOperationnels);
    $dolgraph3->setShowLegend(0);
    $dolgraph3->setShowPercent(1);
    $dolgraph3->SetType(array('linesnopoint'));
    $dolgraph3->SetWidth('540');
    $dolgraph3->SetYLabel("Montant (XAF)");
    $dolgraph3->draw('jojo4');
    print $dolgraph3->show($total5 ? 0 : 1);
    print ' </td></tr></tbody></table></div></div>';
    ?>
</div>

<style>
    .ponct-cont {
        width: 550px;
        height: 250px;
        border-radius: 10px;
        border: 1px solid black;
        display: flex;
        flex-direction: column;
        padding-left: 0px;
        position: relative;
    }

    .ponct-cont form {
        display: flex;
        flex-direction: row;
        column-gap: 5px;
        margin-top: 20px;
        transform: translateX(50px);
    }

    #nb {
        position: absolute;
        top: -90px;
        left: 30px;
        font-size: 140px;
        background-color: #F8F8F8;
        padding: 0;
        display: flex;
        flex-direction: row;
    }

    .ponct-cont span {
        font-size: 17px;
        font-weight: 500;
        transform: translateX(50px);
    }

    .ctn-bord {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        row-gap: 50px;
        column-gap: 50px;
    }

    .arret-cont {
        width: 550px;
        height: 250px;
        display: grid;
        grid-template-columns: 30% 70%;
        grid-template-rows: 250px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid black;
    }

    .arret-cont1 {
        display: flex;
        width: 100%;
        align-items: center;
        justify-items: center;
        padding: 0;
        margin: 0;
        background-color: rgba(0, 0, 255, 0.6);
    }

    .arret-cont1 img {
        margin: auto;
        height: auto;
    }

    .cont2 {
        padding: 10px;
        row-gap: 100px;
    }

    .cont2 h3 {
        margin: 0;
        padding: 0;
        font-weight: 600;
    }

    .arret-select-cont {
        margin-top: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 20px;
        margin-bottom: 25px;
    }

    .arret-select-cont div {
        display: flex;
        flex-direction: column;

    }

    .arret-select-cont div h4 {
        margin: 0;
        padding: 0;
        font-size: 16px;
    }
</style>

<script>
    function compteARebours() {
        let count = 0;
        const countdownElement = document.getElementById('countdown');

        const interval = setInterval(() => {
            countdownElement.textContent = count;
            count++;
            if (count > <?= $pourcent ?>) {
                clearInterval(interval);
            }
        }, 40);
    }

    compteARebours();
</script>

<?php

llxFooter();
$db->close();
