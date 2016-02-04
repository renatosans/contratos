<?php

    include_once("../../defines.php");
    include_once("../../ClassLibrary/Calendar.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/BillingItemDAO.php");
    include_once("../../DataTransferObjects/BillingItemDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $billingMonth = $_GET['billingMonth'];
    $billingYear = $_GET['billingYear'];
    $salesPerson = $_GET['salesPerson'];
    $searchMethod = $_GET['searchMethod'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
    $billingItemDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
    $counterDAO->showErrors = 1;
    $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
    $salesPersonDAO->showErrors = 1;


    // Busca os faturamentos que se enquadram no filtro aplicado
    $billingItemArray = array();
    if (($searchMethod == 0) || ($searchMethod == 2)) {
        $filter = "businessPartnerCode='".$businessPartnerCode."' AND mesReferencia = ".$billingMonth." AND anoReferencia = ".$billingYear." AND incluirRelatorio=1";
        $joins = "JOIN faturamento ON item.codigoFaturamento = faturamento.id";
        $billingItemArray = $billingItemDAO->RetrieveRecordArray2($filter, $joins);
    }
    if ($searchMethod == 1) {
        $filter = "mesReferencia = ".$billingMonth." AND anoReferencia = ".$billingYear." AND incluirRelatorio=1";
        $joins = "JOIN faturamento ON item.codigoFaturamento = faturamento.id";
        $billingItemArray = $billingItemDAO->RetrieveRecordArray2($filter, $joins);
    }
    if ($searchMethod == 3) {
        $filter = "codigoCartaoEquipamento=".$equipmentCode." AND mesReferencia = ".$billingMonth." AND anoReferencia = ".$billingYear." AND incluirRelatorio=1";
        $joins = "JOIN faturamento ON item.codigoFaturamento = faturamento.id";
        $billingItemArray = $billingItemDAO->RetrieveRecordArray2($filter, $joins);
    }

    // Busca os contadores cadastrados no sistema
    $retrievedArray = $counterDAO->RetrieveRecordArray();
    $counterArray = array();
    foreach ($retrievedArray as $counter) {
        $counterArray[$counter->id] = $counter->nome;
    }

    // Busca os vendedores cadastrados no sistema
    $retrievedArray = $salesPersonDAO->RetrieveRecordArray();
    $salesPersonArray = array();
    foreach ($retrievedArray as $salesPersonDTO) {
        $salesPersonArray[$salesPersonDTO->slpCode] = $salesPersonDTO->slpName;
    }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >
    <link href="<?php echo $pathCss; ?>/jquery-ui.css"  rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery.min.js" ></script>
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery-ui.min.js" ></script>
    <style type="text/css">
        @page { margin:0.8cm; size: landscape; }
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 8px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Relatório de Faturamento</title>
</head>
<body>
    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >RELATÓRIO DE FATURAMENTO</h3><br/>
            <h3 style="border:0; margin:0;" >Mês: <?php $calendar = new Calendar(); echo $calendar->GetMonthName($billingMonth); ?>&nbsp;&nbsp;&nbsp;Ano: <?php echo $billingYear; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Cliente</td><td>Descrição Equipamento</td><td>Série Fabricante</td><td>Nosso Núm. Série</td><td>Medidor</td><td>Data Leitura</td><td>Medição Final</td><td>Medição Inicial</td><td>Consumo (com Acresc/Desc)</td><td>Franquia</td><td>Excedente</td><td>Tarifa sobre exced.</td><td>Fixo (R$)</td><td>Variável (R$)</td><td>Total (com Acresc/Desc)</td><td>Vendedor</td></tr>
            <?php
                $grandTotal = 0;
                foreach ($billingItemArray as $billingItem) {
                    $equipment = $equipmentDAO->RetrieveRecord($billingItem->codigoCartaoEquipamento);
                    $receitaTotal = $billingItem->total + $billingItem->acrescimoDesconto;
                    if ($salesPerson > 0) {
                        if ($equipment->salesPerson != $salesPerson) continue;
                    }
                    if (($searchMethod == 1) || ($searchMethod == 2)) {
                        if (!empty($model)) {
                            $modelMatched = false;
                            if (strpos($equipment->itemName, $model)) $modelMatched = true;
                            if (!$modelMatched) continue;
                        }
                    }
                    $salesPersonCode = $equipment->salesPerson; if (empty($salesPersonCode)) $salesPersonCode = -1;
                    $salesPersonName = $salesPersonArray[$salesPersonCode];
                    echo '<tr bgcolor="WHITE" ><td>'.$equipment->custmrName.'</td><td>'.$equipment->itemName.'</td><td>'.$equipment->manufacturerSN.'</td><td>'.$equipment->internalSN.'</td><td>'.$counterArray[$billingItem->counterId].'</td><td>'.$billingItem->dataLeitura.'</td><td>'.$billingItem->medicaoFinal.'</td><td>'.$billingItem->medicaoInicial.'</td><td>'.$billingItem->consumo.'</td><td>'.$billingItem->franquia.'</td><td>'.$billingItem->excedente.'</td><td>'.number_format($billingItem->tarifaSobreExcedente, 6, ',', '.').'</td><td>'.number_format($billingItem->fixo, 2, ',', '.').'</td><td>'.number_format($billingItem->variavel, 2, ',', '.').'</td><td>'.number_format($receitaTotal, 2, ',', '.').'</td><td>'.$salesPersonName.'</td></tr>';
                    $grandTotal += $receitaTotal; // Soma a receita total do equipamento ( fixo mais variável )
                }
                echo '<tr><td colspan=16 ><h3>Total da Receita: '.number_format($grandTotal, 2, ',', '.').'</h3></td></tr>';
            ?>
            </table>
            <div style="clear:both;"><br/></div>
        </div>
        <div style="clear:both;"><br/></div>

        <div id="pageBottom" style="height:12px;"></div>
    </div>
<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
</body>
</html>
