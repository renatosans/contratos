<?php

class ContractDTO{
    var $id                        = 0;
    var $numero                    = "";
    var $pn                        = "";
    var $divisao                   = "";
    var $contato                   = 0;
    var $status                    = 0;
    var $categoria                 = 0;
    var $dataAssinatura            = NULL;
    var $dataEncerramento          = NULL;
    var $inicioAtendimento         = NULL;
    var $fimAtendimento            = NULL;
    var $primeiraParcela           = NULL;
    var $parcelaAtual              = 0;
    var $mesReferencia             = 0;
    var $anoReferencia             = 0;
    var $quantidadeParcelas        = 0;
    var $global                    = false;
    var $vendedor                  = 0;
    var $diaVencimento             = 0;
    var $referencialVencimento     = 0;
    var $diaLeitura                = 0;
    var $referencialLeitura        = 0;
    var $indiceReajuste            = 0;
    var $dataRenovacao             = NULL;
    var $dataReajuste              = NULL;
    var $valorImplantacao          = 0;
    var $quantParcelasImplantacao  = 0;
    var $obs                       = "";


    function ContractDTO(){

    }

}

?>
