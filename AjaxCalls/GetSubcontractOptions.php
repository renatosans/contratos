<?php

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/SubContractDAO.php");
include_once("../DataTransferObjects/SubContractDTO.php");
include_once("../DataAccessObjects/ContractItemDAO.php");
include_once("../DataTransferObjects/ContractItemDTO.php");
include_once("../DataAccessObjects/EquipmentDAO.php");
include_once("../DataTransferObjects/EquipmentDTO.php");


$contractId = $_GET['contractId'];
$subContractId = $_GET['subContractId'];


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;

// Busca o subcontratos pertencentes ao contrato informado
$subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id=".$contractId);

echo '<option value="0" >Todos</option>';
foreach ($subContractArray as $subContract) {
    $attributes = "";
    if ($subContract->id == $subContractId) $attributes = "selected='selected'";

    $contractType = $subContract->siglaTipoContrato;
    $serialNumbers = SubContractDAO::GetSerialNumbers($dataConnector->mysqlConnection, $dataConnector->sqlserverConnection, $subContract->id);
    if (empty($serialNumbers)) $serialNumbers = 'Nenhum item encontrado';

    echo '<option '.$attributes.' value="'.$subContract->id.'" >'.$contractType.' - '.$serialNumbers.'</option>';
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
