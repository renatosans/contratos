<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/EquipmentModelDAO.php");
include_once("../DataTransferObjects/EquipmentModelDTO.php");
include_once("../DataAccessObjects/ManufacturerDAO.php");
include_once("../DataTransferObjects/ManufacturerDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}


// Cria os objetos de mapeamento objeto-relacional
$equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
$equipmentModelDAO->showErrors = 1;
$manufacturerDAO = new ManufacturerDAO($dataConnector->sqlserverConnection);
$manufacturerDAO->showErrors = 1;

// Busca os fabricantes cadastrados no sistema
$manufacturerArray = $manufacturerDAO->RetrieveRecordArray("FirmCode <> -1 ORDER BY FirmCode ASC");


?>

    <form name="fDados" action="Frontend/equipamentos/acaoModelo.php" method="post" >
        <input type="hidden" name="acao" value="store" />

        <label class="left" style="width: 99%;">Modelo<br/>
        <input type="text" name="modelo" style="width: 98%;height:25px;" value="" />
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <label class="left" style="width: 99%;">Fabricante<br/>
        <select name="fabricante" style="width: 98%;">
        <?php
            foreach ($manufacturerArray as $manufacturer) {
                echo "<option value=".$manufacturer->FirmCode." >".$manufacturer->FirmName."</option>";
            }
        ?>
        </select>
        </label>
        <div style="clear:both;">
            <br/><br/>
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
        var targetUrl = 'Frontend/equipamentos/acaoModelo.php';
        $.ajax({ type: 'POST', url: targetUrl, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega a lista de modelos de equipamento
        GetEquipModelOptions();
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
