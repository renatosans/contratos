<?php

class ReadingDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $codigoChamado = "'".$dto->codigoChamadoServico."'";
        $codigoConsumivel = "'".$dto->codigoConsumivel."'";
        if (empty($dto->codigoChamadoServico)) $codigoChamado = "null";
        if (empty($dto->codigoConsumivel)) $codigoConsumivel = "null";

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO leitura VALUES (NULL, '".$dto->codigoCartaoEquipamento."', ".$codigoChamado.", ".$codigoConsumivel.", '".$dto->data." ".$dto->hora."', '".$dto->codigoContador."', ".$dto->contagem.", '".$dto->ajusteContagem."', '".$dto->assinaturaDatacopy."', '".$dto->assinaturaCliente."', '".$dto->observacao."', ".$dto->origemLeitura.", ".$dto->formaLeitura.", ".$dto->reset.");";
        if ($dto->id > 0)
            $query = "UPDATE leitura SET codigoCartaoEquipamento = '".$dto->codigoCartaoEquipamento."', chamadoServico_id = ".$codigoChamado.", consumivel_id = ".$codigoConsumivel.", data = '".$dto->data." ".$dto->hora."', contador_id = '".$dto->codigoContador."', contagem = ".$dto->contagem.", ajusteContagem = '".$dto->ajusteContagem."', assinaturaDatacopy = '".$dto->assinaturaDatacopy."', assinaturaCliente = '".$dto->assinaturaCliente."', obs = '".$dto->observacao."', origemLeitura_id = ".$dto->origemLeitura.", formaLeitura_id = ".$dto->formaLeitura.", reset = ".$dto->reset." WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM leitura WHERE id = ".$id.";";
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, codigoCartaoEquipamento, chamadoServico_id, consumivel_id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, contador_id, contagem, ajusteContagem, assinaturaDatacopy, assinaturaCliente, obs, origemLeitura_id, formaLeitura_id, reset";
        $query = "SELECT ".$fieldList." FROM leitura WHERE id = ".$id.";";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ReadingDTO();
        $dto->id                      = $record['id'];
        $dto->codigoCartaoEquipamento = $record['codigoCartaoEquipamento'];
        $dto->codigoChamadoServico    = $record['chamadoServico_id'];
        $dto->codigoConsumivel        = $record['consumivel_id'];
        $dto->data                    = $record['data'];
        $dto->hora                    = $record['hora'];
        $dto->codigoContador          = $record['contador_id'];
        $dto->contagem                = $record['contagem'];
        $dto->ajusteContagem          = $record['ajusteContagem'];
        $dto->assinaturaDatacopy      = $record['assinaturaDatacopy'];
        $dto->assinaturaCliente       = $record['assinaturaCliente'];
        $dto->observacao              = $record['obs'];
        $dto->origemLeitura           = $record['origemLeitura_id'];
        $dto->formaLeitura            = $record['formaLeitura_id'];
        $dto->reset                   = $record['reset'];
        mysql_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, codigoCartaoEquipamento, chamadoServico_id, consumivel_id, DATE(data) as data, TIME_FORMAT(TIME(data), '%H:%i') as hora, contador_id, contagem, ajusteContagem, assinaturaDatacopy, assinaturaCliente, obs, origemLeitura_id, formaLeitura_id, reset";
        $query = "SELECT ".$fieldList." FROM leitura";
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
            $dto = new ReadingDTO();
            $dto->id                      = $record['id'];
            $dto->codigoCartaoEquipamento = $record['codigoCartaoEquipamento'];
            $dto->codigoChamadoServico    = $record['chamadoServico_id'];
            $dto->codigoConsumivel        = $record['consumivel_id'];
            $dto->data                    = $record['data'];
            $dto->hora                    = $record['hora'];
            $dto->codigoContador          = $record['contador_id'];
            $dto->contagem                = $record['contagem'];
            $dto->ajusteContagem          = $record['ajusteContagem'];
            $dto->assinaturaDatacopy      = $record['assinaturaDatacopy'];
            $dto->assinaturaCliente       = $record['assinaturaCliente'];
            $dto->observacao              = $record['obs'];
            $dto->origemLeitura           = $record['origemLeitura_id'];
            $dto->formaLeitura            = $record['formaLeitura_id'];
            $dto->reset                   = $record['reset'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

    function RetrieveReadingSources()
    {
        $readingSourceArray = array();

        $query = "SELECT * FROM origemLeitura";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $readingSourceArray;

        while( $record = mysql_fetch_array($recordSet) ){
            $readingSourceArray[$record['id']] = $record['nome'];
        }
        mysql_free_result($recordSet);

        return $readingSourceArray;
    }

    function RetrieveReadingKinds()
    {
        $readingKindArray = array();

        $query = "SELECT * FROM formaLeitura";
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $readingKindArray;

        while( $record = mysql_fetch_array($recordSet) ){
            $readingKindArray[$record['id']] = $record['nome'];
        }
        mysql_free_result($recordSet);

        return $readingKindArray;
    }

}

?>
