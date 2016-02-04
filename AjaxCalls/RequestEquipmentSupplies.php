<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../ClassLibrary/XPertMailer/SMTP.php");
    include_once("../DataAccessObjects/SupplyRequestDAO.php");
    include_once("../DataTransferObjects/SupplyRequestDTO.php");
    include_once("../DataAccessObjects/RequestItemDAO.php");
    include_once("../DataTransferObjects/RequestItemDTO.php");
    include_once("../DataAccessObjects/SmtpServerDAO.php");
    include_once("../DataTransferObjects/SmtpServerDTO.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/CounterDAO.php");
    include_once("../DataTransferObjects/CounterDTO.php");
    include_once("../DataAccessObjects/ReadingDAO.php");
    include_once("../DataTransferObjects/ReadingDTO.php");


    $recipients = $_GET['recipients'];
    $supplyRequestId = $_GET['supplyRequestId'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Busca os dados da solicitação de consumível
    $supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
    $supplyRequestDAO->showErrors = 1;
    $supplyRequest = $supplyRequestDAO->RetrieveRecord($supplyRequestId);

    // Traz as informações do equipamento
    $shortDescription = '';
    $model = '';
    $businessPartnerCode = '';
    $address = '';
    $instLocation = '';
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $equipment = $equipmentDAO->RetrieveRecord($supplyRequest->codigoCartaoEquipamento);
    if ($equipment != null) {
        $shortDescription = EquipmentDAO::GetShortDescription($equipment);
        $model = $equipment->itemName;
        $businessPartnerCode = $equipment->customer;
        $address = $equipment->addressType." ".$equipment->street." ".$equipment->streetNo." ".$equipment->building."   CEP: ".$equipment->zip;
        $address = $address."   "."Bairro: ".$equipment->block."   ".$equipment->city." ".$equipment->state." ".$equipment->country;
        $instLocation = $equipment->instLocation;
    }

    // Recupera os itens da solicitação
    $itens = "";
    $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
    $requestItemDAO->showErrors = 1;
    $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$supplyRequest->id);
    foreach ($requestItemArray as $requestItem) {
        if (!empty($itens)) $itens = $itens."<br/>";
        $itens = $itens.$requestItem->quantidade.' '.$requestItem->nomeItem;
    }
    if (empty($itens)) $itens = "NENHUM";

    // Recupera os contadores da solicitação
    $contadores = "";
    $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
    $counterDAO->showErrors = 1;
    $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
    $readingDAO->showErrors = 1;
    $readingArray = $readingDAO->RetrieveRecordArray("consumivel_id=".$supplyRequestId);
    foreach($readingArray as $reading) {
        $counter = $counterDAO->RetrieveRecord($reading->codigoContador);

        if (!empty($contadores)) $contadores = $contadores."<br/>";
        $contadores = $contadores.$counter->nome.': '.$reading->contagem.'  '.$reading->observacao;
    }

    // Busca o servidor default para envio de email
    $smtpServerDAO = new SmtpServerDAO($dataConnector->mysqlConnection);
    $smtpServerDAO->showErrors = 1;
    $serverArray = $smtpServerDAO->RetrieveRecordArray("defaultServer = 1");
    if (sizeof($serverArray) != 1) {
        echo 'Falha no envio. O servidor de envio (SMTP) não foi configurado.';
        exit;
    }
    $defaultServer = $serverArray[0]; 
    $host = $defaultServer->endereco;
    $port = (int)$defaultServer->porta;
    $user = $defaultServer->usuario;
    $pass = $defaultServer->senha;
    $vssl = null; if ($defaultServer->requiresTLS) $vssl = 'tls';
    $extensionLoaded = extension_loaded('openssl');
    if ($vssl != null && !$extensionLoaded) {
        echo 'Falha no envio. A extensão Open SSL não está habilitada no PHP.';
        exit;
    }

    error_reporting(E_ALL); // manage php errors

    $from = trim($user);
    $to   = trim($recipients);
    $toArray = explode(",", $recipients);
    $subj = 'Solicitação de consumíveis';
    $content = '<br/><h1>SOLICITAÇÃO DE CONSUMÍVEIS</h1>';
    $content = $content.'<div style="clear:both;"><br/><br/></div>';
    $content = $content.'Número da solicitação: '.str_pad($supplyRequest->id, 5, '0', STR_PAD_LEFT).'<br/>';
    $content = $content.'Equipamento: '.$shortDescription.'<br/>';
    $content = $content.'Modelo: '.$model.'<br/>';
    $content = $content.'Endereço: '.$address.'<br/>';
    $content = $content.'Local: '.$instLocation.'<br/>';
    $content = $content.'Cliente: '.BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $businessPartnerCode).'<br/>';
    $content = $content.'Observações: '.$supplyRequest->observacao.'<br/><br/>';
    $content = $content.'<b>Itens</b> <div style="border:1px solid black; min-height:100px;" >'.$itens.'</div><br/>';
    $content = $content.'<b>Contadores</b> <div style="border:1px solid black; min-height:100px;" >'.$contadores.'</div><br/>';

    $message = MIME::message($content, 'text/html', null, 'UTF-8');
    // compose message in MIME format
    $mess = MIME::compose(null, $message);
    // standard mail message RFC2822
    $body = 'From: '.$from."\r\n".'To: '.$to."\r\n".'Subject: '.$subj."\r\n".$mess['header']."\r\n\r\n".$mess['content'];

    $conn = SMTP::connect($host, $port, $user, $pass, $vssl) or die('Falha na conexão - '.print_r($_RESULT));
    $sent = SMTP::send($conn, array($toArray[0]), $body, $from);
    if ($sent) echo 'Email enviado com sucesso!'; else echo 'Falha no envio - '.print_r($_RESULT);
    SMTP::disconnect($conn);


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
