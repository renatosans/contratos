<?php

    setlocale(LC_TIME, 'ptb', 'pt_BR', 'portuguese-brazil', 'bra', 'brazil', 'pt_BR.utf-8', 'pt_BR.iso-8859-1','br');
    
        $db_auth_options = Array(
            "host"=>"localhost",
            "user"=>"root",
            "password"=>"p@ssw0rd",
            "database"=>"addoncontratos"
        );

    $urlPath = "/AddOnContratos/";
    $libPathImg = "/AddOnContratos/img/";
    $libPathJs = "/AddOnContratos/lib/js/";
    $libPathCss = "/AddOnContratos/lib/css/";
    $libPathInc = "/AddOnContratos/inc/";

    $cfgCliente = "AddOnContratos";


    $dir = getcwd();
    $section = explode('\\',$dir);
    $dir =  $section[count($section)-1];

?>
