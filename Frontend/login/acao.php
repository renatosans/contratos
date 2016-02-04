<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/Cipher.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/LoginDAO.php");
include_once("../../DataTransferObjects/LoginDTO.php");
include_once("../../DataAccessObjects/AuthorizationDAO.php");
include_once("../../DataTransferObjects/AuthorizationDTO.php");
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
$loginDAO = new LoginDAO($dataConnector->mysqlConnection);
$loginDAO->showErrors = 1;
$authorizationDAO = new AuthorizationDAO($dataConnector->mysqlConnection);
$authorizationDAO->showErrors = 1;
$actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
$actionLogDAO->showErrors = 1;


// Cria o objeto de criptografia
$cipher = new Cipher();


if( $acao == "store") {
    $id = 0;
    $login = new LoginDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $login = $loginDAO->RetrieveRecord($id); 
    }
    $login->idExterno = $_REQUEST["idExterno"];
    $login->nome      = $_REQUEST["nome"];
    $login->usuario   = $_REQUEST["usuario"];
    $login->senha     = $cipher->GenerateHash($_REQUEST["senha"]);

    $recordId = $loginDAO->StoreRecord($login);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    // Cria as autorizações para o novo usuário
    $functionalityArray = array();
    if (empty($id)) $functionalityArray = $authorizationDAO->RetrieveFunctionalities();
    foreach ($functionalityArray as $index=>$name) {
        $authorization = new AuthorizationDTO();
        $authorization->login_id = $recordId;
        $authorization->funcionalidade = $index;
        $authorization->nivelAutorizacao = 3; // inicia com autorização total, a ser restringida depois pelo gerente

        $authorizationDAO->StoreRecord($authorization);
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if( !isset($_POST['reg']) ){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        // Apaga primeiro as autorizações do usuário
        $authorizationArray = $authorizationDAO->RetrieveRecordArray("login_id=".$reg);
        foreach ($authorizationArray as $authorization) {
            $authorizationDAO->DeleteRecord($authorization->id);
        }

        // Depois exclui o login
        if ( !$loginDAO->DeleteRecord($reg) ) {
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

if ($acao == "bindLogin" ) {
    $login = $loginDAO->RetrieveRecord($_REQUEST["id"]);
    $login->idExterno = $_REQUEST["idExterno"]; 

    $recordId = $loginDAO->StoreRecord($login);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if( $acao == "authenticate" ) {
    $username = $_REQUEST["username"];
    $password = $_REQUEST["password"];
    $loginArray = $loginDAO->RetrieveRecordArray("usuario='".$username."'");
    if (sizeof($loginArray) != 1) {
        echo "Não foi possível encontrar seu cadastro!";
        exit;
    }

    $login = $loginArray[0];
    if ($login->senha != $cipher->GenerateHash($password)) {
        echo "Senha inválida!";
        exit;
    }

    $_SESSION["usrID"] = $login->id;
    $_SESSION["nomeUsr"] = $login->nome;
    $_SESSION["currentPage"] = $_SERVER["REQUEST_URI"];
    $_SESSION["lastPage"] = $_SERVER["REQUEST_URI"];
    if (!empty($login->idExterno)) $_SESSION["slpCode"] = $login->idExterno;

    // Grava no histórico a ação
    $actionLog = new ActionLogDTO();
    $actionLog->transacao = 'login no sistema';
    $actionLog->tipoObjeto = "trace";
    $actionLogDAO->StoreRecord($actionLog);

    echo "Acesso permitido, redirecionando...";
    $location = 'principal.php'; if (!empty($login->idExterno)) $location = "contratos.php?slpCode=".$login->idExterno;
    echo "<script>window.location='".$location."'</script>";
}

if( $acao == "changePassword" ) {
    $username     = $_REQUEST["username"];
    $oldPassword  = $_REQUEST["oldPassword"];
    $newPassword  = $_REQUEST["newPassword"];
    $confirmation = $_REQUEST["confirmation"];

    $loginArray = $loginDAO->RetrieveRecordArray("usuario='".$username."'");
    if (sizeof($loginArray) != 1) {
        echo "Não foi possível encontrar seu cadastro!";
        exit;
    }

    $login = $loginArray[0];
    if ($login->senha != $cipher->GenerateHash($oldPassword)) {
        echo "A antiga senha não confere!";
        exit;
    }

    if (empty($newPassword)) {
        echo "Favor digitar a nova senha!";
        exit;
    }

    if ($newPassword != $confirmation) {
        echo "Digite a nova senha na caixa de confirmação também!";
        exit;
    }

    $login->usuario = $username;
    $login->senha   = $cipher->GenerateHash($newPassword);
    $recordId = $loginDAO->StoreRecord($login);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}


// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
