<?php

session_start();

include_once("../../../check.php");

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/ContractDAO.php");
include_once("../../../DataTransferObjects/ContractDTO.php");
include_once("../../../DataAccessObjects/SubContractDAO.php");
include_once("../../../DataTransferObjects/SubContractDTO.php");


$contractId = 0;
if (isset($_REQUEST["contractId"]) && ($_REQUEST["contractId"] != 0)) {
    $contractId = $_REQUEST["contractId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;


$id = 0;
$subContract = new SubContractDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $subContract = $subContractDAO->RetrieveRecord($id);
}

if (($id != 0) && isset($subContract)) $contractId = $subContract->codigoContrato;
$contract = $contractDAO->RetrieveRecord($contractId);

?>
    <h1>Contrato: <?php echo str_pad($contract->numero, 5, '0', STR_PAD_LEFT); ?></h1><br/>
    <h1><?php echo str_pad('_', 52, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

            <script type="text/javascript" >

                function GetContractTypeOptions() {
                    var targetUrl = 'AjaxCalls/GetContractTypeOptions.php?contractTypeId=<?php echo $subContract->codigoTipoContrato; ?>';
                    $("select[name=contractTypeId]").load(targetUrl);
                }

                function GetSubContractItems() {
                    var targetUrl = 'AjaxCalls/GetSubContractItems.php?subContractId=<?php echo $subContract->id; ?>';
                    $("#itemList").load(targetUrl);
                }

                function GetSubContractCharges() {
                    var targetUrl = 'AjaxCalls/GetSubContractCharges.php?subContractId=<?php echo $subContract->id; ?>';
                    $("#chargeList").load(targetUrl);
                }

                function GetSubContractBonuses() {
                    var targetUrl = 'AjaxCalls/GetSubContractBonuses.php?subContractId=<?php echo $subContract->id; ?>';
                    $("#bonusList").load(targetUrl);
                }

                function RemoveSubContractItem(item) {
                    if(!confirm("Confirma exclusão?")) return false;

                    // Faz um chamada sincrona a página de exclusão
                    var equipmentCode = item.attr("rel");
                    var contractId = $("input[name=contractId]").val();
                    var targetUrl = 'Frontend/contrato/sub-contrato/acaoItem.php?acao=remove';
                    var callParameters = {'equipmentCode': equipmentCode, 'contractId': contractId};
                    $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

                    // Recarrega a lista de itens
                    GetSubContractItems();
                }

                function RemoveSubContractCharge(charge) {
                    if(!confirm("Confirma exclusão?")) return false;

                    // Faz um chamada sincrona a página de exclusão
                    var id = charge.attr("rel");
                    var contractId = $("input[name=contractId]").val();
                    var targetUrl = 'Frontend/contrato/sub-contrato/acaoCobranca.php?acao=remove';
                    var callParameters = { 'id': id, 'contractId': contractId };
                    $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

                    // Recarrega a lista de cobranças
                    GetSubContractCharges();
                }

                function RemoveSubContractBonus(bonus) {
                    if(!confirm("Confirma exclusão?")) return false;

                    // Faz um chamada sincrona a página de exclusão
                    var id = bonus.attr("rel");
                    var contractId = $("input[name=contractId]").val();
                    var targetUrl = 'Frontend/contrato/sub-contrato/acaoBonus.php?acao=remove';
                    var callParameters = { 'id': id, 'contractId': contractId };
                    $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

                    // Recarrega a lista de bonus
                    GetSubContractBonuses();
                }

                $(document).ready(function() {
                    $("#btnAddContractType").button({icons: { primary:'ui-icon-circle-plus' }}).click( function() {
                        var targetUrl = 'AjaxCalls/AddContractType.php';
                        $("form[name=fDados]").append("<div id='addDialog'></div>");
                        $("#addDialog").load(targetUrl).dialog({modal:true, width: 280, height: 300, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                    });

                    $("#btnRemoveContractType").button({icons: { primary:'ui-icon-circle-minus' }}).click( function() {
                        if(!confirm("Confirma exclusão?")) return false;

                        // Faz um chamada sincrona a página que exclui o tipo de contrato
                        var contractTypeId = $("select[name=contractTypeId]").val();
                        var targetUrl = 'Frontend/contrato/acaoTipo.php?acao=remove&id=' + contractTypeId;
                        $.ajax({ type: 'POST', url: targetUrl, success: function(response) { alert(response); }, async: false });

                        // Recarrega os tipos de contrato
                        GetContractTypeOptions();
                    });

                    $("#btnExcluirSubContrato").button({icons: { primary:'ui-icon-closethick' }}).click( function() {
                        if(!confirm("Confirma exclusão?")) return false;

                        // Faz um chamada sincrona a página de exclusão
                        var targetUrl = $("#btnExcluirSubContrato").attr("href");
                        var callParameters = { id: $("input[name=id]").val() };
                        $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

                        // Recarrega a página
                        LoadPage('Frontend/contrato/editar.php?id=<?php echo $contract->id; ?>');
                    });

                    $("#btnAddItem").button({ icons: {primary:'ui-icon-circle-plus' } }).click( function() {
                        var targetUrl = 'AjaxCalls/AddSubContractItem.php?subContractId=<?php echo $subContract->id; ?>';
                        $("form[name=fDados]").append("<div id='addDialog'></div>");
                        $("#addDialog").load(targetUrl).dialog({modal:true, width: 300, height: 280, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                    });

                    $("#btnAddCharge").button({ icons: {primary:'ui-icon-circle-plus' } }).click( function() {
                        var targetUrl = 'AjaxCalls/AddSubContractCharge.php?subContractId=<?php echo $subContract->id; ?>';
                        $("form[name=fDados]").append("<div id='addDialog'></div>");
                        $("#addDialog").load(targetUrl).dialog({modal:true, width: 400, height: 460, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                    });

                    $("#btnAddBonus").button({ icons: {primary:'ui-icon-circle-plus' } }).click( function() {
                        var targetUrl = 'AjaxCalls/AddSubContractBonus.php?subContractId=<?php echo $subContract->id; ?>';
                        $("form[name=fDados]").append("<div id='addDialog'></div>");
                        $("#addDialog").load(targetUrl).dialog({modal:true, width: 300, height: 350, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                    });

                    GetContractTypeOptions();

                    GetSubContractItems();

                    GetSubContractCharges();

                    GetSubContractBonuses();
                });

            </script>

    <form name="fDados" action="Frontend/contrato/sub-contrato/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="contractId" value="<?php echo $contract->id; ?>" />

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>

        <label class="left" style="width:280px;" >Tipo de Contrato<br />
            <select name="contractTypeId" style="width:60%;" /> &nbsp;
            <button type="button" <?php echo $attributes; ?> id="btnAddContractType" style="width:30px;height:30px;" title="Adicionar" />
            <button type="button" <?php echo $attributes; ?> id="btnRemoveContractType" style="width:30px;height:30px;" title="Remover" />
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>


        <!-- Só exibe os items de contrato quando o subContrato possuir um id -->
        <div id="itens" style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $attributes; ?> id="btnAddItem" >
                Adicionar Item
            </button>
            <br/>
            <fieldset style="width:650px;" >
                <legend>Itens</legend>
                <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
                    <thead>
                        <tr>
                            <th>&nbsp;SN</th>
                            <th>&nbsp;Obs.</th>
                            <th>&nbsp;SLA</th>
                            <th>&nbsp;Leituras</th>
                            <th>&nbsp;Chamados</th>
                            <th>&nbsp;Consumíveis</th>
                            <th>&nbsp;Excluir</th>
                        </tr>
                    </thead>
                    <tbody id="itemList" >
                        <tr>
                            <td colspan="7" align="center" >
                                Nenhum registro encontrado!
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pager pagerListar">
                    <span class="wraper">
                        <button class="first">First</button>
                    </span>
                    <span class="wraper">
                        <button class="prev">Prev</button>
                    </span>
                    <span class="wraper center">
                        <input type="text" class="pagedisplay"/>
                    </span>
                    <span class="wraper">
                        <button class="next">Next</button>
                    </span>
                    <span class="wraper">
                        <button class="last">Last</button>
                    </span>
                    <input type="hidden" class="pagesize" value="10" />
                </div>
            </fieldset>
            <div style="clear:both;">
                <br/><br/>
            </div>
        </div>


        <!-- Só exibe as formas de cobrança quando o subContrato possuir um id -->
        <div id="cobranca" style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $attributes; ?> id="btnAddCharge" >
                Adicionar Cobrança
            </button>
            <br/>
            <fieldset style="width:650px;" >
                <legend>Cobrança</legend>
                <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
                    <thead>
                        <tr>
                            <th>&nbsp;Contador</th>
                            <th>&nbsp;Modalidade de Medição</th>
                            <th>&nbsp;Fixo</th>
                            <th>&nbsp;Variável</th>
                            <th>&nbsp;Franquia</th>
                            <th>&nbsp;Individual</th>
                            <th>&nbsp;Excluir</th>
                        </tr>
                    </thead>
                    <tbody id="chargeList" >
                        <tr>
                            <td colspan="7" align="center" >
                                Nenhum registro encontrado!
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pager pagerListar">
                    <span class="wraper">
                        <button class="first">First</button>
                    </span>
                    <span class="wraper">
                        <button class="prev">Prev</button>
                    </span>
                    <span class="wraper center">
                        <input type="text" class="pagedisplay"/>
                    </span>
                    <span class="wraper">
                        <button class="next">Next</button>
                    </span>
                    <span class="wraper">
                        <button class="last">Last</button>
                    </span>
                    <input type="hidden" class="pagesize" value="10" />
                </div>
            </fieldset>
            <div style="clear:both;">
                <br/><br/>
            </div>
        </div>


        <!-- Só exibe a lista de bonus quando o subContrato possuir um id -->
        <div id="bonus" style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $attributes; ?> id="btnAddBonus" >
                Adicionar Bonus
            </button>
            <br/>
            <fieldset style="width: 650px;" >
                <legend>Bonus</legend>
                <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
                    <thead>
                        <tr>
                            <th>&nbsp;Contador</th>
                            <th>&nbsp;De</th>
                            <th>&nbsp;Até</th>
                            <th>&nbsp;Valor</th>
                            <th>&nbsp;Excluir</th>
                        </tr>
                    </thead>
                    <tbody id="bonusList" >
                        <tr>
                            <td colspan="5" align="center" >
                                Nenhum registro encontrado!
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pager pagerListar">
                    <span class="wraper">
                        <button class="first">First</button>
                    </span>
                    <span class="wraper">
                        <button class="prev">Prev</button>
                    </span>
                    <span class="wraper center">
                        <input type="text" class="pagedisplay"/>
                    </span>
                    <span class="wraper">
                        <button class="next">Next</button>
                    </span>
                    <span class="wraper">
                        <button class="last">Last</button>
                    </span>
                    <input type="hidden" class="pagesize" value="10" />
                </div>
            </fieldset>
            <div style="clear:both;">
                <br/><br/>
            </div>
        </div>


        <a href="Frontend/contrato/editar.php?id=<?php echo $contract->id ?>" class="buttonVoltar" >
            Voltar
        </a>

        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform" >
            Salvar
        </button>

        <!-- Só exibe o botão quando o subContrato possuir um id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $attributes; ?> style="float:right;" href="Frontend/contrato/sub-contrato/acao.php?acao=remove" id="btnExcluirSubContrato" >
                Excluir
            </button>
        </div>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
