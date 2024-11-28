<?php
use App\DiaPermiso;
require_once 'DiaPermiso.php';

class DiaPermisoController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Dia_permiso($db);
    }

    public function listDias($user, $sortfield, $sortorder,$page, $limit,$filters) 
    {
    
    $dias = $this->model->getDias($user, $sortfield, $sortorder,$page, $limit,$filters);

    if (!$dias) {
       return [];
    }
    return $dias;

}

public function insertarPermiso() {
    // Recoger los datos enviados
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $usuario_id = isset($_POST['usuario']) ? intval($_POST['usuario']) : null;
    $admin_id = isset($_POST['admin']) ? intval($_POST['admin']) : null;
    $fecha_solicitada = isset($_POST['fecha_solicitada']) ? trim($_POST['fecha_solicitada']) : null;
    $fecha_solicitada_fin = isset($_POST['fecha_solicitada_fin']) ? trim($_POST['fecha_solicitada_fin']) : null;

    // Validar que todos los campos requeridos no sean null ni vacíos
    if ($descripcion !== null && $descripcion !== '' &&
        $usuario_id !== null && $usuario_id > 0 &&
        $fecha_solicitada !== null && $fecha_solicitada !== '' &&
        $fecha_solicitada_fin !== null && $fecha_solicitada_fin !== '') {
        
        // Llamar al método del modelo para insertar los datos
        $resultado = $this->model->insertarPermiso($descripcion, $usuario_id, $fecha_solicitada, $fecha_solicitada_fin, $admin_id);

        if ($resultado) {
            return $resultado; // Retornar el ID del permiso insertado
        }
    } else {
        // Mensaje de error si algún campo está vacío o es null
        print '<div style="color: red;text-align: center;margin-top: 20px;"><strong>Error: Todos los campos son obligatorios. Por favor, complete toda la información.</strong></div>';
    }
}





public function actualizarPermiso() {
    // Recoger los datos enviados
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $usuario_id = isset($_POST['usuario']) ? intval($_POST['usuario']) : 0;
    $admin_id = isset($_POST['admin']) ? intval($_POST['admin']) : 0;
    $fecha_solicitada = isset($_POST['fecha_solicitada']) ? trim($_POST['fecha_solicitada']) : '';
    $fecha_solicitada_fin = isset($_POST['fecha_solicitada_fin']) ? trim($_POST['fecha_solicitada_fin']) : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $motivos = isset($_POST['motivos']) ? $_POST['motivos'] : '';
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 0;

    // Validar que todos los campos requeridos están completos
    if ($descripcion && $usuario_id && $fecha_solicitada && $fecha_solicitada_fin && $id) {
        // Llamar al método del modelo para actualizar los datos
        $resultado = $this->model->actualizarPermiso($id, $descripcion, $usuario_id, $fecha_solicitada, $fecha_solicitada_fin, $admin_id, $motivos, $estado);

        if ($resultado) {
            header('Location: dias_permiso_list.php?editada=success');
            setEventMessages(array("Solicitud de permiso actualizada correctamente."), array(), 'mesgs');
        } else {
            header('Location: dias_permiso_list.php?editada=error');
            setEventMessages(array("Error al intentar editar el registro, por favor, inténtelo de nuevo."), array(), 'errors');
        }
    } else {
        // Mensaje de error si algún campo está vacío
        print '<div style="color: red;text-align: center;margin-top: 20px;"><strong>Error: Por favor, complete toda la información.</div></strong>';
    }
}

public function getRegistros($user,$filters) {
    $registros = $this->model->getTotalDias($user,$filters);
    return $registros;
}
public function deleteRegistro($id) {
    $resultado = $this->model->deleteRegistro($id);
    if ($resultado) {

        header('Location: dias_permiso_list.php?delete=success');
        setEventMessages(array("Registro eliminado con éxito."), array(), 'mesgs');
        return $resultado;
    } else {
        header('Location: dias_permiso_list.php?delete=error');
        setEventMessages(array("Error al intentar eliminar el registro, por favor, inténtelo de nuevo."), array(), 'errors');
        return $resultado;
    }
}

}