<?php
use App\Chrono;

require_once 'Chrono.php';

class ChronoController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Chrono($db);
    }

    public function listProjects($user, $search_project_ref = '', $search_task_label = '')
    {
        $user_id = $user->id;

        $projects = $this->model->getProjectsForUser($user_id, $search_project_ref, $search_task_label);

        // Agrupar proyectos
        $groupedProjects = [];
        foreach ($projects as $project) {
            if (!isset($groupedProjects[$project->projectid])) {
                $groupedProjects[$project->projectid] = [
                    'ref' => $project->ref,
                    'title' => $project->title,
                    'tasks' => [],
                ];
            }

            $groupedProjects[$project->projectid]['tasks'][] = $project;
        }

        return $groupedProjects;
    }

    public function getAssignedProjects($user)
    {
        $user_id = $user->id;
        $projects = $this->model->getProjectsAssignedToUser($user_id);

        return $projects;
    }

    public function obtenerProyectosTareas($user, $filter)
    {
        $filters = [
            'filter_type' => isset($_GET['filter_type']) ? $_GET['filter_type'] : null,
            'ls_userid' => isset($_GET['ls_userid']) ? $_GET['ls_userid'] : null,
            'ls_event_type' => isset($_GET['ls_event_type']) ? $_GET['ls_event_type'] : null,
            'ls_event_location_ref' => isset($_GET['ls_event_location_ref']) ? $_GET['ls_event_location_ref'] : null,
            'ls_nombre_proyecto' => isset($_GET['ls_nombre_proyecto']) ? $_GET['ls_nombre_proyecto'] : null,
            'ls_ref_tarea' => isset($_GET['ls_ref_tarea']) ? $_GET['ls_ref_tarea'] : null,
            'ls_nombre_tarea' => isset($_GET['ls_nombre_tarea']) ? $_GET['ls_nombre_tarea'] : null,
            'ls_date_time' => isset($_GET['ls_date_time']) ? $_GET['ls_date_time'] : null,
            'perPage' => isset($_GET['perPage']) ? $_GET['perPage'] : 25,
            'page' => isset($_GET['page']) ? (int) $_GET['page'] : 1,
            'sortorder' => isset($_GET['sortorder']) ? $_GET['sortorder'] : 'DESC',
            'sortfield' => isset($_GET['sortfield']) ? $_GET['sortfield'] : 't.rowid'
        ];

        $projects = $this->model->obtenerProyectosTareas($user, $filter);

        return $projects;
    }

    function obtenerProyectosConFiltros($user, $filters, $sortOrder = 'DESC', $sortField = 't.rowid', $perPage = 25, $page = 1)
    {
        $projects = $this->model->obtenerProyectosConFiltros($user, $filters, $sortOrder, $sortField, $perPage, $page);
        return $projects;
    }

    public function countFilteredRecords($user, $filters)
    {
        $totalRecords = $this->model->countFilteredRecords($user, $filters);
        return $totalRecords;
    }

    public function delete($token)
    {
        $result = $this->model->delete($token);

        if ($result['status']) {
            setEventMessages(array('El registro del tiempo ha sido borrado correctamente.'), array(), 'mesgs');
        } else {
            setEventMessages(array('Error al borrar el registro del tiempo en attendance_event'), array(), 'errors');
        }
    }

    function obtenerDatosEdicion($id)
    {
        return $this->model->obtenerDatosEdicion($id);
    }

    function editarTarea($id, $fecha_inicio, $nota)
    {
        $resultado = $this->model->editarTarea($id, $fecha_inicio, $nota);

        if (isset($resultado['error'])) {
            setEventMessage($resultado['error'], 'errors');
        } elseif (isset($resultado['success'])) {
            setEventMessage($resultado['success'], 'mesgs');
        }
    }

    function getProjectsAndTasks($db, $user)
    {
        $db = $this->model->getDb();

        $sqlUpd = 'SELECT t.rowid, t.*, p.title AS project_title, pt.label AS task_label
               FROM ' . MAIN_DB_PREFIX . 'attendance_event AS t
               INNER JOIN ' . MAIN_DB_PREFIX . 'projet AS p ON t.fk_project = p.rowid
               INNER JOIN ' . MAIN_DB_PREFIX . 'projet_task AS pt ON t.fk_task = pt.rowid
               WHERE 1 = 1 ';

        if (isset($_GET['filter_type']) && $_GET['filter_type'] === 'all') {
            if (isset($_GET['ls_userid']) && intval($_GET['ls_userid']) > 0) {
                $userid = intval($_GET['ls_userid']);
                $sqlUpd .= " AND t.fk_userid = $userid";
            }
        } elseif (isset($_GET['filter_type']) && $_GET['filter_type'] === 'assigned') {
            $sqlUpd .= ' AND t.fk_userid = ' . intval($user->id);
        } else {
            if (!$user->admin) {
                $sqlUpd .= ' AND t.fk_userid = ' . intval($user->id);
            }
        }

        if (isset($_GET['ls_event_type']) && intval($_GET['ls_event_type']) !== 0) {
            $eventype = intval($_GET['ls_event_type']);
            $sqlUpd .= " AND t.event_type = $eventype";
        }

        if (isset($_GET['ls_event_location_ref']) && !empty($_GET['ls_event_location_ref'])) {
            $eventLocationRef = $db->escape($_GET['ls_event_location_ref']);
            $sqlUpd .= " AND p.ref LIKE '%$eventLocationRef%'";
        }

        if (isset($_GET['ls_nombre_proyecto']) && !empty($_GET['ls_nombre_proyecto'])) {
            $nombre = $db->escape($_GET['ls_nombre_proyecto']);
            $sqlUpd .= " AND p.title LIKE '%$nombre%'";
        }

        if (isset($_GET['ls_ref_tarea']) && !empty($_GET['ls_ref_tarea'])) {
            $tarea_ref = $db->escape($_GET['ls_ref_tarea']);
            $sqlUpd .= " AND pt.ref LIKE '%$tarea_ref%'";
        }

        if (isset($_GET['ls_nombre_tarea']) && !empty($_GET['ls_nombre_tarea'])) {
            $tarea_nom = $db->escape($_GET['ls_nombre_tarea']);
            $sqlUpd .= " AND pt.label LIKE '%$tarea_nom%'";
        }

        if (isset($_GET['ls_date_time']) && !empty($_GET['ls_date_time'])) {
            $date_time = $db->escape($_GET['ls_date_time']);
            $sqlUpd .= " AND DATE(t.date_time_event) = '" . $date_time . "'";
        }
        $sortorder = 'DESC';
        $sortfield = 't.rowid';
        $sqlUpd .= ' GROUP BY t.rowid';
        if (isset($_GET['sortfield']) && !empty($_GET['sortfield'])) {
            $sortfield = $_GET['sortfield'];
        }

        if (isset($_GET['sortorder']) && !empty($_GET['sortorder'])) {
            $sortorder = $_GET['sortorder'];
        }

        $sqlUpd .= "
                 ORDER BY $sortfield $sortorder 
                  ";
        $resultUpd = $db->query($sqlUpd);
        if ($resultUpd) {
            $projects = [];
            while ($obj = $db->fetch_object($resultUpd)) {
                $projects[] = $obj;
            }
        } else {
            dol_print_error($db);
            return false;
        }

        $groupedProjects = [];
        $projectTotalTime = [];

        // Agrupar proyectos con tareas
        foreach ($projects as $project) {
            if (!isset($groupedProjects[$project->fk_project])) {
                $groupedProjects[$project->fk_project] = [
                    'projectid' => $project->fk_project,
                    'tasks' => [],
                ];
                $projectTotalTime[$project->fk_project] = 0;
            }

            // Agregar la tarea si no esta en la lista de tareas del proyecto
            if (!in_array($project, $groupedProjects[$project->fk_project]['tasks'], true)) {
                $groupedProjects[$project->fk_project]['tasks'][] = $project;
            }

            // Sumar el tiempo transcurrido
            $projectTotalTime[$project->fk_project] += $project->tiempo_transcurrido;
        }

        return [
            'groupedProjects' => $groupedProjects,
            'projectTotalTime' => $projectTotalTime
        ];
    }
}
?>
