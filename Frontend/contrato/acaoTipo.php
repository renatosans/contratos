<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ContractTypeDAO.php");
include_once("../../DataTransferObjects/ContractTypeDTO.php");


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
$contractTypeDAO = new ContractTypeDAO($dataConnector->mysqlConnection);
$contractTypeDAO->showErrors = 1;


if( $acao == "store") {
    $contractType = new ContractTypeDTO();
    $contractType->sigla        = $_REQUEST["sigla"];
    $contractType->nome         = $_REQUEST["descricao"];
    $contractType->permiteBonus = $_REQUEST["permiteBonus"];

    $recordId = $contractTypeDAO->StoreRecord($contractType);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $id = $_REQUEST['id'];
    if( !$contractTypeDAO->DeleteRecord($id) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
