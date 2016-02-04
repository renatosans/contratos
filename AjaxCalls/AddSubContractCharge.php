<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataTransferObjects/SubContractDTO.php");
include_once("../DataAccessObjects/SubContractDAO.php");
include_once("../DataTransferObjects/ContractChargeDTO.php");
include_once("../DataAccessObjects/ContractChargeDAO.php");
include_once("../DataTransferObjects/CounterDTO.php");
include_once("../DataAccessObjects/CounterDAO.php");


$subContractId = 0;
if (isset($_REQUEST["subContractId"]) && ($_REQUEST["subContractId"] != 0)) {
    $subContractId = $_REQUEST["subContractId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;
$contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
$contractChargeDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Recupera o objeto que contém este (parent object)
$subContract = $subContractDAO->RetrieveRecord($subContractId);

?>

    <form name="fDados" action="Frontend/contrato/sub-contrato/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="0" />
        <input type="hidden" name="contractId" value="<?php echo $subContract->codigoContrato; ?>" />
        <input type="hidden" name="subContractId" value="<?php echo $subContract->id; ?>" />


        <label class="left" style="width: 99%;">Contador<br/>
        <select name="counterId" style="width: 98%;" >
            <?php
            $counterArray = $counterDAO->RetrieveRecordArray();
            $isFirst = true;
            foreach($counterArray as $counter) {
                $attributes = "";
                if ($isFirst) $attributes = "selected='selected'";
                echo "<option ".$attributes." value=".$counter->id." >".$counter->nome."</option>";
                $isFirst = false;
            }
            ?>
        </select>
        </label>

        <label class="left" style="width: 99%;">Modalidade de Medição<br/>
        <select name="modalidadeMedicao" style="width: 98%;" >
            <option value="1">Sem leituras</option>
            <option value="2">Leitura simples</option>
            <option value="3">Diferença entre leituras</option>
        </select>
        </label>

        <label class="left" style="width: 99%;">Fixo<br/>
        <input type="text" name="fixo" value="" style="width: 98%;height:25px;" />
        </label>

        <label class="left" style="width: 99%;">Variável<br/>
        <input type="text" name="variavel" value="" style="width: 98%;height:25px;" />
        </label>

        <label class="left" style="width: 99%;">Franquia<br/>
        <input type="text" name="franquia" value="" style="width: 98%;height:25px;" />
        </label>

        <label class="left" style="width: 99%;">Cobrança Individual<br/><br/>
        <input type="checkbox" name="individual"> Cobrar equipamentos individualmente (considerando capacidade do equipamento ao invés de franquia)</input>
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
        var targetUrl1 = 'Frontend/contrato/sub-contrato/acaoCobranca.php';
        $.ajax({ type: 'POST', url: targetUrl1, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega a lista de cobranças
        var targetUrl2 = 'AjaxCalls/GetSubContractCharges.php?subContractId=<?php echo $subContractId; ?>';
        $("#chargeList").load(targetUrl2);
    }

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
