<?php

class AssetDepreciationDAO{

    var $mysqlConnection;
    var $showErrors;


    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO depreciacao VALUES (NULL, ".$dto->ativo.", ".$dto->mesReferencia.", ".$dto->anoReferencia.", ".$dto->intensidadeDesgaste.")";
        if ($dto->id > 0)
            $query = "UPDATE depreciacao SET ativo = ".$dto->ativo.", mesReferencia = ".$dto->mesReferencia.", anoReferencia = ".$dto->anoReferencia.", intensidadeDesgaste = ".$dto->intensidadeDesgaste."  WHERE id = ".$dto->id;

        $result = mysqli_query($query, $this->mysqlConnection);
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
        $query = "DELETE FROM depreciacao WHERE id = ".$id;
        $result = mysqli_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM depreciacao WHERE id = ".$id;
        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new AssetDepreciationDTO();
        $dto->id                  = $record["id"];
        $dto->ativo               = $record["ativo"];
        $dto->mesReferencia       = $record["mesReferencia"];
        $dto->anoReferencia       = $record["anoReferencia"];
        $dto->intensidadeDesgaste = $record["intensidadeDesgaste"];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query  = "SELECT * FROM depreciacao";
        if (isset($filter) && (!empty($filter))) $query = $query." WHERE ".$filter;

        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }

        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new AssetDepreciationDTO();
            $dto->id                  = $record["id"];
            $dto->ativo               = $record["ativo"];
            $dto->mesReferencia       = $record["mesReferencia"];
            $dto->anoReferencia       = $record["anoReferencia"];
            $dto->intensidadeDesgaste = $record["intensidadeDesgaste"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
