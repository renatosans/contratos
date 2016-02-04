<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/Text.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/IndirectCostDAO.php");
    include_once("../DataTransferObjects/IndirectCostDTO.php");
    include_once("../DataAccessObjects/ServiceCallDAO.php");
    include_once("../DataTransferObjects/ServiceCallDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");

    $indirectCostId = $_GET['indirectCostId'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $indirectCostDAO = new IndirectCostDAO($dataConnector->mysqlConnection);
    $indirectCostDAO->showErrors = 1;
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;

    // Busca os chamados relacionados ao custo indireto
    $serviceCallArray = $indirectCostDAO->GetDistributedExpenses($indirectCostId);
    if (sizeof($serviceCallArray) == 0) {
        echo "<tr>";
        echo "    <td colspan='5' align='center' >Nenhum registro encontrado!</td>";
        echo "</tr>";
        exit;
    }

    foreach($serviceCallArray as $serviceCallId) {
        $serviceCall = $serviceCallDAO->RetrieveRecord($serviceCallId);
        $subject = new Text($serviceCall->defeito);
        $serialNumber = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $serviceCall->codigoCartaoEquipamento);
        ?>
        <tr>
            <td >
                <?php echo str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT); ?>
            </td>
            <td >
                <?php echo $subject->Truncate(34); ?>
            </td>
            <td >
                <?php echo $serialNumber; ?>
            </td>
            <td >
                <?php echo $serviceCall->dataAbertura; ?>
            </td>
            <td>
                <a rel="<?php echo $serviceCall->id; ?>" rev="<?php echo $indirectCostId; ?>" class="removeDistributedExpense" >
                    <span class="ui-icon ui-icon-closethick"></span>
                </a>
            </td>
        </tr>
        <?php
    }

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>

<script type="text/javascript" >

    function RemoveDistributedExpense(distributedExpense){
        if(!confirm("Confirma exclusão?")) return false;

        // Faz um chamada sincrona a página de exclusão
        var serviceCallId = distributedExpense.attr("rel");
        var indirectCostId = distributedExpense.attr("rev");
        var targetUrl = 'Frontend/custoIndireto/acaoDespesaDistribuida.php?acao=remove';
        var callParameters = {'serviceCallId': serviceCallId, 'indirectCostId': indirectCostId};
        $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

        // Recarrega as despesas distribuidas (associação entre chamado e custo indireto)
        var targetUrl = 'AjaxCalls/GetDistributedExpenses.php?indirectCostId=' + indirectCostId;
        $("#serviceCalls").load(targetUrl);
    }

    $(".removeDistributedExpense").css('text-decoration', 'underline');
    $(".removeDistributedExpense").click( function() { RemoveDistributedExpense($(this)); } );

</script>
