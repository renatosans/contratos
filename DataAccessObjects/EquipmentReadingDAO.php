<?php

class EquipmentReadingDAO {

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

        $query   = "SELECT * FROM ( ";
        $query  .= "SELECT EQP.customer AS codigoCliente, CLI.cardName + ' (' + CLI.cardCode + ')' AS nomeCliente, EQP.insID AS codigoEquipamento, EQP.manufSN AS serieEquipamento, MDL.id AS codigoModelo, MDL.modelo AS tagModelo, FAB.nome AS fabricante, ";
        $query  .= "LEITURA.data AS dataLeitura, CONTADOR.nome AS tipoMedidor, LEITURA.contagem AS medicao, LEITURA.ajusteContagem AS ajusteLeitura, CASE WHEN LEITURA.reset = 0 THEN 'N' ELSE 'S' END AS reset, FUNCIONARIO.firstName + ' ' + FUNCIONARIO.lastName AS assinaturaDatacopy, ";
        $query  .= "LEITURA.assinaturaCliente AS assinaturaCliente, LEITURA.obs AS observacao, FORMA.nome AS formaLeitura, ORIGEM.nome AS origemLeitura, ORIGEM.id AS idOrigemLeitura ";
        $query  .= "FROM MYSQL...leitura LEITURA ";
        $query  .= "JOIN OINS EQP ON LEITURA.codigoCartaoEquipamento = EQP.insID ";
        $query  .= "JOIN OCRD CLI ON EQP.customer = CLI.cardCode ";
        $query  .= "JOIN MYSQL...modeloEquipamento MDL ON EQP.U_Model = MDL.id ";
        $query  .= "JOIN MYSQL...fabricante FAB ON MDL.fabricante = FAB.id ";
        $query  .= "JOIN MYSQL...contador CONTADOR ON LEITURA.contador_id = CONTADOR.id ";
        $query  .= "JOIN OHEM FUNCIONARIO ON LEITURA.assinaturaDatacopy = FUNCIONARIO.empId ";
        $query  .= "JOIN MYSQL...formaLeitura FORMA ON LEITURA.formaLeitura_id = FORMA.id ";
        $query  .= "JOIN MYSQL...origemLeitura ORIGEM ON LEITURA.origemLeitura_id = ORIGEM.id ";
        $query .= "              ) LEITURAS ";
        if (isset($filter) && (!empty($filter))) $query = $query." WHERE ".$filter;
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query." ORDER BY dataLeitura");
        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new EquipmentReadingDTO();
            $dto->codigoCliente      = $record["codigoCliente"];
            $dto->nomeCliente        = $record["nomeCliente"];
            $dto->codigoEquipamento  = $record["codigoEquipamento"];
            $dto->serieEquipamento   = $record["serieEquipamento"];
            $dto->codigoModelo       = $record["codigoModelo"];
            $dto->tagModelo          = $record["tagModelo"];
            $dto->fabricante         = $record["fabricante"];
            $dto->dataLeitura        = $record["dataLeitura"];
            $dto->tipoMedidor        = $record["tipoMedidor"];
            $dto->medicao            = $record["medicao"];
            $dto->ajusteLeitura      = $record["ajusteLeitura"];
            $dto->reset              = $record["reset"];
            $dto->assinaturaDatacopy = $record["assinaturaDatacopy"];
            $dto->assinaturaCliente  = $record["assinaturaCliente"];
            $dto->observacao         = $record["observacao"];
            $dto->formaLeitura       = $record["formaLeitura"];
            $dto->origemLeitura      = $record["origemLeitura"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
