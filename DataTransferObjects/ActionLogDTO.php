<?php

class ActionLogDTO{
    var $id            = 0;
    var $login_id      = 0;
    var $data          = NULL;
    var $hora          = NULL;
    var $transacao     = "";
    var $tipoAgregacao = "";
    var $idAgregacao   = 0;
    var $tipoObjeto    = "";
    var $idObjeto      = 0;
    var $propriedade   = "";
    var $valor         = "";


    function ActionLogDTO($transactionType = "", $objectType = "", $objectId = 0) {
        $this->login_id = $_SESSION["usrID"];
        $this->data = date("Y-m-d",time());
        $this->hora = date("H:i",time());
        $this->transacao = $transactionType;
        $this->tipoObjeto = $objectType;
        $this->idObjeto = $objectId;
    }

}

?>
