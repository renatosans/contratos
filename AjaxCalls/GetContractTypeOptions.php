<?php

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ContractTypeDAO.php");
include_once("../DataTransferObjects/ContractTypeDTO.php");


$contractTypeId = $_GET['contractTypeId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}


// Cria o objeto de mapeamento objeto-relacional
$contractTypeDAO = new ContractTypeDAO($dataConnector->mysqlConnection);
$contractTypeDAO->showErrors = 1;

// Busca os tipos de contrato cadastrados
$contractTypeArray = $contractTypeDAO->RetrieveRecordArray();
foreach ($contractTypeArray as $contractType) {
    $attributes = "";
    if ($contractType->id == $contractTypeId) $attributes = "selected='selected'";
    echo '<option '.$attributes.' value="'.$contractType->id.'" >'.$contractType->sigla.'</option>';
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
