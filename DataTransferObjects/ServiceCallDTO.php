<?php

class ServiceCallDTO{
    var $id                      = 0;
    var $defeito                 = "";
    var $dataAbertura            = NULL;
    var $horaAbertura            = NULL;
    var $dataFechamento          = NULL;
    var $horaFechamento          = NULL;
    var $dataAtendimento         = NULL;
    var $horaAtendimento         = NULL;
    var $tempoAtendimento        = NULL;
    var $duracaoEmSegundos       = 0;
    var $businessPartnerCode     = "";
    var $contato                 = "";
    var $status                  = 0;
    var $tipo                    = 0;
    var $abertoPor               = 0;
    var $tecnico                 = 0;
    var $prioridade              = 0;
    var $codigoCartaoEquipamento = 0;
    var $modelo                  = "";
    var $fabricante              = "";
    var $observacaoTecnica       = "";
    var $sintoma                 = "";
    var $causa                   = "";
    var $acao                    = "";


    function __construct(){

    }

}

?>
