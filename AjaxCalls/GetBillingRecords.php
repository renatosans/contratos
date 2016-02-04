<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/BillingDAO.php");
include_once("../DataTransferObjects/BillingDTO.php");
include_once("../DataAccessObjects/InvoiceDAO.php");
include_once("../DataTransferObjects/InvoiceDTO.php");


$mailingId = $_GET['mailingId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["envioFaturamento"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$billingDAO = new BillingDAO($dataConnector->mysqlConnection);
$billingDAO->showErrors = 1;
$invoiceDAO = new InvoiceDAO($dataConnector->sqlserverConnection);
$invoiceDAO->showErrors = 1;


// Busca os registros de faturamento cadastrados no mailing
$billingArray = $billingDAO->RetrieveRecordArray("mailing_id=".$mailingId);
if (sizeof($billingArray) == 0) {
    echo "<tr>";
    echo "    <td colspan='7' align='center' >Nenhum registro encontrado!</td>";
    echo "</tr>";
    exit;
}

foreach($billingArray as $billing) {
    $invoiceNum = 0;
    $invoiceArray = $invoiceDAO->RetrieveRecordArray(null, "U_demFaturamento = '".$billing->id."'");
    if (sizeof($invoiceArray) > 0) {
        $invoice = $invoiceArray[0];
        $invoiceNum = $invoice->docNum;
    }
    ?>
    <tr>
        <td>
            <a href="<?php echo 'Frontend/faturamento/editar.php?id='.$billing->id; ?>" >
                <?php echo str_pad($billing->id, 5, '0', STR_PAD_LEFT); ?>
            </a>
        </td>
        <td>
            <a href="<?php echo 'Frontend/faturamento/editar.php?id='.$billing->id; ?>" >
                <?php echo $billing->dataInicial.' até '.$billing->dataFinal; ?>
            </a>
        </td>
        <td>
            <?php echo $billing->acrescimoDesconto; ?>
        </td>
        <td>
            <?php echo $billing->obs; ?>
        </td>
        <td>
            <a class="invoiceLink" rel="<?php echo $invoiceNum; ?>" >
                <span class="ui-icon ui-icon-alert"></span>
            </a>
        </td>
        <td>
            <a class="reportLink" rel="<?php echo $billing->id; ?>" >
                <span class="ui-icon ui-icon-alert"></span>
            </a>
        </td>
        <td>
            <a class="removeBilling" rel="<?php echo $billing->id; ?>" >
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
    $(".reportLink").click( function() { OpenBillingReport($(this)); } );
    $(".invoiceLink").click( function() { GetInvoiceInfo($(this)); } );

    <?php
    if ($nivelAutorizacao < 3) 
        echo '$(".removeBilling").addClass("ui-state-disabled");';
    else  
        echo '$(".removeBilling").click( function() { RemoveBilling($(this)); } );'
    ?>
</script>
