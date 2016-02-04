<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/CounterDAO.php");
include_once("../DataTransferObjects/CounterDTO.php");
include_once("../DataAccessObjects/ReadingDAO.php");
include_once("../DataTransferObjects/ReadingDTO.php");
include_once("../DataAccessObjects/EmployeeDAO.php");
include_once("../DataTransferObjects/EmployeeDTO.php");


$supplyRequestId = $_GET['supplyRequestId'];

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
$readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
$readingDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Busca as leituras realizadas durante o atendimento ao chamado
$readingArray = $readingDAO->RetrieveRecordArray("consumivel_id=".$supplyRequestId);
if (sizeof($readingArray) == 0) {
    echo "<tr>";
    echo "    <td colspan='6' align='center' >Nenhum registro encontrado!</td>";
    echo "</tr>";
    exit;
}

// Busca os funcionários cadastrados no sistema
$employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
$employeeDAO->showErrors = 1;
$retrievedArray = $employeeDAO->RetrieveRecordArray();
$employeeArray = array();
foreach ($retrievedArray as $employee) {
    $employeeArray[$employee->empID] = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
}


foreach($readingArray as $reading) {
    $counter = $counterDAO->RetrieveRecord($reading->codigoContador);
    ?>
    <tr>
        <td>
            <?php echo $counter->nome; ?>
        </td>
        <td>
            <?php echo $reading->contagem; ?>
        </td>
        <td>
            <?php echo $employeeArray[$reading->assinaturaDatacopy]; ?>
        </td>
        <td>
            <?php echo $reading->assinaturaCliente; ?>
        </td>
        <td>
            <?php echo $reading->observacao; ?>
        </td>
        <td>
            <a rel="<?php echo $reading->id; ?>" class="removeReading" >
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
        echo '$(".removeReading").addClass("ui-state-disabled");';
    else  
        echo '$(".removeReading").click( function() { RemoveReading($(this)); } );';
?> 
</script>
