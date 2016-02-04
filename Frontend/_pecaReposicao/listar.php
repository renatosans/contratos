<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/PartRequestDAO.php");
include_once("../../DataTransferObjects/PartRequestDTO.php");
include_once("../../DataAccessObjects/RequestItemDAO.php");
include_once("../../DataTransferObjects/RequestItemDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoEquipmtPecas"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$partRequestDAO = new PartRequestDAO($dataConnector->mysqlConnection);
$partRequestDAO->showErrors = 1;
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;


// Traz a lista de solicitações cadastradas
$partRequestArray = $partRequestDAO->RetrieveRecordArray();

?>

    <h1>Administração - Solicitação de Peças de Reposição</h1>
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
                <a disabled="disabled" href="#" class="button">
                    Novo
                </a>
                <a disabled="disabled" href="#" class="button">
                    Excluir
                </a>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="60" size="60" type="text"/>
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
                    <th>&nbsp;Número</th>
                    <th>&nbsp;Data da Solicitação</th>
                    <th>&nbsp;Descrição</th>
                    <th>&nbsp;Chamado de Serviço</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($partRequestArray) == 0){
                    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($partRequestArray as $partRequest) {
                    $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoPecaReposicao_id=".$partRequest->id);
                    $description = "";
                    foreach ($requestItemArray as $requestItem) {
                        if (!empty($description)) $description.= ' , ';
                        $description.= $requestItem->quantidade.' '.$requestItem->nomeItem;
                    }
                    if (empty($description)) $description = "Nenhum item encontrado";
                    ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value= "<?php echo $partRequest->id; ?>" name="reg[]"/>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/visualizar.php?id='.$partRequest->id; ?>" >
                               <?php echo str_pad($partRequest->id, 5, '0', STR_PAD_LEFT); ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/visualizar.php?id='.$partRequest->id; ?>" >
                                <?php echo $partRequest->data; ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/visualizar.php?id='.$partRequest->id; ?>" >
                                <?php
                                    $requestDescription = new Text($description);
                                    echo $requestDescription->Truncate(60);
                                ?>
                            </a>
                        </td>
                        <td >
                            <?php echo str_pad($partRequest->codigoChamadoServico, 5, '0', STR_PAD_LEFT); ?>
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
