<?php
    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/InventoryItemDAO.php");
    include_once("../DataTransferObjects/InventoryItemDTO.php");


    $itemCode = $_GET['itemCode'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
    $inventoryItemDAO->showErrors = 1;

    // Busca os dados do item
    $inventoryItem = $inventoryItemDAO->RetrieveRecord($itemCode);

    $useInstructions = $inventoryItem->useInstructions;
    if (empty($useInstructions)) echo 'Nenhuma instrução cadastrada.'; else echo $useInstructions;
?>
