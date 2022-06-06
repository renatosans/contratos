<?php

class InvoicePaymentDTO{
    var $serial               = 0;
    var $tipo                 = "";
    var $cardCode             = "";
    var $cardName             = "";
    var $valorNotaFiscal      = 0;
    var $valorDinheiro        = 0;
    var $valorCheque          = 0;
    var $valorDeposito        = 0;
    var $numeroBoleto         = 0;
    var $valorBoleto          = 0;
    var $quantiaRecebida      = 0;
    var $date                 = NULL;
    var $slpCode              = 0;
    var $slpName              = "";
    var $demFaturamento       = 0;


    function __construct(){

    }

}

?>
