<?php

session_start();

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/ExpenseDAO.php");
include_once("../../../DataTransferObjects/ExpenseDTO.php");


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
$expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
$expenseDAO->showErrors = 1;


if( $acao == "store" ) {
    $id = 0;
    $expense = new ExpenseDTO();
    if (isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
        $id = $_REQUEST["id"];
        $expense = $expenseDAO->RetrieveRecord($id);
    }
    if (isset($_REQUEST["chamado"]))
        $expense->codigoChamado = $_REQUEST["chamado"];
    if (isset($_REQUEST["codigoInsumo"]))
        $expense->codigoInsumo = $_REQUEST["codigoInsumo"];
    if (isset($_REQUEST["codigoItem"]))
        $expense->codigoItem = $_REQUEST["codigoItem"];
    if (isset($_REQUEST["nomeItem"]))
        $expense->nomeItem = $_REQUEST["nomeItem"];
    if (isset($_REQUEST["quantidade"]))
        $expense->quantidade = $_REQUEST["quantidade"];
    $expense->medicaoInicial     = $_REQUEST["medicaoInicial"];
    $expense->medicaoFinal       = $_REQUEST["medicaoFinal"];
    $expense->totalDespesa       = $_REQUEST["totalDespesa"];
    $expense->observacao         = $_REQUEST["observacao"];

    $tipoCusto = $_REQUEST["tipoCusto"];
    if ($tipoCusto != "0") {
        $expense->codigoItem  = "";
        $expense->nomeItem    = "";
        $expense->quantidade  = 0;
    }

    $recordId = $expenseDAO->StoreRecord($expense);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $id = $_REQUEST["id"];
    if (!$expenseDAO->DeleteRecord($id)) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
