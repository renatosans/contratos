<html>
<body>
<?php
    $serviceCallId = $_GET['serviceCallId'];

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/CounterDAO.php");
    include_once("../DataTransferObjects/CounterDTO.php");
    include_once("../DataAccessObjects/ReadingDAO.php");
    include_once("../DataTransferObjects/ReadingDTO.php");
    include_once("../DataAccessObjects/ServiceCallDAO.php");
    include_once("../DataTransferObjects/ServiceCallDTO.php");

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('mySql');
    $dataConnector->OpenConnection();
    if ($dataConnector->mysqlConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
    $readingDAO->showErrors = 1;
    $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
    $counterDAO->showErrors = 1;

    // Busca os dados do chamado
    $serviceCall = $serviceCallDAO->RetrieveRecord($serviceCallId);


    echo "<div style='width:50%'>";
    echo "<label>Número<br />";
    echo "<input type='text' style='width:90%;' value='".str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT)."' />";
    echo "</label>";
    echo "</div>";
    echo "<label>Defeito<br />";
    echo "<input type='text' style='width:99%;' value='".$serviceCall->defeito."' />";
    echo "</label>";
    echo "<div style='width:50%; float:left'>";
    echo "<label>Data de Abertura<br />";
    echo "<input type='text' style='width:90%;' value='".$serviceCall->dataAbertura."' />";
    echo "</label>";
    echo "</div>";
    echo "<div style='width:50%; float:left'>";
    echo "<label>Data de Fechamento<br />";
    echo "<input type='text' style='width:90%;' value='".$serviceCall->dataFechamento."' />";
    echo "</label>";
    echo "</div>";

    // Busca todos os contadores para o chamado
    $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCall->id);
    if (sizeof($readingArray) > 0) {
        $elementCount = 0;
        foreach($readingArray as $reading) {
            $counter = $counterDAO->RetrieveRecord($reading->codigoContador);
            echo "<div style='width:50%; float:left;'>";
            echo "<label>".$counter->nome."<br />";
            echo "<input type='text' style='width:90%;' value='".$reading->contagem."' />";
            echo "</label>";
            echo "</div>";
            $elementCount++;
            if (($elementCount % 2) == 0) { // a cada 2 elementos(contadores) insere uma quebra de linha
                echo "<div style='clear:both;'>";
                echo "    &nbsp;";
                echo "</div>";
            }
        }
    }
    echo "<div style='clear:both;'>"; // insere uma quebra de linha ao final dos contadores
    echo "    &nbsp;";
    echo "</div>";

    echo "<label>Sintoma<br />";
    echo "<input type='text' style='width:99%;' value='".$serviceCall->sintoma."' />";
    echo "</label>";
    echo "<label>Causa<br />";
    echo "<input type='text' style='width:99%;' value='".$serviceCall->causa."' />";
    echo "</label>";
    echo "<label>Ação<br />";
    echo "<input type='text' style='width:99%;' value='".$serviceCall->acao."' />";
    echo "</label>";
    echo "<label>Observações<br />";
    echo "<textarea style='width:98%;height:50px;' >".$serviceCall->observacaoTecnica."</textarea>";
    echo "</label>";

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
</body>
</html>
