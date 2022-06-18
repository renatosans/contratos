<?php

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/EquipmentModelDAO.php");
include_once("../DataTransferObjects/EquipmentModelDTO.php");
include_once("../DataAccessObjects/ManufacturerDAO.php");
include_once("../DataTransferObjects/ManufacturerDTO.php");


$modelId = $_GET['modelId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}


// Cria os objetos de mapeamento objeto-relacional
$equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
$equipmentModelDAO->showErrors = 1;
$manufacturerDAO = new ManufacturerDAO($dataConnector->mysqlConnection);
$manufacturerDAO->showErrors = 1;


// Recupera os modelos cadastrados no sistema
$modelArray = $equipmentModelDAO->RetrieveRecordArray("id > 0 ORDER BY modelo");

// Busca os fabricantes cadastrados no sistema
$manufacturerArray = array();
$tempArray = $manufacturerDAO->RetrieveRecordArray();
foreach ($tempArray as $manufacturer) {
    $manufacturerArray[$manufacturer->id] = $manufacturer->nome;
}


echo '<option value="0" >-Não especificado-</option>';
foreach($modelArray as $model) {
    $attributes = "";
    if ($model->id == $modelId) $attributes = "selected='selected'";
    $spacing = "&nbsp;&nbsp;&nbsp;";
    echo "<option ".$attributes." value=".$model->id.">".$model->modelo." ".$spacing.$spacing." (".$manufacturerArray[$model->fabricante].")"."</option>";
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
