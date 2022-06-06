<?php

class ContactPersonDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecord($contactCode){
        $dto = null;

        // Procura na tabela OCPR (Contact Person)
        $query = "SELECT CntctCode, Name, Tel1, Cellolar, E_MailL FROM OCPR WHERE CntctCode = '".$contactCode."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new ContactPersonDTO();
        $dto->cntctCode = $record["CntctCode"];
        $dto->name = $record["Name"];
        $dto->phoneNumber = $record["Tel1"];
        $dto->cellNumber = $record["Cellolar"];
        $dto->email = $record["E_MailL"];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        // Procura na tabela OCPR (Contact Person)
        $query = "SELECT CntctCode, Name, Tel1, Cellolar, E_MailL FROM OCPR WHERE ".$filter." ORDER BY Name";
        if (empty($filter)) $query = "SELECT CntctCode, Name, Tel1, Cellolar, E_MailL FROM OCPR ORDER BY Name";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new ContactPersonDTO();
            $dto->cntctCode = $record["CntctCode"];
            $dto->name = $record["Name"];
            $dto->phoneNumber = $record["Tel1"];
            $dto->cellNumber = $record["Cellolar"];
            $dto->email = $record["E_MailL"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetContactPersonName($sqlServerConnection, $contactCode){
        $name = "";

        $contactPersonDAO = new ContactPersonDAO($sqlServerConnection);
        $contactPersonDAO->showErrors = 1;
        $contactPerson = $contactPersonDAO->RetrieveRecord($contactCode);
        if ($contactPerson != null) $name = $contactPerson->name;

        return $name; 
    }
}

?>
