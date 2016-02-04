<?php

    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/InvoiceDAO.php");
    include_once("../../DataTransferObjects/InvoiceDTO.php");
    include_once("../../DataAccessObjects/BillingItemDAO.php");
    include_once("../../DataTransferObjects/BillingItemDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/EquipmentModelDAO.php");
    include_once("../../DataTransferObjects/EquipmentModelDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
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
    $invoiceDAO = new InvoiceDAO($dataConnector->sqlserverConnection);
    $invoiceDAO->showErrors = 1;
    $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
    $billingItemDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
    $equipmentModelDAO->showErrors = 1;
    $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
    $salesPersonDAO->showErrors = 1;

    // Busca as notas canceladas no período
    $returnedInvoiceArray = $invoiceDAO->RetrieveReturnedInvoices("(T0.DocDate BETWEEN '".$startDate."' AND '".$endDate."' OR T3.DocDate BETWEEN '".$startDate."' AND '".$endDate."') AND T3.DocTotal > 0");
    $returnedInvoiceList = "";
    foreach ($returnedInvoiceArray as $returnedInvoice) {
        if (!empty($returnedInvoiceList)) $returnedInvoiceList .= ", ";
        $returnedInvoiceList = $returnedInvoiceList.$returnedInvoice->docNum;
    }
    if (empty($returnedInvoiceList)) $returnedInvoiceList = "0";

    // Busca as notas que se enquadram no filtro aplicado
    $invoiceArray = array();
    if (($searchMethod == 0) || ($searchMethod == 2)) {
        $joins = "JOIN INV1 ON OINV.DocEntry = INV1.DocEntry";
        $filter = "cardCode='".$businessPartnerCode."' AND OINV.DocDate BETWEEN '".$startDate."' AND '".$endDate."' AND INV1.ItemCode = 'S001' AND OINV.DocNum NOT IN (".$returnedInvoiceList.")";
        $invoiceArray = $invoiceDAO->RetrieveRecordArray($joins, $filter);
    }
    if ($searchMethod == 1) {
        $joins = "JOIN INV1 ON OINV.DocEntry = INV1.DocEntry";
        $filter = "OINV.DocDate BETWEEN '".$startDate."' AND '".$endDate."' AND INV1.ItemCode = 'S001' AND OINV.DocNum NOT IN (".$returnedInvoiceList.")";
        $invoiceArray = $invoiceDAO->RetrieveRecordArray($joins, $filter);
    }
    if ($searchMethod == 3) {
        $joins = "JOIN INV1 ON OINV.DocEntry = INV1.DocEntry";
        $filter = "cardCode='".$businessPartnerCode."' AND OINV.DocDate BETWEEN '".$startDate."' AND '".$endDate."' AND INV1.ItemCode = 'S001' AND OINV.DocNum NOT IN (".$returnedInvoiceList.")";
        $invoiceArray = $invoiceDAO->RetrieveRecordArray($joins, $filter);
    }

    // Busca os vendedores cadastrados no sistema
    $retrievedArray = $salesPersonDAO->RetrieveRecordArray();
    $salesPersonArray = array();
    foreach ($retrievedArray as $salesPersonDTO) {
        $salesPersonArray[$salesPersonDTO->slpCode] = $salesPersonDTO->slpName;
    }

    // Busca os modelos cadastrados no sistema
    $modelArray = array(0=>"");
    $equipmentModelArray = $equipmentModelDAO->RetrieveRecordArray();
    foreach($equipmentModelArray as $modelDTO) {
        $modelArray[$modelDTO->id] = $modelDTO->modelo;
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
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 11px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Relatório de NFs (Faturas)</title>
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
            <h3 style="border:0; margin:0;" >RELATÓRIO DE NFs (FATURAS)</h3><br/>
            <h3 style="border:0; margin:0;" >Data inicial: <?php echo $startDate; ?>&nbsp;&nbsp;&nbsp;Data final: <?php echo $endDate; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Nº do documento</td><td>Data</td><td>Cliente</td><td>Observações</td><td>Detalhes</td><td>Vencimento</td><td>Total Nota (R$)</td><td>Nº Demonstrativo</td></tr>
            <?php
                $grandTotal = 0;
                foreach ($invoiceArray as $invoice) {
                    $docDate = empty($invoice->docDate) ? '' : $invoice->docDate->format('d/m/Y');
                    $docDueDate = empty($invoice->docDueDate) ? '' : $invoice->docDueDate->format('d/m/Y');
                    $docTotal = number_format($invoice->docTotal, 2, ',', '.');

                    $details = "";
                    $salesPersons = array();
                    $itemNames = "";
                    $equipmentIds = array();
                    $billingItemArray = $billingItemDAO->RetrieveRecordArray("codigoFaturamento=".$invoice->demFaturamento);
                    foreach ($billingItemArray as $billingItem) {
                        $equipment = $equipmentDAO->RetrieveRecord($billingItem->codigoCartaoEquipamento);
                        $equipmentSerial = $equipment->manufacturerSN; 
                        $salesPersonCode = $equipment->salesPerson; if (empty($salesPersonCode)) $salesPersonCode = -1;
                        $salesPersonName = $salesPersonArray[$salesPersonCode];

                        $details .= $equipmentSerial.' '.$salesPersonName.'<br/>';
                        if ($salesPersonCode > 0) array_push($salesPersons, $salesPersonCode);
                        $itemNames .= $equipment->itemName;
                        if ($equipment->insID > 0) array_push($equipmentIds, $equipment->insID);
                    }
                    if ($salesPerson > 0) {
                        if (!in_array($salesPerson, $salesPersons)) continue;
                    }
                    if (($searchMethod == 1) || ($searchMethod == 2)) {
                        if (!empty($model)) {
                            $modelMatched = false;
                            if (strpos($itemNames, $model)) $modelMatched = true;
                            if (!$modelMatched) continue;
                        }
                    }
                    if ($searchMethod == 3) {
                        if (!in_array($equipmentCode, $equipmentIds)) continue;
                    }

                    echo '<tr bgcolor="WHITE" ><td>'.$invoice->docNum.'</td><td>'.$docDate.'</td><td>'.$invoice->cardName.'</td><td>'.$invoice->comments.'</td><td>'.$details.'</td><td>'.$docDueDate.'</td><td>'.$docTotal.'</td><td>'.$invoice->demFaturamento.'</td></tr>';
                    $grandTotal += $invoice->docTotal;
                }
                echo '<tr><td colspan=8 ><h3>Total Geral: '.number_format($grandTotal, 2, ',', '.').'</h3></td></tr>';
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
