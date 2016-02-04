<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ExpenseDAO.php");
include_once("../DataTransferObjects/ExpenseDTO.php");
include_once("../DataAccessObjects/ProductionInputDAO.php");
include_once("../DataTransferObjects/ProductionInputDTO.php");


$serviceCallId = $_GET['serviceCallId'];
if (empty($serviceCallId)) $serviceCallId = 0;

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoChamados"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Localiza todos as despesas que pertencem ao chamado
$expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
$expenseDAO->showErrors = 1;
$productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
$productionInputDAO->showErrors = 1;
$expenseArray = $expenseDAO->RetrieveRecordArray("codigoChamado=".$serviceCallId);
if (sizeof($expenseArray) == 0) {
    echo "<tr>";
    echo "    <td colspan='3' align='center'>Nenhum registro encontrado!</td>";
    echo "</tr>";
    exit;
}

foreach($expenseArray as $expense) {
    $nomeItem = new Text($expense->nomeItem);
    $descricao = $expense->quantidade." ".$nomeItem->Truncate(60);
    $codigoInsumo = $expense->codigoInsumo;
    if (!empty($codigoInsumo)) {
        $productionInput = $productionInputDAO->RetrieveRecord($codigoInsumo);
        $inputTypeArray = $productionInputDAO->RetrieveInputTypes();
        $descricao = $inputTypeArray[$productionInput->tipoInsumo];
    }
    ?>
    <tr>
        <td>
            <?php echo $descricao; ?>
        </td>
        <td>
            <?php echo $expense->totalDespesa; ?>
        </td>
        <td>
            <a rel="<?php echo $expense->id; ?>" class="removeExpense" >
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

function RemoveExpense(expense) {
    if(!confirm("Confirma exclusão?")) return false;

    // Faz um chamada sincrona a página de exclusão
    var expenseId = expense.attr("rel");
    var targetUrl = 'Frontend/chamados/despesas/acao.php?acao=remove';
    var callParameters = {'id': expenseId};
    $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

    // Recarrega as despesas
    GetServiceCallExpenses();
}

<?php
    if ($nivelAutorizacao < 3) 
        echo '$(".removeExpense").addClass("ui-state-disabled");';
    else  
        echo '$(".removeExpense").click( function() { RemoveExpense($(this)); } );'
?>

</script>
