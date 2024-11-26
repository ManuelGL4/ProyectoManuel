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

//Título de la página
llxHeader("", $langs->trans("Envío de nóminas al banco"));

//Cabecera de la página
print load_fiche_titre($langs->trans("Envío de nóminas al banco"), '', 'object_informacion_formacion.png@recursoshumanos');

//Estilos
print '<style>
    .contenedor {
        display: flex;
        justify-content: center;
    }
    .titulo {
        font-size: 80px;
    }
    .contenedor2 {
        display: flex;
        justify-content: center;
    }
    .titulo2 {
        margin-top: 50px;
        font-size: 30px;
    }
    .contenedor3 {
        display: flex;
        justify-content: center;
    }
    .img {
        margin-top: 30px;
        width: 400px;
        height: auto;
    }
    @media screen and (max-width: 400px) {
        .img {
            width: 250px;
            height: auto;
        }
	}
    .nueva {
		display: flex;
		justify-content: center;
	}
    .banco {
		margin-top: 50px;
		width: 700px;
        height: 550px;
	}
	@media screen and (max-width: 800px) {
		.banco {
			width: 500px;
            height: 350px;
		}
	}
	@media screen and (max-width: 500px) {
		.banco {
			width: 300px;
            height: 250px;
		}
	}
</style>';

//Si el usuario es admin
if ($user->admin) {

	print '<div class="fichecenter"><div class="fichethirdleft">';

	print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
	print '<span>Desde esta sección se podrán generar los archivos XML y CSV correspondientes a la información relativa a las nóminas de los trabajadores</span>';
	print '</div>';
	
	print '    <form method="POST" action="generar.php">
	<table>
		<tr>
			<th>FORMATO XML</th>
			<th>FORMATO CSV</th>
		</tr>
		<tr>
			<td><input class="butAction" type="submit" value="Generar XML" name="xml"></td>
			<td><input class="butAction" type="submit" value="Generar CSV" name="csv"></td>
		</tr>
	</table>
	</form>';
	
	print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';
	
	$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
	$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
	
	print '</div></div></div>';

    print '<div class="fichecenter nueva">';
    print '<img src="img/bank.jpg" class="banco">';
    print '</div>';

//Si el usuario no es admin
} else {

    //No se le permite el acceso a esta página
    print '<div class="fichecenter">';

    print '<div class="contenedor">';
    print '<span class="titulo">¡Lo sentimos, '.$user->firstname.'!</span>';
    print '</div>';

    print '<div class="contenedor2">';
    print '<span class="titulo2">Solo los administradores tienen acceso a esta sección</span>';
    print '</div>';

    print '<div class="contenedor2">';
    print '<img src="img/nopermitido.jpg" class="img">';
    print '</div>';

    print '</div>';

}


// End of page
llxFooter();
$db->close();
















