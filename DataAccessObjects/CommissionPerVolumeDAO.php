<?php

class CommissionPerVolumeDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO regraComissaoVolume VALUES (NULL, ".$dto->categoriaContrato.", ".$dto->quantContratosDe.", ".$dto->quantContratosAte.", ".$dto->valorFaturamentoDe.", ".$dto->valorFaturamentoAte.", ".$dto->comissao.");";
        if ($dto->id > 0)
            $query = "UPDATE regraComissaoVolume SET categoriaContrato = ".$dto->categoriaContrato.", quantContratosDe = ".$dto->quantContratosDe.", quantContratosAte = ".$dto->quantContratosAte.", valorFaturamentoDe = ".$dto->valorFaturamentoDe.", valorFaturamentoAte = ".$dto->valorFaturamentoAte.", comissao = ".$dto->comissao." WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM regraComissaoVolume WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, categoriaContrato, quantContratosDe, quantContratosAte, valorFaturamentoDe, valorFaturamentoAte, comissao";
        $query = "SELECT ".$fieldList." FROM regraComissaoVolume WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new CommissionPerVolumeDTO();
        $dto->id                  = $record['id'];
        $dto->categoriaContrato   = $record['categoriaContrato'];
        $dto->quantContratosDe    = $record['quantContratosDe'];
        $dto->quantContratosAte   = $record['quantContratosAte'];
        $dto->valorFaturamentoDe  = $record['valorFaturamentoDe'];
        $dto->valorFaturamentoAte = $record['valorFaturamentoAte'];
        $dto->comissao            = $record['comissao'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, categoriaContrato, quantContratosDe, quantContratosAte, valorFaturamentoDe, valorFaturamentoAte, comissao";
        $query = "SELECT ".$fieldList." FROM regraComissaoVolume WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM regraComissaoVolume";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new CommissionPerVolumeDTO();
            $dto->id                  = $record['id'];
            $dto->categoriaContrato   = $record['categoriaContrato'];
            $dto->quantContratosDe    = $record['quantContratosDe'];
            $dto->quantContratosAte   = $record['quantContratosAte'];
            $dto->valorFaturamentoDe  = $record['valorFaturamentoDe'];
            $dto->valorFaturamentoAte = $record['valorFaturamentoAte'];
            $dto->comissao            = $record['comissao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
