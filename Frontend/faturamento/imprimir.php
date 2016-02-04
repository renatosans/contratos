<?php

    session_start();

    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../ClassLibrary/BillingEngine.php");
    include_once("../../ClassLibrary/BillingSummary.php");
    include_once("../../DataAccessObjects/BillingDAO.php");
    include_once("../../DataTransferObjects/BillingDTO.php");
    include_once("../../DataAccessObjects/MailingDAO.php");
    include_once("../../DataTransferObjects/MailingDTO.php");
    include_once("../../DataAccessObjects/BillingItemDAO.php");
    include_once("../../DataTransferObjects/BillingItemDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");


    $billingId = $_GET['billingId'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $billingDAO = new BillingDAO($dataConnector->mysqlConnection);
    $billingDAO->showErrors = 1;
    $mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
    $mailingDAO->showErrors = 1;
    $billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
    $billingItemDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;

    // Recupera os dados do faturamento
    $billing = $billingDAO->RetrieveRecord($billingId);
    $mailing = $mailingDAO->RetrieveRecord($billing->mailing_id);
    $billingItemArray = $billingItemDAO->RetrieveRecordArray("codigoFaturamento=".$billing->id);
    $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $mailing->businessPartnerCode);

    // Define o filtro do relatório
    $filter = 'Data Inicial: '.$billing->dataInicial.'&nbsp;&nbsp; Data Final: '.$billing->dataFinal;

    // Busca os contadores cadastrados no sistema
    $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
    $counterDAO->showErrors = 1;
    $counterArray = $counterDAO->RetrieveRecordArray();

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
    <title>Demonstrativo de Faturamento</title>
</head>
<body>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <?php
            // Cria um array de totais para os contadores
            $totalContadores = array();
            foreach ($counterArray as $counter) {
                $totalContadores[$counter->id] = new BillingSummary($counter->id);
            }
            ?>
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >DEMONSTRATIVO DE FATURAMENTO (Nº <?php echo str_pad($billing->id, 5, '0', STR_PAD_LEFT); ?>)</h3><br/>
            <h3 style="border:0; margin:0;" >Cliente: <?php echo $clientName; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            &nbsp;&nbsp;
            <?php echo $filter; ?><br/>
            <div style="clear:both;">&nbsp;</div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Tipo do Contador</td><td>Data de Leitura</td><td>Medição Final</td><td>Medição Inicial</td><td>Consumo</td><td>Franquia</td><td>Excedente (Págs.)</td><td>Tarifa sobre exced.</td><td>Valor Fixo (R$)</td><td>Valor Variável (R$)</td><td>Valor Total (R$)</td></tr>
            <?php
                foreach ($billingItemArray as $billingItem) {
                    $equipment = $equipmentDAO->RetrieveRecord($billingItem->codigoCartaoEquipamento);
                    $equipmentInfo = GetEquipmentInfo($equipment, $billingItem->tipoLocacao);
                    echo '<tr bgcolor="LIGHTGRAY" ><td colspan="11" >'.$equipmentInfo.'</td></tr>';

                    $counterName = CounterDAO::GetCounterName($dataConnector->mysqlConnection, $billingItem->counterId);
                    $dataLeitura = $billingItem->dataLeitura;
                    $medicaoInicial = $billingItem->medicaoInicial;
                    $medicaoFinal = $billingItem->medicaoFinal;
                    if (($billingItem->medicaoInicial == 0) && ($billingItem->medicaoFinal == 0)) {
                        $dataLeitura = "Sem Leitura";
                        $medicaoInicial = "Sem Leitura";
                        $medicaoFinal = "Sem Leitura";
                    }
                    $rowData = '<td>'.$counterName.'</td><td>'.$dataLeitura.'</td><td>'.$medicaoFinal.'</td><td>'.$medicaoInicial.'</td><td>'.$billingItem->consumo.'<br/>(Acrésc/Desc = '.$billingItem->ajuste.')'.'</td><td>'.number_format($billingItem->franquia, 0, '', '').'</td><td>'.$billingItem->excedente.'</td><td>'.formatDecimal($billingItem->tarifaSobreExcedente,null).'</td><td>'.formatBrCurrency($billingItem->fixo,2).'</td><td>'.formatBrCurrency($billingItem->variavel,2).'</td><td>'.formatBrCurrency($billingItem->total,2).'</td>';
                    echo '<tr bgcolor="WHITE" >'.$rowData.'</tr>';

                    $billingSummary = $totalContadores[$billingItem->counterId];
                    $billingSummary->consumo += $billingItem->consumo;
                    $billingSummary->franquia += $billingItem->franquia;
                    $billingSummary->excedente += $billingItem->excedente;
                    $billingSummary->valorFixo += $billingItem->fixo;
                    $billingSummary->valorVariavel += $billingItem->variavel;
                    $billingSummary->valorTotal += $billingItem->total;
                }
            ?>
            </table>
            <div style="clear:both;"><br/></div>

            <h3 style="border:0; margin:0;" >&nbsp;&nbsp;QUADRO RESUMO</h3>
            <table>
            <tr bgcolor="LIGHTGRAY" ><td>Tipo do Contador</td><td>Consumo</td><td>Franquia</td><td>Excedente</td><td>Valor Fixo (R$)</td><td>Valor Variável (R$)</td><td>Valor Total (R$)</td></tr>
            <?php
            $grandTotal = 0;
            foreach ($totalContadores as $billingSummary) {
                if ($billingSummary->valorTotal != 0) {
                    $counterName = CounterDAO::GetCounterName($dataConnector->mysqlConnection, $billingSummary->tipoContador);
                    echo '<tr bgcolor="WHITE" ><td>'.$counterName.'</td><td>'.$billingSummary->consumo.'</td><td>'.number_format($billingSummary->franquia, 0, '', '').'</td><td>'.$billingSummary->excedente.'</td><td>'.formatBrCurrency($billingSummary->valorFixo,2).'</td><td>'.formatBrCurrency($billingSummary->valorVariavel,2).'</td><td>'.formatBrCurrency($billingSummary->valorTotal,2).'</td></tr>';
                    $grandTotal += $billingSummary->valorTotal;
                }
            }
            $ajuste = 0;
            if (is_numeric ($billing->acrescimoDesconto)) {
                $grandTotal = $grandTotal + $billing->acrescimoDesconto;
                $ajuste = $billing->acrescimoDesconto;
            }
            echo '<tr><td colspan=7 ><h3>Total Geral: '.formatBrCurrency($grandTotal,2).'&nbsp;&nbsp; (Acrésc/Desc = '.formatBrCurrency($ajuste,2).')'.'</h3></td></tr>';
            ?>
            </table>
            <h4>&nbsp;&nbsp;Observações: <?php echo $billing->obs; ?></h4>
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
