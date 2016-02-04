<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ActionLogDAO.php");
include_once("../../DataTransferObjects/ActionLogDTO.php");
include_once("../../DataAccessObjects/LoginDAO.php");
include_once("../../DataTransferObjects/LoginDTO.php");
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
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../../DataAccessObjects/AdjustmentRateDAO.php");
include_once("../../DataTransferObjects/AdjustmentRateDTO.php");
include_once("../../DataAccessObjects/ContactPersonDAO.php");
include_once("../../DataTransferObjects/ContactPersonDTO.php");
include_once("../../DataAccessObjects/SalesPersonDAO.php");
include_once("../../DataTransferObjects/SalesPersonDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");


$contractId = 0;
if (isset($_REQUEST["contractId"]) && ($_REQUEST["contractId"] != 0)) {
    $contractId = $_REQUEST["contractId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
$actionLogDAO->showErrors = 1;
$loginDAO = new LoginDAO($dataConnector->mysqlConnection);
$loginDAO->showErrors = 1;
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;
$contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
$contractChargeDAO->showErrors = 1;
$contractBonusDAO = new ContractBonusDAO($dataConnector->mysqlConnection);
$contractBonusDAO->showErrors = 1;


// Traz os dados do contrato
$contract = $contractDAO->RetrieveRecord($contractId);

// Traz o nome do cliente
$clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $contract->pn));

// Traz o historico para o contrato
$actionLogArray = $actionLogDAO->RetrieveRecordArray("(tipoObjeto = 'contrato' AND idObjeto = ".$contractId.") OR (tipoAgregacao = 'contrato' AND idAgregacao = ".$contractId.")");


function BuildRowContents($data, $usuario, $acao) {
    echo '<tr>';
    echo '    <td>'.$data.'</td>';
    echo '    <td>'.$usuario.'</td>';
    echo '    <td>'.$acao.'</td>';
    echo '</tr>';
}

?>

<h1>Contrato: <?php echo str_pad($contract->numero, 5, '0', STR_PAD_LEFT); ?></h1>
<br/>
<h1>Cliente: <?php echo $clientName->Truncate(45); ?></h1>
<h1><?php echo str_pad('_', 50, '_', STR_PAD_LEFT); ?></h1>
<div style="clear:both;">
    <br/><br/>
</div>

<div style="width: 650px;">
<table border="0" cellpadding="0" cellspacing="0" class="sorTable">
    <thead>
        <tr>
            <th style="width: 25%;" >Data</th>
            <th style="width: 25%;" >Usuário</th>
            <th style="width: 45%;" >Alteração</th>
        </tr>
    </thead>
    <tbody>
    <?php
        if (sizeof($actionLogArray) == 0) {
            echo '<tr><td colspan="3" align="center" >Nenhum registro encontrado!</td></tr>';
        }

        foreach ($actionLogArray as $actionLog) {
            $login = $loginDAO->RetrieveRecord($actionLog->login_id);
            $action = '';

            if ($actionLog->tipoObjeto == 'contrato') {
                $newValue = $actionLog->valor;
                $checkBoxCaption = Array("0"=>"desmarcado", "1"=>"marcado");
                if ($actionLog->propriedade == 'status') $newValue = ContractDAO::GetStatusAsText($newValue);
                if ($actionLog->propriedade == 'categoria') $newValue = ContractDAO::GetCategoryAsText($newValue);
                if ($actionLog->propriedade == 'vendedor') $newValue = SalesPersonDAO::GetSalesPersonName($dataConnector->sqlserverConnection, $newValue);
                if ($actionLog->propriedade == 'contato') $newValue = ContactPersonDAO::GetContactPersonName($dataConnector->sqlserverConnection, $newValue);
                if ($actionLog->propriedade == 'indiceReajuste') $newValue = AdjustmentRateDAO::GetAlias($dataConnector->mysqlConnection, $newValue);
                if ($actionLog->propriedade == 'global') $newValue = $checkBoxCaption[$newValue];
                $action = '<u>'.$actionLog->propriedade.'</u>'.' alterado para '.'&quot'.$newValue.'&quot';
                if ($actionLog->transacao == 'INSERT')
                    $action = "Contrato incluido no sistema";    
            }

            if ($actionLog->tipoObjeto == 'itemContrato') {
                $serialNumber = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $actionLog->idObjeto);
                if ($actionLog->transacao == 'INSERT') $action = 'Equipamento '.$serialNumber.' adicionado ao contrato';
                if ($actionLog->transacao == 'DELETE') $action = 'Equipamento '.$serialNumber.' retirado do contrato';
            }

            if ($actionLog->tipoObjeto == 'cobranca') {
                $chargeDescription = "";
                $serialEnumeration = "";
                $charge = $contractChargeDAO->RetrieveRecord($actionLog->idObjeto);
                if ($charge != null) {
                    $counterName = CounterDAO::GetCounterName($dataConnector->mysqlConnection, $charge->codigoContador);
                    // Obtem os parâmetros da cobrança
                    $chargeDescription = $counterName.' ( Fixo: '.$charge->fixo.' Variável: '.$charge->variavel.' Franquia: '.$charge->franquia.' )<br/>';
                    // Localiza os itens do subcontrato
                    $itemArray = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $charge->codigoSubContrato);
                    foreach ($itemArray as $contractItem) {
                        $serialNumber = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $contractItem->codigoCartaoEquipamento);
                        if (!empty($serialEnumeration)) $serialEnumeration = $serialEnumeration.", ";
                        $serialEnumeration = $serialEnumeration.$serialNumber;
                    }
                    if (empty($serialEnumeration)) $serialEnumeration = "(Lista de Itens Vazia)";
                }
                if ($actionLog->transacao == 'INSERT') $action = 'Cobrança '.$chargeDescription.' adicionada aos itens '.$serialEnumeration;
                if ($actionLog->transacao == 'UPDATE') $action = 'Cobrança atualizada '.$chargeDescription.' nos itens '.$serialEnumeration;
                if ($actionLog->transacao == 'DELETE') $action = 'Cobrança retirada dos itens '.$serialEnumeration;    
            }

            if ($actionLog->tipoObjeto == 'bonus') {    
                $bonusDescription = "";
                $serialEnumeration = "";
                $bonus = $contractBonusDAO->RetrieveRecord($actionLog->idObjeto);
                if ($bonus != null) {
                    $counterName = CounterDAO::GetCounterName($dataConnector->mysqlConnection, $bonus->codigoContador);
                    // Obtem os parâmetros do bonus
                    $bonusDescription = $counterName.' ( De: '.$bonus->de.' Até: '.$bonus->ate.' Valor: '.$bonus->valor.' )<br/>';
                    // Localiza os itens do subcontrato
                    $itemArray = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $bonus->codigoSubContrato);
                    foreach ($itemArray as $contractItem) {
                        $serialNumber = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $contractItem->codigoCartaoEquipamento);
                        if (!empty($serialEnumeration)) $serialEnumeration = $serialEnumeration.", ";
                        $serialEnumeration = $serialEnumeration.$serialNumber;
                    }
                    if (empty($serialEnumeration)) $serialEnumeration = "(Lista de Itens Vazia)";
                }
                if ($actionLog->transacao == 'INSERT') $action = 'Bonus '.$bonusDescription.' adicionado aos itens '.$serialEnumeration;
                if ($actionLog->transacao == 'UPDATE') $action = 'Bonus atualizado '.$bonusDescription.' nos itens '.$serialEnumeration;
                if ($actionLog->transacao == 'DELETE') $action = 'Bonus retirado dos itens '.$serialEnumeration;
            }

            BuildRowContents($actionLog->data.' '.$actionLog->hora, $login->usuario, $action);
        }
    ?>
    </tbody>
</table>
</div>
<div class="pager pagerListar">
    <span class="wraper">
        <button class="first">First</button>
    </span>
    <span class="wraper">
        <button class="prev">Prev</button>
    </span>
    <span class="wraper center">
        <input type="text" class="pagedisplay"/>
    </span>
    <span class="wraper">
        <button class="next">Next</button>
    </span>
    <span class="wraper">
        <button class="last">Last</button>
    </span>
    <input type="hidden" class="pagesize" value="10" />
</div>
<div style="clear:both;">
    <br/><br/>
</div>


<a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
    Voltar
</a>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
