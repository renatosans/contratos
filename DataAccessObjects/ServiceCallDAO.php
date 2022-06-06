<?php

class ServiceCallDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection) {
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $dataAbertura = "'".$dto->dataAbertura." ".$dto->horaAbertura."'";
        if (empty($dto->dataAbertura)) $dataAbertura = "null";

        $dataFechamento = "'".$dto->dataFechamento." ".$dto->horaFechamento."'";
        if (empty($dto->dataFechamento)) $dataFechamento = "null";

        $dataAtendimento = "'".$dto->dataAtendimento." ".$dto->horaAtendimento."'";
        if (empty($dto->dataAtendimento)) $dataAtendimento = "null"; 

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO chamadoServico VALUES (NULL, NULL, '".$dto->defeito."', ".$dataAbertura.", ".$dataFechamento.", ".$dataAtendimento.", '".$dto->tempoAtendimento."', '".$dto->businessPartnerCode."', '".$dto->contato."', ".$dto->status.", ".$dto->tipo.", ".$dto->abertoPor.", ".$dto->tecnico.", ".$dto->prioridade.", ".$dto->codigoCartaoEquipamento.", '".$dto->modelo."', '".$dto->fabricante."', '".$dto->observacaoTecnica."', '".$dto->sintoma."', '".$dto->causa."', '".$dto->acao."');";
        if ($dto->id > 0)
            $query = "UPDATE chamadoServico SET defeito = '".$dto->defeito."', dataAbertura = ".$dataAbertura.", dataFechamento = ".$dataFechamento.", dataAtendimento = ".$dataAtendimento.", tempoAtendimento = '".$dto->tempoAtendimento."', businessPartnerCode = '".$dto->businessPartnerCode."', contato = '".$dto->contato."', status = ".$dto->status.", tipo = ".$dto->tipo.", abertoPor = ".$dto->abertoPor.", tecnico = ".$dto->tecnico.", prioridade = ".$dto->prioridade.", cartaoEquipamento = ".$dto->codigoCartaoEquipamento.", modelo = '".$dto->modelo."', fabricante = '".$dto->fabricante."', observacaoTecnica = '".$dto->observacaoTecnica."', sintoma = '".$dto->sintoma."', causa = '".$dto->causa."', acao = '".$dto->acao."' WHERE id = ".$dto->id.";";

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
        $query = "DELETE FROM chamadoServico WHERE id = ".$id;
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function GetRecordCount($filter) {
        $recCount = 0;

        $query = "SELECT COUNT(*) as recCount FROM chamadoServico WHERE ".$filter;
        if (empty($filter)) $query = "SELECT COUNT(*) as recCount FROM chamadoServico";

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

        $fieldList = "id, defeito, DATE(dataAbertura) as dataAbertura, TIME_FORMAT(TIME(dataAbertura), '%H:%i') as horaAbertura, ";
        $fieldList = $fieldList."DATE(dataFechamento) as dataFechamento, TIME_FORMAT(TIME(dataFechamento), '%H:%i') as horaFechamento, ";
        $fieldList = $fieldList."DATE(dataAtendimento) as dataAtendimento, TIME_FORMAT(TIME(dataAtendimento), '%H:%i') as horaAtendimento, ";
        $fieldList = $fieldList."TIME_FORMAT(TIME(tempoAtendimento), '%H:%i') as tempoAtendimento, TIME_TO_SEC(tempoAtendimento) as duracaoEmSegundos, businessPartnerCode, contato, ";
        $fieldList = $fieldList."status, tipo, abertoPor, tecnico, prioridade, cartaoEquipamento, modelo, fabricante, observacaoTecnica, sintoma, causa, acao";

        $query = "SELECT ".$fieldList." FROM chamadoServico WHERE id = ".$id;
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ServiceCallDTO();
        $dto->id                      = $record['id'];
        $dto->defeito                 = $record['defeito'];
        $dto->dataAbertura            = $record['dataAbertura'];
        $dto->horaAbertura            = $record['horaAbertura'];
        $dto->dataFechamento          = $record['dataFechamento'];
        $dto->horaFechamento          = $record['horaFechamento'];
        $dto->dataAtendimento         = $record['dataAtendimento'];
        $dto->horaAtendimento         = $record['horaAtendimento'];
        $dto->tempoAtendimento        = $record['tempoAtendimento'];
        $dto->duracaoEmSegundos       = $record['duracaoEmSegundos'];
        $dto->businessPartnerCode     = $record['businessPartnerCode'];
        $dto->contato                 = $record['contato'];
        $dto->status                  = $record['status'];
        $dto->tipo                    = $record['tipo'];
        $dto->abertoPor               = $record['abertoPor'];
        $dto->tecnico                 = $record['tecnico'];
        $dto->prioridade              = $record['prioridade'];
        $dto->codigoCartaoEquipamento = $record['cartaoEquipamento'];
        $dto->modelo                  = $record['modelo'];
        $dto->fabricante              = $record['fabricante'];
        $dto->observacaoTecnica       = $record['observacaoTecnica'];
        $dto->sintoma                 = $record['sintoma'];
        $dto->causa                   = $record['causa'];
        $dto->acao                    = $record['acao'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $fieldList = "id, defeito, DATE(dataAbertura) as dataAbertura, TIME_FORMAT(TIME(dataAbertura), '%H:%i') as horaAbertura, ";
        $fieldList = $fieldList."DATE(dataFechamento) as dataFechamento, TIME_FORMAT(TIME(dataFechamento), '%H:%i') as horaFechamento, ";
        $fieldList = $fieldList."DATE(dataAtendimento) as dataAtendimento, TIME_FORMAT(TIME(dataAtendimento), '%H:%i') as horaAtendimento, ";
        $fieldList = $fieldList."TIME_FORMAT(TIME(tempoAtendimento), '%H:%i') as tempoAtendimento, businessPartnerCode, contato, ";
        $fieldList = $fieldList."status, tipo, abertoPor, tecnico, prioridade, cartaoEquipamento, modelo, fabricante, observacaoTecnica, sintoma, causa, acao";

        $query = "SELECT ".$fieldList." FROM chamadoServico WHERE ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM chamadoServico";

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new ServiceCallDTO();
            $dto->id                      = $record['id'];
            $dto->defeito                 = $record['defeito'];
            $dto->dataAbertura            = $record['dataAbertura'];
            $dto->horaAbertura            = $record['horaAbertura'];
            $dto->dataFechamento          = $record['dataFechamento'];
            $dto->horaFechamento          = $record['horaFechamento'];
            $dto->dataAtendimento         = $record['dataAtendimento'];
            $dto->horaAtendimento         = $record['horaAtendimento'];
            $dto->tempoAtendimento        = $record['tempoAtendimento'];
            $dto->businessPartnerCode     = $record['businessPartnerCode'];
            $dto->contato                 = $record['contato'];
            $dto->status                  = $record['status'];
            $dto->tipo                    = $record['tipo'];
            $dto->abertoPor               = $record['abertoPor'];
            $dto->tecnico                 = $record['tecnico'];
            $dto->prioridade              = $record['prioridade'];
            $dto->codigoCartaoEquipamento = $record['cartaoEquipamento'];
            $dto->modelo                  = $record['modelo'];
            $dto->fabricante              = $record['fabricante'];
            $dto->observacaoTecnica       = $record['observacaoTecnica'];
            $dto->sintoma                 = $record['sintoma'];
            $dto->causa                   = $record['causa'];
            $dto->acao                    = $record['acao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

    static function RetrieveServiceCallStatuses($sqlServerConnection)
    {
        $callStatusArray = array();

        $query = "SELECT StatusID, Name FROM OSCS ORDER BY StatusID";
        $recordSet = sqlsrv_query($sqlServerConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/><br/>';
        }

        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $callStatusArray[$record['StatusID']] = $record['Name'];
        }
        sqlsrv_free_stmt($recordSet);

        return $callStatusArray;
    }

    static function RetrieveServiceCallTypes($sqlServerConnection)
    {
        $callTypeArray = array();

        $query = "SELECT CallTypeID, Name FROM OSCT ORDER BY CallTypeID";
        $recordSet = sqlsrv_query($sqlServerConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/><br/>';
        }

        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $callTypeArray[$record['CallTypeID']] = $record['Name'];
        }
        sqlsrv_free_stmt($recordSet);

        return $callTypeArray;
    }

    static function GetPriorityAsText($priority)
    {
        switch ($priority)
        {
            case 1: return "Baixa";
            case 2: return "Média";
            case 3: return "Alta";
            default: return "Urgente";
        }
    }

}

?>
