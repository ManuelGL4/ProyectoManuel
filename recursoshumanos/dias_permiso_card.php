
<?php
ob_start();

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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/recursoshumanos/app/DiaPermisoController.php';

dol_include_once('/recursoshumanos/class/dias_permiso.class.php');
dol_include_once('/recursoshumanos/lib/recursoshumanos_dias_permiso.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("recursoshumanos@recursoshumanos", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'dias_permisocard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
//$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Dias_permiso($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->recursoshumanos->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('dias_permisocard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


$permissiontoread = $user->rights->recursoshumanos->dias_permiso->read;
$permissiontoadd = $user->rights->recursoshumanos->dias_permiso->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->recursoshumanos->dias_permiso->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->recursoshumanos->dias_permiso->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->recursoshumanos->dias_permiso->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->recursoshumanos->multidir_output[isset($object->entity) ? $object->entity : 1].'/dias_permiso';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
//if (empty($conf->recursoshumanos->enabled)) accessforbidden();
//if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/recursoshumanos/dias_permiso_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/recursoshumanos/dias_permiso_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'RECURSOSHUMANOS_DIAS_PERMISO_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'RECURSOSHUMANOS_DIAS_PERMISO_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_DIAS_PERMISO_TO';
	$trackid = 'dias_permiso'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}

print '<style>
.modal-container {
position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);
}
.modal-body{
background: white !important; margin: 15% auto !important; padding: 20px !important; border-radius: 5px !important; width: 400px !important; text-align: center !important;
}
#descripcion{
    height: 168px !important;
    width:625px !important;
}
#motivos{
    height: 168px !important;
    width:625px !important;
}

@media (min-width: 500px) and (max-width: 768px) {
    #descripcion{
    height: 110px !important;
        width:62% !important;
    }
    #motivos{
        height: 110px !important;
        width:62% !important;
    }

}



</style>';


/*
 * Vista
 *
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);
$controller = new DiaPermisoController($db);
$title = $langs->trans("Dias_permiso");
$help_url = '';
llxHeader('', $title, $help_url);



if ($action == 'create') {
	print '<script>
    function closeModal() {
        document.querySelector("div[style*=fixed]").style.display = "none";
    }

    function confirmEdit() {
        document.getElementById("confirmForm").submit();
    }

    </script>';

	print load_fiche_titre($langs->trans("Solicitud de dias de permiso"), '', 'object_'.$object->picto);

	print '<form id="form1" action="" method="post" enctype="multipart/form-data">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');


	print '<table class="border centpercent tableforfieldcreate">'."\n";
    $fecha_solicitada = isset($_POST['fecha_solicitada']) ? $_POST['fecha_solicitada'] : '';
    $fecha_solicitada_fin = isset($_POST['fecha_solicitada_fin']) ? $_POST['fecha_solicitada_fin'] : '';
    $usuario_id = isset($_POST['usuario']) ? intval($_POST['usuario']) : -1;
    $admin_id = isset($_POST['admin']) ? intval($_POST['admin']) : -1;
    $descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';

	print "
			<tbody>
				
				<tr>
					<td>
						<span class='fieldrequired'>Fechas solicitada</span>
					</td>
					<td>
                    	<input type='datetime-local' name='fecha_solicitada' value='" . htmlspecialchars($fecha_solicitada) . "'>
						a
						<input type='datetime-local' name='fecha_solicitada_fin' value='" . htmlspecialchars($fecha_solicitada_fin) . "'>
                	</td>
					</tr>
";
print "
					<td>
						<span class='fieldrequired'>Usuario que solicita el dia</span>
					</td>
										<td>";


										print $form->select_dolusers($user->id, 'usuario', 1, '', 0);

                        // Si el usuario no es administrador, deshabilitar el campo
                        if (!$user->admin) {
                            echo '<script>
                                var usuarioField = document.getElementsByName("usuario")[0];
                                usuarioField.disabled = true;
                                
                                var hiddenField = document.createElement("input");
                                hiddenField.type = "hidden";
                                hiddenField.name = "usuario";
                                hiddenField.value = usuarioField.value;
                                usuarioField.parentNode.appendChild(hiddenField);
                            </script>';
                        }
						print "
					</td>
					</tr>";

					print "
					<td>
						<span class='fieldrequired'>Administrador validador</span>
					</td>
					<td>
							";
							echo $form->select_dolusers($admin_id, 'admin', 1, '', 0, '', '','','','','AND admin=1');
							print "
					</td>
					</tr>";

					print "<tr>
					<td>
					<span class='fieldrequired'>Descripcion de la solicitud</span>
					</td>
					<td>
						<textarea name='descripcion' id='descripcion' class='required'  > " . htmlspecialchars($descripcion) . "</textarea>
					</td>
					</tr>

					<tr>
                    </tr>
                    ";

                        print '</table>'."\n";

                        print dol_get_fiche_end();

                        print '<div class="center">';
                        print "
                    </table>
                    </div>
                    </div>
                    <div class='center'>
                    <input type='submit' class='button' name='guardar' value='Solicitar' id='btnModal'>
                    <input type='button' class='button button-cancel' value='Volver al listado' onclick=\"window.location.href='dias_permiso_list.php';\">
                    ";

	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

if (isset($_POST['guardar'])) {
    $fecha = isset($_POST['fecha_solicitada']) ? $_POST['fecha_solicitada'] : 'Fecha no especificada';
    $fecha_fin = isset($_POST['fecha_solicitada_fin']) ? $_POST['fecha_solicitada_fin'] : 'Fecha no especificada';
    $usuario_id = isset($_POST['usuario']) ? $_POST['usuario'] : null;
    $admin_id = isset($_POST['admin']) ? $_POST['admin'] : null;


    $usuario_nombre = 'Usuario no especificado';
    $admin_nombre = 'Administrador no especificado';


    $fecha_valida = true;
    $mensaje_error = '';

    if ($fecha !== 'Fecha no especificada' && $fecha_fin !== 'Fecha no especificada') {
        $fecha_obj = new DateTime($fecha);
        $fecha_fin_obj = new DateTime($fecha_fin);
        
        if ($fecha_obj > $fecha_fin_obj) {
            $fecha_valida = false;
            $mensaje_error = 'La fecha de inicio no puede ser mayor que la fecha final.';
        }
        
        if ($fecha_fin_obj < $fecha_obj) {
            $fecha_valida = false;
            $mensaje_error = 'La fecha final no puede ser menor que la fecha de inicio.';
        }
    }

    if ($fecha_valida) {

    if ($usuario_id) {
        $consulta_usuario = "SELECT firstname, lastname FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . intval($usuario_id);
        $resultado_usuario = $db->query($consulta_usuario);
        if ($resultado_usuario && $db->num_rows($resultado_usuario) > 0) {
            $usuario_data = $db->fetch_object($resultado_usuario);
            $usuario_nombre = htmlspecialchars($usuario_data->firstname . ' ' . $usuario_data->lastname);
        }
    }

    if ($admin_id) {
        $consulta_admin = "SELECT firstname, lastname FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . intval($admin_id);
        $resultado_admin = $db->query($consulta_admin);
        if ($resultado_admin && $db->num_rows($resultado_admin) > 0) {
            $admin_data = $db->fetch_object($resultado_admin);
            $admin_nombre = htmlspecialchars($admin_data->firstname . ' ' . $admin_data->lastname);
        }
    }

    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : 'Descripción no especificada';
    $fecha_formateada = ($fecha && $fecha !== 'Fecha no especificada') ? (new DateTime($fecha))->format('d/m/Y H:i') : '';
    $fecha_fin_formateada = ($fecha_fin && $fecha_fin !== 'Fecha fin no especificada') ? (new DateTime($fecha_fin))->format('d/m/Y H:i') : '';



    print '<div class="modal-container">
    <div class="modal-body">
        <h3>Confirmar Acción</h3>
        <p>Está seguro de que desea solicitar el día con la siguiente información:</p>
        <p><strong>Fecha solicitada:</strong> ' . htmlspecialchars($fecha_formateada) . ' <strong>a</strong> ' . htmlspecialchars($fecha_fin_formateada) . '</p>
        <p><strong>Usuario solicitante:</strong> ' . $usuario_nombre . '</p>
        <p><strong>Administrador validador:</strong> ' . $admin_nombre . '</p>
        <p><strong>Descripción:</strong> ' . htmlspecialchars($descripcion) . '</p>';


    print '<form id="confirmForm" method="post" enctype="multipart/form-data" action="">
            <input type="hidden" name="confirmForm" value="1">
            <input type="hidden" name="fecha_solicitada" value="' . htmlspecialchars($fecha) . '">
            <input type="hidden" name="fecha_solicitada_fin" value="' . htmlspecialchars($fecha_fin) . '">
            <input type="hidden" name="usuario" value="' . intval($usuario_id) . '">
            <input type="hidden" name="admin" value="' . intval($admin_id) . '">
            <input type="hidden" name="descripcion" value="' . htmlspecialchars($descripcion) . '">';



    print '<br><br>
            <input type="submit" class="button button-confirm" value="Sí">
            <button type="button" id="cancelReset" class="button button-cancel" onclick="closeModal()">No</button>
        </form>
    </div>
</div>';
} else {
    //Las fechas no son validas
    setEventMessages(array($mensaje_error), array(), 'errors');
}
}

if (isset($_POST['confirmForm'])) {

    $idPermiso = $controller->insertarPermiso(); 
    if ($idPermiso) {
    header('Location: dias_permiso_list.php?creado=success');
    exit();
    }
}










if (($id || $ref) && $action == 'edit') {

    if ($id > 0) {
        $sql = "SELECT * FROM khns_recursoshumanos_dias_permiso WHERE rowid = ".$db->escape($id);
        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $object = $db->fetch_object($resql);  

            $fecha_solicitada = $object->date_solic;
            $fecha_solicitada_fin = $object->date_solic_fin;
            $usuario_id = $object->fk_user_solicitado;
            $admin_id = $object->fk_user_validador;
            $descripcion = $object->label;
            $estado = $object->status;
            $motivos = $object->motivos; 
        } 
    }
	print '<script>
    function closeModal() {
        document.querySelector("div[style*=fixed]").style.display = "none";
    }
    
    function confirmEdit() {
        document.getElementById("confirmForm").submit();
    }
    </script>';
    print load_fiche_titre($langs->trans("Edicion de registro"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?action=edit&id='.$id.'">'; 

    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="id" value="'.$object->id.'">';
    if ($backtopage) {
        print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
    }
    if ($backtopageforcancel) {
        print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
    }

    print dol_get_fiche_head();

    print '<table class="border centpercent tableforfieldedit">'."\n";

    print "
        <tr>
            <td><span class='fieldrequired'>Fechas solicitada</span></td>
            <td>
                <input type='datetime-local' name='fecha_solicitada' value='" . htmlspecialchars($fecha_solicitada) . "'>
                a
                <input type='datetime-local' name='fecha_solicitada_fin' value='" . htmlspecialchars($fecha_solicitada_fin) . "'>
            </td>
        </tr>
        <tr>
            <td><span class='fieldrequired'>Usuario que solicita el dia</span></td>
            <td>";
			print $form->select_dolusers($usuario_id, 'usuario', 1, '', 0);
			if (!$user->admin) {
				echo '<script>
					document.addEventListener("DOMContentLoaded", function() {
						var usuarioField = document.getElementsByName("usuario")[0];
						usuarioField.disabled = true;
						
                        var hiddenField = document.createElement("input");
						hiddenField.type = "hidden";
						hiddenField.name = "usuario";
						hiddenField.value = usuarioField.value;
						usuarioField.parentNode.appendChild(hiddenField);
					});
				</script>';
			}
			
	
    print "</td></tr>";

    print "
        <tr>
            <td><span class='fieldrequired'>Administrador validador</span></td>
            <td>";
    print $form->select_dolusers($admin_id, 'admin', 1, '', 0, '', '','','','','AND admin=1');
    print "</td></tr>";

    print "
        <tr>
            <td><span class='fieldrequired'>Descripcion de la solicitud</span></td>
            <td>
                <textarea name='descripcion' id='descripcion' class='required' style='height: 168px; width: 625px;'>".htmlspecialchars($descripcion)."</textarea>
            </td>
        </tr>";


    print "
        <tr>
            <td><span class='field'>Estado</span></td>
            <td>
            <select name='estado' id='estado' onchange='mostrarMotivos()' ".(($user->id != $admin_id) ? "disabled" : "").">
                    <option value='0'".($estado == 0 ? " selected" : "").">Pendiente</option>
                    <option value='1'".($estado == 1 ? " selected" : "").">Aprobada</option>
                    <option value='9'".($estado == 9 ? " selected" : "").">Rechazada</option>
                </select>
            </td>
        </tr>";

    //El motivo solo se muestra si el estado es aprobado o rechazado
    print "
        <tr id='motivos-row' style='display: ".($estado == 1 || $estado == 9 ? "table-row" : "none").";'>
            <td><span class='field'>Motivos</span></td>
            <td>
                <textarea name='motivos' id='motivos' >".htmlspecialchars($motivos)."</textarea>
            </td>
        </tr>
		<input type='hidden' name='id' value='".$id."'>
		";

    print '</table>';

    print dol_get_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button button-save" name="editar-submit" value="'.$langs->trans("Edit").'">';
    print ' &nbsp; <input type="button" class="button button-cancel" name="cancel" value="'.$langs->trans("Cancel").'" onclick="window.location.href=\'dias_permiso_list.php\';">';
    print '</div>';

    print '</form>';


print '
<script>
function mostrarMotivos() {
    var estado = document.getElementById("estado").value;
    var motivosRow = document.getElementById("motivos-row");
    if (estado == "1" || estado == "9") {
        motivosRow.style.display = "table-row";
    } else {
        motivosRow.style.display = "none";
    }
}

document.addEventListener("DOMContentLoaded", function() {
    mostrarMotivos();
});
</script>';
}


//Confirmacion para guardar
if (isset($_POST['editar-submit'])) {
    $fecha = $_POST['fecha_solicitada'] ?? 'Fecha no especificada';
    $fecha_fin = $_POST['fecha_solicitada_fin'] ?? 'Fecha no especificada';
    $usuario_id = $_POST['usuario'] ?? null;
    $admin_id = $_POST['admin'] ?? null;
	$usuario_nombre = 'Usuario no especificado';
    $admin_nombre = 'Administrador no especificado';
	$motivos = !empty($_POST['motivos']) ? $_POST['motivos'] : 'No se han especificado motivos';
	$id = intval($_GET['id']);
    if ($usuario_id) {
        $consulta_usuario = "SELECT firstname, lastname FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . intval($usuario_id);
        $resultado_usuario = $db->query($consulta_usuario);

        if ($resultado_usuario && $db->num_rows($resultado_usuario) > 0) {
            $usuario_data = $db->fetch_object($resultado_usuario);
            $usuario_nombre = htmlspecialchars($usuario_data->firstname . ' ' . $usuario_data->lastname);
        }
    }

    if ($admin_id) {
        $consulta_admin = "SELECT firstname, lastname FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . intval($admin_id);
        $resultado_admin = $db->query($consulta_admin);

        if ($resultado_admin && $db->num_rows($resultado_admin) > 0) {
            $admin_data = $db->fetch_object($resultado_admin);
            $admin_nombre = htmlspecialchars($admin_data->firstname . ' ' . $admin_data->lastname);
        }
    }

    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : 'Descripción no especificada';
	$fecha_formateada = ($fecha && $fecha !== 'Fecha no especificada') ? (new DateTime($fecha))->format('d/m/Y H:i') : '';
	$fecha_fin_formateada = ($fecha_fin && $fecha_fin !== 'Fecha fin no especificada') ? (new DateTime($fecha_fin))->format('d/m/Y H:i') : '';
	$estado_nombre='';
	$estado=isset($_POST['estado']) ? $_POST['estado'] : 0;
	switch ($estado) {
        case 0:
            $estado_nombre = "Pendiente";
            break;
        case 1:
			$estado_nombre = "Aprobada";
            break;
        case 9:
			$estado_nombre = "Rechazada";
            break;
        default:
		$estado_nombre = "Estado desconocido"; 
            break;
    }




    $fecha_valida = true;
    $mensaje_error = '';

    if ($fecha !== 'Fecha no especificada' && $fecha_fin !== 'Fecha no especificada') {
        $fecha_obj = new DateTime($fecha);
        $fecha_fin_obj = new DateTime($fecha_fin);
        
        if ($fecha_obj > $fecha_fin_obj) {
            $fecha_valida = false;
            $mensaje_error = 'La fecha de inicio no puede ser mayor que la fecha final.';
        }
        
        if ($fecha_fin_obj < $fecha_obj) {
            $fecha_valida = false;
            $mensaje_error = 'La fecha final no puede ser menor que la fecha de inicio.';
        }
    }


    if ($fecha_valida) {

    print '<div class="modal-container">
    <div class="modal-body">
            <h3>Confirmar Acción</h3>
            <p>Está seguro de que desea actualizar el registro con la siguiente información:</p>
            <p><strong>Fecha solicitada:</strong> '.htmlspecialchars(($fecha_formateada)).' <strong>a</strong> '.htmlspecialchars(($fecha_fin_formateada)).'</p> 
            <p><strong>Usuario solicitante:</strong> '.$usuario_nombre.'</p>
            <p><strong>Descripción:</strong> '.htmlspecialchars($descripcion).'</p>
			<p><strong>Estado:</strong> '.htmlspecialchars($estado_nombre).'</p>
            <p><strong>Motivos:</strong> '.htmlspecialchars($motivos).'</p>
			<form id="updateForm" method="post" action="">
                <input type="hidden" name="updateForm" value="1">
                <input type="hidden" name="fecha_solicitada" value="' . htmlspecialchars($fecha) . '">
                <input type="hidden" name="fecha_solicitada_fin" value="' . htmlspecialchars($fecha_fin) . '">
                <input type="hidden" name="usuario" value="' . intval($usuario_id) . '">
                <input type="hidden" name="admin" value="' . intval($admin_id) . '">
                <input type="hidden" name="descripcion" value="' . htmlspecialchars($descripcion) . '">
                <input type="hidden" name="motivos" value="' . htmlspecialchars($motivos) . '">
				<input type="hidden" name="estado" value="' . intval($estado) . '">
				<input type="hidden" name="id" value="' . intval($id) . '">
                <br><br>
                <input type="submit" class="button button-confirm" value="Sí">
                <button type="button" id="cancelReset" class="button button-cancel" onclick="closeModal()">No</button>
            </form>
        </div>
    </div>';
    }else{
        setEventMessages(array($mensaje_error), array(), 'errors');
    }
}



if (isset($_POST['updateForm'])) {
    $controller->actualizarPermiso();
}



ob_end_flush();

llxFooter();
$db->close();
