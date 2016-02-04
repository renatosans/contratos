<?php

class MailingDTO{
    var $id                   = 0;
    var $businessPartnerCode  = "";
    var $businessPartnerName  = "";
    var $contrato_id          = 0;
    var $subContrato_id       = 0;
    var $diaFaturamento       = 0;
    var $destinatarios        = "";
    var $enviarDemonstrativo  = false;
    var $ultimoEnvio          = NULL;


    function MailingDTO(){

    }

}

?>
