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

    // Usar el método público de Chrono para obtener proyectos
    $projects = $this->model->getProjectsForUser($user_id, $search_project_ref, $search_task_label);

    if (!$projects) {
        dol_print_error($this->model->getDb()); // Usa getDb() para obtener el objeto de la base de datos
    }


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
    public function obtenerProyectosTareas($user,$filter) {
        // Recibir los filtros de la solicitud (GET, POST, etc.)
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
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'sortorder' => isset($_GET['sortorder']) ? $_GET['sortorder'] : 'DESC',
            'sortfield' => isset($_GET['sortfield']) ? $_GET['sortfield'] : 't.rowid'
        ];

        // Obtener proyectos y tareas desde el modelo
        $projects = $this->model->obtenerProyectosTareas($user,$filter);


        return $projects;
    }

    function obtenerProyectosConFiltros($user, $filters, $sortOrder = 'DESC', $sortField = 't.rowid', $perPage = 25, $page = 1) {
/*        $db=$this->model->getDb();
    
        // Calcular el offset para la paginación
        $offset = ($page - 1) * $perPage;
    
        // Inicializar la consulta básica
        $sqlUpd = "SELECT DISTINCT t.rowid, t.*, p.title AS project_title, pt.label AS task_label 
                   FROM khns_attendance_event AS t
                   INNER JOIN " . MAIN_DB_PREFIX . "projet AS p ON t.fk_project = p.rowid
                   INNER JOIN " . MAIN_DB_PREFIX . "projet_task AS pt ON p.rowid = pt.fk_projet
                   WHERE 1 = 1 ";
        
        if (!$user->admin) {
            $sqlUpd .= " AND t.fk_userid = " . intval($user->id);
        }
        
        if (isset($filters['filter_type'])) {
            if ($filters['filter_type'] === 'all') {
                if (isset($filters['ls_userid']) && intval($filters['ls_userid']) > 0) {
                    $userid = intval($filters['ls_userid']);
                    $sqlUpd .= " AND t.fk_userid = $userid";
                }
            } elseif ($filters['filter_type'] === 'my_records') {
                $sqlUpd .= " AND t.fk_userid = " . intval($user->id);
            } elseif ($filters['filter_type'] === 'restricted') {
                $sqlUpd .= " AND t.fk_userid = " . intval($user->id);
            }
        }
    
        // Filtro por tipo de evento
        if (isset($filters['ls_event_type']) && intval($filters['ls_event_type']) !== 0) {
            $eventype = intval($filters['ls_event_type']);
            $sqlUpd .= " AND t.event_type = $eventype"; 
        }
    
        // Filtro por referencia de ubicación
        if (isset($filters['ls_event_location_ref']) && !empty($filters['ls_event_location_ref'])) {
            $eventLocationRef = $db->escape($filters['ls_event_location_ref']); // Escapa el valor para evitar inyecciones SQL
            $sqlUpd .= " AND p.ref LIKE '%$eventLocationRef%'";
        }
    
        // Filtro por nombre de proyecto
        if (isset($filters['ls_nombre_proyecto']) && !empty($filters['ls_nombre_proyecto'])) {
            $nombre = $db->escape($filters['ls_nombre_proyecto']); // Escapa el valor para evitar inyecciones SQL
            $sqlUpd .= " AND p.title LIKE '%$nombre%'";
        }
    
        // Filtro por referencia de tarea
        if (isset($filters["ls_ref_tarea"]) && !empty($filters["ls_ref_tarea"])) {
            $tarea_ref = $db->escape($filters["ls_ref_tarea"]);
            $sqlUpd .= " AND pt.ref LIKE '%$tarea_ref%'";
        }
    
        // Filtro por nombre de tarea
        if (isset($filters["ls_nombre_tarea"]) && !empty($filters["ls_nombre_tarea"])) {
            $tarea_nom = $db->escape($filters["ls_nombre_tarea"]);
            $sqlUpd .= " AND pt.label LIKE '%$tarea_nom%'";
        }
    
        // Filtro por fecha y hora
        if (isset($filters["ls_date_time"]) && !empty($filters["ls_date_time"])) {
            $date_time = $db->escape($filters["ls_date_time"]);
    
            // Modificar la consulta para buscar por la fecha (ignorando la hora)
            $sqlUpd .= " AND DATE(t.date_time_event) = '".$date_time."'";
        }
    



        






        // Agrupar por `t.rowid`
        $sqlUpd .= " GROUP BY t.rowid";
    
        // Ordenar y aplicar paginación
        $sqlUpd .= " ORDER BY $sortField $sortOrder 
                     LIMIT $offset, $perPage"; 
    
       // Ejecutar la consulta y retornar los resultados
        $result = $db->query($sqlUpd);
        if ($result) {
            $projects = [];
            while ($obj = $db->fetch_object($result)) {
                $projects[] = $obj;  // Almacenar cada objeto stdClass
            }
            return $projects;  // Devolver los proyectos como un array de objetos stdClass
        } else {
            dol_print_error($db);
            return false;  // Si la consulta falla, devolver false
        }
*/

        $projects = $this->model->obtenerProyectosConFiltros($user, $filters, $sortOrder, $sortField, $perPage, $page);
        return $projects; // Devolver los proyectos
    }






    public function countFilteredRecords($user, $filters){
        // $db = $this->model->getDb();

        // // Base de la consulta para contar registros
        // $countSql = 'SELECT COUNT(*) as total FROM ' . MAIN_DB_PREFIX . 'attendance_event as t';
        // $countSql .= ' JOIN ' . MAIN_DB_PREFIX . 'user as u ON t.fk_userid = u.rowid';
        // $countSql .= ' JOIN ' . MAIN_DB_PREFIX . 'projet as p ON t.fk_project = p.rowid';
        // $countSql .= ' JOIN ' . MAIN_DB_PREFIX . 'projet_task as pt ON t.fk_task = pt.rowid';
        // $countSql .= ' WHERE 1 = 1';

        // // Filtro por tipo de evento
        // if (isset($filters['ls_event_type']) && intval($filters['ls_event_type']) !== 0) {
        //     $eventype = intval($filters['ls_event_type']);
        //     $countSql .= " AND t.event_type = $eventype"; 
        // }

        // // Filtro por tipo de registros
        // if (isset($filters['filter_type']) && $filters['filter_type'] === 'all') {
        //     if (isset($filters['ls_userid']) && intval($filters['ls_userid']) > 0) {
        //         $userid = intval($filters['ls_userid']);
        //         $countSql .= " AND t.fk_userid = $userid";
        //     }
        // } elseif (isset($filters['filter_type']) && $filters['filter_type'] === 'my_records') {
        //     $countSql .= " AND t.fk_userid = " . intval($user->id);
        // } else {
        //     if (!$user->admin) {
        //         $countSql .= " AND t.fk_userid = " . intval($user->id);
        //     }
        // }

        // // Filtro por nombre de proyecto
        // if (isset($filters['ls_nombre_proyecto']) && !empty($filters['ls_nombre_proyecto'])) {
        //     $nombre = $db->escape($filters['ls_nombre_proyecto']);
        //     $countSql .= " AND p.title LIKE '%$nombre%'";
        // }

        // // Filtro por referencia de tarea
        // if (isset($filters['ls_ref_tarea']) && !empty($filters['ls_ref_tarea'])) {
        //     $tarea_ref = $db->escape($filters['ls_ref_tarea']);
        //     $countSql .= " AND pt.ref LIKE '%$tarea_ref%'";
        // }

        // // Filtro por nombre de tarea
        // if (isset($filters['ls_nombre_tarea']) && !empty($filters['ls_nombre_tarea'])) {
        //     $tarea_nom = $db->escape($filters['ls_nombre_tarea']);
        //     $countSql .= " AND pt.label LIKE '%$tarea_nom%'";
        // }

        // // Filtro por fecha
        // if (isset($filters['ls_date_time']) && !empty($filters['ls_date_time'])) {
        //     $date_time = $db->escape($filters['ls_date_time']);
        //     $countSql .= " AND DATE(t.date_time_event) = '" . $date_time . "'";
        // }

        // Ejecutar la consulta
        // $resqlCount = $db->query($countSql);
        // if ($resqlCount) {
        //     $totalRecords = ($db->num_rows($resqlCount) > 0) ? $db->fetch_object($resqlCount)->total : 0;
        //     return $totalRecords;
        // } else {
        //     dol_print_error($db);
        //     return 0;
        // }
        $totalRecords = $this->model->countFilteredRecords($user, $filters);
        return $totalRecords;
    }


    public function delete($token) {
        $result = $this->model->delete($token);

        if ($result['status']) {
            setEventMessages(array("El registro del tiempo ha sido borrado correctamente."), array(), 'mesgs');
        } else {
            setEventMessages(array('Error al borrar el registro del tiempo en attendance_event'), array(), 'errors');
        }
        
    }
    


    function obtenerDatosEdicion( $id) {
        // $db = $this->model->getDb();
        // // Obtener el registro específico de attendance_event
        // $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "attendance_event WHERE rowid = " . intval($id);
        // $resql = $db->query($sql);
        // if ($resql && $db->num_rows($resql) > 0) {
        //     $dat = $db->fetch_object($resql);
    
        //     // Obtener el proyecto relacionado
        //     $sqlProyecto = "SELECT title FROM " . MAIN_DB_PREFIX . "projet WHERE rowid = " . intval($dat->fk_project);
        //     $resqlProyecto = $db->query($sqlProyecto);
        //     $proyecto = $db->fetch_object($resqlProyecto);
    
        //     // Obtener el usuario relacionado
        //     $sqlUsuario = "SELECT lastname, firstname FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . intval($dat->fk_userid);
        //     $resqlUsuario = $db->query($sqlUsuario);
        //     $usuario = $db->fetch_object($resqlUsuario);
    
        //     // Obtener la tarea relacionada
        //     $sqlTarea = "SELECT label FROM " . MAIN_DB_PREFIX . "projet_task WHERE rowid = " . intval($dat->fk_task);
        //     $resqlTarea = $db->query($sqlTarea);
        //     $tarea = $db->fetch_object($resqlTarea);
    
        //     // Calcular el tiempo transcurrido si es una salida
        //     if ($dat->event_type == 3) {
        //         $sqlUltimaEntrada = "SELECT date_time_event FROM " . MAIN_DB_PREFIX . "attendance_event WHERE token = '" . $db->escape($dat->token) . "' AND event_type IN (1, 2) ORDER BY date_time_event DESC LIMIT 1";
        //         $resqlUltimaEntrada = $db->query($sqlUltimaEntrada);
        //         if ($resqlUltimaEntrada && $db->num_rows($resqlUltimaEntrada) > 0) {
        //             $entrada = $db->fetch_object($resqlUltimaEntrada);
        //             $lastEntryTime = $db->jdate($entrada->date_time_event);
        //             $exitTime = $db->jdate($dat->date_time_event);
    
        //             // Calcular el tiempo transcurrido
        //             $elapsedTime = $exitTime - $lastEntryTime;
    
        //             // Convertir a horas, minutos y segundos
        //             $hours = floor($elapsedTime / 3600);
        //             $minutes = floor(($elapsedTime % 3600) / 60);
        //             $seconds = $elapsedTime % 60;
    
        //             // Formatear el tiempo transcurrido como "H:M:S"
        //             $tiempoFormateado = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        //         } else {
        //             $tiempoFormateado = '00:00:00'; // Si no hay entrada, mostrar 0
        //         }
        //     } else {
        //         $tiempoFormateado = '00:00:00'; // Para entradas no mostrar tiempo transcurrido
        //     }
    
        //     // Retornar todos los datos obtenidos
        //     return [
        //         'dat' => $dat,
        //         'proyecto' => $proyecto,
        //         'usuario' => $usuario,
        //         'tarea' => $tarea,
        //         'tiempoTranscurrido' => $tiempoFormateado
        //     ];
        // }
        // return false;
        return $this->model->obtenerDatosEdicion($id);


    }
    


// Método para editar el registro de una tarea
function editarTarea($id, $fecha_inicio, $nota) {
    $resultado = $this->model->editarTarea($id, $fecha_inicio, $nota);

    if (isset($resultado['error'])) {
        setEventMessage($resultado['error'], 'errors');
    } elseif (isset($resultado['success'])) {
        setEventMessage($resultado['success'], 'mesgs');
    }
}


function getProjectsAndTasks($db, $user) {
    
    $db=$this->model->getDb();

    // Iniciar la consulta base
    $sqlUpd = "SELECT t.rowid, t.*, p.title AS project_title, pt.label AS task_label
               FROM ".MAIN_DB_PREFIX."attendance_event AS t
               INNER JOIN " . MAIN_DB_PREFIX . "projet AS p ON t.fk_project = p.rowid
               INNER JOIN " . MAIN_DB_PREFIX . "projet_task AS pt ON t.fk_task = pt.rowid
               WHERE 1 = 1 ";

    // FILTRADO
    if (isset($_GET['filter_type']) && $_GET['filter_type'] === 'all') {
        if (isset($_GET['ls_userid']) && intval($_GET['ls_userid']) > 0) {
            $userid = intval($_GET['ls_userid']);
            $sqlUpd .= " AND t.fk_userid = $userid"; 
        }
    } elseif (isset($_GET['filter_type']) && $_GET['filter_type'] === 'assigned') {
        $sqlUpd .= " AND t.fk_userid = " . intval($user->id);
    } else {
        if (!$user->admin) {
            $sqlUpd .= " AND t.fk_userid = " . intval($user->id);
        }
    }

    // Filtros adicionales
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

    if (isset($_GET["ls_ref_tarea"]) && !empty($_GET["ls_ref_tarea"])) {
        $tarea_ref = $db->escape($_GET["ls_ref_tarea"]);
        $sqlUpd .= " AND pt.ref LIKE '%$tarea_ref%'";
    }

    if (isset($_GET["ls_nombre_tarea"]) && !empty($_GET["ls_nombre_tarea"])) {
        $tarea_nom = $db->escape($_GET["ls_nombre_tarea"]);
        $sqlUpd .= " AND pt.label LIKE '%$tarea_nom%'";
    }

    if (isset($_GET["ls_date_time"]) && !empty($_GET["ls_date_time"])) {
        $date_time = $db->escape($_GET["ls_date_time"]);
        $sqlUpd .= " AND DATE(t.date_time_event) = '".$date_time."'";
    }
    $sortorder = 'DESC';
    $sortfield = 't.rowid';
    $sqlUpd .= " GROUP BY t.rowid";
    if (isset($_GET["sortfield"]) && !empty($_GET["sortfield"]))  {
        $sortfield = $_GET["sortfield"];
    }

    if (isset($_GET["sortorder"]) && !empty($_GET["sortorder"]))  {
        $sortorder = $_GET["sortorder"];
    }

    // PAGINACION EN LA CONSULTA

 $sqlUpd .= "
                 ORDER BY $sortfield $sortorder 
                  "; 
    // Ejecutar la consulta
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

    // Inicializar agrupación de proyectos y tiempo total
    $groupedProjects = [];
    $projectTotalTime = [];

    // Agrupar proyectos con sus tareas
    foreach ($projects as $project) {
        if (!isset($groupedProjects[$project->fk_project])) {
            $groupedProjects[$project->fk_project] = [
                'projectid' => $project->fk_project,
                'tasks' => [],
            ];
            $projectTotalTime[$project->fk_project] = 0;
        }

        // Agregar la tarea si no está ya en la lista de tareas del proyecto
        if (!in_array($project, $groupedProjects[$project->fk_project]['tasks'], true)) {
            $groupedProjects[$project->fk_project]['tasks'][] = $project;
        }

        // Sumar el tiempo transcurrido
        $projectTotalTime[$project->fk_project] += $project->tiempo_transcurrido;
    }

    // Devolver los resultados
    return [
        'groupedProjects' => $groupedProjects,
        'projectTotalTime' => $projectTotalTime
    ];
}

    

}
?>
