<?php

class SmtpServerDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO smtpServer VALUES (NULL, '".$dto->nome."', '".$dto->endereco."', ".$dto->porta.", '".$dto->usuario."', '".$dto->senha."', '".$dto->requiresTLS."', '".$dto->defaultServer."');";
        if ($dto->id > 0)
            $query = "UPDATE smtpServer SET nome = '".$dto->nome."', endereco = '".$dto->endereco."', porta = ".$dto->porta.", usuario = '".$dto->usuario."', senha = '".$dto->senha."', requiresTLS = '".$dto->requiresTLS."', defaultServer = '".$dto->defaultServer."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM smtpServer WHERE id = ".$id.";";
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM smtpServer WHERE id = ".$id.";";
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
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
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM smtpServer WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT * FROM smtpServer;";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
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
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
