<?php

class AccessoriesDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM acessorios WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM acessorios";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new AccessoriesDTO();
            $dto->id = $record["id"];
            $dto->equipamento = $record["equipamento"];
            $dto->codigoItem = $record["codigoItem"];
            $dto->descricaoItem = $record["descricaoItem"];
            $dto->quantidade = $record["quantidade"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
