<?php

class PartRequestDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO pedidoPecaReposicao VALUES (NULL, ".$dto->codigoChamadoServico.", '".$dto->data." ".$dto->hora."', '".$dto->destinatarios."');";
        if ($dto->id > 0)
            $query = "UPDATE pedidoPecaReposicao SET chamadoServico_id = ".$dto->codigoChamadoServico.", data = '".$dto->data." ".$dto->hora."', destinatarios = '".$dto->destinatarios."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM pedidoPecaReposicao WHERE id = ".$id.";";
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, chamadoServico_id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, destinatarios";
        $query = "SELECT ".$fieldList." FROM pedidoPecaReposicao WHERE id = ".$id.";";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new PartRequestDTO();
        $dto->id                       = $record['id'];
        $dto->codigoChamadoServico     = $record['chamadoServico_id'];
        $dto->data                     = $record['data'];
        $dto->hora                     = $record['hora'];
        $dto->destinatarios            = $record['destinatarios'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, chamadoServico_id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, destinatarios";
        $query = "SELECT ".$fieldList." FROM pedidoPecaReposicao WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM pedidoPecaReposicao;";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new PartRequestDTO();
            $dto->id                       = $record['id'];
            $dto->codigoChamadoServico     = $record['chamadoServico_id'];
            $dto->data                     = $record['data'];
            $dto->hora                     = $record['hora'];
            $dto->destinatarios            = $record['destinatarios'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
