<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/ContractDAO.php");
    include_once("../../DataTransferObjects/ContractDTO.php");
    include_once("../../DataAccessObjects/SubContractDAO.php");
    include_once("../../DataTransferObjects/SubContractDTO.php");
    include_once("../../DataAccessObjects/ContractItemDAO.php");
    include_once("../../DataTransferObjects/ContractItemDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
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
    $contractType = $_GET['contractType'];
    $contractStatus = $_GET['contractStatus'];
    $searchMethod = $_GET['searchMethod'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    

    // Busca os contratos que se enquadram no filtro aplicado
    $contractArray = array();
    if (($searchMethod == 0) || ($searchMethod == 2)) {
        $filter = "contrato.pn='".$businessPartnerCode."' AND contrato.encerramento >= '".$startDate." 00:00' AND contrato.encerramento <= '".$endDate." 23:59'";
        if ($contractType > 0) $filter = $filter." AND subcontrato.tipoContrato_id=".$contractType;
        if (!empty($contractStatus)) $filter = $filter." AND contrato.status  IN (".$contractStatus.")";
        $joins = "JOIN subContrato ON contrato.id = subContrato.contrato_id JOIN itens ON contrato.id = itens.contrato_id";
        $contractArray = $contractDAO->RetrieveRecordArray2($filter, $joins);
    }
    if ($searchMethod == 1) {
        $equipmentArray = $equipmentDAO->RetrieveRecordArray("ItemName LIKE '%".$model."%'");
        $equipmentEnumeration = "";
        foreach($equipmentArray as $equipment)
        {
            if (!empty($equipmentEnumeration)) $equipmentEnumeration = $equipmentEnumeration.", ";
            $equipmentEnumeration = $equipmentEnumeration.$equipment->insID;
        }
        if (empty($equipmentEnumeration)) $equipmentEnumeration = "0"; // evita o crash da query, quando a lista está vazia

        $filter = "itens.codigoCartaoEquipamento IN (".$equipmentEnumeration.") AND contrato.encerramento >= '".$startDate." 00:00' AND contrato.encerramento <= '".$endDate." 23:59'";
        if ($contractType > 0) $filter = $filter." AND subcontrato.tipoContrato_id=".$contractType;
        if (!empty($contractStatus)) $filter = $filter." AND contrato.status IN (".$contractStatus.")";
        $joins = "JOIN subContrato ON contrato.id = subContrato.contrato_id JOIN itens ON contrato.id = itens.contrato_id";
        $contractArray = $contractDAO->RetrieveRecordArray2($filter, $joins);
    }
    if ($searchMethod == 3) {
        $filter = "itens.codigoCartaoEquipamento=".$equipmentCode." AND contrato.encerramento >= '".$startDate." 00:00' AND contrato.encerramento <= '".$endDate." 23:59'";
        if ($contractType > 0) $filter = $filter." AND subcontrato.tipoContrato_id=".$contractType;
        if (!empty($contractStatus)) $filter = $filter." AND contrato.status  IN (".$contractStatus.")";
        $joins = "JOIN subContrato ON contrato.id = subContrato.contrato_id JOIN itens ON contrato.id = itens.contrato_id";
        $contractArray = $contractDAO->RetrieveRecordArray2($filter, $joins);
    }



    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="contratos.xls"');
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
        global $contractArray;
        global $model;
        global $searchMethod;

        $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
        $contractDAO->showErrors = 1;
        $subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
        $subContractDAO->showErrors = 1;
        $contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
        $contractItemDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
        $equipmentModelDAO->showErrors = 1;
        $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
        $salesPersonDAO->showErrors = 1;


        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'CONTRATOS');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn); 
        $headers = array('Número', 'Cliente', 'Detalhes', 'Assinatura', 'Encerramento', 'Inicio do Atendimento', 'Fim do Atendimento', 'Parcela', 'Vendedor', 'Status', 'Global(S OU N)');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $activeSheet->getColumnDimension(chr($colNum+1))->setWidth(50);
        $activeSheet->getColumnDimension(chr($colNum+2))->setWidth(50);
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        // Busca os modelos de equipamento cadastrados no sistema
        $modelArray = array(0=>"");
        $equipmentModelArray = $equipmentModelDAO->RetrieveRecordArray();
        foreach($equipmentModelArray as $modelDTO) {
            $modelArray[$modelDTO->id] = $modelDTO->modelo;
        }

        // Busca os vendedores cadastrados no sistema
        $retrievedArray = $salesPersonDAO->RetrieveRecordArray();
        $salesPersonArray = array();
        foreach ($retrievedArray as $salesPersonDTO) {
            $salesPersonArray[$salesPersonDTO->slpCode] = $salesPersonDTO->slpName;
        }

        // Gera as linhas da tabela
        $identifierArray = array();
        $LFCR = chr(10).chr(13);
        foreach ($contractArray as $contract) {
            if (array_key_exists($contract->id, $identifierArray)) continue;  // contrato repetido, pula para o próximo registro

            $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $contract->pn);
            $salesPersonName = $salesPersonArray[$contract->vendedor];
            $details = "";
            $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id=".$contract->id);
            foreach ($subContractArray as $subContract) {
                if (!empty($details)) $details = $details.$LFCR;
                $details = $details.$subContract->siglaTipoContrato;

                $itemArray = $contractItemDAO->RetrieveRecordArray("subContrato_id=".$subContract->id);
                foreach ($itemArray as $contractItem) {
                    $equipment = $equipmentDAO->RetrieveRecord($contractItem->codigoCartaoEquipamento);
                    $installationDate = empty($equipment->installationDate) ? '' : $equipment->installationDate->format('d/m/Y');

                    // filtra apenas os items ativos e emprestados
                    if (($equipment->status == 'A') || ($equipment->status == 'L')) {
                        if (!empty($details)) $details = $details.$LFCR;
                        $equipmentModel = ""; if (array_key_exists($equipment->model, $modelArray)) $equipmentModel = $modelArray[$equipment->model];
                        $details = $details.$equipmentModel.' Série '.$equipment->manufacturerSN.' Data Instalação '.$installationDate;
                    }
                }
            }
            if (($searchMethod == 1) || ($searchMethod == 2)) {
                if (!empty($model)) {
                    $modelMatched = false;
                    if (strpos($details, $model)) $modelMatched = true;
                    if (!$modelMatched) continue;
                }
            }

            $currentRow++;
            $row = array();
            $row[0] = str_pad($contract->numero, 5, '0', STR_PAD_LEFT);
            $row[1] = $clientName;
            $row[2] = $details;
            $row[3] = $contract->dataAssinatura;
            $row[4] = $contract->dataEncerramento;
            $row[5] = $contract->inicioAtendimento;
            $row[6] = $contract->fimAtendimento;
            $row[7] = $contract->parcelaAtual.'/'.$contract->quantidadeParcelas;
            $row[8] = $salesPersonName;
            $row[9] = $contractDAO->GetStatusAsText($contract->status);
            $row[10] = ($contract->global == 0)? 'N' : 'S';
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $identifierArray[$contract->id] = $contract->numero;
        }

    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relatório de Contratos');
    ClearBackground('A1:AF999');

    $activeSheet->setCellValue('B2', 'Relatório de Contratos');
    $styleArray = array( 'font'    => array( 'bold' => true, 'size' => 25) );
    $activeSheet->getStyle('B2:C2')->applyFromArray($styleArray);
    unset($styleArray);

    $activeSheet->setCellValue('B4', 'Encerramento de: '.$startDate.'   '.'Encerramento até: '.$endDate);

    BuildReportTable('B', '6');

    $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, "Excel5");
    $objWriter->save('php://output');


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
