<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/IndirectCostDAO.php");
include_once("../../DataTransferObjects/IndirectCostDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria o objeto de mapeamento objeto-relacional
$indirectCostDAO = new IndirectCostDAO($dataConnector->mysqlConnection);
$indirectCostDAO->showErrors = 1;


if( $acao == "store") {
    $serviceCallId = $_REQUEST["serviceCallId"];
    $indirectCostId = $_REQUEST["indirectCostId"];
    $result = $indirectCostDAO->AddDistributedExpense($serviceCallId, $indirectCostId);

    if (!$result) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $serviceCallId = $_REQUEST["serviceCallId"];
    $indirectCostId = $_REQUEST["indirectCostId"];
    $result = $indirectCostDAO->RemoveDistributedExpense($serviceCallId, $indirectCostId);

    if (!$result) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
