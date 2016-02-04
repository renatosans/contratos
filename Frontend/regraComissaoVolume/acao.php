<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CommissionPerVolumeDAO.php");
include_once("../../DataTransferObjects/CommissionPerVolumeDTO.php");


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
$commissionPerVolumeDAO = new CommissionPerVolumeDAO($dataConnector->mysqlConnection);
$commissionPerVolumeDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $commissionRule = new CommissionPerVolumeDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $commissionRule = $commissionPerVolumeDAO->RetrieveRecord($id);
    }
    $commissionRule->categoriaContrato   = $_REQUEST["categoriaContrato"];
    $commissionRule->quantContratosDe    = $_REQUEST["quantContratosDe"];
    $commissionRule->quantContratosAte   = $_REQUEST["quantContratosAte"];
    $commissionRule->valorFaturamentoDe  = $_REQUEST["valorFaturamentoDe"];
    $commissionRule->valorFaturamentoAte = $_REQUEST["valorFaturamentoAte"];
    $commissionRule->comissao            = $_REQUEST["comissao"];

    $recordId = $commissionPerVolumeDAO->StoreRecord($commissionRule);
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
        if( !$commissionPerVolumeDAO->DeleteRecord($reg) ) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
