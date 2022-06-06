<?php

class BillingItemDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $query = "INSERT INTO itemFaturamento VALUES (NULL, ".$dto->codigoFaturamento.", ".$dto->contrato_id.", ".$dto->subContrato_id.", ".$dto->codigoCartaoEquipamento.", '".$dto->tipoLocacao."', ".$dto->counterId.", '".$dto->dataLeitura."', ".$dto->medicaoFinal.", ".$dto->medicaoInicial.", ".$dto->consumo.", ".$dto->ajuste.", ".$dto->franquia.", ".$dto->excedente.", ".$dto->tarifaSobreExcedente.", ".$dto->fixo.", ".$dto->variavel.", ".$dto->total.", ".$dto->acrescimoDesconto.");";

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
        $query = "DELETE FROM itemFaturamento WHERE id = ".$id;
        $result = mysqli_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, codigoFaturamento, contrato_id, subContrato_id, codigoCartaoEquipamento, tipoLocacao, counterId, DATE_FORMAT(dataLeitura,'%d/%m/%Y') as dataLeitura, medicaoFinal, medicaoInicial, consumo, ajuste, franquia, excedente, tarifaSobreExcedente, fixo, variavel, total, acrescimoDesconto";
        $query = "SELECT ".$fieldList." FROM itemFaturamento WHERE id = ".$id;
        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new BillingItemDTO();
        $dto->id                       = $record['id'];
        $dto->codigoFaturamento        = $record['codigoFaturamento'];
        $dto->contrato_id              = $record['contrato_id'];
        $dto->subContrato_id           = $record['subContrato_id'];
        $dto->codigoCartaoEquipamento  = $record['codigoCartaoEquipamento'];
        $dto->tipoLocacao              = $record['tipoLocacao'];
        $dto->counterId                = $record['counterId'];
        $dto->dataLeitura              = $record['dataLeitura'];
        $dto->medicaoFinal             = $record['medicaoFinal'];
        $dto->medicaoInicial           = $record['medicaoInicial'];
        $dto->consumo                  = $record['consumo'];
        $dto->ajuste                   = $record['ajuste'];
        $dto->franquia                 = $record['franquia'];
        $dto->excedente                = $record['excedente'];
        $dto->tarifaSobreExcedente     = $record['tarifaSobreExcedente'];
        $dto->fixo                     = $record['fixo'];
        $dto->variavel                 = $record['variavel'];
        $dto->total                    = $record['total'];
        $dto->acrescimoDesconto        = $record['acrescimoDesconto'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $fieldList = "id, codigoFaturamento, contrato_id, subContrato_id, codigoCartaoEquipamento, tipoLocacao, counterId, DATE_FORMAT(dataLeitura,'%d/%m/%Y') as dataLeitura, medicaoFinal, medicaoInicial, consumo, ajuste, franquia, excedente, tarifaSobreExcedente, fixo, variavel, total, acrescimoDesconto";
        $query = "SELECT ".$fieldList." FROM itemFaturamento WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM itemFaturamento";
        $dtoArray = $this->FetchArray($query);

        return $dtoArray;
    }

    function RetrieveRecordArray2($filter = null, $joins = null){
        // Verifica os parâmetros passados
        if ((empty($filter)) || (empty($joins))) return array();

        $fieldList = "ITEM.id, ITEM.codigoFaturamento, ITEM.contrato_id, ITEM.subContrato_id, ITEM.codigoCartaoEquipamento, ITEM.tipoLocacao, ITEM.counterId, DATE_FORMAT(ITEM.dataLeitura,'%d/%m/%Y') as dataLeitura, ITEM.medicaoFinal, ITEM.medicaoInicial, ITEM.consumo, ITEM.ajuste, ITEM.franquia, ITEM.excedente, ITEM.tarifaSobreExcedente, ITEM.fixo, ITEM.variavel, ITEM.total, ITEM.acrescimoDesconto";
        $query = "SELECT ".$fieldList." FROM itemFaturamento ITEM ".$joins." WHERE ".$filter;
        $dtoArray = $this->FetchArray($query);

        return $dtoArray;
    }

    function FetchArray($query){
        $dtoArray = array();

        $recordSet = mysqli_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new BillingItemDTO();
            $dto->id                       = $record['id'];
            $dto->codigoFaturamento        = $record['codigoFaturamento'];
            $dto->contrato_id              = $record['contrato_id'];
            $dto->subContrato_id           = $record['subContrato_id'];
            $dto->codigoCartaoEquipamento  = $record['codigoCartaoEquipamento'];
            $dto->tipoLocacao              = $record['tipoLocacao'];
            $dto->counterId                = $record['counterId'];
            $dto->dataLeitura              = $record['dataLeitura'];
            $dto->medicaoFinal             = $record['medicaoFinal'];
            $dto->medicaoInicial           = $record['medicaoInicial'];
            $dto->consumo                  = $record['consumo'];
            $dto->ajuste                   = $record['ajuste'];
            $dto->franquia                 = $record['franquia'];
            $dto->excedente                = $record['excedente'];
            $dto->tarifaSobreExcedente     = $record['tarifaSobreExcedente'];
            $dto->fixo                     = $record['fixo'];
            $dto->variavel                 = $record['variavel'];
            $dto->total                    = $record['total'];
            $dto->acrescimoDesconto        = $record['acrescimoDesconto'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
