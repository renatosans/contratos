<?php

class PartnerAddressDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecord($businessPartnerCode, $addressLabel){
        $dto = null;

        $query = "SELECT Address, AddrType, Street, StreetNo, Building, ZipCode, Block, City, State, Country, U_Secretaria FROM CRD1 WHERE CardCode = '".$businessPartnerCode."' AND Address ='".$addressLabel."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new PartnerAddressDTO();
        $dto->addressLabel   = $record["Address"];
        $dto->addrType       = $record["AddrType"]; 
        $dto->street         = $record["Street"];
        $dto->streetNo       = $record["StreetNo"];
        $dto->building       = $record["Building"];
        $dto->zipCode        = $record["ZipCode"];
        $dto->block          = $record["Block"];
        $dto->city           = $record["City"];
        $dto->state          = $record["State"];
        $dto->country        = $record["Country"];
        $dto->locationRef    = $record["U_Secretaria"];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT Address, AddrType, Street, StreetNo, Building, ZipCode, Block, City, State, Country, U_Secretaria FROM CRD1 WHERE ".$filter;
        if (empty($filter)) $query = "SELECT Address, AddrType, Street, StreetNo, Building, ZipCode, Block, City, State, Country, U_Secretaria FROM CRD1 WHERE Address IS NOT NULL";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new PartnerAddressDTO();
            $dto->addressLabel   = $record["Address"];
            $dto->addrType       = $record["AddrType"]; 
            $dto->street         = $record["Street"];
            $dto->streetNo       = $record["StreetNo"];
            $dto->building       = $record["Building"];
            $dto->zipCode        = $record["ZipCode"];
            $dto->block          = $record["Block"];
            $dto->city           = $record["City"];
            $dto->state          = $record["State"];
            $dto->country        = $record["Country"];
            $dto->locationRef    = $record["U_Secretaria"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
