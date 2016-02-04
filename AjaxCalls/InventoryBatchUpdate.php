
<form name="fDados" action="Frontend/inventario/acao.php" method="post" >
    <input type="hidden" name="acao" value="batchUpdate" />

    <label class="left" style="width:99%; text-align: left;">Nome de itens (que contenham o texto)<br/>
        <input type="text" name="itemNameQuery" style="width:98%;height:25px;" value="" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>

    <label class="left" style="width:99%; text-align: left;">Custo Pág. Peças (Equipamentos)<br/>
        <input type="text" name="expenses" style="width:98%;height:25px;" value="" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>

    <label class="left" style="width:99%; text-align: left;">Durabilidade/Vida Útil<br/>
        <input type="text" name="durability" style="width:98%;height:25px;" value="" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>

    <label class="left" style="width:99%; text-align: left;">Instruções de Uso<br/>
        <input type="text" name="useInstructions" style="width:98%;height:25px;" value="" ></input>
    </label>
    <div style="clear:both;">
        <br/>
    </div>

    <div class="left" style="width:99%; text-align: center;">
        <input id="btnOK" type="button" value="OK" style="width:80px; height:30px;"></input>
    </div>
</form>

<script type="text/javascript" >

    function OkButtonClicked() {
        var itemNameQuery   = $("input[name=itemNameQuery]").val();
        var expenses        = $("input[name=expenses]").val();
        var durability      = $("input[name=durability]").val();
        var useInstructions = $("input[name=useInstructions]").val();

        // Faz um chamada sincrona a página de atualização
        var targetUrl = 'Frontend/inventario/acao.php';
        var callParameters = 'acao=batchUpdate&itemNameQuery=' + itemNameQuery + '&expenses=' + expenses + '&durability=' + durability + '&useInstructions=' + useInstructions;
        $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#popup").dialog('close');
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
