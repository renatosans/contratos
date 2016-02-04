<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ReadingDAO.php");
include_once("../../DataTransferObjects/ReadingDTO.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");


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

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoLeituras"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
$readingDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Traz a lista de leituras cadastradas
$readingArray = $readingDAO->RetrieveRecordArray("codigoCartaoEquipamento=".$equipmentCode);
$readingSourceArray = $readingDAO->RetrieveReadingSources();
$readingKindArray = $readingDAO->RetrieveReadingKinds();

$equipmentInfo = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $equipmentCode);

?>

    <h1>Administração - Leituras <?php echo $equipmentInfo; ?></h1><br/>
    <h1><?php echo str_pad('_', 52, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function GetEquipmentOptions()
        {
            var businessPartnerCode = $("select[name=businessPartnerCode]").val(); // traz o valor selecionado no combo
            var targetUrl = 'AjaxCalls/GetEquipmentOptions.php?businessPartnerCode=' + businessPartnerCode + '&equipmentCode=0';
            $.get(targetUrl, function(response){ ReloadEquipments(response); });
        }

        function ReloadEquipments(options)
        {
            $("select[name=equipmentCode]").empty();
            $("select[name=equipmentCode]").append(options);
            $("select[name=equipmentCode]").trigger("change");
        }

        $(document).ready(function() {
            $("#btnSelectEquipment").button({ icons: {primary:'ui-icon-check' } }).click(function() {
                var equipmentCode = $("select[name=equipmentCode]").val();
                LoadPage('Frontend/_leitura/listar.php?equipmentCode=' + equipmentCode);
            });

            $("select[name=businessPartnerCode]").change(function() { GetEquipmentOptions(); });
            GetEquipmentOptions();
        });
    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >

        <!-- Exibe a caixa de escolha de equipamento quando o equipmentCode não foi informado -->
        <fieldset style="<?php echo $equipmentCode == 0 ? "display:inline;" : "display:none;" ?>" >
            <legend>Equipamento:</legend>
            <select name="businessPartnerCode" style="float:left; width: 250px;" >
            <?php
                $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
                $businessPartnerDAO->showErrors = 1;
                $clientArray = $businessPartnerDAO->RetrieveRecordArray("CardName IS NOT NULL ORDER BY CardName");
                foreach ($clientArray as $client) {
                    $informacaoAdicional = "";
                    if ($client->cardName != $client->cardFName) $informacaoAdicional = " (".$client->cardFName.")";
                    $clientInfo = new Text($client->cardName.$informacaoAdicional);
                    echo "<option value=".$client->cardCode.">".$clientInfo->Truncate(85)."</option>";
                }
            ?>
            </select>
            <select name="equipmentCode" style="float:left; margin-left:8px; width: 250px;" ></select>
            <button type="button" id="btnSelectEquipment" style="float:left; margin-left:8px;" >
                Selecionar
            </button>
        </fieldset>
        <div style="<?php echo $equipmentCode == 0 ? "display:inline;" : "display:none;" ?>" >
            <div style="clear:both;">
                <br/><br/>
            </div>
        </div>

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
                    $url = 'Frontend/'.$currentDir.'/editar.php?id=0&equipmentCode='.$equipmentCode.'&subContract='.$subContract;
                    if ($nivelAutorizacao < 3) {
                        $attributes = 'disabled="disabled"';
                        $url = '#';
                    }
                ?>
                <a <?php echo $attributes; ?> href="<?php echo $url; ?>" class="button">
                    Novo
                </a>
                <button type="submit" <?php echo $attributes; ?> id="btnExcluir" class="button">
                    Excluir
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
        <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;Data</th>
                    <th>&nbsp;Contador(Tipo)</th>
                    <th>&nbsp;Contagem(Leitura)</th>
                    <th>&nbsp;Origem</th>
                    <th>&nbsp;Forma de Leitura</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($readingArray) == 0){
                    echo '<tr><td colspan="6" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($readingArray as $reading) {
                    $counter = $counterDAO->RetrieveRecord($reading->codigoContador); 
            ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value= "<?php echo $reading->id; ?>" name="reg[]"/>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$reading->id.'&equipmentCode='.$equipmentCode.'&subContract='.$subContract; ?>" >
                                <?php echo $reading->data; ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$reading->id.'&equipmentCode='.$equipmentCode.'&subContract='.$subContract; ?>" >
                                <?php echo $counter->nome; ?>
                            </a>
                        </td>
                        <td>
                            <?php echo $reading->contagem; ?>
                        </td>
                        <td>
                            <?php echo $readingSourceArray[$reading->origemLeitura]; ?>
                        </td>
                        <td>
                            <?php echo $readingKindArray[$reading->formaLeitura]; ?>
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

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>
    </form>
<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
