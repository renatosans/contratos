<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/MailingDAO.php");
include_once("../../DataTransferObjects/MailingDTO.php");
include_once("../../DataAccessObjects/BillingDAO.php");
include_once("../../DataTransferObjects/BillingDTO.php");


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

// Cria o objeto de mapeamento objeto-relacional
$mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
$mailingDAO->showErrors = 1;


if( $acao == "store") {
    $id = 0;
    $mailing = new MailingDTO();
    // define uma data inicial menor que a data corrente, a alteração do último envio é feita pelo serviço de envio(Billing Mailer)
    $mailing->ultimoEnvio          = '2001-01-01 01:01:01';
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $mailing = $mailingDAO->RetrieveRecord($id);
    }
    $mailing->businessPartnerCode  = $_REQUEST["businessPartnerCode"];
    $mailing->businessPartnerName  = $_REQUEST["businessPartnerName"];
    $mailing->contrato_id          = $_REQUEST["contrato_id"];
    $mailing->subContrato_id       = $_REQUEST["subContrato_id"];
    $mailing->diaFaturamento       = $_REQUEST["diaFaturamento"];
    $mailing->destinatarios        = $_REQUEST["destinatarios"];
    $mailing->enviarDemonstrativo  = $_REQUEST["enviarDemonstrativo"];

    $recordId = $mailingDAO->StoreRecord($mailing);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if(!isset($_POST['reg'])){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        // Verifica as dependências do mailing
        $billingDAO = new BillingDAO($dataConnector->mysqlConnection);
        $billingDAO->showErrors = 1;
        $billingArray = $billingDAO->RetrieveRecordArray("mailing_id=".$reg);
        if (sizeof($billingArray) > 0) {
            echo 'O registro não pode ser excluído pois está amarrado a demonstrativos. Exclua essas dependências primeiro.';
            exit;
        }

        if( !$mailingDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
