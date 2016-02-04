<?php

class CommissionPerSignatureDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function CommissionPerSignatureDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO regraComissaoAssinatura VALUES (NULL, ".$dto->segmento.", '".$dto->dataAssinaturaDe."', '".$dto->dataAssinaturaAte."', ".$dto->comissao.");";
        if ($dto->id > 0)
            $query = "UPDATE regraComissaoAssinatura SET segmento = ".$dto->segmento.", dataAssinaturaDe = '".$dto->dataAssinaturaDe."', dataAssinaturaAte = '".$dto->dataAssinaturaAte."', comissao = ".$dto->comissao." WHERE id = ".$dto->id.";";

        $result = mysql_query($query, $this->mysqlConnection);
        if ($result) {
            $insertId = mysql_insert_id($this->mysqlConnection);
            if ($insertId == null) return $dto->id;
            return $insertId;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return null;
    }

    function DeleteRecord($id){
        $query = "DELETE FROM regraComissaoAssinatura WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, segmento, DATE(dataAssinaturaDe) as dataAssinaturaDe, DATE(dataAssinaturaAte) as dataAssinaturaAte, comissao";
        $query = "SELECT ".$fieldList." FROM regraComissaoAssinatura WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new CommissionPerSignatureDTO();
        $dto->id                  = $record['id'];
        $dto->segmento            = $record['segmento'];
        $dto->dataAssinaturaDe    = $record['dataAssinaturaDe'];
        $dto->dataAssinaturaAte   = $record['dataAssinaturaAte'];
        $dto->comissao            = $record['comissao'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, segmento, DATE(dataAssinaturaDe) as dataAssinaturaDe, DATE(dataAssinaturaAte) as dataAssinaturaAte, comissao";
        $query = "SELECT ".$fieldList." FROM regraComissaoAssinatura WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM regraComissaoAssinatura";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new CommissionPerSignatureDTO();
            $dto->id                  = $record['id'];
            $dto->segmento            = $record['segmento'];
            $dto->dataAssinaturaDe    = $record['dataAssinaturaDe'];
            $dto->dataAssinaturaAte   = $record['dataAssinaturaAte'];
            $dto->comissao            = $record['comissao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
