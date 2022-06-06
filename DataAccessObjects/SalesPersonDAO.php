<?php

class SalesPersonDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Alterar apenas campos de usuário aqui (começados com U_ ), é proibido alterar campos do SAP B1
        $query = "UPDATE OSLP SET U_SerializedData = ".$dto->serializedData." WHERE SlpCode = ".$dto->slpCode;

        $result = sqlsrv_query($this->sqlserverConnection, $query);
        if ($result) {
            return $dto->slpCode;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/>';
        }
        return null;
    }

    function RetrieveRecord($slpCode){
        $dto = null;

        // Procura na tabela OSLP (Sales Person)
        $query = "SELECT SlpCode, SlpName, Commission, U_SerializedData FROM OSLP WHERE SlpCode = '".$slpCode."'";

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new SalesPersonDTO();
        $dto->slpCode        = $record['SlpCode'];
        $dto->slpName        = $record['SlpName'];
        $dto->commission     = $record['Commission'];
        $dto->serializedData = $record['U_SerializedData'];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        // Procura na tabela OSLP (Sales Person)
        $query = "SELECT SlpCode, SlpName, Commission, U_SerializedData FROM OSLP WHERE ".$filter;
        if (empty($filter)) $query = "SELECT SlpCode, SlpName, Commission, U_SerializedData FROM OSLP";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new SalesPersonDTO();
            $dto->slpCode        = $record['SlpCode'];
            $dto->slpName        = $record['SlpName'];
            $dto->commission     = $record['Commission'];
            $dto->serializedData = $record['U_SerializedData'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetSalesPersonName($sqlServerConnection, $slpCode){
        $name = "-Nenhum vendedor-";

        $salesPersonDAO = new SalesPersonDAO($sqlServerConnection);
        $salesPersonDAO->showErrors = 1;
        $salesPerson = $salesPersonDAO->RetrieveRecord($slpCode);
        if ($salesPerson != null) $name = $salesPerson->slpName;

        return $name; 
    }
}

?>
