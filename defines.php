<?php

    // Configura o fuso horário
    date_default_timezone_set ("Brazil/East");

    $appTitle  = "Gestão de Contratos";
    $pathTrack = explode('\\', getcwd());
    $currentDir = $pathTrack[count($pathTrack)-1];

    // Diretórios utilizados no sistema
    $root      = "/Contratos";
    $pathImg   = $root."/img/admin";   // $root."/Images";
    $pathJs    = $root."/lib/js";      // $root."/Javascript";
    $pathCss   = $root."/lib/css";     // $root."/StyleSheets";

    // Tipos de banco de dados suportados no sistema
    $databaseType = Array(
        "mySql",
        "sqlServer",
        "both"
    );

    // Parâmetros de login no banco de dados
    $mysqlAuthentication = Array(
        "host"=>"localhost",
        "username"=>"root",
        "password"=>"P@ssw0rd",
        "database"=>"addoncontratos"
    );

    // Parâmetros de login no banco de dados
    $sqlserverAuthentication = Array(
        "host"=>"DATADB",
        "username"=>"sapBusinessOne",
        "password"=>"P@ssw0rd",
        "database"=>"SBO_MSANSEVERINO_PROD"
    );

    // Itens do menu lateral
    $sideMenu = Array(
        // "Wellcome"=>"Frontend/Wellcome/Display.php",
        "Equipamentos"=>"Frontend/equipamentos/listar.php",
        "Chamados"=>"Frontend/chamados/listar.php",
        "Índices"=>"Frontend/indices/listar.php",
        "Contadores"=>"Frontend/contador/listar.php",
        "Contratos"=>"Frontend/contrato/listar.php",
        "Envio de Faturamento"=>"Frontend/mailing/listar.php",
        "Síntese Faturamento"=>"Frontend/faturamento/listar.php",
        "Insumos"=>"Frontend/insumo/listar.php",
        "Custos Indiretos"=>"Frontend/custoIndireto/listar.php",
        "Solicitação Consumível"=>"Frontend/_consumivel/listar.php",
        "Solicitação Peça Reps."=>"Frontend/_pecaReposicao/listar.php",
        "Inventário"=>"Frontend/inventario/listar.php",
        "Vendedores"=>"Frontend/vendedores/listar.php",
        "Regras de Comissão (assinatura dos contratos)"=>"Frontend/regraComissaoAssinatura/listar.php",
        "Regras de Comissão (volume de contratos/fat.)"=>"Frontend/regraComissaoVolume/listar.php",
        "Logins"=>"Frontend/login/listar.php",
        "Autorizações"=>"Frontend/autorizacao/gerenciar.php",
        "Servidores SMTP"=>"Frontend/servidores SMTP/listar.php",
        "Relatórios"=>"Frontend/relatorios/listar.php",
        "Configurações"=>"Frontend/config/gerenciar.php"
    );

    // Itens do menu lateral, versão com restrições
    $restrictedMenu = Array(
        "Contratos"=>"Frontend/contrato/listar.php",
        "Chamados"=>"Frontend/chamados/listar.php",
        "Leituras"=>"Frontend/_leitura/listar.php",
        "Solicitação Consumível"=>"Frontend/_consumivel/listar.php",
        "Solicitação Peça Reps."=>"Frontend/_pecaReposicao/listar.php"
    );

    // Fucionalidades do sistema, os itens de menu se agrupam de acordo com as funcionalidades
    $functionalities = Array(
        "administracaoSistema"       =>1,
        "gerenciamentoChamados"      =>2,
        "gerenciamentoContratos"     =>3,
        "solicitacaoConsumiveis"     =>4,
        "gerenciamentoEquipmtPecas"  =>5,
        "gerenciamentoLeituras"      =>6,
        "envioFaturamento"           =>7,
        "sinteseFaturamento"         =>8
    );

?>
