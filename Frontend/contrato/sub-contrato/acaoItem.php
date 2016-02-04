<?php

session_start();

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/ContractItemDAO.php");
include_once("../../../DataTransferObjects/ContractItemDTO.php");
include_once("../../../DataAccessObjects/ActionLogDAO.php");
include_once("../../../DataTransferObjects/ActionLogDTO.php");


if ( !isset($_REQUEST["acao"]) || !isset($_REQUEST["contractId"])) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];
$contractId = $_REQUEST['contractId'];


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria os objetos de mapeamento objeto-relacional
$contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
$contractItemDAO->showErrors = 1;
$actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
$actionLogDAO->showErrors = 1;


if( $acao == "store") {
    $transactionType = 'INSERT';
    $contractItem = new ContractItemDTO();
    $contractItem->codigoCartaoEquipamento = $_REQUEST["equipmentCode"];
    $contractItem->businessPartnerCode     = $_REQUEST["businessPartnerCode"];
    $contractItem->codigoContrato          = $_REQUEST["contractId"];
    $contractItem->codigoSubContrato       = $_REQUEST["subContractId"];

    $recordId = $contractItemDAO->StoreRecord($contractItem);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $transactionType = 'DELETE';
    $recordId = $_REQUEST['equipmentCode'];

    if( !$contractItemDAO->DeleteRecord($recordId) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Grava no histórico a ação
$actionLog = new ActionLogDTO($transactionType, 'itemContrato', $recordId);
$actionLog->tipoAgregacao = 'contrato';
$actionLog->idAgregacao   = $contractId;

$actionLogDAO->StoreRecord($actionLog);


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
