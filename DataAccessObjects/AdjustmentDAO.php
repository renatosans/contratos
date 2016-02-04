<?php

class AdjustmentDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function AdjustmentDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO reajusteContrato VALUES (NULL, ".$dto->contrato_id.", '".$dto->data."', '".$dto->indiceUtilizado."', ".$dto->aliquotaUtilizada.");";
        if ($dto->id > 0)
            $query = "UPDATE reajusteContrato SET contrato_id = ".$dto->contrato_id.", data = '".$dto->data."', indiceUtilizado = '".$dto->indiceUtilizado."', aliquotaUtilizada = ".$dto->aliquotaUtilizada." WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM reajusteContrato WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, contrato_id, DATE(data) as data, indiceUtilizado, aliquotaUtilizada";
        $query = "SELECT ".$fieldList." FROM reajusteContrato WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new AdjustmentDTO();
        $dto->id                 = $record['id'];
        $dto->contrato_id        = $record['contrato_id'];
        $dto->data               = $record['data'];
        $dto->indiceUtilizado    = $record['indiceUtilizado'];
        $dto->aliquotaUtilizada  = $record['aliquotaUtilizada'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, contrato_id, DATE(data) as data, indiceUtilizado, aliquotaUtilizada";
        $query = "SELECT ".$fieldList." FROM reajusteContrato WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM reajusteContrato";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new AdjustmentDTO();
            $dto->id                 = $record['id'];
            $dto->contrato_id        = $record['contrato_id'];
            $dto->data               = $record['data'];
            $dto->indiceUtilizado    = $record['indiceUtilizado'];
            $dto->aliquotaUtilizada  = $record['aliquotaUtilizada'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }
}

?>
