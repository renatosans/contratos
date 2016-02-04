<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SupplyRequestDAO.php");
include_once("../../DataTransferObjects/SupplyRequestDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");


$equipmentCode = 0;
if (isset($_REQUEST["equipmentCode"]) && ($_REQUEST["equipmentCode"] != 0)) {
    $equipmentCode = $_REQUEST["equipmentCode"];
}
$subContract = 0;
if (isset($_REQUEST["subContract"]) && ($_REQUEST["subContract"] != 0)) {
    $subContract = $_REQUEST["subContract"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["solicitacaoConsumiveis"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
$supplyRequestDAO->showErrors = 1;

$id = 0;
$supplyRequest = new SupplyRequestDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $supplyRequest = $supplyRequestDAO->RetrieveRecord($id);
    $equipmentCode = $supplyRequest->codigoCartaoEquipamento; // sobrescreve o parâmetro recebido na url caso a solicitação esteja sendo alterada/editada
}

$equipmentInfo = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $equipmentCode);

$defaultEmailAddress = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "emailPadrao");

?>

    <h1>Solicitação de Consumível <?php echo $equipmentInfo; ?></h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function CheckConsumption(){
            // Faz uma chamada a página que busca o nível de utilização do consumível
            var supplyRequestId = $("input[name=id]").val();
            var cutoffDate = $("input[name=data]").val() + 'T' + $("input[name=hora]").val();
            var targetUrl = 'AjaxCalls/GetSupplyConsumption.php?supplyRequestId=' + supplyRequestId + '&cutoffDate=' + cutoffDate;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 750, height: 280, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        function GetRequestItems(){
            var supplyRequestId = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetRequestItems.php?supplyRequestId=' + supplyRequestId;
            $("#requestItems").load(targetUrl);
        }

        function GetSupplyReadings(){
            var supplyRequestId = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetSupplyReadings.php?supplyRequestId=' + supplyRequestId;
            $("#supplyReadings").load(targetUrl);
        }

        function RemoveRequestItem(item){
            if(!confirm("Confirma exclusão?")) return false;

            // Faz um chamada sincrona a página que exclui o item
            var requestItemId = item.attr("rel");
            var targetUrl = 'Frontend/_consumivel/acaoItem.php?acao=remove';
            var callParameters = {'id': requestItemId};
            $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

            // Recarrega os itens da solicitação
            GetRequestItems();
        }

        function RemoveReading(reading) {
            if(!confirm("Confirma exclusão?")) return false;

            // Faz um chamada sincrona a página que exclui o contador
            var leitura = reading.attr("rel");
            var targetUrl = 'Frontend/_leitura/acao.php?acao=remove';
            var callParameters = {'reg[]': leitura};
            $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

            // Recarrega os contadores
            GetSupplyReadings();
        }


        $(document).ready(function() {
            $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

            $("#btnSalvar").button({ icons: {primary:'ui-icon-circle-check'} }).click( function() {
                var supplyRequestId = 0;

                // Faz um chamada sincrona a página de gravação
                var targetUrl = 'Frontend/_consumivel/acao.php';
                $.ajax({ type: 'POST', url: targetUrl, data: $("form").serialize(), success: function(response) { if (isNaN(response)) { alert(response); return; } supplyRequestId = response; }, async: false });

                if (supplyRequestId == 0) return;

                // Recarrega a solicitação de consumível
                LoadPage('Frontend/_consumivel/editar.php?id=' + supplyRequestId);
            });
            $("#btnAdicionarItem").button({ icons: {primary:'ui-icon-plus' } }).click( function() {
                var supplyRequestId = $("input[name=id]").val();
                var targetUrl = 'AjaxCalls/AddRequestItem.php?supplyRequestId=' + supplyRequestId;
                $("form[name=fDados]").append("<div id='addDialog'></div>");
                $("#addDialog").load(targetUrl).dialog({modal:true, width: 430, height: 380, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#btnAdicionarContador").button({ icons: {primary:'ui-icon-plus' } }).click( function() {
                var supplyRequestId = $("input[name=id]").val();
                var targetUrl = 'AjaxCalls/AddSupplyReading.php?supplyRequestId=' + supplyRequestId;
                $("form[name=fDados]").append("<div id='addDialog'></div>");
                $("#addDialog").load(targetUrl).dialog({modal:true, width: 540, height: 480, close: function(event, ui) { $(this).dialog('destroy').remove(); CheckConsumption(); }});
            });
            $("#btnEnviarSolicitacao").button({ icons: {primary:'ui-icon-mail-closed'} }).click( function() {
                var status = $("select[name=status]").val();
                if(status == 2) {
                    alert('Não é possível enviar uma solicitação com o status [Em espera].');
                    return false;
                }
                // Para cada item da solicitação verifica se excede a quantidade em estoque, só exibe uma mensagem de erro 
                // de cada vez ( a última do loop ), o usuário corrige uma por vez até esvaziar a pilha de erros
                var exceeded = false;
                var description = '';
                $('#requestItems > tr').each(function() {
                    var itemCode = $(this).attr("rel");
                    var quantity = $(this).attr("rev");
                    quantity = parseInt(quantity) || 0;

                    var stockQuantity = 0;
                    var targetUrl = 'AjaxCalls/GetStockQuantity.php?itemCode=' + itemCode;
                    $.ajax({ url: targetUrl, success: function(response) { stockQuantity = response; }, async: false });
                    stockQuantity = parseInt(stockQuantity) || 0;
 
                    if (quantity > stockQuantity) {
                        exceeded = true;
                        description = 'Item: ' + itemCode + ' qtd. em estoque: ' + stockQuantity;
                    }
                });
                if (exceeded) {
                    alert('A quantidade solicitada excede a quantidade em estoque!\n' + description);
                    return false;
                }
                $("#btnEnviarSolicitacao").attr('disabled', 'disabled');
                var recipients = $("input[name=recipients]").val(); // traz o valor do input
                var supplyRequestId = $("input[name=id]").val();
                var targetUrl = 'AjaxCalls/RequestEquipmentSupplies.php?recipients=' + recipients + '&supplyRequestId=' + supplyRequestId;
                $.ajax({ url: targetUrl, success: function(response) { $("#btnEnviarSolicitacao").removeAttr('disabled'); alert(response); }, async: true });
            });

            GetRequestItems();
            GetSupplyReadings();
        });
    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="equipmentCode" value="<?php echo $equipmentCode; ?>" />

        <?php
            $btnAttributes = '';
            if ($nivelAutorizacao < 3) $btnAttributes = 'disabled="disabled"';
        ?>

        <!-- Só exibe a caixa com o número da solicitação quando tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <label>Número da solicitação<br /> <!-- exibe o número para o usuário, o campo "id" é um input hidden -->
            <input type="text" readonly="readonly" size="30" value="<?php echo str_pad($supplyRequest->id, 5, '0', STR_PAD_LEFT); ?>" />
            </label>
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Data da solicitação<br/>
        <input type="text" class="datepick" name="data" size="30" value="<?php echo empty($supplyRequest->data)? date("Y-m-d",time()) : $supplyRequest->data; ?>" />
        <input type="text" name="hora" size="10" value="<?php echo empty($supplyRequest->hora)? date("H:i",time()) : $supplyRequest->hora; ?>" />
        &nbsp;&nbsp;&nbsp;
        </div>

        <label class="left" >Status<br/>
            <select name="status" style="width: 210px;">
                <option value="1" <?php if ($supplyRequest->status == 1) echo ' selected '; ?>>
                    Em andamento
                </option>
                <option value="2" <?php if ($supplyRequest->status == 2) echo ' selected '; ?>>
                    Em espera
                </option>
                <option value="3" <?php if ($supplyRequest->status == 3) echo ' selected '; ?>>
                    Finalizada
                </option>
            </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Observação<br/>
            <textarea name="observacao" style="width: 650px;" ><?php echo $supplyRequest->observacao; ?></textarea>
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <!-- Só exibe o botão quando tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $btnAttributes; ?> id="btnAdicionarItem" >Adicionar Item</button>
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width: 650px;" >
            <legend>Itens</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th>&nbsp;Código</th>
                    <th>&nbsp;Descrição</th>
                    <th>&nbsp;Quantidade</th>
                    <th>&nbsp;Subtotal(R$)</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="requestItems"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="5" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
           </table>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <!-- Só exibe o botão quando tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $btnAttributes; ?> id="btnAdicionarContador" >Adicionar Contador</button>
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width: 650px;" >
            <legend>Contadores</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th>&nbsp;Contador</th>
                    <th>&nbsp;Medição</th>
                    <th>&nbsp;Assinatura Datacopy</th>
                    <th>&nbsp;Assinatura Cliente</th>
                    <th>&nbsp;Observação</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="supplyReadings"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="6" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
           </table>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>


        <fieldset style="width: 650px;" >
            <legend>Solicitação de Consumível</legend>
            <div id="solicitacaoConsumivel" style="width: 510px;">
                <label class="left">Destinatários (emails separados por vírgula)<br/>
                    <input type="text" name="recipients" style="width:310px;" value="<?php echo $defaultEmailAddress; ?>" ></input>
                </label>
                <label class="left">
                <br/>&nbsp;&nbsp;&nbsp;
                <button type="button" <?php echo $btnAttributes; ?> id="btnEnviarSolicitacao" >Enviar Solicitação</button>
                </label>
            </div>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo NavigateBackTarget(); ?>" class="buttonVoltar" >
            Voltar
        </a>

        <!-- Salva através de chamada AJAX para o usuário continuar o preenchimento dos outros dados -->
        <button type="button" <?php echo $btnAttributes; ?> id="btnSalvar" >
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
