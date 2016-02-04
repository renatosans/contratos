<html>
<body>
<?php
    $equipmentCode = $_GET['equipmentCode'];
    $showSla = null; if (isset($_GET['showSla'])) $showSla = $_GET['showSla'];


    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");

    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
    $businessPartnerDAO->showErrors = 1;


    $manufacturerSN = "";
    $internalSN = "";
    $itemCode = "";
    $itemName = "";
    $customer = "";
    $address = "";
    $instLocation = "";
    $sla = "";
    $equipmentDTO = $equipmentDAO->RetrieveRecord($equipmentCode);
    if (!empty($equipmentDTO)) {
        $manufacturerSN = $equipmentDTO->manufacturerSN;
        $internalSN = $equipmentDTO->internalSN;
        $itemCode = $equipmentDTO->itemCode;
        $itemName = $equipmentDTO->itemName;
        $customer = $equipmentDTO->customer;
        $address = $equipmentDTO->addressType." ".$equipmentDTO->street." ".$equipmentDTO->streetNo." ".$equipmentDTO->building."   CEP: ".$equipmentDTO->zip;
        $address = $address."   "."Bairro: ".$equipmentDTO->block."   ".$equipmentDTO->city." ".$equipmentDTO->state." ".$equipmentDTO->country;
        $instLocation = $equipmentDTO->instLocation;
        if (!empty($equipmentDTO->sla))
            $sla = $equipmentDTO->sla.' horas';
    }
    $customerName = "";
    $clientDTO = $businessPartnerDAO->RetrieveRecord($customer);
    if (!empty($clientDTO)) {
        $customerName = $clientDTO->cardName;
    }


    echo "<label>Número série Fabricante<br/>";
    echo "<input type='text' style='width:99%;' value='".$manufacturerSN."' />";
    echo "</label>";
    echo "<label>Nosso número de série<br/>";
    echo "<input type='text' style='width:99%;' value='".$internalSN."' />";
    echo "</label>";
    echo "<label>Descrição do equipamento<br/>";
    echo "<input type='text' style='width:99%;' value='".$itemName."' />";
    echo "</label>";
    echo "<label>Cliente(Business Partner)<br/>";
    echo "<input type='text' style='width:99%;' value='".$customerName."' />";
    echo "</label>";
    echo "<label>Endereço<br/>";
    echo "<input type='text' style='width:99%;' value='".$address."' />";
    echo "</label>";
    echo "<label>Local de instalação<br/>";
    echo "<input type='text' style='width:99%;' value='".$instLocation."' />";
    echo "</label>";
    if ($showSla) {
        echo "<label>Nível de Serviço (SLA)<br/>";
        echo "<input type='text' style='width:99%;' value='".$sla."' />";
        echo "</label>";
    } 
?>
</body>
</html>
