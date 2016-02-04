<?php

class AuthorizationDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function AuthorizationDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO autorizacao VALUES (NULL, '".$dto->login_id."', '".$dto->funcionalidade."', '".$dto->nivelAutorizacao."');";
        if ($dto->id > 0)
            $query = "UPDATE autorizacao SET login_id = '".$dto->login_id."', funcionalidade = '".$dto->funcionalidade."', nivelAutorizacao = '".$dto->nivelAutorizacao."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM autorizacao WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM autorizacao WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new AuthorizationDTO();
        $dto->id                  = $record['id'];
        $dto->login_id            = $record['login_id'];
        $dto->funcionalidade      = $record['funcionalidade'];
        $dto->nivelAutorizacao    = $record['nivelAutorizacao'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM autorizacao WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM autorizacao";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new AuthorizationDTO();
            $dto->id                  = $record['id'];
            $dto->login_id            = $record['login_id'];
            $dto->funcionalidade      = $record['funcionalidade'];
            $dto->nivelAutorizacao    = $record['nivelAutorizacao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

    function RetrieveFunctionalities()
    {
        $functionalityArray = array();

        $query = "SELECT * FROM funcionalidade";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $functionalityArray;

        while( $record = mysql_fetch_array($recordSet) ){
            $functionalityArray[$record['id']] = $record['nome'];
        }
        mysql_free_result($recordSet);

        return $functionalityArray;
    }

}

?>
