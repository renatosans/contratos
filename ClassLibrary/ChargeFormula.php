<?php

class ChargeFormula{
    var $modalidadeMedicao  = 0;
    var $fixo               = 0.0;
    var $variavel           = 0.0;
    var $franquia           = 0;
    var $individual         = 0;


    function ChargeFormula($modalidadeMedicao, $fixo, $variavel, $franquia, $individual){
        $this->modalidadeMedicao = $modalidadeMedicao;
        $this->fixo = $fixo;
        $this->variavel = $variavel;
        $this->franquia = $franquia;
        $this->individual = $individual;
    }
}

?>
