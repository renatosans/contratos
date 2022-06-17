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
