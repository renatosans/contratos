<?php

session_start();

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/ContractBonusDAO.php");
include_once("../../../DataTransferObjects/ContractBonusDTO.php");
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
$contractBonusDAO = new ContractBonusDAO($dataConnector->mysqlConnection);
$contractBonusDAO->showErrors = 1;
$actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
$actionLogDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $transactionType = 'INSERT';
    $contractBonus = new ContractBonusDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $transactionType = 'UPDATE';
        $contractBonus = $contractBonusDAO->RetrieveRecord($id);
    }

    $contractBonus->codigoContrato    = $_REQUEST["contractId"];
    $contractBonus->codigoSubContrato = $_REQUEST["subContractId"];
    $contractBonus->codigoContador    = $_REQUEST["counterId"];
    $contractBonus->de                = $_REQUEST["de"];
    $contractBonus->ate               = $_REQUEST["ate"];
    $contractBonus->valor             = $_REQUEST["valor"];

    $recordId = $contractBonusDAO->StoreRecord($contractBonus);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $transactionType = 'DELETE';
    $recordId = $_REQUEST['id'];

    if( !$contractBonusDAO->DeleteRecord($recordId) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Grava no histórico a ação
$actionLog = new ActionLogDTO($transactionType, 'bonus', $recordId);
$actionLog->tipoAgregacao = 'contrato';
$actionLog->idAgregacao   = $contractId;

$actionLogDAO->StoreRecord($actionLog);


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
