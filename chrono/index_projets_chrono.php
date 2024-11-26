<?php
ob_start();

print '<style>
.overlay {
    position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center;
}
.overlay form {
    background: white; padding: 20px; border-radius: 8px; width: 500px; text-align: center;
}
.overlay form input {
    width: 80%;
}

.modal-header {
    border-bottom: 1px solid #ddd;
    padding: 10px;
    background-color: #f1f1f1;
}

.modal-title {
    font-size: 18px;
    font-weight: bold;
}

.modal-content {
    max-height: 290px;
    overflow-y: auto;
    margin-top: 10px;
}

.modal-table {
    width: 100%;
    margin-bottom: 10px;
}

.field {
    font-weight: bold;
}

.input-field {
    width: 100%;
    padding: 5px;
    margin: 5px 0;
    border-radius: 4px;
    border: 1px solid #ccc;
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

.btn-save {
    background-color: #5bc0de;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-cancel {
    background-color: #d9534f;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-save:hover,
.btn-cancel:hover {
    opacity: 0.8;
}
</style>';
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/project.lib.php';
require_once '../chrono/class/tiempotarea.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/chrono/app/ChronoController.php';

$controller = new ChronoController($db);
$proyectos = $controller->listProjects($user); 
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');


if (!$user->rights->projet->lire) {
    accessforbidden();
}

$title = $langs->trans("Listado tiempo proyectos");
$langs->load("projects");

/*FILTROS DEL FORMULARIO*/
$view_all_projects = isset($_GET['filter']) && $_GET['filter'] === 'all';
$selected_project = isset($_GET['project']) ? intval($_GET['project']) : 0;
$ls_userid = isset($_GET['ls_userid']) ? intval($_GET['ls_userid']) : 0;
$date_start_input = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end_input = isset($_GET['date_end']) ? $_GET['date_end'] : '';

/*PAGINACION*/ 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$perPage = 10; 




$user_id = $user->id;
$project_ids = [];

$resql_projects = $controller->getAssignedProjects($user);  

if ($resql_projects) {
    foreach ($resql_projects as $obj) {
        if (isset($obj->rowid)) {
            $project_ids[] = $obj->rowid;
        }
    }
} else {
    echo "No se encontraron proyectos asignados.";
}




if ($_GET["action"] == "edit") {
    $id = $_GET['id'];
    $datos = $controller->obtenerDatosEdicion($id);

    if ($datos) {
        $dat = $datos['dat'];
        $proyecto = $datos['proyecto'];
        $usuario = $datos['usuario'];
        $tarea = $datos['tarea'];
        $tiempoFormateado = $datos['tiempoTranscurrido'];

        print '
        <div class="overlay">
            <form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&action=save" name="formfilter" autocomplete="off" onsubmit="return validarFormulario();">
                <div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle" style="border-bottom: 1px solid #ddd; padding: 10px;">
                    <span id="ui-id-1" class="ui-dialog-title">Edición de Horas</span>
                </div>
                <div style="width: auto; min-height: 0px; max-height: none; height: 290px;" class="ui-dialog-content ui-widget-content">
                    <div class="confirmquestions"></div>
                    <div>
                        <table style="width: 100%; margin-bottom: 10px;">
                            <tr>
                                <td><span class="field">Usuario</span></td>
                                <td><input type="text" name="usuario" value="' . htmlspecialchars($usuario->firstname . " " . $usuario->lastname) . '" readonly style="width: 100%; padding: 5px;"></td>
                            </tr>
                            <tr>
                                <td><span class="field">Nombre del proyecto</span></td>
                                <td><input type="text" name="proyecto" value="' . htmlspecialchars($proyecto->title) . '" readonly style="width: 100%; padding: 5px;"></td>
                            </tr>
                            <tr>
                                <td><span class="field">Tarea</span></td>
                                <td><input type="text" name="tarea" value="' . htmlspecialchars($tarea->label) . '" readonly style="width: 100%; padding: 5px;"></td>
                            </tr>';

        if ($user->admin) {
            print '
                            <tr>
                                <td><span class="field">Fecha/Hora de ' . ($dat->event_type == 2 ? 'entrada' : 'salida') . '</span></td>
                                <td>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="' . ($dat->date_time_event) . '" style="width: 100%; padding: 5px;">
                                    <div id="error-fecha" style="color: red; font-size: 12px; display: none;">Por favor, ingrese una fecha válida.</div>
                                </td>
                            </tr>';
        } else {
            print '
                            <tr>
                                <td><span class="field">Fecha/Hora de ' . ($dat->event_type == 2 ? 'entrada' : 'salida') . '</span></td>
                                <td>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="' . ($dat->date_time_event) . '" readonly style="width: 100%; padding: 5px;">
                                </td>
                            </tr>';
        }

        print '
                            <tr>
                                <td><span class="field">Tiempo Transcurrido (H:M:S)</span></td>
                                <td><input type="text" name="tiempo_transcurrido" value="' . htmlspecialchars($tiempoFormateado) . '" placeholder="HH:MM:SS" readonly style="width: 100%; padding: 5px;"></td>
                            </tr>
                            <tr>
                                <td><span class="field">Nota</span></td>
                                <td>
                                    <input type="text" id="nota" name="nota" value="' . htmlspecialchars($dat->note) . '" style="width: 100%; padding: 5px;">
                                    <div id="error-nota" style="color: red; font-size: 12px; display: none;">La nota no puede exceder los 255 caracteres.</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="ui-dialog-buttonset" style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                    <button type="submit" class="ui-button ui-corner-all ui-widget" name="edit" style="background-color: #5bc0de; color: white; padding: 10px 20px; border: none; border-radius: 4px;">
                        Guardar
                    </button>
                    <button type="button" class="ui-button ui-corner-all ui-widget" onclick="window.history.back();" style="background-color: #d9534f; color: white; padding: 10px 20px; border: none; border-radius: 4px;">
                        Salir
                    </button>
                </div>
            </form>
        </div>';

        print '
        <script>
            function validarFormulario() {
                let valido = true;

                const fechaInicio = document.getElementById("fecha_inicio");
                const errorFecha = document.getElementById("error-fecha");
                const nota = document.getElementById("nota");
                const errorNota = document.getElementById("error-nota");

                // Validar fecha
                if (!fechaInicio.value) {
                    errorFecha.style.display = "block";
                    valido = false;
                } else {
                    errorFecha.style.display = "none";
                }

                // Validar nota
                if (nota.value.trim().length > 255) {
                    errorNota.style.display = "block";
                    valido = false;
                } else {
                    errorNota.style.display = "none";
                }

                return valido;
            }
        </script>';
    } else {
        print '<p>Error: No se encontró el registro solicitado.</p>';
    }
}


if (isset($_POST['edit'])) {
        $id = intval($_GET['id']);

        $fecha_inicio = $_POST['fecha_inicio'];
        $nota = $_POST['nota'];
    
        $controller->editarTarea($id, $fecha_inicio, $nota);
}





/*SI SE DECIDE BORRAR*/ 
if ($_GET["action"] == "delete") {
    $token = $_GET['token'];

    //Mostrar el modal
    print '
    <div style="position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center;">
        <form method="POST" action="' . $_SERVER['PHP_SELF'] . '?token=' . $token . '" name="formfilter" autocomplete="off" style="background: white; padding: 20px; border-radius: 8px; width: 400px; text-align: center;">
                    <span id="ui-id-1" class="ui-dialog-title">Borrar Hora Imputada</span>
                    <button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close" onclick="window.history.back();" style="background: none; border: none; cursor: pointer;">
                        <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
                    </button>
                    <br><br>
                    <p>¿Desea borrar esta hora imputada?</p>

                    <div class="ui-dialog-buttonset" style="display: flex; gap: 10px; justify-content: center;">
                        <button type="submit" class="ui-button ui-corner-all ui-widget" name="Borrar" style="background-color: #d9534f; color: white; padding: 10px 20px; border: none; border-radius: 4px;">
                            Borrar
                        </button>
                        <button type="button" class="ui-button ui-corner-all ui-widget" onclick="window.history.back();" style="background-color: #5bc0de; color: white; padding: 10px 20px; border: none; border-radius: 4px;">
                            Salir
                        </button>
                </div>
            </div>
        </form>
    </div>';
    
}

//Si se pulsa en confirmar borrado
if (isset($_POST['Borrar'])) {
    $token = $_GET['token'];

        //Borrar TODOS los registros donde el token sea igual al token del registro que se quiera borrar,para borrar tanto la entrada como la salida
        $sqlUpd = "DELETE FROM " . MAIN_DB_PREFIX . "attendance_event WHERE token = '" . $token."'";

        $resultUpd = $db->query($sqlUpd);

        if (!$resultUpd) {
            header('Location: index_projets_chrono.php?eliminado=error');
        }else{
            header('Location: index_projets_chrono.php?eliminado=success'); 
        }


}


if (isset($_GET['eliminado'])) {
    if ($_GET['eliminado'] == 'success') {
        setEventMessages(array("La hora imputada ha sido borrada correctamente."), array(), 'mesgs');
    } elseif ($_GET['eliminado'] == 'error') {
        setEventMessages(array("Error al borrar el registro del tiempo"), array(), 'errors');
    }
}



$result = $controller->getProjectsAndTasks($db, $user);
if ($result) {
    $groupedProjects = $result['groupedProjects'];
    $projectTotalTime = $result['projectTotalTime'];
} else {
}







/**
 * 
 * 
 * 
 * VISTA
 */




llxHeader('', $title);
$totalRecords = count($groupedProjects);
print_barre_liste($title, $page, $_SERVER["PHP_SELF"], '', '', '', '', $totalRecords, $totalRecords, 'title_companies', 0, '', '', '');

print '<form method = "GET" action = "">';

//Solo si el usuario es administrador permitir que pueda ver todos los registros
if ($user->admin) {
    print '<div style="text-align: center; margin-bottom: 20px;">';
    print '<select name="filter_type" onchange="this.form.submit()" style="padding: 5px;">';
    print '<option value="all" ' . (isset($_GET['filter_type']) && $_GET['filter_type'] === 'all' ? 'selected' : '') . '>Ver todos los tiempos de todos los usuarios</option>';
    print '<option value="my_records" ' . (isset($_GET['filter_type']) && $_GET['filter_type'] === 'my_records' ? 'selected' : '') . '>Ver Tiempos Tareas Asignadas</option>';
    print '</select>';
    print '</div>';
}


print '<table class = "liste" width = "100%">'."\n";
print '<tr class = "liste_titre">';
print_liste_field_titre('Eventtype', $PHP_SELF, 't.event_type', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre('User', $PHP_SELF, 't.fk_userid', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Nombre del proyecto"), $_SERVER["PHP_SELF"], "p.title", "", '', '', $sortfield, $sortorder);
print_liste_field_titre('Date');

print "\n";
print_liste_field_titre($langs->trans("Acciones"), $_SERVER["PHP_SELF"], "t.duration_effective", "", '', '', $sortfield, $sortorder);

$ls_nombre_proyecto=NULL;
if(isset($_GET["ls_nombre_proyecto"])){
    $ls_nombre_proyecto=$_GET["ls_nombre_proyecto"];
}
if(isset($_GET["ls_event_type"])){
    $ls_event_type=$_GET["ls_event_type"];
}
$ls_userid=NULL;
if(isset($_GET["ls_userid"])){
    $ls_userid=$_GET["ls_userid"];
}
if(isset($_GET["ls_date_time"])){
    $ls_date_time=$_GET["ls_date_time"];
}
$options = [
    "0" => "-",
    "2" => "Entrada",
    "3" => "Salida"
];

if (isset($_GET["ls_event_type"])) {
    $selected_value = $_GET["ls_event_type"];
} else {
    $selected_value = "0"; 
}

print "\n";
print '</tr>';
print '<tr class = "liste_titre">';
print '<td class = "liste_titre" colspan = "1" >';
print '<select name="ls_event_type" class="litre_titre" colspan="1">';
foreach ($options as $value => $label) {
    $selected = ($value == $selected_value) ? ' selected' : ''; 
    print '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
}
print '</select>';

print '</select>';
print '</td>';
print '<td class = "liste_titre" colspan = "1" >';
print $form->select_dolusers($ls_userid, 'ls_userid', 1, '', 0);
print '</td>';

print '<td class = "liste_titre" colspan = "1" >';



print '<input class = "flat" type = "text"   name = "ls_nombre_proyecto" value = "'.$ls_nombre_proyecto.'">';


print '</td>';

print '<td class = "liste_titre" colspan = "1" >';
print '<input class = "flat" type = "date"   name = "ls_date_time" value = "'.$ls_date_time.'">';
print '</td>';



print '<td class = "liste_titre" colspan = "1" />';
print '<button class="butAction" type="submit">Buscar</button>';

print '</td>';
print '</tr>'."\n";



print '<table class="noborder centpercent">';

//tabla de proyectos
foreach ($groupedProjects as $project) {
    $projectDetails = new Project($db);
    $projectDetails->fetch($project['projectid']);

    //Tiempo total del proyecto
    $total_time_seconds = 0;

    // Calcular el tiempo total sumando el tiempo de cada tarea
    foreach ($project['tasks'] as $task) {
        if ($task->event_type == 3) { // Solo contar las tareas de salida
            $query = "SELECT date_time_event FROM ".MAIN_DB_PREFIX."attendance_event 
                      WHERE token = '{$task->token}' AND event_type = 2 
                      ORDER BY date_time_event DESC LIMIT 1";

            $lastEntryResult = $db->query($query);
            $lastEntry = $lastEntryResult->fetch_object();

            if ($lastEntry) {
                $lastEntryTime = $db->jdate($lastEntry->date_time_event);
                $exitTime = $db->jdate($task->date_time_event);
                $elapsedTime = $exitTime - $lastEntryTime;

                $total_time_seconds += $elapsedTime;
            }
        }
    }

    $hours = floor($total_time_seconds / 3600);
    $minutes = floor(($total_time_seconds % 3600) / 60);
    $seconds = $total_time_seconds % 60;


    print '<tr class="liste_titre">';
    print '<td colspan="6"><strong>Proyecto: </strong><a href="' . DOL_URL_ROOT . '/projet/card.php?id=' . $project['projectid'] . '">' . $projectDetails->title . '</a> <strong>Total: </strong>' . $hours . ' h ' . $minutes . ' m ' . $seconds . ' s</td>';
    print '</tr>';

    $totalTasks = count($project['tasks']);
    
    //Paaginar solo para el proyecto actual
    $totalPages = ceil($totalTasks / $perPage);
    $startIndex = ($page - 1) * $perPage;
    $projectTasks = array_slice($project['tasks'], $startIndex, $perPage);

    //Tabla de tareas
    print '<tr class="liste_titre">';
    print_liste_field_titre('Eventtype', $PHP_SELF, 't.event_type', '', $param, '', $sortfield, $sortorder);
    print_liste_field_titre('User', $PHP_SELF, 't.fk_userid', '', $param, '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Inicio"), $_SERVER["PHP_SELF"], "t.date_time_event", '', $param, '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Tiempo transcurrido"), $_SERVER["PHP_SELF"], "", "", $param, '', '', '');
    print_liste_field_titre($langs->trans("Nota"), $_SERVER["PHP_SELF"], "t.note", '', $param, '', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Acciones"), $_SERVER["PHP_SELF"], "", "", $param, '', '', '');
    print '</tr>';
    
    //Tareas del proyecto
    foreach ($projectTasks as $task) {
        $userDetails = new User($db);
        $userDetails->fetch($task->fk_userid);

        print '<tr>';
        print '<td>' . ($task->event_type == 1 || $task->event_type == 2 ? 'Entrada' : ($task->event_type == 3 ? 'Salida' : '')) . '</td>';
        

        print '<td>' . $userDetails->getFullName($langs) . '</td>';

        $date_start = '';
        if (!empty($task->date_time_event)) {
            $dateTime = new DateTime($task->date_time_event);
            $dateTime->setTimezone(new DateTimeZone('CET'));
            $date_start = $dateTime->format('Y-m-d H:i:s');
        }
        
        
        print '<td>' . $date_start . '</td>';
        
        if ($task->event_type == 1 || $task->event_type == 2) { // Entrada
            print '<td>--</td>';
        } elseif ($task->event_type == 3) { // Salida
            
            $query = "SELECT date_time_event FROM ".MAIN_DB_PREFIX."attendance_event 
                      WHERE token = '{$task->token}' AND event_type = 2 
                      ORDER BY date_time_event DESC LIMIT 1"; 
            
            $lastEntryResult = $db->query($query);
            $lastEntry = $lastEntryResult->fetch_object();
    
            if ($lastEntry) {
                $lastEntryTime = $db->jdate($lastEntry->date_time_event);
                $exitTime = $db->jdate($task->date_time_event);
                $elapsedTime = $exitTime - $lastEntryTime;
    
                $hours = floor($elapsedTime / 3600);
                $minutes = floor(($elapsedTime % 3600) / 60);
                $seconds = $elapsedTime % 60;
    
                print '<td>' . sprintf('%02d h %02d m %02d s', $hours, $minutes, $seconds) . '</td>';
            } else {
                print '<td>--</td>'; 
            }
        } else {
            print '<td>--</td>';
        }

        print '<td>' . $task->note . '</td>';
        
        print '<td>';
        print '<a class="fas fa-clock pictodelete" title="Imputar tiempo" href="' . $_SERVER["PHP_SELF"] . '?action=edit&id=' . $task->rowid . '"></a>';
        print '<a class="fas fa-trash pictodelete" title="Eliminar" href="' . $_SERVER["PHP_SELF"] . '?action=delete&token=' . $task->token . '"></a>';
        print '</td>';

        print '</tr>';
    }

    // PAGINACION
    if ($totalPages > 1) {
        print '<tr><td colspan="6" style="text-align:center;">';
    
        // Agregar enlace "<<" (primera página)
        if ($page > 1) {
            $firstPageUrl = $_SERVER["PHP_SELF"] . '?page=1';
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $firstPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            print '<a href="' . $firstPageUrl . '">&laquo;</a> ';
        }
    
        // Agregar enlace "<" (página anterior)
        if ($page > 1) {
            $prevPageUrl = $_SERVER["PHP_SELF"] . '?page=' . ($page - 1);
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $prevPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            print '<a href="' . $prevPageUrl . '">&lsaquo;</a> ';
        }
    
        // Generar enlaces para cada página
        for ($i = 1; $i <= $totalPages; $i++) {
            $paginationUrl = $_SERVER["PHP_SELF"] . '?page=' . $i;
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $paginationUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            if ($i == $page) {
                print '<strong>' . $i . '</strong> ';
            } else {
                print '<a href="' . $paginationUrl . '">' . $i . '</a> ';
            }
        }
    
        // Agregar enlace ">" (página siguiente)
        if ($page < $totalPages) {
            $nextPageUrl = $_SERVER["PHP_SELF"] . '?page=' . ($page + 1);
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $nextPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            print '<a href="' . $nextPageUrl . '">&rsaquo;</a> ';
        }
    
        // Agregar enlace ">>" (última página)
        if ($page < $totalPages) {
            $lastPageUrl = $_SERVER["PHP_SELF"] . '?page=' . $totalPages;
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $lastPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            print '<a href="' . $lastPageUrl . '">&raquo;</a> ';
        }
    
        print '</td></tr>';
    }
    

}

print '</table>';



ob_end_flush();

llxFooter();
$db->close();
?>
