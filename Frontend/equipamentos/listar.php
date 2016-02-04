<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoEquipmtPecas"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
$equipmentDAO->showErrors = 1;

// Traz a lista de equipamentos
$cutoffDate = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "limiteListaEquipamentos");
$equipmentArray = $equipmentDAO->RetrieveRecordArray("(status = 'A' OR status = 'L') AND U_InstallationDate > '".$cutoffDate."' ORDER BY manufSN");

?>

    <h1>Equipamentos (instalados a partir de <?php echo $cutoffDate; ?>)</h1>

    <script type="text/javascript" >

        // Traz a lista de equipamentos selecionados pelo usuário (checkboxes marcados)
        function ObterListaEquipamentos()
        {
            var checkedCount = 0;
            var equipmentList = '';
            $("input[type=checkbox]").each( function() {
                if (($(this).attr("name") == 'reg[]') && $(this).is(":checked")) {
                    checkedCount++;
                    if (checkedCount > 1) equipmentList += ',';
                    equipmentList += $(this).val();
                }
            });
            if (checkedCount == 0) {
                return null;
            }
            return equipmentList;
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

            $("#btnEmitirTempoAtendimento").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                equipmentList = ObterListaEquipamentos();
                if (!equipmentList) {
                    alert('Marque os equipamentos para emissão do relatório.');
                    return;
                }

                var reportUrl = 'Frontend/<?php echo $currentDir; ?>/emitirTempoAtendimento.php';
                var targetUrl = 'AjaxCalls/DateFilterDialog.php';
                var aditionalParameters = '&equipmentList=' +  equipmentList;
                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:aditionalParameters}).dialog({modal:true, width: 250, height: 230, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });

            $("#btnHistoricoTrocaPecas").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                equipmentList = ObterListaEquipamentos();
                if (!equipmentList) {
                    alert('Marque os equipamentos para emissão do relatório.');
                    return;
                }

                var reportUrl = 'Frontend/<?php echo $currentDir; ?>/historicoTrocaPecas.php?equipmentList=' +  equipmentList;
                window.open(reportUrl);
            });

            $("#btnFind").button({ icons: {primary:'ui-icon-search'} }).click( function() {
                var serial = $("input[name=serial]").val();
                if (!serial) {
                    alert('Preencher o número de série do equipamento!');
                    return;
                }

                LoadPage('Frontend/equipamentos/editar.php?serial=' + escape(serial));
            });
        });

    </script>

    <form id="fLista" name="fLista" action="#" method="post">
        <div class="clear" >
            <fieldset>
                <legend>Ações:</legend>
                <a href="#" id="checkall" class="button" >
                    Todos
                </a>
                <a href="#" id="uncheckall" class="button">
                    Nenhum
                </a>
                <button type="button" id="btnEmitirTempoAtendimento" >
                    Tempo em Atend.
                </button>
                <button type="button" id="btnHistoricoTrocaPecas" >
                    Troca de Peças
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="40" size="40" type="text" />
                    <button id="filter-clear-button" type="submit" value="Clear">Clear</button>
                </fieldset>
            </div>
        </div>
        <br/>

        <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;SN</th>
                    <th>&nbsp;Obs.</th>
                    <th>&nbsp;SLA</th>
                    <th>&nbsp;Leituras</th>
                    <th>&nbsp;Chamados</th>
                    <th>&nbsp;Consumíveis</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($equipmentArray) == 0){
                    echo '<tr><td colspan="7" align="center" >Nenhum registro encontrado!</td></tr>';
                }

                foreach ($equipmentArray as $equipment) {
                    $shortDescription = '<span style="color: CadetBlue;">(Equipamento não encontrado)</span>';
                    $sla = "";
                    $comments = ""; 
                    if ($equipment != null) {
                        $shortDescription = '<span style="color: CadetBlue;">'.EquipmentDAO::GetShortDescription($equipment).'</span>';
                        if (!empty($equipment->sla)) $sla = $equipment->sla.' horas';
                        if (!empty($equipment->comments)) $comments = $equipment->comments;
                    }
                    ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value= "<?php echo $equipment->insID; ?>" name="reg[]"/>
                        </td>
                        <td >
                           <a href="Frontend/<?php echo $currentDir; ?>/editar.php?equipmentCode=<?php echo $equipment->insID; ?>" >
                               <?php echo $shortDescription; ?>
                           </a>
                        </td>
                        <td style="max-width:250px;" >
                            <?php echo $comments; ?>
                        </td>
                        <td>
                            <?php echo $sla; ?>
                        </td>
                        <td >
                            <a href="Frontend/_leitura/listar.php?equipmentCode=<?php echo $equipment->insID.'&subContract=0'; ?>" >
                                <span class="ui-icon ui-icon-alert"></span>
                            </a>
                        </td>
                        <td >
                            <a href="Frontend/chamados/listar.php?equipmentCode=<?php echo $equipment->insID.'&subContract=0'; ?>" >
                                <span class="ui-icon ui-icon-alert"></span>
                            </a>
                        </td>
                        <td>
                            <a href="Frontend/_consumivel/listar.php?equipmentCode=<?php echo $equipment->insID.'&subContract=0'; ?>" >
                                <span class="ui-icon ui-icon-alert"></span>
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

        <label><h3>Localizar outros (Exemplo: equipamentos devolvidos ou encerrados)</h3><br/>
        <input name="serial" style="height:25px;" value="" />
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
