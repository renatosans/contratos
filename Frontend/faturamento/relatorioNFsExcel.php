<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/PHPExcel.php");
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

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $invoiceDAO = new InvoiceDAO($dataConnector->sqlserverConnection);
    $invoiceDAO->showErrors = 1;

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


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="faturas.xls"');
    header("Cache-Control: max-age=0");


    function ClearBackground($cellRange) {
        global $objPhpExcel;

        $activeSheet = $objPhpExcel->getActiveSheet();
        $styleArray = array( 'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_NONE ) ),
                             'fill'    => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'FFFFFF') ),
                             'font'    => array( 'bold' => true ) );
        $activeSheet->getStyle($cellRange)->applyFromArray($styleArray);
    }

    function InsereLinhaPlanilha($numeroLinha, $primeiraColuna, $valores, $corFundo = null, $altura = null, $horizAlign = null)
    {
        global $objPhpExcel;

        $colNum = ord($primeiraColuna);
        $activeSheet = $objPhpExcel->getActiveSheet();

        $offset = 0;
        foreach($valores as $valor)
        {
            $cellValue = $valor; if (empty($valor)) $cellValue = " - ";
            $activeSheet->setCellValue(chr($colNum+$offset).$numeroLinha, $cellValue);
            $offset++;
        }

        if (!isset($horizAlign)) $horizAlign = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $vertAlign = PHPExcel_Style_Alignment::VERTICAL_TOP;

        $wrapText = false;
        $LFCR = chr(10).chr(13);
        foreach ($valores as $valor) {
            if (strpos($valor,$LFCR)) $wrapText = true;
        }

        $first = 0;
        $last = $offset-1;
        $styleArray = array( 'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN ) ) );
        if (isset($corFundo)) $styleArray['fill'] = array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>$corFundo ) );
        $activeSheet->getStyle(chr($colNum+$first).$numeroLinha.':'.chr($colNum+$last).$numeroLinha)->applyFromArray($styleArray);
        $activeSheet->getStyle(chr($colNum+$first).$numeroLinha.':'.chr($colNum+$last).$numeroLinha)->getAlignment()->setHorizontal($horizAlign);
        $activeSheet->getStyle(chr($colNum+$first).$numeroLinha.':'.chr($colNum+$last).$numeroLinha)->getAlignment()->setVertical($vertAlign);
        $activeSheet->getStyle(chr($colNum+$first).$numeroLinha.':'.chr($colNum+$last).$numeroLinha)->getAlignment()->setWrapText($wrapText);
        if (isset($altura)) $activeSheet->getRowDimension($numeroLinha)->setRowHeight($altura);
    }

    function BuildReportTable($startColumn, $startRow) {
        global $dataConnector;
        global $objPhpExcel;
        global $invoiceArray;
        global $salesPerson;
        global $model;
        global $equipmentCode;
        global $searchMethod;


        $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
        $billingItemDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
        $equipmentModelDAO->showErrors = 1;
        $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
        $salesPersonDAO->showErrors = 1;


        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'FATURAS');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn);
        $headers = array('Nº do documento', 'Data', 'Cliente', 'Observações', 'Detalhes', 'Vencimento', 'Total Nota (R$)', 'Nº Demonstrativo');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $activeSheet->getColumnDimension(chr($colNum+2))->setWidth(60);
        $activeSheet->getColumnDimension(chr($colNum+4))->setWidth(60);
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


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

        // Gera as linhas da tabela
        $grandTotal = 0;
        $LFCR = chr(10).chr(13);
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

                $details .= $equipmentSerial.' '.$salesPersonName.$LFCR;
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

            $currentRow++;
            $row = array();
            $row[0] = $invoice->docNum;
            $row[1] = $docDate;
            $row[2] = $invoice->cardName;
            $row[3] = $invoice->comments;
            $row[4] = $details;
            $row[5] = $docDueDate;
            $row[6] = $docTotal;
            $row[7] = $invoice->demFaturamento;
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $grandTotal += $invoice->docTotal;
        }

        $currentRow++;
        $total = array('Total Geral: '.number_format($grandTotal, 2, ',', '.'), '0', '0', '0', '0', '0', '0', '0');
        InsereLinhaPlanilha($currentRow, $startColumn, $total, '80BB80FF', 45, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->mergeCells(chr($colNum+0).$currentRow.':'.chr($colNum+sizeof($headers)-1).$currentRow);
    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relatório de NFs (Faturas)');
    ClearBackground('A1:AF999');

    $activeSheet->setCellValue('B2', 'Relatório de NFs (Faturas)');
    $styleArray = array( 'font'    => array( 'bold' => true, 'size' => 25) );
    $activeSheet->getStyle('B2:C2')->applyFromArray($styleArray);
    unset($styleArray);

    $activeSheet->setCellValue('B4', 'Data inicial: '.$startDate.'   '.'Data final: '.$endDate);

    BuildReportTable('B', '6');

    $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, "Excel5");
    $objWriter->save('php://output');


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
