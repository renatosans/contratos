<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");
include_once("../../DataAccessObjects/SupplyRequestDAO.php");
include_once("../../DataTransferObjects/SupplyRequestDTO.php");
include_once("../../DataAccessObjects/RequestItemDAO.php");
include_once("../../DataTransferObjects/RequestItemDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");


$equipmentCode = 0;
if (isset($_REQUEST["equipmentCode"]) && ($_REQUEST["equipmentCode"] != 0)) {
    $equipmentCode = $_REQUEST["equipmentCode"];
}
$subContract = 0;
if (isset($_REQUEST["subContract"]) && ($_REQUEST["subContract"] != 0)) {
    $subContract = $_REQUEST["subContract"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["solicitacaoConsumiveis"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
$supplyRequestDAO->showErrors = 1;
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;

// Traz a lista de consumíveis solicitados
$maxRecordCount = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "limiteListaConsumiveis");
$recCount = $supplyRequestDAO->GetRecordCount();
$boundary = ""; if (($recCount > $maxRecordCount) && empty($equipmentCode)) $boundary = "LIMIT ".($recCount - $maxRecordCount).", ".$recCount;

$filter = "id > 0 ORDER BY id";
if ($equipmentCode != 0) $filter = "codigoCartaoEquipamento = ".$equipmentCode;
$supplyRequestArray = $supplyRequestDAO->RetrieveRecordArray($filter.' '.$boundary);

$extraInfo = "( últimas ".$maxRecordCount." )";
if (!empty($equipmentCode)) { 
    $extraInfo = EquipmentDAO::GetSerialNumber($dataConnector->sqlserverConnection, $equipmentCode);
}

?>

    <h1>Solicitações de consumível <?php echo $extraInfo; ?></h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

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
                <!-- Só habilita o botão quando navegou vindo da listagem de equipamentos ou do subcontrato -->
                <?php
                    $attributes = '';
                    $url = 'Frontend/'.$currentDir.'/editar.php?id=0&equipmentCode='.$equipmentCode.'&subContract='.$subContract;
                    if (empty($equipmentCode) || ($nivelAutorizacao < 3)) {
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
                <th>&nbsp;Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if (sizeof($supplyRequestArray) == 0){
                echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
            }
            foreach ($supplyRequestArray as $supplyRequest) {
                $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$supplyRequest->id);
                $description = "";
                foreach ($requestItemArray as $requestItem) {
                    if (!empty($description)) $description.= ' , ';
                    $description.= $requestItem->quantidade.' '.$requestItem->nomeItem;
                }
                if (empty($description)) $description = "Nenhum item encontrado";
                ?>
                <tr>
                    <td align="center" >
                        <input type="checkbox" value= "<?php echo $supplyRequest->id; ?>" name="reg[]"/>
                    </td>
                    <td >
                    <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$supplyRequest->id.'&equipmentCode='.$equipmentCode.'&subContract='.$subContract; ?>" >
                        <?php echo str_pad($supplyRequest->id, 5, '0', STR_PAD_LEFT); ?>
                    </a>
                    </td>
                    <td >
                    <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$supplyRequest->id.'&equipmentCode='.$equipmentCode.'&subContract='.$subContract; ?>" >
                        <?php echo $supplyRequest->data; ?>
                    </a>
                    </td>
                    <td >
                    <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$supplyRequest->id.'&equipmentCode='.$equipmentCode.'&subContract='.$subContract; ?>" >
                        <?php
                            $requestDescription = new Text($description);
                            echo $requestDescription->Truncate(60);
                        ?>
                    </a>
                    </td>
                    <td >
                        <?php echo $supplyRequestDAO::GetStatusAsText($supplyRequest->status); ?>
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

        <!-- Só exibe o botão voltar quando navegou vindo da listagem de equipamentos ou do subcontrato -->
        <div style="display: <?php echo empty($equipmentCode) ? 'none;' : 'inline;'; ?>" >
            <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
                Voltar
            </a>
        </div>
    </form>
<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
