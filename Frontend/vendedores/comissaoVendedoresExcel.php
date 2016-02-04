<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/PHPExcel.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../ClassLibrary/SalesPerson.php");
    include_once("../../ClassLibrary/SalesPersonStats.php");
    include_once("../../ClassLibrary/CommissionRule.php");
    include_once("../../DataAccessObjects/InvoicePaymentDAO.php");
    include_once("../../DataTransferObjects/InvoicePaymentDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/IndustryDAO.php");
    include_once("../../DataTransferObjects/IndustryDTO.php");
    include_once("../../DataAccessObjects/ContractDAO.php");
    include_once("../../DataTransferObjects/ContractDTO.php");
    include_once("../../DataAccessObjects/BillingDAO.php");
    include_once("../../DataTransferObjects/BillingDTO.php");
    include_once("../../DataAccessObjects/BillingItemDAO.php");
    include_once("../../DataTransferObjects/BillingItemDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");
    include_once("../../DataAccessObjects/CommissionPerSignatureDAO.php");
    include_once("../../DataTransferObjects/CommissionPerSignatureDTO.php");


    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $invoicePaymentDAO = new InvoicePaymentDAO($dataConnector->sqlserverConnection);
    $invoicePaymentDAO->showErrors = 1;

    // Busca os pagamentos realizados no período
    $filter = "ORCT.JrnlMemo != 'Cancelado' AND OINV.DocTotal != '0' AND OBOE.BoeStatus = 'P' AND OBOE.U_PaymentDate >= '".str_replace('-', '', $startDate)."' AND OBOE.U_PaymentDate <= '".str_replace('-', '', $endDate)."'";
    $boePayments = $invoicePaymentDAO->RetrievePaymentsByBillOfExchange($filter);
    $filter = "ORCT.JrnlMemo != 'Cancelado' AND OINV.DocTotal != '0' AND (ORCT.CashSum != '0' OR ORCT.CheckSum != '0' OR ORCT.TrsfrSum != '0') AND ORCT.DocDueDate >= '".str_replace('-', '', $startDate)."' AND ORCT.DocDueDate <= '".str_replace('-', '', $endDate)."'";
    $otherPayments = $invoicePaymentDAO->RetrieveOther($filter);
    $invoicePaymentArray = array_merge($boePayments, $otherPayments);


    function GetBOENumber($payment) {
        if (!empty($payment->numeroBoleto)) return $payment->numeroBoleto;
        if ($payment->valorDinheiro > 0) return 'Dinheiro';
        if ($payment->valorCheque > 0) return 'Cheque';
        if ($payment->valorDeposito > 0) return 'Depósito';
        return ' - ';
    }

    function GetDocTotal($payment) {
        if (!empty($payment->numeroBoleto)) return $payment->valorBoleto;
        return $payment->valorNotaFiscal;
    }

    function GetReceivedAmount($payment) {
        if (!empty($payment->numeroBoleto)) return $payment->quantiaRecebida;
        if ($payment->valorDinheiro > 0) return $payment->valorDinheiro;
        if ($payment->valorCheque > 0) return $payment->valorCheque;
        if ($payment->valorDeposito > 0) return $payment->valorDeposito;
        return ' - ';
    }

    function GetInvoiceTypeAsText($invoiceType) {
        switch ($invoiceType)
        {
            case 18: return 'Locação';
            case 17: return 'Assist. Técnica';
            case 10: return 'Venda Ativo';
            case 20: return 'Venda Serviço';
            default: return 'Outros';
        }
    }

    function IsContractAdded($contractDTO, $comissionRuleStats) {
        if (!isset($comissionRuleStats->contractArray)) return false; // o array não existe, falta criar o array e adicionar
        if (array_key_exists($contractDTO->id, $comissionRuleStats->contractArray)) return true;
        return false;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="comissao.xls"');
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
        global $invoicePaymentArray;

        $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
        $salesPersonDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;
        $industryDAO = new IndustryDAO($dataConnector->sqlserverConnection);
        $industryDAO->showErrors = 1;
        $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
        $contractDAO->showErrors = 1;
        $billingDAO = new BillingDAO($dataConnector->mysqlConnection);
        $billingDAO->showErrors = 1;
        $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
        $billingItemDAO->showErrors = 1;
        $commissionPerSignatureDAO = new CommissionPerSignatureDAO($dataConnector->mysqlConnection);
        $commissionPerSignatureDAO->showErrors = 1;

        // Define o titulo da tabela
        $currentRow = $startRow;
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$startRow, 'PAGAMENTOS');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$startRow.':'.$startColumn.$startRow)->applyFromArray($styleArray);

        // Cria o cabeçalho da tabela
        $colNum = ord($startColumn);
        $headers = array('Nº NF', 'Tipo', 'Cliente', 'Segmento', 'Nº Boleto', 'Valor Cobrado', 'Valor Recebido', 'Data Pagamento', 'Demonstrativo Fat.', 'Descritivo Faturamento');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;  
        }
        $activeSheet->getColumnDimension(chr($colNum+$offset-1))->setWidth(50);
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        // Cria um array para as estatísticas dos vendedores
        $statsArray = array();
        $salesPersonArray = $salesPersonDAO->RetrieveRecordArray();
        foreach ($salesPersonArray as $salesPerson) {
            $statsArray[$salesPerson->slpCode] = new SalesPerson($salesPerson->slpCode, $salesPerson->slpName);
        }

        // Cria um array para as regras de comissão
        $rulesArray = array(0 => new CommissionRule(0, "", "", 0));
        $commissionRuleArray = $commissionPerSignatureDAO->RetrieveRecordArray();
        foreach ($commissionRuleArray as $commissionRuleDTO) {
            $rulesArray[$commissionRuleDTO->id] = new CommissionRule($commissionRuleDTO->segmento, $commissionRuleDTO->dataAssinaturaDe, $commissionRuleDTO->dataAssinaturaAte, $commissionRuleDTO->comissao);
        }

        // Busca os segmentos/ramos de atividade cadastrados no sistema
        $industryArray = array(-1=>"- Nenhum Segmento -");
        $tempArray = $industryDAO->RetrieveRecordArray();
        foreach ($tempArray as $industry) {
            $industryArray[$industry->id] = $industry->name;
        }

        foreach ($invoicePaymentArray as $payment) {
            $billingDescription = "";
            $billing = new BillingDTO();
            if ($payment->demFaturamento >= 1) {
                $billing = $billingDAO->RetrieveRecord($payment->demFaturamento);
                if (!isset($billing)) $billing = new BillingDTO();
            }
            $industry = -1;

            // Busca os itens de faturamento
            $billingItemArray = $billingItemDAO->RetrieveRecordArray("codigoFaturamento = '".$billing->id."'");
            $quantidadeItens = sizeof($billingItemArray); if ($quantidadeItens < 1) $quantidadeItens = 1;
            $multaRecisoria = $billing->multaRecisoria / $quantidadeItens; 
            foreach ($billingItemArray as $billingItem) {
                $equipment = $equipmentDAO->RetrieveRecord($billingItem->codigoCartaoEquipamento);
                $businessPartner = $businessPartnerDAO->RetrieveRecord($payment->cardCode);
                if (isset($businessPartner->industry)) $industry = $businessPartner->industry;
                if ($equipment->salesPerson > 0) {
                    $contract = $contractDAO->RetrieveRecord($billingItem->contrato_id);

                    $salesPersonStats = $statsArray[$equipment->salesPerson];
                    if (!isset($salesPersonStats->statistics)) $salesPersonStats->statistics = array();

                    $ruleKey  = 0;
                    $comissao = 0;
                    $commissionRuleArray = $commissionPerSignatureDAO->RetrieveRecordArray("(segmento = 0 OR segmento = ".$industry.") AND dataAssinaturaDe <= '".$contract->dataAssinatura."' AND dataAssinaturaAte >= '".$contract->dataAssinatura."' ORDER BY segmento DESC, comissao DESC");
                    if (sizeof($commissionRuleArray) > 0) {
                        $rule = $commissionRuleArray[0];
                        $ruleKey  = $rule->id;
                        $comissao = $rule->comissao;
                    }
                    if (!array_key_exists($ruleKey, $salesPersonStats->statistics))
                        $salesPersonStats->statistics[$ruleKey] = new SalesPersonStats($ruleKey);
                    $ruleStats = $salesPersonStats->statistics[$ruleKey];
                    $ruleStats->comissionRate = $comissao;
                    $ruleStats->revenue += ($billingItem->total + $billingItem->acrescimoDesconto - $multaRecisoria);
                    if (!IsContractAdded($contract, $ruleStats)) {
                        $ruleStats->contractCount++;
                        if (!isset($ruleStats->contractArray)) $ruleStats->contractArray = array();
                        $ruleStats->contractArray[$contract->id] = $contract->id;
                    }
                }
                $LFCR = chr(10).chr(13);
                $salesPersonName = SalesPersonDAO::GetSalesPersonName($dataConnector->sqlserverConnection, $equipment->salesPerson);
                $billingDescription .= $salesPersonName.' Serial '.$equipment->manufacturerSN.' R$ '.$billingItem->total.$LFCR;
            }

            $currentRow++;
            $row = array();
            $row[0] = $payment->serial;
            $row[1] = GetInvoiceTypeAsText($payment->tipo);
            $row[2] = $payment->cardName;
            $row[3] = $industryArray[$industry];
            $row[4] = GetBOENumber($payment);
            $row[5] = GetDocTotal($payment);
            $row[6] = GetReceivedAmount($payment);
            $row[7] = empty($payment->date) ? '' : $payment->date->format('d/m/Y');
            $row[8] = empty($billing->id) ? ' - ' : str_pad($billing->id, 5, '0', STR_PAD_LEFT);
            $row[9] = $billingDescription;
            InsereLinhaPlanilha($currentRow, $startColumn, $row);
        }

        $currentRow += 3; // pula 3 linhas
        // Define o titulo do quadro resumo
        $activeSheet = $objPhpExcel->getActiveSheet();
        $activeSheet->setCellValue($startColumn.$currentRow, 'COMISSÕES');
        $styleArray = array( 'font' => array( 'bold' => true, 'size' => 16) );
        $activeSheet->getStyle($startColumn.$currentRow.':'.$startColumn.$currentRow)->applyFromArray($styleArray);

        // Cria o cabeçalho do quadro resumo
        $colNum = ord($startColumn);
        $headers = array('Vendedor', 'Segmento', 'Data de Assinatura', 'Quantidade de Contratos', 'Faturamento (R$)', 'Comissão (%)', 'Comissão (R$)');
        $offset = 0;
        foreach($headers as $header)
        {
            $activeSheet->getColumnDimension(chr($colNum+$offset))->setWidth(25);
            $offset++;
        }
        $currentRow++;
        InsereLinhaPlanilha($currentRow, $startColumn, $headers, PHPExcel_Style_Color::COLOR_YELLOW, 30, PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        foreach ($statsArray as $salesPersonStats) {
            if (isset($salesPersonStats->statistics)) {
                $statistics = $salesPersonStats->statistics;
                foreach ($statistics as $stat) {
                    $commissionRule = $rulesArray[$stat->index];
                    $industryName = "Todos"; if ($commissionRule->segmento > 0) $industryName = $industryArray[$commissionRule->segmento];
                    $period = ""; if ($commissionRule->comissao > 0) $period = $commissionRule->dataAssinaturaDe.' até '.$commissionRule->dataAssinaturaAte; 
                    $comissionPercentage = $stat->comissionRate;
                    $comissionValue = ($stat->comissionRate/100) * $stat->revenue;

                    $currentRow++;
                    $row = array();
                    $row[0] = $salesPersonStats->name;
                    $row[1] = $industryName;
                    $row[2] = $period;
                    $row[3] = $stat->contractCount;
                    $row[4] = number_format($stat->revenue, 2, ',', '.');
                    $row[5] = $comissionPercentage;
                    $row[6] = number_format($comissionValue, 2, ',', '.');
                    InsereLinhaPlanilha($currentRow, $startColumn, $row);
                }
            }
        }
    }

    $objPhpExcel = new PHPExcel();
    $activeSheet = $objPhpExcel->getActiveSheet();
    $activeSheet->setTitle('Comissão dos Vendedores');
    ClearBackground('A1:AF999');

    $activeSheet->setCellValue('B2', 'Comissão dos Vendedores');
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
