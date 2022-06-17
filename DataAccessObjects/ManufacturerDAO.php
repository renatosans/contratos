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

        $query = "SELECT * FROM fabricante WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT * FROM fabricante;";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new ManufacturerDTO();
            $dto->id       = $record['id'];
            $dto->nome     = $record['nome'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

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
