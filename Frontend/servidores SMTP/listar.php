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

?>
    <h1>Servidores Smtp</h1>

    <script type="text/javascript" >
        $(document).ready(function() {
            $("#btnSetDefault").button({ icons: {primary:'ui-icon-check'} }).click( function() {
                var checkedCount = 0;
                var idServidor = 0;
                $("input[type=checkbox]").each( function() {
                    if ($(this).is(":checked")) {
                        checkedCount++;
                        idServidor = $(this).val();
                    }
                });
                if ((checkedCount == 0) || (checkedCount > 1)){
                    alert('Marque o servidor que deseje tornar padrão. ( Somente um )');
                    return false;
                }
                // Faz uma requisição sincrona chamando a ação
                var targetUrl = "Frontend/<?php echo $currentDir; ?>/acao.php";
                $.ajax({ type: 'POST', url: targetUrl, data: 'acao=makeDefault&servidorDefault=' + idServidor, success: function(response) { alert(response); }, async: false });
                // Recarrega a página
                LoadPage("Frontend/<?php echo $currentDir; ?>/listar.php");
                return false;
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
                <button type="button" <?php echo $attributes; ?> id="btnSetDefault" >
                    Default
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
                    <th>&nbsp;Nome</th>
                    <th>&nbsp;Endereço</th>
                    <th>&nbsp;Porta</th>
                    <th style="text-align: center;" >&nbsp;Default</th>
                </tr>
            </thead>
            <tbody>
            <?php
                // Traz a lista de servidores de SMTP
                $serverArray = $smtpServerDAO->RetrieveRecordArray();
                if (sizeof($serverArray) == 0){
                    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($serverArray as $smtpServer) {
            ?>
                    <tr>
                        <td align="center">
                            <input type="checkbox" value= "<?php echo $smtpServer->id; ?>" name="reg[]"/>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$smtpServer->id; ?>" >
                               <?php echo $smtpServer->nome; ?>
                            </a>
                        </td>
                        <td>
                           <?php echo $smtpServer->endereco; ?>
                        </td>
                        <td>
                           <?php echo $smtpServer->porta; ?>
                        </td>
                        <td align="center">
                           <?php if ($smtpServer->defaultServer == 1) echo "<img src='img/admin/checked_sign.png' alt='Default' style='margin: 0;' />"; ?>
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
