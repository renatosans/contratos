<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/InventoryItemDAO.php");
include_once("../../DataTransferObjects/InventoryItemDTO.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");


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

// Cria os objetos de mapeamento objeto-relacional
$inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
$inventoryItemDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;

// Traz os grupos existentes no cadastro de items
$itemGroupArray = $inventoryItemDAO::GetItemGroups($dataConnector->sqlserverConnection);

// Traz os contadores cadastrados no sistema
$counterArray = $counterDAO->RetrieveRecordArray("id > 0 LIMIT 0, 3"); // exibe apenas os 3 primeiros

$itemCode = "";
$inventoryItem = new InventoryItemDTO();
if ( isset($_REQUEST["itemCode"]) && ($_REQUEST["itemCode"] != "")) {
    $itemCode = urldecode($_REQUEST["itemCode"]);
    $inventoryItem = $inventoryItemDAO->RetrieveRecord($itemCode);
}

function BuildSerializedContent($content) {
    global $counterArray;

    $xml = simplexml_load_string($content);
    foreach ($counterArray as $counter) {
        $aditionalAttributes = '';
    
        foreach ($xml as $element) {
            if ($counter->id == $element["id"]) $aditionalAttributes = 'checked="checked"';
        }
        echo '<input type="checkbox" '.$aditionalAttributes.' name="counter'.$counter->id.'" > <u>'.$counter->nome.'</u> </input>';
        echo '<br/><br/>';
    }
}

?>

    <h1>Administração - Item de inventário</h1>
    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="itemCode" value="<?php echo $itemCode; ?>" />

        <fieldset style="width:650px;">
            <legend>Dados</legend>
            Código:  <?php echo $inventoryItem->itemCode; ?><br/><br/>
            Descrição do item:  <?php echo $inventoryItem->itemName; ?><br/><br/>
            Grupo:  <?php echo $itemGroupArray[$inventoryItem->itemGroup]; ?><br/><br/>
            Preço Médio:  R$ <?php echo number_format($inventoryItem->avgPrice, 2, ',', '.'); ?><br/><br/>
            <?php echo $inventoryItem->userText; ?><br/><br/>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Custo Pág. Peças (Equipamentos)<br/>
        <input type="text" name="expenses" size="65" value="<?php echo number_format($inventoryItem->expenses, 4, '.', ','); ?>" />
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Durabilidade/Vida Útil<br/>
        <input type="text" name="durability" size="65" value="<?php echo $inventoryItem->durability; ?>" />
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Instruções de Uso<br/>
        <textarea name="useInstructions" style="width:460px;height:50px;" ><?php echo $inventoryItem->useInstructions; ?></textarea>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width:650px;">
            <legend>Medidores de utilização da peça (Material de Reposição)</legend>
        <?php
            if (!empty($inventoryItem->serializedData))
                BuildSerializedContent($inventoryItem->serializedData);
            else
                foreach ($counterArray as $counter) {
                    $aditionalAttributes = '';
                    echo '<input type="checkbox" '.$aditionalAttributes.' name="counter'.$counter->id.'" > <u>'.$counter->nome.'</u> </input>';
                    echo '<br/><br/>';
                }
        ?>
        </fieldset>
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
