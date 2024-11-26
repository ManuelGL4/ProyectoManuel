<?php

// Load Dolibarr environment
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once('funciones_generar.php');

// Load translation files required by the page
$langs->loadLangs(array("recursoshumanos@recursoshumanos"));

$action = GETPOST('action', 'aZ09');

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




/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

$fecha_actual = date("Y-m-d");

//CONSEGUIR EL ARRAY DE COBRADORES

//RECORRER ARRAY DE COBRADORES
$cobradores = obtenerCobradores($db);

if (isset($_POST['csv'])) {

    $input = "DELTANET;Nominas";
    $input .= "\r\n";

    $input .= "\r\n";
    $input .= "\r\n";

    $input .= "Empleado;DNI;IBAN;CosteEmpresa;SalarioBruto;SalarioLiquido";

    for ($i = 0; $i < count($cobradores); $i++) {
        $input .= "\r\n";
        $nombreCobrador = $cobradores[$i]->__get('nombreApell');
        $nombreCobrador = str_replace(" ", "", $nombreCobrador);
        $nombreCobrador = str_replace(",", "-", $nombreCobrador);

        $dni = $cobradores[$i]->__get('dni');
        $iban = $cobradores[$i]->__get('iban');
        $coste_empresa = $cobradores[$i]->__get('coste_empresa');
        $salario_bruto = $cobradores[$i]->__get('salario_bruto');
        $salario_liquido = $cobradores[$i]->__get('salario_liquido');

        $input .= $nombreCobrador . ";" . $dni . ";" . $iban . ";" . $coste_empresa . ";" . $salario_bruto . ";" . $salario_liquido . "";
    }

    $filesize = strlen($input);
    $filename = "info_empleados.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Length: '.$filesize);
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo $input;

}

?>