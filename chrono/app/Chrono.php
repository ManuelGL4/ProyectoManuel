<?php
namespace App;

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';

class Chrono
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
    public function getDb()
    {
        return $this->db;
    }
    public function getProjectsForUser($user_id, $search_project_ref = '', $search_task_label = '')
    {
        $sql = "SELECT DISTINCT 
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
                FROM ".MAIN_DB_PREFIX."projet AS p
                LEFT JOIN ".MAIN_DB_PREFIX."societe AS s ON p.fk_soc = s.rowid
                LEFT JOIN ".MAIN_DB_PREFIX."projet_task AS t ON t.fk_projet = p.rowid
                LEFT JOIN ".MAIN_DB_PREFIX."element_contact AS ecp ON ecp.element_id = p.rowid
                LEFT JOIN ".MAIN_DB_PREFIX."element_contact AS ect ON ect.element_id = t.rowid
                WHERE 1=1";
    
        // Añadir filtros condicionalmente
        if (!empty($user_id)) {
            $sql .= " AND ecp.fk_socpeople = " . intval($user_id);
            $sql .= " AND ect.fk_socpeople = " . intval($user_id);
        }
    
        if (!empty($search_project_ref)) {
            $sql .= " AND p.ref LIKE '%" . $this->db->escape($search_project_ref) . "%'";
        }
    
        if (!empty($search_task_label)) {
            $sql .= " AND t.label LIKE '%" . $this->db->escape($search_task_label) . "%'";
        }
    
        $sql .= " ORDER BY p.rowid";
    
        $resql = $this->db->query($sql);
        if (!$resql) {
            dol_print_error($this->db);
            return [];
        }
    
        $projects = [];
        while ($obj = $this->db->fetch_object($resql)) {
            $projects[] = $obj;
        }
    
        return $projects;
    }
    
    public function getProjectsAssignedToUser($user_id)
    {
        $sql = "SELECT DISTINCT p.rowid 
                FROM " . MAIN_DB_PREFIX . "projet AS p
                LEFT JOIN " . MAIN_DB_PREFIX . "element_contact AS ecp ON ecp.element_id = p.rowid 
                WHERE ecp.fk_socpeople = " . $user_id;

        return $this->db->query($sql);
    }
    public function obtenerProyectosTareas($user, $filters) {
        // Construir la consulta base
        $sql = "SELECT t.rowid, t.*, p.title AS project_title, pt.label AS task_label
           FROM ".MAIN_DB_PREFIX."attendance_event AS t
            INNER JOIN " . MAIN_DB_PREFIX . "projet AS p ON t.fk_project = p.rowid
            INNER JOIN " . MAIN_DB_PREFIX . "projet_task AS pt ON t.fk_task = pt.rowid
            WHERE 1 = 1 ";

        // Filtros pasados como parámetros
        if (isset($filters['filter_type']) && $filters['filter_type'] === 'all') {
            if (isset($filters['ls_userid']) && intval($filters['ls_userid']) > 0) {
                $sql .= " AND t.fk_userid = " . intval($filters['ls_userid']);
            }
        } elseif (isset($filters['filter_type']) && $filters['filter_type'] === 'assigned') {
            $sql .= " AND t.fk_userid = " . intval($user->id);
        } else {
            if (!$user->admin) {
                $sql .= " AND t.fk_userid = " . intval($user->id);
            }
        }





        if (isset($filters['ls_event_type']) && intval($filters['ls_event_type']) !== 0) {
            $sql .= " AND t.event_type = " . intval($filters['ls_event_type']);
        }

        if (isset($filters['ls_event_location_ref']) && !empty($filters['ls_event_location_ref'])) {
            $sql .= " AND p.ref LIKE '%" . $this->db->escape($filters['ls_event_location_ref']) . "%'";
        }

        if (isset($filters['ls_nombre_proyecto']) && !empty($filters['ls_nombre_proyecto'])) {
            $sql .= " AND p.title LIKE '%" . $this->db->escape($filters['ls_nombre_proyecto']) . "%'";
        }

        if (isset($filters["ls_ref_tarea"]) && !empty($filters["ls_ref_tarea"])) {
            $sql .= " AND pt.ref LIKE '%" . $this->db->escape($filters["ls_ref_tarea"]) . "%'";
        }

        if (isset($filters["ls_nombre_tarea"]) && !empty($filters["ls_nombre_tarea"])) {
            $sql .= " AND pt.label LIKE '%" . $this->db->escape($filters["ls_nombre_tarea"]) . "%'";
        }

        if (isset($filters["ls_date_time"]) && !empty($filters["ls_date_time"])) {
            $sql .= " AND DATE(t.date_time_event) = '" . $this->db->escape($filters["ls_date_time"]) . "'";
        }

        // Paginación
        $perPage = isset($filters['perPage']) ? $filters['perPage'] : 25;
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $offset = ($page - 1) * $perPage;
        $sortorder = isset($filters['sortorder']) ? $filters['sortorder'] : 'DESC';
        $sortfield = isset($filters['sortfield']) ? $filters['sortfield'] : 't.rowid';

        $sql .= " ORDER BY $sortfield $sortorder LIMIT $offset, $perPage";

        // Ejecutar la consulta
        $result = $this->db->query($sql);

        if ($result) {
            $projects = [];
            while ($obj = $this->db->fetch_object($result)) {
                $projects[] = $obj;
            }
            return $projects;
        } else {
            dol_print_error($this->db);
            return [];
        }
    }








    function getAssignedProjects($user_id, $view_all_projects, $db) {
        if ($view_all_projects ) {
            // Si es administrador y selecciona ver todos los proyectos
            $sql_projects = "SELECT DISTINCT p.rowid 
                             FROM " . MAIN_DB_PREFIX . "projet AS p";
        } else {
            // Si es un usuario normal, solo los proyectos asignados
            $sql_projects = "SELECT DISTINCT p.rowid 
                             FROM " . MAIN_DB_PREFIX . "projet AS p
                             LEFT JOIN " . MAIN_DB_PREFIX . "element_contact AS ecp ON ecp.element_id = p.rowid 
                             WHERE ecp.fk_socpeople = " . $user_id;
        }
    
        $resql_projects = $db->query($sql_projects);
        $project_ids = [];
        if ($resql_projects) {
            while ($obj = $db->fetch_object($resql_projects)) {
                $project_ids[] = $obj->rowid;
            }
        }
    
        return $project_ids;
    }

    function buildTaskQuery($user, $db) {
        $sqlUpd = "SELECT t.rowid, t.*, p.title AS project_title, pt.label AS task_label
                   FROM ".MAIN_DB_PREFIX."attendance_event AS t
                   INNER JOIN " . MAIN_DB_PREFIX . "projet AS p ON t.fk_project = p.rowid
                   INNER JOIN " . MAIN_DB_PREFIX . "projet_task AS pt ON t.fk_task = pt.rowid
                   WHERE 1 = 1 ";
    
        // Filtros basados en la URL
        if (isset($_GET['filter_type']) && $_GET['filter_type'] === 'all') {
            if (isset($_GET['ls_userid']) && intval($_GET['ls_userid']) > 0) {
                $userid = intval($_GET['ls_userid']);
                $sqlUpd .= " AND t.fk_userid = $userid"; 
            }
        } elseif (isset($_GET['filter_type']) && $_GET['filter_type'] === 'assigned') {
            $sqlUpd .= " AND t.fk_userid = " . intval($user->id);
        } else {
            // En caso de no ser admin ver solo sus registros
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
    
        // Agrupamiento y ordenación
        $sqlUpd .= " GROUP BY t.rowid";
        return $sqlUpd;
    }
    
    function getProjectsWithTasks($sqlUpd, $db, $perPage = 25) {
        // Paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $perPage;
    
        $sortorder = isset($_GET['sortorder']) ? $_GET['sortorder'] : 'DESC';
        $sortfield = isset($_GET['sortfield']) ? $_GET['sortfield'] : 't.rowid';
    
        $sqlUpd .= " ORDER BY $sortfield $sortorder LIMIT $offset, $perPage";
    
        $resultUpd = $db->query($sqlUpd);
        $projects = [];
        if ($resultUpd) {
            while ($obj = $db->fetch_object($resultUpd)) {
                $projects[] = $obj;
            }
        } else {
            dol_print_error($db);
        }
    
        return $projects;
    }
    
    function groupProjectsAndSumTime($projects) {
        $groupedProjects = [];
        $projectTotalTime = [];
    
        // Se agrupan los proyectos con sus tareas
        foreach ($projects as $project) {
            if (!isset($groupedProjects[$project->fk_project])) {
                $groupedProjects[$project->fk_project] = [
                    'projectid' => $project->fk_project,
                    'tasks' => [],
                ];
                $projectTotalTime[$project->fk_project] = 0;
            }
    
            // Agrega la tarea si no está ya en la lista de tareas del proyecto
            if (!in_array($project, $groupedProjects[$project->fk_project]['tasks'], true)) {
                $groupedProjects[$project->fk_project]['tasks'][] = $project;
            }
    
            // Suma el tiempo transcurrido
            $projectTotalTime[$project->fk_project] += $project->tiempo_transcurrido;
        }
    
        return [$groupedProjects, $projectTotalTime];
    }




    function obtenerProyectosConFiltros($user, $filters, $sortOrder = 'DESC', $sortField = 't.rowid', $perPage = 25, $page = 1) {
    
        // Calcular el offset para la paginación
        $offset = ($page - 1) * $perPage;
    
        // Inicializar la consulta básica
        $sqlUpd = "SELECT DISTINCT t.rowid, t.*, p.title AS project_title, pt.label AS task_label 
                   FROM ".MAIN_DB_PREFIX."attendance_event AS t
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
            $eventLocationRef = $this->db->escape($filters['ls_event_location_ref']); // Escapa el valor para evitar inyecciones SQL
            $sqlUpd .= " AND p.ref LIKE '%$eventLocationRef%'";
        }
    
        // Filtro por nombre de proyecto
        if (isset($filters['ls_nombre_proyecto']) && !empty($filters['ls_nombre_proyecto'])) {
            $nombre = $this->db->escape($filters['ls_nombre_proyecto']); // Escapa el valor para evitar inyecciones SQL
            $sqlUpd .= " AND p.title LIKE '%$nombre%'";
        }
    
        // Filtro por referencia de tarea
        if (isset($filters["ls_ref_tarea"]) && !empty($filters["ls_ref_tarea"])) {
            $tarea_ref = $this->db->escape($filters["ls_ref_tarea"]);
            $sqlUpd .= " AND pt.ref LIKE '%$tarea_ref%'";
        }
    
        // Filtro por nombre de tarea
        if (isset($filters["ls_nombre_tarea"]) && !empty($filters["ls_nombre_tarea"])) {
            $tarea_nom = $this->db->escape($filters["ls_nombre_tarea"]);
            $sqlUpd .= " AND pt.label LIKE '%$tarea_nom%'";
        }
    
        // Filtro por fecha y hora
        if (isset($filters["ls_date_time"]) && !empty($filters["ls_date_time"])) {
            $date_time = $this->db->escape($filters["ls_date_time"]);
    
            // Modificar la consulta para buscar por la fecha (ignorando la hora)
            $sqlUpd .= " AND DATE(t.date_time_event) = '".$date_time."'";
        }
    



        






        // Agrupar por `t.rowid`
        $sqlUpd .= " GROUP BY t.rowid";
    
        // Ordenar y aplicar paginación
        $sqlUpd .= " ORDER BY $sortField $sortOrder 
                     LIMIT $offset, $perPage"; 
    
        // Ejecutar la consulta y retornar los resultados
        $result = $this->db->query($sqlUpd);
        if ($result) {
            $projects = [];
            while ($obj = $this->db->fetch_object($result)) {
                $projects[] = $obj;  // Almacenar cada objeto stdClass
            }
            return $projects;  // Devolver los proyectos como un array de objetos stdClass
        } else {
            dol_print_error($this->db);
            return false;  // Si la consulta falla, devolver false
        }
    
    }





    public function countFilteredRecords($user, $filters){
        $db = $this->db;

        // Base de la consulta para contar registros
        $countSql = 'SELECT COUNT(*) as total FROM ' . MAIN_DB_PREFIX . 'attendance_event as t';
        $countSql .= ' JOIN ' . MAIN_DB_PREFIX . 'user as u ON t.fk_userid = u.rowid';
        $countSql .= ' JOIN ' . MAIN_DB_PREFIX . 'projet as p ON t.fk_project = p.rowid';
        $countSql .= ' JOIN ' . MAIN_DB_PREFIX . 'projet_task as pt ON t.fk_task = pt.rowid';
        $countSql .= ' WHERE 1 = 1';

        // Filtro por tipo de evento
        if (isset($filters['ls_event_type']) && intval($filters['ls_event_type']) !== 0) {
            $eventype = intval($filters['ls_event_type']);
            $countSql .= " AND t.event_type = $eventype"; 
        }

        // Filtro por tipo de registros
        if (isset($filters['filter_type']) && $filters['filter_type'] === 'all') {
            if (isset($filters['ls_userid']) && intval($filters['ls_userid']) > 0) {
                $userid = intval($filters['ls_userid']);
                $countSql .= " AND t.fk_userid = $userid";
            }
        } elseif (isset($filters['filter_type']) && $filters['filter_type'] === 'my_records') {
            $countSql .= " AND t.fk_userid = " . intval($user->id);
        } else {
            if (!$user->admin) {
                $countSql .= " AND t.fk_userid = " . intval($user->id);
            }
        }

        // Filtro por nombre de proyecto
        if (isset($filters['ls_nombre_proyecto']) && !empty($filters['ls_nombre_proyecto'])) {
            $nombre = $db->escape($filters['ls_nombre_proyecto']);
            $countSql .= " AND p.title LIKE '%$nombre%'";
        }

        // Filtro por referencia de tarea
        if (isset($filters['ls_ref_tarea']) && !empty($filters['ls_ref_tarea'])) {
            $tarea_ref = $db->escape($filters['ls_ref_tarea']);
            $countSql .= " AND pt.ref LIKE '%$tarea_ref%'";
        }

        // Filtro por nombre de tarea
        if (isset($filters['ls_nombre_tarea']) && !empty($filters['ls_nombre_tarea'])) {
            $tarea_nom = $db->escape($filters['ls_nombre_tarea']);
            $countSql .= " AND pt.label LIKE '%$tarea_nom%'";
        }

        // Filtro por fecha
        if (isset($filters['ls_date_time']) && !empty($filters['ls_date_time'])) {
            $date_time = $db->escape($filters['ls_date_time']);
            $countSql .= " AND DATE(t.date_time_event) = '" . $date_time . "'";
        }

        // Ejecutar la consulta
        $resqlCount = $db->query($countSql);
        if ($resqlCount) {
            $totalRecords = ($db->num_rows($resqlCount) > 0) ? $db->fetch_object($resqlCount)->total : 0;
            return $totalRecords;
        } else {
            dol_print_error($db);
            return 0;
        }
    }
    public function delete($token) {
        $db = $this->db;
    
        $response = [
            'status' => false,
            'message' => '',
            'deleted_task_time' => false,
            'deleted_attendance' => false,
        ];
    
        $sqlAttendance = "SELECT * FROM " . MAIN_DB_PREFIX . "attendance_event 
                          WHERE token = '" . $token . "' AND event_type = 3;";
    
        $resqlAttendance = $db->query($sqlAttendance);
    
        if ($resqlAttendance && $db->num_rows($resqlAttendance) > 0) {
            $datAttendance = $db->fetch_object($resqlAttendance);
            $formattedDate = date('Y-m-d H:i:s', strtotime($datAttendance->date_time_event));
            $fk_user = $datAttendance->fk_userid;
    
            // Eliminar el registro de projet_task_time
            $sqlDeleteTaskTime = "DELETE FROM " . MAIN_DB_PREFIX . "projet_task_time 
                                  WHERE fk_user = " . intval($fk_user) . " 
                                  AND task_datehour = '" . $db->escape($formattedDate) . "';";
            $resultDeleteTaskTime = $db->query($sqlDeleteTaskTime);
    
            $response['deleted_task_time'] = $resultDeleteTaskTime;
    
            if (!$resultDeleteTaskTime) {
                $response['message'] = 'Error al borrar el registro de tiempo en projet_task_time.';
                return $response;
            }
    
            // Eliminar el registro de attendance_event
            $sqlDeleteAttendance = "DELETE FROM " . MAIN_DB_PREFIX . "attendance_event WHERE token = '" . $token . "';";
            $resultDeleteAttendance = $db->query($sqlDeleteAttendance);
    
            $response['deleted_attendance'] = $resultDeleteAttendance;
    
            if ($resultDeleteAttendance) {
                $response['status'] = true;
                $response['message'] = 'El registro del tiempo ha sido borrado correctamente.';
            } else {
                $response['message'] = 'Error al borrar el registro del tiempo en attendance_event.';
            }
        } else {
            $response['message'] = 'Error: No se encontró el registro en attendance_event con ese token.';
        }
    
        return $response;
    }
    function obtenerDatosEdicion( $id) {
        $db = $this->db;
        // Obtener el registro específico de attendance_event
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "attendance_event WHERE rowid = " . intval($id);
        $resql = $db->query($sql);
        if ($resql && $db->num_rows($resql) > 0) {
            $dat = $db->fetch_object($resql);
    
            // Obtener el proyecto relacionado
            $sqlProyecto = "SELECT title FROM " . MAIN_DB_PREFIX . "projet WHERE rowid = " . intval($dat->fk_project);
            $resqlProyecto = $db->query($sqlProyecto);
            $proyecto = $db->fetch_object($resqlProyecto);
    
            // Obtener el usuario relacionado
            $sqlUsuario = "SELECT lastname, firstname FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . intval($dat->fk_userid);
            $resqlUsuario = $db->query($sqlUsuario);
            $usuario = $db->fetch_object($resqlUsuario);
    
            // Obtener la tarea relacionada
            $sqlTarea = "SELECT label FROM " . MAIN_DB_PREFIX . "projet_task WHERE rowid = " . intval($dat->fk_task);
            $resqlTarea = $db->query($sqlTarea);
            $tarea = $db->fetch_object($resqlTarea);
    
            // Calcular el tiempo transcurrido si es una salida
            if ($dat->event_type == 3) {
                $sqlUltimaEntrada = "SELECT date_time_event FROM " . MAIN_DB_PREFIX . "attendance_event WHERE token = '" . $db->escape($dat->token) . "' AND event_type IN (1, 2) ORDER BY date_time_event DESC LIMIT 1";
                $resqlUltimaEntrada = $db->query($sqlUltimaEntrada);
                if ($resqlUltimaEntrada && $db->num_rows($resqlUltimaEntrada) > 0) {
                    $entrada = $db->fetch_object($resqlUltimaEntrada);
                    $lastEntryTime = $db->jdate($entrada->date_time_event);
                    $exitTime = $db->jdate($dat->date_time_event);
    
                    // Calcular el tiempo transcurrido
                    $elapsedTime = $exitTime - $lastEntryTime;
    
                    // Convertir a horas, minutos y segundos
                    $hours = floor($elapsedTime / 3600);
                    $minutes = floor(($elapsedTime % 3600) / 60);
                    $seconds = $elapsedTime % 60;
    
                    // Formatear el tiempo transcurrido como "H:M:S"
                    $tiempoFormateado = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    $tiempoFormateado = '00:00:00'; // Si no hay entrada, mostrar 0
                }
            } else {
                $tiempoFormateado = '00:00:00'; // Para entradas no mostrar tiempo transcurrido
            }
    
            // Retornar todos los datos obtenidos
            return [
                'dat' => $dat,
                'proyecto' => $proyecto,
                'usuario' => $usuario,
                'tarea' => $tarea,
                'tiempoTranscurrido' => $tiempoFormateado
            ];
        }
        return false;
    }

    function editarTarea($id, $fecha_inicio, $nota) {
        $db = $this->db; // Obtener la conexión a la base de datos
    
        // Validación básica
        if (empty($fecha_inicio)) {
            return ['error' => 'La fecha de inicio no puede estar vacía.'];
        }
    
        // Obtener el registro específico de attendance_event
        $sqlAttendance = "SELECT * FROM " . MAIN_DB_PREFIX . "attendance_event WHERE rowid = " . intval($id);
        $resqlAttendance = $db->query($sqlAttendance);
    
        if ($resqlAttendance && $db->num_rows($resqlAttendance) > 0) {
            $datAttendance = $db->fetch_object($resqlAttendance);
            $eventType = $datAttendance->event_type;
            $token = $db->escape($datAttendance->token);
            $userStartTime = strtotime($fecha_inicio);
            $isTimeValid = true;
            $otherDateTimeTimestamp = null;
    
            // Buscar el evento relacionado (entrada o salida) con el mismo token y tipo opuesto
            if ($eventType == 2) { // Es una entrada
                $sqlOtherEvent = "SELECT * FROM " . MAIN_DB_PREFIX . "attendance_event 
                                  WHERE fk_userid = " . intval($datAttendance->fk_userid) . " 
                                  AND token = '" . $token . "' 
                                  AND event_type = 3"; // Salida
                $resqlOtherEvent = $db->query($sqlOtherEvent);
    
                if ($resqlOtherEvent && $db->num_rows($resqlOtherEvent) > 0) {
                    $datOtherEvent = $db->fetch_object($resqlOtherEvent);
                    $otherDateTimeTimestamp = strtotime($datOtherEvent->date_time_event);
    
                    if ($userStartTime > $otherDateTimeTimestamp) {
                        return ['error' => 'La hora de entrada no puede ser posterior a la hora de salida.'];
                    }
                } else {
                    return ['error' => 'No se encontró el evento de salida correspondiente.'];
                }
            } elseif ($eventType == 3) { // Es una salida
                $sqlOtherEvent = "SELECT * FROM " . MAIN_DB_PREFIX . "attendance_event 
                                  WHERE fk_userid = " . intval($datAttendance->fk_userid) . " 
                                  AND token = '" . $token . "' 
                                  AND event_type = 2"; // Entrada
                $resqlOtherEvent = $db->query($sqlOtherEvent);
    
                if ($resqlOtherEvent && $db->num_rows($resqlOtherEvent) > 0) {
                    $datOtherEvent = $db->fetch_object($resqlOtherEvent);
                    $otherDateTimeTimestamp = strtotime($datOtherEvent->date_time_event);
    
                    if ($userStartTime < $otherDateTimeTimestamp) {
                        return ['error' => 'La hora de salida no puede ser anterior a la hora de entrada.'];
                    }
                } else {
                    return ['error' => 'No se encontró el evento de entrada correspondiente.'];
                }
            }
    
            if ($isTimeValid) {
                $formattedDate = date('Y-m-d H:i:s', strtotime($datAttendance->date_time_event));
                $taskDuration = abs($userStartTime - $otherDateTimeTimestamp);
    
                $sqlUpdateTask = "UPDATE " . MAIN_DB_PREFIX . "projet_task_time SET
                    task_datehour = '" . $db->escape($fecha_inicio) . "',
                    note = '" . $db->escape($nota) . "',
                    task_duration = task_duration + " . $taskDuration . "
                    WHERE fk_user = " . intval($datAttendance->fk_userid) . " 
                    AND task_datehour = '" . $db->escape($formattedDate) . "'";
    
                if (!$db->query($sqlUpdateTask)) {
                    return ['error' => 'Error al actualizar projet_task_time: ' . $db->lasterror()];
                }
    
                $sqlUpdateAttendance = "UPDATE " . MAIN_DB_PREFIX . "attendance_event SET
                    date_time_event = '" . $db->escape($fecha_inicio) . "',
                    note = '" . $db->escape($nota) . "',
                    date_modification = NOW()  
                    WHERE rowid = " . intval($id);
    
                if ($db->query($sqlUpdateAttendance)) {
                    return ['success' => 'Registro actualizado correctamente.'];
                } else {
                    return ['error' => 'Error al actualizar attendance_event: ' . $db->lasterror()];
                }
            }
        } else {
            return ['error' => 'No se encontró el registro de la tabla attendance_event.'];
        }
    }
    
}
?>
