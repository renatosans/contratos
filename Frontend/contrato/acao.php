<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/AdjustmentDAO.php");
include_once("../../DataTransferObjects/AdjustmentDTO.php");
include_once("../../DataAccessObjects/AdjustmentRateDAO.php");
include_once("../../DataTransferObjects/AdjustmentRateDTO.php");
include_once("../../DataAccessObjects/SubContractDAO.php");
include_once("../../DataTransferObjects/SubContractDTO.php");
include_once("../../DataAccessObjects/ContractItemDAO.php");
include_once("../../DataTransferObjects/ContractItemDTO.php");
include_once("../../DataAccessObjects/ContractChargeDAO.php");
include_once("../../DataTransferObjects/ContractChargeDTO.php");
include_once("../../DataAccessObjects/ContractBonusDAO.php");
include_once("../../DataTransferObjects/ContractBonusDTO.php");
include_once("../../DataAccessObjects/ActionLogDAO.php");
include_once("../../DataTransferObjects/ActionLogDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria os objetos de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;
$actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
$actionLogDAO->showErrors = 1;


if( $acao == "store" ) {
    $id = 0;
    $oldContract = new ContractDTO();
    $newContract = new ContractDTO();
	$transactionType = "INSERT";
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
        $id = $_REQUEST["id"];
        $oldContract = $contractDAO->RetrieveRecord($id);
        $newContract = $contractDAO->RetrieveRecord($id);
		$transactionType = "UPDATE"; 
    }
    $lastMonth = mktime(0,0,0,date("m")-1,1,date("Y"));

    $newContract->numero                    = $_REQUEST["numero"];
    $newContract->pn                        = $_REQUEST["pn"];
    $newContract->divisao                   = $_REQUEST["divisao"];
    if (isset($_REQUEST["contato"]) ) $newContract->contato = $_REQUEST["contato"];
    $newContract->status                    = $_REQUEST["status"];
    $newContract->categoria                 = $_REQUEST["categoria"];
    $newContract->dataAssinatura            = $_REQUEST["assinatura"];
    $newContract->dataEncerramento          = $_REQUEST["encerramento"];
    $newContract->inicioAtendimento         = $_REQUEST["inicioAtendimento"];
    $newContract->fimAtendimento            = $_REQUEST["fimAtendimento"];
    $newContract->primeiraParcela           = $_REQUEST["primeiraParcela"];
    $newContract->parcelaAtual              = $_REQUEST["parcelaAtual"];
    $newContract->mesReferencia             = $_REQUEST["mesReferencia"];
    if (empty($newContract->mesReferencia)) $newContract->mesReferencia = date("m", $lastMonth);
    $newContract->anoReferencia             = $_REQUEST["anoReferencia"];
    if (empty($newContract->anoReferencia)) $newContract->anoReferencia = date("Y", $lastMonth);
    $newContract->quantidadeParcelas        = $_REQUEST["quantidadeParcelas"];
    $newContract->global = 0; if (isset($_REQUEST["global"])) $newContract->global = 1;
    $newContract->vendedor                  = $_REQUEST["vendedor"];
    $newContract->diaVencimento             = $_REQUEST["diaVencimento"];
    $newContract->referencialVencimento     = $_REQUEST["referencialVencimento"];
    $newContract->diaLeitura                = $_REQUEST["diaLeitura"];
    $newContract->referencialLeitura        = $_REQUEST["referencialLeitura"];
    $newContract->indiceReajuste            = $_REQUEST["indicesReajuste_id"];
    $newContract->dataRenovacao             = $_REQUEST["dataRenovacao"];
    $newContract->dataReajuste              = $_REQUEST["dataReajuste"];
    $newContract->valorImplantacao          = $_REQUEST["valorImplantacao"];
    $newContract->quantParcelasImplantacao  = $_REQUEST["quantParcelasImplantacao"];
    $newContract->obs                       = $_REQUEST["comments"];


    $recordId = $contractDAO->StoreRecord($newContract);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "renew" ) {
    $id = $_REQUEST["id"];
    $transactionType = "UPDATE"; 
    $oldContract = $contractDAO->RetrieveRecord($id);
    $newContract = $contractDAO->RetrieveRecord($id);

    $newContract->status = 5; // Renovado
    $newContract->dataRenovacao         = $_REQUEST["dataRenovacao"];
    $newContract->dataEncerramento      = $_REQUEST["encerramento"];
    $newContract->primeiraParcela       = $_REQUEST["primeiraParcela"];
    $newContract->parcelaAtual          = $_REQUEST["parcelaAtual"];
    $newContract->quantidadeParcelas    = $_REQUEST["quantidadeParcelas"];
    $newContract->fimAtendimento        = $_REQUEST["fimAtendimento"];

    $recordId = $contractDAO->StoreRecord($newContract);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "adjustment" ) {
    $id = $_REQUEST["id"];
    $transactionType = "UPDATE";
    $oldContract = $contractDAO->RetrieveRecord($id);
    $newContract = $contractDAO->RetrieveRecord($id);

    $newContract->status = 6; // Reajustado
    $newContract->dataReajuste = date("Y-m-d H:i:s",time());

    $recordId = $contractDAO->StoreRecord($newContract);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    $adjustmentRateDAO = new AdjustmentRateDAO($dataConnector->mysqlConnection);
    $adjustmentRateDAO->showErrors = 1;
    $adjustmentRate = $adjustmentRateDAO->RetrieveRecord($newContract->indiceReajuste);
    
    $adjustment = new AdjustmentDTO();
    $adjustment->contrato_id = $id;
    $adjustment->data = date("Y-m-d",time());
    $adjustment->indiceUtilizado = $adjustmentRate->sigla.' - '.$adjustmentRate->nome;
    $adjustment->aliquotaUtilizada = $adjustmentRate->aliquota;
    $adjustmentDAO = new AdjustmentDAO($dataConnector->mysqlConnection);
    $adjustmentDAO->showErrors = 1;
    $adjustmentDAO->StoreRecord($adjustment);

    $contractChargeDAO = new ContractChargeDAO($dataConnector->mysqlConnection);
    $contractChargeDAO->showErrors = 1;
    $contractChargeArray = $contractChargeDAO->RetrieveRecordArray("contrato_id=".$id);
    foreach ($contractChargeArray as $contractCharge) {
        if (isset($_REQUEST["fixoReajustado".$contractCharge->id]) && isset($_REQUEST["variavelReajustado".$contractCharge->id])) {
            if (!$contractChargeDAO->DeleteRecord($contractCharge->id)) {
                echo "Não foi possivel efetuar a operação...";
                exit;
            }
            $updatedCharge = new ContractChargeDTO();
            $updatedCharge->codigoContrato    = $contractCharge->codigoContrato;
            $updatedCharge->codigoSubContrato = $contractCharge->codigoSubContrato;
            $updatedCharge->codigoContador    = $contractCharge->codigoContador;
            $updatedCharge->modalidadeMedicao = $contractCharge->modalidadeMedicao;
            $updatedCharge->fixo              = $_REQUEST["fixoReajustado".$contractCharge->id];
            $updatedCharge->variavel          = $_REQUEST["variavelReajustado".$contractCharge->id];
            $updatedCharge->franquia          = $contractCharge->franquia;
            $updatedCharge->individual        = $contractCharge->individual;
            $chargeId = $contractChargeDAO->StoreRecord($updatedCharge);
            if ($chargeId == null) {
                echo "Não foi possivel efetuar a operação...";
                exit;
            }
            $actionLog = new ActionLogDTO($transactionType, 'cobranca', $chargeId);
            $actionLog->tipoAgregacao = 'contrato';
            $actionLog->idAgregacao   = $id;
            $actionLogDAO->StoreRecord($actionLog);
        }
    }

    $contractBonusDAO = new ContractBonusDAO($dataConnector->mysqlConnection);
    $contractBonusDAO->showErrors = 1;
    $contractBonusArray = $contractBonusDAO->RetrieveRecordArray("contrato_id=".$id);
    foreach ($contractBonusArray as $contractBonus) {
        if (isset($_REQUEST["valorReajustado".$contractBonus->id])) {
            if (!$contractBonusDAO->DeleteRecord($contractBonus->id)) {
                echo "Não foi possivel efetuar a operação...";
                exit;
            }
            $updatedBonus = new ContractBonusDTO();
            $updatedBonus->codigoContrato    = $contractBonus->codigoContrato;
            $updatedBonus->codigoSubContrato = $contractBonus->codigoSubContrato;
            $updatedBonus->codigoContador    = $contractBonus->codigoContador;
            $updatedBonus->de                = $contractBonus->de;
            $updatedBonus->ate               = $contractBonus->ate;
            $updatedBonus->valor             = $_REQUEST["valorReajustado".$contractBonus->id];
            $bonusId = $contractBonusDAO->StoreRecord($updatedBonus);
            if ($bonusId == null) {
                echo "Não foi possivel efetuar a operação...";
                exit;
            }
            $actionLog = new ActionLogDTO($transactionType, 'bonus', $bonusId);
            $actionLog->tipoAgregacao = 'contrato';
            $actionLog->idAgregacao   = $id;
            $actionLogDAO->StoreRecord($actionLog);
        }
    }

    echo 'Operação efetuada com sucesso!';
}

if ( $acao == "remove" ) {
    $transactionType = "DELETE";

    if(!isset($_POST['reg'])){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        $subContractEnumeration = SubContractDAO::GetSubcontractsByOwner($dataConnector->mysqlConnection, $reg);
        $itemArray = ContractItemDAO::GetItemsByOwner($dataConnector->mysqlConnection, $subContractEnumeration);
        $itemCount = sizeof($itemArray);
        if ($itemCount != 0) {
            echo "É necessário excluir os itens de contrato antes de prosseguir.";
            exit;
        }
        if( !$contractDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";

}

// Grava no histórico a ação
if ($transactionType == "INSERT") {
    $actionLog = new ActionLogDTO($transactionType, 'contrato', $recordId);
    $actionLogDAO->StoreRecord($actionLog);
}
if ($transactionType == "UPDATE") {
    foreach ($newContract as $propertyName => $value) {
        if ($newContract->{$propertyName} != $oldContract->{$propertyName}) {
            $actionLog = new ActionLogDTO($transactionType, 'contrato', $recordId);
            $actionLog->propriedade = $propertyName;
            $actionLog->valor = $value;
            $actionLogDAO->StoreRecord($actionLog);
        }
    }
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
