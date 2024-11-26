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
llxHeader("", $langs->trans("Ficha de Empleado"));

//Estilos
print '<style>
	.contenedor {
		display: flex;
		flex-wrap: wrap;
        justify-content: center;
        align-items: center;
		gap: 4%;
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
	.titulo {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #E5F891;
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
	.aviso {
		font-style: italic;
		color: grey;
	}
    .numero1 {
		font-size: 180px;
		color: red;
	}
    .numero2 {
		font-size: 180px;
		color: green;
	}
    .numero3 {
		font-size: 180px;
		color: grey;
	}
	@media screen and (max-width: 1020px) {
		.carta1 {
			flex: 0 0 40%;
		}
	}
	@media screen and (max-width: 640px) {
		.carta1 {
			flex: 0 0 50%;
		}
	}
	@media screen and (max-width: 520px) {
		.carta1 {
			flex: 0 0 100%;
		}
	}
	
</style>';

//Cabecera de la página
print load_fiche_titre($langs->trans("Ficha de empleado"), '', 'object_informacion_formacion.png@recursoshumanos');

//Si el usuario es admin
if ($user->admin) {

	//Consulta para las solicitudes
    $consultaSol = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consultaSol.= " WHERE cerrada = 0 ";

    $resultConsultaSol = $db->query($consultaSol);
    $numSol = $db->num_rows($resultConsultaSol);

	//Consulta para los cumpleaños
    $consultaCumple = " SELECT rowid FROM ".MAIN_DB_PREFIX."user ";
    $consultaCumple.= " WHERE MONTH(birth) = MONTH(CURDATE()) AND DAY(birth) = DAY(CURDATE()) ";

    $resultCumple = $db->query($consultaCumple);
    $numCumple = $db->num_rows($resultCumple);

	//Consulta para las encuestas
    $consultaEncu = " SELECT * FROM ".MAIN_DB_PREFIX."opensurvey_sondage ";
    $consultaEncu.= " WHERE date_fin > NOW() ";

    $resultEncu = $db->query($consultaEncu);
    $numEncu = $db->num_rows($resultEncu);

	//Consulta para las noticias
	$consultaNoti = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_noticias ";

    $resultNoti = $db->query($consultaNoti);
    $numNoti = $db->num_rows($resultNoti);

	//Consulta para la formación
	$consultaForm = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_formacion ";

	$resultForm = $db->query($consultaForm);
	$numForm = $db->num_rows($resultForm);

    print '<div class="fichecenter">';

    print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
    print '<span>Esta es tu ficha de empleado. Desde aquí podrás ver la posición global de tu módulo de recursos humanos</span>';
    print '</div>';

    print '<div class="contenedor">';

	//Carta 1
    print '<div class="carta1">';
    print '<div class="titulo1">Solicitudes</div>';
    print '<div>Tienes:</div>';
    
	if ($numSol > 0) {
        print '<span class="numero1">'.$numSol.'</span>';
    } else {
        print '<span class="numero2">'.$numSol.'</span>';
    }

    print '<div class="titulo">solicitud/es abierta/s</div>';
    print '<div><a class="fas fa-arrow-right" href="solicitudes.php" target="_blank" class="vermas"> Ir a las solicitudes</a></div>';
    print '</div>';

	//Carta 2
    print '<div class="carta1">';
    print '<div class="titulo2">Encuestas</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numEncu.'</span>';
    print '<div class="titulo">encuesta/s abierta/s</div>';
    print '<div><a class="fas fa-arrow-right" href="../../opensurvey/list.php" target="_blank" class="vermas"> Ir a las encuestas</a></a></div>';
    print '</div>';

	//Carta 3
    print '<div class="carta1">';
    print '<div class="titulo3">Cumpleaños</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numCumple.'</span>';
    print '<div class="titulo">cumpleaño/s hoy</div>';
    print '<div><a class="fas fa-arrow-right" href="alertacumple.php" target="_blank" class="vermas"> Ir a los cumpleaños</a></div>';
    print '</div>';

	//Carta 4
	print '<div class="carta1">';
    print '<div class="titulo3">Noticias</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numNoti.'</span>';
    print '<div class="titulo">noticia/s posteada/s</div>';
    print '<div><a class="fas fa-arrow-right" href="informacion_noticias_list.php" target="_blank" class="vermas"> Ir a las noticias</a></div>';
    print '</div>';

	//Carta 5
	print '<div class="carta1">';
    print '<div class="titulo2">Ofertas de formación</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numForm.'</span>';
    print '<div class="titulo">oferta/s posteada/s</div>';
    print '<div><a class="fas fa-arrow-right" href="informacion_formacion_list.php" target="_blank" class="vermas"> Ir a las ofertas de formación</a></div>';
    print '</div>';

	//Carta 6
	print '<div class="carta1">';
    print '<div class="titulo1">Tutoriales</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">2</span>';
    print '<div class="titulo">tutorial/es posteado/s</div>';
    print '<div><a class="fas fa-arrow-right" href="videotutoriales.php" target="_blank" class="vermas"> Ir a los videotutoriales</a></div>';
    print '</div>';

    print '</div>';

    print '</div>';

//Si el usuario no es admin
} else {

	//Consulta para las solicitudes
	$consultaSol = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consultaSol.= " WHERE fk_solicitado = ".$user->id;

    $resultConsultaSol = $db->query($consultaSol);
    $numSol = $db->num_rows($resultConsultaSol);

	//Consulta para los cumpleaños
    $consultaCumple = " SELECT rowid FROM ".MAIN_DB_PREFIX."user ";
    $consultaCumple.= " WHERE MONTH(birth) = MONTH(CURDATE()) AND DAY(birth) = DAY(CURDATE()) ";

    $resultCumple = $db->query($consultaCumple);
    $numCumple = $db->num_rows($resultCumple);

	//Consulta para las encuestas
	$consultaEncu = " SELECT * FROM ".MAIN_DB_PREFIX."opensurvey_sondage ";
    $consultaEncu.= " WHERE date_fin > NOW() ";

    $resultEncu = $db->query($consultaEncu);
    $numEncu = $db->num_rows($resultEncu);

	//Noticias creadas en la última semana
	$consultaNoti = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_noticias ";
	$consultaNoti.= " WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 7 DAY); ";

    $resultNoti = $db->query($consultaNoti);
    $numNoti = $db->num_rows($resultNoti);

	//Consulta para las ofertas de formación
	$consultaForm = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_formacion ";
	$consultaForm.= " WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 7 DAY); ";

	$resultForm = $db->query($consultaForm);
	$numForm = $db->num_rows($resultForm);

    print '<div class="fichecenter">';

    print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
    print '<span>Esta es tu ficha de empleado. Desde aquí podrás ver la posición global de tu módulo de recursos humanos</span>';
    print '</div>';

    print '<div class="contenedor">';

	//Carta 1
    print '<div class="carta1">';
    print '<div class="titulo1">Solicitudes</div>';
    print '<div>Tienes:</div>';
    
	if ($numSol > 0) {
        print '<span class="numero1">'.$numSol.'</span>';
    } else {
        print '<span class="numero2">'.$numSol.'</span>';
    }

    print '<div class="titulo">solicitud/es abierta/s</div>';
    print '<div><a class="fas fa-arrow-right" href="solicitudes.php" target="_blank" class="vermas"> Ir a las solicitudes</a></div>';
    print '</div>';

	//Carta 2
    print '<div class="carta1">';
    print '<div class="titulo2">Encuestas</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numEncu.'</span>';
    print '<div class="titulo">encuesta/s sin responder</div>';
    print '<div><a class="fas fa-arrow-right" href="../../opensurvey/list.php" target="_blank" class="vermas"> Ir a las encuestas</a></a></div>';


    print '</div>';

	//Carta 3
    print '<div class="carta1">';
    print '<div class="titulo3">Cumpleaños</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numCumple.'</span>';
    print '<div class="titulo">cumpleaño/s hoy</div>';
    print '<div><a class="fas fa-arrow-right" href="alertacumple.php" target="_blank" class="vermas"> Ir a los cumpleaños</a></div>';
    print '</div>';

	//Carta 4
	print '<div class="carta1">';
    print '<div class="titulo3">Noticias</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numNoti.'</span>';
    print '<div class="titulo">noticia/s nueva/s</div>';
    print '<div><a class="fas fa-arrow-right" href="informacion_noticias_list.php" target="_blank" class="vermas"> Ir a las noticias</a></div>';
    print '</div>';

	//Carta 5
	print '<div class="carta1">';
    print '<div class="titulo2">Ofertas de formación</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numForm.'</span>';
    print '<div class="titulo">oferta/s nueva/s</div>';
    print '<div><a class="fas fa-arrow-right" href="informacion_formacion_list.php" target="_blank" class="vermas"> Ir a las ofertas de formación</a></div>';
    print '</div>';

	//Carta 6
	print '<div class="carta1">';
    print '<div class="titulo1">Videotutoriales</div>';
    print '<div>Tienes:</div>';
    print '<span class="numero3">'.$numCumple.'</span>';
    print '<div class="titulo">videotutorial/es nuevo/s</div>';
    print '<div><a class="fas fa-arrow-right" href="videotutoriales.php" target="_blank" class="vermas"> Ir a los videotutoriales</a></div>';
    print '</div>';

    print '</div>';

    print '</div>';

}


// End of page
llxFooter();
$db->close();