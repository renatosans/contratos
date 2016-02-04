<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataTransferObjects/ContractBonusDTO.php");
include_once("../DataAccessObjects/ContractBonusDAO.php");
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

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractBonusDAO = new ContractBonusDAO($dataConnector->mysqlConnection);
$contractBonusDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Busca os bonus associados ao subContrato
$bonusArray = $contractBonusDAO->RetrieveRecordArray("subcontrato_id = ".$subContractId." ORDER BY de, ate");

if (sizeof($bonusArray) == 0){
    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
}

foreach($bonusArray as $contractBonus) {
    $counter = $counterDAO->RetrieveRecord($contractBonus->codigoContador);
    ?>
    <tr>
        <td >
            <?php echo $counter->nome; ?>
        </td>
        <td >
            <?php echo $contractBonus->de; ?>
        </td>
        <td >
            <?php echo $contractBonus->ate; ?>
        </td>
        <td >
            <?php echo $contractBonus->valor; ?>
        </td>
        <td>
            <a rel="<?php echo $contractBonus->id; ?>" class="removeBonus" >
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
        echo '$(".removeBonus").addClass("ui-state-disabled");';
    else  
        echo '$(".removeBonus").click( function() { RemoveSubContractBonus($(this)); } );'
?>
</script>
