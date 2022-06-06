<?php

class BillingItemDTO{
    var $id                       = 0;
    var $codigoFaturamento        = 0;
    var $contrato_id              = 0;
    var $subContrato_id           = 0;
    var $codigoCartaoEquipamento  = 0;
    var $tipoLocacao              = 0;
    var $counterId                = 0;
    var $dataLeitura              = NULL;
    var $medicaoFinal             = 0;
    var $medicaoInicial           = 0;
    var $consumo                  = 0;
    var $ajuste                   = 0;
    var $franquia                 = 0;
    var $excedente                = 0;
    var $tarifaSobreExcedente     = 0;
    var $fixo                     = 0;
    var $variavel                 = 0;
    var $total                    = 0;
    var $acrescimoDesconto        = 0;


    function __construct(){

    }

}

?>
