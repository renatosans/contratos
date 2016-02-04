<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/Calendar.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../ClassLibrary/BillingEngine.php");
    include_once("../../ClassLibrary/CustomerRevenue.php");
    include_once("../../ClassLibrary/EquipmentRevenue.php");
    include_once("../../ClassLibrary/EquipmentModelStats.php");
    include_once("../../DataAccessObjects/ConfigDAO.php");
    include_once("../../DataTransferObjects/ConfigDTO.php");
    include_once("../../DataAccessObjects/BillingItemDAO.php");
    include_once("../../DataTransferObjects/BillingItemDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/IndustryDAO.php");
    include_once("../../DataTransferObjects/IndustryDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/EquipmentModelDAO.php");
    include_once("../../DataTransferObjects/EquipmentModelDTO.php");
    include_once("../../DataAccessObjects/ManufacturerDAO.php");
    include_once("../../DataTransferObjects/ManufacturerDTO.php");
    include_once("../../DataAccessObjects/InventoryItemDAO.php");
    include_once("../../DataTransferObjects/InventoryItemDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");
    include_once("../../DataAccessObjects/ContractDAO.php");
    include_once("../../DataTransferObjects/ContractDTO.php");


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

    // Traz os faturamentos de acordo com o mês e ano de referência
    $mesFaturamento = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "mesFaturamento");
    $anoFaturamento = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "anoFaturamento");
    $filter = "mesReferencia = '".$mesFaturamento."' AND anoReferencia = '".$anoFaturamento."' AND incluirRelatorio=1";
    $joins = "JOIN faturamento ON item.codigoFaturamento = faturamento.id";
    $billingItemArray = $billingItemDAO->RetrieveRecordArray2($filter, $joins);


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="relacaoCustosReceitas.xls"');
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
            if(strpos($cellValue,'http://') !== false)
                $activeSheet->setCellValue(GetNameFromNumber($colNum+$offset).$numeroLinha, 'Link', true)->getHyperlink()->setUrl($cellValue);
            else
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
        global $root;
        global $dataConnector;
        global $objPhpExcel;
        global $billingItemArray;


        $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;
        $industryDAO = new IndustryDAO($dataConnector->sqlserverConnection);
        $industryDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
        $equipmentModelDAO->showErrors = 1;
        $manufacturerDAO = new ManufacturerDAO($dataConnector->sqlserverConnection);
        $manufacturerDAO->showErrors = 1;
        $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
        $contractDAO->showErrors = 1;
        $inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
        $inventoryItemDAO->showErrors = 1;
        $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
        $counterDAO->showErrors = 1;
        $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
        $salesPersonDAO->showErrors = 1;

        // Busca os segmentos/ramos de atividade cadastrados no sistema
        $industryArray = array(0=>"");
        $tempArray = $industryDAO->RetrieveRecordArray();
        foreach ($tempArray as $industry) {
            $industryArray[$industry->id] = $industry->name;
        }

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

        // Cria um array para as estatísticas dos equipamentos (por modelo)
        $statsArray = array();
        $equipmentModelArray = $equipmentModelDAO->RetrieveRecordArray("id > 0 ORDER BY fabricante, modelo");
        foreach($equipmentModelArray as $modelDTO) {
            $statsArray[$modelDTO->id] = new EquipmentModelStats($modelDTO->id, $modelDTO->modelo, $modelDTO->fabricante);
        }

        // Cria um array para as receitas de cada equipamento
        $equipRevenueArray = array();

        // Cria um array para as receitas provenientes de cada cliente
        $customerRevenueArray = array();

        $associativeList = array(0=>"");
        foreach($equipmentModelArray as $model) {
            $associativeList[$model->id] = $manufacturerArray[$model->fabricante];
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

        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'SÍNTESE POR EQUIPAMENTO');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn) - 65; // startColumn é uma letra no intervalo A-Z
        $headers = array('Cliente', 'Segmento', 'Modelo', 'Fabricante', 'Série Fabricante', 'Nosso Núm. Série', 'Obs. Item', 'Data Instalação', 'Inicio Atendimento', 'Fim Atendimento', 'Parcela Atual', 'Vendedor', 'Medidor', 'Data Leitura', 'Medição Final', 'Medição Inicial', 'Ajuste Medição(Acrésc/Desc)', 'Consumo', 'Franquia', 'Excedente (Págs.)', 'Tarifa sobre exced.', 'Valor Fixo (R$)', 'Valor Variável (R$)', 'Acrésc/Desc (R$)', 'Valor Total (R$)', 'Custo Aquisição', 'Vida Útil', 'Custo Pág. do Equip.', 'Custo Pág. Peças/Mat.', 'Custo Pág. Total', 'Custo/Receita', 'Despesas');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(GetNameFromNumber($colNum+$offset))->setWidth(30);
            $offset++;  
        }
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Gera as linhas da tabela
        $totalFixo = 0;
        $totalVariavel = 0;
        $totalAcrescDesc = 0;
        $grandTotal = 0;
        foreach ($billingItemArray as $billingItem) {
            $equipment = $equipmentDAO->RetrieveRecord($billingItem->codigoCartaoEquipamento);
            $businessPartner = $businessPartnerDAO->RetrieveRecord($equipment->customer);
            $contractCoveragePeriod = ContractDAO::GetContractCoveragePeriod($dataConnector->mysqlConnection, $billingItem->contrato_id);
            $installationDate = empty($equipment->installationDate) ? '' : $equipment->installationDate->format('d/m/Y');
            $inicioAtendimento = isset($contractCoveragePeriod)? $contractCoveragePeriod["inicioAtendimento"] : '';
            $fimAtendimento = isset($contractCoveragePeriod)? $contractCoveragePeriod["fimAtendimento"] : '';
            $parcelaAtual = isset($contractCoveragePeriod)? $contractCoveragePeriod["parcelaAtual"] : '';
            $receitaTotal = $billingItem->total + $billingItem->acrescimoDesconto;
            $inventoryItem = $inventoryItemDAO->RetrieveRecord($equipment->itemCode);
            $obsItem = $inventoryItem->userText;
            $custoAquisicao = $inventoryItem->avgPrice;
            $custoPagPecas = $inventoryItem->expenses;
            $vidaUtil = $inventoryItem->durability;
            if (empty($vidaUtil))
            {
                $custoPagEquip = 0;
                $custoTotal = 0;
                $custoSobreReceita = 0;
            }
            else
            {
                $custoPagEquip = ($custoAquisicao / $vidaUtil) * 1.4;
                $custoTotal = ($custoPagEquip + $custoPagPecas) * $billingItem->consumo;
                $custoSobreReceita = 0; if ($receitaTotal != 0) $custoSobreReceita = $custoTotal/$receitaTotal;
            }
            // Busca os dados do vendedor
            $salesPersonCode = $equipment->salesPerson; if (empty($salesPersonCode)) $salesPersonCode = -1;
            $salesPersonName = $salesPersonArray[$salesPersonCode];
            // Busca os dados do segmento/ramo de atividade
            $industryCode = $businessPartner->industry; if (empty($industryCode)) $industryCode = 0;
            $industryName = $industryArray[$industryCode];
            // Busca o modelo e fabricante do equipamento
            $modelName = ""; if (array_key_exists($equipment->model, $modelArray)) $modelName = $modelArray[$equipment->model];
            $manufacturerName = ""; if (array_key_exists($equipment->model, $modelArray)) $manufacturerName = $associativeList[$equipment->model];

            if (!array_key_exists($equipment->manufacturerSN, $equipRevenueArray))
                $equipRevenueArray[$equipment->manufacturerSN] = new EquipmentRevenue($equipment->manufacturerSN, $equipment->model, $modelName, $manufacturerName, 0);
            $equipRevenue = $equipRevenueArray[$equipment->manufacturerSN];
            $equipRevenue->revenue += $receitaTotal;

            if (!array_key_exists($equipment->customer, $customerRevenueArray))
                $customerRevenueArray[$equipment->customer] = new CustomerRevenue($equipment->customer, 0);
            $customerRevenue = $customerRevenueArray[$equipment->customer];
            $customerRevenue->revenue += $receitaTotal;

            $row = array();
            $row[0] = $businessPartner->cardName.' ('.$businessPartner->cardCode.')';
            $row[1] = $industryName;
            $row[2] = $modelName;
            $row[3] = $manufacturerName;
            $row[4] = $equipment->manufacturerSN;
            $row[5] = $equipment->internalSN;
            $row[6] = $obsItem;
            $row[7] = $installationDate;
            $row[8] = $inicioAtendimento;
            $row[9] = $fimAtendimento;
            $row[10] = $parcelaAtual;
            $row[11] = $salesPersonName;
            $row[12] = $counterArray[$billingItem->counterId]; // Recupera o nome do medidor/contador
            $row[13] = $billingItem->dataLeitura;
            $row[14] = $billingItem->medicaoFinal;
            $row[15] = $billingItem->medicaoInicial;
            $row[16] = $billingItem->ajuste;
            $row[17] = $billingItem->consumo;
            $row[18] = $billingItem->franquia;
            $row[19] = $billingItem->excedente;
            $row[20] = formatDecimal($billingItem->tarifaSobreExcedente,null);
            $row[21] = formatBrCurrency($billingItem->fixo,2);
            $row[22] = formatBrCurrency($billingItem->variavel,2);
            $row[23] = formatBrCurrency($billingItem->acrescimoDesconto,2);
            $row[24] = formatBrCurrency($receitaTotal,2);
            $row[25] = formatBrCurrency($custoAquisicao,2);
            $row[26] = $vidaUtil;
            $row[27] = formatDecimal($custoPagEquip, 6);
            $row[28] = formatDecimal($custoPagPecas, 6);
            $row[29] = formatBrCurrency($custoTotal,2);
            $row[30] = formatDecimal($custoSobreReceita, 6);
            $row[31] = 'http://datadb/contratos/AjaxCalls/GetEquipmentExpenses.php?equipmentCode='.$equipment->insID.'&billingId='.$billingItem->codigoFaturamento.'&showDetails=true';

            $currentRow++;
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
            $totalFixo += $billingItem->fixo; // Soma os valores fixos dos equipamentos
            $totalVariavel += $billingItem->variavel; // Soma os valores variáveis dos equipamentos
            $totalAcrescDesc += $billingItem->acrescimoDesconto; // Soma os acrescimos descontos dos equipamentos
            $grandTotal += $receitaTotal; // Soma a receita total dos equipamentos ( fixo mais variável )
        }

        $currentRow++;
        $summary = array('Quant. Clientes: '.sizeof($customerRevenueArray), '', '', '', 'Quant. Equipamentos: '.sizeof($equipRevenueArray), '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Total Fixo: '.number_format($totalFixo, 2, ',', '.'), 'Total Variável: '.number_format($totalVariavel, 2, ',', '.'), 'Total Acrésc/Desc: '.number_format($totalAcrescDesc, 2, ',', '.'), 'Total Receita: '.number_format($grandTotal, 2, ',', '.'), '', '', '', '', '', '', '');
        InsereLinhaPlanilha($currentRow, $startColumn, $summary, '80BB80FF', 32, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        foreach($equipRevenueArray as $equipRevenue) {
            if (array_key_exists($equipRevenue->modelId, $statsArray)) {
                $modelStats = $statsArray[$equipRevenue->modelId];

                $modelStats->revenue += $equipRevenue->revenue;
                $modelStats->equipmentCount += 1;
            }
        }

        $currentRow += 3; // pula 3 linhas
        $startColumn = 'G'; // fixa o quadro resumo na setima coluna da planilha

        // Define o titulo do quadro resumo
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$currentRow, 'QUADRO RESUMO');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$currentRow.':'.$startColumn.$currentRow)->applyFromArray($styleArray);

        // Cria o cabeçalho do quadro resumo
        $colNum = ord($startColumn);
        $headers = array('Modelo', 'Fabricante', 'Quantidade', 'Receita');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $valorPrevio = "";
        $equipCount = 0;
        $somaReceita = 0;
        foreach ($statsArray as $equipmentModelStats) {
            if ($equipmentModelStats->equipmentCount > 0) {
                $manufacturerName = $manufacturerArray[$equipmentModelStats->fabricante];
                $valorAtual = $manufacturerName;
                if ($valorPrevio != $valorAtual) {
                    if (!empty($valorPrevio)) {
                        $subTotal = array('', '', $equipCount, number_format($somaReceita, 2, ',', '.'));
                        $currentRow++;
                        InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, '80AAFFFF', 20, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $equipCount = 0;
                        $somaReceita = 0;
                    }
                    $valorPrevio = $valorAtual;
                }

                $currentRow++;
                $row = array();
                $row[0] = $equipmentModelStats->model;
                $row[1] = $manufacturerName;
                $row[2] = $equipmentModelStats->equipmentCount;
                $row[3] = number_format($equipmentModelStats->revenue, 2, ',', '.');
                InsereLinhaPlanilha($currentRow, $startColumn, $row);
                $equipCount = $equipCount + $equipmentModelStats->equipmentCount;
                $somaReceita = $somaReceita + $equipmentModelStats->revenue;
            }
        }
        $subTotal = array('', '', $equipCount, number_format($somaReceita, 2, ',', '.'));
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $subTotal, '80AAFFFF', 20, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }


    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Relação entre custos e receitas');
    ClearBackground('A1:AK1999');

    $activeSheet->setCellValue('B2', 'Relação entre custos e receitas');
    $styleArray = array( 'font'    => array( 'bold' => true, 'size' => 25) );
    $activeSheet->getStyle('B2:C2')->applyFromArray($styleArray);
    unset($styleArray);

    $calendar = new Calendar();
    $activeSheet->setCellValue('B4', 'Mês: '.$calendar->GetMonthName($mesFaturamento).'   '.'Ano: '.$anoFaturamento);

    BuildReportTable('B', '6');

    $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, "Excel5");
    $objWriter->save('php://output');


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
