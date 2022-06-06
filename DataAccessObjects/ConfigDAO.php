<?php

class ConfigDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO config VALUES (NULL, '".$dto->nomeParametro."', '".$dto->descricao."', '".$dto->tipoParametro."', '".$dto->valor."');";
        if ($dto->id > 0)
            $query = "UPDATE config SET nomeParametro = '".$dto->nomeParametro."', descricao = '".$dto->descricao."', tipoParametro = '".$dto->tipoParametro."', valor = '".$dto->valor."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM config WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM config WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ConfigDTO();
        $dto->id             = $record['id'];
        $dto->nomeParametro  = $record['nomeParametro'];
        $dto->descricao      = $record['descricao'];
        $dto->tipoParametro  = $record['tipoParametro'];
        $dto->valor          = $record['valor'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM config WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM config";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new ConfigDTO();
            $dto->id             = $record['id'];
            $dto->nomeParametro  = $record['nomeParametro'];
            $dto->descricao      = $record['descricao'];
            $dto->tipoParametro  = $record['tipoParametro'];
            $dto->valor          = $record['valor'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

    static function GetConfigurationParam($mysqlConnection, $paramName) {
        $configDAO = new ConfigDAO($mysqlConnection);
        $configDAO->showErrors = 1;

        $configParamArray = $configDAO->RetrieveRecordArray("nomeParametro='".$paramName."'");
        if (sizeof($configParamArray) != 1) return "";

        $configParam = $configParamArray[0];
        return $configParam->valor;
    }

}

?>
