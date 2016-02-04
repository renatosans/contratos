<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CommissionPerSignatureDAO.php");
include_once("../../DataTransferObjects/CommissionPerSignatureDTO.php");


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
$commissionPerSignatureDAO = new CommissionPerSignatureDAO($dataConnector->mysqlConnection);
$commissionPerSignatureDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $commissionRule = new CommissionPerSignatureDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $commissionRule = $commissionPerSignatureDAO->RetrieveRecord($id);
    }
    $commissionRule->segmento          = $_REQUEST["segmento"];
    $commissionRule->dataAssinaturaDe  = $_REQUEST["dataAssinaturaDe"];
    $commissionRule->dataAssinaturaAte = $_REQUEST["dataAssinaturaAte"];
    $commissionRule->comissao          = $_REQUEST["comissao"];

    $recordId = $commissionPerSignatureDAO->StoreRecord($commissionRule);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if(!isset($_POST['reg'])){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        if( !$commissionPerSignatureDAO->DeleteRecord($reg) ) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
