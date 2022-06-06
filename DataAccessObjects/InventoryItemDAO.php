<?php

class InventoryItemDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Alterar apenas campos de usuário aqui (começados com U_ ), é proibido alterar campos do SAP B1
        $query = "UPDATE OITM SET U_Expenses = ".$dto->expenses.",U_Durability = ".$dto->durability.", U_SerializedData = ".$dto->serializedData.", U_UseInstructions = '".$dto->useInstructions."' WHERE ItemCode = '".$dto->itemCode."'";

        $result = sqlsrv_query($this->sqlserverConnection, $query);
        if ($result) {
            return $dto->itemCode;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/>';
        }
        return null;
    }

    function BatchUpdate($itemNameQuery, $expenses, $durability, $useInstructions) {
        // Alterar apenas campos de usuário aqui (começados com U_ ), é proibido alterar campos do SAP B1
        $query = "UPDATE OITM SET U_Expenses = ".$expenses.",U_Durability = ".$durability.", U_UseInstructions = '".$useInstructions."' WHERE ItemName LIKE '%".$itemNameQuery."%'";

        $result = sqlsrv_query($this->sqlserverConnection, $query);
        if ($result) {
            return sqlsrv_rows_affected($result);
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/>';
        }
        return null;
    }

    function RetrieveRecord($itemCode){
        $dto = null;

        $fieldList = 'ItemCode, ItemName, ItmsGrpCod, AvgPrice, UserText, U_Expenses, U_Durability, U_SerializedData, U_UseInstructions';
        $query = "SELECT ".$fieldList." FROM OITM WHERE ItemCode = '".$itemCode."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new InventoryItemDTO();
        $dto->itemCode        = $record["ItemCode"];
        $dto->itemName        = $record["ItemName"];
        $dto->itemGroup       = $record["ItmsGrpCod"];
        $dto->avgPrice        = $record["AvgPrice"];
        $dto->userText        = $record["UserText"];
        $dto->expenses        = $record["U_Expenses"];
        $dto->durability      = $record["U_Durability"];
        $dto->serializedData  = $record["U_SerializedData"];
        $dto->useInstructions = $record["U_UseInstructions"];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = 'ItemCode, ItemName, ItmsGrpCod, AvgPrice, UserText, U_Expenses, U_Durability, U_SerializedData, U_UseInstructions';
        $query = "SELECT ".$fieldList." FROM OITM WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM OITM WHERE ItemName IS NOT NULL ORDER BY ItemName";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new InventoryItemDTO();
            $dto->itemCode        = $record["ItemCode"];
            $dto->itemName        = $record["ItemName"];
            $dto->itemGroup       = $record["ItmsGrpCod"];
            $dto->avgPrice        = $record["AvgPrice"];
            $dto->userText        = $record["UserText"];
            $dto->expenses        = $record["U_Expenses"];
            $dto->durability      = $record["U_Durability"];
            $dto->serializedData  = $record["U_SerializedData"];
            $dto->useInstructions = $record["U_UseInstructions"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetStockQuantity($sqlserverConnection, $itemCode) {
        $stockQuantity = 0;

        $query = "SELECT SUM(OnHand) As OnHandTotal FROM OITW WHERE ItemCode = '".$itemCode."'";
        $recordSet = sqlsrv_query($sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return 0;

        $stockQuantity = $record["OnHandTotal"];
        sqlsrv_free_stmt($recordSet);

        return $stockQuantity;
    }

    static function GetUseInstructions($sqlserverConnection, $itemCode) {
        $useInstructions = "";

        $query = "SELECT U_UseInstructions FROM OITM WHERE ItemCode = '".$itemCode."'";
        $recordSet = sqlsrv_query($sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return "";

        $useInstructions = $record["U_UseInstructions"];
        sqlsrv_free_stmt($recordSet);

        return $useInstructions;
    }

    static function GetItemGroups($sqlServerConnection) {
        $itemGroupArray = array();

        $query = "SELECT ItmsGrpCod, ItmsGrpNam FROM OITB ORDER BY ItmsGrpCod";
        $recordSet = sqlsrv_query($sqlServerConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/><br/>';
        }

        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $itemGroupArray[$record['ItmsGrpCod']] = $record['ItmsGrpNam'];
        }
        sqlsrv_free_stmt($recordSet);

        return $itemGroupArray;
    }

}

?>
