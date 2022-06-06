<?php

class IndirectCostDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO custoIndireto VALUES (NULL, '".$dto->data." ".$dto->hora."', ".$dto->solicitante.", '".$dto->infoSolicitante."', ".$dto->codigoInsumo.", ".$dto->medicaoInicial.", ".$dto->medicaoFinal.", ".$dto->total.", '".$dto->observacao."');";
        if ($dto->id > 0)
            $query = "UPDATE custoIndireto SET data = '".$dto->data." ".$dto->hora."', solicitante = ".$dto->solicitante.", infoSolicitante = '".$dto->infoSolicitante."', codigoInsumo = ".$dto->codigoInsumo.", medicaoInicial = ".$dto->medicaoInicial.", medicaoFinal = ".$dto->medicaoFinal.", total = ".$dto->total.", observacao = '".$dto->observacao."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM custoIndireto WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, solicitante, infoSolicitante, codigoInsumo, medicaoInicial, medicaoFinal, total, observacao";
        $query = "SELECT ".$fieldList." FROM custoIndireto WHERE id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new IndirectCostDTO();
        $dto->id              = $record['id'];
        $dto->data            = $record['data'];
        $dto->hora            = $record['hora'];
        $dto->solicitante     = $record['solicitante'];
        $dto->infoSolicitante = $record['infoSolicitante'];
        $dto->codigoInsumo    = $record['codigoInsumo'];
        $dto->medicaoInicial  = $record['medicaoInicial'];
        $dto->medicaoFinal    = $record['medicaoFinal'];
        $dto->total           = $record['total'];
        $dto->observacao      = $record['observacao'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null) {
        $dtoArray = array();

        $fieldList = "id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, solicitante, infoSolicitante, codigoInsumo, medicaoInicial, medicaoFinal, total, observacao";
        $query = "SELECT ".$fieldList." FROM custoIndireto WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM custoIndireto";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new IndirectCostDTO();
            $dto->id              = $record['id'];
            $dto->data            = $record['data'];
            $dto->hora            = $record['hora'];
            $dto->solicitante     = $record['solicitante'];
            $dto->infoSolicitante = $record['infoSolicitante'];
            $dto->codigoInsumo    = $record['codigoInsumo'];
            $dto->medicaoInicial  = $record['medicaoInicial'];
            $dto->medicaoFinal    = $record['medicaoFinal'];
            $dto->total           = $record['total'];
            $dto->observacao      = $record['observacao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

    // Busca os chamados relacionados ao custo indireto
    function GetDistributedExpenses($indirectCostId){
        $serviceCallArray = array();

        $query = "SELECT * FROM despesaDistribuida WHERE custoIndireto_id=".$indirectCostId;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $serviceCallArray;

        while( $record = mysql_fetch_array($recordSet) ){
            array_push($serviceCallArray, $record['chamadoServico_id']);
        }
        mysql_free_result($recordSet);

        return $serviceCallArray;
    }

    // Obtem um array com os ids dos custos indiretos, recece o filtro da query
    function GetIds($filter){
        $idArray = array();

        $query = "SELECT * FROM despesaDistribuida WHERE ".$filter;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $idArray;

        while( $record = mysql_fetch_array($recordSet) ){
            array_push($idArray, $record['custoIndireto_id']);
        }
        mysql_free_result($recordSet);

        return $idArray;
    }

    // Associa um chamado ao custo indireto
    function AddDistributedExpense($serviceCallId, $indirectCostId) {
        $query = "INSERT INTO despesaDistribuida VALUES (".$serviceCallId.", ".$indirectCostId.");";
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    // Remove um chamado associado ao custo indireto
    function RemoveDistributedExpense($serviceCallId, $indirectCostId) {
        $query = "DELETE FROM despesaDistribuida WHERE chamadoServico_id = ".$serviceCallId." AND custoIndireto_id = ".$indirectCostId;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }
}

?>
