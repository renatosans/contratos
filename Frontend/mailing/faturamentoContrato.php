<?php
    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../ClassLibrary/BillingEngine.php");
    include_once("../../ClassLibrary/BillingSummary.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/ContractDAO.php");
    include_once("../../DataTransferObjects/ContractDTO.php");
    include_once("../../DataAccessObjects/SubContractDAO.php");
    include_once("../../DataTransferObjects/SubContractDTO.php");
    include_once("../../DataAccessObjects/ContractItemDAO.php");
    include_once("../../DataTransferObjects/ContractItemDTO.php");
    include_once("../../DataAccessObjects/InventoryItemDAO.php");
    include_once("../../DataTransferObjects/InventoryItemDTO.php");


    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $acrescimo = $_GET['acrescimo'];
    $obs = $_GET['obs'];
    $contractId = $_GET['contractId'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];
    $valuesOnly = null; if (isset($_GET['valuesOnly'])) $valuesOnly = $_GET['valuesOnly'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;


    // Busca os dados do contrato
    $contract = $contractDAO->RetrieveRecord($contractId);

    // Define o filtro do relatório
    $filter = 'Sem filtro de datas (recuperar duas últimas leituras)';
    if (($startDate != null) && ($endDate != null)) $filter = 'Data Inicial: '.$startDate.'&nbsp;&nbsp; Data Final: '.$endDate;

    // Busca os subcontratos que pertencem ao contrato (enumeração)
    $subContractEnumeration = SubContractDAO::GetSubcontractsByOwner($dataConnector->mysqlConnection, $contract->id);

    // Busca os items pertencentes ao contrato
    $itemArray = array();
    $statusEquipamentos = array();
    $itemsByOwner = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $subContractEnumeration);
    foreach ($itemsByOwner as $itemDTO) {
        $equipment  = $equipmentDAO->RetrieveRecord($itemDTO->codigoCartaoEquipamento);
        $statusEquipamentos[$equipment->insID] = $equipment->status;

        // filtra apenas os items ativos e emprestados
        if (($equipment->status == 'A') || ($equipment->status == 'L')) array_push($itemArray, $itemDTO);
    }

    // Busca os dados do cliente
    $cardCode = $contract->pn; // no contrato normalmente
    if (!empty($subContractId) && (sizeof($itemArray) > 0)) {
        foreach ($itemArray as $contractItem) {
            if ($contractItem->codigoSubContrato == $subContractId)
                $cardCode = $contractItem->businessPartnerCode; // no item de contrato em casos específicos
        }
    }
    $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $cardCode);

    // Cria o objeto auxiliar para calculos de faturamento
    $calculoFaturamento = new BillingEngine($dataConnector, $subContractEnumeration, $itemArray, $startDate, $endDate);
    $calculoFaturamento->statusEquipamentos = $statusEquipamentos;

    function BuildRows($valuesOnly) {
        global $dataConnector;
        global $itemArray;
        global $calculoFaturamento;
        global $totalContadores;


        // Cria os objetos de mapeamento objeto-relacional
        $subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
        $subContractDAO->showErrors = 1;
        $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;
        $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
        $inventoryItemDAO->showErrors = 1;

        // Para cada equipamento monta os dados de faturamento
        foreach ($itemArray as $contractItem) {
            $equipmentId = $contractItem->codigoCartaoEquipamento;
            $subContract = $subContractDAO->RetrieveRecord($contractItem->codigoSubContrato);
            $contractType = $subContract->codigoTipoContrato;
            $contractDTO = $calculoFaturamento->GetContract($dataConnector->mysqlConnection, $subContract->id);
            $divisor = $calculoFaturamento->GetDivisor($dataConnector->mysqlConnection, $contractDTO, $subContract); // divisor para calculo da média (no caso de contrato global)

            // Caso o equipamento não pertença ao subcontrato escolhido no filtro não fatura, passa para o proximo equipamento
            $subContractId = $_GET['subContractId'];
            if (!empty($subContractId)) {
                if ($subContract->id != $subContractId) continue;
            }

            $equipmentDTO = $equipmentDAO->RetrieveRecord($equipmentId);
            $inventoryItem = $inventoryItemDAO->RetrieveRecord($equipmentDTO->itemCode);
            $contractTypeInfo = $calculoFaturamento->GetTipoContratoAsText($contractType);
            $equipmentInfo = GetEquipmentInfo($equipmentDTO, $contractTypeInfo);
            echo '<tr bgcolor="LIGHTGRAY" ><td colspan="11" >'.$equipmentInfo.'</td></tr>';

            $counterIdArray = array_keys($calculoFaturamento->GetContadores());
            foreach ($counterIdArray as $counterId) {
                $billingSummary = $totalContadores[$counterId];
                $formaCobranca = $calculoFaturamento->GetFormaCobranca($subContract->id, $counterId);
                $consumo = $calculoFaturamento->GetConsumo($equipmentId, $counterId);
                $consumoGlobal = 0;
                $excedenteGlobal = 0;
                if ($contractDTO->global) {
                    $consumoGlobal = $calculoFaturamento->GetConsumoGlobal($dataConnector->mysqlConnection, $contractDTO->id, $counterId);
                    $franquiaGlobal = $calculoFaturamento->GetFranquiaGlobal($dataConnector->mysqlConnection, $counterId);
                    $excedenteGlobal = ($consumoGlobal > $franquiaGlobal)? $consumoGlobal - $franquiaGlobal : 0;
                    if ($franquiaGlobal == 0) $excedenteGlobal = 0;
                }

                $modalidadeMedicao = 0; $fixo = 0.0; $variavelDefault = 0.0; $franquia = 0; $individualPorCapacidade = 0;
                if ($formaCobranca != null) {
                    $modalidadeMedicao = $formaCobranca->modalidadeMedicao;
                    $fixo = $formaCobranca->fixo / $divisor;
                    $variavelDefault = $formaCobranca->variavel;
                    $franquia = $formaCobranca->franquia / $divisor;
                    $individualPorCapacidade = $formaCobranca->individual;
                    if ($individualPorCapacidade) $franquia = $equipmentDTO->capacity;
                }

                if ($modalidadeMedicao == 1) { // Sem leituras
                    $rowData = '<td>'.$calculoFaturamento->GetContadorAsText($counterId).'</td><td>Sem leitura</td><td>Sem leitura</td><td>Sem leitura</td><td>0</td><td>0</td><td>0</td><td>0</td><td>'.formatBrCurrency($fixo,2).'</td><td>0</td><td>'.formatBrCurrency($fixo,2).'</td>';
                    if ($valuesOnly) $rowData = '<td>'.$equipmentId.'</td><td>'.$contractTypeInfo.'</td><td>'.$counterId.'</td><td>Sem leitura</td><td>Sem leitura</td><td>Sem leitura</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td><td>'.$fixo.'</td><td>0</td><td>'.$fixo.'</td>';
                    echo '<tr bgcolor="WHITE" >'.$rowData.'</tr>';
                    $billingSummary->valorFixo += $fixo;
                    $billingSummary->excedente += 0;
                    $billingSummary->valorTotal += $fixo;
                }

                if (($consumo != null) && ($modalidadeMedicao != 1)) {
                    $dataLeitura = $consumo->dataLeitura;
                    $medicaoInicial = $consumo->medicaoInicial;
                    $medicaoFinal = $consumo->medicaoFinal;
                    $consumoMedido = $consumo->total;
                    $ajuste = $consumo->ajusteTotal;

                    $excedente = ($consumoMedido > $franquia)? $consumoMedido - $franquia : 0;
                    if ($contractDTO->global && !$individualPorCapacidade) $excedente = $excedenteGlobal / sizeof($itemArray);
                    if ($franquia == 0) $excedente = 0;
                    $tarifaExcedente = $calculoFaturamento->GetCustoVariavel($subContract->id, $counterId, $consumoMedido, $variavelDefault);
                    if ($contractDTO->global && !$individualPorCapacidade) $tarifaExcedente = $calculoFaturamento->GetCustoVariavel($subContract->id, $counterId, $consumoGlobal, $variavelDefault);

                    $valorFixo = $fixo;
                    $valorVariavel = ($franquia != 0)? $excedente * $tarifaExcedente : $consumoMedido * $tarifaExcedente;
                    $valorTotal = $valorFixo + $valorVariavel;

                    $rowData = '<td>'.$calculoFaturamento->GetContadorAsText($counterId).'</td><td>'.date("d/m/Y", $dataLeitura).'</td><td>'.$medicaoFinal.'</td><td>'.$medicaoInicial.'</td><td>'.$consumoMedido.'<br/>(Acrésc/Desc = '.$ajuste.')'.'</td><td>'.formatDecimal($franquia,2).'</td><td>'.round($excedente).'</td><td>'.formatDecimal($tarifaExcedente,null).'</td><td>'.formatBrCurrency($valorFixo,2).'</td><td>'.formatBrCurrency($valorVariavel,2).'</td><td>'.formatBrCurrency($valorTotal,2).'</td>';
                    if ($valuesOnly) $rowData = '<td>'.$equipmentId.'</td><td>'.$contractTypeInfo.'</td><td>'.$counterId.'</td><td>'.date("Y-m-d", $dataLeitura).'</td><td>'.$medicaoFinal.'</td><td>'.$medicaoInicial.'</td><td>'.$consumoMedido.'</td><td>'.$ajuste.'</td><td>'.$franquia.'</td><td>'.$excedente.'</td><td>'.$tarifaExcedente.'</td><td>'.$valorFixo.'</td><td>'.$valorVariavel.'</td><td>'.$valorTotal.'</td>';
                    echo '<tr bgcolor="WHITE" >'.$rowData.'</tr>';
                    $billingSummary->consumo += $consumoMedido;
                    $billingSummary->franquia += $franquia;
                    $billingSummary->excedente += $excedente;
                    $billingSummary->valorFixo += $valorFixo;
                    $billingSummary->valorVariavel += $valorVariavel;
                    $billingSummary->valorTotal += $valorTotal;
                }
            } // foreach de tipos de contador
        } // foreach de itens de faturamento (equipamentos)
    }

    if ($valuesOnly) {
        BuildRows(true); // omite os detalhes do html (formatação, divs, etc)

        // Fecha a conexão com o banco de dados
        $dataConnector->CloseConnection();
        exit;
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
    <title>Demonstrativo de Faturamento</title>
</head>
<body>
    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <?php
            // Cria um array de totais para os contadores
            $totalContadores = array();
            $counterIdArray = array_keys($calculoFaturamento->GetContadores());
            foreach ($counterIdArray as $counterId) {
                $totalContadores[$counterId] = new BillingSummary($counterId);
            }
            ?>
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >DEMONSTRATIVO DE FATURAMENTO</h3><br/>
            <h3 style="border:0; margin:0;" >Número do Contrato: <?php echo $contract->numero; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            &nbsp;&nbsp;
            <?php echo $filter; ?><br/>
            &nbsp;&nbsp;
            Cliente: <?php echo $clientName; ?><br/>
            <div style="clear:both;">&nbsp;</div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Tipo do Contador</td><td>Data de Leitura</td><td>Medição Final</td><td>Medição Inicial</td><td>Consumo</td><td>Franquia</td><td>Excedente (Págs.)</td><td>Tarifa sobre exced.</td><td>Valor Fixo (R$)</td><td>Valor Variável (R$)</td><td>Valor Total (R$)</td></tr>
            <?php BuildRows(false); ?>
            </table>
            <div style="clear:both;"><br/></div>

            <h3 style="border:0; margin:0;" >&nbsp;&nbsp;QUADRO RESUMO</h3>
            <table>
            <tr bgcolor="LIGHTGRAY" ><td>Tipo do Contador</td><td>Consumo</td><td>Franquia</td><td>Excedente</td><td>Valor Fixo (R$)</td><td>Valor Variável (R$)</td><td>Valor Total (R$)</td></tr>
            <?php
            $grandTotal = 0;
            foreach ($totalContadores as $billingSummary) {
                if ($billingSummary->valorTotal != 0) {
                    echo '<tr bgcolor="WHITE" ><td>'.$calculoFaturamento->GetContadorAsText($billingSummary->tipoContador).'</td><td>'.$billingSummary->consumo.'</td><td>'.$billingSummary->franquia.'</td><td>'.$billingSummary->excedente.'</td><td>'.formatBrCurrency($billingSummary->valorFixo,2).'</td><td>'.formatBrCurrency($billingSummary->valorVariavel,2).'</td><td>'.formatBrCurrency($billingSummary->valorTotal,2).'</td></tr>';
                    $grandTotal += $billingSummary->valorTotal;
                }
            }
            $ajuste = 0;
            if (is_numeric ($acrescimo)) {
                $grandTotal = $grandTotal + $acrescimo;
                $ajuste = $acrescimo;
            }
            echo '<tr><td colspan=7 ><h3>Total Geral: '.formatBrCurrency($grandTotal,2).'&nbsp;&nbsp; (Acrésc/Desc = '.formatBrCurrency($ajuste,2).')'.'</h3></td></tr>';
            ?>
            </table>
            <h4>&nbsp;&nbsp;Observações: <?php echo $obs; ?></h4>
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
