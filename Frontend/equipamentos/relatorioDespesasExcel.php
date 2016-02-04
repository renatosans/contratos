<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/EquipmentExpenseDAO.php");
    include_once("../../DataTransferObjects/EquipmentExpenseDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $searchMethod = $_GET['searchMethod'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $equipmentExpenseDAO = new EquipmentExpenseDAO($dataConnector->sqlserverConnection);
    $equipmentExpenseDAO->showErrors = 1;


    // Busca as despesas que se enquadram no filtro aplicado
    if ($searchMethod == 0) {
        $filter = "codigoCliente='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter = "codigoCliente <> ''"; // qualquer cliente
    }
    if ($searchMethod == 1) {
        $filter = "codigoModelo=".$model;
        if (empty($model)) $filter = "codigoModelo <> ''"; // qualquer modelo
    }
    if ($searchMethod == 2) {
        $filter1 = "codigoCliente='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter1 = "codigoCliente <> ''"; // qualquer cliente
        $filter2 = "codigoModelo=".$model;
        if (empty($model)) $filter2 = "codigoModelo <> ''"; // qualquer modelo
        $filter = $filter1." AND ".$filter2;
    }
    if ($searchMethod == 3) {
        $filter = "codigoEquipamento=".$equipmentCode;
    }
    $filter .= " AND dataDespesa >= '".$startDate." 00:00' AND dataDespesa <= '".$endDate." 23:59'";
    $equipmentExpenseArray = $equipmentExpenseDAO->RetrieveRecordArray($filter);


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="despesas.xls"');
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
        global $equipmentExpenseArray;


        $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;

        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'DESPESAS');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn);
        $headers = array('Cliente', 'Série', 'Modelo', 'Fabricante', 'Data', 'Descrição', 'Valor (R$)');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $activeSheet->getColumnDimension(chr($colNum+1))->setWidth(50);
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Gera as linhas da tabela
        $previousCustomer = '';
        $currentCustomer = '';
        $currentValue = 0;
        $grandTotal = 0;
        foreach ($equipmentExpenseArray as $equipmentExpense) {
            $currentCustomer = $equipmentExpense->nomeCliente;
            if ($currentCustomer != $previousCustomer) {
                if (!empty($previousCustomer)) {
                    $currentRow++;
                    $subtotal = array('SUBTOTAL', '', '', '', '', '', number_format($currentValue, 2, ',', '.'));
                    InsereLinhaPlanilha($currentRow, $startColumn, $subtotal, '80AAFFFF');
                    $currentValue = 0;
                }
                $previousCustomer = $currentCustomer;
            }

            $currentRow++;
            $row = array();
            $row[0] = $equipmentExpense->nomeCliente;
            $row[1] = $equipmentExpense->serieEquipamento;
            $row[2] = $equipmentExpense->tagModelo;
            $row[3] = $equipmentExpense->fabricante;
            $row[4] = empty($equipmentExpense->dataDespesa) ? '' : $equipmentExpense->dataDespesa->format('d/m/Y');
            $row[5] = $equipmentExpense->descricaoDespesa;
            $row[6] = number_format($equipmentExpense->totalDespesa, 2, ',', '.');
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $currentValue += $equipmentExpense->totalDespesa;
            $grandTotal += $equipmentExpense->totalDespesa;
        }
        $currentRow++;
        $subtotal = array('SUBTOTAL', '', '', '', '', '', number_format($currentValue, 2, ',', '.'));
        InsereLinhaPlanilha($currentRow, $startColumn, $subtotal, '80AAFFFF');

        $currentRow++;
        $total = array('TOTAL', '', '', '', '', '', number_format($grandTotal, 2, ',', '.'));
        InsereLinhaPlanilha($currentRow, $startColumn, $total, 'FFAA2040');
    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relatório de Despesas');
    ClearBackground('A1:AF1999');

    $activeSheet->setCellValue('B2', 'Relatório de Despesas');
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
