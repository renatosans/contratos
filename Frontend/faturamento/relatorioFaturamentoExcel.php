<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/Calendar.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/BillingItemDAO.php");
    include_once("../../DataTransferObjects/BillingItemDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/EquipmentModelDAO.php");
    include_once("../../DataTransferObjects/EquipmentModelDTO.php");
    include_once("../../DataAccessObjects/ManufacturerDAO.php");
    include_once("../../DataTransferObjects/ManufacturerDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");
    include_once("../../DataAccessObjects/ContractDAO.php");
    include_once("../../DataTransferObjects/ContractDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $billingMonth = $_GET['billingMonth'];
    $billingYear = $_GET['billingYear'];
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
    $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
    $billingItemDAO->showErrors = 1;


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


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="faturamento.xls"');
    header("Cache-Control: max-age=0");


    function ClearBackground($cellRange) {
        global $objPhpExcel;

        $activeSheet = $objPhpExcel->getActiveSheet();
        $styleArray = array( 'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_NONE ) ),
                             'fill'    => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb'=>'FFFFFF') ),
                             'font'    => array( 'bold' => true ) );
        $activeSheet->getStyle($cellRange)->applyFromArray($styleArray);
    }

    function GetNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return GetNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    function InsereLinhaPlanilha($numeroLinha, $primeiraColuna, $valores, $corFundo = null, $altura = null, $horizAlign = null)
    {
        global $objPhpExcel;

        $colNum = ord($primeiraColuna) - 65; // primeiraColuna é uma letra no intervalo A-Z
        $activeSheet = $objPhpExcel->getActiveSheet();

        $offset = 0;
        foreach($valores as $valor)
        {
            $cellValue = $valor; if ($valor === '') $cellValue = " - ";
            $activeSheet->setCellValue(GetNameFromNumber($colNum+$offset).$numeroLinha, $cellValue);
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
        $activeSheet->getStyle(GetNameFromNumber($colNum+$first).$numeroLinha.':'.GetNameFromNumber($colNum+$last).$numeroLinha)->applyFromArray($styleArray);
        $activeSheet->getStyle(GetNameFromNumber($colNum+$first).$numeroLinha.':'.GetNameFromNumber($colNum+$last).$numeroLinha)->getAlignment()->setHorizontal($horizAlign);
        $activeSheet->getStyle(GetNameFromNumber($colNum+$first).$numeroLinha.':'.GetNameFromNumber($colNum+$last).$numeroLinha)->getAlignment()->setVertical($vertAlign);
        $activeSheet->getStyle(GetNameFromNumber($colNum+$first).$numeroLinha.':'.GetNameFromNumber($colNum+$last).$numeroLinha)->getAlignment()->setWrapText($wrapText);
        if (isset($altura)) $activeSheet->getRowDimension($numeroLinha)->setRowHeight($altura);
    }

    function BuildReportTable($startColumn, $startRow) {
        global $dataConnector;
        global $objPhpExcel;
        global $billingItemArray;
        global $salesPerson;
        global $model;
        global $searchMethod;


        $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
        $billingItemDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
        $equipmentModelDAO->showErrors = 1;
        $manufacturerDAO = new ManufacturerDAO($dataConnector->sqlserverConnection);
        $manufacturerDAO->showErrors = 1;
        $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
        $counterDAO->showErrors = 1;
        $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
        $salesPersonDAO->showErrors = 1;


        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'ITENS DE FATURAMENTO');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn) - 65; // startColumn é uma letra no intervalo A-Z
        $headers = array('Cliente', 'Descrição Equipamento', 'Modelo', 'Fabricante', 'Série Fabricante', 'Nosso Núm. Série', 'Data Instalação', 'Inicio Atendimento', 'Fim Atendimento', 'Medidor', 'Data Leitura', 'Medição Final', 'Medição Inicial', 'Ajuste Medição(Acrésc/Desc)', 'Consumo', 'Franquia', 'Excedente', 'Tarifa sobre exced.', 'Fixo (R$)', 'Variável (R$)', 'Acrésc/Desc (R$)', 'Total (R$)', 'Parcela Atual', 'Vendedor');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(GetNameFromNumber($colNum+$offset))->setWidth(30);
            $offset++;  
        }
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Busca os fabricantes cadastrados no sistema
        $manufacturerArray = array(0=>"");
        $tempArray = $manufacturerDAO->RetrieveRecordArray();
        foreach ($tempArray as $manufacturer) {
            $manufacturerArray[$manufacturer->FirmCode] = $manufacturer->FirmName;
        }

        // Busca os modelos de equipamento cadastrados no sistema
        $modelArray = array(0=>"");
        $equipmentModelArray = $equipmentModelDAO->RetrieveRecordArray();
        foreach($equipmentModelArray as $modelDTO) {
            $modelArray[$modelDTO->id] = $modelDTO->modelo;
        }

        $associativeList = array(0=>"");
        foreach($equipmentModelArray as $modelDTO) {
            $associativeList[$modelDTO->id] = $manufacturerArray[$modelDTO->fabricante];
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

        // Gera as linhas da tabela
        $grandTotal = 0;
        foreach ($billingItemArray as $billingItem) {
            $equipment = $equipmentDAO->RetrieveRecord($billingItem->codigoCartaoEquipamento);
            $contractCoveragePeriod = ContractDAO::GetContractCoveragePeriod($dataConnector->mysqlConnection, $billingItem->contrato_id);
            $inicioAtendimento = isset($contractCoveragePeriod)? $contractCoveragePeriod["inicioAtendimento"] : '';
            $fimAtendimento = isset($contractCoveragePeriod)? $contractCoveragePeriod["fimAtendimento"] : '';
            $parcelaAtual = isset($contractCoveragePeriod)? $contractCoveragePeriod["parcelaAtual"] : '';
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
            $installationDate = empty($equipment->installationDate) ? '' : $equipment->installationDate->format('d/m/Y');
            $salesPersonCode = $equipment->salesPerson; if (empty($salesPersonCode)) $salesPersonCode = -1;
            $salesPersonName = $salesPersonArray[$salesPersonCode];

            $currentRow++;
            $row = array();
            $row[0] = $equipment->custmrName;
            $row[1] = $equipment->itemName;
            $row[2] = $modelArray[$equipment->model];
            $row[3] = $associativeList[$equipment->model]; // Recupera o fabricante do modelo de equipamento
            $row[4] = $equipment->manufacturerSN;
            $row[5] = $equipment->internalSN;
            $row[6] = $installationDate;
            $row[7] = $inicioAtendimento;
            $row[8] = $fimAtendimento;
            $row[9] = $counterArray[$billingItem->counterId]; 
            $row[10] = $billingItem->dataLeitura;
            $row[11] = $billingItem->medicaoFinal;
            $row[12] = $billingItem->medicaoInicial;
            $row[13] = $billingItem->ajuste;
            $row[14] = $billingItem->consumo;
            $row[15] = $billingItem->franquia; 
            $row[16] = $billingItem->excedente;
            $row[17] = number_format($billingItem->tarifaSobreExcedente, 6, ',', '.'); 
            $row[18] = number_format($billingItem->fixo, 2, ',', '.'); 
            $row[19] = number_format($billingItem->variavel, 2, ',', '.');
            $row[20] = number_format($billingItem->acrescimoDesconto, 2, ',', '.');
            $row[21] = number_format($receitaTotal, 2, ',', '.');
            $row[22] = $parcelaAtual;
            $row[23] = $salesPersonName;
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $grandTotal += $receitaTotal; // Soma a receita total do equipamento ( fixo mais variável )
        }

        $currentRow++;
        $total = array('Total da Receita: '.number_format($grandTotal, 2, ',', '.'), '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
        InsereLinhaPlanilha($currentRow, $startColumn, $total, '80BB80FF', 45, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->mergeCells(GetNameFromNumber($colNum+0).$currentRow.':'.GetNameFromNumber($colNum+sizeof($headers)-1).$currentRow);
    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relatório de Faturamento');
    ClearBackground('A1:AF999');

    $activeSheet->setCellValue('B2', 'Relatório de Faturamento');
    $styleArray = array( 'font' => array( 'bold' => true, 'size' => 25) );
    $activeSheet->getStyle('B2:C2')->applyFromArray($styleArray);
    unset($styleArray);

    $calendar = new Calendar();
    $activeSheet->setCellValue('B4', 'Mês: '.$calendar->GetMonthName($billingMonth).'   '.'Ano: '.$billingYear);

    BuildReportTable('B', '6');

    $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, "Excel5");
    $objWriter->save('php://output');


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
