<?php

class ManufacturerDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function ManufacturerDAO($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT FirmCode, FirmName FROM OMRC WHERE ".$filter;
        if (empty($filter)) $query = "SELECT FirmCode, FirmName FROM OMRC ORDER BY FirmCode ASC";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new ManufacturerDTO();
            $dto->FirmCode = $record["FirmCode"];
            $dto->FirmName = $record["FirmName"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetManufacturerName($sqlserverConnection, $manufacturerCode) {
        $manufacturerName = "";
        if (empty($manufacturerCode)) return $manufacturerName;

        $manufacturerDAO = new ManufacturerDAO($sqlserverConnection);
        $manufacturerDAO->showErrors = 1;
        $manufacturerArray = $manufacturerDAO->RetrieveRecordArray();
        foreach ($manufacturerArray as $manufacturer) {
            if ($manufacturerCode == $manufacturer->FirmCode) $manufacturerName = $manufacturer->FirmName;
        }

        return $manufacturerName;
    }
}

?>
