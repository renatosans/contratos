<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");
include_once("../../DataAccessObjects/ServiceCallDAO.php");
include_once("../../DataTransferObjects/ServiceCallDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/ServiceStatisticsDAO.php");
include_once("../../DataTransferObjects/ServiceStatisticsDTO.php");


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

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoChamados"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
$serviceCallDAO->showErrors = 1;
$businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
$businessPartnerDAO->showErrors = 1;

// Traz a lista de chamados cadastrados
$maxRecordCount = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "limiteListaChamados");
$recCount = $serviceCallDAO->GetRecordCount(null);
$boundary = ""; if (($recCount > $maxRecordCount) && empty($equipmentCode)) $boundary = "LIMIT ".($recCount - $maxRecordCount).", ".$recCount;

$filter = "id > 0 ORDER BY id";
if ($equipmentCode != 0) $filter = "cartaoEquipamento = ".$equipmentCode;
$serviceCallArray = $serviceCallDAO->RetrieveRecordArray($filter.' '.$boundary);


$extraInfo = "( últimos ".$maxRecordCount." )";
if (!empty($equipmentCode)) { 
    $extraInfo = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $equipmentCode);
}

?>

    <h1>Chamados de Serviço <?php echo $extraInfo; ?></h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >
    
        $(document).ready(function() {
            $("#btnExcluirChamados").button({ icons: {primary:'ui-icon-circle-minus'} }).click( function() {
                var checkedCount = 0;
                var regArray = new Array();
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) { checkedCount++; regArray.push($(this).val()); }
                });
                if (checkedCount == 0) {
                    alert('Marque os chamados que deseja excluir.');
                    return;
                }
                if (!confirm("Deseja realmente excluir os chamados ?")) {
                    return;
                }

                // Faz um chamada sincrona a página de exclusão
                var targetUrl = 'Frontend/chamados/acao.php?acao=remove';
                var callParameters = {'reg[]': regArray};
                $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

                // Recarrega a página
                LoadPage('Frontend/chamados/listar.php');
            });

            $("#btnImprimirChamados").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                var checkedCount = 0;
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) {
                        checkedCount++;
                        var targetUrl = 'Frontend/chamados/imprimir.php?serviceCallId=000';
                        targetUrl = targetUrl.replace("000", $(this).val());
                        window.open(targetUrl);
                    }
                });
                if (checkedCount == 0) {
                    alert('Marque os chamados que deverão ser impressos.');
                }
                return false;
            });

            $("#btnFind").button({ icons: {primary:'ui-icon-search'} }).click( function() {
                var serviceCallId = $("input[name=serviceCallId]").val();
                if ((!serviceCallId) || isNaN(serviceCallId)) {
                    alert('Preencher o número do chamado!');
                    return;
                }

                LoadPage('Frontend/chamados/editar.php?id=' + serviceCallId);
            });
        });

    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
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
                <button type="button" <?php echo $attributes; ?> id="btnExcluirChamados" >
                    Excluir
                </button>  
                <button type="button" <?php echo $attributes; ?> id="btnImprimirChamados" >
                    Imprimir
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="45" size="45" type="text"/>
                    <button id="filter-clear-button" type="submit" value="Clear">Clear</button>
                </fieldset>
            </div>
        </div>
        <br/>
        <input type="hidden" name="acao" value="remove" />
        <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;Chamado</th>
                    <th>&nbsp;Defeito</th>
                    <th>&nbsp;Parceiro de Negócios</th>
                    <th>&nbsp;Data de Abertura</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (sizeof($serviceCallArray) == 0){
                    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($serviceCallArray as $serviceCall) {
                    $subject = new Text($serviceCall->defeito);
                    // Busca os dados do cliente
                    $clientName = new Text("-");
                    $businessPartner = $businessPartnerDAO->RetrieveRecord($serviceCall->businessPartnerCode);
                    if ($businessPartner != null) {
                        $informacaoAdicional = "";
                        if ($businessPartner->cardName != $businessPartner->cardFName) $informacaoAdicional = " (".$businessPartner->cardFName.")";
                        $clientName = new Text($businessPartner->cardName.$informacaoAdicional);
                    }
                ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value= "<?php echo $serviceCall->id; ?>" name="reg[]" />
                        </td>
                        <td >
                           <a href="Frontend/<?php echo $currentDir; ?>/editar.php?id=<?php echo $serviceCall->id; ?>" >
                               <?php echo str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT); ?>
                           </a>
                        </td>
                        <td >
                           <a href="Frontend/<?php echo $currentDir; ?>/editar.php?id=<?php echo $serviceCall->id; ?>" >
                               <?php echo $subject->Truncate(34); ?>
                           </a>
                        </td>
                        <td >
                           <?php echo $clientName->Truncate(35); ?>
                        </td>
                        <td >
                           <?php echo $serviceCall->dataAbertura; ?>
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

        <?php
            $filter = "YEAR(dataAbertura) = YEAR(NOW()) AND MONTH(dataAbertura) = MONTH(NOW())";
            $quantChamados = $serviceCallDAO->GetRecordCount($filter);
            echo '<h3 style="color:darkorange;" >Quantidade de Chamados este mês: '.$quantChamados.'</h3><br/>';
            $filter = "YEAR(dataAbertura) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND MONTH(dataAbertura) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))";
            $quantChamados = $serviceCallDAO->GetRecordCount($filter);
            echo '<h3 style="color:darkorange;" >Quantidade de Chamados mês passado: '.$quantChamados.'</h3><br/>';


            // Grava as estatísticas de atendimento
            $serviceStatisticsDAO = new ServiceStatisticsDAO($dataConnector->mysqlConnection);
            $serviceStatisticsDAO->showErrors = 1;

            $stats = $serviceStatisticsDAO->GetLastMonthStatistics();
            if (!isset($stats)) // Registro não existe, cria pela primeira vez
            {
                $lastMonth = mktime(0,0,0,date("m")-1,1,date("Y"));
                $stats = new ServiceStatisticsDTO();
                $stats->mesReferencia = date("m", $lastMonth);
                $stats->anoReferencia = date("Y", $lastMonth);
                $serviceStatisticsDAO->StoreRecord($stats);
                $stats = $serviceStatisticsDAO->GetLastMonthStatistics();
            }
            $totals = $serviceStatisticsDAO->GetServiceTotals();
            if (sizeof($totals) == 2)
            {
                $stats->quantidadeChamados = $totals[0];
                $stats->tempoEmAtendimento = $totals[1];
                $serviceStatisticsDAO->StoreRecord($stats);  // Atualiza as estatísticas
            }
        ?>

        <!-- Só exibe o botão localizar quando está listando todos os chamados -->
        <div style="display: <?php echo empty($equipmentCode) ?  'inline;' : 'none;'; ?>">
            <label><h3>Localizar outros (Exemplo: chamado fora da listagem )</h3><br/>
            <input name="serviceCallId" style="height:25px;" value="" />
            <button type="button" id="btnFind" style="height: 30px;" >Localizar</button>
            </label>
            <div style="clear:both;">
                <br/><br/>
            </div>
        </div>

        <!-- Só exibe o botão voltar quando navegou vindo da listagem de equipamentos ou do subcontrato -->
        <div style="display: <?php echo empty($equipmentCode) ?  'none;' : 'inline;'; ?>">
            <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
                Voltar
            </a>
        </div>
    </form>
<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
