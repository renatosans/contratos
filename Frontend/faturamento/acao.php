<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");
include_once("../../DataAccessObjects/BillingDAO.php");
include_once("../../DataTransferObjects/BillingDTO.php");


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
$billingDAO = new BillingDAO($dataConnector->mysqlConnection);
$billingDAO->showErrors = 1;

// Busca o período de faturamento
$mesFaturamento = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "mesFaturamento");
$anoFaturamento = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "anoFaturamento");


if( $acao == "editProperties") {
    $billing = $billingDAO->RetrieveRecord($_REQUEST['id']);
    $billing->mesReferencia = $_REQUEST['mesReferencia'];
    $billing->anoReferencia = $_REQUEST['anoReferencia'];

    $recordId = $billingDAO->StoreRecord($billing);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "add") {
    if( !isset($_POST['reg']) ){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$id){
        $billing = $billingDAO->RetrieveRecord($id);
        $billing->incluirRelatorio = true;

        $recordId = $billingDAO->StoreRecord($billing);
        if ($recordId == null) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "addAll") {
    // Traz os faturamentos cadastrados no ano corrente
    $billingArray = $billingDAO->RetrieveRecordArray("mesReferencia = '".$mesFaturamento."' AND anoReferencia = '".$anoFaturamento."'");
    foreach ($billingArray as $billing) {
        $billing->incluirRelatorio = true;
        $recordId = $billingDAO->StoreRecord($billing);
        if ($recordId == null) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }
    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if( !isset($_POST['reg']) ){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$id){
        $billing = $billingDAO->RetrieveRecord($id);
        $billing->incluirRelatorio = false;

        $recordId = $billingDAO->StoreRecord($billing);
        if ($recordId == null) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "removeAll") {
    // Traz os faturamentos cadastrados no ano corrente
    $billingArray = $billingDAO->RetrieveRecordArray("mesReferencia = '".$mesFaturamento."' AND anoReferencia = '".$anoFaturamento."'");
    foreach ($billingArray as $billing) {
        $billing->incluirRelatorio = false;
        $recordId = $billingDAO->StoreRecord($billing);
        if ($recordId == null) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }
    echo "Operação efetuada com sucesso!";
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
