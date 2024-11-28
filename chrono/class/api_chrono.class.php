<?php

/*
 * Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024 Comercial ORTRAT <prueba@deltanet.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use App\Chrono;
use Luracast\Restler\RestException;

require_once DOL_DOCUMENT_ROOT . '/custom/chrono/app/Chrono.php';

// require_once '../../timesheet/class/TimesheetAttendanceEvent.class.php';

/**
 * \file    chrono/class/api_chrono.class.php
 * \ingroup chrono
 * \brief   File for API management of chrono.
 */

/**
 * API class for chrono
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class ChronoApi extends DolibarrApi
{
    public $chrono;
    /**
     * Constructor
     *
     * @url     GET /
     */
    public function __construct()
    {
        global $db, $conf;
        $this->db = $db;
        $this->chrono = new Chrono($this->db);
    }


    /**
     * 
     * METODO PARA GENERAR UN TOKEN NUEVO
     * 
     * @return string
     */
    function generateToken()
    {
        $randomBytes = random_bytes(16);

        $token = bin2hex($randomBytes);

        return substr($token, 0, 32); 
    }

    /**
     * Post nuevo tiempo
     *
     * @param  array $request_data Datos del tiempo a insertar en la base de datos
     *
     * @url    POST chrono/
     *
     * @throws RestException 401 Not allowed
     * @throws RestException 404 Not found
     */
    public function insertar($request_data = null)
    {
        global $user;

        if (is_null($request_data) || !is_array($request_data)) {
            throw new RestException(400, 'No data provided or data is not an array');
        }

        $insertedIds = [];
        $now = date('Y-m-d H:i:s');

        $event_location_ref = isset($request_data['event_location_ref']) ? $this->db->escape($request_data['event_location_ref']) : 'NULL';
        $event_type = isset($request_data['event_type']) ? (int) $request_data['event_type'] : 2;
        $note = isset($request_data['note']) ? $this->db->escape($request_data['note']) : 'NULL';
        $fk_third_party = isset($request_data['fk_third_party']) ? (int) $request_data['fk_third_party'] : 'NULL';
        $fk_task = isset($request_data['fk_task']) ? (int) $request_data['fk_task'] : 'NULL';
        $fk_project = isset($request_data['fk_project']) ? (int) $request_data['fk_project'] : 'NULL';
        $status = isset($request_data['status']) ? (int) $request_data['status'] : 'NULL';
        $fk_userid = isset($request_data['fk_userid']) ? (int) $request_data['fk_userid'] : 'NULL';
        $token = $this->generateToken();

        if ($event_type == 3) {  // Si es una salida,se debe de obtener el token de entrada
            $entry_sql = 'SELECT token FROM ' . MAIN_DB_PREFIX . 'attendance_event 
                      WHERE fk_userid = ' . $fk_userid . ' 
                      AND fk_task = ' . $fk_task . ' 
                      AND fk_project = ' . $fk_project . ' 
                      AND event_type = 2 -- Entrada
                      ORDER BY date_time_event DESC LIMIT 1';

            $entry_resql = $this->db->query($entry_sql);

            if ($entry_resql && $entry_row = $this->db->fetch_object($entry_resql)) {
                $entry_token = $entry_row->token;
                $token = $entry_token;
            }
        }

        //Se inserta el registro
        $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . "attendance_event (
                date_time_event,
                event_location_ref,
                event_type,
                note,
                fk_userid,
                fk_user_modification,
                fk_third_party,
                fk_task,
                fk_project,
                status,
                token
            ) VALUES (
                '" . $now . "',              
                '" . $event_location_ref . "',
                " . $event_type . ",       
                '" . $note . "',             
                " . $fk_userid . ',            
                ' . $fk_userid . ',           
                ' . $fk_third_party . ',      
                ' . $fk_task . ',         
                ' . $fk_project . ',           
                ' . $status . ",              
                '" . $token . "'            
            )";

        $resql = $this->db->query($sql);
        if (!$resql) {
            throw new RestException(500, 'Error inserting TiempoTarea in database', [
                'sql_error' => $this->db->lasterror(),
                'field_values' => $request_data
            ]);
        }

        $insertedIds[] = $this->db->last_insert_id('' . MAIN_DB_PREFIX . 'attendance_event');

        if ($event_type == 3) {//Si el evento es salida
            $token = isset($request_data['token']) ? $this->db->escape($request_data['token']) : null;

            if ($event_type == 3 && $token) {  // Si es una salida y el token es válido
                $entry_sql = 'SELECT date_time_event FROM ' . MAIN_DB_PREFIX . "attendance_event 
                          WHERE token = '" . $token . "' AND event_type = 2 
                          ORDER BY date_time_event DESC LIMIT 1";

                $entry_resql = $this->db->query($entry_sql);

                //Obtener la diferencia en segundos 
                if ($entry_resql && $entry_row = $this->db->fetch_object($entry_resql)) {
                    $entry_date_time = $entry_row->date_time_event;

                    $entry_timestamp = strtotime($entry_date_time);
                    $exit_timestamp = strtotime($now);

                    $task_duration = $exit_timestamp - $entry_timestamp;

                    $task_duration = max(0, $task_duration);
                } else {
                    throw new RestException(404, 'No se encontró un evento de entrada correspondiente al token proporcionado');
                }
            } else {
                $task_duration = 0;
            }
            //Seleccionar el coste por hora del usuario
            $user_thm_sql = 'SELECT thm FROM ' . MAIN_DB_PREFIX . 'user WHERE rowid = ' . (int) $fk_userid;
            $user_thm_resql = $this->db->query($user_thm_sql);

            if ($user_thm_resql && $user_thm_row = $this->db->fetch_object($user_thm_resql)) {
                $thm = $user_thm_row->thm;
            } else {
                $thm = NULL;
            }

            $task_date = $now;
            $task_datehour = $now;
            $invoice_id = isset($request_data['invoice_id']) ? (int) $request_data['invoice_id'] : 'NULL';
            $invoice_line_id = isset($request_data['invoice_line_id']) ? (int) $request_data['invoice_line_id'] : 'NULL';
            $import_key = isset($request_data['import_key']) ? $this->db->escape($request_data['import_key']) : 'NULL';
            $status_exit = isset($request_data['status']) ? (int) $request_data['status'] : 1;

            //Se inserta en la tabla de dolibarr para asignarle ese tiempo a la tarea
            $sql_task_time = 'INSERT INTO ' . MAIN_DB_PREFIX . 'projet_task_time (
                            fk_task,
                            task_date,
                            task_datehour,
                            task_date_withhour,
                            task_duration,
                            fk_user,
                            thm,
                            note,
                            invoice_id,
                            invoice_line_id,
                            import_key,
                            datec,
                            status
                        ) VALUES (
                            ' . $fk_task . ",              -- fk_task
                            '" . $task_date . "',         -- task_date
                            '" . $task_datehour . "',     -- task_datehour
                            1, -- task_date_withhour
                            " . $task_duration . ',       -- task_duration
                            ' . $fk_userid . ',           -- fk_user
                            ' . $thm . ",                 -- thm
                            '" . $note . "',              -- note
                            " . $invoice_id . ',          -- invoice_id
                            ' . $invoice_line_id . ",     -- invoice_line_id
                            '" . $import_key . "',        -- import_key
                            '" . $now . "',               -- datec
                            " . $status_exit . '          -- status
                        )';

            $resql_task_time = $this->db->query($sql_task_time);
            if (!$resql_task_time) {
                throw new RestException(500, 'Error inserting task time in database', [
                    'sql_error' => $this->db->lasterror(),
                    'field_values' => $request_data
                ]);
            }
        }

        return [
            'status' => 'success',
            'message' => 'Evento registrado correctamente',
            'data' => [
                'id' => $insertedIds[0],
                'event_location_ref' => $event_location_ref,
                'event_type' => $event_type,
                'fk_task' => $fk_task,
                'fk_userid' => $fk_userid,
                'token' => $token
            ]
        ];
    }

    /**
     * Obtener registros activos para un usuario
     *
     * @param  int $fk_userid El ID del usuario
     *
     * @return array El registro de evento de entrada que no tengan salida (event_type = 2 sin un evento de salida)
     *
     * @throws RestException 404 Not found Si no se encuentran registros
     */
    public function obtenerRegistrosActivos($fk_userid)
    {
        if (!is_int($fk_userid)) {
            throw new RestException(400, 'Parámetros inválidos');
        }

        // Seleccionar el registro que tenga entrada pero NO salida,entonces es esa la tarea activa

        $sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'attendance_event AS entry 
    WHERE fk_userid = ' . $fk_userid . ' 
    AND event_type = 2  
    AND NOT EXISTS (
        SELECT 1 
        FROM ' . MAIN_DB_PREFIX . 'attendance_event AS exit_event 
        WHERE exit_event.fk_userid = ' . $fk_userid . ' 
          AND exit_event.fk_task = entry.fk_task 
          AND exit_event.token = entry.token 
          AND exit_event.event_type = 3
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM ' . MAIN_DB_PREFIX . 'attendance_event AS check_exit 
        WHERE check_exit.fk_userid = ' . $fk_userid . ' 
          AND check_exit.fk_task = entry.fk_task 
          AND check_exit.event_type = 3 
          AND check_exit.date_time_event > entry.date_time_event
    )';

        $resql = $this->db->query($sql);

        if (!$resql) {
            throw new RestException(404, 'No se encontraron registros activos');
        }

        $activeRecords = [];
        while ($row = $this->db->fetch_object($resql)) {
            $activeRecords[] = $row;
        }

        return $activeRecords;
    }

    /**
     * Get list of tiempos with pagination and filtering by userId
     *
     * @param int $userId   ID of the user making the request
     * @param int $offset   Number of records to skip
     * @param int $limit    Number of records to return
     * @return array        Array of TiempoTarea objects
     *
     * @throws RestException
     */
    public function listar($offset = 0, $limit = 10, $fk_user_id)
    {
        global $db;

        $obj_ret = array();


        $userSql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'user WHERE rowid = ' . intval($fk_user_id);
        $userResql = $this->db->query($userSql);

        if ($userResql && $userRow = $this->db->fetch_object($userResql)) {
            $user = $userRow;
        } else {
            throw new RestException(404, 'No se encontró el usuario');
        }

        $sql = "SELECT 
                t.rowid, 
                t.date_time_event, 
                t.event_location_ref, 
                t.event_type, 
                t.note, 
                t.date_modification, 
                t.fk_userid, 
                t.fk_user_modification, 
                t.fk_third_party, 
                t.fk_task, 
                t.fk_project, 
                t.token, 
                t.status,
                CONCAT(u.firstname, ' ', u.lastname) AS user_name,
                ta.label AS task_label  
            FROM " . MAIN_DB_PREFIX . 'attendance_event AS t
            LEFT JOIN ' . MAIN_DB_PREFIX . 'user AS u ON t.fk_userid = u.rowid
            LEFT JOIN ' . MAIN_DB_PREFIX . 'projet_task AS ta ON t.fk_task = ta.rowid';

        if (!$user->admin) {
            $sql .= ' WHERE t.fk_userid = ' . intval($fk_user_id);
        }

        $sql .= ' ORDER BY t.rowid DESC';
        $sql .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);

        $result = $this->db->query($sql);

        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $obj_ret[] = $obj;
            }
        } else {
            throw new RestException(503, 'Error when retrieving tiempos list: ' . $this->db->lasterror());
        }

        if (count($obj_ret) == 0) {
            throw new RestException(404, 'No tiempos found');
        }

        return $obj_ret;
    }

    /**
     * eliminar
     *
     * @param  string $token token del tiempo a eliminar
     *
     * @url    DELETE chrono/
     *
     * @throws RestException 404 Not found
     * @throws RestException 401 Not allowed
     */
    public function eliminar($token)
    {
        $resql=$this->chrono->delete($token);
        if (!$resql) {
            throw new RestException(500, 'Error deleting TiempoTarea in database', [
                'error' => $this->db->lasterror()
            ]);
        }
        return ['status' => 'success', 'message' => 'TiempoTarea eliminado'];
    }

    /**
     * Put para actualizar un tiempo existente
     *
     * @param  int $id ID del tiempo a actualizar
     * @param  datetime $fecha_inicio Fecha de inicio del tiempo
     * @param  string $nota Nota del tiempo
     * @param  int $fk_user ID del usuario que actualiza el tiempo
     *
     * @url    PUT chrono/update/{id}
     *
     * @throws RestException 404 Not found
     * @throws RestException 400 Bad Request
     */
    public function update($id, $fecha_inicio, $nota, $fk_user)
    {
        global $db;
        // Check if user exists
        $userSql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'user WHERE rowid = ' . intval($fk_user);
        $userResql = $this->db->query($userSql);

        if ($userResql && $userRow = $this->db->fetch_object($userResql)) {
            $user = $userRow;
        } else {
            throw new RestException(404, 'No se encontró el usuario');
        }

        // Si el usuario no es administrador no se envia la fecha_inicio
        if (!$user->admin) {
            $sql = 'SELECT date_time_event FROM ' . MAIN_DB_PREFIX . 'attendance_event WHERE rowid = ' . $id;
            $resql = $this->db->query($sql);
            if ($resql && $this->db->num_rows($resql) > 0) {
                $obj = $this->db->fetch_object($resql);
                $fecha_inicio = $obj->date_time_event;
            }
        }

        $controller = new ChronoController($db);
        $resultado = $controller->editarTarea($id, $fecha_inicio, $nota);

        if (isset($resultado['error'])) {
            throw new RestException(400, $resultado['error']);
        } elseif (isset($resultado['success'])) {
            return ['message' => $resultado['success']];
        } else {
            return ['message' => $resultado['success']];
        }
    }

    /**
     * Obtener el estado de los temporizadores
     *
     * @url    GET /timer/state?userid={userid}
     *
     * @param  int $userid ID del usuario para el que se desea obtener el estado
     *
     * @throws RestException 404 Not found
     * @throws RestException 401 Not allowed
     */
    public function getTimerState($userid)
    {
        // Permisos para el usuario
        if (!DolibarrApiAccess::$user->rights->tiempotarea->ver) {
            throw new RestException(401, 'No tienes permiso para ver los tiempos');
        }

        $sql = '
            SELECT 
                SC1.fk_userid AS id_usuario,
                SC1.fk_task AS id_tarea,
                SC1.date_time_event AS hora_inicio,
                COALESCE(MIN(TIMESTAMPDIFF(SECOND, SC1.date_time_event, SC2.date_time_event)), 0) AS duracion_en_segundos
            FROM 
                (SELECT fk_userid, fk_task, date_time_event 
                 FROM ' . MAIN_DB_PREFIX . 'attendance_event
                 WHERE event_type = 2) SC1
            LEFT JOIN 
                (SELECT fk_userid, fk_task, date_time_event 
                 FROM ' . MAIN_DB_PREFIX . "attendance_event
                 WHERE event_type = 3) SC2
            ON 
                SC1.fk_userid = SC2.fk_userid AND SC1.fk_task = SC2.fk_task
            WHERE
                SC1.fk_userid = $userid
            GROUP BY 
                SC1.fk_userid, SC1.fk_task, SC1.date_time_event;
        ";

        $result = $this->db->query($sql);

        if ($result) {
            $obj_ret = [];
            while ($obj = $this->db->fetch_object($result)) {
                $obj_ret[] = [
                    'id_usuario' => $obj->id_usuario,
                    'id_tarea' => $obj->id_tarea,
                    'hora_inicio' => $obj->hora_inicio,
                    'duracion_en_segundos' => $obj->duracion_en_segundos
                ];
            }

            if (!count($obj_ret)) {
                throw new RestException(404, 'No se encontraron temporizadores para el usuario.');
            }

            return $obj_ret;
        } else {
            throw new RestException(503, 'Error al recuperar el estado de los temporizadores: ' . $this->db->lasterror());
        }
    }
}
