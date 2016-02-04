<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/AuthorizationDAO.php");
include_once("../../DataTransferObjects/AuthorizationDTO.php");
include_once("../../DataAccessObjects/LoginDAO.php");
include_once("../../DataTransferObjects/LoginDTO.php");


$action = "";
if (isset($_REQUEST["action"]) && ($_REQUEST["action"] != "")) {
    $action = $_REQUEST["action"];
}

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

// Cria os objetos de mapeamento objeto-relacional
$authorizationDAO = new AuthorizationDAO($dataConnector->mysqlConnection);
$authorizationDAO->showErrors = 1;
$loginDAO = new LoginDAO($dataConnector->mysqlConnection);
$loginDAO->showErrors = 1;


// Traz as autorizações cadastradas no sistema
$authorizationArray = $authorizationDAO->RetrieveRecordArray();

// Traz os logins cadastrados no sistema
$loginArray = $loginDAO->RetrieveRecordArray();

// Traz as funcionalidades existentes  
$functionalityArray = $authorizationDAO->RetrieveFunctionalities();

if ($action == "store") {
    foreach ($authorizationArray as $authorization) {
        if (isset($_REQUEST["nivelAutorizacao".$authorization->id])) {
            $authorization->nivelAutorizacao = $_REQUEST["nivelAutorizacao".$authorization->id];
            $authorizationDAO->StoreRecord($authorization);
        }
    }
    echo 'Operação efetuada com sucesso!';
    exit;
}


?>

    <h1>Administração - Autorizações</h1>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >
        $(document).ready(function() {
            $("#btnSalvar").button({ icons: {primary:'ui-icon-circle-check'} }).click( function() {
                var targetUrl = 'Frontend/<?php echo $currentDir; ?>/gerenciar.php?action=store';
                $.ajax({ type: 'POST', url: targetUrl, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

                // Recarrega a página
                LoadPage('Frontend/<?php echo $currentDir; ?>/gerenciar.php');
            });
        });
    </script>

    <form name="frmAuthorizations" action="Frontend/<?php echo $currentDir; ?>/gerenciar.php" method="post" >
        <table cellpadding="0" cellspacing="0" style="border:1px solid black;" >
            <thead style="font-size: 15px; font-weight:bold; background:#DDD;" >
                <tr>
                    <th>&nbsp;Usuário</th>
                    <th>&nbsp;Funcionalidade</th>
                    <th>&nbsp;Nível de Autorização</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $rowCount = 0;
                foreach ($loginArray as $login) {
                    echo '<tr><td colspan="3"><h1 style="font-size: 15px;">'.$login->usuario.'</h1></td></tr>';
                    $authorizationArray = $authorizationDAO->RetrieveRecordArray("login_id=".$login->id);
                    foreach($authorizationArray as $authorization){
                        $rowCount++;
                        ?>
                        <tr>
                            <td>
                            </td>
                            <td>
                               <?php echo $functionalityArray[$authorization->funcionalidade]; ?>
                            </td>
                            <td>
                                <select name="<?php echo 'nivelAutorizacao'.$authorization->id; ?>" style="width: 210px;" >
                                    <option value="1" <?php if ($authorization->nivelAutorizacao == 1) echo ' selected '; ?>>
                                        Sem permissão
                                    </option>
                                    <option value="2" <?php if ($authorization->nivelAutorizacao == 2) echo ' selected '; ?>>
                                        Somente Leitura
                                    </option>
                                    <option value="3" <?php if ($authorization->nivelAutorizacao == 3) echo ' selected '; ?>>
                                        Permissão Total
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <?php
                    }
                }

                if ($rowCount == 0){
                    echo '<tr><td colspan="4" align="center" >Nenhum registro encontrado!</td></tr>';
                }
            ?>
            </tbody>
        </table>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="button" <?php echo $attributes; ?> id="btnSalvar" >
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
