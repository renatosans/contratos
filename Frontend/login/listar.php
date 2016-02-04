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

?>
    <h1>Administração - Logins</h1>

    <script type="text/javascript" >
        $(document).ready(function() {
            $("#btnBind").button({ icons: {primary:'ui-icon-transferthick-e-w' } }).click(function() {
                var checkedCount = 0;
                var id = 0;
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) {
                        checkedCount++;
                        id = $(this).val();
                    }
                });
                if ((checkedCount == 0) || (checkedCount > 1)){
                    alert('Marque o login que deseja vincular (somente um).');
                    return;
                }

                var targetUrl = "AjaxCalls/BindLogin.php?loginId=" + id;
                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl).dialog({modal:true, width: 280, height: 160, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
        });
    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post">
        <div class="clear">
            <fieldset>
                <legend>Ações:</legend>
                <a href="#" id="checkall" class="button" >
                    Todos
                </a>
                <a href="#" id="uncheckall" class="button">
                    Nenhum
                </a>
                <!-- Só habilita o botão se o usuário possui o nível máximo de autorização -->
                <?php
                    $attributes = '';
                    $url = 'Frontend/'.$currentDir.'/editar.php?id=0';
                    if ($nivelAutorizacao < 3) {
                        $attributes = 'disabled="disabled"';
                        $url = '#';
                    }
                ?>
                <a <?php echo $attributes; ?> href="<?php echo $url; ?>" class="button">
                    Novo
                </a>
                <button type="submit" <?php echo $attributes; ?> id="btnExcluir" class="button">
                    Excluir
                </button>
                <button type="button" <?php echo $attributes; ?> id="btnBind" >
                    Vincular
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="45" size="45" type="text"/>
                    <button id="filter-clear-button" type="submit" value="Clear">Clear</button>
                </fieldset>
            </div>
        </div>
        <br/>
        <input type="hidden" name="acao" value="remove" />
        <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;Login</th>
                    <th>&nbsp;Nome</th>
                </tr>
            </thead>
            <tbody>
            <?php
                // Traz a lista de logins cadastrados
                $loginArray = $loginDAO->RetrieveRecordArray();
                if (sizeof($loginArray) == 0)
                {
                    echo '<tr><td colspan="3" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($loginArray as $login) {
            ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value="<?php echo $login->id; ?>" name="reg[]"/>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$login->id; ?>" >
                               <?php echo $login->usuario; ?>
                            </a>
                        </td>
                        <td >
                           <?php echo $login->nome; ?>
                        </td>
                    </tr>
            <?php
                }
            ?>
            </tbody>
        </table>
        <div class="pager pagerListar">
                <span class="wraper">
                    <button class="first">First</button>
                </span>
                <span class="wraper">
                    <button class="prev">Prev</button>
                </span>
                <span class="wraper center">
                    <input type="text" class="pagedisplay"/>
                </span>
                <span class="wraper">
                    <button class="next">Next</button>
                </span>
                <span class="wraper">
                    <button class="last">Last</button>
                </span>
            <input type="hidden" class="pagesize" value="10" />
        </div>
    </form>
<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
