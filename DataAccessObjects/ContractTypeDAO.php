<?php

class ContractTypeDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query
        $query = "INSERT INTO tipocontrato VALUES (NULL, '".$dto->sigla."', '".$dto->nome."', ".$dto->permiteBonus.");";

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
        $query = "DELETE FROM tipocontrato WHERE id = ".$id;
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM tipocontrato WHERE id = ".$id;
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ContractTypeDTO();
        $dto->id           = $record['id'];
        $dto->sigla        = $record['sigla'];
        $dto->nome         = $record['nome'];
        $dto->permiteBonus = $record['bonus'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM tipocontrato WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM tipocontrato";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new ContractTypeDTO();
            $dto->id           = $record['id'];
            $dto->sigla        = $record['sigla'];
            $dto->nome         = $record['nome'];
            $dto->permiteBonus = $record['bonus'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

    static function GetAlias($mysqlConnection, $id){
        $alias = "";

        $contractTypeDAO = new ContractTypeDAO($mysqlConnection);
        $contractTypeDAO->showErrors = 1;
        $contractType = $contractTypeDAO->RetrieveRecord($id);
        if ($contractType != null) $alias = $contractType->sigla;

        return $alias;
    }

}

?>
