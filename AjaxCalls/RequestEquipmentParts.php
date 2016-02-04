<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
	include_once("../ClassLibrary/XPertMailer/SMTP.php");
    include_once("../DataAccessObjects/ServiceCallDAO.php");
    include_once("../DataTransferObjects/ServiceCallDTO.php");
    include_once("../DataAccessObjects/ExpenseDAO.php");
    include_once("../DataTransferObjects/ExpenseDTO.php");
    include_once("../DataAccessObjects/PartRequestDAO.php");
    include_once("../DataTransferObjects/PartRequestDTO.php");
    include_once("../DataAccessObjects/RequestItemDAO.php");
    include_once("../DataTransferObjects/RequestItemDTO.php");
    include_once("../DataAccessObjects/SmtpServerDAO.php");
    include_once("../DataTransferObjects/SmtpServerDTO.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/EmployeeDAO.php");
    include_once("../DataTransferObjects/EmployeeDTO.php");


    $recipients = $_GET['recipients'];
    $serviceCallId = $_GET['serviceCallId'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Busca os dados do chamado
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $serviceCall = $serviceCallDAO->RetrieveRecord($serviceCallId);

    // Busca os dados do técnico
    $technicianName = "";
    $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
    $employeeDAO->showErrors = 1;
    $employee = $employeeDAO->RetrieveRecord($serviceCall->tecnico);
    if ($employee != null) {
        $technicianName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
    }

    // Traz as informações do equipamento
    $shortDescription = '';
    $model = '';
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $equipment = $equipmentDAO->RetrieveRecord($serviceCall->codigoCartaoEquipamento);
    if ($equipment != null) {
        $shortDescription = EquipmentDAO::GetShortDescription($equipment);
        $model = $equipment->itemName;
    }

    // Traz as despesas com peças para o chamado de serviço
    $pecas = "";
    $expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
    $expenseDAO->showErrors = 1;
    $expenseArray = $expenseDAO->RetrieveRecordArray("codigoChamado = ".$serviceCall->id." AND codigoInsumo IS NULL");
    if (sizeof($expenseArray) > 0)
    {
        foreach ($expenseArray as $equipmentPart) {
            if (!empty($pecas)) $pecas = $pecas."<br/>";
            $pecas = $pecas.$equipmentPart->quantidade." ".$equipmentPart->nomeItem;
        }
    }
    if (empty($pecas)) $pecas = "NENHUM";

    // Recupera as solicitações anteriores para este chamado (as revisões da solicitação de peças)
    $partRequestDAO = new PartRequestDAO($dataConnector->mysqlConnection);
    $partRequestDAO->showErrors = 1;
    $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
    $requestItemDAO->showErrors = 1;
    $partRequestArray = $partRequestDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCall->id." ORDER BY data DESC");
    $revisions = '';
    foreach ($partRequestArray as $partRequest) {
        $itens = "";
        $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoPecaReposicao_id=".$partRequest->id);
        foreach ($requestItemArray as $requestItem) {
            if (!empty($itens)) $itens = $itens."<br/>";
            $itens = $itens.$requestItem->quantidade.' '.$requestItem->nomeItem;
        }
        if (empty($itens)) $itens = "NENHUM";
        $reqNumber = 'Número da solicitação: '.str_pad($partRequest->id, 5, '0', STR_PAD_LEFT).'<br/>';
        $reqDate   = 'Data: '.$partRequest->data.' '.$partRequest->hora.'<br/>';
        $revisions = $reqNumber.$reqDate.'<div style="border:1px solid black; min-height:100px;" >'.$itens.'</div><br/>'.$revisions;
    }

    // Grava os dados da solicitação de peças no banco de dados
    $partRequest = new PartRequestDTO();
    $partRequest->codigoChamadoServico  = $serviceCall->id;
    $partRequest->data                  = date("Y-m-d",time());
    $partRequest->hora                  = date("H:i",time());
    $partRequest->destinatarios         = $recipients;
    $partRequestId = $partRequestDAO->StoreRecord($partRequest);
    foreach ($expenseArray as $equipmentPart) {
        $requestItem = new RequestItemDTO();
        $requestItem->codigoPedidoPecaRepos  = $partRequestId;
        $requestItem->codigoItem             = $equipmentPart->codigoItem;
        $requestItem->nomeItem               = $equipmentPart->nomeItem;
        $requestItem->quantidade             = $equipmentPart->quantidade;
        $requestItem->total                  = $equipmentPart->totalDespesa;
        $requestItemDAO->StoreRecord($requestItem);
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
    $subj = 'Solicitação de peças para equipamento';
    $content = '<br/><h1>SOLICITAÇÃO DE PEÇAS</h1>';
    $content = $content.'<div style="clear:both;"><br/><br/></div>';
    $content = $content.'Número da solicitação: '.str_pad($partRequestId, 5, '0', STR_PAD_LEFT).'<br/>';
    $content = $content.'Chamado de Serviço: '.str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT).'<br/>';
    $content = $content.'Data do Chamado: '.$serviceCall->dataAbertura.'<br/>';
    $content = $content.'Técnico: '.$technicianName.'<br/>';
    $content = $content.'Cliente: '.BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $serviceCall->businessPartnerCode).'<br/>';
    $content = $content.'Equipamento: '.$shortDescription.'<br/>';
    $content = $content.'Modelo: '.$model.'<br/><br/>';
    $content = $content.'<h3>Itens</h3> <div style="border:1px solid black; min-height:100px;" >'.$pecas.'</div><br/><br/>';
    $content = $content.'<h3>Revisões</h3><br/>'.$revisions;


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
