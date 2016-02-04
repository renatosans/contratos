<?php

session_start();

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/SubContractDAO.php");
include_once("../../../DataTransferObjects/SubContractDTO.php");
include_once("../../../DataAccessObjects/ContractItemDAO.php");
include_once("../../../DataTransferObjects/ContractItemDTO.php");


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
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;

if( $acao == "store" ) {
    $id = 0;
    $subContract = new SubContractDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $subContract = $subContractDAO->RetrieveRecord($id); 
    }
    $subContract->codigoContrato      = $_REQUEST["contractId"];
    $subContract->codigoTipoContrato  = $_REQUEST["contractTypeId"];


    $recordId = $subContractDAO->StoreRecord($subContract);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $id = $_REQUEST['id'];

    $itemArray = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $id);
    $itemCount = sizeof($itemArray);
    if ($itemCount != 0) {
        echo "É necessário excluir os itens de contrato antes de prosseguir.";
        exit;
    }

    if( !$subContractDAO->DeleteRecord($id) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
