<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/InventoryItemDAO.php");
    include_once("../DataTransferObjects/InventoryItemDTO.php");


    $sortedBy = $_GET['sortedBy'];
    $defaultItemCode = $_GET['defaultItemCode'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Busca os items cadastrados
    $orderBy = "ORDER BY ItemName";
    if ($sortedBy == 2) $orderBy = "ORDER BY ItemCode";
    $inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
    $inventoryItemDAO->showErrors = 1;
    $inventoryItemArray = $inventoryItemDAO->RetrieveRecordArray("ItemName IS NOT NULL ".$orderBy);
    foreach ($inventoryItemArray as $item) {
        $attributes = "";
        if ($item->itemCode == $defaultItemCode) $attributes = "selected='selected'";
        $option = "<option ".$attributes." value=".$item->itemCode." alt=".$item->avgPrice." >".$item->itemName."&nbsp;&nbsp;&nbsp;( Código: ".$item->itemCode." )"."</option>";
        if ($sortedBy == 2) $option = "<option ".$attributes." value=".$item->itemCode." alt=".$item->avgPrice." >".$item->itemCode."&nbsp;&nbsp;&nbsp;( ".$item->itemName." )"."</option>";
        echo $option;
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
