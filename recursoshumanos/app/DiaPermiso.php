<?php

class Dia_permiso
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
    public function getDias($user, $sortfield, $sortorder, $page , $limit,$filters )
    {        
        $query = "
        SELECT t.rowid AS peticion_id, 
            t.label, 
            t.date_solic, 
            t.date_solic_fin, 
            u1.firstname AS creator_firstname, 
            u1.lastname AS creator_lastname, 
            u2.firstname AS modifier_firstname, 
            u2.lastname AS modifier_lastname, 
            t.date_creation, 
            t.status 
        FROM khns_recursoshumanos_dias_permiso AS t
        LEFT JOIN khns_user AS u1 ON t.fk_user_creat = u1.rowid
        LEFT JOIN khns_user AS u2 ON t.fk_user_validador = u2.rowid
        WHERE 1 = 1"; 



        if ($user->admin) {
            // Si es administrador, no se añade ninguna condición
        } else {
            // Si no es administrador, se añade la condición para filtrar por fk_user_solicitador
            $query .= " AND t.fk_user_creat = " . intval($user->id);

        }

        if(isset($filters['codigo']) && !empty($filters['codigo'])){
            $query .= " AND t.rowid = " . intval($filters['codigo']);
        }

        if(isset($filters['description']) && !empty($filters['description'])){
            $query .= " AND t.label LIKE '%" . $filters['description'] . "%'";
        }

        if(isset($filters['fecha_inicio']) && !empty($filters['fecha_inicio'])){
            $fecha_inicio = date('Y-m-d H:i:s', strtotime($filters['fecha_inicio'] . ' 00:00:00'));
            $query .= " AND t.date_solic >= '" . $fecha_inicio . "'";
        }

        if(isset($filters['fecha_fin']) && !empty($filters['fecha_fin'])){
            $fecha_fin = date('Y-m-d H:i:s', strtotime($filters['fecha_fin'] . ' 23:59:59'));
            $query .= " AND t.date_solic_fin <=  '" . $fecha_fin . "'";
        }

        if(isset($filters['ls_userid']) && !empty($filters['ls_userid']) && $filters['ls_userid'] != -1){
            $query .= " AND t.fk_user_creat = " . intval($filters['ls_userid']);
        }

        if(isset($filters['fecha_create']) && !empty($filters['fecha_create'])){
            $fecha_create = date('Y-m-d H:i:s', strtotime($filters['fecha_create'] . ' 00:00:00'));
            $query .= " AND t.date_creation >= '" . $fecha_create . "'";
        }

        if(isset($filters['validador']) && !empty($filters['validador']) && $filters['validador'] != -1){
            $query .= " AND t.fk_user_validador = " . intval($filters['validador']);
        }

        if(isset($filters['status']) && $filters['status'] != -1){
            $query .= " AND t.status = " . intval($filters['status']);
        }


        // Filtrado por código
        if (isset($_POST['codigo']) && !empty($_POST['codigo'])) {
            $query .= " AND t.rowid = " . intval($_POST['codigo']);
        }

        // Filtrado por descripción
        if (isset($_POST['description']) && !empty($_POST['description'])) {
            $query .= " AND t.label LIKE '%" . ($_POST['description']) . "%'";
        }

        // Filtrado por fecha de inicio
        if (isset($_POST['fecha_inicio']) && !empty($_POST['fecha_inicio'])) {
            $fecha_inicio = date('Y-m-d H:i:s', strtotime($_POST['fecha_inicio'] . ' 00:00:00'));

            $query .= " AND t.date_solic >= '" . ($fecha_inicio) . "'";
        }

        // Filtrado por fecha de fin
        if (isset($_POST['fecha_fin']) && !empty($_POST['fecha_fin'])) {

            $fecha_fin = date('Y-m-d H:i:s', strtotime($_POST['fecha_fin'] . ' 23:59:59'));
            $query .= " AND t.date_solic_fin <=  '" . ($fecha_fin) . "'";
        }

        // Filtrado por usuario creador
        if (isset($_POST['ls_userid']) && !empty($_POST['ls_userid']) && $_POST['ls_userid'] != -1) {
            $query .= " AND t.fk_user_creat = " . intval($_POST['ls_userid']);
        }

        // Filtrado por fecha de creación
        if (isset($_POST['fecha_create']) && !empty($_POST['fecha_create'])) {
            $fecha_create = date('Y-m-d H:i:s', strtotime($_POST['fecha_create'] . ' 00:00:00'));
            $query .= " AND t.date_creation >= '" . ($fecha_create) . "'";
        }

        // Filtrado por usuario validador
        if (isset($_POST['validador']) && !empty($_POST['validador']) && $_POST['validador'] != -1) {
            $query .= " AND t.fk_user_validador = " . intval($_POST['validador']);
        }

        // Filtrado por estado
        if (isset($_POST['status']) && $_POST['status'] != -1) {
            $query .= " AND t.status = " . intval($_POST['status']);
        }
        $offset = ($page - 1) * $limit;

        $query .= " ORDER BY " . $sortfield . " " . $sortorder;
        $query .= " LIMIT " . $limit . " OFFSET " . $offset;


        $resql = $this->db->query($query);
        $result = [];

        if ($resql) {
            while ($row = $this->db->fetch_object($resql)) {
                $result[] = $row;
            }
        }

        return $result;
    }





    public function getTotalDias($user)
    {        
        $query = "
        SELECT COUNT(t.rowid) AS total
        FROM khns_recursoshumanos_dias_permiso AS t
        LEFT JOIN khns_user AS u1 ON t.fk_user_creat = u1.rowid
        LEFT JOIN khns_user AS u2 ON t.fk_user_validador = u2.rowid
        WHERE 1 = 1"; 



        if ($user->admin) {
            // Si es administrador, no se añade ninguna condición
        } else {
            // Si no es administrador, se añade la condición para filtrar por fk_user_solicitador
            $query .= " AND t.fk_user_creat = " . intval($user->id);

        }
        // Filtrado por código
        if (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
            $query .= " AND t.rowid = " . intval($_GET['codigo']);
        }

        // Filtrado por descripción
        if (isset($_GET['description']) && !empty($_GET['description'])) {
            $query .= " AND t.label LIKE '%" . ($_GET['description']) . "%'";
        }

        // Filtrado por fecha de inicio
        if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
            $fecha_inicio = date('Y-m-d H:i:s', strtotime($_GET['fecha_inicio'] . ' 00:00:00'));

            $query .= " AND t.date_solic >= '" . ($fecha_inicio) . "'";
        }

        // Filtrado por fecha de fin
        if (isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) {

            $fecha_fin = date('Y-m-d H:i:s', strtotime($_GET['fecha_fin'] . ' 23:59:59'));
            $query .= " AND t.date_solic_fin <=  '" . ($fecha_fin) . "'";
        }

        // Filtrado por usuario creador
        if (isset($_GET['ls_userid']) && !empty($_GET['ls_userid']) && $_GET['ls_userid'] != -1) {
            $query .= " AND t.fk_user_creat = " . intval($_GET['ls_userid']);
        }

        // Filtrado por fecha de creación
        if (isset($_GET['fecha_create']) && !empty($_GET['fecha_create'])) {
            $fecha_create = date('Y-m-d H:i:s', strtotime($_GET['fecha_create'] . ' 00:00:00'));
            $query .= " AND t.date_creation >= '" . ($fecha_create) . "'";
        }

        // Filtrado por usuario validador
        if (isset($_GET['validador']) && !empty($_GET['validador']) && $_GET['validador'] != -1) {
            $query .= " AND t.fk_user_validador = " . intval($_GET['validador']);
        }

        // Filtrado por estado
        if (isset($_GET['status']) && $_GET['status'] != -1) {
            $query .= " AND t.status = " . intval($_GET['status']);
        }

        $resql = $this->db->query($query);
        if ($resql) {
            $row = $this->db->fetch_object($resql);
            return $row->total;
        }
        return 0;
    }









    public function insertarPermiso($descripcion, $usuario_id, $fecha_solicitada, $fecha_solicitada_fin, $admin_id) {
        // Establecer valores fijos y convertir formato de fecha si es necesario
        $status = 0; // Status fijo en 0
        $date_creation = date("Y-m-d H:i:s"); // Fecha actual para la creación

        // Construir la consulta de inserción (igual que la proporcionada)
        $consulta = 'INSERT INTO ' . MAIN_DB_PREFIX . 'recursoshumanos_dias_permiso 
            (label, date_creation, fk_user_creat, fk_user_modif, status, fk_user_solicitado, date_solic, date_solic_fin, fk_user_validador) 
            VALUES 
            ("' . $this->db->escape($descripcion) . '", 
            "' . $date_creation . '", 
            ' . $usuario_id . ', 
            ' . $usuario_id . ', 
            ' . $status . ', 
            ' . $usuario_id . ', 
            "' . $this->db->escape($fecha_solicitada) . '", 
            "' . $this->db->escape($fecha_solicitada_fin) . '",
            ' . $admin_id . ')';

            if ($this->db->query($consulta)) {
                // Devuelve el ID del último registro insertado
                $consulta = "SELECT LAST_INSERT_ID() AS id FROM " . MAIN_DB_PREFIX . "recursoshumanos_dias_permiso";
                $resultado = $this->db->query($consulta);
                $row = $this->db->fetch_object($resultado);
                return $row->id;
            } else {
                // Manejar errores en la consulta
                dol_syslog("Error en insertarPermiso: " . $this->db->lasterror(), LOG_ERR);
                return false;
            }
    }


    public function actualizarPermiso($id, $descripcion, $usuario_id, $fecha_solicitada, $fecha_solicitada_fin, $admin_id, $motivos, $estado) {
        // Convertir formato de fecha si es necesario
        $fecha_solicitada = (new DateTime($fecha_solicitada))->format('Y-m-d H:i:s');
        $fecha_solicitada_fin = (new DateTime($fecha_solicitada_fin))->format('Y-m-d H:i:s');

        // Construir la consulta de actualización (igual que la proporcionada)
        $consulta = 'UPDATE ' . MAIN_DB_PREFIX . 'recursoshumanos_dias_permiso 
            SET 
                motivos = "' . $this->db->escape($motivos) . '",
                label = "' . $this->db->escape($descripcion) . '", 
                fk_user_creat = ' . $usuario_id . ', 
                fk_user_modif = ' . $usuario_id . ', 
                status = ' . $estado . ', 
                fk_user_solicitado = ' . $usuario_id . ', 
                date_solic = "' . $this->db->escape($fecha_solicitada) . '", 
                date_solic_fin = "' . $this->db->escape($fecha_solicitada_fin) . '",
                fk_user_validador = ' . $admin_id . '
            WHERE rowid = ' . $id;  // Asegúrate de usar el ID para identificar el registro a actualizar

        // Ejecutar la consulta
        return $this->db->query($consulta);
    }
    public function deleteRegistro($id) {
        $consulta = "DELETE FROM " . MAIN_DB_PREFIX . "recursoshumanos_dias_permiso WHERE rowid = " . $id;
        return $this->db->query($consulta);
    }



    public function download($idPermiso) {
        // Directorio donde están los archivos
        $uploadDir = DOL_DOCUMENT_ROOT . "/permisos/archivos/";
    
        // Obtener las rutas de los archivos asociados a este permiso desde la base de datos
        $sql = "SELECT nombre_archivo, ruta_archivo 
                FROM " . MAIN_DB_PREFIX . "permiso_archivos 
                WHERE fk_permiso = " . intval($idPermiso);
        $result = $this->db->query($sql);
    
        if ($this->db->num_rows($result) == 0) {
            die("No se encontraron archivos para este permiso.");
        }
    
        // Crear el archivo .zip
        $zip = new ZipArchive();
        $zipFileName = DOL_DOCUMENT_ROOT . "/permisos/archivos/permiso_$idPermiso.zip";
        
        // Abrir el archivo .zip para escritura
        if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
            die("No se pudo crear el archivo .zip.");
        }
    
        // Iterar sobre cada archivo y agregarlo al .zip
        while ($row = $this->db->fetch_object($result)) {
            // Convertir las barras invertidas a barras normales
            $filePath = str_replace("\\", "/", $row->ruta_archivo); // Asegúrate de usar barras normales
            $fileName = basename($filePath);  // Solo el nombre del archivo
    
            // Verificar si el archivo existe en el directorio
            if (file_exists($filePath)) {
                // Agregar el archivo al .zip
                $zip->addFile($filePath, $fileName);
            } else {
                echo "El archivo $fileName no se encuentra en el directorio: $filePath";
            }
        }
    
        // Cerrar el archivo .zip
        $zip->close();
    
        // Forzar la descarga del archivo .zip
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="permiso_' . $idPermiso . '.zip"');
        header('Content-Length: ' . filesize($zipFileName));
        readfile($zipFileName);
    
        // Eliminar el archivo .zip temporal después de la descarga
        unlink($zipFileName);
        exit();
    }
    
    
    
    
}