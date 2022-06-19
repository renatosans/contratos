<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/UnixTime.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../ClassLibrary/Technician.php");
    include_once("../../ClassLibrary/EquipmentModelStats.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/EquipmentModelDAO.php");
    include_once("../../DataTransferObjects/EquipmentModelDTO.php");
    include_once("../../DataAccessObjects/ManufacturerDAO.php");
    include_once("../../DataTransferObjects/ManufacturerDTO.php");
    include_once("../../DataAccessObjects/ReadingDAO.php");
    include_once("../../DataTransferObjects/ReadingDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");
    include_once("../../DataAccessObjects/EmployeeDAO.php");
    include_once("../../DataTransferObjects/EmployeeDTO.php");

    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $modelName = $_GET['modelName'];
    $equipmentCode = $_GET['equipmentCode'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $technician = $_GET['technician'];
    $status = $_GET['status'];
    $orderBy = $_GET['orderBy'];
    $searchMethod = $_GET['searchMethod'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;


    // Busca os chamados que se enquadram no filtro aplicado
    if ($searchMethod == 0) {
        $filter = "businessPartnerCode='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter = "businessPartnerCode <> ''"; // qualquer cliente
    }
    if ($searchMethod == 1) {
        $filter = "modelo='".$modelName."'";
        if (empty($model)) $filter = "modelo <> ''"; // qualquer modelo
    }
    if ($searchMethod == 2) {
        $filter1 = "businessPartnerCode='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter1 = "businessPartnerCode <> ''"; // qualquer cliente
        $filter2 = "modelo='".$modelName."'";
        if (empty($model)) $filter2 = "modelo <> ''"; // qualquer modelo
        $filter = $filter1." AND ".$filter2;
    }
    if ($searchMethod == 3) {
        $filter1 = "businessPartnerCode='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter1 = "businessPartnerCode <> ''"; // qualquer cliente
        $equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
        $manufSN = $equipment->manufacturerSN;
        $equipmentArray = $equipmentDAO->RetrieveRecordArray("manufSN='".$manufSN."'");
        $equipmentEnumeration = "";
        foreach($equipmentArray as $equipment)
        {
            if (!empty($equipmentEnumeration)) $equipmentEnumeration = $equipmentEnumeration.", ";
            $equipmentEnumeration = $equipmentEnumeration.$equipment->insID;
        }
        if (empty($equipmentEnumeration)) $equipmentEnumeration = "0"; // evita o crash da query, quando a lista está vazia
        $filter2 = "cartaoEquipamento IN (".$equipmentEnumeration.")";
        $filter = $filter1." AND ".$filter2;
    }
    $filter .= " AND dataAbertura >= '".$startDate." 00:00' AND dataAbertura <= '".$endDate." 23:59'";
    if ($technician != 0) $filter = $filter." AND tecnico=".$technician;
    if ($status != 0) $filter = $filter." AND status=".$status;
    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray($filter." ORDER BY ".$orderBy.", fabricante, modelo");



    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="chamados.xls"');
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
        global $serviceCallArray;


        $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
        $serviceCallDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
        $equipmentModelDAO->showErrors = 1;
        $manufacturerDAO = new ManufacturerDAO($dataConnector->mysqlConnection);
        $manufacturerDAO->showErrors = 1;
        $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
        $readingDAO->showErrors = 1;
        $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
        $counterDAO->showErrors = 1;
        $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
        $employeeDAO->showErrors = 1;

        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'CHAMADOS');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn);
        $headers = array('Data Abertura', 'Nº do chamado', 'Status', 'Cliente', 'Depto.', 'Modelo', 'Série', 'Fabricante', 'Defeito', 'Data Atendimento', 'Horário/Duração', 'Sintoma', 'Causa', 'Ação', 'Observação Técnica', 'Contadores', 'Aberto Por', 'Técnico');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $activeSheet->getColumnDimension(chr($colNum+3))->setWidth(50);
        $activeSheet->getColumnDimension(chr($colNum+4))->setWidth(35);
        $activeSheet->getColumnDimension(chr($colNum+8))->setWidth(60);
        $activeSheet->getColumnDimension(chr($colNum+11))->setWidth(60);
        $activeSheet->getColumnDimension(chr($colNum+12))->setWidth(60);
        $activeSheet->getColumnDimension(chr($colNum+13))->setWidth(60);
        $activeSheet->getColumnDimension(chr($colNum+14))->setWidth(35);
        $activeSheet->getColumnDimension(chr($colNum+16))->setWidth(40);
        $activeSheet->getColumnDimension(chr($colNum+17))->setWidth(40);
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Busca os Status de chamado cadastrados no sistema
        $statusArray = ServiceCallDAO::RetrieveServiceCallStatuses($dataConnector->sqlserverConnection);

        // Busca os fabricantes cadastrados no sistema
        $manufacturerArray = array(0=>"");
        $tempArray = $manufacturerDAO->RetrieveRecordArray();
        foreach ($tempArray as $manufacturer) {
            $manufacturerArray[$manufacturer->id] = $manufacturer->nome;
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

        // Busca os funcionários cadastrados no sistema
        $retrievedArray = $employeeDAO->RetrieveRecordArray("empID IS NOT NULL ORDER BY empID");
        $employeeArray = array();
        foreach ($retrievedArray as $employee) {
            $employeeArray[$employee->empID] = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
        }

        // Cria um array para as estatísticas dos técnicos
        $statsArray = array(0=>"");
        foreach ($employeeArray as $id => $name) {
            // considera todos os funcionários como possíveis técnicos, depois filtra somente os que realizaram atendimentos
            $statsArray[$id] = new Technician($id, $name);
        }

        // Gera as linhas da tabela
        $quantChamados = 0;
        foreach ($serviceCallArray as $serviceCall) {
            $equipment = $equipmentDAO->RetrieveRecord($serviceCall->codigoCartaoEquipamento);
            if (!isset($equipment)) $equipment = new EquipmentDTO();  // cria objeto vazio em caso de erro
            $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCall->id);
            $counters = "";
            $LFCR = chr(10).chr(13);
            foreach($readingArray as $reading) {
                if (!empty($counters)) $counters = $counters.$LFCR;
                $counters = $counters.$counterArray[$reading->codigoContador].' '.$reading->contagem;
            }
            $modelName = ""; if (array_key_exists($equipment->model, $modelArray)) $modelName = $modelArray[$equipment->model];
            $manufacturerName = ""; if (array_key_exists($equipment->model, $modelArray)) $manufacturerName = $associativeList[$equipment->model];
            $creator = " - "; if ($serviceCall->abertoPor > 0) $creator = $employeeArray[$serviceCall->abertoPor];
            $technicianName = " - "; if ($serviceCall->tecnico > 0) $technicianName = $employeeArray[$serviceCall->tecnico];

            $technicianStats = $statsArray[$serviceCall->tecnico];
            if (!isset($technicianStats->statistics)) $technicianStats->statistics = array();
            if (!array_key_exists($equipment->model, $technicianStats->statistics))
                $technicianStats->statistics[$equipment->model] = new EquipmentModelStats($equipment->model, $modelName, $manufacturerName);
            $equipmentModelStat = $technicianStats->statistics[$equipment->model];
            $equipmentModelStat->serviceCallCount++;
            $parts = explode(":", $serviceCall->tempoAtendimento, 2);
            $equipmentModelStat->tempoTotalAtendimento += ((int)$parts[0]) +  ((int)$parts[1] / 60);

            $currentRow++;
            $row = array();
            $row[0] = $serviceCall->dataAbertura;
            $row[1] = str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT);
            $row[2] = $statusArray[$serviceCall->status];
            $row[3] = $equipment->custmrName;
            $row[4] = $equipment->instLocation;
            $row[5] = $modelName;
            $row[6] = $equipment->manufacturerSN;
            $row[7] = $manufacturerName;
            $row[8] = $serviceCall->defeito;
            $row[9] = $serviceCall->dataAtendimento;
            $row[10] = $serviceCall->horaAtendimento." (Duração ".$serviceCall->tempoAtendimento.")";
            $row[11] = $serviceCall->sintoma;
            $row[12] = $serviceCall->causa;
            $row[13] = $serviceCall->acao;
            $row[14] = $serviceCall->observacaoTecnica;
            $row[15] = $counters;
            $row[16] = $creator;
            $row[17] = $technicianName;
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $quantChamados++; // Faz a contagem dos chamados técnicos
        }

        $currentRow++;
        $footer = array('Quantidade de Chamados: '.$quantChamados.' (Depende do filtro escolhido)', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
        InsereLinhaPlanilha($currentRow, $startColumn, $footer, '80BB80FF', 32, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->mergeCells(chr($colNum+0).$currentRow.':'.chr($colNum+sizeof($headers)-1).$currentRow);

        $currentRow += 3; // pula 3 linhas
        // Define o titulo do quadro resumo
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$currentRow, 'QUADRO RESUMO');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$currentRow.':'.$startColumn.$currentRow)->applyFromArray($styleArray);

        // Cria o cabeçalho do quadro resumo
        $colNum = ord($startColumn);
        $headers = array('Técnico', 'Modelo', 'Fabricante', 'Quant. Chamados', 'Tempo Médio Atendimento');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $callCount = 0;
        $timeSum = 0;
        foreach ($statsArray as $technicianStats) {
            if (!isset($technicianStats->statistics)) continue;
            $valorPrevio = "";
            $serviceCallCount = 0;
            $tempoTotalAtendimento = 0;
            foreach ($technicianStats->statistics as $equipmentModelStats) {
                if ($equipmentModelStats->serviceCallCount > 0) {
                    $valorAtual = $equipmentModelStats->fabricante;
                    if ($valorPrevio != $valorAtual) {
                        if (!empty($valorPrevio)) {
                            $subTotal = array('', '', '', $callCount, UnixTime::ConvertToTime($timeSum/$callCount));
                            $currentRow++;
                            InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, '80AAFFFF', 20, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $callCount = 0;
                            $timeSum = 0;
                        }
                        $valorPrevio = $valorAtual;
                    }

                    $currentRow++;
                    $row = array();
                    $row[0] = $technicianStats->name;
                    $row[1] = $equipmentModelStats->model;
                    $row[2] = $equipmentModelStats->fabricante;
                    $row[3] = $equipmentModelStats->serviceCallCount;
                    $row[4] = UnixTime::ConvertToTime($equipmentModelStats->tempoTotalAtendimento/$equipmentModelStats->serviceCallCount);
                    InsereLinhaPlanilha($currentRow, $startColumn, $row);
                    $callCount = $callCount + $equipmentModelStats->serviceCallCount;
                    $timeSum = $timeSum + $equipmentModelStats->tempoTotalAtendimento;
                    $serviceCallCount = $serviceCallCount + $equipmentModelStats->serviceCallCount;
                    $tempoTotalAtendimento = $tempoTotalAtendimento + $equipmentModelStats->tempoTotalAtendimento;
                }
            }
            $subTotal = array('', '', '', $callCount, UnixTime::ConvertToTime($timeSum/$callCount));
            $currentRow++;
            InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, '80AAFFFF', 20, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $callCount = 0;
            $timeSum = 0;

            $subTotal = array($technicianStats->name, '', '', $serviceCallCount, UnixTime::ConvertToTime($tempoTotalAtendimento/$serviceCallCount));
            $currentRow++;
            InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, 'FFAA2040', 20, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $activeSheet->mergeCells(chr($colNum+0).$currentRow.':'.chr($colNum+2).$currentRow);
        }
    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relatório de Chamados');
    ClearBackground('A1:AF1999');

    $activeSheet->setCellValue('B2', 'Relatório de Chamados');
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
