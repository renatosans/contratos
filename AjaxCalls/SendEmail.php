<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/XPertMailer/SMTP.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/SmtpServerDAO.php");
    include_once("../DataTransferObjects/SmtpServerDTO.php");


    $subject = $_REQUEST['subject'];
    $mailBody = $_REQUEST['mailBody'];
    $recipients = $_REQUEST['recipients'];
    $fileCount = $_REQUEST['fileCount'];
    $fileIndex = 0;
    $attachmentFiles = array();
    while ($fileIndex < $fileCount)
    {
        $attachmentFiles[$_REQUEST['filename'.$fileIndex]] = $_REQUEST['path'.$fileIndex];
        $fileIndex++;
    }

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('mySql');
    $dataConnector->OpenConnection();
    if ($dataConnector->mysqlConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
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
    $subj = trim($subject);
    $content = trim($mailBody);
    $content = nl2br($content);

    $message = MIME::message($content, 'text/html', null, 'UTF-8');
    $attachment = null;
    foreach ($attachmentFiles as $filename => $path) {
        $attachment[] = MIME::message(file_get_contents($path), FUNC::mime_type($path), $filename, null, 'base64', 'attachment');
    }
    // compose message in MIME format
    $mess = MIME::compose(null, $message, $attachment);
    // standard mail message RFC2822
    $body = 'From: '.$from."\r\n".'To: '.$to."\r\n".'Subject: '.$subj."\r\n".$mess['header']."\r\n\r\n".$mess['content'];

    $conn = SMTP::connect($host, $port, $user, $pass, $vssl) or die('Falha na conexão - '.print_r($_RESULT));
    $sent = SMTP::send($conn, array($toArray[0]), $body, $from);
    if ($sent) echo 'Email enviado com sucesso!'; else echo 'Falha no envio - '.print_r($_RESULT);
    SMTP::disconnect($conn);


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
