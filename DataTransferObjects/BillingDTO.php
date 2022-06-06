<?php

class BillingDTO{
    var $id                   = 0;
    var $businessPartnerCode  = '';
    var $businessPartnerName  = '';
    var $mailing_id           = 0;
    var $dataInicial          = NULL;
    var $dataFinal            = NULL;
    var $mesReferencia        = 0;
    var $anoReferencia        = 0;
    var $multaRecisoria       = 0;
    var $acrescimoDesconto    = 0;
    var $total                = 0;
    var $obs                  = '';
    var $incluirRelatorio     = false;


    function __construct(){

    }

}

?>
