<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/LoginDAO.php");
include_once("../DataTransferObjects/LoginDTO.php");
include_once("../DataAccessObjects/SalesPersonDAO.php");
include_once("../DataTransferObjects/SalesPersonDTO.php");

$loginId   = $_GET['loginId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$loginDAO = new LoginDAO($dataConnector->mysqlConnection);
$loginDAO->showErrors = 1;
$salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
$salesPersonDAO->showErrors = 1;

// Busca os dados do login
$login = $loginDAO->RetrieveRecord($loginId);

// Busca os vendedores disponíveis para vincular
$salesPersonArray = $salesPersonDAO->RetrieveRecordArray();

?>

<form name="fDados" action="Frontend/login/acao.php" method="post" >
    <input type="hidden" name="acao" value="bindLogin" />
    <input type="hidden" name="id" value="<?php echo $login->id; ?>" />

    <label class="left" style="width:99%; text-align: left;">Vendedor<br/>
        <select name="vendedor" style="width: 250px;">
            <?php
                foreach($salesPersonArray as $salesPerson)
                {
                    $attributes = "";
                    if ($salesPerson->slpCode == $login->idExterno) $attributes = "selected='selected'";
                    echo "<option ".$attributes." value=".$salesPerson->slpCode.">".$salesPerson->slpName."</option>";
                }
            ?>
        </select>
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
        var id        = $("input[name=id]").val();
        var idExterno = $("select[name=vendedor]").val();

        // Faz um chamada sincrona a página de atualização
        var targetUrl = 'Frontend/login/acao.php';
        var callParameters = 'acao=bindLogin&id=' + id + '&idExterno=' + idExterno;
        $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#popup").dialog('close');
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
