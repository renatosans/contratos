<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/MailingDAO.php");
include_once("../../DataTransferObjects/MailingDTO.php");
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

// Cria o objeto de mapeamento objeto-relacional
$mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
$mailingDAO->showErrors = 1;

$id = 0;
$mailing = new MailingDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $mailing = $mailingDAO->RetrieveRecord($id); 
}

?>

    <script type="text/javascript" >
        function GetContractOptions() {
            var businessPartnerCode = $("select[name=businessPartnerCode]").val(); // traz o valor selecionado no combo
            var contractId = <?php echo $mailing->contrato_id; ?>; // traz o último valor gravado no banco
            var targetUrl = 'AjaxCalls/GetContractOptions.php?businessPartnerCode=' + businessPartnerCode + '&contractId=' + contractId;
            $.ajax({ type: 'POST', url: targetUrl, success: function(response) { ReloadContractOptions(response); }, async: false });
        }

        function SetBusinessPartnerName() {
            var businessPartnerName = $("select[name=businessPartnerCode] option:selected").text();
            $("input[name=businessPartnerName]").val(businessPartnerName);
        }

        function ReloadContractOptions(options) {
            $("select[name=contrato_id]").empty();
            $("select[name=contrato_id]").append(options);
        }

        function GetSubcontractOptions() {
            var contractId = $("select[name=contrato_id]").val();
            var subContractId = <?php echo $mailing->subContrato_id; ?>; // traz o último valor gravado no banco
            var targetUrl = 'AjaxCalls/GetSubcontractOptions.php?contractId=' + contractId + '&subContractId=' + subContractId;
            $.get(targetUrl, function(response){ ReloadSubcontractOptions(response); });
        }

        function ReloadSubcontractOptions(options) {
            var selectedContract = $("select[name=contrato_id]").val();
            if (selectedContract == 0) {
                $("#subContractField").hide();
                $("select[name=subContrato_id]").empty();
                $("select[name=subContrato_id]").append('<option value="0" >Todos</option>');
                return;
            }

            $("#subContractField").show();
            $("select[name=subContrato_id]").empty();
            $("select[name=subContrato_id]").append(options);
        }

        function GetBillingRecords() {
            var mailingId = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetBillingRecords.php?mailingId=' + mailingId;
            $("#billingRecords").load(targetUrl);
        }

        function RemoveBilling(billing) {
            if(!confirm("Confirma exclusão?")) return false;

            // Faz um chamada sincrona a página que exclui o registro de faturamento
            var billingId = billing.attr("rel");
            var targetUrl = 'Frontend/mailing/acaoFaturamento.php?acao=remove';
            var callParameters = {'id': billingId};
            $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

            // Recarrega os registros de faturamentos cadastrados para o mailing
            GetBillingRecords();
        }

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
            $("select[name=businessPartnerCode]").change(function() { GetContractOptions(); SetBusinessPartnerName(); });
            GetContractOptions();
            SetBusinessPartnerName();
            $("select[name=contrato_id]").change(function() { GetSubcontractOptions(); });
            GetSubcontractOptions();

            $("#btnAdicionarFaturamento").button({ icons: {primary:'ui-icon-plus' } }).click( function() {
                var mailingId = $("input[name=id]").val();
                var targetUrl = 'AjaxCalls/AddBillingRecord.php?mailingId=' + mailingId;
                $("form[name=fDados]").append("<div id='addDialog'></div>");
                $("#addDialog").load(targetUrl).dialog({modal:true, width: 440, height: 460, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            GetBillingRecords();
        });
    </script>

    <h1>Envio de Faturamento</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <?php
            $btnAttributes = '';
            if ($nivelAutorizacao < 3) $btnAttributes = 'disabled="disabled"';
        ?>

        <label>Cliente<br/>
        <select name="businessPartnerCode" style="width: 350px;">
            <option selected='selected' value="0"></option>
            <?php
                $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
                $businessPartnerDAO->showErrors = 1;
                $clientArray = $businessPartnerDAO->RetrieveRecordArray("CardName IS NOT NULL ORDER BY CardName");
                foreach ($clientArray as $client) {
                    $attributes = "";
                    if ($client->cardCode == $mailing->businessPartnerCode) $attributes = "selected='selected'";
                    $informacaoAdicional = "";
                    if ($client->cardName != $client->cardFName) $informacaoAdicional = " (".$client->cardFName.")";
                    $clientInfo = new Text($client->cardName.$informacaoAdicional);
                    echo "<option ".$attributes." value=".$client->cardCode." >".$clientInfo->Truncate(85)."</option>";
                }
            ?>
        </select>
        <br/>
        <input type="hidden" name="businessPartnerName" value="" />
        </label>

        <label>Contrato(s)<br/>
            <select name="contrato_id" style="width: 350px;" />
        </label>

        <label id="subContractField" >SubContrato(s)<br/>
            <select name="subContrato_id" style="width: 350px;" />
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <label>Dia do Faturamento<br/>
        <input type="text" name="diaFaturamento" size="15" value="<?php echo $mailing->diaFaturamento; ?>" />
        </label>

        <label>Conteúdo do e-mail<br/>
            <select name="enviarDemonstrativo" style="width: 350px;" >
                <option value="0" <?php echo ($mailing->enviarDemonstrativo == 0)? 'selected="selected"' : ''; ?> >Dados da fatura</option>
                <option value="1" <?php echo ($mailing->enviarDemonstrativo == 1)? 'selected="selected"' : ''; ?> >Dados da fatura e demonstrativo</option>
            </select>
        </label>

        <label>Destinatários (emails separados por vírgula)<br/>
            <input type="text" name="destinatarios" size="85" value="<?php echo $mailing->destinatarios; ?>" />
        </label>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <!-- Só exibe o botão quando tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $btnAttributes; ?> id="btnAdicionarFaturamento" >Adicionar Faturamento</button>
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width: 750px;" >
            <legend>Faturamentos</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th>&nbsp;Número</th>
                    <th>&nbsp;Periodo</th>
                    <th>&nbsp;Acréscimo/Desconto</th>
                    <th>&nbsp;Observações</th>
                    <th>&nbsp;Nota Fiscal</th>
                    <th>&nbsp;Imprimir</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="billingRecords"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="7" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
           </table>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <button type="submit" <?php echo $btnAttributes; ?> class="button" id="btnform">
            Salvar
        </button>
    </form>

<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
