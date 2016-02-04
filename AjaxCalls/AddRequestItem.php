<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/RequestItemDAO.php");
include_once("../DataTransferObjects/RequestItemDTO.php");


$supplyRequestId = 0;
if (isset($_REQUEST["supplyRequestId"]) && ($_REQUEST["supplyRequestId"] != 0)) {
    $supplyRequestId = $_REQUEST["supplyRequestId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;

?>

    <form name="fDados" action="Frontend/_consumivel/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="supplyRequestId" value="<?php echo $supplyRequestId; ?>" />

        <fieldset align="left" style="width:92%;" >
            <legend style="font-size: 15px;">Ordenação de itens</legend>
            <input type="radio" name="rdioSortedBy" value="1" checked="checked" >&nbsp;Por nome&nbsp;</input><br/>
            <input type="radio" name="rdioSortedBy" value="2" >&nbsp;Por código&nbsp;</input><br/>
        </fieldset>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label align="left" style="width:99%;" >Item<br/>
        <select name="itemCode" style="width:98%;"></select>
        <br/>
        <input type="hidden" name="itemName" value="" />
        </label>

        <label align="left" style="width:99%;">Quantidade<br/>
            <input type="text" name="quantity" style="width:60%;height:25px;" value="" />
            &nbsp;&nbsp;&nbsp;
            <b style="font-size:13px;color:red;" >Em estoque: <span id="stockQuantity" >0</span></b>
        </label>

        <label align="left" style="width:99%;">Total (R$)<br/>
            <input type="text" name="total" style="width:60%;height:25px;" value="" />
            <input type="button" id="btnCalcular" value="..." style="width: 30px; height: 30px;" />
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <div class="left" style="width:99%; text-align: center;">
            <input type="button" id="btnOK" value="OK" style="width:80px; height:30px;"></input>
        </div>
    </form>

<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>

<script type="text/javascript" >

    function ChangeSortCriteria() {
        var sortedBy = $("input[name=rdioSortedBy]:checked").val();
        HandleItemReload(sortedBy);
    }

    function HandleItemReload(sortedBy) {
        var targetUrl = 'AjaxCalls/GetInventoryItemOptions.php?sortedBy=' + sortedBy + '&defaultItemCode=0';
        $.get(targetUrl, function(response){ ReloadItem(response); });
    }

    function ReloadItem(options) {
        $("select[name=itemCode]").empty();
        $("select[name=itemCode]").append(options);
        $("select[name=itemCode]").trigger("change");
    }

    function UpdateItem()
    {
        var itemCode = $("select[name=itemCode] option:selected").val();
        var itemName = $("select[name=itemCode] option:selected").text();

        // Atualiza o nome do item
        $("input[name=itemName]").val(itemName);

        // Atualiza a quantidade de itens em estoque (faz um chamada sincrona a página de busca)
        var stockQuantity = 0;
        var targetUrl = 'AjaxCalls/GetStockQuantity.php?itemCode=' + itemCode;
        $.ajax({ url: targetUrl, success: function(response) { stockQuantity = response; }, async: false });
        $("#stockQuantity").text(stockQuantity);
    }

    function Calcular(){
        var precoMedioItem = $("select[name=itemCode] option:selected").attr("alt");
        var quantidade = $("input[name=quantity]").val();
        var total = precoMedioItem * quantidade;
        $("input[name=total]").val(total);
    }

    function OkButtonClicked() {
        var supplyRequestId = $("input[name=supplyRequestId]").val();
        var itemCode = $("select[name=itemCode]").val();
        var itemName = $("input[name=itemName]").val();
        var quantity = $("input[name=quantity]").val();
        var total = $("input[name=total]").val();

        // Faz um chamada sincrona a página de inserção
        var targetUrl1 = 'Frontend/_consumivel/acaoItem.php';
        var callParameters1 = 'acao=store&supplyRequestId=' + supplyRequestId + '&itemCode=' + itemCode + '&itemName=' + itemName + '&quantity=' + quantity + '&total=' + total;
        $.ajax({ type: 'POST', url: targetUrl1, data: callParameters1, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega os itens da solicitação
        var targetUrl2 = 'AjaxCalls/GetRequestItems.php?supplyRequestId=<?php echo $supplyRequestId; ?>';
        $("#requestItems").load(targetUrl2);
    }

    $(document).ready(function() {
        HandleItemReload(1); // Carrega o combo de itens, deixa o item default selecionado
        UpdateItem(); // Atualiza dados do item baseado no combo de seleção de item

        $("input[name=rdioSortedBy]").click( function() { ChangeSortCriteria(); });
        $("select[name=itemCode]").change(function() { UpdateItem(); });
        $("#btnCalcular").click( function() { Calcular(); });
        $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });
    });
</script>
