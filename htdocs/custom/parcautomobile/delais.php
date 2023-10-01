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
require_once './lib/parcautomobile.lib.php';


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
    $permissiontoread = $user->rights->parcautomobile->notification->look;
} else {
    $permissiontoread = 1;
}

$upload_dir = $conf->parcautomobile->multidir_output[isset($object->entity) ? $object->entity : 1] . '/vehicule';

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

?>

<style>
    .notif-error {
        color: black;
        padding: 10px;
        font-size: 1.05rem;
        border-radius: 7px;
        background-color: rgba(255, 0, 0, 0.1);
        border: 1px solid rgba(255, 0, 0, 0.6);
        font-weight: 500;
    }

    .notif-btn {
        color: black;
        font-weight: bold;
        text-decoration: none;
        padding-left: 15px;
        border-left: 1px solid black;
    }

    .notif-success {
        color: black;
        background-color: rgba(0, 0, 255, 0.1);
        border: 1px solid rgba(0, 0, 255, 0.6);
        border-radius: 7px;

        padding: 10px;
        font-size: 1.05rem;
    }

    .custom-nav {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 5px;
        border-bottom: 1px solid black;
        list-style-type: none;
        padding-left: 3px;
    }

    .custom-nav li {
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        padding: 10px 15px;
        transition: all 0.5s;
    }

    .custom-nav li:hover {
        color: #263C5C;
    }

    .custom-nav-active {
        color: #263C5C;
        border-left: 1px solid black;
        border-right: 1px solid black;
        border-top: 3px solid #263C5C;
    }
</style>

<?php

/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Vehicule");
$help_url = '';
llxHeader('', 'Notification & Delais', $help_url);

function getDelais()
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "const WHERE name = 'TRANSIT_DELAIS_NOTIFICATION'";
    $resql = $db->query($sql);
    if ($resql->num_rows != 0) {
        $item = $db->fetch_object($resql);
        return (int) $item->value;
    } else {
        return 10;
    }
}


/* custom */

$dateOfToday = new DateTime();
$vehicule = new Vehicule($db);
$daylimit = getDelais();

if (isset($_GET['id_regler'])) {
    $sql5 = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_cartedisque SET status = 'not visible' WHERE rowid = " . $_GET['id_regler'];
    $resql5 = $db->query($sql5);
    echo "<script type='text/javascript'>document.location.replace('notificationdelais.php');</script>";
}

if (isset($_GET['idBook'])) {
    $sql = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_booking_conteneur SET isShow = 1 WHERE rowid = " . $_GET['idBook'];
    $resql = $db->query($sql);
    echo "<script type='text/javascript'>document.location.replace('notificationdelais.php');</script>";
}

if (isset($_GET['idLoc'])) {
    $sql = "UPDATE " . MAIN_DB_PREFIX . "parcautomobile_location SET isShow = 1 WHERE rowid = " . $_GET['idLoc'];
    $resql = $db->query($sql);
    echo "<script type='text/javascript'>document.location.replace('notificationdelais.php');</script>";
}


// Recuperer le ref du conteneur
function getRefConteneur($id)
{
    global $db;
    $sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_conteneur WHERE rowid = ' . $id;
    $resql = $db->query($sql);
    $item = $db->fetch_object($resql);
    return $item->ref;
}
function getRefBooking($id)
{
    global $db;
    $sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_booking WHERE rowid = ' . $id;
    $resql = $db->query($sql);
    $item = $db->fetch_object($resql);
    return $item->ref;
}
function getRefVehicule($id)
{
    global $db;
    $sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_vehicule WHERE rowid = ' . $id;
    $resql = $db->query($sql);
    $item = $db->fetch_object($resql);
    return $item->ref;
}

?>


<?php

// ********************** delais des booking

$booking = [];
$sql6 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_booking WHERE status = 2";
$resql6 = $db->query($sql6);
while ($item = $db->fetch_object($resql6)) {
    array_push($booking, $item);
}

$head = parcautomobileNotifPrepareHead();
print dol_get_fiche_head($head, 'delais', 'Configuration de Transit', -1, "notification@parcautomobile");

$k = 0;
while ($k < count($booking)) {
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_booking_conteneur WHERE isShow = 0 and booking = " . $booking[$k]->rowid;
    $resql = $db->query($sql);
    while ($item1 = $db->fetch_object($resql)) {
        $date_max_book = new DateTime($item1->date_echeance);
        $interval = $date_max_book->diff($dateOfToday);
        $day = $interval->format('%d');
        if ($date_max_book < $dateOfToday) {
            print '<br><span class="notif-error">Le delais du conteneur(<a href="conteneur_card.php?id=' . $item1->conteneur . '">' . getRefConteneur($item1->conteneur) . '</a>) du booking(' . getRefBooking($booking[$k]->rowid) . ') est passé &nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?idBook=' . $item1->rowid . '" class="notif-btn"><i class="fa fa-close"></i></a>&nbsp;&nbsp;&nbsp;<a target="_blank" href="booking_card.php?id=' . $booking[$k]->rowid . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        } elseif ($day < $daylimit) {
            print '<br><span class="notif-error"> Le conteneur(<a href="conteneur_card.php?id=' . $item1->conteneur . '">' . getRefConteneur($item1->conteneur) . '</a>) du booking(' . getRefBooking($booking[$k]->rowid) . ') doit bientot etre ramené &nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?idBook=' . $item1->rowid . '" class="notif-btn"><i class="fa fa-close"></i></a>&nbsp;&nbsp;&nbsp;<a target="_blank" href="booking_card.php?id=' . $booking[$k]->rowid . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        }
    }
    $k++;
}

// ********************** delais de location des vehicules

$sql7 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_location WHERE status = 1 and isShow = 0";
$resql7 = $db->query($sql7);
while ($item = $db->fetch_object($resql7)) {
    $date_max_location = new DateTime($item->date_echance);
    $interval = $date_max_location->diff($dateOfToday);
    $day = $interval->format('%d');
    if ($date_max_location < $dateOfToday) {
        print '<br><span class="notif-error">Le delais de location du vehicule (<a href="vehicule_card.php?id=' . $item->vehicule . '">' . getRefVehicule($item->vehicule) . '</a>) est passé &nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?idLoc=' . $item->rowid . '" class="notif-btn"><i class="fa fa-close"></i></a>&nbsp;&nbsp;<a target="_blank" href="location_card.php?id=' . $item->rowid . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
    } elseif ($day < $daylimit) {
        print '<br><span class="notif-error">Le vehicule (<a href="vehicule_card.php?id=' . $item->vehicule . '">' . getRefVehicule($item->vehicule) . '</a>) doit bientot etre ramené &nbsp;&nbsp;<a href="' . $_SERVER["PHP_SELF"] . '?idLoc=' . $item->rowid . '" class="notif-btn"><i class="fa fa-close"></i></a>&nbsp;&nbsp;<a target="_blank" href="location_card.php?id=' . $item->rowid . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
    }
}

/* carte grise */

$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_cartegrise";
$resql = $db->query($sql);
while ($cartegrise = $db->fetch_object($resql)) {
    $date_cartegrise = new DateTime($cartegrise->date_limite);
    $vehicule_info = $vehicule->fetchInfoVehicules($cartegrise->id_vehicule);
    $interval = $date_cartegrise->diff($dateOfToday);
    $day = $interval->format('%d');

    if ($date_cartegrise < $dateOfToday) {
        print '<br><span class="notif-error">La carte grise du ' . $vehicule_info->ref . ' est perimée &nbsp;&nbsp;<a href="./paper/carte_grise.php?id=' . $vehicule_info->rowid . '&veh_name=' . $vehicule_info->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
    } elseif ($day < $daylimit) {
        print '<br><span class="notif-error">La carte grise du ' . $vehicule_info->ref . ' va bientot perimer &nbsp;&nbsp;<a href="./paper/carte_grise.php?id=' . $vehicule_info->rowid . '&veh_name=' . $vehicule_info->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
    }
}

$All_vehicules = $vehicule->fetchAllVehicules();

/* assurance */

foreach ($All_vehicules as $veh) {
    $sql1 = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_assurance WHERE id_vehicule = ' . $veh->rowid . ' ORDER BY rowid DESC LIMIT 1';
    $resql1 = $db->query($sql1);
    if ($db->num_rows($resql1) != 0) {
        $item = $db->fetch_object($resql1);
        $date_max_ass = new DateTime($item->date_limite);
        $interval = $date_max_ass->diff($dateOfToday);
        $day = $interval->format('%d');

        if ($date_max_ass < $dateOfToday) {
            print '<br><span class="notif-error">L`assurance du ' . $veh->ref . ' est perimée &nbsp;&nbsp;<a href="./paper/assurance.php?id=' . $veh->rowid . '&veh_name=' . $veh->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        } elseif ($day < $daylimit) {
            print '<br><span class="notif-error">L`assurance du ' . $veh->ref . ' va bientot perimer &nbsp;&nbsp;<a href="./paper/assurance.php?id=' . $veh->rowid . '&veh_name=' . $veh->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        }
    }
}

/* visite technique */

foreach ($All_vehicules as $veh) {
    $sql2 = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_visitetechnique WHERE id_vehicule = ' . $veh->rowid . ' ORDER BY rowid DESC LIMIT 1';
    $resql2 = $db->query($sql2);
    if ($db->num_rows($resql2) != 0) {
        $item = $db->fetch_object($resql2);
        $date_max_visit = new DateTime($item->date_limite);
        $interval = $date_max_visit->diff($dateOfToday);
        $day = $interval->format('%d');

        if ($date_max_visit < $dateOfToday) {
            print '<br><span class="notif-error">La visite technique du ' . $veh->ref . ' est perimée &nbsp;&nbsp;<a href="./paper/visite_technique.php?id=' . $veh->rowid . '&veh_name=' . $veh->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        } elseif ($day < $daylimit) {
            print '<br><span class="notif-error">La visite technique du ' . $veh->ref . ' va bientot perimer &nbsp;&nbsp;<a href="./paper/visite_technique.php?id=' . $veh->rowid . '&veh_name=' . $veh->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        }
    }
}

/* vignette */

foreach ($All_vehicules as $veh) {
    $sql3 = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_vignette WHERE id_vehicule = ' . $veh->rowid . ' ORDER BY rowid DESC LIMIT 1';
    $resql3 = $db->query($sql3);
    if ($db->num_rows($resql3) != 0) {
        $date_max_vign = '2000';
        $item = $db->fetch_object($resql3);
        if ($item->year > $date_max_vign) {
            $date_max_vign = $item->year;
        }

        if ($date_max_vign < date('Y')) {
            print '<br><span class="notif-error">La vignette du ' . $veh->ref . ' est perimée &nbsp;&nbsp;<a href="./paper/vignette.php?id=' . $veh->rowid . '&veh_name=' . $veh->ref . '" class="notif-btn"><i class="fa fa-angle-right"></i></a></span><br><br>';
        }
    }
}

// End of page
llxFooter();
$db->close();
