<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/EquipmentModelDAO.php");
include_once("../../DataTransferObjects/EquipmentModelDTO.php");


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
$equipamentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
$equipamentModelDAO->showErrors = 1;


if( $acao == "store") {
    $model = new EquipmentModelDTO();
    $model->modelo     = $_REQUEST["modelo"];
    $model->fabricante = $_REQUEST["fabricante"];

    $recordId = $equipamentModelDAO->StoreRecord($model);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $id = $_REQUEST['id'];
    if( !$equipamentModelDAO->DeleteRecord($id) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
