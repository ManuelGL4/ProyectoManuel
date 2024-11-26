<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
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

use Luracast\Restler\RestException;
require_once DOL_DOCUMENT_ROOT . '/custom/recursoshumanos/app/DiaPermisoController.php';

// require_once '../../timesheet/class/TimesheetAttendanceEvent.class.php';
/**
 * \file    mantenimiento/class/api_mantenimiento.class.php
 * \ingroup mantenimiento
 * \brief   File for API management of contratos.
 */

/**
 * API class for dias permisos
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class DiaPermisoApi extends DolibarrApi
{
    /**
     * Constructor
     *
     * @url     GET /
     *
     */
    public function __construct()
    {
        global $db, $conf;
        $this->db = $db;
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
public function listar($offset = 0, $limit = 10, $fk_user_id) {
    global $db;

    $obj_ret = array();

    // Check if user exists
    $userSql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid = " . intval($fk_user_id);
    $userResql = $this->db->query($userSql);
    
    if ($userResql && $userRow = $this->db->fetch_object($userResql)) {
        $user = $userRow;
    } else {
        throw new RestException(404, "No se encontró el usuario");
    }

    // Construct SQL query with JOINs to get username and task label
    $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "recursoshumanos_dias_permiso";

    // If the user is not an admin, filter by fk_userid
    if (!$user->admin) {
        $sql .= " WHERE fk_user_creat = " . intval($fk_user_id);
    }

    // Add ordering and pagination
    $sql .= " ORDER BY rowid DESC";
    $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

    // Execute the query
    $result = $this->db->query($sql);

    // Check if the query was successful
    if ($result) {
        // Fetch results and add to $obj_ret
        while ($obj = $this->db->fetch_object($result)) {
            $obj_ret[] = $obj;
        }
    } else {
        // If there's an error in the query, throw an exception
        throw new RestException(503, 'Error when retrieving tiempos list: ' . $this->db->lasterror());
    }

    // If no results are found, throw an exception
    if (count($obj_ret) == 0) {
        throw new RestException(404, 'No tiempos found');
    }

    // Return the result
    return $obj_ret;
}


    


}
