<?php

    // Dialogo com filtro "Hora Inicial" e "Hora Final"
    // retorna o tempo decorrido

    include_once("../defines.php");
    include_once("../ClassLibrary/UnixTime.php");

    // Obtem o input que receberá o resultado
    $target = $_REQUEST['target'];

    $currentTime = new UnixTime(strtotime("00:00"));

?>

<input type="hidden" name="target" value="<?php echo $target; ?>" />

<label class="left" style="width:99%; text-align: left;">Horário de Entrada<br/>
    <input type="text" name="horaInicial" style="width:98%;height:25px;" value="<?php echo date("H:i", $currentTime->value); ?>" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>
<label class="left" style="width:99%; text-align: left;">Horário de Saída<br/>
    <input type="text" name="horaFinal" style="width:98%;height:25px;" value="<?php echo date("H:i", $currentTime->AddTime(1)); ?>" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>

<div class="left" style="width:99%; text-align: center;">
    <input id="btnOK" type="button" value="OK" style="width:50px; height:30px;"></input>
</div>

<script type="text/javascript" >

    function parseVal(val)
    {
       while ((val.charAt(0) == '0') && (val.length > 1))
          val = val.substring(1, val.length);

       return parseInt(val);
    }

    function PadNumber(number, length) {
        var result = "" + number;
        var len = result.length;

        if (len >= length) return result;

        while (len < length) {
            result = "0" + result;
            len = result.length;
        }

        return result;
    }

    function OkButtonClicked() {
        var target = $("input[name=target]").val();
        var startTime = $("input[name=horaInicial]").val();
        var endTime = $("input[name=horaFinal]").val();

        // Retorna o resultado do dialogo
        var startTimeArray = startTime.split(":", 2);
        var endTimeArray = endTime.split(":", 2);
        var diff = (parseVal(endTimeArray[0])*60 + parseVal(endTimeArray[1])) - (parseVal(startTimeArray[0])*60 + parseVal(startTimeArray[1]));
        var timeInterval = PadNumber(Math.floor(diff/60), 2) + ":" + PadNumber(diff%60, 2);
        $("input[name=" + target + "]").val(timeInterval);

        // Fecha o dialogo
        $("#popup").dialog('close');
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
