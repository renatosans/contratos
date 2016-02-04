<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../ClassLibrary/EquipmentOperator.php");
    include_once("../DataAccessObjects/ContractDAO.php");
    include_once("../DataTransferObjects/ContractDTO.php");
    include_once("../DataAccessObjects/SubContractDAO.php");
    include_once("../DataTransferObjects/SubContractDTO.php");
    include_once("../DataAccessObjects/ContractItemDAO.php");
    include_once("../DataTransferObjects/ContractItemDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../DataAccessObjects/ContactPersonDAO.php");
    include_once("../DataTransferObjects/ContactPersonDTO.php");
    include_once("../DataAccessObjects/ReadingDAO.php");
    include_once("../DataTransferObjects/ReadingDTO.php");


    $dayOffset = $_GET['dayOffset'];
    $counterId = $_GET['counterId'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
    $readingDAO->showErrors = 1;


    // Busca os contratos que possuem leitura marcada para hoje
    $contractArray = $contractDAO->RetrieveRecordArray("status <> 3 AND status <> 4 AND diaLeitura = DAY(DATE_SUB( NOW(), INTERVAL ".$dayOffset." DAY))");
    $contractEnumeration = "";
    foreach ($contractArray as $contract) {
        if (!empty($contractEnumeration)) $contractEnumeration = $contractEnumeration.", ";
        $contractEnumeration = $contractEnumeration.$contract->id;
    }
    if (empty($contractEnumeration)) $contractEnumeration = "0"; // evita o crash da query, quando a lista está vazia

    // Busca os respectivos subcontratos
    $subContractEnumeration = SubContractDAO::GetSubcontractsByOwner($dataConnector->mysqlConnection, $contractEnumeration);

    // Busca os respectivos equipamentos (itens de contrato)
    $itemArray = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $subContractEnumeration);

    // Verifica se o array está vazio no final, juntamente com $rowCount
    $rowCount = 0;
    foreach ($itemArray as $contractItem) {
        // Verifica se o equipamento já possui leitura nos últimos 5 dias
        $filter = "codigoCartaoEquipamento=".$contractItem->codigoCartaoEquipamento." AND contador_id=".$counterId." AND origemLeitura_id=2 AND data > DATE_SUB( NOW(), INTERVAL 5 DAY)";
        $readingArray = $readingDAO->RetrieveRecordArray($filter);
        if (sizeof($readingArray) == 0) { // Nenhuma leitura do equipamento hoje
          $equipmentOperator = new EquipmentOperator($dataConnector->sqlserverConnection, $contractItem->businessPartnerCode, $contractItem->codigoCartaoEquipamento);
          if (($equipmentOperator->equipmentStatus == 'A') || ($equipmentOperator->equipmentStatus == 'L')) {
            $serialNumber = '<a rel="'.$equipmentOperator->equipmentCode.'" class="itemInfo" >'.$equipmentOperator->serialNumber.'</a>';
            $equipmentStatus = EquipmentDAO::GetStatusDescription($equipmentOperator->equipmentStatus);
            $telephoneNumber = $equipmentOperator->telephoneNumber;
            $reading = '<a href="Frontend/_leitura/editar.php?equipmentCode='.$equipmentOperator->equipmentCode.'&subContract=0" ><span class="ui-icon ui-icon-alert"></span></a>';
            echo '<tr><td>'.$serialNumber.' ('.$equipmentStatus.')</td><td>'.$telephoneNumber.'</td><td>'.$reading.'</td></tr>';
            $rowCount++;
          }
        }
    }
    if ( (sizeof($itemArray) == 0) || ($rowCount == 0) ) { // Todas as leituras de hoje foram completadas
        echo '<tr><td colspan="3" align="center" >Nenhum registro encontrado!</td></tr>';
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>

<script type="text/javascript" >
    function GetEquipmentInfo(equipmentLink) {
        var equipmentCode = equipmentLink.attr("rel");
        if (!equipmentCode) equipmentCode = 0;
    
        var targetUrl = "AjaxCalls/GetEquipmentInfo.php?equipmentCode=" + equipmentCode;
        $("#leituras").append("<div id='popup'></div>");
        $("#popup").load(targetUrl).dialog({modal:true, width: 560, height: 340, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
    }

    $(".itemInfo").css('text-decoration', 'underline');
    $(".itemInfo").click( function() { GetEquipmentInfo($(this)); } );
</script>
