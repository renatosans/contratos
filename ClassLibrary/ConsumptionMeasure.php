<?php

class ConsumptionMeasure{
    var $equipmentCode         = 0;
    var $codigoContador        = 0;
    var $dataLeitura           = "";
    var $medicaoInicial        = 0;
    var $ajusteMedicaoInicial  = 0;
    var $medicaoFinal          = 0;
    var $ajusteMedicaoFinal    = 0;
    var $total                 = 0;
    var $ajusteTotal           = 0;


    function ConsumptionMeasure($equipmentCode, $codigoContador, $dataLeitura, $medicaoInicial, $medicaoFinal){
        $this->equipmentCode = $equipmentCode;
        $this->codigoContador = $codigoContador;
        $this->dataLeitura = $dataLeitura;
        $this->medicaoInicial = $medicaoInicial;
        $this->medicaoFinal = $medicaoFinal;
    }
}

?>
