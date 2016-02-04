<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/InvoiceDAO.php");
include_once("../../DataTransferObjects/InvoiceDTO.php");
include_once("../../DataAccessObjects/BillingDAO.php");
include_once("../../DataTransferObjects/BillingDTO.php");
include_once("../../DataAccessObjects/MailingDAO.php");
include_once("../../DataTransferObjects/MailingDTO.php");
include_once("../../DataAccessObjects/BillingItemDAO.php");
include_once("../../DataTransferObjects/BillingItemDTO.php");
include_once("../../DataAccessObjects/ContractItemDAO.php");
include_once("../../DataTransferObjects/ContractItemDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria os objetos de mapeamento objeto-relacional
$invoiceDAO = new InvoiceDAO($dataConnector->sqlserverConnection);
$invoiceDAO->showErrors = 1;
$billingDAO = new BillingDAO($dataConnector->mysqlConnection);
$billingDAO->showErrors = 1;
$mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
$mailingDAO->showErrors = 1;
$billingItemDAO = new BillingItemDAO($dataConnector->mysqlConnection);
$billingItemDAO->showErrors = 1;
$contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
$contractItemDAO->showErrors = 1;


if( $acao == "store") {
    $mailing_id = $_REQUEST["mailingId"];
    $startDate = $_REQUEST["dataInicial"];
    $endDate = $_REQUEST["dataFinal"];
    $mailing = $mailingDAO->RetrieveRecord($mailing_id);
    $billingParams = 'businessPartnerCode='.$mailing->businessPartnerCode.'&contractId='.$mailing->contrato_id.'&subContractId='.$mailing->subContrato_id.'&startDate='.$startDate.'&endDate='.$endDate.'&acrescimo=&obs=';
    $report = "faturamentoContrato.php";
    if (empty($mailing->contrato_id)) {
        $report = "faturamentoCliente.php";
    }
    $reportUrl = 'http://'.$_SERVER['HTTP_HOST'].$root.'/Frontend/mailing/'.$report.'?'.$billingParams.'&valuesOnly=true';
    $billingContent = file_get_contents($reportUrl);
    $billingContent = str_replace('&nbsp;', ' ', $billingContent);
    $xml = simplexml_load_string('<root>'.$billingContent.'</root>');

    $itemCount = 0;
    $total = 0;
    $rows = $xml[0];
    foreach ($rows as $row) {
        if (sizeof($row) == 1) continue; // pula o cabeçalho ( linha contendo os dados equipamento )

        $itemCount++;
        $total += (float)$row->td[13]; // adiciona o preço do item ao total
    }
    if ($itemCount == 0) $itemCount = 1;


    $billing = new BillingDTO();
    $billing->businessPartnerCode = $_REQUEST["businessPartnerCode"];
    $billing->businessPartnerName = $_REQUEST["businessPartnerName"];
    $billing->mailing_id          = $_REQUEST["mailingId"];
    $billing->dataInicial         = $_REQUEST["dataInicial"];
    $billing->dataFinal           = $_REQUEST["dataFinal"];
    $billing->mesReferencia       = $_REQUEST["mesReferencia"];
    $billing->anoReferencia       = $_REQUEST["anoReferencia"];
    $billing->multaRecisoria      = $_REQUEST["multaRecisoria"];
    $billing->acrescimoDesconto   = $_REQUEST["acrescimoDesconto"];
    $billing->total               = $total;
    $billing->obs                 = $_REQUEST["obs"];

    $recordId = $billingDAO->StoreRecord($billing);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    // Calcula o acrescimo/desconto da fatura distribuido para os itens da fatura
    $acrescimoDescontoIndividual = $billing->acrescimoDesconto / $itemCount;

    foreach ($rows as $row) {
        if (sizeof($row) == 1) continue; // pula o cabeçalho ( linha contendo os dados equipamento )

        // Busca o item correspondente no contrato
        $contractItem = $contractItemDAO->RetrieveRecord($row->td[0]);

        $billingItem = new BillingItemDTO();
        $billingItem->codigoFaturamento       = $recordId;
        $billingItem->contrato_id             = $contractItem->codigoContrato;
        $billingItem->subContrato_id          = $contractItem->codigoSubContrato;
        $billingItem->codigoCartaoEquipamento = $row->td[0];
        $billingItem->tipoLocacao             = $row->td[1];
        $billingItem->counterId               = $row->td[2];
        $billingItem->dataLeitura             = ($row->td[3] == "Sem leitura") ? 0 : $row->td[3];
        $billingItem->medicaoFinal            = ($row->td[4] == "Sem leitura") ? 0 : $row->td[4];
        $billingItem->medicaoInicial          = ($row->td[5] == "Sem leitura") ? 0 : $row->td[5];
        $billingItem->consumo                 = $row->td[6];
        $billingItem->ajuste                  = $row->td[7];
        $billingItem->franquia                = $row->td[8];
        $billingItem->excedente               = $row->td[9];
        $billingItem->tarifaSobreExcedente    = $row->td[10];
        $billingItem->fixo                    = $row->td[11];
        $billingItem->variavel                = $row->td[12];
        $billingItem->total                   = $row->td[13];
        $billingItem->acrescimoDesconto       = $acrescimoDescontoIndividual;

        $billingItemDAO->StoreRecord($billingItem);
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    $id = $_REQUEST['id'];

    $invoiceNum = 0;
    $invoiceArray = $invoiceDAO->RetrieveRecordArray(null, "U_demFaturamento = '".$id."'");
    if (sizeof($invoiceArray) > 0) {
        $invoice = $invoiceArray[0];
        $invoiceNum = $invoice->docNum;
    }

    if ($invoiceNum > 0) {
        echo "Não é possível excluir este faturamento pois a fatura NF ".$invoice->serial." faz referência a ele.";
        echo "Modifique a nota fiscal para que aponte para outro faturamento e tente novamente.";
        exit;
    }

    if( !$billingDAO->DeleteRecord($id) ){
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
