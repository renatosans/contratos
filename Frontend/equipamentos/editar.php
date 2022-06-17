<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/AccessoriesDAO.php");
include_once("../../DataTransferObjects/AccessoriesDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../../DataAccessObjects/EmployeeDAO.php");
include_once("../../DataTransferObjects/EmployeeDTO.php");
include_once("../../DataAccessObjects/SalesPersonDAO.php");
include_once("../../DataTransferObjects/SalesPersonDTO.php");


$equipmentCode = 0;
if (isset($_REQUEST["equipmentCode"]) && ($_REQUEST["equipmentCode"] != 0)) {
    $equipmentCode = $_REQUEST["equipmentCode"];
}
$serial = '';
if (isset($_REQUEST["serial"]) && ($_REQUEST["serial"] != '')) {
    $serial = $_REQUEST["serial"];
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

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoEquipmtPecas"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
$equipmentDAO->showErrors = 1;

$equipment = new EquipmentDTO();
$equipmentArray = array(); // Histórico do equipamento (cartões de equipamento com este número de série)
if ($equipmentCode != 0) {
    $equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
    $equipmentArray = $equipmentDAO->RetrieveRecordArray("manufSN='".$equipment->manufacturerSN."'");
}
if ($serial != '') {
    $serial = urldecode($serial);
    $equipmentArray = $equipmentDAO->RetrieveRecordArray("manufSN='".$serial."'");
    if (sizeof($equipmentArray) > 0) {
        $equipment = $equipmentArray[0];
        $equipmentCode = $equipment->insID;
    }
}

$equipmentHistory = "";
foreach ($equipmentArray as $equipmentCard) {
    $equipmentHistory = $equipmentHistory.'<a href="Frontend/equipamentos/editar.php?equipmentCode='.$equipmentCard->insID.'">'.$equipmentCard->custmrName.'</a><br/>';
}


if ($equipment->insID == 0) {
    echo '<br/><h1>Equipamento não encontrado</h1><br/>';
    exit;
}

$clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $equipment->customer));
$address = $equipment->addressType." ".$equipment->street." ".$equipment->streetNo." ".$equipment->building."   CEP: ".$equipment->zip;
$address = $address."   "."Bairro: ".$equipment->block."   ".$equipment->city." ".$equipment->state." ".$equipment->country;

function GetAccessories($equipmentCode){
    global $dataConnector;
    $accessoryList = '';

    $accessoriesDAO = new AccessoriesDAO($dataConnector->mysqlConnection);
    $accessoriesDAO->showErrors = 1;
    $accessoriesArray = $accessoriesDAO->RetrieveRecordArray("equipamento=".$equipmentCode);
    foreach ($accessoriesArray as $accessory) {
        if (!empty($accessoryList)) $accessoryList = $accessoryList.', ';
        $accessoryList = $accessoryList.$accessory->quantidade.' '.$accessory->descricaoItem;
    }

    return $accessoryList;
}

?>

    <h1>Cartão de Equipamento (<?php echo EquipmentDAO::GetStatusDescription($equipment->status); ?>)</h1><br/>
    <h1><?php echo str_pad('_', 52, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function GetEquipModelOptions() {
            var targetUrl = 'AjaxCalls/GetEquipModelOptions.php?modelId=<?php echo $equipment->model; ?>';
            $("select[name=model]").load(targetUrl);
        }

        $(document).ready(function() {
            $("#btnAddModel").button({icons: { primary:'ui-icon-circle-plus' }}).click( function() {
                var targetUrl = 'AjaxCalls/AddEquipmentModel.php';
                $("form[name=fDados]").append("<div id='addDialog'></div>");
                $("#addDialog").load(targetUrl).dialog({modal:true, width: 320, height: 280, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });

            $("#btnRemoveModel").button({icons: { primary:'ui-icon-circle-minus' }}).click( function() {
                if(!confirm("Confirma exclusão?")) return false;

                // Faz um chamada sincrona a página que exclui o modelo
                var modelId = $("select[name=model]").val();
                var targetUrl = 'Frontend/equipamentos/acaoModelo.php?acao=remove&id=' + modelId;
                $.ajax({ type: 'POST', url: targetUrl, success: function(response) { alert(response); }, async: false });

                // Recarrega a lista de modelos de equipamento
                GetEquipModelOptions();
            });

            // Seta o formato de data do datepicker para manter compatibilidade com o formato do SQL Server
            $('.datepick').datepicker({dateFormat: 'dd/mm/yy'});

            // Carrega a lista de modelos de equipamento
            GetEquipModelOptions();
        });

    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="equipmentCode" value="<?php echo $equipmentCode; ?>" />

        <?php
            $btnAttributes = '';
            if ($nivelAutorizacao < 3) $btnAttributes = 'disabled="disabled"';
        ?>

        <fieldset style="width:650px;">
            <legend>Dados</legend>
            Número série Fabricante: <?php echo $equipment->manufacturerSN; ?><br/><br/>
            Nosso número de série: <?php echo $equipment->internalSN; ?><br/><br/>
            Descrição do equipamento: <?php echo $equipment->itemName; ?><br/><br/>
            Cliente(Business Partner): <span style="color:darkorange;font-weight:bold;" ><?php echo $clientName->Truncate(60); ?></span><br/><br/>
            Endereço: <?php echo $address; ?><br/><br/>
            Local de instalação: <?php echo $equipment->instLocation; ?><br/><br/>
            Acessórios: <?php echo GetAccessories($equipment->insID); ?><br/><br/>
            Histórico: <br/><?php echo $equipmentHistory; ?><br/><br/>

            <div style="<?php echo (($equipment->status == 'A') || ($equipment->status == 'L')) ? "display:inline;" : "display:none;" ?>" >
                <a href="Frontend/_leitura/listar.php?equipmentCode=<?php echo $equipment->insID; ?>" >
                    <span class="ui-icon ui-icon-alert" style="display:inline-block;" ></span> LEITURAS
                </a><br/><br/>
                <a href="Frontend/chamados/listar.php?equipmentCode=<?php echo $equipment->insID; ?>" >
                    <span class="ui-icon ui-icon-alert" style="display:inline-block;" ></span> CHAMADOS
                </a><br/><br/>
                <a href="Frontend/_consumivel/listar.php?equipmentCode=<?php echo $equipment->insID; ?>" >
                    <span class="ui-icon ui-icon-alert" style="display:inline-block;" ></span> CONSUMÍVEIS
                </a><br/><br/>
            </div>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Data de Instalação<br/>
        <input class="datepick" type="text" name="installationDate" size="65" value="<?php echo empty($equipment->installationDate) ? '' : $equipment->installationDate->format('d/m/Y'); ?>" />
        </label>

        <label style="float:left;" >Número da NF de Remessa<br/>
        <input type="text" name="installationDocNum" size="40" value="<?php echo $equipment->installationDocNum; ?>" />
        </label>

        <label style="float:left;" >Contador Inicial (Pb)<br/>
        <input type="text" name="counterInitialVal" size="40" value="<?php echo $equipment->counterInitialVal; ?>" />
        </label>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <label>Data de Devolução (Remoção do equipamento)<br/>
        <input class="datepick" type="text" name="removalDate" size="65" value="<?php echo empty($equipment->removalDate) ? '' : $equipment->removalDate->format('d/m/Y'); ?>" />
        </label>

        <label style="float:left;" >Número da NF de Retorno<br/>
        <input type="text" name="removalDocNum" size="40" value="<?php echo $equipment->removalDocNum; ?>" />
        </label>

        <label style="float:left;" >Contador Final (Pb)<br/>
        <input type="text" name="counterFinalVal" size="40" value="<?php echo $equipment->counterFinalVal; ?>" />
        </label>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <label class="left">Responsável Técnico<br/>
            <select name="technician" style="width: 350px;">
            <?php
                //SELECT OHEM.empID id, (OHEM.firstName + ' ' + OHEM.lastName) nome FROM HEM6
                //JOIN OHEM ON HEM6.empID = OHEM.empID
                //JOIN OHTY ON HEM6.roleID = OHTY.typeID WHERE roleID = -2   -- Técnico

                echo '<option value="0" >-Nenhum técnico-</option>';
                $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
                $employeeDAO->showErrors = 1;
                $employeeArray = $employeeDAO->RetrieveEmployeesByPosition('Técnico', 'Tecnico');
                foreach($employeeArray as $employee)
                {
                    $attributes = "";
                    if ($employee->empID == $equipment->technician) $attributes = "selected='selected'";
                    $employeeName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
                    echo "<option ".$attributes." value=".$employee->empID.">".$employeeName."</option>";
                }
            ?>
            </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Vendedor<br/>
        <select name="salesPerson" style="width: 350px;">
            <?php
                $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
                $salesPersonDAO->showErrors = 1;
                $salesPersonArray = $salesPersonDAO->RetrieveRecordArray();
                foreach($salesPersonArray as $salesPerson)
                {
                    $attributes = "";
                    if ($salesPerson->slpCode == $equipment->salesPerson) $attributes = "selected='selected'";
                    echo "<option ".$attributes." value=".$salesPerson->slpCode.">".$salesPerson->slpName."</option>";
                }
            ?>
        </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Modelo<br/>
            <select name="model" style="width: 350px;"></select>
            <button type="button" <?php echo $btnAttributes; ?> id="btnAddModel" style="margin-left:8px; width:30px; height:30px;" title="Adicionar" />
            <button type="button" <?php echo $btnAttributes; ?> id="btnRemoveModel" style="margin-left:2px; width:30px; height:30px;" title="Remover" />
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Capacidade do Equipamento (Mensal)<br/>
        <input type="text" name="capacity" size="65" value="<?php echo $equipment->capacity; ?>" />
        </label>

        <label>Nível de Serviço (SLA)<br/>
        <input type="text" name="sla" size="65" maxlength="100" value="<?php echo $equipment->sla; ?>" />
        </label>

        <label>Observações<br/>
            <textarea name="comments" style="width:460px;height:50px;" ><?php echo $equipment->comments; ?></textarea>
        </label>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <button type="submit" <?php echo $btnAttributes; ?> class="button" id="btnform">
            Salvar
        </button>
    </form>

<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
