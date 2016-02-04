<?php

class FixedAssetDAO{

    var $mysqlConnection;
    var $showErrors;


    #construtor
    function FixedAssetDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO ativofixo VALUES (NULL, '".$dto->businessPartnerCode."', '".$dto->businessPartnerName."', ".$dto->codigoCartaoEquipamento.", '".$dto->codigoItem."', '".$dto->descricao."', ".$dto->valorAquisicao.", '".$dto->dataInstalacao."', ".$dto->vidaUtil.")";
        if ($dto->id > 0)
            $query = "UPDATE ativofixo SET businessPartnerCode = '".$dto->businessPartnerCode."', businessPartnerName = '".$dto->businessPartnerName."', codigoCartaoEquipamento = ".$dto->codigoCartaoEquipamento.", codigoItem = '".$dto->codigoItem."', descricao = '".$dto->descricao."', valorAquisicao = ".$dto->valorAquisicao.", dataInstalacao = '".$dto->dataInstalacao."', vidaUtil = ".$dto->vidaUtil."  WHERE id = ".$dto->id;

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
        $query = "DELETE FROM ativofixo WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM ativofixo WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new FixedAssetDTO();
        $dto->id                      = $record['id'];
        $dto->businessPartnerCode     = $record['businessPartnerCode'];
        $dto->businessPartnerName     = $record['businessPartnerName'];
        $dto->codigoCartaoEquipamento = $record['codigoCartaoEquipamento'];
        $dto->codigoItem              = $record['codigoItem'];
        $dto->descricao               = $record['descricao'];
        $dto->valorAquisicao          = $record['valorAquisicao'];
        $dto->dataInstalacao          = $record['dataInstalacao'];
        $dto->vidaUtil                = $record['vidaUtil'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM ativofixo";
		if (isset($filter) && (!empty($filter))) $query = $query." WHERE ".$filter;

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new FixedAssetDTO();
            $dto->id                      = $record['id'];
            $dto->businessPartnerCode     = $record['businessPartnerCode'];
            $dto->businessPartnerName     = $record['businessPartnerName'];
            $dto->codigoCartaoEquipamento = $record['codigoCartaoEquipamento'];
            $dto->codigoItem              = $record['codigoItem'];
            $dto->descricao               = $record['descricao'];
            $dto->valorAquisicao          = $record['valorAquisicao'];
            $dto->dataInstalacao          = $record['dataInstalacao'];
            $dto->vidaUtil                = $record['vidaUtil'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
