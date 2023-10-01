<?php

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
require_once DOL_DOCUMENT_ROOT . '/product/class/productbatch.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

dol_include_once('/mouvements/class/mouvements.class.php');
dol_include_once('/mouvements/lib/mouvements_mouvements.lib.php');

$action = $_POST['action'];

if ($action == "get_batch_product") {
    $id = $_POST['id'];

    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande_fournisseur_dispatch WHERE fk_product=" . $id . " AND batch <>" . "''";

    $resql = $db->query($sql);

    $out = '';

    if ($resql) {
        $num = $db->num_rows($resql);
        $i = 0;
        $result = [];
        while ($i < $num) {
            $obj = $db->fetch_object($resql);
            $result[$i] = (array)$obj;
            $out .= '<option value=' . $result[$i]['batch'] . '>' . $result[$i]['batch'] . '</option>';
            $i++;
        }
    }

    echo $out;
}

if ($action == "get_product_entrepot") {
    $entrepot = $_POST['entrepot'];
    $tupleArray = [];
    $out = '<option></option>';

    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande_fournisseur_dispatch_extrafields WHERE a1234 = " . $entrepot;
    $resql = $db->query($sql);
    while ($item = $db->fetch_object($resql)) {
        array_push($tupleArray, $item);
    }
    if (count($tupleArray) > 0) {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande_fournisseur_dispatch WHERE rowid = " . $tupleArray[0]->fk_object . " AND qty > 0";
        $i = 1;
        while ($i < count($tupleArray)) {
            $sql .= " OR rowid =  " . $tupleArray[$i]->fk_object;
            $i++;
        }
        $sql .= " GROUP BY fk_product";
        $resql = $db->query($sql);
        while ($item = $db->fetch_object($resql)) {
            $out .= '<option value=' . $item->fk_product . '>' . getOneProduct($item->fk_product)->label . '</option>';
        }
        echo $out;
    }
}

if ($action == "get_lot_product_entrepot") {
    $entrepot = $_POST['entrepot'];
    $product = $_POST['product'];

    $tupleArray = [];
    $out = '<option></option>';

    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande_fournisseur_dispatch_extrafields WHERE a1234 = " . $entrepot;
    $resql = $db->query($sql);
    while ($item = $db->fetch_object($resql)) {
        array_push($tupleArray, $item);
    }
    if (count($tupleArray) > 0) {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "commande_fournisseur_dispatch WHERE rowid = " . $tupleArray[0]->fk_object . " AND qty > 0 AND fk_product = " . $product;
        $i = 1;
        while ($i < count($tupleArray)) {
            $sql .= " OR rowid =  " . $tupleArray[$i]->fk_object;
            $i++;
        }
        $sql .= " GROUP BY batch";
        $resql = $db->query($sql);
        while ($item = $db->fetch_object($resql)) {
            $out .= '<option value=' . $item->batch . '>' . $item->batch . '</option>';
        }
        echo $out;
    }
}

function getOneProduct($id)
{
    global $db;
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "product WHERE rowid=" . $id;
    $resql = $db->query($sql);
    $num = $db->num_rows($resql);
    $i = 0;
    $result = [];
    while ($i < $num) {
        $obj = $db->fetch_object($resql);
        $result[$i] = $obj;
        $i++;
    }

    return $result[0];
}
