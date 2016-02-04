<?php

class EquipmentModelDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function EquipmentModelDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO modeloEquipamento VALUES (NULL, '".$dto->modelo."', ".$dto->fabricante.");";
        if ($dto->id > 0)
            $query = "UPDATE modeloEquipamento SET modelo = '".$dto->modelo."', fabricante = ".$dto->fabricante." WHERE id = ".$dto->id;

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
        $query = "DELETE FROM modeloEquipamento WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM modeloEquipamento WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new EquipmentModelDTO();
        $dto->id          = $record['id'];
        $dto->modelo      = $record['modelo'];
        $dto->fabricante  = $record['fabricante'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM modeloEquipamento WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM modeloEquipamento";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new EquipmentModelDTO();
            $dto->id          = $record['id'];
            $dto->modelo      = $record['modelo'];
            $dto->fabricante  = $record['fabricante'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
