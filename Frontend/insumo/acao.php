<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ProductionInputDAO.php");
include_once("../../DataTransferObjects/ProductionInputDTO.php");


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
$productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
$productionInputDAO->showErrors = 1;


if ( $acao == "store" ) {
    $id = 0;
    $productionInput = new ProductionInputDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
        $id = $_REQUEST["id"];
        $productionInput = $productionInputDAO->RetrieveRecord($id);
    }
    $productionInput->descricao   = $_REQUEST["descricao"];
    $productionInput->tipoInsumo  = $_REQUEST["tipoInsumo"]; 
    $productionInput->valor       = $_REQUEST["valor"];

    $recordId = $productionInputDAO->StoreRecord($productionInput);
    if ($recordId == null){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if (!isset($_POST['reg'])) {
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        if ( !$productionInputDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;            
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
