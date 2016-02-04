<?php

// Dialogo com os filtros do relatório de contratos
// Recebe como parâmetros a url do relatório e filtros complementares

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/UnixTime.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../DataAccessObjects/ContractDAO.php");
include_once("../DataTransferObjects/ContractDTO.php");
include_once("../DataAccessObjects/ContractTypeDAO.php");
include_once("../DataTransferObjects/ContractTypeDTO.php");

// Obtem a url do relatório e seus filtros
$reportUrl = $_POST['reportUrl'];
$parameters = $_POST['parameters'];
parse_str($parameters, $paramsArray);
$searchMethod = $paramsArray['searchMethod'];

$currentDate = new UnixTime(time());

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
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
    <label class="left" style="width:45%; text-align: left;">Encerramento de<br/>
        <input class="datepick" type="text" name="dataInicial" style="width:95%;height:25px;" value="<?php echo date("Y-m-d", $currentDate->AddMonths(-36)); ?>" ></input>
    </label>
    <label class="left" style="width:45%; text-align: left;">Encerramento até<br/>
        <input class="datepick" type="text" name="dataFinal" style="width:95%;height:25px;" value="<?php echo date("Y-m-d", $currentDate->AddMonths(+36)); ?>" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left;">Tipo do contrato<br/>
    <select name="contractType" style="width: 98%;" >
        <option value=0 >-- Todos --</option>
        <?php
            $contractTypeDAO = new ContractTypeDAO($dataConnector->mysqlConnection);
            $contractTypeDAO->showErrors = 1;
            $contractTypes = $contractTypeDAO->RetrieveRecordArray();
            foreach ($contractTypes as $type) {
                echo '<option value='.$type->id.' >'.$type->nome.'</option>';
            }
        ?>
    </select>
    </label>
    <div style="clear:both;">
        <br/>
    </div>
    <table class="slimTable" style="width:99%"; float:"left"; >
    <tr><th class="slimTableHeader" ><input type="checkbox" id="selectall" /></th><th class="slimTableHeader" >Status do contrato</th></tr>
    <?php
        $contractStatuses = ContractDAO::GetStatusArray();
        foreach ($contractStatuses as $id => $status) {
            $selectionBox = '<input type="checkbox" class="contractStatus" name="contractStatus" value="'.$id.'" />';
            echo '<tr><td class="slimTableData" align="center">'.$selectionBox.'</td><td class="slimTableData" >'.$status.'</td></tr>';
        }
    ?>
    </table>
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
    // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
    $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});


    // add multiple select / deselect functionality
    $("#selectall").click(function () {
          $('.contractStatus').attr('checked', this.checked);
    });

    // if all checkbox are selected, check the selectall checkbox
    // and viceversa
    $(".contractStatus").click(function(){
 
        if($(".contractStatus").length == $(".contractStatus:checked").length) {
            $("#selectall").attr("checked", "checked");
        } else {
            $("#selectall").removeAttr("checked");
        }
 
    });

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
        var contractType = $("select[name=contractType]").val();
        var contractStatus = '';
        $("input[name=contractStatus]").each( function() {
            if ($(this).is(":checked")) {
                if (contractStatus) contractStatus = contractStatus + ",";
                contractStatus = contractStatus + $(this).val();
            }
        });
        var parameters = '?businessPartnerCode=' + businessPartnerCode + '&model=' + model + '&equipmentCode=' + equipmentCode + '&startDate=' + startDate + '&endDate=' + endDate + '&contractType=' + contractType + '&contractStatus=' + contractStatus;
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
