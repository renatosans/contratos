<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SmtpServerDAO.php");
include_once("../../DataTransferObjects/SmtpServerDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["administracaoSistema"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$smtpServerDAO = new SmtpServerDAO($dataConnector->mysqlConnection);
$smtpServerDAO->showErrors = 1;

$id = 0;
$smtpServer = new SmtpServerDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
    $id = $_REQUEST["id"];
    $smtpServer = $smtpServerDAO->RetrieveRecord($id);
}

?>

    <h1>Dados do Servidor Smtp</h1>
    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <label>Nome<br />
        <input type="text" name="nome" size="65" value="<?php echo $smtpServer->nome; ?>" />
        </label>

        <label>Endereço<br />
        <input type="text" name="endereco" size="65" value="<?php echo $smtpServer->endereco; ?>" />
        </label>

        <label>Porta<br />
        <input type="text" name="porta" size="65" value="<?php echo $smtpServer->porta; ?>" />
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label>Requer TLS/SSL<br/><br/>
        <input type="checkbox" name="requiresTLS" <?php if ($smtpServer->requiresTLS) echo 'checked="checked"'; ?> > Transport Layer Security </input>
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label>Usuário<br />
        <input type="text" name="usuario" size="65" value="<?php echo $smtpServer->usuario; ?>" />
        </label>
        
        <label>Senha<br />
        <input type="password" name="senha" size="65" value="<?php echo $smtpServer->senha; ?>" />
        </label>
        
        <input type="hidden" name="defaultServer" value="<?php echo $smtpServer->defaultServer; ?>" />
        
        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform">
            Salvar
        </button>
    </form>

<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
