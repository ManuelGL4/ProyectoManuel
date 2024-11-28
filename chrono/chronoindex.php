<?php

/*
 * ACCIONES
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/project.lib.php';

if (!$user->rights->projet->lire) {
    accessforbidden();
}

$user_id = $user->id;
if (isset($_POST['button_removefilter'])) {
    // Limpiar los filtros
    $search_project_ref = '';
    $search_task_label = '';
} else {
    $search_project_ref = isset($_POST['search_project_ref']) ? trim($_POST['search_project_ref']) : '';
    $search_task_label = isset($_POST['search_task_label']) ? trim($_POST['search_task_label']) : '';
}

// obtener proyectos/tareas donde el usuario esté asignado
$sql = 'SELECT DISTINCT 
            p.rowid AS projectid, 
            p.ref AS projectref, 
            p.title AS projecttitle, 
            p.fk_statut AS projectstatus, 
            p.datee AS projectdatee, 
            p.fk_opp_status, 
            p.public, 
            p.fk_user_creat AS projectusercreate, 
            p.usage_bill_time, 
            s.nom AS name, 
            s.rowid AS socid, 
            t.datec AS date_creation, 
            t.dateo AS date_start, 
            t.datee AS date_end, 
            t.tms AS date_update, 
            t.rowid AS id, 
            t.ref, 
            t.label, 
            t.planned_workload, 
            t.duration_effective, 
            t.progress, 
            t.fk_statut, 
            t.description, 
            t.fk_task_parent
        FROM 
            ' . MAIN_DB_PREFIX . 'projet AS p
        LEFT JOIN 
            ' . MAIN_DB_PREFIX . 'societe AS s ON p.fk_soc = s.rowid
        LEFT JOIN 
            ' . MAIN_DB_PREFIX . 'projet_task AS t ON t.fk_projet = p.rowid
        LEFT JOIN 
            ' . MAIN_DB_PREFIX . 'element_contact AS ecp ON ecp.element_id = p.rowid 
        LEFT JOIN 
            ' . MAIN_DB_PREFIX . 'element_contact AS ect ON ect.element_id = t.rowid
        WHERE 
            ecp.fk_socpeople = ' . $user_id . ' 
            AND ect.fk_socpeople = ' . $user_id . '  
';

if (!empty($search_project_ref)) {
    $sql .= " AND (p.ref LIKE '%" . $db->escape($search_project_ref) . "%' OR p.title LIKE '%" . $db->escape($search_project_ref) . "%')";
}

if (!empty($search_task_label)) {
    $sql .= " AND t.label LIKE '%" . $db->escape($search_task_label) . "%'";
}
$sql .= ' ORDER BY p.rowid;';

$resql = $db->query($sql);
if (!$resql) {
    dol_print_error($db);
}

// Agrupacion de tareas por proyectos
$projects = [];
while ($obj = $db->fetch_object($resql)) {
    // Si el proyecto no existe en el array
    if (!isset($projects[$obj->projectid])) {
        $projects[$obj->projectid] = [
            'ref' => $obj->projectref,
            'title' => $obj->projecttitle,
            'tasks' => [],
            'projectid' => $obj->projectid
        ];
    }
    $projects[$obj->projectid]['tasks'][] = $obj;
}

if (isset($_GET['parado'])) {
    setEventMessages(array('Temporizador detenido'), array(), 'mesgs');
}

if (isset($_GET['iniciado'])) {
    setEventMessages(array('Temporizador iniciado'), array(), 'mesgs');
}
print '<style>
#confirmModal{
display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);
}
.bodyModal{
    background: white; margin: 15% auto; padding: 20px; border-radius: 5px; width: 300px; text-align: center;
}
    .project-container {
    margin-bottom: 30px;
}
.project-title {
    background-color: #C0C0C0;
    padding: 10px;
    border-radius: 10px;
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: bold;
}
.task-container {
    background-color: #D3D3D3;
    border-radius: 20px;
    padding: 20px;
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.task-container .status-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    cursor: pointer;
}
.task-container .task-info {
    flex-grow: 1;
}
.task-container .task-info h3 {
    margin: 0;
    font-size: 18px;
}
.task-container .task-info p {
    margin: 0;
    color: #333;
}
.task-container .notes {
    flex-basis: 30%;
    background-color: #ffffff;
    padding: 10px;
    border-radius: 15px;
}
.task-controls {
    display: flex;
    gap: 10px;
}


.time-buttons {
    display: flex;
    flex-direction: column;
    margin-right: 20px;
}
.time-buttons button {
    margin-bottom: 10px;
}




@media (max-width: 768px) {
    table.noborder.centpercent {
        display: block;
        width: 110%;
    }

    table.noborder.centpercent .liste_titre_filter {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    table.noborder.centpercent td.liste_titre {
        flex: 1 1 100%;
        box-sizing: border-box;
        padding: 5px;
        margin-bottom: 26px;
    }

    table.noborder.centpercent input.flat {
        width: 100%;
        max-width: 100%;
    }

    table.noborder.centpercent button {
        width: 100%;
        max-width: 100px;
        margin: 5px auto;

    }
    button#resetButton{
        display: none;
    }


    table.liste tr:last-of-type td, table.noborder:not(#tablelines) tr:last-of-type td, table.formdoc tr:last-of-type td, div.noborder tr:last-of-type td {
        border-bottom-width: 0px !important;
        border-bottom-color: none !important; 
        border-bottom-style: none !important; 
    }
}




</style>';

/** VISTA */
$title = $langs->trans('UserProjectsAndTasks');
$langs->load('projects');

print '<link rel="stylesheet" type="text/css" href="index.css">';

llxHeader('', $title);
print load_fiche_titre($langs->trans('Chrono'), '', 'object_informacion_formacion.png@recursoshumanos');
print '<script src="script.js"></script>';

print '<form name="buscar" method="POST" action="' . $_SERVER['PHP_SELF'] . ($project->id > 0 ? '?id=' . $project->id : '') . '">';
print '<table class = "liste" width = "100%">' . "\n";
print '<tr class = "liste_titre">';
print_liste_field_titre('Nombre del proyecto', $PHP_SELF, '`p.name', '', $filters, '');
print_liste_field_titre('Nombre de la tarea', $PHP_SELF, 't.name', '', $filters, '');
print_liste_field_titre('');

print '</tr>' . "\n";
print '<tr class = "liste_titre">';

print '<td class = "liste_titre" colspan = "1" >';
print ' <input class="flat" type="text" name="search_project_ref" value="' . htmlspecialchars($search_project_ref) . '">';
print '</td>';

print '<td class = "liste_titre" colspan = "1" >';
print '<input class="flat" type="text" name="search_task_label" value="' . htmlspecialchars($search_task_label) . '">';
print '</td>';

print '<td class = "liste_titre" colspan = "1" >';
print '<button type="submit" class="liste_titre button_search" name="button_search_x" value="x">
                                <span class="fa fa-search"></span>
                            </button>
                            <button type="submit" class="liste_titre button_removefilter" name="button_removefilter" value="1">
                                <span class="fa fa-remove"></span>
                            </button>';
print '</td>';



print '</tr>';
print '</table>
</form>
<br>';

print '<input type="hidden" name="usuario_id" value="' . $user->id . '">';

// Tabla de  proyectos y sus tareas
foreach ($projects as $project) {
    print "<div class='project-container'>";
    print "<div class='project-title'>" . $project['title'] . " - <a href='" . DOL_URL_ROOT . '/projet/card.php?id=' . $project['projectid'] . "'>" . $project['ref'] . '</a></div>';

    foreach ($project['tasks'] as $task) {
        print "<div class='task-container'>";
        print "<div class='status-icon' id='icon-" . $task->id . "'>";
        print "<img src='img/notstarted.png' alt='Iniciar' style='height: 40px;' onclick='startTimer(" . intval($task->id) . ', "' . htmlspecialchars($user->api_key) . '", "' . intval($user->id) . '", ' . intval($project['projectid']) . ")'>";

        print '</div>';
        print "<div class='task-info'>";
        print "<h3><a href='" . DOL_URL_ROOT . '/projet/tasks/task.php?id=' . $task->id . "'>" . $task->label . '</a></h3>';
        print "<p>Hora de inicio: <span id='start-time-" . $task->id . "'>no iniciado</span></p>";
        print "<p>Tiempo transcurrido: <span id='time-" . $task->id . "'>no iniciado</span></p>";
        print '</div>';
        print "<div class='task-controls'>";
        print "<div class='status-icon' id='reset-" . $task->id . "' style='display: none;' onclick='resetTimer(" . $task->id . ")'>";
        print "<img src='img/reinicio.png' alt='Reiniciar' style='height: 40px; display: none;'>";
        print '</div>';
        print '</div>';
        print "<input type='hidden' data-task-id='" . $task->id . "' value='" . $project['projectid'] . "'>";
        print "<input type='text' class='notes' placeholder='Notas adicionales' data-task-id='" . $task->id . "'>";
        print '</div>';
    }

    print '</div>';
}

print '<div class="">';
print '<div class="time">';
print '<button class=" button-cancel butAction" id="current-time">Hora actual: 00:00:00</button>';
print '<button class=" button-cancel butAction" id="total-time">Tiempo total: 0h 0m 0s</button>';
print '</div>';
print '</div>';
print '</form>';

llxFooter();
$db->close();
?>
