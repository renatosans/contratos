<?php

class ReadingDTO{
    var $id                       = 0;
    var $codigoCartaoEquipamento  = 0;
    var $codigoChamadoServico     = 0;
    var $codigoConsumivel         = 0;
    var $data                     = NULL;
    var $hora                     = NULL;
    var $codigoContador           = 0;
    var $contagem                 = 0;
    var $ajusteContagem           = 0;
    var $assinaturaDatacopy       = 0;
    var $assinaturaCliente        = "";
    var $observacao               = "";
    var $origemLeitura            = 0;
    var $formaLeitura             = 0;
    var $reset                    = 0;


    function __construct(){

    }

}

?>
