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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

// load recursoshumanos libraries
require_once __DIR__.'/class/informacion_noticias.class.php';

// for other modules
//dol_include_once('/othermodule/class/otherobject.class.php');

// Load translation files required by the page
$langs->loadLangs(array("recursoshumanos@recursoshumanos", "other"));

$action     = GETPOST('action', 'aZ09') ?GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...

$id = GETPOST('id', 'int');

// Initialize technical objects
$object = new Informacion_noticias($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->recursoshumanos->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('informacion_noticiaslist')); // Note that conf->hooks_modules contains array

$permissiontoread = $user->rights->recursoshumanos->informacion_noticias->read;
$permissiontoadd = $user->rights->recursoshumanos->informacion_noticias->write;
$permissiontodelete = $user->rights->recursoshumanos->informacion_noticias->delete;


if ($user->socid > 0) accessforbidden();

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

	$destination_url = 'informacion_noticias_list.php';

	print '<meta http-equiv="refresh" content="0; url=' . $destination_url . '">';

}

/*
 * View
 */

$form = new Form($db);

$now = dol_now();

$help_url = '';

//Título de la página
$title = $langs->trans('ListOf', $langs->transnoentitiesnoconv("noticias interesantes"));
$morejs = array();
$morecss = array();

//Cabecera de la página
llxHeader('', $title, $help_url, '', 0, 0, $morejs, $morecss, '', '');

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
		margin-bottom: 4%;
	}
	.imagen {
		width: 360px;
		height: 230px;
		border-radius: 10px;
		margin-bottom: 3%;
		margin-left: 1.5%;
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
</style>';

$arrayofselected = is_array($toselect) ? $toselect : array();

//Si el usuario es admin, nos aparecerá el botón que permitirá añadir nuevas noticias
if ($user->admin) {
	$newcardbutton = dolGetButtonTitle($langs->trans('Nueva noticia'), '', 'fa fa-plus-circle', dol_buildpath('/recursoshumanos/informacion_noticias_card.php', 1).'?action=create&backtopage='.urlencode($_SERVER['PHP_SELF']), '', $permissiontoadd);
}

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_'.$object->picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

//Consulta para mostrar todas las noticias
$sql = "SELECT * FROM ".MAIN_DB_PREFIX."recursoshumanos_informacion_noticias";
$result = $db->query($sql);

$numNoticias = $db->num_rows($result);

print '<div class="contenedor">';

//Si el número de noticias es mayor que 0, se muestran
if ($numNoticias > 0) {

	while ($data = $db->fetch_object($result) ){

		print '<div class="carta">';

		if ($data->link_img == "") {
			print '<img src="img/noticias.jpg" class="imagen" alt="Imagen no disponible">';
		} else {
			print '<img src="'.$data->link_img.'" class="imagen" alt="Imagen no disponible">';
		}

		print '<div class="titulo">'.$data->titulo.'</div>';
		print '<div class="descripcion">'.$data->descripcion.'</div>';
		print '<div><a class="fas fa-arrow-right" href="'.$data->link.'" target="_blank" class="vermas"> Ir a la noticia</a><a class="fas fa-trash pictodelete" style="" title="Eliminar" href="' . $_SERVER["PHP_SELF"] . '?action=delete&id=' . $data->rowid . '"></a></div>';
		print '</div>';

	}

//Si el número de noticias es 0, se muestra mensaje
} else {

	print '<div class="aviso">';
	print '<span>No hay noticias para mostrar</span>';
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

// End of page
llxFooter();
$db->close();
