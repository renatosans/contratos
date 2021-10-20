<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/LoginDAO.php");
include_once("../../DataTransferObjects/LoginDTO.php");


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
$loginDAO = new LoginDAO($dataConnector->mysqlConnection);
$loginDAO->showErrors = 1;

$id = 0;
$login = new LoginDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $login = $loginDAO->RetrieveRecord($id);
}

?>

    <script type="text/javascript" >
        $(document).ready(function() {
            $('form').validate({rules: { nome:"required", usuario:"required", senha:"required" }});
        });
    </script>

    <h1>Administração - Login</h1>
    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $login->id; ?>" />
        <input type="hidden" name="idExterno" value="<?php echo $login->idExterno; ?>" />

        <label>Nome<br />
        <input type="text" name="nome" size="65" value="<?php echo $login->nome; ?>" />
        </label>

        <label>Usuário<br />
        <input type="text" name="usuario" size="65" maxlength="100" value="<?php echo $login->usuario; ?>" />
        </label>

        <label>Senha<br />
            <input type="password" name="senha" size="65" value="" />
        </label>
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
