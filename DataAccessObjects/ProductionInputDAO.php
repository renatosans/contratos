<?php

class ProductionInputDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function ProductionInputDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO insumo VALUES (NULL, '".$dto->descricao."', ".$dto->tipoInsumo.", ".$dto->valor.");";
        if ($dto->id > 0)
            $query = "UPDATE insumo SET descricao = '".$dto->descricao."', tipoInsumo = ".$dto->tipoInsumo.", valor = ".$dto->valor." WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM insumo WHERE id = ".$id.";";
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM insumo WHERE id = ".$id.";";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ProductionInputDTO();
        $dto->id          = $record['id'];
        $dto->descricao   = $record['descricao'];
        $dto->tipoInsumo  = $record['tipoInsumo'];
        $dto->valor       = $record['valor'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM insumo WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT * FROM insumo;";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new ProductionInputDTO();
            $dto->id          = $record['id'];
            $dto->descricao   = $record['descricao'];
            $dto->tipoInsumo  = $record['tipoInsumo'];
            $dto->valor       = $record['valor'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

    // Retorna os tipos de insumo (production input)
    function RetrieveInputTypes()
    {
        $inputTypeArray = array();

        $query = "SELECT * FROM tipoInsumo";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $inputTypeArray;

        while( $record = mysql_fetch_array($recordSet) ){
            $inputTypeArray[$record['id']] = $record['tipoInsumo'];
        }
        mysql_free_result($recordSet);

        return $inputTypeArray;
    }

    // Retorna as unidades de medida para os tipos de insumo
    function RetrieveMeasurementUnits()
    {
        $unitArray = array();

        $query = "SELECT * FROM tipoInsumo";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $unitArray;

        while( $record = mysql_fetch_array($recordSet) ){
            $unitArray[$record['id']] = $record['unidadeMedida'];
        }
        mysql_free_result($recordSet);

        return $unitArray;
    } 

}

?>
