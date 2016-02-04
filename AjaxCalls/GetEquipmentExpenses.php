<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/BillingDAO.php");
    include_once("../DataTransferObjects/BillingDTO.php");
    include_once("../DataAccessObjects/ServiceCallDAO.php");
    include_once("../DataTransferObjects/ServiceCallDTO.php");
    include_once("../DataAccessObjects/ExpenseDAO.php");
    include_once("../DataTransferObjects/ExpenseDTO.php");
    include_once("../DataAccessObjects/IndirectCostDAO.php");
    include_once("../DataTransferObjects/IndirectCostDTO.php");
    include_once("../DataAccessObjects/ProductionInputDAO.php");
    include_once("../DataTransferObjects/ProductionInputDTO.php");
    include_once("../DataAccessObjects/SupplyRequestDAO.php");
    include_once("../DataTransferObjects/SupplyRequestDTO.php");
    include_once("../DataAccessObjects/RequestItemDAO.php");
    include_once("../DataTransferObjects/RequestItemDTO.php");


    $equipmentCode = $_GET['equipmentCode'];
    $billingId = $_GET['billingId'];
    $showDetails = $_GET['showDetails'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // busca os dados do faturamento
    $billingDAO = new BillingDAO($dataConnector->mysqlConnection);
    $billingDAO->showErrors = 1;
    $billing = $billingDAO->RetrieveRecord($billingId);
    $startDate = $billing->dataInicial;
    $endDate = $billing->dataFinal;

    // localiza todos as despesas dos chamados no período
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $pediodFilter = "dataAbertura >= '".$startDate." 00:00' AND dataAbertura <= '".$endDate." 23:59' ";
    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray("cartaoEquipamento = ".$equipmentCode." AND ".$pediodFilter);
    $callEnumeration = "";
    foreach($serviceCallArray as $serviceCall) {
        if (!empty($callEnumeration)) $callEnumeration = $callEnumeration.", ";
        $callEnumeration = $callEnumeration.$serviceCall->id;
    }
    if (empty($callEnumeration)) $callEnumeration = "0";
    $expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
    $expenseDAO->showErrors = 1;
    $expenseArray = $expenseDAO->RetrieveRecordArray( "codigoChamado IN (".$callEnumeration.")" );


    // localiza todos os custos indiretos no período
    $indirectCostDAO = new IndirectCostDAO($dataConnector->mysqlConnection);
    $indirectCostDAO->showErrors = 1;
    $indirectCostIdArray = $indirectCostDAO->GetIds("chamadoServico_id IN (".$callEnumeration.") GROUP BY custoIndireto_id");
    $idEnumeration = "";
    foreach($indirectCostIdArray as $indirectCostId) {
        if (!empty($idEnumeration)) $idEnumeration .= ", ";
        $idEnumeration = $idEnumeration.$indirectCostId;
    }
    if (empty($idEnumeration)) $idEnumeration = "0";
    $indirectCostArray = $indirectCostDAO->RetrieveRecordArray("id IN (".$idEnumeration.")");


    // localiza todos os gastos com consumíveis no período
    $supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
    $supplyRequestDAO->showErrors = 1;
    $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
    $requestItemDAO->showErrors = 1;
    $pediodFilter = "data >= '".$startDate." 00:00' AND data <= '".$endDate." 23:59' ";
    $supplyRequestArray = $supplyRequestDAO->RetrieveRecordArray("codigoCartaoEquipamento = ".$equipmentCode." AND ".$pediodFilter);

    $details = "";
    if ((sizeof($expenseArray) < 1) && (sizeof($indirectCostArray) < 1) && (sizeof($supplyRequestArray) < 1)) {
        $details .= "<tr>";
        $details .= "    <td colspan='4' align='center'>Nenhum registro encontrado!</td>";
        $details .= "</tr>";
    }

    $somaTotais = 0;
    $productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
    $productionInputDAO->showErrors = 1;
    $inputTypeArray = $productionInputDAO->RetrieveInputTypes();
    foreach ($expenseArray as $expense) {
        $serviceCall = $serviceCallDAO->RetrieveRecord($expense->codigoChamado);
        $serieEquipamento = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $serviceCall->codigoCartaoEquipamento);
        $codigoInsumo = $expense->codigoInsumo;
        $descricao = $expense->quantidade.' '.$expense->nomeItem;
        if (!empty($codigoInsumo)) {
            $productionInput = $productionInputDAO->RetrieveRecord($codigoInsumo);
            $inputType =  $inputTypeArray[$productionInput->tipoInsumo];
            $numeroChamado = str_pad($expense->codigoChamado, 4, '0', STR_PAD_LEFT);
            $descricao = $inputType." ( Número do Chamado: ".$numeroChamado." )";
        }

        $details .= '<tr>';    
        $details .= '    <td>'.$serviceCall->dataAbertura.'</td>';
        $details .= '    <td>'.$serieEquipamento.'</td>';
        $details .= '    <td>'.$descricao.'</td>';
        $details .= '    <td>'.$expense->totalDespesa.'</td>';
        $details .= '</tr>';
        $somaTotais += $expense->totalDespesa;
    }

    foreach ($supplyRequestArray as $supplyRequest) {
        $equipmentSN = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $supplyRequest->codigoCartaoEquipamento);
        $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$supplyRequest->id);
        foreach ($requestItemArray as $requestItem) {
            $description = $requestItem->quantidade.' '.$requestItem->nomeItem;
            $details .= '<tr>';
            $details .= '    <td>'.$supplyRequest->data.'</td>';
            $details .= '    <td>'.$equipmentSN.'</td>';
            $details .= '    <td>'.$description.'</td>';
            $details .= '    <td>'.number_format($requestItem->total, 2, ',', '.').'</td>';
            $details .= '</tr>';
            $somaTotais += $requestItem->total;
        }
    }

    foreach($indirectCostArray as $indirectCost) {
        $serviceCallArray = $indirectCostDAO->GetDistributedExpenses($indirectCost->id);
        $serviceCallCount = sizeof($serviceCallArray);
        $custoRateado = 0; if ($serviceCallCount > 0) $custoRateado = $indirectCost->total / $serviceCallCount;
        $serialNumbers = "Vários";
        $productionInput = $productionInputDAO->RetrieveRecord($indirectCost->codigoInsumo);
        $inputType = $inputTypeArray[$productionInput->tipoInsumo];
        $descricao = $inputType." ( Custo Indireto Rateado )";
        $details .= '<tr>';
        $details .= '    <td>'.$indirectCost->data.'</td>';
        $details .= '    <td>'.$serialNumbers.'</td>';
        $details .= '    <td>'.$descricao.'</td>';
        $details .= '    <td>'.number_format($custoRateado, 2, ',', '.').'</td>';
        $details .= '</tr>';
        $somaTotais += $custoRateado;
    }
    $details .= '<tr style="color: red;"><td colspan="3" align="center">Total das despesas no período</td><td>'.number_format($somaTotais, 2, ',', '.').'</td></tr>';

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();


    if ($showDetails == "no") {
        echo $somaTotais;
        exit;
    }

?>

<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <style type="text/css">
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 11px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" class="sorTable" style="width: 95%; height: 95%;">
    <thead>
        <tr style="height: 30px; font-size: 15px;">
            <th>&nbsp;Data</th>
            <th>&nbsp;Equipamento</th>
            <th>&nbsp;Descrição</th>
            <th>&nbsp;Valor (R$)</th>
        </tr>
    </thead>
    <tbody style="font-size: 12px; font-weight: bold;">
    <?php
        echo $details;
    ?>
    </tbody>
    </table>
</body>
</html>
