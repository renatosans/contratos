<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");


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
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $counter = new CounterDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $counter = $counterDAO->RetrieveRecord($id); 
    }
    $counter->nome  = $_REQUEST["nome"];

    $recordId = $counterDAO->StoreRecord($counter);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if( !isset($_POST['reg']) ){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        if( !$counterDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
