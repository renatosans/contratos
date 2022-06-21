<?php

class LaborExpenseDAO {

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }


    // Esta consulta utiliza Linked Server para consulta Cross Database, configurar fonte ODBC para o MySQL no servidor onde se
    // encontra o SQL SERVER, e fazer o mapeamento no SQL SERVER 
    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query  = "SELECT * FROM ( ";
        $query .= "SELECT EQP.customer AS codigoCliente, CLI.cardName + ' (' + CLI.cardCode + ')' AS nomeCliente, EQP.insID AS codigoEquipamento, EQP.manufSN AS serieEquipamento, MDL.id AS codigoModelo, MDL.modelo AS tagModelo, ";
        $query .= "FAB.FirmName AS fabricante, CHAM.id AS numeroChamado, CHAM.tempoAtendimento, MONTH(CHAM.dataAtendimento) AS mesReferencia, YEAR(CHAM.dataAtendimento) AS anoReferencia ";
        $query .= "FROM MYSQL...chamadoServico CHAM ";
        $query .= "JOIN OINS EQP ON CHAM.cartaoEquipamento = EQP.insID ";
        $query .= "JOIN OCRD CLI ON EQP.customer = CLI.cardCode ";
        $query .= "JOIN MYSQL...modeloEquipamento MDL ON EQP.U_Model = MDL.id ";
        $query .= "JOIN MYSQL...fabricante FAB ON MDL.fabricante = FAB.FirmCode ";
        $query .= "              ) LABOREXPENSES ";
        if (isset($filter) && (!empty($filter))) $query = $query." WHERE ".$filter;

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query." ORDER BY nomeCliente, serieEquipamento");
        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new LaborExpenseDTO();
            $dto->codigoCliente      = $record["codigoCliente"];
            $dto->nomeCliente        = $record["nomeCliente"];
            $dto->codigoEquipamento  = $record["codigoEquipamento"];
            $dto->serieEquipamento   = $record["serieEquipamento"];
            $dto->codigoModelo       = $record["codigoModelo"];
            $dto->tagModelo          = $record["tagModelo"];
            $dto->fabricante         = $record["fabricante"];
            $dto->numeroChamado      = $record["numeroChamado"];
            $dto->tempoAtendimento   = $record["tempoAtendimento"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
