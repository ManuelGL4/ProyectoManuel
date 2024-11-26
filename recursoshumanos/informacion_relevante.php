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

// Si pulsamos en confirmar borrado
if (isset($_POST['Borrar'])) {

	$id = $_GET['id'];
	$ahora = date('Y-m-d H:i:s');

	$sqlDel = "DELETE FROM " . MAIN_DB_PREFIX . "recursoshumanos_informacion_noticias ";
	$sqlDel.= "WHERE rowid = ".$id;
	$resultDel = $db->query($sqlDel);

	$destination_url = 'informacion_relevante.php';

	print '<meta http-equiv="refresh" content="0; url=' . $destination_url . '">';

}


// Si pulsamos en confirmar borrado
if (isset($_POST['Borrar2'])) {

	$id = $_GET['id'];
	$ahora = date('Y-m-d H:i:s');

	$sqlDel = "DELETE FROM " . MAIN_DB_PREFIX . "recursoshumanos_informacion_formacion ";
	$sqlDel.= "WHERE rowid = ".$id;
	$resultDel = $db->query($sqlDel);

	$destination_url = 'informacion_relevante.php';

	print '<meta http-equiv="refresh" content="0; url=' . $destination_url . '">';

}

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

//Título de la página
llxHeader("", $langs->trans("RecursosHumanosArea"));

//Estilos
print '<style>
	.contenedor {
		display: flex;
		flex-wrap: wrap;
		gap: 4%;
  	}
	.carta {
		flex: 0 0 25%;
		box-sizing: border-box;
		box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
		border-radius: 10px;
		padding: 20px;
		display: flex;
		flex-direction: column;
		margin-bottom: 2%;
	}
	.imagen {
		width: 360px;
		height: 230px;
		border-radius: 10px;
		margin-bottom: 3%;
		margin-left: 1%;
	}
	.titulo {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #C5E7FF;
		margin-bottom: 3%;
	}
    .titulo2 {
		text-align: center;
		width: 100%;
		border-radius: 10px;
		font-weight: bold;
		font-size: 20px;
		background-color: #FFEEC5;
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
	.pictodelete {
		margin-left: 63%;
	}
    .pictodelete2 {
		margin-left: 69%;
	}
	.center-modal {
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
    .inicio {
        font-weight: bold;
        font-size: 20px;
    }
    .butAction {
        margin-left: 0 !important;
        margin-top: -4px !important;
        margin-bottom: 20px !important;
    }
	.aviso {
		font-style: italic;
		color: grey;
	}
</style>';

//Cabecera de la página
print load_fiche_titre($langs->trans("Información relevante"), '', 'object_informacion_formacion.png@recursoshumanos');

print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
print '<span>Aquí podrás visualizar las últimas noticias y ofertas de formación posteadas. Además , podrás acceder a sus respectivas secciones</span>';
print '</div>';

//Consulta para las 3 últimas noticias añadidas
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_noticias ORDER BY rowid DESC LIMIT 3";
$result = $db->query($sql);

$numNoticias = $db->num_rows($result);

print '<p class="inicio">ÚLTIMAS NOTICIAS</p>';

print '<div class="contenedor">';

//Si el número de noticias posteadas es mayor que 0, se muestran
if ($numNoticias > 0) {

	while ($data = $db->fetch_object($result) ){

	print '<div class="carta">';

	if ($data->link_img == "") {
		print '<img src="img/noticias.jpg" class="imagen">';
	} else {
		print '<img src="'.$data->link_img.'" class="imagen">';
	}

		print '<div class="titulo2">'.$data->titulo.'</div>';
		print '<div class="descripcion">'.$data->descripcion.'</div>';
		print '<div><a class="fas fa-arrow-right" href="'.$data->link.'" target="_blank" class="vermas"> Ir a la noticia</a><a class="fas fa-trash pictodelete2" style="" title="Eliminar" href="' . $_SERVER["PHP_SELF"] . '?action=delete&id=' . $data->rowid . '"></a></div>';
		print '</div>';

	}

	print '</div>';

	print '<a href="informacion_noticias_list.php" class="butAction">Ver todas</a>';

//Si no hay noticias, se muestra mensaje
} else {

	print '<div class="aviso">';
	print '<span>No hay noticias para mostrar</span>';
	print '</div>';
	print '</div>';

}

//Consulta para las 3 últimas ofertas añadidas
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_formacion ORDER BY rowid DESC LIMIT 3";
$result = $db->query($sql);

$numFormaciones = $db->num_rows($result);

print '<p class="inicio">ÚLTIMAS OFERTAS DE FORMACIÓN</p>';

print '<div class="contenedor">';

//Si el número de ofertas posteadas es mayor que 0, se muestran
if ($numFormaciones > 0) {

	while ($data = $db->fetch_object($result) ){

		print '<div class="carta">';

		if ($data->link_img == "") {
			print '<img src="img/formacion-trabajadores.jpg" class="imagen">';
		} else {
			print '<img src="'.$data->link_img.'" class="imagen">';
		}

		print '<div class="titulo">'.$data->titulo.'</div>';
		print '<div class="descripcion">'.$data->descripcion.'</div>';
		print '<div><a class="fas fa-arrow-right" href="'.$data->link.'" class="vermas" target="_blank"> Ir a la formación</a><a class="fas fa-trash pictodelete" style="" title="Eliminar" href="' . $_SERVER["PHP_SELF"] . '?action=delete2&id=' . $data->rowid . '"></a></div>';
		print '</div>';

	}

	print '</div>';

	print '<a href="informacion_formacion_list.php" class="butAction">Ver todas</a>';

//Si no hay ofertas, se muestra mensaje
} else {

	print '<div class="aviso">';
	print '<span>No hay ofertas de formación para mostrar</span>';
	print '</div>';
	print '</div>';

}


//Modal para borrar noticias
if ($_GET["action"] == "delete") {
	$id = $_GET['id'];

	print '
	<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" name="formfilter" autocomplete="off">
		<div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-dialog-buttons ui-draggable center-modal" aria-describedby="dialog-confirm" aria-labelledby="ui-id-1" style="height: auto; width: 500px; top: 40%; left: 50%; z-index: 101;">
			<div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle">
				<span id="ui-id-1" class="ui-dialog-title">Borrar Noticia</span>
				<button type="submit" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close">
					<span class="ui-button-icon ui-icon ui-icon-closethick"></span>
					<span class="ui-button-icon-space"> </span>
					Close
				</button>
			</div>
			<div id="dialog-confirm" style="width: auto; min-height: 0px; max-height: none; height: 90.928px;" class="ui-dialog-content ui-widget-content">
				<div class="confirmquestions">
				</div>
				<div class="">
					<table>
						<tr>
							<td>
								<input type="text" name="tit" style="width: 300px" value="¿Desea borrar esta noticia?">
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
				<div class="ui-dialog-buttonset">
					<button type="submit" class="ui-button ui-corner-all ui-widget" name="Borrar">
						Borrar
					</button>
					<button type="submit" class="ui-button ui-corner-all ui-widget">
						Salir
					</button>
				</div>
			</div>
		</div>
	</form>';
}

//Modal para borrar ofertas
if ($_GET["action"] == "delete2") {
	$id = $_GET['id'];

	print '
	<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" name="formfilter" autocomplete="off">
		<div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-dialog-buttons ui-draggable center-modal" aria-describedby="dialog-confirm" aria-labelledby="ui-id-1" style="height: auto; width: 500px; top: 40%; left: 50%; z-index: 101;">
			<div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle">
				<span id="ui-id-1" class="ui-dialog-title">Borrar Formación</span>
				<button type="submit" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close">
					<span class="ui-button-icon ui-icon ui-icon-closethick"></span>
					<span class="ui-button-icon-space"> </span>
					Close
				</button>
			</div>
			<div id="dialog-confirm" style="width: auto; min-height: 0px; max-height: none; height: 90.928px;" class="ui-dialog-content ui-widget-content">
				<div class="confirmquestions">
				</div>
				<div class="">
					<table>
						<tr>
							<td>
								<input type="text" name="tit" style="width: 300px" value="¿Desea borrar esta formación?">
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
				<div class="ui-dialog-buttonset">
					<button type="submit" class="ui-button ui-corner-all ui-widget" name="Borrar2">
						Borrar
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
