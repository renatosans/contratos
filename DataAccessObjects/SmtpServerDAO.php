<?php

class SmtpServerDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function SmtpServerDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO smtpServer VALUES (NULL, '".$dto->nome."', '".$dto->endereco."', ".$dto->porta.", '".$dto->usuario."', '".$dto->senha."', '".$dto->requiresTLS."', '".$dto->defaultServer."');";
        if ($dto->id > 0)
            $query = "UPDATE smtpServer SET nome = '".$dto->nome."', endereco = '".$dto->endereco."', porta = ".$dto->porta.", usuario = '".$dto->usuario."', senha = '".$dto->senha."', requiresTLS = '".$dto->requiresTLS."', defaultServer = '".$dto->defaultServer."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM smtpServer WHERE id = ".$id.";";
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM smtpServer WHERE id = ".$id.";";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new SmtpServerDTO();
        $dto->id             = $record['id'];
        $dto->nome           = $record['nome'];
        $dto->endereco       = $record['endereco'];
        $dto->porta          = $record['porta'];
        $dto->usuario        = $record['usuario'];
        $dto->senha          = $record['senha'];
        $dto->requiresTLS    = $record['requiresTLS'];
        $dto->defaultServer  = $record['defaultServer'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM smtpServer WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT * FROM smtpServer;";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new SmtpServerDTO();
            $dto->id             = $record['id'];
            $dto->nome           = $record['nome'];
            $dto->endereco       = $record['endereco'];
            $dto->porta          = $record['porta'];
            $dto->usuario        = $record['usuario'];
            $dto->senha          = $record['senha'];
            $dto->requiresTLS    = $record['requiresTLS'];
            $dto->defaultServer  = $record['defaultServer'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
