<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ContractItemDAO.php");
include_once("../DataTransferObjects/ContractItemDTO.php");
include_once("../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../DataAccessObjects/ContractDAO.php");
include_once("../DataTransferObjects/ContractDTO.php");
include_once("../DataAccessObjects/SubContractDAO.php");
include_once("../DataTransferObjects/SubContractDTO.php");


$subContractId = 0;
if (isset($_REQUEST["subContractId"]) && ($_REQUEST["subContractId"] != 0)) {
    $subContractId = $_REQUEST["subContractId"];
}


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$contractItem = new ContractItemDTO();

// Busca o código do Parceiro de Negócios
$businessPartnerCode = "";
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;
$subContract = $subContractDAO->RetrieveRecord($subContractId);
if ($subContract != null) {
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $contract = $contractDAO->RetrieveRecord($subContract->codigoContrato);
    $businessPartnerCode = $contract->pn;
}


?>

    <script type="text/javascript" >
        function GetEquipmentInfo() {
            var equipmentCode = $("select[name=equipmentCode]").val();
            if (!equipmentCode) equipmentCode = 0;
        
            var targetUrl = "AjaxCalls/GetEquipmentInfo.php?equipmentCode=" + equipmentCode;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 560, height: 340, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

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
            $("#btnEquipmentInfo").click( function() { GetEquipmentInfo(); });
            $("select[name=businessPartnerCode]").change(function() { GetEquipmentOptions(); });
            GetEquipmentOptions(); // Carrega seleção inicial
        });    
    </script>

    <form name="fDados" action="Frontend/contrato/sub-contrato/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="contractId" value="<?php echo $contract->id; ?>" />
        <input type="hidden" name="subContractId" value="<?php echo $subContractId; ?>" />


        <label class="left" style="width: 99%;" >Parceiro de Negócio<br/>
        <select name="businessPartnerCode" style="width: 98%;" >
            <?php
                $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
                $businessPartnerDAO->showErrors = 1;
                $clientArray = $businessPartnerDAO->RetrieveRecordArray("CardName IS NOT NULL ORDER BY CardName");
                foreach ($clientArray as $client) {
                    $attributes = "";
                    if ($client->cardCode == $businessPartnerCode) $attributes = "selected='selected'";
                    $informacaoAdicional = "";
                    if ($client->cardName != $client->cardFName) $informacaoAdicional = " (".$client->cardFName.")";
                    $clientInfo = new Text($client->cardName.$informacaoAdicional);
                    echo "<option ".$attributes." value=".$client->cardCode.">".$clientInfo->Truncate(85)."</option>";
                }
            ?>
        </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" style="width: 99%;">Equipamento<br/>
        <select name="equipmentCode" style="width: 80%;" ></select>
        <input type="button" id="btnEquipmentInfo" value="?" style="width: 30px; height: 30px;" ></input>
        </label>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <div class="left" style="width:99%; text-align: center;">
            <button type="button" id="btnOK" style="width:80px; height:30px;">OK</button>
        </div>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>

<script type="text/javascript" >

    function OkButtonClicked() {
        // Faz um chamada sincrona a página de inserção
        var targetUrl1 = 'Frontend/contrato/sub-contrato/acaoItem.php';
        $.ajax({ type: 'POST', url: targetUrl1, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega a lista de itens
        var targetUrl2 = 'AjaxCalls/GetSubContractItems.php?subContractId=<?php echo $subContractId; ?>';
        $("#itemList").load(targetUrl2);
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
