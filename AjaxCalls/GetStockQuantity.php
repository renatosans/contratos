<?php

    $itemCode = $_GET['itemCode'];

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/InventoryItemDAO.php");
    include_once("../DataTransferObjects/InventoryItemDTO.php");


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }


    // Busca a quantidade de itens em estoque
    $stockQuantity = InventoryItemDAO::GetStockQuantity($dataConnector->sqlserverConnection, $itemCode);
    $stockQuantity = number_format($stockQuantity, 0, '', '');

    // Retorna o valor encontrado
    echo $stockQuantity;


    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
