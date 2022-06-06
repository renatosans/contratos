<?php

class MailingDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $enviarDemonstrativo = "0"; if ($dto->enviarDemonstrativo) $enviarDemonstrativo = "1";
        $ultimoEnvio = "'".$dto->ultimoEnvio."'";

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO mailing VALUES (NULL, '".$dto->businessPartnerCode."', '".$dto->businessPartnerName."', ".$dto->contrato_id.", ".$dto->subContrato_id.", ".$dto->diaFaturamento.", '".$dto->destinatarios."', ".$enviarDemonstrativo.", ".$ultimoEnvio.");";
        if ($dto->id > 0)
            $query = "UPDATE mailing SET businessPartnerCode = '".$dto->businessPartnerCode."', businessPartnerName = '".$dto->businessPartnerName."', contrato_id = ".$dto->contrato_id.", subContrato_id = ".$dto->subContrato_id.", diaFaturamento = ".$dto->diaFaturamento.", destinatarios = '".$dto->destinatarios."', enviarDemonstrativo = ".$enviarDemonstrativo.", ultimoEnvio = ".$ultimoEnvio." WHERE id = ".$dto->id;

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
        $query = "DELETE FROM mailing WHERE id = ".$id;
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT * FROM mailing WHERE id = ".$id;
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new MailingDTO();
        $dto->id                   = $record['id'];
        $dto->businessPartnerCode  = $record['businessPartnerCode'];
        $dto->businessPartnerName  = $record['businessPartnerName'];
        $dto->contrato_id          = $record['contrato_id'];
        $dto->subContrato_id       = $record['subContrato_id'];
        $dto->diaFaturamento       = $record['diaFaturamento'];
        $dto->destinatarios        = $record['destinatarios'];
        $dto->enviarDemonstrativo  = $record['enviarDemonstrativo'];
        $dto->ultimoEnvio          = $record['ultimoEnvio'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM mailing WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM mailing";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new MailingDTO();
            $dto->id                   = $record['id'];
            $dto->businessPartnerCode  = $record['businessPartnerCode'];
            $dto->businessPartnerName  = $record['businessPartnerName'];
            $dto->contrato_id          = $record['contrato_id'];
            $dto->subContrato_id       = $record['subContrato_id'];
            $dto->diaFaturamento       = $record['diaFaturamento'];
            $dto->destinatarios        = $record['destinatarios'];
            $dto->enviarDemonstrativo  = $record['enviarDemonstrativo'];
            $dto->ultimoEnvio          = $record['ultimoEnvio'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
