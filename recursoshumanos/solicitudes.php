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




/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

//Título de la página
llxHeader("", $langs->trans("Solicitudes"));

//Estilos
print '<style>
	.aviso {
		font-style: italic;
		color: grey;
	}
	.cerradas {
		font-style: italic;
		color: grey;
	}
	.div-table-responsive {
		margin-top: 20px;
	}
</style>';

//Cabecera de la página
print load_fiche_titre($langs->trans("Solicitudes"), '', 'object_informacion_formacion.png@recursoshumanos');

//Si el usuario es admin
if ($user->admin) {

	//Consulta para el total de solicitudes
	$consulta = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";

	$resultConsulta = $db->query($consulta);
	$numSolicitudes = $db->num_rows($resultConsulta);

	//Consulta para el total de solicitudes vistas
	$consultaVistas = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consultaVistas.= " WHERE vista = 1";

	$resultConsultaVistas = $db->query($consultaVistas);
	$numSolicitudesVistas = $db->num_rows($resultConsultaVistas);

	//Consulta para el total de solicitudes cerradas
	$consultaCerradas = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consultaCerradas.= " WHERE cerrada = 1";

	$resultConsultaCerradas = $db->query($consultaCerradas);
	$numSolicitudesCerradas = $db->num_rows($resultConsultaCerradas);

	//Consulta para el total de solicitudes abiertas
	$consultaAbiertas = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consultaAbiertas.= " WHERE cerrada = 0";

	$resultConsultaAbiertas = $db->query($consultaAbiertas);
	$numSolicitudesAbiertas = $db->num_rows($resultConsultaAbiertas);

	print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
	print '<span>Desde esta sección podrás ver todas las solicitudes que tienes pendientes y abiertas. Además, como administrador, podrás abrir nuevas solicitudes</span>';
	print '</div>';

	//Info general de solicitudes
	print '<div class="fichecenter">';
	print '<div class="fichethirdleft">';
	
	print "
		<div class='div-table-responsive'>
			<table class='tagtable liste'>
				<tbody>
					<tr class='liste_titre'>
						<th class='center liste_titre'>Solicitudes totales</th>
						<th class='center liste_titre'>Vistas</th>
						<th class='center liste_titre'>Abiertas</th>
						<th class='center liste_titre'>Cerradas</th>
					</tr>";

			

				print "<tr class='oddeven'>
							<td class='center'>".$numSolicitudes."</td>
							<td class='center'>".$numSolicitudesVistas."</td>
							<td class='center'>".$numSolicitudesAbiertas."</td>
							<td class='center'>".$numSolicitudesCerradas."</td>";
							
						print "</tr>";     
			
					

				print "</tbody>
			</table>
		</div>";

	print '</div>';
	print '</div>';

	print "<br>";
	print "<br>";
	print "<br>";
	print "<br>";
	print "<br>";

	//Total de solicitudes abiertas
	print '<div class="fichecenter">';

	print "
		<div class='div-table-responsive'>
			<table class='tagtable liste'>
				<tbody>
					<tr class='liste_titre'>
						<th class='center liste_titre'>Tipo de solicitud</th>
						<th class='center liste_titre'>Descripción</th>
						<th class='center liste_titre'>Urgencia</th>
						<th class='center liste_titre'>Solicitado por</th>
						<th class='center liste_titre'>Solicitado a</th>
						<th class='center liste_titre'>Vista</th>
						<th class='center liste_titre'>Cerrada</th>
						<th class='center liste_titre'></th>
					</tr>";

					while ($solicitud = $db->fetch_object($resultConsultaAbiertas)) {

						//Consulta para el solicitante de cada solicitud
						$consultaUsuario = " SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
						$consultaUsuario.= " WHERE rowid = ".$solicitud->fk_solicitante;

						$resultUsuario = $db->query($consultaUsuario);
						$solicitante = $db->fetch_object($resultUsuario);

						//Consulta para el solicitado de cada solicitud
						$consultaUsuario2 = " SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
						$consultaUsuario2.= " WHERE rowid = ".$solicitud->fk_solicitado;

						$resultUsuario2 = $db->query($consultaUsuario2);
						$solicitado = $db->fetch_object($resultUsuario2);

						print "<tr class='oddeven'>
									<td class='center'>".$solicitud->tipo."</td>
									<td class='center'>".$solicitud->descripcion."</td>
									<td class='center'>".$solicitud->urgencia."</td>
									<td class='center'>".$solicitante->firstname." ".$solicitante->lastname."</td>
									<td class='center'>".$solicitado->firstname." ".$solicitado->lastname."</td>";
									
									//Si no está vista, imprime NO, si lo está, imprime SI
									if ($solicitud->vista == 0) {
										print "<td class='center'>No</td>";
									} else {
										print "<td class='center'>Si</td>";
									}
									
									//Si está cerrada, da la opción de abrirla, si está abierta, da la opción de cerrarla
									if ($solicitud->cerrada == 0) {
										print "<td class='center'>No</td>";
										print '<td><a class="fas fa-lock" href="'. $_SERVER["PHP_SELF"] .'?action=close&id=' . $object->id . '&rowid=' . $solicitud->rowid . '" title="Cerrar Solicitud"></a></td>';
									} else {
										print "<td class='center'>Si</td>";
										print '<td><a class="fas fa-lock-open" href="'. $_SERVER["PHP_SELF"] .'?action=open&id=' . $object->id . '&rowid=' . $solicitud->rowid . '" title="Reabrir Solicitud"></a></td>';
									}
									
								print "</tr>";     
					}
					

				print "</tbody>
			</table>
		</div>
		<form method='POST' action='solicitudes_card.php?action=create'>
		<div class='tabsAction'>
		<td><input class='butAction' type='submit' value='Nueva solicitud' name='csv'></td>
		</div>
		</form>";

	print '</div>';

	//Consulta para las solicitudes cerradas los últimos 7 días
	$consultaCe = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consultaCe.= " WHERE cerrada = 1 ";
	$consultaCe.= " AND fecha_cerrada >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";

	$resultConsultaCe = $db->query($consultaCe);
	$numSolicitudesCe = $db->num_rows($resultConsultaCe);

	//Total de solicitudes cerradas en los últimas 7 días
	print '<div class="fichecenter">';

	print '<span class="cerradas">Solicitudes cerradas la última semana (7 días)</span>';
	print "
		<div class='div-table-responsive'>
			<table class='tagtable liste'>
				<tbody>
					<tr class='liste_titre'>
						<th class='center liste_titre'>Tipo de solicitud</th>
						<th class='center liste_titre'>Descripción</th>
						<th class='center liste_titre'>Urgencia</th>
						<th class='center liste_titre'>Solicitado por</th>
						<th class='center liste_titre'>Solicitado a</th>
						<th class='center liste_titre'>Vista</th>
						<th class='center liste_titre'>Cerrada</th>
						<th class='center liste_titre'></th>
					</tr>";

					while ($solicitudCe = $db->fetch_object($resultConsultaCe)) {

						//Consulta para el usuario solicitante de cada solicitud
						$consultaUsuario = " SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
						$consultaUsuario.= " WHERE rowid = ".$solicitudCe->fk_solicitante;

						$resultUsuario = $db->query($consultaUsuario);
						$solicitante = $db->fetch_object($resultUsuario);

						//Consulta para el usuario solicitado de cada solicitud
						$consultaUsuario2 = " SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
						$consultaUsuario2.= " WHERE rowid = ".$solicitudCe->fk_solicitado;

						$resultUsuario2 = $db->query($consultaUsuario2);
						$solicitado = $db->fetch_object($resultUsuario2);

						print "<tr class='oddeven'>
									<td class='center'>".$solicitudCe->tipo."</td>
									<td class='center'>".$solicitudCe->descripcion."</td>
									<td class='center'>".$solicitudCe->urgencia."</td>
									<td class='center'>".$solicitante->firstname." ".$solicitante->lastname."</td>
									<td class='center'>".$solicitado->firstname." ".$solicitado->lastname."</td>";
									
									//Si no está vista, imprime NO, si lo está, imprime SI
									if ($solicitudCe->vista == 0) {
										print "<td class='center'>No</td>";
									} else {
										print "<td class='center'>Si</td>";
									}
									
									//Si está cerrada, da la opción de abrirla, si está abierta, da la opción de cerrarla
									if ($solicitudCe->cerrada == 0) {
										print "<td class='center'>No</td>";
										print '<td><a class="fas fa-lock" href="'. $_SERVER["PHP_SELF"] .'?action=close&id=' . $object->id . '&rowid=' . $solicitudCe->rowid . '" title="Cerrar Solicitud"></a></td>';
									} else {
										print "<td class='center'>Si</td>";
										print '<td><a class="fas fa-lock-open" href="'. $_SERVER["PHP_SELF"] .'?action=open&id=' . $object->id . '&rowid=' . $solicitudCe->rowid . '" title="Reabrir Solicitud"></a></td>';
									}
									
								print "</tr>";     
					}
					

				print "</tbody>
			</table>
		</div>";

	print '</div>';

//Si el usuario no es admin
} else {

	//Consulta para el total de solicitudes abiertas que tiene ese usuario
	$consulta = " SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$consulta.= " WHERE fk_solicitado = ".$user->id." AND cerrada = 0";

	$resultConsulta2 = $db->query($consulta);

	print '<div class="fichecenter">';

	print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
	print '<span>Desde esta sección podrás ver todas las solicitudes que tienes pendientes y abiertas</span>';
	print '</div>';

	print "
		<div class='div-table-responsive'>
			<table class='tagtable liste'>
				<tbody>
					<tr class='liste_titre'>
						<th class='center liste_titre'>Tipo de solicitud</th>
						<th class='center liste_titre'>Descripción</th>
						<th class='center liste_titre'>Urgencia</th>
						<th class='center liste_titre'>Solicitado por</th>
						<th class='center liste_titre'>Vista</th>
						<th class='center liste_titre'>Cerrada</th>
						<th class='center liste_titre'></th>
					</tr>";

					while ($solicitud = $db->fetch_object($resultConsulta2)) {

						//Consulta para el usuario solicitante de cada solicitud
						$consultaUsuario = " SELECT firstname, lastname FROM ".MAIN_DB_PREFIX."user ";
						$consultaUsuario.= " WHERE rowid = ".$solicitud->fk_solicitante;

						$resultConsulta = $db->query($consultaUsuario);
						$solicitante = $db->fetch_object($resultConsulta);

						print "<tr class='oddeven'>
									<td class='center'>".$solicitud->tipo."</td>
									<td class='center'>".$solicitud->descripcion."</td>
									<td class='center'>".$solicitud->urgencia."</td>
									<td class='center'>".$solicitante->firstname." ".$solicitante->lastname."</td>";
									
									//Si no está vista, imprime NO, si lo está, imprime SI
									if ($solicitud->vista == 0) {
										print "<td class='center'>No</td>";
									} else {
										print "<td class='center'>Si</td>";
									}
									
									//Si está cerrada, da la opción de abrirla, si está abierta, da la opción de cerrarla
									if ($solicitud->cerrada == 0) {
										print "<td class='center'>No</td>";
									} else {
										print "<td class='center'>Si</td>";
									}

									//Si no está vista, da la opción de marcar como vista
									if ($solicitud->vista == 0) {
										print '<td><a class="fas fa-eye" href="'. $_SERVER["PHP_SELF"] .'?action=ver&id=' . $object->id . '&rowid=' . $solicitud->rowid . '" title="Marcar como vista"></a></td>';
									} else {
										print "<td class='center'></td>";
									}
									
								print "</tr>";     
					}
					

				print "</tbody>
			</table>
		</div>
		<form method='POST' action='solicitudes_card.php?action=create'>
		</form>";

	print '</div>';

}

//Para marcar como cerrada la solicitud 
if ($_GET["action"] == "close") {

	$id = $_GET['id'];
	$rowid = $_GET['rowid'];

	$fecha = date('Y-m-d H:i:s');

	$sqlCerrar = " UPDATE ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$sqlCerrar.= " SET cerrada = 1, fecha_cerrada = '".$fecha."' ";
	$sqlCerrar.= " WHERE rowid = ".$rowid;

	$db->query($sqlCerrar);

	$destination_url = 'solicitudes.php';

	print '<meta http-equiv="refresh" content="0; url=' . $destination_url . '">';

}

//Para volver a abrir la solicitud
if ($_GET["action"] == "open") {

	$id = $_GET['id'];
	$rowid = $_GET['rowid'];

	$sqlCerrar = " UPDATE ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$sqlCerrar.= " SET cerrada = 0, fecha_cerrada = NULL ";
	$sqlCerrar.= " WHERE rowid = ".$rowid;

	$db->query($sqlCerrar);

	$destination_url = 'solicitudes.php';

	print '<meta http-equiv="refresh" content="0; url=' . $destination_url . '">';

}

//Para marcar como vista la solicitud
if ($_GET["action"] == "ver") {

	$id = $_GET['id'];
	$rowid = $_GET['rowid'];

	$sqlCerrar = " UPDATE ".MAIN_DB_PREFIX."recursoshumanos_solicitudes ";
	$sqlCerrar.= " SET vista = 1 ";
	$sqlCerrar.= " WHERE rowid = ".$rowid;

	$db->query($sqlCerrar);

	$destination_url = 'solicitudes.php';

	print '<meta http-equiv="refresh" content="0; url=' . $destination_url . '">';

}

// End of page
llxFooter();
$db->close();





