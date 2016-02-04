<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/PartRequestDAO.php");
include_once("../../DataTransferObjects/PartRequestDTO.php");
include_once("../../DataAccessObjects/RequestItemDAO.php");
include_once("../../DataTransferObjects/RequestItemDTO.php");


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
$partRequestDAO = new PartRequestDAO($dataConnector->mysqlConnection);
$partRequestDAO->showErrors = 1;
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;


if ( $acao == "remove" ) {
    $recordId = $_REQUEST['id'];

    $partRequest = $partRequestDAO->RetrieveRecord($recordId);
    $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoPecaReposicao_id=".$partRequest->id);
    foreach ($requestItemArray as $requestItem) {
        if( !$requestItemDAO->DeleteRecord($requestItem->id) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }
    if( !$partRequestDAO->DeleteRecord($partRequest->id) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
