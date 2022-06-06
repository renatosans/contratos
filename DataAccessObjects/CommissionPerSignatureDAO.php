<?php

class CommissionPerSignatureDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO regraComissaoAssinatura VALUES (NULL, ".$dto->segmento.", '".$dto->dataAssinaturaDe."', '".$dto->dataAssinaturaAte."', ".$dto->comissao.");";
        if ($dto->id > 0)
            $query = "UPDATE regraComissaoAssinatura SET segmento = ".$dto->segmento.", dataAssinaturaDe = '".$dto->dataAssinaturaDe."', dataAssinaturaAte = '".$dto->dataAssinaturaAte."', comissao = ".$dto->comissao." WHERE id = ".$dto->id.";";

        $result = mysqli_query($this->mysqlConnection, $query);
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
        $query = "DELETE FROM regraComissaoAssinatura WHERE id = ".$id;
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, segmento, DATE(dataAssinaturaDe) as dataAssinaturaDe, DATE(dataAssinaturaAte) as dataAssinaturaAte, comissao";
        $query = "SELECT ".$fieldList." FROM regraComissaoAssinatura WHERE id = ".$id;
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new CommissionPerSignatureDTO();
        $dto->id                  = $record['id'];
        $dto->segmento            = $record['segmento'];
        $dto->dataAssinaturaDe    = $record['dataAssinaturaDe'];
        $dto->dataAssinaturaAte   = $record['dataAssinaturaAte'];
        $dto->comissao            = $record['comissao'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, segmento, DATE(dataAssinaturaDe) as dataAssinaturaDe, DATE(dataAssinaturaAte) as dataAssinaturaAte, comissao";
        $query = "SELECT ".$fieldList." FROM regraComissaoAssinatura WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM regraComissaoAssinatura";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new CommissionPerSignatureDTO();
            $dto->id                  = $record['id'];
            $dto->segmento            = $record['segmento'];
            $dto->dataAssinaturaDe    = $record['dataAssinaturaDe'];
            $dto->dataAssinaturaAte   = $record['dataAssinaturaAte'];
            $dto->comissao            = $record['comissao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
