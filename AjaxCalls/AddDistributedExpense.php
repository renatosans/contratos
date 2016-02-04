<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ServiceCallDAO.php");
include_once("../DataTransferObjects/ServiceCallDTO.php");

$indirectCostId = $_GET['indirectCostId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
$serviceCallDAO->showErrors = 1;

// Busca os chamados cadastrados no sistema
$serviceCallArray = $serviceCallDAO->RetrieveRecordArray("id > 0 ORDER BY id");

?>

    <form name="fDados" action="Frontend/custoIndireto/acaoDespesaDistribuida.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="indirectCostId" value="<?php echo $indirectCostId; ?>" />

        <label class="left" style="width: 99%;">Chamado<br/>
        <select name="serviceCallId" style="width: 96%;">
            <option selected='selected' value="0"></option>
            <?php
                foreach ($serviceCallArray as $serviceCall) {
                    $subject = new Text($serviceCall->defeito);
                    $serviceCallInfo = str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT).' - '.$subject->Truncate(34);
                    echo "<option value=".$serviceCall->id." >".$serviceCallInfo."</option>";
                }
            ?>
        </select>
        </label>
        <div style="clear:both;">
            <br/><br/>
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

    function OkButtonClicked() {
        var serviceCallId   = $("select[name=serviceCallId]").val();
        var indirectCostId  = $("input[name=indirectCostId]").val();

        // Faz um chamada sincrona a página de inserção
        var targetUrl = 'Frontend/custoIndireto/acaoDespesaDistribuida.php';
        var callParameters = 'acao=store&serviceCallId=' + serviceCallId + '&indirectCostId=' + indirectCostId;
        $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega as despesas distribuidas (associação entre chamado e custo indireto)
        var targetUrl = 'AjaxCalls/GetDistributedExpenses.php?indirectCostId=' + indirectCostId;
        $("#serviceCalls").load(targetUrl);
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
