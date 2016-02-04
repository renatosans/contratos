<?php

class PriceByVolume{
    var $codigoSubContrato = 0;
    var $codigoContador    = 0;
    var $de                = 0;
    var $ate               = 0;
    var $valor             = 0.0;


    function PriceByVolume($codigoSubContrato, $codigoContador, $de, $ate, $valor){
        $this->codigoSubContrato = $codigoSubContrato;
        $this->codigoContador    = $codigoContador;
        $this->de                = $de;
        $this->ate               = $ate;
        $this->valor             = $valor;
    }
}

?>
