<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Calendar.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");
include_once("../../DataAccessObjects/BillingDAO.php");
include_once("../../DataTransferObjects/BillingDTO.php");
include_once("../../DataAccessObjects/MailingDAO.php");
include_once("../../DataTransferObjects/MailingDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["sinteseFaturamento"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$billingDAO = new BillingDAO($dataConnector->mysqlConnection);
$billingDAO->showErrors = 1;
$mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
$mailingDAO->showErrors = 1;
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;


// Traz os faturamentos de acordo com o mês e ano de referência
$mesFaturamento = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "mesFaturamento");
$anoFaturamento = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "anoFaturamento");
$billingArray = $billingDAO->RetrieveRecordArray("mesReferencia = '".$mesFaturamento."' AND anoReferencia = '".$anoFaturamento."'");

?>
    <h1>Síntese de Faturamento (<?php $calendar = new Calendar(); echo $calendar->GetMonthName($mesFaturamento).' de '.$anoFaturamento; ?>)</h1><br/>
    <h1><?php echo str_pad('_', 64, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function OpenBillingReport(target) {
            var billingId = target.attr("rel");
            var reportUrl = "Frontend/faturamento/imprimir.php";

            window.open(reportUrl + '?billingId=' + billingId);
        }

        $(document).ready(function() {

            var pageLoad = true;

            $("input[name=filter]").keyup(function() {
                document.cookie = "lastSearch=" + $(this).val() + "...";
            });

            $(".sorTable").bind("sortEnd", function() {
                var cookieIdentifier = new String("lastSearch=");
                var startPos = document.cookie.indexOf(cookieIdentifier);
                var cookieLength = document.cookie.indexOf("...") - startPos - cookieIdentifier.length;
                var lastSearch = document.cookie.substr(startPos + cookieIdentifier.length, cookieLength);

                if ((lastSearch != '') && (pageLoad)) { 
                    $("input[name=filter]").val(lastSearch);
                    $("input[name=filter]").trigger("keyup");
                }
                pageLoad = false;
            });


            $(".reportLink").click( function() { OpenBillingReport($(this)); } );


            $("#btnIncluirRelatorio").button({icons: { primary:'ui-icon-circle-plus' }}).click( function() {
                var checkedCount = 0;
                var idArray = new Array();
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) {
                        checkedCount++;
                        idArray.push($(this).val());
                    }
                });
                if (checkedCount == 0) {
                    alert('Marque os faturamentos para inclusão.');
                    return;
                }
                // Faz uma requisição sincrona chamando a ação
                var targetUrl = "Frontend/<?php echo $currentDir; ?>/acao.php?acao=add";
                var callParameters = {'reg[]': idArray};
                $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });
                // Recarrega a página
                LoadPage("Frontend/<?php echo $currentDir; ?>/listar.php");
            });

            $("#btnIncluirTodos").button({icons: { primary:'ui-icon-circle-plus' }}).click( function() {
                // Faz uma requisição sincrona chamando a ação
                var targetUrl = "Frontend/<?php echo $currentDir; ?>/acao.php?acao=addAll";
                $.ajax({ type: 'POST', url: targetUrl, success: function(response) { alert(response); }, async: false });
                // Recarrega a página
                LoadPage("Frontend/<?php echo $currentDir; ?>/listar.php");
            });

            $("#btnRemoverRelatorio").button({icons: { primary:'ui-icon-circle-minus' }}).click( function() {
                var checkedCount = 0;
                var idArray = new Array();
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) {
                        checkedCount++;
                        idArray.push($(this).val());
                    }
                });
                if (checkedCount == 0) {
                    alert('Marque os faturamentos para remoção.');
                    return;
                }
                // Faz uma requisição sincrona chamando a ação
                var targetUrl = "Frontend/<?php echo $currentDir; ?>/acao.php?acao=remove";
                var callParameters = {'reg[]': idArray};
                $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });
                // Recarrega a página
                LoadPage("Frontend/<?php echo $currentDir; ?>/listar.php");
            });

            $("#btnRemoverTodos").button({icons: { primary:'ui-icon-circle-minus' }}).click( function() {
                // Faz uma requisição sincrona chamando a ação
                var targetUrl = "Frontend/<?php echo $currentDir; ?>/acao.php?acao=removeAll";
                $.ajax({ type: 'POST', url: targetUrl, success: function(response) { alert(response); }, async: false });
                // Recarrega a página
                LoadPage("Frontend/<?php echo $currentDir; ?>/listar.php");
            });

            $("#btnEmitirRelacaoCustosReceitas").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                var reportUrl = 'Frontend/<?php echo $currentDir; ?>/relacaoCustosReceitasExcel.php';
                window.open(reportUrl);
            });

            $("#btnFind").button({ icons: {primary:'ui-icon-search'} }).click( function() {
                var billingId = $("input[name=billingId]").val();
                if ((!billingId) || isNaN(billingId)) {
                    alert('Preencher o número do demonstrativo!');
                    return;
                }

                LoadPage('Frontend/faturamento/editar.php?id=' + billingId);
            });
        });
    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post">
        <div class="clear">
            <fieldset>
                <legend>Ações:</legend>
                <button type="button" id="btnIncluirRelatorio" >
                    Incluir
                </button>
                <button type="button" id="btnIncluirTodos" >
                    Incluir Todos
                </button>
                <button type="button" id="btnRemoverRelatorio" >
                    Retirar
                </button>
                <button type="button" id="btnRemoverTodos" >
                    Retirar Todos
                </button>
                <button type="button" id="btnEmitirRelacaoCustosReceitas" >
                    Custos/Receitas
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="25" size="25" type="text"/>
                    <button id="filter-clear-button" type="submit" value="Clear">Clear</button>
                </fieldset>
            </div>
        </div>
        <br/>
        <input type="hidden" name="acao" value="remove" />
        <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;Número</th>
                    <th>&nbsp;Cliente</th>
                    <th>&nbsp;Contrato(s)</th>
                    <th>&nbsp;Período</th>
                    <th style="text-align: center;" >&nbsp;Print</th>
                    <th style="text-align: center;" >&nbsp;Check</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (sizeof($billingArray) == 0){
                    echo '<tr><td colspan="7" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($billingArray as $billing) {
                    $mailing = $mailingDAO->RetrieveRecord($billing->mailing_id);
                ?>
                    <tr>
                        <td align="center">
                            <input type="checkbox" value= "<?php echo $billing->id; ?>" name="reg[]"/>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$billing->id; ?>" >
                                <?php echo str_pad($billing->id, 5, '0', STR_PAD_LEFT); ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$billing->id; ?>" >
                                <?php
                                    $clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $mailing->businessPartnerCode));
                                    echo $clientName->Truncate(35);
                                ?>
                            </a>
                        </td>
                        <td>
                            <?php
                                if ($mailing->contrato_id == 0) {
                                    echo 'Agrupar equipmts. do cliente';
                                }
                                else
                                {
                                    $contract = $contractDAO->RetrieveRecord($mailing->contrato_id);
                                    $numero = str_pad($contract->numero, 5, '0', STR_PAD_LEFT);
                                    $status = ContractDAO::GetStatusAsText($contract->status);
                                    echo $numero.'('.$status.')';
                                }
                            ?>
                        </td>
                        <td>
                           <?php echo $billing->dataInicial.' até '.$billing->dataFinal; ?>
                        </td>
                        <td>
                            <a class="reportLink" rel="<?php echo $billing->id; ?>" >
                                <span class="ui-icon ui-icon-alert"></span>
                            </a>
                        </td>
                        <td align="center">
                           <?php if ($billing->incluirRelatorio == 1) echo "<img src='img/admin/checked_sign.png' style='margin: 0;' />"; ?>
                        </td>
                    </tr>
                <?php
                }
                ?>
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
        <div style="clear:both;">
            <br/><br/>
        </div>

        <label><h3>Localizar outros (Exemplo: faturamento de outro mês)</h3><br/>
        <input name="billingId" style="height:25px;" value="" />
        <button type="button" id="btnFind" style="height: 30px;" >Localizar</button>
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
