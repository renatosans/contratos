<?php

class AccessoriesDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
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
            $dto->id = $record["Code"];
            $dto->equipmentCode = $record["U_InsId"];
            $dto->itemCode = $record["U_ItemCode"];
            $dto->itemName = $record["U_ItemName"];
            $dto->amount = $record["U_Amount"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
