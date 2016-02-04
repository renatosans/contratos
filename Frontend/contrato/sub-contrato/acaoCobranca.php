<?php

session_start();

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/ContractChargeDAO.php");
include_once("../../../DataTransferObjects/ContractChargeDTO.php");
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
$contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
$contractChargeDAO->showErrors = 1;
$actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
$actionLogDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $transactionType = 'INSERT';
    $contractCharge = new ContractChargeDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $transactionType = 'UPDATE';
        $contractCharge = $contractChargeDAO->RetrieveRecord($id);
    }

    $contractCharge->codigoContrato    = $_REQUEST["contractId"];
    $contractCharge->codigoSubContrato = $_REQUEST["subContractId"];
    $contractCharge->codigoContador    = $_REQUEST["counterId"];
    $contractCharge->modalidadeMedicao = $_REQUEST["modalidadeMedicao"];
    $contractCharge->fixo              = $_REQUEST["fixo"];
    $contractCharge->variavel          = $_REQUEST["variavel"];
    $contractCharge->franquia          = $_REQUEST["franquia"];
    $contractCharge->individual = 0; if (isset($_REQUEST["individual"])) $contractCharge->individual = 1;

    // Verifica se a cobrança não é repetida (duplicada), não existe uma contraint no banco pois registros excluídos são marcados com flag e assim
    // impedem a criação da constraint
    $previousCharges = $contractChargeDAO->RetrieveRecordArray("subContrato_id = ".$contractCharge->codigoSubContrato.' AND contador_id = '.$contractCharge->codigoContador);
    if (sizeof($previousCharges) > 0) {
        echo "Já existe uma cobrança cadastrada para este contador.";
        exit;
    }

    $recordId = $contractChargeDAO->StoreRecord($contractCharge);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $transactionType = 'DELETE';
    $recordId = $_REQUEST['id'];

    if( !$contractChargeDAO->DeleteRecord($recordId) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Grava no histórico a ação
$actionLog = new ActionLogDTO($transactionType, 'cobranca', $recordId);
$actionLog->tipoAgregacao = 'contrato';
$actionLog->idAgregacao   = $contractId;

$actionLogDAO->StoreRecord($actionLog);


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
