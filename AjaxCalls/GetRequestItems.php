<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/RequestItemDAO.php");
include_once("../DataTransferObjects/RequestItemDTO.php");
include_once("../DataAccessObjects/InventoryItemDAO.php");
include_once("../DataTransferObjects/InventoryItemDTO.php");


$supplyRequestId = 0;
if (isset($_REQUEST["supplyRequestId"]) && ($_REQUEST["supplyRequestId"] != 0)) {
    $supplyRequestId = $_REQUEST["supplyRequestId"];
}
$partRequestId = 0;
if (isset($_REQUEST["partRequestId"]) && ($_REQUEST["partRequestId"] != 0)) {
    $partRequestId = $_REQUEST["partRequestId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["solicitacaoConsumiveis"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;

// Busca os itens da solicitação
$firstCondition = 'pedidoConsumivel_id IS NULL';  if ($supplyRequestId != 0) $firstCondition = 'pedidoConsumivel_id='.$supplyRequestId; 
$secondCondition = 'pedidoPecaReposicao_id IS NULL';  if ($partRequestId != 0) $secondCondition = 'pedidoPecaReposicao_id='.$partRequestId;
$filter = $firstCondition.' AND '.$secondCondition;
$requestItemArray = $requestItemDAO->RetrieveRecordArray($filter);


if (sizeof($requestItemArray) == 0){
    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
}

foreach ($requestItemArray as $requestItem) {
    $stockQuantity = InventoryItemDAO::GetStockQuantity($dataConnector->sqlserverConnection, $requestItem->codigoItem);
    $stockQuantity = number_format($stockQuantity, 0);
    ?>
    <tr rel="<?php echo $requestItem->codigoItem; ?>" rev="<?php echo $requestItem->quantidade; ?>" >
        <td title="Quantidade em estoque: <?php echo $stockQuantity; ?>" >
            <?php echo $requestItem->codigoItem; ?>
        </td>
        <td title="Quantidade em estoque: <?php echo $stockQuantity; ?>" >
            <?php echo $requestItem->nomeItem; ?>
        </td>
        <td>
            <?php echo $requestItem->quantidade; ?>
        </td>
        <td>
            <?php echo number_format($requestItem->total, 2, ',', '.'); ?>
        </td>
        <td>
            <a rel="<?php echo $requestItem->id; ?>" class="removeItem" >
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
<?php
    if ($nivelAutorizacao < 3)
        echo '$(".removeItem").addClass("ui-state-disabled");';
    else
        echo '$(".removeItem").click( function() { RemoveRequestItem($(this)); } );';
?>
</script>
