<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SmtpServerDAO.php");
include_once("../../DataTransferObjects/SmtpServerDTO.php");


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
$smtpServerDAO = new SmtpServerDAO($dataConnector->mysqlConnection);
$smtpServerDAO->showErrors = 1;


function ClearDefault($smtpServerDAO) {
    $serverArray = $smtpServerDAO->RetrieveRecordArray();
    foreach ($serverArray as $smtpServer) {
        // Desmarca todos os servidores (defaultServer = false)
        $smtpServer->defaultServer = false;
        $smtpServerDAO->StoreRecord($smtpServer);
    }
}


if ( $acao == "makeDefault" ) {
    // Desmarca todos os outros servidores
    ClearDefault($smtpServerDAO);

    // Marca o servidor definido pelo usuário como default
    $servidorDefault = $_REQUEST["servidorDefault"];
    $smtpServer = $smtpServerDAO->RetrieveRecord($servidorDefault);
    $smtpServer->defaultServer = true;

    $recordId = $smtpServerDAO->StoreRecord($smtpServer);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "store") {
    $id = 0;
    $smtpServer = new SmtpServerDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $smtpServer = $smtpServerDAO->RetrieveRecord($id); 
    }
    $smtpServer->nome           = $_REQUEST["nome"];
    $smtpServer->endereco       = $_REQUEST["endereco"];
    $smtpServer->porta          = $_REQUEST["porta"];
    $smtpServer->usuario        = $_REQUEST["usuario"];
    $smtpServer->senha          = $_REQUEST["senha"]; // não encriptar pois o valor precisa ser informado a outro sistema
    $smtpServer->requiresTLS    = false;  if (isset($_REQUEST["requiresTLS"])) $smtpServer->requiresTLS = true;
    $smtpServer->defaultServer  = $_REQUEST["defaultServer"];

    if ($id == 0)
    {
        ClearDefault($smtpServerDAO);
        $smtpServer->defaultServer = true;
    }

    $recordId = $smtpServerDAO->StoreRecord($smtpServer);
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
        if( !$smtpServerDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
