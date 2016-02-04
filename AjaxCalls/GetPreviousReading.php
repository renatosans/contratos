<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/UnixTime.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/ReadingDAO.php");
    include_once("../DataTransferObjects/ReadingDTO.php");


    $equipmentCode = $_GET['equipmentCode'];
    $counterId = $_GET['counterId'];
    $cutoffDate = null;
    // Define uma data de corte para evitar que a consulta traga o contador sendo editado (subtrai 1 minuto)
    $currentTime = new UnixTime(time());
    $cutoffDate = date('Y-m-d H:i', $currentTime->AddTime(0, -1));
    // Alternativamente extrai a data de corte do parâmetro caso seja passado
    if (isset($_GET['cutoffDate'])) $cutoffDate = $_GET['cutoffDate'];
    $origemLeitura = null;
    if (isset($_GET['origemLeitura'])) $origemLeitura = $_GET['origemLeitura'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('mySql');
    $dataConnector->OpenConnection();
    if ($dataConnector->mysqlConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Traz a leitura mais recente para o cartão de equipamento em questão partindo de dataDeCorte (contadores anteriores a dataDeCorte)
    $counterReading = -1;
    $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
    $readingDAO->showErrors = 1;
    $filter = "codigoCartaoEquipamento = ".$equipmentCode." AND contador_id = ".$counterId." AND data < '".$cutoffDate."'";
    if (isset($origemLeitura)) $filter = $filter." AND origemLeitura_id = ".$origemLeitura;
    $filter = $filter." ORDER BY data DESC LIMIT 0, 1";
    $readingArray = $readingDAO->RetrieveRecordArray($filter);
    if (sizeof($readingArray) == 1)
    {
        $reading = $readingArray[0];
        $counterReading = $reading->contagem; 
    }

    echo $counterReading;

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
