<?php

class BillingSummary{
    var $tipoContador      = 0;
    var $consumo           = 0;
    var $franquia          = 0;
    var $exedente          = 0;
    var $valorFixo         = 0;
    var $valorVariavel     = 0;
    var $valorTotal        = 0;


    function BillingSummary($tipoContador){
        $this->tipoContador = $tipoContador;
    }

}

?>
