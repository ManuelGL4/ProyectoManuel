<?php

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
require_once '../chrono/class/tiempotarea.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/chrono/app/ChronoController.php';

$controller = new ChronoController($db);

if (!$user->rights->projet->lire) {
    accessforbidden();
}

$title = $langs->trans('Listado tiempo tareas');
$langs->load('projects');

llxHeader('', $title);

$user_id = $user->id;
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
.admin-filter {
text-align: center; margin-bottom: 20px;
}

.title-modal{
border-bottom: 1px solid #ddd; padding: 10px;
}
.contenido-modal{
width: auto; min-height: 0px; max-height: none; height: 290px; border:none !important;
}
.tabla-modal{
width: 100%; margin-bottom: 10px;
}
.tabla-modal td input{
width: 100%;
padding: 5px;
}
.edit{
background-color: #5bc0de !important; color: white !important; padding: 10px 20px !important; border: none !important; border-radius: 4px !important;
}
.back{
    background-color: #d9534f !important; color: white !important; padding: 10px 20px !important; border: none !important; border-radius: 4px !important;
    }
    .modal-footer{
display: flex; gap: 10px; justify-content: center; margin-top: 20px;
    }
.form-delete{
background: white; padding: 20px; border-radius: 8px; width: 400px; text-align: center;
}

@media (min-width: 500px) and (max-width: 768px) {
table.liste th {
    position: -webkit-sticky;
    position: sticky;
    top: 0;
    background-color: #fff; 
    z-index: 1; 
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
}

table.liste {
    width: 100%; 
    border-collapse: collapse;
}

table.liste th, table.liste td {
    padding: 10px; 
    border: 1px solid #ddd; 
}
    .table-container {
        max-height: 700px;
        overflow-y: auto;
    }


}



</style>';

print '<script>
function calcularTiempoTranscurrido() {
    // Obtener la fecha seleccionada
    const fechaInicio = document.getElementById("fecha_inicio").value;
    if (!fechaInicio) return;

    const fechaInicioDate = new Date(fechaInicio);

    // Obtener la fecha y hora actual
    const fechaActual = new Date();

    // Calcular la diferencia de tiempo en milisegundos
    const diff = fechaActual - fechaInicioDate;

    // Convertir la diferencia de milisegundos a horas, minutos y segundos
    const horas = Math.floor(diff / 3600000);
    const minutos = Math.floor((diff % 3600000) / 60000);
    const segundos = Math.floor((diff % 60000) / 1000);

    // Formatear el tiempo en HH:MM:SS
    const tiempoFormateado = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;

    // Mostrar el tiempo transcurrido
    document.getElementById("tiempo_transcurrido").value = tiempoFormateado;
}

// Función para agregar un cero al inicio si es menor a 10
function pad(num) {
    return num < 10 ? "0" + num : num;
}
</script>';

if ($_GET['action'] == 'edit') {
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
                <div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle title-modal" >
                    <span id="ui-id-1" class="ui-dialog-title">Edición de Horas</span>
                </div>
                <div  class="ui-dialog-content ui-widget-content contenido-modal">
                    <div class="confirmquestions"></div>
                    <div>
                        <table class="tabla-modal">
                            <tr>
                                <td><span class="field">Usuario</span></td>
                                <td><input type="text" name="usuario" value="' . htmlspecialchars($usuario->firstname . ' ' . $usuario->lastname) . '" readonly ></td>
                            </tr>
                            <tr>
                                <td><span class="field">Nombre del proyecto</span></td>
                                <td><input type="text" name="proyecto" value="' . htmlspecialchars($proyecto->title) . '" readonly ></td>
                            </tr>
                            <tr>
                                <td><span class="field">Tarea</span></td>
                                <td><input type="text" name="tarea" value="' . htmlspecialchars($tarea->label) . '" readonly ></td>
                            </tr>';

        if ($user->admin) {
            print '
                            <tr>
                                <td><span class="field">Fecha/Hora de ' . ($dat->event_type == 2 ? 'entrada' : 'salida') . '</span></td>
                                <td>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="' . ($dat->date_time_event) . '" >
                                    <div id="error-fecha" style="color: red; font-size: 12px; display: none;">Por favor, ingrese una fecha válida.</div>
                                </td>
                            </tr>';
        } else {
            print '
                            <tr>
                                <td><span class="field">Fecha/Hora de ' . ($dat->event_type == 2 ? 'entrada' : 'salida') . '</span></td>
                                <td>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" value="' . ($dat->date_time_event) . '" readonly >
                                </td>
                            </tr>';
        }

        print '
                            <tr>
                                <td><span class="field">Tiempo Transcurrido (H:M:S)</span></td>
                                <td><input type="text" name="tiempo_transcurrido" value="' . htmlspecialchars($tiempoFormateado) . '" placeholder="HH:MM:SS" readonly ></td>
                            </tr>
                            <tr>
                                <td><span class="field">Nota</span></td>
                                <td>
                                    <input type="text" id="nota" name="nota" value="' . htmlspecialchars($dat->note) . '" >
                                    <div id="error-nota" style="color: red; font-size: 12px; display: none;">La nota no puede exceder los 255 caracteres.</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="ui-dialog-buttonset modal-footer" >
                    <button type="submit" class="ui-button ui-corner-all ui-widget edit" name="edit" >
                        Guardar
                    </button>
                    <button type="button" class="ui-button ui-corner-all ui-widget back" onclick="window.history.back();" >
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

/* SI SE DECIDE BORRAR */
if ($_GET['action'] == 'delete') {
    // Obtener el token
    $token = $_GET['token'];

    print '
    <div class="overlay">
        <form method="POST" action="' . $_SERVER['PHP_SELF'] . '?token=' . $token . '" name="formfilter" autocomplete="off" class="form-delete">
                    <span id="ui-id-1" class="ui-dialog-title">Borrar Hora Imputada</span>
                    <button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close" onclick="window.history.back();" style="background: none; border: none; cursor: pointer;">
                        <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
                    </button>
                    <br><br>
                    <p>¿Desea borrar esta hora imputada?</p>

                    <div class="ui-dialog-buttonset modal-footer" >
                        <button type="submit" class="ui-button ui-corner-all ui-widget back" name="Borrar" >
                            Borrar
                        </button>
                        <button type="button" class="ui-button ui-corner-all ui-widget edit " onclick="window.history.back();">
                            Cancelar
                        </button>
                </div>
            </div>
        </form>
    </div>';
}

// Si se pulsa en confirmar borrado
if (isset($_POST['Borrar'])) {
    $token = $_GET['token'];
    $controller->delete($token);
}

function formatTime($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':'
        . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':'
        . str_pad($seconds, 2, '0', STR_PAD_LEFT);
}

$filters = [];

// Filtrado según el tipo de filtro
if (isset($_GET['filter_type'])) {
    $filters['filter_type'] = $_GET['filter_type'];

    if ($_GET['filter_type'] === 'all') {
        if (isset($_GET['ls_userid']) && intval($_GET['ls_userid']) > 0) {
            $filters['ls_userid'] = intval($_GET['ls_userid']);
        }
    } elseif ($_GET['filter_type'] === 'my_records') {
        // Filtra solo por el usuario actual
        $filters['filter_type'] = 'my_records';  // Solo se añade el tipo 'my_records'
    } else {
        if (!$user->admin) {
            $filters['filter_type'] = 'restricted';  // Se marca como restringido en caso de no ser admin
        }
    }
}

// Filtro por tipo de evento
if (isset($_GET['ls_event_type']) && intval($_GET['ls_event_type']) !== 0) {
    $filters['ls_event_type'] = intval($_GET['ls_event_type']);
}

// Filtro por referencia de ubicación
if (isset($_GET['ls_event_location_ref']) && !empty($_GET['ls_event_location_ref'])) {
    $filters['ls_event_location_ref'] = $_GET['ls_event_location_ref'];
}

// Filtro por nombre de proyecto
if (isset($_GET['ls_nombre_proyecto']) && !empty($_GET['ls_nombre_proyecto'])) {
    $filters['ls_nombre_proyecto'] = $_GET['ls_nombre_proyecto'];
}

// Filtro por referencia de tarea
if (isset($_GET['ls_ref_tarea']) && !empty($_GET['ls_ref_tarea'])) {
    $filters['ls_ref_tarea'] = $_GET['ls_ref_tarea'];
}

// Filtro por nombre de tarea
if (isset($_GET['ls_nombre_tarea']) && !empty($_GET['ls_nombre_tarea'])) {
    $filters['ls_nombre_tarea'] = $_GET['ls_nombre_tarea'];
}

// Filtro por fecha y hora
if (isset($_GET['ls_date_time']) && !empty($_GET['ls_date_time'])) {
    $filters['ls_date_time'] = $_GET['ls_date_time'];
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 25;
$sortorder = isset($_GET['sortorder']) && !empty($_GET['sortorder']) ? $_GET['sortorder'] : 'desc';
$sortfield = isset($_GET['sortfield']) && !empty($_GET['sortfield']) ? $_GET['sortfield'] : 't.rowid';

$projects = $controller->obtenerProyectosConFiltros($user, $filters, $sortorder, $sortfield, $perPage, $page);

if ($projects === -1) {
    dol_print_error($db);
}

$totalRecords = $controller->countFilteredRecords($user, $filters);
$totalPages = ceil($totalRecords / $perPage);

print_barre_liste($title, $page, $_SERVER['PHP_SELF'], '', $sortfield, $sortorder, '', $totalRecords, $totalRecords, 'title_companies', 0, '', '', '');

$isAdmin = $user->admin;
if ($totalPages > 1) {
    print '<div class="pagination" style="text-align: center;">';

    // "<<"
    if ($page > 1) {
        $firstPageUrl = $_SERVER['PHP_SELF'] . '?page=1';
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $firstPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $firstPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&laquo;</a>';
    }

    // "<"
    if ($page > 1) {
        $prevPageUrl = $_SERVER['PHP_SELF'] . '?page=' . ($page - 1);
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $prevPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $prevPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&lsaquo;</a>';
    }

    // paginas
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $page || ($i >= $page - 1 && $i <= $page + 1)) {
            $paginationUrl = $_SERVER['PHP_SELF'] . '?page=' . $i;
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $paginationUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            if ($i === $page) {
                print '<strong style="font-size: 1.2em; margin: 0 10px;">' . $i . '</strong>';
            } else {
                print '<a href="' . $paginationUrl . '" style="font-size: 1.2em; margin: 0 10px;">' . $i . '</a>';
            }
        } elseif ($i == 1 || $i == $totalPages) {
            $paginationUrl = $_SERVER['PHP_SELF'] . '?page=' . $i;
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $paginationUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            print '<a href="' . $paginationUrl . '" style="font-size: 1.2em; margin: 0 10px;">' . $i . '</a>';
        } elseif ($i == $page - 2 || $i == $page + 2) {
            print '<span style="font-size: 1.2em; margin: 0 10px;">...</span>';
        }
    }

    if ($page < $totalPages) {
        $nextPageUrl = $_SERVER['PHP_SELF'] . '?page=' . ($page + 1);
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $nextPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $nextPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&rsaquo;</a>';
    }

    if ($page < $totalPages) {
        $lastPageUrl = $_SERVER['PHP_SELF'] . '?page=' . $totalPages;
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $lastPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $lastPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&raquo;</a>';
    }

    print '</div>';
}

print '</tr>';
print '<form method = "GET" action = "">';
if ($user->admin) {
    print '<div class="admin-filter">';
    print '<select name="filter_type" onchange="this.form.submit()" style="padding: 5px;">';
    print '<option value="all" ' . (isset($_GET['filter_type']) && $_GET['filter_type'] === 'all' ? 'selected' : '') . '>Ver todos los tiempos de todos los usuarios</option>';
    print '<option value="my_records" ' . (isset($_GET['filter_type']) && $_GET['filter_type'] === 'my_records' ? 'selected' : '') . '>Ver Tiempos Tareas Asignadas</option>';
    print '</select>';
    print '</div>';
}
print '<div class="div-table-responsive table-container" >';
print '<table class = "liste" width = "100%">' . "\n";

print '<tr class = "liste_titre">';
print_liste_field_titre('Eventtype', $PHP_SELF, 't.event_type', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre('User', $PHP_SELF, 't.fk_userid', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('ProjectRef'), $_SERVER['PHP_SELF'], 'p.ref', '', '', '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Nombre del proyecto'), $_SERVER['PHP_SELF'], 'p.title', '', '', '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Referencia de tarea'), $_SERVER['PHP_SELF'], 'pt.ref', '', '', '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Nombre de tarea'), $_SERVER['PHP_SELF'], 'pt.label', '', '', '', $sortfield, $sortorder);
print_liste_field_titre('Date', $_SERVER['PHP_SELF'], 't.date_time_event', '', '', '', $sortfield, $sortorder);
print "\n";
print_liste_field_titre($langs->trans('Tiempo transcurrido'), $_SERVER['PHP_SELF'], '', '', '', '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Note'), $_SERVER['PHP_SELF'], 't.note', '', '', '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Acciones'), $_SERVER['PHP_SELF'], '', '', '', '', $sortfield, $sortorder);

print "\n";
print '</tr>';
print '<tr class = "liste_titre">';

$ls_event_type = isset($_GET['ls_event_type']) ? intval($_GET['ls_event_type']) : 0;
$ls_userid = isset($_GET['ls_userid']) ? htmlspecialchars($_GET['ls_userid']) : '';
$ls_event_location_ref = isset($_GET['ls_event_location_ref']) ? htmlspecialchars($_GET['ls_event_location_ref']) : '';
$ls_nombre_proyecto = isset($_GET['ls_nombre_proyecto']) ? htmlspecialchars($_GET['ls_nombre_proyecto']) : '';
$ls_ref_tarea = isset($_GET['ls_ref_tarea']) ? htmlspecialchars($_GET['ls_ref_tarea']) : '';
$ls_nombre_tarea = isset($_GET['ls_nombre_tarea']) ? htmlspecialchars($_GET['ls_nombre_tarea']) : '';
$ls_date_time = isset($_GET['ls_date_time']) ? htmlspecialchars($_GET['ls_date_time']) : '';

print '<td class="liste_titre" colspan="1">';
print '<select name="ls_event_type" class="litre_titre">';
print '<option value="0"' . ($ls_event_type === 0 ? ' selected' : '') . '>-</option>';
print '<option value="2"' . ($ls_event_type === 2 ? ' selected' : '') . '>Entrada</option>';
print '<option value="3"' . ($ls_event_type === 3 ? ' selected' : '') . '>Salida</option>';
print '</select>';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print $form->select_dolusers($ls_userid, 'ls_userid', 1, '', 0);
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input class="flat" size="16" type="text" name="ls_event_location_ref" value="' . $ls_event_location_ref . '">';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input class="flat" size="16" type="text" name="ls_nombre_proyecto" value="' . $ls_nombre_proyecto . '">';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input class="flat" size="16" type="text" name="ls_ref_tarea" value="' . $ls_ref_tarea . '">';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input class="flat" size="16" type="text" name="ls_nombre_tarea" value="' . $ls_nombre_tarea . '">';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input class="flat" type="date" name="ls_date_time" value="' . $ls_date_time . '">';
print '</td>';

print '<td class = "liste_titre" colspan = "1" >';
print '</td>';
print '<td class = "liste_titre" colspan = "1" />';
print '<td >';
print '<button class="butAction" type="submit">Buscar</button>';

print '</td>';
print '</tr>' . "\n";
$lastEntryTime = null;

if (!empty($projects)) {
    foreach ($projects as $project) {
        $projectDetails = new Project($db);
        $projectDetails->fetch($project->fk_project);

        $taskDetails = new Task($db);
        $taskDetails->fetch($project->fk_task);

        $userDetails = new User($db);
        $userDetails->fetch($project->fk_userid);

        print '<tr>';
        print '<td>' . ($project->event_type == 1 || $project->event_type == 2 ? 'Entrada' : ($project->event_type == 3 ? 'Salida' : '')) . '</td>';
        print '<td>' . $userDetails->getFullName($langs) . '</td>';
        print '<td><a href="' . DOL_URL_ROOT . '/projet/card.php?id=' . $project->fk_project . '">' . $projectDetails->ref . '</a></td>';
        print '<td>' . $projectDetails->title . '</td>';
        print '<td><a href="' . DOL_URL_ROOT . '/projet/tasks/task.php?id=' . $project->fk_task . '">' . $taskDetails->ref . '</a></td>';
        print '<td><a href="' . DOL_URL_ROOT . '/projet/tasks/task.php?id=' . $project->fk_task . '">' . $taskDetails->label . '</a></td>';

        $date_start = '';
        if (!empty($project->date_time_event)) {
            $dateTime = new DateTime($project->date_time_event);
            $dateTime->setTimezone(new DateTimeZone('CET'));
            $date_start = $dateTime->format('Y-m-d H:i:s');
        }

        print '<td>' . $date_start . '</td>';

        if ($project->event_type == 1 || $project->event_type == 2) {  // Es una entrada
            print '<td>--</td>';
        } elseif ($project->event_type == 3) {
            $query = 'SELECT date_time_event FROM ' . MAIN_DB_PREFIX . "attendance_event 
                      WHERE token = '{$project->token}' AND event_type = 2 
                      ORDER BY date_time_event DESC LIMIT 1";

            $lastEntryResult = $db->query($query);
            $lastEntry = $lastEntryResult->fetch_object();

            if ($lastEntry) {
                $lastEntryTime = $db->jdate($lastEntry->date_time_event);
                $exitTime = $db->jdate($project->date_time_event);
                // Segundos de tiempo transcurrido
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

        $total_time_seconds = $project->tiempo_transcurrido;
        $hours = floor($total_time_seconds / 3600);
        $minutes = floor(($total_time_seconds % 3600) / 60);
        $seconds = $total_time_seconds % 60;

        print '<td>' . $project->note . '</td>';
        print '<td>';
        print '<a class="fas fa-clock pictodelete" style="" title="Editar tiempo" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $project->rowid . '"></a>';
        print '<a class="fas fa-trash pictodelete" style="" title="Eliminar" href="' . $_SERVER['PHP_SELF'] . '?action=delete&token=' . $project->token . '"></a>';
        print '</td>';
        print '</tr>';
    }
}

print '</table>';
print '</div>';

llxFooter();
?>
