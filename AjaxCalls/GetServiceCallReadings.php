<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/CounterDAO.php");
include_once("../DataTransferObjects/CounterDTO.php");
include_once("../DataAccessObjects/ReadingDAO.php");
include_once("../DataTransferObjects/ReadingDTO.php");


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

// Cria os objetos de mapeamento objeto-relacional
$readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
$readingDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Busca as leituras realizadas durante o atendimento ao chamado
$readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCallId);
if (sizeof($readingArray) == 0) {
    echo "<tr>";
    echo "    <td colspan='4' align='center' >Nenhum registro encontrado!</td>";
    echo "</tr>";
    exit;
}

foreach($readingArray as $reading) {
    $counter = $counterDAO->RetrieveRecord($reading->codigoContador);        
    ?>
    <tr>
        <td>
            <?php echo $counter->nome; ?>
        </td>
        <td >
            <?php echo $reading->contagem; ?>
        </td>
        <td >
            <?php echo $reading->observacao; ?>
        </td>
        <td>
            <a rel="<?php echo $reading->id; ?>" class="excluirContador" >
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
        echo '$(".excluirContador").addClass("ui-state-disabled");';
    else  
        echo '$(".excluirContador").click( function() { ExcluirContador($(this)); } );'
?>
</script>
