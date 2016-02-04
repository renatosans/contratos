<?php

class LoginDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function LoginDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $idExterno = $dto->idExterno;
        if (empty($dto->idExterno)) $idExterno = "null";
        if ($dto->idExterno == -1) $idExterno = "null";

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO login VALUES (NULL, ".$idExterno.", '".$dto->nome."', '".$dto->usuario."', '".$dto->senha."', 0);";
        if ($dto->id > 0)
            $query = "UPDATE login SET idExterno = ".$idExterno.", nome = '".$dto->nome."', usuario = '".$dto->usuario."', senha = '".$dto->senha."' WHERE id = ".$dto->id;

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
        $query = "UPDATE login SET removido = 1 WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM login WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new LoginDTO();
        $dto->id        = $record['id'];
        $dto->idExterno = $record['idExterno'];
        $dto->nome      = $record['nome'];
        $dto->usuario   = $record['usuario'];
        $dto->senha     = $record['senha'];
        mysql_free_result($recordSet);

        return $dto;
    }

    // Retorna os logins, exceto os registros marcados como "removido"
    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM login WHERE removido = 0";
        if (!empty($filter)) $query = $query." AND ".$filter;

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new LoginDTO();
            $dto->id        = $record['id'];
            $dto->idExterno = $record['idExterno'];
            $dto->nome      = $record['nome'];
            $dto->usuario   = $record['usuario'];
            $dto->senha     = $record['senha'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
