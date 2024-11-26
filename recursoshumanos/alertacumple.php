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
llxHeader("", $langs->trans("Alerta de cumpleaños"));

//Estilos
print '<style>
	.contenedor {
		display: flex;
		flex-wrap: wrap;
        justify-content: center;
        align-items: center;
		gap: 4%;
		margin-bottom: -50px
  	}
	.carta1 {
		flex: 0 0 25%;
		box-sizing: border-box;
		box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
		border-radius: 10px;
		padding: 20px;
		display: flex;
		flex-direction: column;
		margin-bottom: 4%;
        justify-content: center;
        align-items: center;
	}
    .carta2 {
		flex: 0 0 25%;
		box-sizing: border-box;
		box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
		border-radius: 10px;
		padding: 20px;
		display: flex;
		flex-direction: column;
		margin-bottom: 4%;
        margin-bottom: 10%;
        justify-content: center;
        align-items: center;
	}
    .carta3 {
		flex: 0 0 25%;
		box-sizing: border-box;
		box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
		border-radius: 10px;
		padding: 20px;
		display: flex;
		flex-direction: column;
		margin-bottom: 4%;
        justify-content: center;
        align-items: center;
	}
	.imagen {
		width: 300px;
		height: 230px;
		border-radius: 40%;
		margin-bottom: 3%;
	}
	.titulo {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #FFEEC5;
		margin-bottom: 3%;
	}
    .titulo1 {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #DC8B5C;
		margin-bottom: 3%;
	}
    .titulo2 {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #81DC5C;
		margin-bottom: 3%;
	}
    .titulo3 {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #5CBFDC;
		margin-bottom: 3%;
	}
	.vermas {
		text-align: left !important;
	}
	.descripcion {
		display: -webkit-box;
		-webkit-line-clamp: 3;
		-webkit-box-orient: vertical;
		overflow: hidden;
		height: 60px;
		max-width: 350px;
		margin-bottom: 3%;
		text-align: left !important;
	}
	@media screen and (max-width: 1580px) {
		.contenedor {
			display: flex;
			flex-wrap: wrap;
			gap: 1%;
		}
	}
    @media screen and (max-width: 1261px) {
        .carta2 {
            flex: 0 0 25%;
            box-sizing: border-box;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            margin-bottom: 4%;
            justify-content: center;
            align-items: center;
        }
	}
	.pictodelete {
		margin-left: 69%;
	}
	.center-modal {
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
	.aviso {
		font-style: italic;
		color: grey;
	}
	.div-table-responsive {
		margin-top: 40px;
	}
	.agenda {
		margin-top: 40px;
	}
</style>';

//Cabecera de la página
print load_fiche_titre($langs->trans("Alerta de cumpleaños"), '', 'object_informacion_formacion.png@recursoshumanos');

//Consulta para el cumpleaños de hoy
$sqlHoy = " SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
$sqlHoy.= " WHERE MONTH(birth) = MONTH(CURDATE()) AND DAY(birth) = DAY(CURDATE()) ";

$resultHoy = $db->query($sqlHoy);
$numHoy = $db->num_rows($resultHoy);
$hoy = $db->fetch_object($resultHoy);

//Consulta para el último cumpleaños
$sqlUltimo = " SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
$sqlUltimo.= " WHERE DAYOFYEAR(birth) < DAYOFYEAR(CURDATE()) OR (DAYOFYEAR(birth) = DAYOFYEAR(CURDATE()) AND birth < CURDATE()) ";
$sqlUltimo.= " ORDER BY DAYOFYEAR(birth) DESC, birth DESC ";
$sqlUltimo.= " LIMIT 1";

$resultUltimo = $db->query($sqlUltimo);
$ultimo = $db->fetch_object($resultUltimo);

//Consulta para el próximo cumpleaños
$sqlProximo = " SELECT * FROM ".MAIN_DB_PREFIX."user ";
$sqlProximo.= " WHERE DATE_ADD(birth, INTERVAL YEAR(CURDATE())-YEAR(birth) ";
$sqlProximo.= " + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birth),1,0) YEAR) = (SELECT MIN(DATE_ADD(birth, INTERVAL YEAR(CURDATE())-YEAR(birth) ";
$sqlProximo.= " + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birth),1,0) YEAR)) FROM ".MAIN_DB_PREFIX."user ";
$sqlProximo.= " WHERE DATE_ADD(birth, INTERVAL YEAR(CURDATE())-YEAR(birth) + IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(birth),1,0) YEAR) > CURDATE()) ";

$resultProximo = $db->query($sqlProximo);
$proximo = $db->fetch_object($resultProximo);

print '<div class="fichecenter">';

print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
print '<span>Desde esta sección podrás estar al día de los cumpleaños de tus compañeros, para que no se te olvide felicitarles</span>';
print '</div>';

print '<div class="contenedor">';

//Últimos cumpleaños
print '<div class="carta1">';
print '<div class="titulo1">Último Cumpleaños:</div>';
print '<img src="img/user_man.png" class="imagen" alt="Imagen no disponible">';
print '<div class="titulo">'.$ultimo->firstname.' '.$ultimo->lastname.'</div>';
print '<div class="descripcion">¡Espero que le hayas felicitado!</div>';
print '<div><a class="fas fa-arrow-right" href="../../user/card.php?id='.$ultimo->rowid.'" target="_blank" class="vermas"> Ir a la ficha del usuario</a></div>';
print '</div>';

//Cumpleaños actual
print '<div class="carta2">';
print '<div class="titulo2">Hoy cumple años:</div>';
print '<img src="img/user_man.png" class="imagen" alt="Imagen no disponible">';

if ($numHoy > 0) {
    print '<div class="titulo">'.$hoy->firstname.' '.$hoy->lastname.'</div>';
    print '<div class="descripcion">¿A qué esperas? ¡Felicítale!</div>';
} else {
    print '<div class="titulo">Nadie</div>';
    print '<div class="descripcion">Puedes estar tranquilo</div>';
}

if ($numHoy > 0) {
    print '<div><a class="fas fa-arrow-right" href="../../user/card.php?id='.$hoy->rowid.'" target="_blank" class="vermas"> Ir a la ficha del usuario</a></a></div>';
}

print '</div>';

//Próximo cumpleaños
print '<div class="carta3">';
print '<div class="titulo3">Próximo Cumpleaños:</div>';
print '<img src="img/user_man.png" class="imagen" alt="Imagen no disponible">';
print '<div class="titulo">'.$proximo->firstname.' '.$proximo->lastname.'</div>';
print '<div class="descripcion">¡Ya queda poco!</div>';
print '<div><a class="fas fa-arrow-right" href="../../user/card.php?id='.$proximo->rowid.'" target="_blank" class="vermas"> Ir a la ficha del usuario</a></div>';
print '</div>';

print '</div>';

print '<div class="center agenda">';
print '<a class="butAction" type="button" href="../../comm/action/index.php">Ver la agenda</a>';
print '</div>';

print '</div>';

//Consulta para info de cumpleaños de todos los usuarios
$consulta = " SELECT rowid, firstname, lastname, birth FROM ".MAIN_DB_PREFIX."user u ";

$resultConsulta = $db->query($consulta);

print '<div class="fichecenter">';

print "
    <div class='div-table-responsive'>
        <table class='tagtable liste'>
            <tbody>
                <tr class='liste_titre'>
                    <th class='center liste_titre'>Empleado</th>
                    <th class='center liste_titre'>Fecha de nacimiento</th>
                </tr>";

                while ($usuario = $db->fetch_object($resultConsulta)) {

                    print "<tr class='oddeven'>
                                <td class='center'>".$usuario->firstname." ".$usuario->lastname."</td>
                                <td class='center'>".$usuario->birth."</td>
                            </tr>";     
                }
                

            print "</tbody>
        </table>
    </div>
    <tr>
</tr>";

print '</div>';

// End of page
llxFooter();
$db->close();





