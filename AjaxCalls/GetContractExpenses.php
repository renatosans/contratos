<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/UnixTime.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/ContractDAO.php");
    include_once("../DataTransferObjects/ContractDTO.php");
    include_once("../DataAccessObjects/SubContractDAO.php");
    include_once("../DataTransferObjects/SubContractDTO.php");
    include_once("../DataAccessObjects/ContractItemDAO.php");
    include_once("../DataTransferObjects/ContractItemDTO.php");
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
    

    $contractId = $_GET['contractId'];
    if (empty($contractId)) $contractId = 0;
    $period = $_GET['period'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }


    // Obtem os dados do contrato
    $dataAbertura = null;
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $contract = $contractDAO->RetrieveRecord($contractId);
    if ($contract != null) {
        $dataAbertura = $contract->dataAssinatura;
    }

    // Define a faixa de datas para as despesas
    $startDate = null;
    $endDate = null;
    $today = new UnixTime(time());
    switch ($period)
    {
        case 1:  $startDate = date("Y-m-d",mktime(0,0,0,date("m")-1,1,date("Y"))); // mês passado
                 $lastMonth = mktime(0,0,0,date("m")-1,1,date("Y"));
                 $daysInMonth = cal_days_in_month(CAL_GREGORIAN, date("m", $lastMonth), date("Y", $lastMonth));
                 $endDate = date("Y-m-d",mktime(0,0,0,date("m")-1,$daysInMonth,date("Y")));
                 break;
        case 2:  $startDate = date("Y-m-d", $today->AddMonths(-1)); // últimos 30 dias
                 $endDate = date("Y-m-d",time());
                 break;
        default: $startDate = $dataAbertura; // desde a abertura do contrato
                 $endDate = date("Y-m-d",time());
                 break;
    }

    // Busca os itens(equipamentos) pertencentes ao contrato
    $subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
    $subContractDAO->showErrors = 1;
    $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id = ".$contractId);
    $subContractEnumeration = "";
    foreach($subContractArray as $subContract) {
        if (!empty($subContractEnumeration)) $subContractEnumeration = $subContractEnumeration.", ";
        $subContractEnumeration = $subContractEnumeration.$subContract->id;
    }
    if (empty($subContractEnumeration)) $subContractEnumeration = "0"; // evita o crash da query, quando a lista está vazia

    $contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
    $contractItemDAO->showErrors = 1;
    $itemArray = $contractItemDAO->RetrieveRecordArray("subContrato_id IN (".$subContractEnumeration.")");
    $equipmentEnumeration = "";
    foreach($itemArray as $contractItem) {
        if (!empty($equipmentEnumeration)) $equipmentEnumeration = $equipmentEnumeration.", ";
        $equipmentEnumeration = $equipmentEnumeration.$contractItem->codigoCartaoEquipamento;
    }
    if (empty($equipmentEnumeration)) $equipmentEnumeration = "0"; // evita o crash da query, quando a lista está vazia

    // localiza todos as despesas dos chamados no período
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $pediodFilter = "dataAbertura >= '".$startDate." 00:00' AND dataAbertura <= '".$endDate." 23:59' ";
    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray("cartaoEquipamento IN (".$equipmentEnumeration.") AND ".$pediodFilter);
    $callEnumeration = "";
    foreach($serviceCallArray as $serviceCall) {
        if (!empty($callEnumeration)) $callEnumeration = $callEnumeration.", ";
        $callEnumeration = $callEnumeration.$serviceCall->id;
    }
    if (empty($callEnumeration)) $callEnumeration = "0";
    $expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
    $expenseDAO->showErrors = 1;
    $expenseArray = $expenseDAO->RetrieveRecordArray("codigoChamado IN (".$callEnumeration.")");


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
    $supplyRequestArray = $supplyRequestDAO->RetrieveRecordArray("codigoCartaoEquipamento IN (".$equipmentEnumeration.") AND ".$pediodFilter);


    if ( (sizeof($expenseArray) < 1) && (sizeof($indirectCostArray) < 1) && (sizeof($supplyRequestArray) < 1) ) {
        echo "<tr>";
        echo "    <td colspan='4' align='center'>Nenhum registro encontrado!</td>";
        echo "</tr>";
        exit;
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
            $descricao = "<a href='Frontend/chamados/editar.php?id=".$expense->codigoChamado."'>".$inputType." ( Número do Chamado: ".$numeroChamado." )</a>";
        }

        echo '<tr>';
        echo '    <td>'.$serviceCall->dataAbertura.'</td>';
        echo '    <td>'.$serieEquipamento.'</td>';
        echo '    <td>'.$descricao.'</td>';
        echo '    <td>'.number_format($expense->totalDespesa, 2, ',', '.').'</td>';
        echo '</tr>';
        $somaTotais += $expense->totalDespesa;
    }

    foreach ($supplyRequestArray as $supplyRequest) {
        $equipmentSN = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $supplyRequest->codigoCartaoEquipamento);
        $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$supplyRequest->id);
        foreach ($requestItemArray as $requestItem) {
            $description = $requestItem->quantidade.' '.$requestItem->nomeItem;
            echo '<tr>';
            echo '    <td>'.$supplyRequest->data.'</td>';
            echo '    <td>'.$equipmentSN.'</td>';
            echo '    <td>'.$description.'</td>';
            echo '    <td>'.number_format($requestItem->total, 2, ',', '.').'</td>';
            echo '</tr>';
            $somaTotais += $requestItem->total;
        }
    }

    foreach($indirectCostArray as $indirectCost) {
        $serviceCallArray = $indirectCostDAO->GetDistributedExpenses($indirectCost->id);
        $serviceCallCount = sizeof($serviceCallArray);
        $custoRateado = 0; if ($serviceCallCount > 0) $custoRateado = $indirectCost->total / $serviceCallCount;
        $serialNumbers = "Vários";
        $productionInput = $productionInputDAO->RetrieveRecord($indirectCost->codigoInsumo);
        $inputType =  $inputTypeArray[$productionInput->tipoInsumo];
        $descricao = "<a href='Frontend/custoIndireto/editar.php?id=".$indirectCost->id."'>".$inputType." ( Custo Indireto Rateado )"."</a>";
        echo '<tr>';
        echo '    <td>'.$indirectCost->data.'</td>';
        echo '    <td>'.$serialNumbers.'</td>';
        echo '    <td>'.$descricao.'</td>';
        echo '    <td>'.number_format($custoRateado, 2, ',', '.').'</td>';
        echo '</tr>';
        $somaTotais += $custoRateado;
    }
    echo '<tr style="color: red;"><td colspan="3" align="center">Total das despesas no período</td><td>'.number_format($somaTotais, 2, ',', '.').'</td></tr>';


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
