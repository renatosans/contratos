<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SupplyRequestDAO.php");
include_once("../../DataTransferObjects/SupplyRequestDTO.php");
include_once("../../DataAccessObjects/RequestItemDAO.php");
include_once("../../DataTransferObjects/RequestItemDTO.php");
include_once("../../DataAccessObjects/ReadingDAO.php");
include_once("../../DataTransferObjects/ReadingDTO.php");


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
$supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
$supplyRequestDAO->showErrors = 1;


if( $acao == "store" ) {
    $id = 0;
    $supplyRequest = new SupplyRequestDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $supplyRequest = $supplyRequestDAO->RetrieveRecord($id);
    }
    $supplyRequest->codigoCartaoEquipamento  = $_REQUEST["equipmentCode"];
    $supplyRequest->data                     = $_REQUEST["data"];
    $supplyRequest->hora                     = $_REQUEST["hora"];
    $supplyRequest->status                   = $_REQUEST["status"];
    $supplyRequest->observacao               = $_REQUEST["observacao"];

    $recordId = $supplyRequestDAO->StoreRecord($supplyRequest);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo $recordId; // retorna o id do registro inserido para a página de edição
}

if ( $acao == "remove" ) {
    if(!isset($_POST['reg'])){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        // Verifica as dependências da solicitação ( foreign keys )
        $supplyRequestId = str_pad($reg, 5, '0', STR_PAD_LEFT);


        $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
        $requestItemDAO->showErrors = 1;
        $itemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$reg);
        if (sizeof($itemArray) > 0) {
            echo "O solicitação de consumível ".$supplyRequestId." não pode ser excluída pois possui amarrações. Exclua primeiro os itens da solicitação.";
            exit;
        }

        $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
        $readingDAO->showErrors = 1;
        $readingArray = $readingDAO->RetrieveRecordArray("consumivel_id=".$reg);
        if (sizeof($readingArray) > 0) {
            echo "O solicitação de consumível ".$supplyRequestId." não pode ser excluída pois está amarrada à leituras de contador. Exclua essas dependências primeiro.";
            exit;
        }

        if (!$supplyRequestDAO->DeleteRecord($reg)) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
