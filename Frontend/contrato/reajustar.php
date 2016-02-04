<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/SubContractDAO.php");
include_once("../../DataTransferObjects/SubContractDTO.php");
include_once("../../DataAccessObjects/ContractItemDAO.php");
include_once("../../DataTransferObjects/ContractItemDTO.php");
include_once("../../DataAccessObjects/ContractChargeDAO.php");
include_once("../../DataTransferObjects/ContractChargeDTO.php");
include_once("../../DataAccessObjects/ContractBonusDAO.php");
include_once("../../DataTransferObjects/ContractBonusDTO.php");
include_once("../../DataAccessObjects/AdjustmentRateDAO.php");
include_once("../../DataTransferObjects/AdjustmentRateDTO.php");
include_once("../../DataAccessObjects/ContractTypeDAO.php");
include_once("../../DataTransferObjects/ContractTypeDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");


$id = $_REQUEST["id"];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;
$contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
$contractChargeDAO->showErrors = 1;
$contractBonusDAO = new ContractBonusDAO($dataConnector->mysqlConnection);
$contractBonusDAO->showErrors = 1;
$adjustmentRateDAO = new AdjustmentRateDAO($dataConnector->mysqlConnection);
$adjustmentRateDAO->showErrors = 1;


// Busca os dados do contrato
$contract = $contractDAO->RetrieveRecord($id); 

// Busca o índice de reajuste
$adjustmentRate = $adjustmentRateDAO->RetrieveRecord($contract->indiceReajuste);

// Busca todos os subcontratos pertencentes ao contrato
$subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id = ".$id);

?>

    <h1>Reajuste de contrato</h1><br/>
    <h1><?php echo str_pad('_', 52, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="adjustment" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <div style="max-width:650px;" >
            <h1>Reajuste: <?php echo $adjustmentRate->aliquota.'% pelo '.$adjustmentRate->sigla.' ('.$adjustmentRate->nome.') '; ?></h1>
        </div>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <?php
            foreach ($subContractArray as $subContract) {
                $subContractItems = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $subContract->id);
                $serialNumbers = '';
                foreach ($subContractItems as $contractItem) {
                    if (!empty($serialNumbers)) $serialNumbers .= ', ';
                    $serialNumbers .= EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $contractItem->codigoCartaoEquipamento);
                }
                if (empty($serialNumbers)) $serialNumbers = 'Nenhum item encontrado';
                $chargeArray = $contractChargeDAO->RetrieveRecordArray("subContrato_id = ".$subContract->id);
                $bonusArray = $contractBonusDAO->RetrieveRecordArray("subcontrato_id = ".$subContract->id." ORDER BY de, ate");
    
                echo '<fieldset style="width:650px;" >';
                echo '    <legend>'.ContractTypeDAO::GetAlias($dataConnector->mysqlConnection, $subContract->codigoTipoContrato).'</legend>';
                echo '    Itens:<br/>';
                echo '    - '.$serialNumbers;
                echo '    <br/><br/>';

                echo '    Cobrança:<br/>';
                foreach ($chargeArray as $charge) {
                    $counterName = CounterDAO::GetCounterName($dataConnector->mysqlConnection, $charge->codigoContador);
                    $fixoReajustado = $charge->fixo * ( 1 + ( $adjustmentRate->aliquota / 100) );
                    echo '<input type="hidden" name="fixoReajustado'.$charge->id.'" value="'.$fixoReajustado.'" />';
                    $fixoReajustado = '<b style="font-size:13px;color:red;" >'.number_format($fixoReajustado, 2, ',', '.').'</b>';
                    $variavelReajustado = $charge->variavel * ( 1 + ( $adjustmentRate->aliquota / 100) );
                    echo '<input type="hidden" name="variavelReajustado'.$charge->id.'" value="'.$variavelReajustado.'" />';
                    $variavelReajustado = '<b style="font-size:13px;color:red;" >'.number_format($variavelReajustado, 4, ',', '.').'</b>';
                    echo '    - '.$counterName.' fixo: '.$fixoReajustado.' variável: '.$variavelReajustado.' franquia: '.$charge->franquia.'</br>';
                }
                echo '    <br/><br/>';

                echo '    Bonus:<br/>';
                foreach ($bonusArray as $bonus) {
                    $counterName = CounterDAO::GetCounterName($dataConnector->mysqlConnection, $bonus->codigoContador);
                    $valorReajustado = $bonus->valor * ( 1 + ( $adjustmentRate->aliquota / 100) );
                    echo '<input type="hidden" name="valorReajustado'.$bonus->id.'" value="'.$valorReajustado.'" />';
                    $valorReajustado = '<b style="font-size:13px;color:red;" >'.number_format($valorReajustado, 4, ',', '.').'</b>';
                    echo '    - '.$counterName.' de: '.$bonus->de.' até: '.$bonus->ate.' valor: '.$valorReajustado.'</br>';
                }
                echo '    <br/><br/>';

                echo '</fieldset>';
                echo '<div style="clear:both;">';
                echo '    <br/><br/>';
                echo '</div>';
            }        
        ?>

        <div style="max-width:650px;" >
        <span style="color:red; font-weight:bold;" >
            Observação: Faça a conferência de cobranças e bonus no subcontrato após salvar o reajuste.
        </span>
        </div>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <a href="Frontend/<?php echo $currentDir; ?>/editar.php?id=<?php echo $id; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform" >
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
