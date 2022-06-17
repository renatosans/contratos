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

        $query = "SELECT Code, U_InsId, U_ItemCode, U_ItemName, U_Amount FROM [@ACCESSORIES] WHERE ".$filter;
        if (empty($filter)) $query = "SELECT Code, U_InsId, U_ItemCode, U_ItemName, U_Amount FROM [@ACCESSORIES]";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new AccessoriesDTO();
            $dto->id = $record["id"];
            $dto->equipamento = $record["equipamento"];
            $dto->codigoItem = $record["codigoItem"];
            $dto->descricaoItem = $record["descricaoItem"];
            $dto->quantidade = $record["quantidade"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
