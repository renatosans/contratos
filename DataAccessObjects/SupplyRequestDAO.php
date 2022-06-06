<?php

class SupplyRequestDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO pedidoConsumivel VALUES (NULL, '".$dto->codigoCartaoEquipamento."', '".$dto->data." ".$dto->hora."', ".$dto->status.", '".$dto->observacao."');";
        if ($dto->id > 0)
            $query = "UPDATE pedidoConsumivel SET codigoCartaoEquipamento = '".$dto->codigoCartaoEquipamento."', data = '".$dto->data." ".$dto->hora."', status = ".$dto->status.", observacao = '".$dto->observacao."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM pedidoConsumivel WHERE id = ".$id.";";
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function GetRecordCount(){
        $recCount = 0;

        $query = "SELECT COUNT(*) as recCount FROM pedidoConsumivel";
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $record = mysqli_fetch_array($recordSet);
        if (!$record) return 0;
        $recCount = $record['recCount'];
        mysqli_free_result($recordSet);

        return $recCount;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, codigoCartaoEquipamento, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, status, observacao";
        $query = "SELECT ".$fieldList." FROM pedidoConsumivel WHERE id = ".$id.";";
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new SupplyRequestDTO();
        $dto->id                       = $record['id'];
        $dto->codigoCartaoEquipamento  = $record['codigoCartaoEquipamento'];
        $dto->data                     = $record['data'];
        $dto->hora                     = $record['hora'];
        $dto->status                   = $record['status'];
        $dto->observacao               = $record['observacao'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, codigoCartaoEquipamento, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, status, observacao";
        $query = "SELECT ".$fieldList." FROM pedidoConsumivel WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM pedidoConsumivel;";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new SupplyRequestDTO();
            $dto->id                       = $record['id'];
            $dto->codigoCartaoEquipamento  = $record['codigoCartaoEquipamento'];
            $dto->data                     = $record['data'];
            $dto->hora                     = $record['hora'];
            $dto->status                   = $record['status'];
            $dto->observacao               = $record['observacao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

    // Obtem o status da solicitação de consumível
    static function GetStatusAsText($status) {
        switch ($status)
        {
            case 1: return "Em andamento";
            case 2: return "Em espera";
            default: return "Finalizada";
        }
    }

}

?>
