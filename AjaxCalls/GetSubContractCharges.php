<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
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

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
$contractChargeDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;


function GetModalidadeAsText($modalidadeMedicao)
{
    switch ($modalidadeMedicao)
    {
        case 1: return "Sem leituras";
        case 2: return "Leitura simples";
        default: return "Diferença entre leituras";
    }
}


// Busca as cobranças associadas ao subContrato
$chargeArray = $contractChargeDAO->RetrieveRecordArray("subContrato_id = ".$subContractId);

if (sizeof($chargeArray) == 0){
    echo '<tr><td colspan="7" align="center" >Nenhum registro encontrado!</td></tr>';
}

foreach ($chargeArray as $contractCharge) {
    $counter = $counterDAO->RetrieveRecord($contractCharge->codigoContador);
    ?>
    <tr>
        <td>
            <?php echo $counter->nome; ?>
        </td>
        <td>
            <?php echo GetModalidadeAsText($contractCharge->modalidadeMedicao); ?>
        </td>
        <td>
            <?php echo $contractCharge->fixo; ?>
        </td>
        <td>
            <?php echo $contractCharge->variavel; ?>
        </td>
        <td>
            <?php echo $contractCharge->franquia; ?>
        </td>
        <td align="center">
           <?php if ($contractCharge->individual == 1) echo "<img src='img/admin/checked_sign.png' style='margin: 0;' />"; ?>
        </td>
        <td>
            <a rel="<?php echo $contractCharge->id; ?>" class="removeCharge" >
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
        echo '$(".removeCharge").addClass("ui-state-disabled");';
    else  
        echo '$(".removeCharge").click( function() { RemoveSubContractCharge($(this)); } );'
?>
</script>
