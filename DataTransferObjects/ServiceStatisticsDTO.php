<?php

class ServiceStatisticsDTO{
    public int    $id                  = 0;
    public int    $mesReferencia       = 0;
    public int    $anoReferencia       = 0;
    public int    $quantidadeChamados  = 0;
    public String $tempoEmAtendimento  = ""; // '00:00:00'
    public int    $totalEmSegundos     = 0;


    function __construct(){

    }

}

?>
