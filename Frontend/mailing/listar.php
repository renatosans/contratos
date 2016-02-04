<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/MailingDAO.php");
include_once("../../DataTransferObjects/MailingDTO.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
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
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;


// Traz a lista de mailings cadastrados
$mailingArray = $mailingDAO->RetrieveRecordArray();

?>
    <h1>Administração - Envio de Faturamento</h1>

    <script type="text/javascript" >

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

            $(".mailingPreview").click(function() {
                var businessPartnerCode = $(this).attr("alt");
                var contractId = $(this).attr("rel");
                var subContractId = $(this).attr("rev");

                var reportUrl = "Frontend/mailing/faturamentoContrato.php";
                var addtionalParameters = 'contractId=' + contractId + '&subContractId=' + subContractId;
                if ((contractId == 0) || (contractId == '0')) {
                    reportUrl = "Frontend/mailing/faturamentoCliente.php";
                    addtionalParameters = 'businessPartnerCode=' + businessPartnerCode;
                }

                window.open(reportUrl + '?startDate=&endDate=&acrescimo=&obs=&' + addtionalParameters);
            });

            $("#btnRemoveMailings").button({ icons: {primary:'ui-icon-circle-minus'} }).click( function() {
                var checkedCount = 0;
                var regArray = new Array();
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) { checkedCount++; regArray.push($(this).val()); }
                });
                if (checkedCount == 0) {
                    alert('Marque os registros que deseja excluir.');
                    return;
                }
                if (!confirm("Deseja realmente excluir os registros marcados ?")) {
                    return;
                }

                // Faz um chamada sincrona a página de exclusão
                var targetUrl = 'Frontend/mailing/acao.php?acao=remove';
                var callParameters = {'reg[]': regArray};
                $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

                // Recarrega a página
                LoadPage('Frontend/mailing/listar.php');
            });

        });
    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post">
        <div class="clear">
            <fieldset>
                <legend>Ações:</legend>
                <a href="#" id="checkall" class="button" >
                    Todos
                </a>
                <a href="#" id="uncheckall" class="button">
                    Nenhum
                </a>
                <!-- Só habilita o botão se o usuário possui o nível máximo de autorização -->
                <?php
                    $attributes = '';
                    $url = 'Frontend/'.$currentDir.'/editar.php?id=0';
                    if ($nivelAutorizacao < 3) {
                        $attributes = 'disabled="disabled"';
                        $url = '#';
                    }
                ?>
                <a <?php echo $attributes; ?> href="<?php echo $url; ?>" class="button">
                    Novo
                </a>
                <button type="button" <?php echo $attributes; ?> id="btnRemoveMailings" >
                    Excluir
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="72" size="72" type="text"/>
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
                    <th>&nbsp;Cliente</th>
                    <th>&nbsp;Contrato(s)</th>
                    <th>&nbsp;Dia do Mês</th>
                    <th>&nbsp;Destinatários</th>
                    <th>&nbsp;Preview</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($mailingArray) == 0){
                    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($mailingArray as $mailing) {
            ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value="<?php echo $mailing->id; ?>" name="reg[]"/>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$mailing->id; ?>" >
                            <?php
                                $clientName = new Text($mailing->businessPartnerName);
                                echo $clientName->Truncate(38);
                            ?>
                            </a>
                        </td>
                        <td align="center" >
                            <?php
                                if ($mailing->contrato_id == 0) {
                                    echo 'Agrupar equipmts. do cliente';
                                }
                                else
                                {
                                    $contract = $contractDAO->RetrieveRecord($mailing->contrato_id);
                                    $numero = str_pad($contract->numero, 5, '0', STR_PAD_LEFT);
                                    $status = ContractDAO::GetStatusAsText($contract->status);
                                    $divisao = new Text($contract->divisao);
                                    echo $numero.'('.$status.')'.' '.$divisao->Truncate(12);
                                }
                            ?>
                        </td>
                        <td align="center" >
                           <?php echo $mailing->diaFaturamento; ?>
                        </td>
                        <td >
                            <?php
                                $destinatarios = new Text($mailing->destinatarios);
                                echo $destinatarios->Truncate(20);
                            ?>
                        </td>
                        <td align="center" >
                            <a alt="<?php echo $mailing->businessPartnerCode; ?>" rel="<?php echo $mailing->contrato_id; ?>" rev="<?php echo $mailing->subContrato_id; ?>" class="mailingPreview" >
                                <span class="ui-icon ui-icon-info"></span>
                            </a>
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
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
