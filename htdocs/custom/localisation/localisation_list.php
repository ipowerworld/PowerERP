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
 *   	\file       entrepot_card.php
 *		\ingroup    localisation
 *		\brief      Page to create/edit/view entrepot
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
dol_include_once('/localisation/class/entrepot.class.php');
dol_include_once('/localisation/lib/localisation_entrepot.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("localisation@localisation", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'entrepotcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Entrepot($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->localisation->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('entrepotcard', 'globalcard')); // Note that conf->hooks_modules contains array

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
    $permissiontoread = $user->rights->localisation->entrepot->read;
    $permissiontoadd = $user->rights->localisation->entrepot->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
    $permissiontodelete = $user->rights->localisation->entrepot->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
    $permissionnote = $user->rights->localisation->entrepot->write; // Used by the include of actions_setnotes.inc.php
    $permissiondellink = $user->rights->localisation->entrepot->write; // Used by the include of actions_dellink.inc.php
} else {
    $permissiontoread = 1;
    $permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
    $permissiontodelete = 1;
    $permissionnote = 1;
    $permissiondellink = 1;
}

$upload_dir = $conf->localisation->multidir_output[isset($object->entity) ? $object->entity : 1] . '/entrepot';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->localisation->enabled)) accessforbidden();
if (!$user->rights->localisation->entrepot->localiser) accessforbidden();


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

    $backurlforlist = dol_buildpath('/localisation/entrepot_list.php', 1);

    if (empty($backtopage) || ($cancel && empty($id))) {
        if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
            if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
                $backtopage = $backurlforlist;
            } else {
                $backtopage = dol_buildpath('/localisation/entrepot_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
            }
        }
    }

    $triggermodname = 'LOCALISATION_ENTREPOT_MODIFY'; // Name of trigger action code to execute when we modify record

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
    $triggersendname = 'LOCALISATION_ENTREPOT_SENTBYMAIL';
    $autocopy = 'MAIN_MAIL_AUTOCOPY_ENTREPOT_TO';
    $trackid = 'entrepot' . $object->id;
    include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("titleLocalisation");
$help_url = '';
llxHeader('', $title, $help_url);

/************************/

$Entre = new Entrepot($db);
$idE = $_GET['idE'];

$single_entrepot = $Entre->fetchEntrepot($idE);
$sous_entrepot = $Entre->fetchEntrepot($idE, true);
$sous_produit = $Entre->fetchProduct($idE, $sous_entrepot);
$str = '';

if ($single_entrepot->entrepot == null) {
    $str = $langs->trans('ContainerMere');
}

print load_fiche_titre($langs->trans("LocaliserContainer") . ' ' . $single_entrepot->ref . ' ' . $str, '', 'object_entrepot@localisation');
?>

<hr>
<br>

<div class="All-container">
    <div class="div1">
        <div class="titre inline-block">Liste des sous container</div>
        <br>
        <div class="info-container">
            <table class="noborder allwidth">
                <tbody>
                    <tr class="liste-titre">
                        <td class="left">Container</td>
                        <td class="center">Actions</td>
                    </tr>
                    <?php
                    $num = count($sous_entrepot);
                    $i = 0;
                    if ($num == 0) {
                        print '<tr><td colspan="2">Aucun sous container</td></tr>';
                    } else {
                        while ($i < $num) {
                            print '<tr class="oddeven">';
                            print '     <td class="left"><a href="./entrepot_card.php?id=' . $sous_entrepot[$i]->rowid . '"><img src="./img/object_entrepot.png" class="paddingright classfortooltip">' . $sous_entrepot[$i]->ref . '<a/></td>';
                            print '     <td class="center small">' . dolGetButtonAction($langs->trans('butLocalised'), '', 'default', './localisation_list.php?idE=' . $sous_entrepot[$i]->rowid, '', $user->rights->localisation->entrepot->localiser) . '</td>';
                            print '</tr>';

                            $i++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="div2">
        <div class="titre inline-block">Liste des sous produits</div>
        <br>
        <div class="info-container">
            <table class="noborder allwidth">
                <tbody>
                    <tr class="liste-titre">
                        <td class="left">Référence</td>
                        <td class="center">Numéro de lot/serie</td>
                        <td class="center">Qauntité</td>
                    </tr>
                    <?php
                    $num = count($sous_produit);
                    $i = 0;
                    if ($num == 0) {
                        print '<tr><td colspan="3">Aucun produit dans ce container</td></tr>';
                    } else {
                        while ($i < $num) {
                            print '<tr class="oddeven">';
                            print '     <td class="left"><a href="../../product/card.php?id=' . $sous_produit[$i]->rowid . '"><span class="fas fa-cube valignmiddle pictotitle widthpictotitle" style="color: #a69944;"></span>' . $sous_produit[$i]->ref . '</a></td>';
                            print '     <td class="center">' . $sous_produit[$i]->batch . '</td>';
                            print '     <td class="center">' . $sous_produit[$i]->qty . '</td>';
                            print '</tr>';
                            $i++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="div3">
        <div class="titre inline-block">Arborescence de <?php print $single_entrepot->ref; ?></div>
        <hr>
        <div class="div4">
            <tbody>
                <tr>
                    <div class="tree-entrepot">
                        <img src="./img/object_entrepot.png" class="paddingright classfortooltip">
                        <?php print $single_entrepot->ref; ?>
                        &nbsp;&nbsp;<button class="btn open1" title="derouler"><i class="fa fa-angle-right"></i></button>
                    </div>
                    <?php
                    print $Entre->fetchArborescence($idE, 50);
                    ?>
                </tr>
            </tbody>
        </div>
    </div>
</div>

<style>
    button {
        background-color: rgba(0, 0, 0, 0);
        color: black;
        border: 0;
        transform: scale(1.4);
        cursor: pointer;
    }

    .tree-entrepot {
        background-color: #fff;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        column-gap: 5px;
        padding: 5px 15px;
        text-align: center;
        border-radius: 5px;
        width: fit-content;
        color: black;
        margin-bottom: 10px;
        border-left: 6px #263C5C solid;
        border-top: 1px #263C5C solid;
        border-right: 1px #263C5C solid;
        border-bottom: 1px #263C5C solid;
    }

    .tree-product {
        background-color: #E6EDF4;
        border: 2px black solid;
        padding: 5px 15px;
        text-align: center;
        border-radius: 10px;
        width: fit-content;
        margin-bottom: 10px;
    }

    .All-container {
        display: flex;
        flex-direction: row;
        row-gap: 50px;
        column-gap: 60px;
        flex-wrap: wrap;
    }

    .div1 {
        display: flex;
        flex-direction: column;
        width: 40%;
    }

    .div2 {
        display: flex;
        flex-direction: column;
        width: 50%;
    }

    .div3 {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .div4 {
        /* background-color: rgba(0, 0, 0, 0.1); */
        border-top: 1px solid #D7D7D7;
        border-bottom: 1px solid #D7D7D7;
        padding: 20px 0;
    }

    .info-container {
        height: 200px;
        overflow-y: auto;
    }

    .info-field {
        margin-bottom: 5px;
    }
</style>

<script>
    <?php

    for ($i = 1; $i <= $Entre->nb_iter; $i++) {
        print '
            const btn' . $i . ' = document.querySelector(".open' . $i . '");
            const div' . $i . ' = document.querySelector(".div-container' . $i . '");
        
            btn' . $i . '.addEventListener("click", () => {
                if ((div' . $i . '.style.display == "block") || (div' . $i . '.style.display == "")) {
                    div' . $i . '.style.display = "none";
                    btn' . $i . '.style.rotate = "90deg";
                } else {
                    div' . $i . '.style.display = "block";
                    btn' . $i . '.style.rotate = "0deg";
                }
            });
        ';
    }

    ?>
</script>