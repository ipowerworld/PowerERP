
<?php




// Load powererp environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
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




include_once('class/mouvement_pour_attribution.class.php');

$mouvementattribution=new Mouvement_pour_attribution($db);

var_dump($_POST);

if (isset($_POST['addMvt'])) {
	$mouvementattribution->addMouv($_POST);
}

if (isset($_GET['deleteid']) && $_GET['confirm'] == "yes") {
	$mouvementattribution->deleteMouvementLine($_GET['deleteid']);
}
if (isset($_GET['action'])) {
	if ($_GET['action'] == "confirm_validate_object" && $_GET['confirm'] == "yes") {
		$mouvementattribution->validate($user, 0);
	}
}

if (isset($_POST['saveproductline'])) {
	$mouvementattribution->updateMouv($_POST);
} elseif (isset($_POST['canceleditline'])) {
	echo "<script type='text/javascript'>document.location.replace('" . $_SERVER["PHP_SELF"] . "?id=" . $object->id . "');</script>";
}
