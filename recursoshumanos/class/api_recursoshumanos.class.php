<?php
/* Copyright (C) 2015   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024 Comercial ORTRAT <comercial@ortrat.es>
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

dol_include_once('/recursoshumanos/class/informacion_noticias.class.php');
dol_include_once('/recursoshumanos/app/DiaPermiso.php');



/**
 * \file    recursoshumanos/class/api_recursoshumanos.class.php
 * \ingroup recursoshumanos
 * \brief   File for API management of informacion_noticias.
 */

/**
 * API class for recursoshumanos informacion_noticias
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class RecursosHumanosApi extends DolibarrApi
{
	/**
	 * @var Informacion_noticias $informacion_noticias {@type Informacion_noticias}
	 */
	public $informacion_noticias;
	public $diapermiso;

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
		$this->informacion_noticias = new Informacion_noticias($this->db);
		$this->diapermiso = new Dia_Permiso($this->db);
	}

	/**
	 * Get properties of a informacion_noticias object
	 *
	 * Return an array with informacion_noticias informations with an id of noticias
	 *
	 * @param 	int 	$id ID of informacion_noticias
	 * @return 	array|mixed data without useless information
	 *
	 * @url	GET informacion_noticiass/{id}
	 *
	 * @throws RestException 401 Not allowed
	 * @throws RestException 404 Not found
	 */
	public function get($id)
	{

		$result = $this->informacion_noticias->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Informacion_noticias not found');
		}

		if (!DolibarrApi::_checkAccessToResource('informacion_noticias', $this->informacion_noticias->id, 'recursoshumanos_informacion_noticias')) {
			throw new RestException(401, 'Access to instance id='.$this->informacion_noticias->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		return $this->_cleanObjectDatas($this->informacion_noticias);
	}


	/**
	 * List informacion_noticiass
	 *
	 * Get a list of informacion_noticiass
	 *
	 * @param string	       $sortfield	        Sort field
	 * @param string	       $sortorder	        Sort order
	 * @param int		       $limit		        Limit for list
	 * @param int		       $page		        Page number
	 * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @return  array                               Array of order objects
	 *
	 * @throws RestException
	 *
	 * @url	GET /informacion_noticiass/
	 */
	public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '')
	{
		global $db, $conf;

		$obj_ret = array();
        
		$sql = "SELECT t.rowid";
		
		$sql .= " FROM `khns_recursoshumanos_informacion_noticias` as t";

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		$i = 0;
		if ($result) {
			$num = $this->db->num_rows($result);
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$tmp_object = new Informacion_noticias($this->db);
				if ($tmp_object->fetch($obj->rowid)) {
					$obj_ret[] = $this->_cleanObjectDatas($tmp_object);
				}
				$i++;
			}
		} else {
			throw new RestException(503, "$sql");
		}
		if (!count($obj_ret)) {
			throw new RestException(404, 'No Noticias found');
		}
		return $obj_ret;
	}

	/**
	 * Create informacion_noticias object
	 *
	 * @param array $request_data   Request datas
	 * @return int  ID of informacion_noticias
	 *
	 * @throws RestException
	 *
	 * @url	POST informacion_noticiass/
	 */
	public function post($request_data = null)
	{

		// Check mandatory fields
		$result = $this->_validate($request_data);

		foreach ($request_data as $field => $value) {
			$this->informacion_noticias->$field = $this->_checkValForAPI($field, $value, $this->informacion_noticias);
		}

		// Clean data
		// $this->informacion_noticias->abc = checkVal($this->informacion_noticias->abc, 'alphanohtml');

		if ($this->informacion_noticias->create(DolibarrApiAccess::$user)<0) {
			throw new RestException(500, "Error creating Informacion_noticias", array_merge(array($this->informacion_noticias->error), $this->informacion_noticias->errors));
		}
		return $this->informacion_noticias->id;
	}

	/**
	 * Update informacion_noticias
	 *
	 * @param int   $id             Id of informacion_noticias to update
	 * @param array $request_data   Datas
	 * @return int
	 *
	 * @throws RestException
	 *
	 * @url	PUT informacion_noticiass/{id}
	 */
	public function put($id, $request_data = null)
	{

		$result = $this->informacion_noticias->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Informacion_noticias not found');
		}

		if (!DolibarrApi::_checkAccessToResource('informacion_noticias', $this->informacion_noticias->id, 'recursoshumanos_informacion_noticias')) {
			throw new RestException(401, 'Access to instance id='.$this->informacion_noticias->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			$this->informacion_noticias->$field = $this->_checkValForAPI($field, $value, $this->informacion_noticias);
		}

		// Clean data
		// $this->informacion_noticias->abc = checkVal($this->informacion_noticias->abc, 'alphanohtml');

		if ($this->informacion_noticias->update(DolibarrApiAccess::$user, false) > 0) {
			return $this->get($id);
		} else {
			throw new RestException(500, $this->informacion_noticias->error);
		}
	}

	/**
	 * Delete informacion_noticias
	 *
	 * @param   int     $id   Informacion_noticias ID
	 * @return  array
	 *
	 * @throws RestException
	 *
	 * @url	DELETE informacion_noticiass/{id}
	 */
	public function delete($id)
	{

		$result = $this->informacion_noticias->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Informacion_noticias not found');
		}

		if (!$this->informacion_noticias->delete(DolibarrApiAccess::$user)) {
			throw new RestException(500, 'Error when deleting Informacion_noticias : '.$this->informacion_noticias->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Informacion_noticias deleted'
			)
		);
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 * Clean sensible object datas
	 *
	 * @param   Object  $object     Object to clean
	 * @return  Object              Object with cleaned properties
	 */
	protected function _cleanObjectDatas($object)
	{
		// phpcs:enable
		$object = parent::_cleanObjectDatas($object);

		unset($object->rowid);
		unset($object->canvas);

		unset($object->name);
		unset($object->lastname);
		unset($object->firstname);
		unset($object->civility_id);
		unset($object->statut);
		unset($object->state);
		unset($object->state_id);
		unset($object->state_code);
		unset($object->region);
		unset($object->region_code);
		unset($object->country);
		unset($object->country_id);
		unset($object->country_code);
		unset($object->barcode_type);
		unset($object->barcode_type_code);
		unset($object->barcode_type_label);
		unset($object->barcode_type_coder);
		unset($object->total_ht);
		unset($object->total_tva);
		unset($object->total_localtax1);
		unset($object->total_localtax2);
		unset($object->total_ttc);
		unset($object->fk_account);
		unset($object->comments);
		unset($object->note);
		unset($object->mode_reglement_id);
		unset($object->cond_reglement_id);
		unset($object->cond_reglement);
		unset($object->shipping_method_id);
		unset($object->fk_incoterms);
		unset($object->label_incoterms);
		unset($object->location_incoterms);

		// If object has lines, remove $db property
		if (isset($object->lines) && is_array($object->lines) && count($object->lines) > 0) {
			$nboflines = count($object->lines);
			for ($i = 0; $i < $nboflines; $i++) {
				$this->_cleanObjectDatas($object->lines[$i]);

				unset($object->lines[$i]->lines);
				unset($object->lines[$i]->note);
			}
		}

		return $object;
	}

	/**
	 * Validate fields before create or update object
	 *
	 * @param	array		$data   Array of data to validate
	 * @return	array
	 *
	 * @throws	RestException
	 */
	private function _validate($data)
	{
		$informacion_noticias = array();
		foreach ($this->informacion_noticias->fields as $field => $propfield) {
			if (in_array($field, array('rowid', 'titulo', 'descripcion', 'date_creation', 'tms', 'link', 'link_img')) || $propfield['notnull'] != 1) {
				continue; // Not a mandatory field
			}
			if (!isset($data[$field])) {
				throw new RestException(400, "$field field missing");
			}
			$informacion_noticias[$field] = $data[$field];
		}
		return $informacion_noticias;
	}





	/**
 * Get list of tiempos with pagination and filtering by userId
 *
 * @param int $userId   ID of the user making the request
 * @param int $offset   Number of records to skip
 * @param int $limit    Number of records to return
 * @param int $page     The current page (for pagination)
 * @return array        Array of TiempoTarea objects
 *
 * @throws RestException
 */
public function listar($offset = 0, $limit = 10, $fk_user_id, $page = 1) {
    global $db;

    $obj_ret = array();

    $userSql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE rowid = " . intval($fk_user_id);
    $userResql = $this->db->query($userSql);
    
    if ($userResql && $userRow = $this->db->fetch_object($userResql)) {
        $user = $userRow;
    } else {
        throw new RestException(404, "No se encontró el usuario");
    }

	$sql = "SELECT dp.*, CONCAT(u.firstname, ' ', u.lastname) AS user_name 
	FROM ".MAIN_DB_PREFIX."recursoshumanos_dias_permiso dp
	LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = dp.fk_user_creat";

    if (!$user->admin) {
        $sql .= " WHERE dp.fk_user_creat = " . intval($fk_user_id);
    }

    $offset = ($page - 1) * $limit; 

    $sql .= " ORDER BY dp.rowid DESC";
    $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

    $result = $this->db->query($sql);

    if ($result) {
        while ($obj = $this->db->fetch_object($result)) {
            $obj->is_admin = $user->admin ? true : false;
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
 * Update dia_permiso
 *
 * @param array $request_data   Data
 * @return int
 *
 * @throws RestException
 *
 * @url    PUT diapermiso/
 */
public function updateDiaPermiso($request_data) {
    // Obtener del cuerpo los campos
    $rowid = intval($request_data['rowid']);
    $label = $request_data['label'];
    $fk_user_solicitado = intval($request_data['fk_user_solicitado']);
    $date_solic = $request_data['date_solic'];
    $date_solic_fin = $request_data['date_solic_fin'];
    $motivos = $request_data['motivos'];
    $status = $request_data['status'];

    try {
        $date_solic = new DateTime($date_solic); 
        $date_solic_fin = new DateTime($date_solic_fin); 
    } catch (Exception $e) {
        throw new RestException(400, "Error en el formato de las fechas: " . $e->getMessage());
    }

    $date_solic = $date_solic->format('Y-m-d H:i:s');
    $date_solic_fin = $date_solic_fin->format('Y-m-d H:i:s');
    $result = $this->diapermiso->actualizarPermisoAPI($rowid,$label,$fk_user_solicitado,$date_solic,$date_solic_fin,$motivos,$status);
    
    if ($result) {
        return ['message' => 'Permiso actualizado correctamente'];
    } else {
        throw new RestException(500, 'Error al actualizar el permiso');
    }
}

	/**
	 * Delete diapermiso
	 *
	 * @param   int     $id   permiso ID
	 * @return  array
	 *
	 * @throws RestException
	 *
	 * @url	DELETE diapermiso/{id}
	 */
	public function deletePermiso($id)
	{
		$result = $this->diapermiso->deleteRegistro($id);	
		if ($result) {
		return ['message' => 'Permiso eliminado correctamente'];

		} else {
			throw new RestException(500, 'Error al eliminar el permiso');
		}
	}

/**
 * Get list of usuarios with pagination and filtering by userId
 *
 * @return array        Array of user objects with rowid and name
 *
 * @throws RestException
 */
public function listarUsuarios() {
    global $db;

    $obj_ret = array();

    $userSql = "SELECT rowid, login, firstname, lastname FROM ".MAIN_DB_PREFIX."user WHERE admin=1";
    $userResql = $this->db->query($userSql);
    
    if ($userResql && $this->db->num_rows($userResql) > 0) {
        while ($obj = $this->db->fetch_object($userResql)) {
            $obj_ret[] = array(
                'rowid' => $obj->rowid,
                'nombre' => $obj->firstname . ' ' . $obj->lastname  
            );
        }
    } else {
        throw new RestException(404, 'No se encontraron usuarios');
    }

    return $obj_ret;
}


/**
 * Crear un nuevo día de permiso para un usuario
 *
 * @param array $request_data   Datos de la solicitud, como el usuario y el día de permiso
 * @return array   ID del nuevo permiso o error
 *
 * @throws RestException
 *
 * @url    POST permisos/
 */
public function postPermiso($request_data = null)
{
        
        // Recoger los datos desde $request_data
        $descripcion = $request_data['motivos'];
        $usuario = intval($request_data['usuario']);
        $admin = $request_data['fk_user_validador'];
        $fecha_solicitada = $request_data['date_solic'];
        $fecha_solicitada_fin = $request_data['date_solic_fin'];

		try {
			$fecha_solicitada_obj = new DateTime($fecha_solicitada);  // Crea un objeto DateTime
			$fecha_solicitada_fin_obj = new DateTime($fecha_solicitada_fin);  // Crea un objeto DateTime
		} catch (Exception $e) {
			throw new RestException(400, "Fecha inválida: " . $e->getMessage());
		}
	
		// Convertir las fechas a formato 'Y-m-d H:i:s' para insertarlas en la base de datos
		$fecha_solicitada = $fecha_solicitada_obj->format('Y-m-d H:i:s');
		$fecha_solicitada_fin = $fecha_solicitada_fin_obj->format('Y-m-d H:i:s');
	    $fecha_creation = date('Y-m-d H:i:s');  // Fecha actual para la columna date_creation

		$result=$this->diapermiso->insertarPermiso($descripcion,$usuario,$fecha_solicitada,$fecha_solicitada_fin,$admin);

		if ($result) {
			return ['id' => $result];
		} else {
			throw new RestException(500, "Hubo un problema al insertar el permiso.");
		}

}




}
