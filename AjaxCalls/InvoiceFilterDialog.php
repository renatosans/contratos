<?php

// Dialogo com os filtros do relatório de Notas Fiscais
// Recebe como parâmetros a url do relatório e filtros complementares

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/UnixTime.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../DataAccessObjects/SalesPersonDAO.php");
include_once("../DataTransferObjects/SalesPersonDTO.php");

// Obtem a url do relatório e seus filtros
$reportUrl = $_POST['reportUrl'];
$parameters = $_POST['parameters'];
parse_str($parameters, $paramsArray);
$searchMethod = $paramsArray['searchMethod'];

$currentDate = new UnixTime(time());

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('sqlServer');
$dataConnector->OpenConnection();
if ($dataConnector->sqlserverConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

?>

<form name="fDados" >
    <input type="hidden" name="reportUrl" value="<?php echo $reportUrl; ?>" />
    <input type="hidden" name="parameters" value="<?php echo $parameters; ?>" />

    <label class="left" style="width:99%; text-align: left; <?php echo ($searchMethod != 1) ? "display:inline;" : "display:none;" ?>">Cliente<br/>
    <select name="businessPartnerCode" style="width: 98%;" >
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
    </label>
    <div style="clear:both; <?php echo ($searchMethod != 1) ? "display:inline;" : "display:none;" ?>">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left; <?php echo (($searchMethod == 1) || ($searchMethod == 2)) ? "display:inline;" : "display:none;" ?>">Modelo<br/>
        <input type="text" name="model" style="width:98%;height:25px;" value="" ></input>
    </label>
    <div style="clear:both; <?php echo (($searchMethod == 1) || ($searchMethod == 2)) ? "display:inline;" : "display:none;" ?>">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left; <?php echo ($searchMethod == 3) ? "display:inline;" : "display:none;" ?>">Equipamento<br/>
        <select name="equipmentCode" style="width: 98%;" ></select>
    </label>
    <div style="clear:both; <?php echo ($searchMethod == 3) ? "display:inline;" : "display:none;" ?>">
        <br/>
    </div>
    <label class="left" style="width:45%; text-align: left;">Data Inicial<br/>
        <input class="datepick" type="text" name="dataInicial" style="width:95%;height:25px;" value="<?php echo date("d/m/Y", $currentDate->AddMonths(-1)); ?>" ></input>
    </label>
    <label class="left" style="width:45%; text-align: left;">Data Final<br/>
        <input class="datepick" type="text" name="dataFinal" style="width:95%;height:25px;" value="<?php echo date("d/m/Y", $currentDate->value); ?>" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left;">Vendedor<br/>
    <select name="salesPerson" style="width: 350px;">
        <option value=0 >-- Todos --</option>
        <?php
            $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
            $salesPersonDAO->showErrors = 1;
            $salesPersonArray = $salesPersonDAO->RetrieveRecordArray("SlpCode > 0");
            foreach($salesPersonArray as $salesPerson)
            {
                echo "<option value=".$salesPerson->slpCode.">".$salesPerson->slpName."</option>";
            }
        ?>
    </select>
    </label>
    <div style="clear:both;">
        <br/>
    </div>

    <div class="left" style="width:99%; text-align: center;">
        <input id="btnOK" type="button" value="OK" style="width:50px; height:30px;"></input>
    </div>
</form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>

<script type="text/javascript" >
    // Seta o formato de data do datepicker para manter compatibilidade com o formato do SQL Server
    $('.datepick').datepicker({dateFormat: 'dd/mm/yy'});

    function GetEquipmentOptions() {
        var businessPartnerCode = $("select[name=businessPartnerCode]").val(); // traz o valor selecionado no combo
        var targetUrl = 'AjaxCalls/GetEquipmentOptions.php?businessPartnerCode=' + businessPartnerCode + '&equipmentCode=0';
        $.get(targetUrl, function(response){ ReloadEquipments(response); });
    }

    function ReloadEquipments(options)
    {
        $("select[name=equipmentCode]").empty();
        $("select[name=equipmentCode]").append(options);
    }

    function OkButtonClicked() {
        var reportUrl = $("input[name=reportUrl]").val();
        var additionalParameters = $("input[name=parameters]").val();
        var businessPartnerCode = $("select[name=businessPartnerCode]").val();
        var model = $("input[name=model]").val();
        var equipmentCode = $("select[name=equipmentCode]").val();
        var startDate = $("input[name=dataInicial]").val();
        var endDate = $("input[name=dataFinal]").val();
        var salesPerson = $("select[name=salesPerson]").val();
        var parameters = '?businessPartnerCode=' + businessPartnerCode + '&model=' + model + '&equipmentCode=' + equipmentCode + '&startDate=' + startDate + '&endDate=' + endDate + '&salesPerson=' + salesPerson;
        if (additionalParameters[0] == '&') parameters = parameters + additionalParameters;

        // Abre a página de relatórios em outra janela
        window.open(reportUrl + parameters);

        // Fecha o dialogo
        $("#popup").dialog('close');
    }

    $("select[name=businessPartnerCode]").change(function() { GetEquipmentOptions(); });
    GetEquipmentOptions();

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
