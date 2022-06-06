<?php

class ExpenseDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $codigoChamado = "'".$dto->codigoChamado."'";
        if (empty($dto->codigoChamado)) $codigoChamado = "null";

        $codigoInsumo = "'".$dto->codigoInsumo."'";
        if (empty($dto->codigoInsumo)) $codigoInsumo = "null"; 

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO despesaChamado VALUES (NULL, ".$codigoChamado.", ".$codigoInsumo.", '".$dto->codigoItem."', '".$dto->nomeItem."', ".$dto->quantidade.", ".$dto->medicaoInicial.", ".$dto->medicaoFinal.", ".$dto->totalDespesa.", '".$dto->observacao."');";
        if ($dto->id > 0)
            $query = "UPDATE despesaChamado SET codigoChamado = ".$codigoChamado.", codigoInsumo = ".$codigoInsumo.", codigoItem = '".$dto->codigoItem."', nomeItem = '".$dto->nomeItem."', quantidade = ".$dto->quantidade.", medicaoInicial = ".$dto->medicaoInicial.", medicaoFinal = ".$dto->medicaoFinal.", totalDespesa = ".$dto->totalDespesa.", observacao = '".$dto->observacao."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM despesaChamado WHERE id = ".$id.";";
        $result = mysqli_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM despesaChamado WHERE id = ".$id.";";
        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ExpenseDTO();
        $dto->id                 = $record['id'];
        $dto->codigoChamado      = $record['codigoChamado'];
        $dto->codigoInsumo       = $record['codigoInsumo'];
        $dto->codigoItem         = $record['codigoItem'];
        $dto->nomeItem           = $record['nomeItem'];
        $dto->quantidade         = $record['quantidade'];
        $dto->medicaoInicial     = $record['medicaoInicial'];
        $dto->medicaoFinal       = $record['medicaoFinal'];
        $dto->totalDespesa       = $record['totalDespesa'];
        $dto->observacao         = $record['observacao'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM despesaChamado WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT * FROM despesaChamado;";

        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new ExpenseDTO();
            $dto->id                 = $record['id'];
            $dto->codigoChamado      = $record['codigoChamado'];
            $dto->codigoInsumo       = $record['codigoInsumo'];
            $dto->codigoItem         = $record['codigoItem'];
            $dto->nomeItem           = $record['nomeItem'];
            $dto->quantidade         = $record['quantidade'];
            $dto->medicaoInicial     = $record['medicaoInicial'];
            $dto->medicaoFinal       = $record['medicaoFinal'];
            $dto->totalDespesa       = $record['totalDespesa'];
            $dto->observacao         = $record['observacao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
