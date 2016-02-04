<html>
<body>
<?php

    $invoiceNum = $_GET['invoiceNum'];
    if (empty($invoiceNum)) {
        echo '<h3>Nenhuma nota fiscal encontrada.</h3>';
        exit;
    }

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/InvoiceDAO.php");
    include_once("../DataTransferObjects/InvoiceDTO.php");
    include_once("../DataTransferObjects/InvoiceItemDTO.php");


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $invoiceDAO = new InvoiceDAO($dataConnector->sqlserverConnection);
    $invoiceDAO->showErrors = 1;

    $invoice = $invoiceDAO->RetrieveRecord($invoiceNum);
    if ($invoice == null) {
        echo '<h3>Erro ao localizar nota fiscal.</h3>';
        exit;
    }

    $invoiceItems = "";
    $itemArray = $invoiceDAO->RetrieveInvoiceItems($invoice->docNum);
    foreach ($itemArray as $item) {
        $description = $item->description.'('.$item->itemCode.')';
        $quantity = number_format($item->quantity, 0, '', '');
        $lineTotal = number_format($item->lineTotal, 2, ',', '.');
        $usage = $item->usage;

        if (!empty($invoiceItems)) $invoiceItems = $invoiceItems."<br/>";
        $invoiceItems = $invoiceItems.$quantity.'  '.$description.'  '.$lineTotal.'  '.$usage;
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

    echo "<label style='width:99%;' >Nº NF<br/>";
    echo "<input type='text' style='width:98%;' value='".$invoice->serial."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Cliente (Business Partner)<br/>";
    echo "<input type='text' style='width:98%;' value='".$invoice->cardName."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Data<br/>";
    echo "<input type='text' style='width:98%;' value='".$invoice->docDate->format('d/m/Y')."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Valor<br/>";
    echo "<input type='text' style='width:98%;' value='R$ ".number_format($invoice->docTotal, 2, ',', '.')."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Observações<br/>";
    echo "<input type='text' style='width:98%;' value='".$invoice->comments."' />";
    echo "</label>";
    echo "<div style='width:99%;' ><br/><br/><h3>Itens</h3>";
    echo "<div style='border:1px solid black; width:98%; min-height:100px;' >".$invoiceItems."</div><br/><br/>";
    echo "</div>";

?>
</body>
</html>
