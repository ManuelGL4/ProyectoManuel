<?php

class Cobrador {

    private $nombreApell;
    private $direccion; //entera
    private $pais; //ES
    private $dni;
    private $cantidad;
    //private $empresa;
    private $fk_object;
    private $iban;



    public function __get($propiedad)
    {
        return $this->$propiedad;
    }

    public function __set($propiedad, $valor)
    {
        $this->$propiedad = $valor;
    }
    
}