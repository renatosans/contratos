<script type="text/javascript" >

    // TipoCusto:    1- Mão de Obra  2- Comubstível  3-Alimentação
    function OkButtonClicked() {
        var chamado = $("input[name=chamado]").val();
        var medicaoInicial = $("input[name=medicaoInicial]").val();
        var medicaoFinal = $("input[name=medicaoFinal]").val();
        var codigoInsumo = $("select[name=codigoInsumo] option:selected").val();
        var valorUnitario = $("select[name=codigoInsumo] option:selected").attr("alt");
        var totalDespesa = (medicaoFinal - medicaoInicial) * valorUnitario;
        var observacao = "Custos com mão de obra";

        // Faz um chamada sincrona a página de inserção da despesa
        var targetUrl = 'Frontend/chamados/despesas/acao.php';
        var callParameters = 'acao=store&chamado=' + chamado + '&codigoInsumo=' + codigoInsumo + '&tipoCusto=1';
        callParameters = callParameters + '&medicaoInicial=' + medicaoInicial + '&medicaoFinal=' + medicaoFinal;
        callParameters = callParameters + '&totalDespesa=' + totalDespesa + '&observacao=' + observacao;
        $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#popup").dialog('close');

        // Recarrega as despesas do chamado
        var targetUrl = 'AjaxCalls/GetServiceCallExpenses.php?serviceCallId=' + chamado;
        $("#despesas").load(targetUrl);

        return false;
    }

</script>

<?php

    $serviceCallId = $_GET['serviceCallId'];
    $assistanceDate = $_GET['assistanceDate'];
    $assistanceTime = $_GET['assistanceTime'];
    $assistanceDuration = $_GET['assistanceDuration'];


    $startDate = strtotime($assistanceDate."T".$assistanceTime);
    $startHour = (int)date("H", $startDate) + ((int)date("i", $startDate) / 60);
    $parts = explode(":", $assistanceDuration, 2);
    $endHour = $startHour + ((int)$parts[0]) +  ((int)$parts[1] / 60);

?>

<input type="hidden" name="chamado" value="<?php echo $serviceCallId; ?>" />
<input type="hidden" name="medicaoInicial" value="<?php echo $startHour; ?>" />
<input type="hidden" name="medicaoFinal" value="<?php echo $endHour; ?>" />

<label class="left" style="width:99%; text-align: left;">Valor da Mão de Obra<br />
    <select name="codigoInsumo" style="width:100%;" ></select>
</label>
<div style="clear:both;">
    <br/><br/>
</div>

<div class="left" style="width:99%; text-align: center;">
    <input id="btnOK" type="button" value="OK" style="width:50px; height:30px;"></input>
</div>

<script type="text/javascript" >
    $("#btnOK").click(function() { OkButtonClicked(); });

    var targetUrl = 'AjaxCalls/GetProductionInputOptions.php?inputType=1&productionInputId=0';
    $.get(targetUrl, function(options){
        $("select[name=codigoInsumo]").empty();
        $("select[name=codigoInsumo]").append(options);
        $("select[name=codigoInsumo]").change();
    });
</script>
