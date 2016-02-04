<?php

class ActionLogDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function ActionLogDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $tipoAgregacao = "'".$dto->tipoAgregacao."'";
        if (empty($dto->tipoAgregacao)) $tipoAgregacao = "null";
        $idAgregacao = $dto->idAgregacao;
        if (empty($dto->idAgregacao)) $idAgregacao = "null";

        $query = "INSERT INTO historico VALUES (NULL, ".$dto->login_id.", '".$dto->data." ".$dto->hora."', '".$dto->transacao."', ".$tipoAgregacao.", ".$idAgregacao.", '".$dto->tipoObjeto."', ".$dto->idObjeto.", '".$dto->propriedade."', '".$dto->valor."');";

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
        $query = "DELETE FROM historico WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, login_id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, transacao, tipoAgregacao, idAgregacao, tipoObjeto, idObjeto, propriedade, valor";
        $query = "SELECT ".$fieldList." FROM historico WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ActionLogDTO();
        $dto->id            = $record['id'];
        $dto->login_id      = $record['login_id'];
        $dto->data          = $record['data'];
        $dto->hora          = $record['hora'];
        $dto->transacao     = $record['transacao'];
        $dto->tipoAgregacao = $record['tipoAgregacao'];
        $dto->idAgregacao   = $record['idAgregacao'];
        $dto->tipoObjeto    = $record['tipoObjeto'];
        $dto->idObjeto      = $record['idObjeto'];
        $dto->propriedade   = $record['propriedade'];
        $dto->valor         = $record['valor'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, login_id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, transacao, tipoAgregacao, idAgregacao, tipoObjeto, idObjeto, propriedade, valor";
        $query = "SELECT ".$fieldList." FROM historico WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM historico";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new ActionLogDTO();
            $dto->id            = $record['id'];
            $dto->login_id      = $record['login_id'];
            $dto->data          = $record['data'];
            $dto->hora          = $record['hora'];
            $dto->transacao     = $record['transacao'];
            $dto->tipoAgregacao = $record['tipoAgregacao'];
            $dto->idAgregacao   = $record['idAgregacao'];
            $dto->tipoObjeto    = $record['tipoObjeto'];
            $dto->idObjeto      = $record['idObjeto'];
            $dto->propriedade   = $record['propriedade'];
            $dto->valor         = $record['valor'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
