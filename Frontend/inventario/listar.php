<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/InventoryItemDAO.php");
include_once("../../DataTransferObjects/InventoryItemDTO.php");


$itemGroup = 0;
if (isset($_REQUEST["itemGroup"]) && ($_REQUEST["itemGroup"] != 0)) {
    $itemGroup = $_REQUEST["itemGroup"];
}
if (empty($itemGroup)) $itemGroup = 100;

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoEquipmtPecas"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
$inventoryItemDAO->showErrors = 1;

// Traz a lista de items de inventário
$filter = "ItmsGrpCod=".$itemGroup;
$inventoryItemArray = $inventoryItemDAO->RetrieveRecordArray($filter);

?>
    <h1>Administração - Inventário</h1>
    <h1><?php echo str_pad('_', 64, '_', STR_PAD_LEFT); ?></h1>
    <br/>

    <script type="text/javascript" >

        var pageLoad = true;
        
        $("input[name=filter]").keyup(function() {
            document.cookie = "lastSearch=" + $(this).val() + "...";
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

        $(document).ready(function() {
            $("#btnSelectGroup").button({ icons: {primary:'ui-icon-check' } }).click(function() {
                var itemGroup = $("select[name=itemGroup]").val();
                LoadPage('Frontend/inventario/listar.php?itemGroup=' + itemGroup);
            });
            $("#btnBatchUpdate").button({ icons: {primary:'ui-icon-transferthick-e-w' } }).click(function() {
                var targetUrl = "AjaxCalls/InventoryBatchUpdate.php";
                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl).dialog({modal:true, width: 460, height: 380, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
        });
    </script>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/listar.php" method="post" >
        <div class="clear">
            <fieldset>
                <legend>Grupo:</legend>
                <select name="itemGroup" class="left" >
                <?php
                    // Traz os grupos existentes no cadastro de items
                    $itemGroupArray = $inventoryItemDAO::GetItemGroups($dataConnector->sqlserverConnection);
                    foreach ($itemGroupArray as $groupId => $groupName) {
                        $attributes = "";
                        if ($groupId == $itemGroup) $attributes = "selected='selected'";
                        echo '<option '.$attributes.' value="'.$groupId.'" >'.$groupName.'</option>';
                    }
                ?>
                </select>
                <button type="button" id="btnSelectGroup" style="float:left; margin-left:8px;" >
                    Selecionar
                </button>
                <button type="button" id="btnBatchUpdate" style="float:left; margin-left:8px;" >
                    Alteração em lote
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="56" size="56" type="text"/>
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
                    <th>&nbsp;Código</th>
                    <th>&nbsp;Descrição</th>
                    <th>&nbsp;Grupo</th>
                    <th>&nbsp;Durabilidade/Vida Útil</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($inventoryItemArray) == 0){
                    echo '<tr><td colspan="4" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($inventoryItemArray as $inventoryItem) {
                    $encodedId = urlencode($inventoryItem->itemCode);
                    $description = new Text($inventoryItem->itemName);
            ?>
                    <tr>
                        <td >
                            &nbsp;
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?itemGroup='.$itemGroup.'&itemCode='.$encodedId; ?>" >
                                <?php echo $inventoryItem->itemCode; ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?itemGroup='.$itemGroup.'&itemCode='.$encodedId; ?>" >
                                <?php echo $description->Truncate(55); ?>
                            </a>
                        </td>
                        <td >
                           <?php echo $itemGroupArray[$inventoryItem->itemGroup]; ?>
                        </td>
                        <td >
                           <?php echo $inventoryItem->durability; ?>
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
