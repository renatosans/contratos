<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/ConfigDAO.php");
    include_once("../DataTransferObjects/ConfigDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $equipmentCode = $_GET['equipmentCode'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Recupera a configuração de ordenação dos equipamentos
    $ordenarPorSerieFabrica = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, 'ordenarPorSerieFabrica');

    // Busca os equipamentos cadastrados para o parceiro de negócios
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    if ($ordenarPorSerieFabrica == 'true')
        $orderBy = "ORDER BY manufSN";
    else
        $orderBy = "ORDER BY internalSN";
    $equipmentArray = $equipmentDAO->RetrieveRecordArray("Customer = '".$businessPartnerCode."' ".$orderBy);
    foreach ($equipmentArray as $equipment) {
        $attributes = "";
        if ($equipment->insID == $equipmentCode) $attributes = "selected='selected'";
        if ($ordenarPorSerieFabrica == 'true')
            $serialNumber = $equipment->manufacturerSN." (".$equipment->internalSN.") ";
        else
            $serialNumber = $equipment->internalSN." (".$equipment->manufacturerSN.") ";
        $status = EquipmentDAO::GetStatusDescription($equipment->status);
        $location = ""; if (!empty($equipment->instLocation)) $location = " - ".$equipment->instLocation;
        echo "<option ".$attributes." value=".$equipment->insID.">".$serialNumber.$status.$location."</option>";
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
