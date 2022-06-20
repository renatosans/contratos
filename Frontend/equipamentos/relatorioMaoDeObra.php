<?php

    include_once("../../defines.php");
    include_once("../../ClassLibrary/Calendar.php");
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
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $laborExpenseDAO = new LaborExpenseDAO($dataConnector->sqlserverConnection);
    $laborExpenseDAO->showErrors = 1;
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $serviceStatisticsDAO = new ServiceStatisticsDAO($dataConnector->mysqlConnection);
    $serviceStatisticsDAO->showErrors = 1;

    $stats = $serviceStatisticsDAO->RetrieveRecord($month, $year);
    empty($stats) ? $tempoTotalAtendimento  = "" : $tempoTotalAtendimento = $stats->tempoEmAtendimento;
    empty($stats) ? $totalEmSegundos = 0         : $totalEmSegundos = $stats->totalEmSegundos;


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
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 8px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Relatório de Mão de Obra</title>
</head>
<body>
    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >RELATÓRIO DE MÃO DE OBRA</h3><br/>
            <h3 style="border:0; margin:0;" >Mês: <?php $calendar = new Calendar(); echo $calendar->GetMonthName($month); ?>&nbsp;&nbsp;&nbsp;Ano: <?php echo $year; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Cliente</td><td>Nº Série Fabricante</td><td>Modelo</td><td>Fabricante</td><td>Número do Chamado</td><td>Tempo de Atendimento</td><td>Tempo Total Area Técnica</td><td>Valor Despesa (R$)</td></tr>
            <?php
                foreach ($laborExpenseArray as $laborExpense) {
                    $serviceCall = $serviceCallDAO->RetrieveRecord($laborExpense->numeroChamado);

                    $cliente = $laborExpense->nomeCliente;
                    $serie = $laborExpense->serieEquipamento;
                    $modelo = $laborExpense->tagModelo;
                    $fabricante = $laborExpense->fabricante;
                    $numeroChamado = $laborExpense->numeroChamado;
                    $tempoAtendimento = $serviceCall->tempoAtendimento;
                    $valorDespesa = ($serviceCall->duracaoEmSegundos / $totalEmSegundos) * $despesaMensal;

                    echo '<tr bgcolor="WHITE" ><td>'.$cliente.'</td><td>'.$serie.'</td><td>'.$modelo.'</td><td>'.$fabricante.'</td><td>'.$numeroChamado.'</td><td>'.$tempoAtendimento.'</td><td>'.$tempoTotalAtendimento.'</td><td>'.number_format($valorDespesa, 2, ',', '.').'</td></tr>';
                }
            ?>
            </table>
            <div style="clear:both;"><br/></div>
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
