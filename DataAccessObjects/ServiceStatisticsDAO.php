<?php

class ServiceStatisticsDAO{

    var $mysqlConnection;
    var $showErrors;


    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO estatisticaAtendimento VALUES (NULL, ".$dto->mesReferencia.", ".$dto->anoReferencia.", ".$dto->quantidadeChamados.", '".$dto->tempoEmAtendimento."')";
        if ($dto->id > 0)
            $query = "UPDATE estatisticaAtendimento SET mesReferencia = ".$dto->mesReferencia.", anoReferencia = ".$dto->anoReferencia.", quantidadeChamados = ".$dto->quantidadeChamados.", tempoEmAtendimento = '".$dto->tempoEmAtendimento."'  WHERE id = ".$dto->id;

        $result = mysqli_query($this->mysqlConnection, $query);
        if ($result) {
            $insertId = mysqli_insert_id($this->mysqlConnection);
            if ($insertId == null) return $dto->id;
            return $insertId;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return null;
    }

    function DeleteRecord($id){
        $query = "DELETE FROM estatisticaAtendimento WHERE id = ".$id;
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($month, $year){
        $dto = null;

        $query = "SELECT id, mesReferencia, anoReferencia, quantidadeChamados, time_format(tempoEmAtendimento, '%H:%i') tempoEmAtendimento, time_to_sec(tempoEmAtendimento) totalEmSegundos FROM estatisticaAtendimento WHERE mesReferencia = ".$month." AND anoReferencia = ".$year;
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ServiceStatisticsDTO();
        $dto->id                  = $record["id"];
        $dto->mesReferencia       = $record["mesReferencia"];
        $dto->anoReferencia       = $record["anoReferencia"];
        $dto->quantidadeChamados  = $record["quantidadeChamados"];
        $dto->tempoEmAtendimento  = $record["tempoEmAtendimento"];
        $dto->totalEmSegundos     = $record["totalEmSegundos"];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query  = "SELECT * FROM estatisticaAtendimento";
        if (isset($filter) && (!empty($filter))) $query = $query." WHERE ".$filter;

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }

        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new ServiceStatisticsDTO();
            $dto->id                  = $record["id"];
            $dto->mesReferencia       = $record["mesReferencia"];
            $dto->anoReferencia       = $record["anoReferencia"];
            $dto->quantidadeChamados  = $record["quantidadeChamados"];
            $dto->tempoEmAtendimento  = $record["tempoEmAtendimento"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

    // Retorna os totais de atendimento
    function GetServiceTotals()
    {
        $totals = array();

        $query =  "SELECT COALESCE(SUM(1),0) AS quantidadeChamados, COALESCE(SEC_TO_TIME(SUM(TIME_TO_SEC(tempoAtendimento))),'') AS tempoTotalAtendimento FROM addoncontratos.chamadoservico WHERE ";
        $query .= "YEAR(dataAtendimento) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND MONTH(dataAtendimento) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $record = mysqli_fetch_array($recordSet);
        if (!$record) return 0;
        $totals[0] = $record['quantidadeChamados'];
        $totals[1] = $record['tempoTotalAtendimento'];
        mysqli_free_result($recordSet);

        return $totals;
    }

    // Retorna as statisticas de atendimento do último mês
    function GetLastMonthStatistics(){
        $dto = null;

        $query = "SELECT * FROM estatisticaAtendimento WHERE anoReferencia = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND mesReferencia = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))";
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ServiceStatisticsDTO();
        $dto->id                  = $record["id"];
        $dto->mesReferencia       = $record["mesReferencia"];
        $dto->anoReferencia       = $record["anoReferencia"];
        $dto->quantidadeChamados  = $record["quantidadeChamados"];
        $dto->tempoEmAtendimento  = $record["tempoEmAtendimento"];
        mysqli_free_result($recordSet);

        return $dto;
    }

}

?>
