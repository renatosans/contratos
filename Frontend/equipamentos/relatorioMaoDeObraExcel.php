<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/Calendar.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/LaborExpenseDAO.php");
    include_once("../../DataTransferObjects/LaborExpenseDTO.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/ServiceStatisticsDAO.php");
    include_once("../../DataTransferObjects/ServiceStatisticsDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $month = $_GET['month'];
    $year = $_GET['year'];
    $despesaMensal = $_GET['despesaMensal'];
    $searchMethod = $_GET['searchMethod'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $laborExpenseDAO = new LaborExpenseDAO($dataConnector->sqlserverConnection);
    $laborExpenseDAO->showErrors = 1;


    // Busca os registros que se enquadram no filtro aplicado
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
    $filter .= " AND mesReferencia = ".$month." AND anoReferencia = ".$year;
    $laborExpenseArray = $laborExpenseDAO->RetrieveRecordArray($filter);


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="maoDeObra.xls"');
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
        global $month;
        global $year;
        global $despesaMensal;
        global $laborExpenseArray;


        $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
        $serviceCallDAO->showErrors = 1;
        $serviceStatisticsDAO = new ServiceStatisticsDAO($dataConnector->mysqlConnection);
        $serviceStatisticsDAO->showErrors = 1;


        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'CUSTOS DE MÃO DE OBRA');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn);
        $headers = array('Cliente', 'Nº Série Fabricante', 'Modelo', 'Fabricante', 'Número do Chamado', 'Tempo de Atendimento', 'Tempo Total Area Técnica', 'Valor Despesa (R$)');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;
        }
        $activeSheet->getColumnDimension(chr($colNum+3))->setWidth(60);
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Busca as estatísticas de atendimento do mês escolhido
        $stats = $serviceStatisticsDAO->RetrieveRecord($month, $year);
        $tempoTotalAtendimento = $stats->tempoEmAtendimento;
        $totalEmSegundos = $stats->totalEmSegundos;


        // Gera as linhas da tabela
        $grandTotal = 0;
        $valorPrevio = "";
        $totalCliente = 0;
        foreach ($laborExpenseArray as $laborExpense) {
            $serviceCall = $serviceCallDAO->RetrieveRecord($laborExpense->numeroChamado);
            $valorDespesa = ($serviceCall->duracaoEmSegundos / $totalEmSegundos) * $despesaMensal;

            $valorAtual = $laborExpense->nomeCliente;
            if ($valorPrevio != $valorAtual) {
                if (!empty($valorPrevio)) {
                    $currentRow++;
                    $subTotal = array($valorPrevio, '', '', '', '', '', '', number_format($totalCliente, 2, ',', '.'));
                    InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, '80AAFFFF', 20, PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $totalCliente = 0;
                }
                $valorPrevio = $valorAtual;
            }

            $currentRow++;
            $row = array();
            $row[0] = $laborExpense->nomeCliente;
            $row[1] = $laborExpense->serieEquipamento;
            $row[2] = $laborExpense->tagModelo;
            $row[3] = $laborExpense->fabricante;
            $row[4] = $laborExpense->numeroChamado;
            $row[5] = $serviceCall->tempoAtendimento;
            $row[6] = $tempoTotalAtendimento;
            $row[7] = number_format($valorDespesa, 2, ',', '.');
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $grandTotal += $valorDespesa;
            $totalCliente += $valorDespesa;
        }

        $currentRow++;
        $subTotal = array($valorPrevio, '', '', '', '', '', '', number_format($totalCliente, 2, ',', '.'));
        InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, '80AAFFFF', 20, PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $currentRow++;
        $total = array('Total Geral: '.number_format($grandTotal, 2, ',', '.'), '0', '0', '0', '0', '0', '0', '0');
        InsereLinhaPlanilha($currentRow, $startColumn, $total, 'FFAA2040', 32, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->mergeCells(chr($colNum+0).$currentRow.':'.chr($colNum+sizeof($headers)-1).$currentRow);
    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relatório de Mão de Obra');
    ClearBackground('A1:AF1999');

    $activeSheet->setCellValue('B2', 'Relatório de Mão de Obra');
    $styleArray = array( 'font'    => array( 'bold' => true, 'size' => 25) );
    $activeSheet->getStyle('B2:C2')->applyFromArray($styleArray);
    unset($styleArray);

    $calendar = new Calendar();
    $activeSheet->setCellValue('B4', 'Mês: '.$calendar->GetMonthName($month).'   '.'Ano: '.$year);

    BuildReportTable('B', '6');

    $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, "Excel5");
    $objWriter->save('php://output');


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
