<?php

// Dialogo com os filtros do relatório de chamados técnicos
// Recebe como parâmetros a url do relatório e filtros complementares

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/UnixTime.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ServiceCallDAO.php");
include_once("../DataTransferObjects/ServiceCallDTO.php");
include_once("../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../DataAccessObjects/EquipmentModelDAO.php");
include_once("../DataTransferObjects/EquipmentModelDTO.php");
include_once("../DataAccessObjects/ManufacturerDAO.php");
include_once("../DataTransferObjects/ManufacturerDTO.php");
include_once("../DataAccessObjects/EmployeeDAO.php");
include_once("../DataTransferObjects/EmployeeDTO.php");


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

// Cria os objetos de mapeamento objeto-relacional
$businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
$businessPartnerDAO->showErrors = 1;
$equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
$equipmentModelDAO->showErrors = 1;
$manufacturerDAO = new ManufacturerDAO($dataConnector->sqlserverConnection);
$manufacturerDAO->showErrors = 1;
$employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
$employeeDAO->showErrors = 1;

// Recupera os clientes cadastrados no sistema
$clientArray = $businessPartnerDAO->RetrieveRecordArray("CardName IS NOT NULL ORDER BY CardName");

// Recupera os modelos cadastrados no sistema
$modelArray = $equipmentModelDAO->RetrieveRecordArray("id > 0 ORDER BY modelo");

// Busca os fabricantes cadastrados no sistema
$manufacturerArray = array();
$tempArray = $manufacturerDAO->RetrieveRecordArray();
foreach ($tempArray as $manufacturer) {
    $manufacturerArray[$manufacturer->FirmCode] = $manufacturer->FirmName;
}

// Recupera os técnicos cadastrados no sistema
$employeeArray = $employeeDAO->RetrieveEmployeesByPosition('Técnico', 'Tecnico');

?>

<form name="fDados" >
    <input type="hidden" name="reportUrl" value="<?php echo $reportUrl; ?>" />
    <input type="hidden" name="parameters" value="<?php echo $parameters; ?>" />

    <label class="left" style="width:99%; text-align: left; <?php echo ($searchMethod != 1) ? "display:inline;" : "display:none;" ?>">Cliente<br/>
    <select name="businessPartnerCode" style="width: 98%;" >
        <option value=0 >-- Todos --</option>
        <?php
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
    <select name="model" style="width: 98%;" >
        <option value=0 >-- Todos --</option>
        <?php
            foreach($modelArray as $model) {
                $spacing = "&nbsp;&nbsp;&nbsp;";
                $tag = $model->modelo.$spacing.$spacing." (".$manufacturerArray[$model->fabricante].")";
                echo "<option value=".$model->id." alt=".$model->modelo." >".$tag."</option>";
            }
        ?>
    </select>
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
        <input class="datepick" type="text" name="dataInicial" style="width:95%;height:25px;" value="<?php echo date("Y-m-d", $currentDate->AddMonths(-1)); ?>" ></input>
    </label>
    <label class="left" style="width:45%; text-align: left;">Data Final<br/>
        <input class="datepick" type="text" name="dataFinal" style="width:95%;height:25px;" value="<?php echo date("Y-m-d", $currentDate->value); ?>" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left;">Técnico<br/>
    <select name="tecnico" style="width: 350px;">
        <option value=0 >-- Todos --</option>
        <?php
            foreach($employeeArray as $employee)
            {
                $employeeName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
                echo "<option value=".$employee->empID.">".$employeeName."</option>";
            }
        ?>
    </select>
    </label>
    <div style="clear:both;">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left;">Status<br/>
    <select name="status" style="width: 350px;">
        <option value=0 >-- Todos --</option>
        <?php
            $statusArray = ServiceCallDAO::RetrieveServiceCallStatuses($dataConnector->sqlserverConnection);
            foreach($statusArray as $key=>$value)
            {
                echo "<option value=".$key.">".$value."</option>";
            }
        ?>
    </select>
    </label>
    <div style="clear:both;">
        <br/>
    </div>
    <label class="left" style="width:99%; text-align: left;">Ordenar por<br/>
    <select name="ordenarPor" style="width: 350px;">
        <option value="dataAbertura" >Data de abertura</option>
        <option value="id" >Número do Chamado</option>
        <option value="businessPartnerCode" >Cliente</option>
        <option value="dataAtendimento" >Data de Atendimento</option>
        <option value="tecnico" >Técnico</option>
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
    // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
    $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

    function GetEquipmentOptions() {
        var businessPartnerCode = $("select[name=businessPartnerCode]").val(); // traz o valor selecionado no combo
        var targetUrl = 'AjaxCalls/GetEquipmentOptions.php?businessPartnerCode=' + businessPartnerCode + '&equipmentCode=0';
        $.get(targetUrl, function(response){ if (businessPartnerCode != 0) ReloadEquipments(response); });
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
        var model = $("select[name=model]").val();
        var modelName = $("select[name=model] option:selected").attr("alt");
        var equipmentCode = $("select[name=equipmentCode]").val();
        var startDate = $("input[name=dataInicial]").val();
        var endDate = $("input[name=dataFinal]").val();
        var technician = $("select[name=tecnico]").val();
        var status = $("select[name=status]").val();
        var orderBy = $("select[name=ordenarPor]").val();
        var parameters = '?businessPartnerCode=' + businessPartnerCode + '&model=' + model + '&modelName=' + modelName + '&equipmentCode=' + equipmentCode + '&startDate=' + startDate + '&endDate=' + endDate + '&technician=' + technician + '&status=' + status + '&orderBy=' + orderBy;
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
