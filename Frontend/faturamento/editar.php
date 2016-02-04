<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Calendar.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/BillingDAO.php");
include_once("../../DataTransferObjects/BillingDTO.php");
include_once("../../DataAccessObjects/MailingDAO.php");
include_once("../../DataTransferObjects/MailingDTO.php");
include_once("../../DataAccessObjects/InvoiceDAO.php");
include_once("../../DataTransferObjects/InvoiceDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["envioFaturamento"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$billingDAO = new BillingDAO($dataConnector->mysqlConnection);
$billingDAO->showErrors = 1;
$mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
$mailingDAO->showErrors = 1;
$invoiceDAO = new InvoiceDAO($dataConnector->sqlserverConnection);
$invoiceDAO->showErrors = 1;


$id = 0;
$billing = new BillingDTO();
$mailing = new MailingDTO();
$invoiceNum = 0;
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $billing = $billingDAO->RetrieveRecord($id);
    if ($billing == null) {
        echo '<br/><h1>Demonstrativo de faturamento não encontrado</h1><br/>';
        exit;
    }

    $mailing = $mailingDAO->RetrieveRecord($billing->mailing_id);
    $invoiceArray = $invoiceDAO->RetrieveRecordArray(null, "U_demFaturamento = '".$billing->id."'");
    if (sizeof($invoiceArray) > 0) {
        $invoice = $invoiceArray[0];
        $invoiceNum = $invoice->docNum;
    }
}

?>

    <h1>Administração - Faturamento</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function OpenBillingReport(target) {
            var billingId = target.attr("rel");
            var reportUrl = "Frontend/faturamento/imprimir.php";

            window.open(reportUrl + '?billingId=' + billingId);
        }

        function GetInvoiceInfo(target) {
            var invoiceNum = target.attr("rel");

            var targetUrl = "AjaxCalls/GetInvoiceInfo.php?invoiceNum=" + invoiceNum;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 460, height: 460, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        $(document).ready(function() {
            $("#reportLink").click( function() { OpenBillingReport($(this)); } );
            $("#invoiceLink").click( function() { GetInvoiceInfo($(this)); } );
        });
    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="editProperties" />
        <input type="hidden" name="id" value="<?php echo $billing->id; ?>" />

        <fieldset style="width:650px;">
            <legend>Dados</legend>
            Cliente: <?php echo $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $mailing->businessPartnerCode); ?><br/><br/>
            Período: <?php echo $billing->dataInicial.' até '.$billing->dataFinal; ?><br/><br/>
            <a href="#" id="reportLink" rel="<?php echo $billing->id; ?>" >Demonstrativo</a><br/><br/>
            <a href="#" id="invoiceLink" rel="<?php echo $invoiceNum; ?>" >Nota Fiscal</a><br/><br/>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Mes Referência<br/>
        <select name="mesReferencia" style="width:250px;height:30px;" ><?php $calendar = new Calendar(); echo $calendar->GetMonthOptions($billing->mesReferencia); ?></select>
        </label>

        <label>Ano Referência<br/>
        <input type="text" name="anoReferencia" size="45" value="<?php echo $billing->anoReferencia; ?>" />
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform">
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
