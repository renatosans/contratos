<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/IndirectCostDAO.php");
include_once("../../DataTransferObjects/IndirectCostDTO.php");


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
$indirectCostDAO = new IndirectCostDAO($dataConnector->mysqlConnection);
$indirectCostDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $indirectCost = new IndirectCostDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $indirectCost = $indirectCostDAO->RetrieveRecord($id);
    }
    $indirectCost->data            = $_REQUEST["data"];
    $indirectCost->hora            = $_REQUEST["hora"];
    $indirectCost->solicitante     = $_REQUEST["solicitante"];
    $indirectCost->infoSolicitante = $_REQUEST["infoSolicitante"];
    $indirectCost->codigoInsumo    = $_REQUEST["codigoInsumo"];
    $indirectCost->medicaoInicial  = $_REQUEST["medicaoInicial"];
    $indirectCost->medicaoFinal    = $_REQUEST["medicaoFinal"];
    $indirectCost->total           = $_REQUEST["total"];
    $indirectCost->observacao      = $_REQUEST["observacao"];

    $recordId = $indirectCostDAO->StoreRecord($indirectCost);
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
        if( !$indirectCostDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
