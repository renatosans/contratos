<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/SupplyRequestDAO.php");
    include_once("../DataTransferObjects/SupplyRequestDTO.php");
    include_once("../DataAccessObjects/RequestItemDAO.php");
    include_once("../DataTransferObjects/RequestItemDTO.php");
    include_once("../DataAccessObjects/InventoryItemDAO.php");
    include_once("../DataTransferObjects/InventoryItemDTO.php");
    include_once("../DataAccessObjects/ReadingDAO.php");
    include_once("../DataTransferObjects/ReadingDTO.php");


    $supplyRequestId = $_GET['supplyRequestId'];
    $cutoffDate = $_GET['cutoffDate'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto relacional
    $supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
    $supplyRequestDAO->showErrors = 1;
    $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
    $requestItemDAO->showErrors = 1;
    $inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
    $inventoryItemDAO->showErrors = 1;
    $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
    $readingDAO->showErrors = 1;

    // Busca os dados da solicitação de consumível
    $equipmentCode = 0;
    $itemEnumeration = '';
    $supplyRequest = $supplyRequestDAO->RetrieveRecord($supplyRequestId);
    if ($supplyRequest != null) {
        $equipmentCode = $supplyRequest->codigoCartaoEquipamento;
        $reqItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$supplyRequest->id);
        foreach ($reqItemArray as $reqItem) {
            if (!empty($itemEnumeration)) $itemEnumeration .= ', ';
            $itemEnumeration .= "'".$reqItem->codigoItem."'";
        }
    }

    // busca as solicitações prévias para este consumível ( leva em conta o equipamento e a data)
    $previousSupplyArray = $supplyRequestDAO->RetrieveRecordArray("codigoCartaoEquipamento = ".$equipmentCode." AND data < '".$cutoffDate."' ORDER BY data DESC");

    $consumption = "";
    if (sizeof($previousSupplyArray) < 1) {
        $consumption .= "<tr>";
        $consumption .= "    <td colspan='6' align='center'>Nenhum registro encontrado!</td>";
        $consumption .= "</tr>";
    }

    function GetSupplyReadingsTotal($supplyRequestId, $counterEnumerationXml) {
        $counterTotal = 0;
        global $readingDAO;

        // obtem os medidores de utilização do consumível
        $xml = simplexml_load_string($counterEnumerationXml);

        $readingArray = $readingDAO->RetrieveRecordArray("consumivel_id=".$supplyRequestId);
        foreach($readingArray as $reading) {
            foreach ($xml as $element) {
                if ($reading->codigoContador == $element["id"]) $counterTotal += $reading->contagem;
            }
        }

        return $counterTotal;
    }

    function GetUtilizationPercentage($measuredConsumption, $itemAmount, $itemDurability) {
        $percentage = "";

        $utilizationRate = 0;
        if (($itemAmount  > 0) && ($itemDurability > 0))
            $utilizationRate = 100 * ( $measuredConsumption / ($itemAmount * $itemDurability) );

        if (($utilizationRate >= 0) && ($utilizationRate <= 100)) {
            $color = 'green'; if ($utilizationRate < 80) $color = 'red';
            $percentage = number_format($utilizationRate, 1, ",", ".").'%';
            $percentage = '<span style="color:'.$color.'" >'.$percentage.'</span>';
        }

        // Acima de 100% significa que o consumível já acabou
        if ($utilizationRate > 100)
            $percentage = '<span style="color:green" >Esgotado</span>';

        return $percentage;
    }

    foreach ($previousSupplyArray as $previousSupplyRequest) {
        $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$previousSupplyRequest->id." AND codigoItem IN (".$itemEnumeration.")");
        if (sizeof($requestItemArray) < 1) {
            $consumption .= "<tr>";
            $consumption .= "    <td colspan='6' align='center'>Nenhum registro encontrado!</td>";
            $consumption .= "</tr>";
        }

        foreach ($requestItemArray as $requestItem) {
            $inventoryItem = $inventoryItemDAO->RetrieveRecord($requestItem->codigoItem);
            $counterTotal = GetSupplyReadingsTotal($supplyRequest->id, $inventoryItem->serializedData);
            $counterTotalBefore = GetSupplyReadingsTotal($previousSupplyRequest->id, $inventoryItem->serializedData);
            $diff = 0;
            $percentage = "";
            if ($counterTotal >= $counterTotalBefore) {
                if (($counterTotal > $counterTotalBefore) && ($counterTotalBefore != 0)) $diff = $counterTotal - $counterTotalBefore;
                $percentage = GetUtilizationPercentage($diff, $requestItem->quantidade, $inventoryItem->durability);
            }
            $consumption .= '<tr>';
            $consumption .= '    <td>'.$previousSupplyRequest->data.'</td>';
            $consumption .= '    <td>'.$requestItem->quantidade.' '.$requestItem->nomeItem.'</td>';
            $consumption .= '    <td>'.$inventoryItem->durability.'</td>';
            $consumption .= '    <td>'.$counterTotalBefore.'</td>';
            $consumption .= '    <td>'.$diff.'</td>';
            $consumption .= '    <td>'.$percentage.'</td>';
            $consumption .= '</tr>';
        }
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>

<html>
<head>
    <style type="text/css">
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 11px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
</head>
<body>
    <div style="text-align: center;"><h3>UTILIZAÇÃO DO CONSUMÍVEL</h3></div>
    <table border="0" cellpadding="0" cellspacing="0" class="sorTable" style="width: 95%; height: 80%;">
    <thead>
        <tr style="height: 25px; font-size: 15px;">
            <th style="width:15%;" >Data da solicitação</th>
            <th style="width:30%;" >Consumível</th>
            <th style="width:15%;" >Durabilidade</th>
            <th style="width:15%;" >Aferição do Contador</th>
            <th style="width:15%;" >Páginas Extraídas</th>
            <th style="width:15%;" >Porcentagem Utilização</th>
        </tr>
    </thead>
    <tbody style="font-size: 12px; font-weight: bold;">
    <?php
        echo $consumption;
    ?>
    </tbody>
    </table>
</body>
</html>
