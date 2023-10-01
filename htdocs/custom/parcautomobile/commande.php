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
 *	\file       parcautomobile/parcautomobileindex.php
 *	\ingroup    parcautomobile
 *	\brief      Home page of parcautomobile top menu
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
$langs->loadLangs(array("parcautomobile@parcautomobile"));

$action = GETPOST('action', 'aZ09');


// Security check
if (!$user->rights->parcautomobile->commande->read) {
    accessforbidden();
}
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
    $action = '';
    $socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */
$transport = [];
$commande_select = [];
$commande = [];

$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande";
$resql = $db->query($sql);
while ($item = $db->fetch_object($resql)) {
    array_push($commande_select, $item);
}

if (isset($_POST['id_commande'])) {
    $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status != 0 and commande = " . $_POST['id_commande'];
    $resql1 = $db->query($sql1);
    while ($item2 = $db->fetch_object($resql1)) {
        array_push($transport, $item2);
    }
}
if (isset($_GET['id_commande'])) {
    $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_transport WHERE status != 0 and commande = " . $_GET['id_commande'];
    $resql1 = $db->query($sql1);
    while ($item2 = $db->fetch_object($resql1)) {
        array_push($transport, $item2);
    }
}

if (isset($_POST['id_client'])) {
    $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "commande WHERE fk_soc = " . $_POST['id_client'];
    $resql1 = $db->query($sql1);
    if ($db->num_rows($resql1) > 0) {
        while ($item2 = $db->fetch_object($resql1)) {
            array_push($commande, $item2);
        }
    }
}
if (isset($_GET['ref'])) {
    $sql1 = "SELECT * FROM " . MAIN_DB_PREFIX . "commande WHERE fk_soc = " . $_GET['ref'];
    $resql1 = $db->query($sql1);
    if ($db->num_rows($resql1) > 0) {
        while ($item2 = $db->fetch_object($resql1)) {
            array_push($commande, $item2);
        }
    }
}

function getRefSoc($id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "societe WHERE rowid = " . $id;
    $resql = $db->query($sql);
    $societe = $db->fetch_object($resql);
    return $societe->nom;
}

function getRefArret($id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_arret WHERE rowid = " . $id;
    $resql = $db->query($sql);
    $item = $db->fetch_object($resql);
    return $item->ref;
}

/*
 * View
 */


function getTitleTransport($transport)
{
    global $db;
    $title = "";

    // Si le transport est conteneurisee
    if ($transport->type == 0 and $transport->conteneur != null) {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_conteneur WHERE rowid = " . $transport->conteneur;
        $resql = $db->query($sql);
        $item = $db->fetch_object($resql);
        $title .= "Numero conteneur : " . $item->ref . "\n";
    }

    // chauffeur
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_chauffeur WHERE rowid = " . $transport->chauffeur;
    $resql = $db->query($sql);
    $item = $db->fetch_object($resql);
    $title .= "Chauffeur : " . $item->ref . "\n";

    // vehicule
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "parcautomobile_vehicule WHERE rowid = " . $transport->vehicule;
    $resql = $db->query($sql);
    $item = $db->fetch_object($resql);
    $title .= "Vehicule : " . $item->ref;

    return $title;
}

llxHeader("", 'Commande');

print '	<div class="fichecenter"><br>';

//************************* Client


print '				<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
						<tbody>
							<tr class="titre">
								<td class="nobordernopadding valignmiddle col-title">
									<div class="titre inline-block"><img src="./img/object_commande.png" class="paddingright classfortooltip small"> COMMANDE DU CLIENT</div>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="noborder allwidth">
						<tbody>
                            <tr>
                                <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                                    <td>Selectionner le client</td>
                                    <td>';
if (isset($_POST['id_client'])) {
    print $form->select_company($_POST['id_client'], 'id_client', 's.client != 0');
} elseif (isset($_GET['ref'])) {
    print $form->select_company($_GET['ref'], 'id_client', 's.client != 0');
} else {
    print $form->select_company('', 'id_client', 's.client != 0');
}

print '                             </td>
                                    <td><button class="butAction">Afficher les commandes</button></td>
                                    <td></td>
                                </form>
                            </tr>';

if (empty($commande)) {
    print '			        <tr>
								<td class="impair" colspan="7"><span class="opacitymedium">Aucune commande n\'a été effectué par ce client</span></td>
							</tr>';
} else {
    $k = 0;
    print '			        <tr class="titre">
                                <td class="nobordernopadding valignmiddle col-title">
                                    <div class="titre inline-block">Référence commande</div>
                                </td>
                                <td class="nobordernopadding valignmiddle col-title">
                                    <div class="titre inline-block">Tiers</div>
                                </td>
                                <td class="nobordernopadding valignmiddle col-title">
                                    <div class="titre inline-block">Date de la commande</div>
                                </td>
                                <td class="nobordernopadding valignmiddle col-title">
                                    <div class="titre inline-block">Actions</div>
                                </td>
                            </tr>';
    while ($k < count($commande)) {
        $date1 = new DateTimeImmutable($commande[$k]->date_commande);
        print '
            <tr class="oddeven">
                <td class="nobordernopadding valignmiddle col-title">
                    <a href="../../commande/card.php?id=' . $commande[$k]->rowid . '"><img src="./img/object_commande.png" class="paddingright classfortooltip small">' . $commande[$k]->ref . '</a>
                </td>
                <td class="nobordernopadding valignmiddle col-title">
                    <a href="../../societe/card.php?id=' . $commande[$k]->fk_soc . '"><span class="fas fa-building paddingright classfortooltip" style=" color: #6c6aa8;"></span>' . getRefSoc(1) . '</a>
                </td>
                <td class="nobordernopadding valignmiddle col-title">
                    ' . date_format($date1, 'd M Y') . '
                </td>
                <td class="left">';
        if (isset($_GET['ref'])) {
            print '<a class="butAction small" href="' . $_SERVER['PHP_SELF'] . '?id_commande=' . $commande[$k]->rowid . '&ref=' . $_GET['ref'] . '">Afficher les transports</a>';
        } else {
            print '<a class="butAction small" href="' . $_SERVER['PHP_SELF'] . '?id_commande=' . $commande[$k]->rowid . '&ref=' . $_POST['id_client'] . '">Afficher les transports</a>';
        }
        print '</td>
            </tr>
        ';
        $k++;
    }
}

print '</tbody></table><br><br>';


//******************* Transport


print '				<table class="centpercent notopnoleftnoright table-fiche-title showlinkedobjectblock">
						<tbody>
							<tr class="titre">
								<td class="nobordernopadding valignmiddle col-title">
									<div class="titre inline-block"><img src="./img/object_transport.png" class="paddingright classfortooltip small"> TRANSPORT DE LA COMMANDE</div>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="noborder allwidth">
						<tbody>
                            <tr>
                                <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                                    <td>Selectionner la commande</td>
                                    <td>
                                        <select class="select" style="width:100%;" name="id_commande" required>';
$l = 0;
while ($l < count($commande_select)) {
    if (isset($_POST['id_commande'])) {
        if ($_POST['id_commande'] == $commande_select[$l]->rowid) {
            print '<option value="' . $commande_select[$l]->rowid . '" selected="selected">' . $commande_select[$l]->ref . '</option>';
        } else {
            print '<option value="' . $commande_select[$l]->rowid . '">' . $commande_select[$l]->ref . '</option>';
        }
    } else {
        print '<option value="' . $commande_select[$l]->rowid . '">' . $commande_select[$l]->ref . '</option>';
    }
    $l++;
}
print '                                 </select>
                                    </td>
                                    <td><button class="butAction">Afficher les transports</button></td></form>';
if (count($transport) > 3) {
    print '<td>';
    print '<button class="butAction" id="plus">VOIR PLUS</button>';
    print '<button class="butAction" id="moins" style="opacity:0; width: 0;">VOIR MOINS</button>';
    print '</td>';
}
print '                         
                            </tr></tbody></table>';

if (empty($transport)) {
    print '			        <tr>
								<td class="impair" colspan="7"><span class="opacitymedium">Aucun voyage effectué pour cette commande</span></td>
							</tr>';
} else {
    print '<div id="st-container" style="overflow: hidden; height: 410px;">';
    $i = 0;
    while ($i < count($transport)) {
        $class_veh = '';
        $title = getTitleTransport($transport[$i]);
        $class_cont = 'end';
        $date2 = new DateTimeImmutable($transport[$i]->date_depart);
        $date3 = new DateTimeImmutable($transport[$i]->date_arrive);
        if ($transport[$i]->status == 2) {
            $class_veh = 'roule';
            $class_cont = 'center';
        }
        print '	<a href="./transport_card.php?id=' . $transport[$i]->rowid . '" class="st-row oddeven" title="' . $title . '">
                    <div  class="position-container">
                        <img src="./img/object_map_marker.png"  class="map-marker"/>
                        <div>
                            ' . getRefArret($transport[$i]->lieu_depart) . ' <br> ' . date_format($date2, 'd M Y') . ' à ' . date_format($date2, 'H:s') . '
                        </div>
                    </div>
                    <div class="voyage-container">
                        <div class="voiture-container" style="justify-content:' . $class_cont . ';">';
        if ($transport[$i]->type == 0) {
            print '         <img src="./img/object_camion_conteneurise.png" class="' . $class_veh . '"/>';
        } else {
            print '         <img src="./img/object_camion_conventionnel.png" class="' . $class_veh . '"/>';
        }

        print '			</div>
						<div class="route"></div>
					</div>
                    <div class="position-container">
                        <img src="./img/object_map_marker.png"  class="map-marker"/>
                        <div>
                        ' . getRefArret($transport[$i]->lieu_arrive) . ' <br> ' . date_format($date3, 'd M Y') . ' à ' . date_format($date3, 'H:s') . '
                        </div>
                    </div>
				</a>';
        $i++;
    }
    print '			</div>';
}


print '	    </tbody>
		</table>';

print '</div><div class="fichetwothirdright">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;



print '</div></div>';

// End of page
llxFooter();
$db->close();

?>

<style>
    .position-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        gap: 10px;
        width: 20%;
    }

    .position-container img {
        width: 30px;
        height: 45px;
    }

    .position-container div {
        text-align: center;
        font-size: 15px;
        font-weight: 600;
        /* font-weight: 500; */
        color: #263C5C;
    }

    .st-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
    }

    .voyage-container {
        width: 60%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .voiture-container {
        height: 80%;
        width: 100%;
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .voiture-container img {
        height: 100px;
        width: auto;

    }

    .route {
        height: 3px;
        border-radius: 5px;
        width: 100%;
        background-color: black;
        transform: translateY(-3px);
    }

    .roule {
        animation-name: roule;
        animation-iteration-count: infinite;
        animation-duration: 0.5s;
    }

    @keyframes roule {
        0% {
            transform: translateX(0px);
        }

        50% {
            transform: translateX(5px);
        }
    }
</style>

<script>
    const butPlus = document.getElementById("plus");
    const butMoins = document.getElementById("moins");
    const container = document.getElementById("st-container");

    butMoins.addEventListener("click", () => {
        butMoins.style.width = "0";
        butMoins.style.opacity = "0";
        butPlus.style.width = "100px";
        butPlus.style.opacity = "1";
        container.style.height = "410px";
    })
    butPlus.addEventListener("click", () => {
        butPlus.style.width = "0";
        butPlus.style.opacity = "0";
        butMoins.style.width = "100px";
        butMoins.style.opacity = "1";
        container.style.height = "fit-content";
    })
</script>