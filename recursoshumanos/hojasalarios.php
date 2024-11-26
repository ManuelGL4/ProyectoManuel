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

// Load translation files required by the page
$langs->loadLangs(array("recursoshumanos@recursoshumanos"));

$action = GETPOST('action', 'aZ09');


// Security check
// if (! $user->rights->recursoshumanos->myobject->read) {
// 	accessforbidden();
// }
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


if (isset($_POST['editar'])) {

    $id = $_GET['id'];

    $coste_empresa = $_POST['coste'];
    $salario_bruto = $_POST['salario_bruto'];
    $salario_liquido = $_POST['salario_liquido'];

    if ($coste_empresa == "") {
        $coste_empresa = "NULL";
    } else {
        $coste_empresa = str_replace(['.', ','], ['', '.'], $coste_empresa);
    }

    if ($salario_liquido == "") {
        $salario_liquido = "NULL";
    } else {
        $salario_liquido = str_replace(['.', ','], ['', '.'], $salario_liquido);
    }

    if ($salario_bruto == "") {
        $salario_bruto = "NULL";
    } else {
        $salario_bruto = str_replace(['.', ','], ['', '.'], $salario_bruto);
    }

    $sqlInsert = " UPDATE ".MAIN_DB_PREFIX."user_extrafields ue ";
    $sqlInsert.= " SET salario_liquido = ".$salario_liquido.", salario_bruto = ".$salario_bruto.", coste_empresa = ".$coste_empresa." ";
    $sqlInsert.= " WHERE fk_object = ".$id;

    $db->query($sqlInsert);

}


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

//Título de la página
llxHeader("", $langs->trans("Hoja de salarios"));

//Cabecera de la págína
print load_fiche_titre($langs->trans("Hoja de salarios"), '', 'object_informacion_formacion.png@recursoshumanos');

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
</style>';

//Si el usuario es admin
if ($user->admin) {

    //Consulta para sacar los datos de todos los usuarios
    $consulta = " SELECT u.rowid, u.firstname, u.lastname, ue.salario_liquido, ue.salario_bruto, ue.coste_empresa, ue.iban, ue.dni FROM ".MAIN_DB_PREFIX."user u ";
    $consulta.= " INNER JOIN ".MAIN_DB_PREFIX."user_extrafields ue ON ue.fk_object = u.rowid ";

    $resultConsulta = $db->query($consulta);

    print '<div class="fichecenter">';

    print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
    print '<span>Desde esta sección se podrán ver, editar y descargar en formato CSV (tabla) todos los datos referentes a los costes y nóminas de los empleados de la empresa</span>';
    print '</div>';

    print "    <form method='POST' action='generar2.php'>
        <div class='div-table-responsive'>
            <table class='tagtable liste'>
                <tbody>
                    <tr class='liste_titre'>
                        <th class='center liste_titre'>Empleado</th>
                        <th class='center liste_titre'>DNI</th>
                        <th class='center liste_titre'>IBAN</th>
                        <th class='center liste_titre'>Coste empresa</th>
                        <th class='center liste_titre'>Salario bruto</th>
                        <th class='center liste_titre'>Salario líquido</th>
                        <th class='center liste_titre'></th>
                    </tr>";

                    while ($usuario = $db->fetch_object($resultConsulta)) {

                        print "<tr class='oddeven'>
                                    <td class='center'>".$usuario->firstname." ".$usuario->lastname."</td>
                                    <td class='center'>".$usuario->dni."</td>
                                    <td class='center'>".$usuario->iban."</td>";
                                    
                                    //Si el coste de la empresa está vacío
                                    if ($usuario->coste_empresa == "") {
                                        print "<td class='center'></td>";
                                    } else {
                                        print "<td class='center'>".strtr(number_format($usuario->coste_empresa,2),['.' => ',', ',' => '.'])."</td>";
                                    }

                                    //Si el salario bruto está vacío
                                    if ($usuario->salario_bruto == "") {
                                        print "<td class='center'></td>";
                                    } else {
                                        print "<td class='center'>".strtr(number_format($usuario->salario_bruto,2),['.' => ',', ',' => '.'])."</td>";
                                    }

                                    //Si el salario líquido está vacío
                                    if ($usuario->salario_liquido == "") {
                                        print "<td class='center'></td>";
                                    } else {
                                        print "<td class='center'>".strtr(number_format($usuario->salario_liquido,2),['.' => ',', ',' => '.'])."</td>";
                                    }
                                    
                                    print "<td class='center'><a class='editfielda' href='".$_SERVER['PHP_SELF'] ."?action=editar&id=" . $usuario->rowid . "'>".img_edit()."</a></td>
                                </tr>";     
                    }
                    

                print "</tbody>
            </table>
        </div>
        
        <div class='tabsAction'>
        <td><input class='butAction' type='submit' value='Generar CSV' name='csv'></td>
        </div>

    </form>";

    print '<div class="fichetwothirdright"><div class="ficheaddleft">';

    print '</div></div></div>';

//Si el usuario no es admin
} else {

    //No se le permite acceder a esta sección
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

//Para editar los datos
if ($_GET["action"] == "editar") {

	$id = $_GET['id'];

    //Consulta para recoger los datos actuales
    $consulta = " SELECT u.rowid, u.firstname, u.lastname, ue.salario_liquido, ue.salario_bruto, ue.coste_empresa, ue.iban, ue.dni FROM ".MAIN_DB_PREFIX."user u ";
    $consulta.= " INNER JOIN ".MAIN_DB_PREFIX."user_extrafields ue ON ue.fk_object = u.rowid ";
    $consulta.= " WHERE u.rowid = ".$id;

    $resultConsulta = $db->query($consulta);

    $usuario = $db->fetch_object($resultConsulta);

    //Modal
	print '
	<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id='.$usuario->rowid.'" name="formfilter" autocomplete="off">
		<div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-dialog-buttons ui-draggable center-modal" aria-describedby="dialog-confirm" aria-labelledby="ui-id-1" style="height: auto; width: 500px; top: 35%; left: 35%; z-index: 101;">
			<div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle">
				<span id="ui-id-1" class="ui-dialog-title">Editar usuario</span>
				<button type="submit" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close">
					<span class="ui-button-icon ui-icon ui-icon-closethick"></span>
					<span class="ui-button-icon-space"> </span>
					Close
				</button>
			</div>
			<div id="dialog-confirm" style="width: auto; min-height: 0px; max-height: none; height: 220.928px;" class="ui-dialog-content ui-widget-content">
				<div class="confirmquestions">
				</div>
				<div class="">
					<table>
                        <tbody>
                            <tr>
                                <td>
                                    <span>'.$usuario->firstname.' '.$usuario->lastname.'</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span>'.$usuario->dni.'</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span>'.$usuario->iban.'</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="coste">Coste empresa</label>
                                </td>';

                                if ($usuario->coste_empresa == "") {
                                    print '<td>
                                    <input type="text" name="coste">
                                    </td>';
                                } else {
                                    print '<td>
                                    <input type="text" name="coste" value="'.strtr(number_format($usuario->coste_empresa,2),['.' => ',', ',' => '.']).'">
                                    </td>';
                                }


                            print '</tr>
                            <tr>
                                <td>
                                    <label for="salario_bruto">Salario bruto</label>
                                </td>';

                                if ($usuario->salario_bruto == "") {
                                    print '<td>
                                    <input type="text" name="salario_bruto">
                                    </td>';
                                } else {
                                    print '<td>
                                    <input type="text" name="salario_bruto" value="'.strtr(number_format($usuario->salario_bruto,2),['.' => ',', ',' => '.']).'">
                                    </td>';
                                }

                            print '</tr>
                            <tr>
                                <td>
                                    <label for="salario_liquido">Salario líquido</label>
                                </td>';

                                if ($usuario->salario_liquido == "") {
                                    print '<td>
                                    <input type="text" name="salario_liquido">
                                    </td>';
                                } else {
                                    print '<td>
                                    <input type="text" name="salario_liquido" value="'.strtr(number_format($usuario->salario_liquido,2),['.' => ',', ',' => '.']).'">
                                    </td>';
                                }

                            print '</tr>
                        </tbody>
					</table>
				</div>
			</div>
			<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
				<div class="ui-dialog-buttonset">
					<button type="submit" class="ui-button ui-corner-all ui-widget" name="editar">
						Guardar
					</button>
					<button type="submit" class="ui-button ui-corner-all ui-widget">
						Salir
					</button>
				</div>
			</div>
		</div>
	</form>';
}

// End of page
llxFooter();
$db->close();





