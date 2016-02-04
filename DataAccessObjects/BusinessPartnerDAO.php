<?php

class BusinessPartnerDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function BusinessPartnerDAO($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecord($cardCode){
        $dto = null;

        // Procura na tabela OCRD (Partner Card)
        $query = "SELECT CardCode, CardName, CardFName, frozenFor, CntctPrsn, Phone1, IndustryC FROM OCRD WHERE CardCode = '".$cardCode."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new BusinessPartnerDTO();
        $dto->cardCode         = $record['CardCode'];
        $dto->cardName         = $record['CardName'];
        $dto->cardFName        = $record['CardFName'];
        $dto->inactive         = $record['frozenFor'];
        $dto->contactPerson    = $record['CntctPrsn'];
        $dto->telephoneNumber  = $record['Phone1'];
        $dto->industry         = $record['IndustryC'];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        // Procura na tabela OCRD (Partner Card)
        $query = "SELECT CardCode, CardName, CardFName, frozenFor, CntctPrsn, Phone1, IndustryC FROM OCRD WHERE ".$filter;
        if (empty($filter)) $query = "SELECT CardCode, CardName, CardFName, frozenFor, CntctPrsn, Phone1, IndustryC FROM OCRD WHERE CardName IS NOT NULL ORDER BY cardName";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new BusinessPartnerDTO();
            $dto->cardCode         = $record['CardCode'];
            $dto->cardName         = $record['CardName'];
            $dto->cardFName        = $record['CardFName'];
            $dto->inactive         = $record['frozenFor'];
            $dto->contactPerson    = $record['CntctPrsn'];
            $dto->telephoneNumber  = $record['Phone1'];
            $dto->industry         = $record['IndustryC'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetClientName($sqlserverConnection, $cardCode) {
        $clientName = "";

        $businessPartnerDAO = new BusinessPartnerDAO($sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;

        $businessPartner = $businessPartnerDAO->RetrieveRecord($cardCode);
        if ($businessPartner != null) {
            $informacaoAdicional = "";
            if ($businessPartner->cardName != $businessPartner->cardFName) $informacaoAdicional = " (".$businessPartner->cardFName.")";
            $clientName = $businessPartner->cardName.$informacaoAdicional;
        }

        return $clientName;
    }

    static function GetTelephoneNumber($sqlserverConnection, $cardCode) {
        $telephoneNumber = "";

        $businessPartnerDAO = new BusinessPartnerDAO($sqlserverConnection);
        $businessPartnerDAO->showErrors = 1;

        $businessPartner = $businessPartnerDAO->RetrieveRecord($cardCode);
        if ($businessPartner != null) {
            $telephoneNumber = $businessPartner->telephoneNumber;
        }

        return $telephoneNumber;
    }

}

?>
