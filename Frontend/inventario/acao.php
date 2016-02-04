<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");
include_once("../../DataAccessObjects/InventoryItemDAO.php");
include_once("../../DataTransferObjects/InventoryItemDTO.php");


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
$inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
$inventoryItemDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Traz os contadores cadastrados no sistema
$counterArray = $counterDAO->RetrieveRecordArray();


if( $acao == "store") {
    if ( isset($_REQUEST["itemCode"]) && ($_REQUEST["itemCode"] != "") ) {
        $itemCode = $_REQUEST["itemCode"];
        $inventoryItem = $inventoryItemDAO->RetrieveRecord($itemCode);
    }
    $inventoryItem->expenses         = $_REQUEST["expenses"];
    $inventoryItem->durability       = $_REQUEST["durability"];
    $inventoryItem->useInstructions  = $_REQUEST["useInstructions"];

    $xml = '<DurabilityAgents>';
    foreach ($counterArray as $counter) {
        $elementName = 'counter'.$counter->id;
        if (isset($_REQUEST[$elementName]))
            $xml = $xml.'<Counter id="'.$counter->id.'" name="'.$counter->nome.'" />'; 
    }
    $xml = $xml.'</DurabilityAgents>';
    $inventoryItem->serializedData = "'".$xml."'";

    $recordId = $inventoryItemDAO->StoreRecord($inventoryItem);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "batchUpdate") {
    $itemNameQuery  = $_REQUEST["itemNameQuery"];
    $expenses = $_REQUEST["expenses"];
    $durability = $_REQUEST["durability"];
    $useInstructions = $_REQUEST["useInstructions"];

    $rowsAffected = $inventoryItemDAO->BatchUpdate($itemNameQuery, $expenses, $durability, $useInstructions);
    if ($rowsAffected == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo $rowsAffected." registros alterados.";
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
