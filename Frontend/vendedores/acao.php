<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SalesPersonDAO.php");
include_once("../../DataTransferObjects/SalesPersonDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria o objeto de mapeamento objeto-relacional
$salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
$salesPersonDAO->showErrors = 1;


if( $acao == "store") {
    if ( isset($_REQUEST["slpCode"]) && ($_REQUEST["slpCode"] != 0)) {
        $slpCode = $_REQUEST["slpCode"];
        $salesPerson = $salesPersonDAO->RetrieveRecord($slpCode);
    }
    $xml = '<xml></xml>';
    $salesPerson->serializedData = "'".$xml."'";

    $recordId = $salesPersonDAO->StoreRecord($salesPerson);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
