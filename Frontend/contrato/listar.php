<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/SubContractDAO.php");
include_once("../../DataTransferObjects/SubContractDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;


// Traz os contratos cadastrados no sistema
$filter = "";
if(isset($_SESSION["slpCode"])) $filter .= "vendedor=".$_SESSION["slpCode"]." AND ";
$filter .= "id > 0 ORDER BY convert(numero, signed)";
$contractArray = $contractDAO->RetrieveRecordArray($filter);

?>

    <h1>Administração - Contratos</h1>

    <script type="text/javascript" >
        $(document).ready(function() {

            var pageLoad = true;

            $("input[name=filter]").keyup(function() {
                document.cookie = "lastSearch=" + $(this).val() + "...";

                var filter = $(this).val();

                if ((!filter) || (filter.length == 0)) {
                    $('a[class="contractIcon"]').css("display", "inline");
                    exit;
                }

                $('a[class="contractIcon"]').css("display", "none");
                $('a[rev*="' + filter + '"]').each( function() {
                    if ($(this).hasClass("contractIcon")) $(this).css("display", "inline");
                });
            });

            $(".sorTable").bind("sortEnd", function() {
                var cookieIdentifier = new String("lastSearch=");
                var startPos = document.cookie.indexOf(cookieIdentifier);
                var cookieLength = document.cookie.indexOf("...") - startPos - cookieIdentifier.length;
                var lastSearch = document.cookie.substr(startPos + cookieIdentifier.length, cookieLength);

                if ((lastSearch != '') && (pageLoad)) { 
                    $("input[name=filter]").val(lastSearch);
                    $("input[name=filter]").trigger("keyup");
                }
                pageLoad = false;
            });
        });
    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
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
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="72" size="72" type="text"/>
                    <button id="filter-clear-button" type="submit" value="Clear">Clear</button>
                </fieldset>
            </div>
        </div>
        <br/>
        <input type="hidden" name="acao" value="remove" />

        <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
        <thead>
            <tr>
                <th style="min-width: 10px;"  >&nbsp;</th>
                <th style="min-width: 10px;" >Contrato</th>
                <th style="min-width: 90px;" >Parceiro de Negócios</th>
                <th style="min-width: 90px;" >Divisão</th>
                <th style="min-width: 90px;" >Tipos</th>
                <th style="min-width: 10px;" >Status</th>
                <th style="min-width: 10px; " >Histórico</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (sizeof($contractArray) == 0){
                echo '<tr><td colspan="6" align="center" >Nenhum registro encontrado!</td></tr>';
            }
            foreach ($contractArray as $contract) {
                $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id=".$contract->id);
                $typeEnumeration = "";
                foreach ($subContractArray as $subContract) {
                    if (!empty($typeEnumeration)) $typeEnumeration.= ', ';
                    $typeEnumeration.= $subContract->siglaTipoContrato;
                }
            ?>
                <tr>
                    <td align="center" >
                        <input type="checkbox" value= "<?php echo $contract->id; ?>" name="reg[]"/>
                    </td>
                    <td >
                        <a href="Frontend/<?php echo $currentDir; ?>/editar.php?id=<?php echo $contract->id; ?>" >
                            <?php echo str_pad($contract->numero, 5, '0', STR_PAD_LEFT); ?>
                        </a>
                    </td>
                    <td >
                        <a href="Frontend/<?php echo $currentDir; ?>/editar.php?id=<?php echo $contract->id; ?>" >
                        <?php
                            $clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $contract->pn));
                            echo $clientName->Truncate(42);
                        ?>
                        </a>
                    </td>
                    <td >
                        <?php
                            $divisao = new Text($contract->divisao);
                            echo $divisao->Truncate(12);
                        ?>
                    </td>
                    <td >
                        <?php
                            $contractTypes = new Text($typeEnumeration);
                            echo $contractTypes->Truncate(18);
                        ?>
                    </td>
                    <td >
                        <?php echo ContractDAO::GetStatusAsText($contract->status); ?>
                    </td>
                    <td align="center" >
                        <a href="<?php echo 'Frontend/'.$currentDir.'/historico.php?contractId='.$contract->id; ?>" >
                            <span class="ui-icon ui-icon-info"></span>
                        </a>
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
