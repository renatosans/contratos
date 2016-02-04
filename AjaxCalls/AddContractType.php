<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataTransferObjects/ContractTypeDTO.php");
include_once("../DataAccessObjects/ContractTypeDAO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$contractTypeDAO = new ContractTypeDAO($dataConnector->mysqlConnection);
$contractTypeDAO->showErrors = 1;

?>

    <form name="fDados" action="Frontend/contrato/acaoTipo.php" method="post" >
        <input type="hidden" name="acao" value="store" />

        <label class="left" style="width: 99%;">Sigla<br/>
        <input type="text" name="sigla" value="" style="width: 98%;height:25px;" />
        </label>
        
        <label class="left" style="width: 99%;">Descricao<br/>
        <input type="text" name="descricao" value="" style="width: 98%;height:25px;" />
        </label> 
        <div style="clear:both;">
            <br/><br/>
        </div>
        
        <label>
        <input type="checkbox" name="permiteBonus" />
        Permitir Bonus?
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <button type="button" id="btnOK" >
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>

<script type="text/javascript" >

    function OkButtonClicked() {
        var sigla         = $("input[name=sigla]").val();
        var descricao     = $("input[name=descricao]").val();
        var permiteBonus  = "0"; if ($("input[name=permiteBonus]").is(":checked")) permiteBonus  = "1";

        // Faz um chamada sincrona a página de inserção
        var targetUrl1 = 'Frontend/contrato/acaoTipo.php';
        var callParameters1 = 'acao=store&sigla=' + sigla + '&descricao=' + descricao + '&permiteBonus=' + permiteBonus;
        $.ajax({ type: 'POST', url: targetUrl1, data: callParameters1, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega o combo com os tipos de contrato
        GetContractTypeOptions();
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
