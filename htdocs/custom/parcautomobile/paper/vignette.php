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
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
    $permissiontoread = $user->rights->parcautomobile->vehicule->read;
    $permissiontoadd = $user->rights->parcautomobile->vehicule->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
    $permissiontodelete = $user->rights->parcautomobile->vehicule->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
    $permissionnote = $user->rights->parcautomobile->vehicule->write; // Used by the include of actions_setnotes.inc.php
    $permissiondellink = $user->rights->parcautomobile->vehicule->write; // Used by the include of actions_dellink.inc.php
} else {
    $permissiontoread = 1;
    $permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
    $permissiontodelete = 1;
    $permissionnote = 1;
    $permissiondellink = 1;
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

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Vehicule");
$help_url = '';
llxHeader('', $title, $help_url);

$vehicule = new Vehicule($db);
$vehicule_id = $_GET['id'];
$vehicule_info = $vehicule->fetchInfoVehicules($vehicule_id);
$dateOfToday = new DateTime();

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'add_vignette') {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "parcautomobile_vignette (id_vehicule, ref, company, year) VALUES ('" . $vehicule_id . "','" . $_POST['libelle'] . "','" . $_POST['company'] . "','" . $_POST['year'] . "')";
        $resql = $db->query($sql);
        echo "<script type='text/javascript'>document.location.replace('" . $_SERVER['PHP_SELF'] . "?id=$vehicule_id');</script>";
    }
}

$head = vehiculePrepareHead($object);
print dol_get_fiche_head($head, 'paper', $langs->trans("Vehicule"), -1, $object->picto);

$head = PaperVehiculePrepareHead($object);
print dol_get_fiche_head($head, 'vignette', $langs->trans("Vehicule"), -1, dol_buildpath('/parcautomobile/img/object_paper.png', 1), 1);
?>

<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
    <tbody>
        <tr class="titre">
            <td class="nobordernopadding valignmiddle col-title">
                <div class="titre inline-block">Liste des vignettes</div>
            </td>
        </tr>
    </tbody>
</table>
<?php
$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'parcautomobile_vignette WHERE id_vehicule = ' . $vehicule_id;
$resql = $db->query($sql);
if ($db->num_rows($resql) == 0) {
    print ' <p>Ce vehicule ne possede pas de vignette</p><br>';
    print ' <form action="' . $_SERVER['PHP_SELF'] . '?id=' . $vehicule_id . '&action=add_vignette" method="POST" class="st-form">
                <table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
                    <tbody>
                        <tr class="titre">
                            <td class="nobordernopadding valignmiddle col-title">
                                <div class="titre inline-block">Acheter une Vignette</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="border centpercent tableforfield">
                    <tbody>
                        <tr>
                            <td class="titlefield">Libelle</td>
                            <td class="valuefield"><input type="text" name="libelle" id="libelle" required></td>
                        </tr>
                        <tr>
                            <td class="titlefield">Entreprise souscrite</td>
                            <td class="valuefield"><input type="text" name="company" id="company" required></td>
                        </tr>
                        <tr>
                            <td class="titlefield">Année de validité</td>
                            <td class="valuefield">
                                <select for="year" name="year" required>
                                    <option value="2015">2015</option>
                                    <option value="2016">2016</option>
                                    <option value="2017">2017</option>
                                    <option value="2018">2018</option>
                                    <option value="2019">2019</option>
                                    <option value="2020">2020</option>
                                    <option value="2021">2021</option>
                                    <option value="2022">2022</option>
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                <select>
                            </td>
                        </tr>
                    </tbody>
                </table><br>
                <button type="submit" class="butAction">Souscrire</button>
            </form>';
} else {
    print ' <table class="noborder allwidth" data-block="showLinkedObject" data-element="booking" data-elementid="1">
                <tbody>
                    <tr class="liste_titre">
                        <td class="left">Réference</td>
                        <td class="left">Entreprise</td>
                        <td class="left">Année de validité</td>
                    </tr>';
    $date_max_vign = '2000';
    while ($item = $db->fetch_object($resql)) {
        if ($item->year > $date_max_vign) {
            $date_max_vign = $item->year;
        }
        print '     <tr class="oddeven">
                        <td class="left">' . $item->ref . '</td>
                        <td class="left">' . $item->company . '</td>
                        <td class="left">' . $item->year . '</td>
                    </tr>';
    }
    print  '    </tbody>
            </table>';
    if ($date_max_vign < date('Y')) {
        print ' <br><span class="notif-error">Ce vehicule a besoin une nouvelle vignette !!</span>';
        print ' <form action="' . $_SERVER['PHP_SELF'] . '?id=' . $vehicule_id . '&action=add_vignette" method="POST" class="st-form">
                    <table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
                        <tbody>
                            <tr class="titre">
                                <td class="nobordernopadding valignmiddle col-title">
                                    <div class="titre inline-block">Acheter une Vignette</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="border centpercent tableforfield">
                        <tbody>
                            <tr>
                                <td class="titlefield">Libelle</td>
                                <td class="valuefield"><input type="text" name="libelle" id="libelle" required></td>
                            </tr>
                            <tr>
                                <td class="titlefield">Entreprise souscrite</td>
                                <td class="valuefield"><input type="text" name="company" id="company" required></td>
                            </tr>
                            <tr>
                                <td class="titlefield">Année de validité</td>
                                <td class="valuefield">
                                    <select for="year" name="year" required>
                                        <option value="2015">2015</option>
                                        <option value="2016">2016</option>
                                        <option value="2017">2017</option>
                                        <option value="2018">2018</option>
                                        <option value="2019">2019</option>
                                        <option value="2020">2020</option>
                                        <option value="2021">2021</option>
                                        <option value="2022">2022</option>
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                    <select>
                                </td>
                            </tr>
                        </tbody>
                    </table><br>
                    <button type="submit" class="butAction">Souscrire</button>
                </form>';
    }
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
</style>

<?php
// End of page
llxFooter();
$db->close();
