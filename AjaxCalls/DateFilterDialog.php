<?php

    // Dialogo com filtro de datas para ser utilizado em relatórios
    // Recebe como parâmetros o relatório e filtros complementares

    include_once("../defines.php");
    include_once("../ClassLibrary/UnixTime.php");

    // Obtem a url do relatório e seus filtros
    $reportUrl = $_POST['reportUrl'];
    $parameters = $_POST['parameters'];

    $currentDate = new UnixTime(time());

?>

<input type="hidden" name="reportUrl" value="<?php echo $reportUrl; ?>" />
<input type="hidden" name="parameters" value="<?php echo $parameters; ?>" />

<label class="left" style="width:99%; text-align: left;">Data Inicial<br />
    <input class="datepick" type="text" name="dataInicial" style="width:98%;height:25px;" value="<?php echo date("Y-m-d", $currentDate->AddMonths(-1)); ?>" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>
<label class="left" style="width:99%; text-align: left;">Data Final<br />
    <input class="datepick" type="text" name="dataFinal" style="width:98%;height:25px;" value="<?php echo date("Y-m-d", $currentDate->value); ?>" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>

<div class="left" style="width:99%; text-align: center;">
    <input id="btnOK" type="button" value="OK" style="width:50px; height:30px;"></input>
</div>

<script type="text/javascript" >
    // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
    $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>


<script type="text/javascript" >
    function OkButtonClicked() {
        // Filtra o relatório de acordo com o período informado
        var reportUrl = $("input[name=reportUrl]").val();
        var additionalParameters = $("input[name=parameters]").val();
        var startDate = $("input[name=dataInicial]").val();
        var endDate = $("input[name=dataFinal]").val();
        var parameters = '?startDate=' + startDate + '&endDate=' + endDate;
        if (additionalParameters[0] == '&') parameters = parameters + additionalParameters;

        // Abre a página de relatórios em outra janela
        window.open(reportUrl + parameters);

        // Fecha o dialogo
        $("#popup").dialog('close');
    }
</script>
