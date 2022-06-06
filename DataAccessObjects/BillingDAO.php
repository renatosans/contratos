<?php

class BillingDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO faturamento VALUES (NULL, '".$dto->businessPartnerCode."', '".$dto->businessPartnerName."', ".$dto->mailing_id.", '".$dto->dataInicial."', '".$dto->dataFinal."', '".$dto->mesReferencia."', '".$dto->anoReferencia."', '".$dto->multaRecisoria."', ".$dto->acrescimoDesconto.", ".$dto->total.", '".$dto->obs."', '".$dto->incluirRelatorio."');";
        if ($dto->id > 0)
            $query = "UPDATE faturamento SET businessPartnerCode = '".$dto->businessPartnerCode."', businessPartnerName = '".$dto->businessPartnerName."', mailing_id = ".$dto->mailing_id.", dataInicial = '".$dto->dataInicial."', dataFinal = '".$dto->dataFinal."', mesReferencia = '".$dto->mesReferencia."', anoReferencia = '".$dto->anoReferencia."', multaRecisoria = '".$dto->multaRecisoria."', acrescimoDesconto = ".$dto->acrescimoDesconto.", total = ".$dto->total.", obs = '".$dto->obs."', incluirRelatorio = '".$dto->incluirRelatorio."' WHERE id = ".$dto->id;

        $result = mysqli_query($query, $this->mysqlConnection);
        if ($result) {
            $insertId = mysqli_insert_id($this->mysqlConnection);
            if ($insertId == null) return $dto->id;
            return $insertId;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return null;
    }

    function DeleteRecord($id){
        $query = "DELETE FROM faturamento WHERE id = ".$id;
        $result = mysqli_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, businessPartnerCode, businessPartnerName, mailing_id, DATE(dataInicial) as dataInicial, DATE(dataFinal) as dataFinal, mesReferencia, anoReferencia, multaRecisoria, acrescimoDesconto, total, obs, incluirRelatorio";
        $query = "SELECT ".$fieldList." FROM faturamento WHERE id = ".$id;
        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new BillingDTO();
        $dto->id                  = $record['id'];
        $dto->businessPartnerCode = $record['businessPartnerCode'];
        $dto->businessPartnerName = $record['businessPartnerName'];
        $dto->mailing_id          = $record['mailing_id'];
        $dto->dataInicial         = $record['dataInicial'];
        $dto->dataFinal           = $record['dataFinal'];
        $dto->mesReferencia       = $record['mesReferencia'];
        $dto->anoReferencia       = $record['anoReferencia'];
        $dto->multaRecisoria      = $record['multaRecisoria'];
        $dto->acrescimoDesconto   = $record['acrescimoDesconto'];
        $dto->total               = $record['total'];
        $dto->obs                 = $record['obs'];
        $dto->incluirRelatorio    = $record['incluirRelatorio'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, businessPartnerCode, businessPartnerName, mailing_id, DATE(dataInicial) as dataInicial, DATE(dataFinal) as dataFinal, mesReferencia, anoReferencia, multaRecisoria, acrescimoDesconto, total, obs, incluirRelatorio";
        $query = "SELECT ".$fieldList." FROM faturamento WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM faturamento";

        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new BillingDTO();
            $dto->id                  = $record['id'];
            $dto->businessPartnerCode = $record['businessPartnerCode'];
            $dto->businessPartnerName = $record['businessPartnerName'];
            $dto->mailing_id          = $record['mailing_id'];
            $dto->dataInicial         = $record['dataInicial'];
            $dto->dataFinal           = $record['dataFinal'];
            $dto->mesReferencia       = $record['mesReferencia'];
            $dto->anoReferencia       = $record['anoReferencia'];
            $dto->multaRecisoria      = $record['multaRecisoria'];
            $dto->acrescimoDesconto   = $record['acrescimoDesconto'];
            $dto->total               = $record['total'];
            $dto->obs                 = $record['obs'];
            $dto->incluirRelatorio    = $record['incluirRelatorio'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
