<?php

class ContractBonusDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO bonus VALUES (NULL, ".$dto->codigoContrato.", ".$dto->codigoSubContrato.", ".$dto->codigoContador.", ".$dto->de.", ".$dto->ate.", ".$dto->valor.", 0);";
        if ($dto->id > 0)
            $query = "UPDATE bonus SET contrato_id = ".$dto->codigoContrato.", subContrato_id = ".$dto->codigoSubContrato.", contador_id = ".$dto->codigoContador.", de = ".$dto->de.", ate = ".$dto->ate.", valor = ".$dto->valor." WHERE id = ".$dto->id.";";

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
        $query = "UPDATE bonus SET removido = 1 WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM bonus WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ContractBonusDTO();
        $dto->id                = $record['id'];
        $dto->codigoContrato    = $record['contrato_id'];
        $dto->codigoSubContrato = $record['subContrato_id'];
        $dto->codigoContador    = $record['contador_id'];
        $dto->de                = $record['de'];
        $dto->ate               = $record['ate'];
        $dto->valor             = $record['valor'];
        mysql_free_result($recordSet);

        return $dto;
    }

    // Retorna os bonus de contrato, exceto os registros marcados como "removido"
    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM bonus WHERE removido = 0 AND ".$filter;
        if (empty($filter)) $query = "SELECT * FROM bonus WHERE removido = 0";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new ContractBonusDTO();
            $dto->id                = $record['id'];
            $dto->codigoContrato    = $record['contrato_id'];
            $dto->codigoSubContrato = $record['subContrato_id'];
            $dto->codigoContador    = $record['contador_id'];
            $dto->de                = $record['de'];
            $dto->ate               = $record['ate'];
            $dto->valor             = $record['valor'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
