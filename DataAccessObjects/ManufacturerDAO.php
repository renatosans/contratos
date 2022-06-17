<?php

class ManufacturerDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
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

    static function GetManufacturerName($mysqlConnection, $manufacturerCode) {
        if (empty($manufacturerCode)) return ""; // early return if empty

        $manufacturerName = "";
        $manufacturerDAO = new ManufacturerDAO($mysqlConnection);
        $manufacturerDAO->showErrors = 1;
        $manufacturerArray = $manufacturerDAO->RetrieveRecordArray();
        foreach ($manufacturerArray as $manufacturer) {
            if ($manufacturerCode == $manufacturer->id) $manufacturerName = $manufacturer->nome;
        }

        return $manufacturerName;
    }
}

?>
