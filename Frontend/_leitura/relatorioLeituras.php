<?php

    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/EquipmentReadingDAO.php");
    include_once("../../DataTransferObjects/EquipmentReadingDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $readingSource = $_GET['readingSource'];
    $searchMethod = $_GET['searchMethod'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $equipmentReadingDAO = new EquipmentReadingDAO($dataConnector->sqlserverConnection);
    $equipmentReadingDAO->showErrors = 1;

    // Busca as leituras que se enquadram no filtro aplicado
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
    $filter .= " AND dataLeitura >= '".$startDate." 00:00' AND dataLeitura <= '".$endDate." 23:59'";
    if ($readingSource > 0) $filter = $filter." AND idOrigemLeitura=".$readingSource;
    $equipmentReadingArray = $equipmentReadingDAO->RetrieveRecordArray($filter);

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
    <title>Relatório de Leituras</title>
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
            <h3 style="border:0; margin:0;" >RELATÓRIO DE LEITURAS</h3><br/>
            <h3 style="border:0; margin:0;" >Data inicial: <?php echo $startDate; ?>&nbsp;&nbsp;&nbsp;Data final: <?php echo $endDate; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Data de Leitura</td><td>Cliente</td><td>Série</td><td>Modelo</td><td>Fabricante</td><td>Tipo do Medidor</td><td>Medição</td><td>Ajuste de Leitura</td><td>Reset(S OU N)</td><td>Assinatura Datacopy</td><td>Assinatura Cliente</td><td>Observação</td><td>Forma de Leitura</td><td>Origem</td></tr>
            <?php
                foreach ($equipmentReadingArray as $equipmentReading) {
                    $dataLeitura = empty($equipmentReading->dataLeitura) ? '' : $equipmentReading->dataLeitura->format('d/m/Y');
                    $cliente = $equipmentReading->nomeCliente;
                    $serieEquipamento = $equipmentReading->serieEquipamento;
                    $modelo = $equipmentReading->tagModelo;
                    $fabricante = $equipmentReading->fabricante;
                    $tipoMedidor = $equipmentReading->tipoMedidor;
                    $medicao = $equipmentReading->medicao;
                    $ajusteLeitura = $equipmentReading->ajusteLeitura;
                    $reset = $equipmentReading->reset;
                    $assinaturaDatacopy = $equipmentReading->assinaturaDatacopy;
                    $assinaturaCliente = $equipmentReading->assinaturaCliente;
                    $observacao = $equipmentReading->observacao;
                    $formaLeitura = $equipmentReading->formaLeitura;
                    $origemLeitura = $equipmentReading->origemLeitura;
                    echo '<tr bgcolor="WHITE" ><td>'.$dataLeitura.'</td><td>'.$cliente.'</td><td>'.$serieEquipamento.'</td><td>'.$modelo.'</td><td>'.$fabricante.'</td><td>'.$tipoMedidor.'</td><td>'.$medicao.'</td><td>'.$ajusteLeitura.'</td><td>'.$reset.'</td><td>'.$assinaturaDatacopy.'</td><td>'.$assinaturaCliente.'</td><td>'.$observacao.'</td><td>'.$formaLeitura.'</td><td>'.$origemLeitura.'</td></tr>';
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
