<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
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
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;


if( $acao == "store") {
    $requestItem = new RequestItemDTO();
    $requestItem->codigoPedidoConsumivel = $_REQUEST["supplyRequestId"];
    $requestItem->codigoItem             = $_REQUEST["itemCode"];
    $requestItem->nomeItem               = $_REQUEST["itemName"];
    $requestItem->quantidade             = $_REQUEST["quantity"];
    $requestItem->total                  = $_REQUEST["total"];

    $recordId = $requestItemDAO->StoreRecord($requestItem);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $recordId = $_REQUEST['id'];
    if( !$requestItemDAO->DeleteRecord($recordId) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
