<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/ProductionInputDAO.php");
    include_once("../DataTransferObjects/ProductionInputDTO.php");


    $inputType = $_GET['inputType'];
    $productionInputId = $_GET['productionInputId'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('mySql');
    $dataConnector->OpenConnection();
    if ($dataConnector->mysqlConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Busca os dados do insumo
    $productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
    $productionInputDAO->showErrors = 1;
    $filter = "tipoInsumo=".$inputType; if ($inputType == 0) $filter = null;
    $productionInputArray = $productionInputDAO->RetrieveRecordArray($filter);
    foreach ($productionInputArray as $productionInput) {
        $attributes = "";
        if ($productionInput->id == $productionInputId) $attributes = "selected='selected'";
        echo "<option ".$attributes." value=".$productionInput->id." class=".$productionInput->tipoInsumo." alt=".$productionInput->valor." >".$productionInput->descricao."</option>";
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
