<?php

// Industry = Segmento de atuação
class IndustryDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function IndustryDAO($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT IndCode, IndName, IndDesc FROM OOND WHERE ".$filter;
        if (empty($filter)) $query = "SELECT IndCode, IndName, IndDesc FROM OOND ORDER BY IndCode ASC";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new IndustryDTO();
            $dto->id = $record["IndCode"];
            $dto->name = $record["IndName"];
            $dto->description = $record["IndDesc"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
