<?php

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ContractDAO.php");
include_once("../DataTransferObjects/ContractDTO.php");
include_once("../DataAccessObjects/SubContractDAO.php");
include_once("../DataTransferObjects/SubContractDTO.php");
include_once("../DataAccessObjects/ContractItemDAO.php");
include_once("../DataTransferObjects/ContractItemDTO.php");


$businessPartnerCode = $_GET['businessPartnerCode'];
$contractId = $_GET['contractId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;
$contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
$contractItemDAO->showErrors = 1;
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;


// Busca os contratos pertencentes ao parceiro de negócios
$contractArray = $contractDAO->RetrieveRecordArray("pn='".$businessPartnerCode."'");

// Busca os items de contrato pertencentes ao parceiro de negócios
$itemArray = $contractItemDAO->RetrieveRecordArray("businessPartnerCode = '".$businessPartnerCode."'");

echo '<option value="0" >Agrupar equips. do cliente(independente do contrato)</option>';
$contractIdArray = array();
foreach ($contractArray as $contract) 
    array_push($contractIdArray, $contract->id);
foreach ($itemArray as $contractItem) {
    $subContract = $subContractDAO->RetrieveRecord($contractItem->codigoSubContrato);
    if (!in_array($subContract->codigoContrato, $contractIdArray))
        array_push($contractIdArray, $subContract->codigoContrato);
}
foreach ($contractIdArray as $id) {
    $contract = $contractDAO->RetrieveRecord($id);
    $status = ContractDAO::GetStatusAsText($contract->status);
    $attributes = "";
    if ($contract->id == $contractId) $attributes = "selected='selected'";
    echo '<option '.$attributes.' value="'.$contract->id.'" >'.str_pad($contract->numero, 5, '0', STR_PAD_LEFT).' [SYS ID='.$contract->id.' STATUS='.$status.']</option>';
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
