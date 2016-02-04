<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
?>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery.min.js" ></script>
    <style type="text/css">
        @page { margin:0.6cm; size: portrait; }
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 11px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Requisição de peças</title>
</head>
<body>
<?php

    $serviceCallId = $_GET['serviceCallId'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];


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

    // Busca os dados do chamado
    $serviceCall = $serviceCallDAO->RetrieveRecord($serviceCallId);

    // Recupera dados do cartão de equipamento
    $equipment = $equipmentDAO->RetrieveRecord($serviceCall->codigoCartaoEquipamento);

    // Busca os dados do cliente
    $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $serviceCall->businessPartnerCode);

?>

    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >REQUISIÇÃO DE PEÇAS</h3><br/>
            <h3 style="border:0; margin:0;" >Equipamento: <?php echo $equipment->manufacturerSN.' ('.$equipment->internalSN.')'; ?></h3>
            </div>
            <div style="clear:both;"><br/></div>
            <hr/>
            <div style="width:96%; margin-left:auto; margin-right:auto;" >
                Chamado: <?php echo str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT); ?><br/><br/>
                Cliente: <?php echo $clientName; ?><br/><br/>
                Modelo: <?php echo $equipment->itemName; ?><br/><br/>
            </div>
            <table id="requisicaoPecas" style="width:96%;" >
                <tr style="height:25px;" ><td>Código</td><td>Descrição do Item</td><td style="width:15%;" >Quantidade</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            </table>
            <div style="clear:both;"><br/></div>
        </div>
        <div style="clear:both;"><br/></div>

        <div style="width:96%; margin-top:1%; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <div style="clear:both;"><br/><br/><br/></div>
            <div style='width:20%;height:20px;float:left;'>
            </div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Data:</p>
            <div style='width:30%;height:20px;float:left;'>
                &nbsp;&nbsp;<hr style='margin:0;' />
            </div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Visto:</p>
            <div style='width:30%;height:20px;float:left;'>
                &nbsp;&nbsp;<hr style='margin:0;' />
            </div>
            <div style="clear:both;"><br/></div>
        </div>

        <div id="pageBottom" style="height:12px;"></div>
    </div>

<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
</body>
</html>
