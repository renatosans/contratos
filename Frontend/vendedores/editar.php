<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SalesPersonDAO.php");
include_once("../../DataTransferObjects/SalesPersonDTO.php");
include_once("../../DataAccessObjects/CommissionPerSignatureDAO.php");
include_once("../../DataTransferObjects/CommissionPerSignatureDTO.php");
include_once("../../DataAccessObjects/CommissionPerVolumeDAO.php");
include_once("../../DataTransferObjects/CommissionPerVolumeDTO.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/IndustryDAO.php");
include_once("../../DataTransferObjects/IndustryDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
$salesPersonDAO->showErrors = 1;
$commissionPerSignatureDAO = new CommissionPerSignatureDAO($dataConnector->mysqlConnection);
$commissionPerSignatureDAO->showErrors = 1;
$commissionPerVolumeDAO = new CommissionPerVolumeDAO($dataConnector->mysqlConnection);
$commissionPerVolumeDAO->showErrors = 1;
$industryDAO = new IndustryDAO($dataConnector->sqlserverConnection);
$industryDAO->showErrors = 1;


$slpCode = 0;
$salesPerson = new SalesPersonDTO();
if ( isset($_REQUEST["slpCode"]) && ($_REQUEST["slpCode"] != 0)) {
    $slpCode = $_REQUEST["slpCode"];
    $salesPerson = $salesPersonDAO->RetrieveRecord($slpCode);
}

// Traz as regras de comissão cadastradas no sistema
$commissionRuleArray1 = $commissionPerSignatureDAO->RetrieveRecordArray(); 
$commissionRuleArray2 = $commissionPerVolumeDAO->RetrieveRecordArray();

// Busca os segmentos/ramos de atividade cadastrados no sistema
$industryArray = array(0=>"Todos");
$tempArray = $industryDAO->RetrieveRecordArray();
foreach ($tempArray as $industry) {
    $industryArray[$industry->id] = $industry->name;
}


function GetCommissionRules1() {
    global $commissionRuleArray1;
    global $industryArray;

    echo '<table border="0" cellpadding="0" cellspacing="0" >';
    echo '<thead>';
    echo '    <tr><th>&nbsp;Segmento</th><th>&nbsp;Data de Assinatura</th><th>&nbsp;Comissão</th></tr>';
    echo '</thead>';
    echo '<tbody>';
    if (sizeof($commissionRuleArray1) == 0){
        echo '<tr><td colspan="4" align="center" >Nenhum registro encontrado!</td></tr>';
    }
    foreach ($commissionRuleArray1 as $commissionRule) {
        ?>
        <tr>
            <td >
                <?php echo $industryArray[$commissionRule->segmento]; ?>
            </td>
            <td >
                <?php
                    echo $commissionRule->dataAssinaturaDe.'  até  '.$commissionRule->dataAssinaturaAte;
                ?>
            </td>
            <td >
                <?php echo $commissionRule->comissao.'%'; ?>
            </td>
        </tr>
        <?php
    }
    echo '</tbody>';
    echo '</table>';
}

function GetCommissionRules2() {
    global $commissionRuleArray2;

    echo '<table border="0" cellpadding="0" cellspacing="0" >';
    echo '<thead>';
    echo '    <tr><th>&nbsp;Categoria de contrato</th><th>&nbsp;Quantidade de Contratos</th><th>&nbsp;Valor dos faturamentos</th><th>&nbsp;Comissão</th></tr>';
    echo '</thead>';
    echo '<tbody>';
    if (sizeof($commissionRuleArray2) == 0){
        echo '<tr><td colspan="4" align="center" >Nenhum registro encontrado!</td></tr>';
    }
    foreach ($commissionRuleArray2 as $commissionRule) {
        ?>
        <tr>
            <td >
                <?php echo ContractDAO::GetCategoryAsText($commissionRule->categoriaContrato); ?>
            </td>
            <td >
                <?php
                    $quantContratosDe  = $commissionRule->quantContratosDe;
                    $quantContratosAte = $commissionRule->quantContratosAte;
                    echo $quantContratosDe.' até '.$quantContratosAte;
                ?>
            </td>
            <td >
                <?php
                    $valorFaturamentoDe  = number_format($commissionRule->valorFaturamentoDe , 2, ',', '.');
                    $valorFaturamentoAte = number_format($commissionRule->valorFaturamentoAte, 2, ',', '.');
                    echo $valorFaturamentoDe.' até '.$valorFaturamentoAte;
                ?>
            </td>
            <td >
                <?php echo $commissionRule->comissao.'%'; ?>
            </td>
        </tr>
        <?php
    }
    echo '</tbody>';
    echo '</table>';
}

?>

    <h1>Administração - Vendedor</h1>
    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="slpCode" value="<?php echo $slpCode; ?>" />

        <fieldset style="width:650px;">
            <legend>Dados</legend>
            Código:  <?php echo $salesPerson->slpCode; ?><br/><br/>
            Nome:  <?php echo $salesPerson->slpName; ?><br/><br/>
            Comissão Simples:  <?php echo number_format($salesPerson->commission, 1, ',', '.'); ?> % <br/><br/>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width:650px;" >
            <legend>Regras de comissão (Por assinatura dos contratos)</legend>
            <?php GetCommissionRules1(); ?>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <fieldset style="width:650px;" >
            <legend>Regras de comissão (Por volume de contratos)</legend>
            <?php GetCommissionRules2(); ?>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>


        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
