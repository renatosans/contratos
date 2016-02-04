<?php

class ContractTypeDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function ContractTypeDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query
        $query = "INSERT INTO tipocontrato VALUES (NULL, '".$dto->sigla."', '".$dto->nome."', ".$dto->permiteBonus.");";

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
        $query = "DELETE FROM tipocontrato WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM tipocontrato WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ContractTypeDTO();
        $dto->id           = $record['id'];
        $dto->sigla        = $record['sigla'];
        $dto->nome         = $record['nome'];
        $dto->permiteBonus = $record['bonus'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM tipocontrato WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM tipocontrato";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new ContractTypeDTO();
            $dto->id           = $record['id'];
            $dto->sigla        = $record['sigla'];
            $dto->nome         = $record['nome'];
            $dto->permiteBonus = $record['bonus'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

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
