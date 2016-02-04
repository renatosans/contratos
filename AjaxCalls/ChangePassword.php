
<form name="fDados" action="Frontend/login/acao.php" method="POST" >
    <input type="hidden" name="acao" value="changePassword" />

    <label class="left" style="width: 99%;">Usuário<br/>
    <input type="text" name="username" value="" style="width: 98%;height:25px;" />
    </label>

    <label class="left" style="width: 99%;">Antiga senha<br/>
    <input type="password" name="oldPassword" value="" style="width: 98%;height:25px;" />
    </label>

    <label class="left" style="width: 99%;">Nova senha<br/>
    <input type="password" name="newPassword" value="" style="width: 98%;height:25px;" />
    </label>

    <label class="left" style="width: 99%;">Confirmar nova senha<br/>
    <input type="password" name="confirmation" value="" style="width: 98%;height:25px;" />
    </label>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <div class="left" style="width:99%; text-align: center;">
        <button type="button" id="btnOK" style="width:80px; height:30px;">OK</button>
    </div>
</form>


<script type="text/javascript" >
    function OkButtonClicked() {
        // Faz uma chamada síncrona a página que executa a ação
        var targetUrl = 'Frontend/login/acao.php';
        $.ajax({ type: 'POST', url: targetUrl, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#passwordDialog").dialog('close');
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });
</script>
