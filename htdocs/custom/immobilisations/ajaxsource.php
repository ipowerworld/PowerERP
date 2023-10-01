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

$action = $_POST['action'];

if ($action == "get_product_fournisseur") {
    $out = '';

    $sql  = 'SELECT DISTINCT cfd.fk_product, cfd.batch, p.ref, p.label FROM ' . MAIN_DB_PREFIX . 'commande_fournisseur_dispatch as cfd';
    $sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'product as p ON p.rowid = cfd.fk_product';
    $sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'product_extrafields as pe ON pe.fk_object = cfd.fk_product';
    $sql .= ' WHERE pe.immobilisations_immobilisable = 1 AND cfd.fk_commande = ' . $_POST['fk_commande'] . ' AND cfd.batch != ""';

    $resql = $db->query($sql);
    if ($db->num_rows($resql) > 0) {
        while ($product = $db->fetch_object($resql)) {
            $out .= '<option value="' . $product->batch . '">' . $product->ref . ' - ' . $product->label . ' - ' . $product->batch . '</option>';
        }
    } else {
        $out .= '<option value="">Aucun produit immobilisable pour cette commande</option>';
    }

    echo $out;
}
