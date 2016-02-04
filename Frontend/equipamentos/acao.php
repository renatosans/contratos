<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('sqlServer');
$dataConnector->OpenConnection();
if ($dataConnector->sqlserverConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria o objeto de mapeamento objeto-relacional
$equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
$equipmentDAO->showErrors = 1;


if( $acao == "store") {
    if ( isset($_REQUEST["equipmentCode"]) && ($_REQUEST["equipmentCode"] != "") ) {
        $equipmentCode = $_REQUEST["equipmentCode"];
        $equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
    }
    $equipment->installationDate    = $_REQUEST["installationDate"];
    $equipment->installationDocNum  = $_REQUEST["installationDocNum"];
    $equipment->counterInitialVal   = $_REQUEST["counterInitialVal"];
    $equipment->removalDate         = $_REQUEST["removalDate"];
    $equipment->removalDocNum       = $_REQUEST["removalDocNum"];
    $equipment->counterFinalVal     = $_REQUEST["counterFinalVal"];
    $equipment->technician          = $_REQUEST["technician"];
    $equipment->model               = $_REQUEST["model"];
    $equipment->capacity            = $_REQUEST["capacity"];
    $equipment->sla                 = $_REQUEST["sla"];
    $equipment->comments            = $_REQUEST["comments"];
    $equipment->salesPerson         = $_REQUEST["salesPerson"];

    $recordId = $equipmentDAO->StoreRecord($equipment);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
