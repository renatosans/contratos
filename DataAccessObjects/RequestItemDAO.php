<?php

class RequestItemDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function RequestItemDAO($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $codigoPedidoConsumivel = "'".$dto->codigoPedidoConsumivel."'";
        if (empty($dto->codigoPedidoConsumivel)) $codigoPedidoConsumivel = "null";

        $codigoPedidoPecaRepos = "'".$dto->codigoPedidoPecaRepos."'";
        if (empty($dto->codigoPedidoPecaRepos)) $codigoPedidoPecaRepos = "null";

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO solicitacaoItem VALUES (NULL, ".$codigoPedidoConsumivel.", ".$codigoPedidoPecaRepos.", '".$dto->codigoItem."', '".$dto->nomeItem."', '".$dto->quantidade."', '".$dto->total."');";
        if ($dto->id > 0)
            $query = "UPDATE solicitacaoItem SET pedidoConsumivel_id = ".$codigoPedidoConsumivel.", pedidoPecaReposicao_id = ".$codigoPedidoPecaRepos.", codigoItem = '".$dto->codigoItem."', nomeItem = '".$dto->nomeItem."', quantidade = ".$dto->quantidade.", total = ".$dto->total." WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM solicitacaoItem WHERE id = ".$id.";";
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, pedidoConsumivel_id, pedidoPecaReposicao_id, codigoItem, nomeItem, quantidade, total";
        $query = "SELECT ".$fieldList." FROM solicitacaoItem WHERE id = ".$id.";";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new RequestItemDTO();
        $dto->id                       = $record['id'];
        $dto->codigoPedidoConsumivel   = $record['pedidoConsumivel_id'];
        $dto->codigoPedidoPecaRepos    = $record['pedidoPecaReposicao_id'];
        $dto->codigoItem               = $record['codigoItem'];
        $dto->nomeItem                 = $record['nomeItem'];
        $dto->quantidade               = $record['quantidade'];
        $dto->total                    = $record['total'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, pedidoConsumivel_id, pedidoPecaReposicao_id, codigoItem, nomeItem, quantidade, total";
        $query = "SELECT ".$fieldList." FROM solicitacaoItem WHERE ".$filter.";";
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM solicitacaoItem;";

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new RequestItemDTO();
            $dto->id                       = $record['id'];
            $dto->codigoPedidoConsumivel   = $record['pedidoConsumivel_id'];
            $dto->codigoPedidoPecaRepos    = $record['pedidoPecaReposicao_id'];
            $dto->codigoItem               = $record['codigoItem'];
            $dto->nomeItem                 = $record['nomeItem'];
            $dto->quantidade               = $record['quantidade'];
            $dto->total                    = $record['total'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

}

?>
