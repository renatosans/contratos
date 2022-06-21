<?php

class EquipmentExpenseDAO {

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
        $query .= "SELECT EQP.customer AS codigoCliente, CLI.cardName + ' (' + CLI.cardCode + ')' AS nomeCliente, EQP.insID AS codigoEquipamento, EQP.manufSN AS serieEquipamento, MDL.id AS codigoModelo, MDL.modelo AS tagModelo, FAB.nome AS fabricante, ";
        $query .= "CHAM.dataAtendimento AS dataDespesa, CASE WHEN DESP.codigoInsumo IS NULL THEN CAST(DESP.quantidade AS VARCHAR) + 'UN ' + DESP.nomeItem ELSE (TINS.tipoInsumo + '( Número do Chamado: ' + CAST(CHAM.id AS VARCHAR) + ' )') END AS descricaoDespesa, ";
        $query .= "PROD.AvgPrice AS precoMedio, PROD.U_Durability AS durabilidade, DESP.totalDespesa ";
        $query .= "FROM MYSQL.addoncontratos..chamadoServico CHAM ";
        $query .= "JOIN MYSQL.addoncontratos..despesaChamado DESP ON CHAM.id = DESP.codigoChamado ";
        $query .= "LEFT JOIN MYSQL.addoncontratos..insumo INS ON INS.id = DESP.codigoInsumo ";
        $query .= "LEFT JOIN MYSQL.addoncontratos..tipoInsumo TINS ON INS.tipoInsumo = TINS.id ";
        $query .= "JOIN OINS EQP ON CHAM.cartaoEquipamento = EQP.insID ";
        $query .= "LEFT JOIN OITM PROD ON PROD.ItemCode = DESP.codigoItem COLLATE database_default ";
        $query .= "JOIN OCRD CLI ON EQP.customer = CLI.cardCode ";
        $query .= "JOIN MYSQL.addoncontratos..modeloEquipamento MDL ON EQP.U_Model = MDL.id ";
        $query .= "JOIN MYSQL.addoncontratos..fabricante FAB ON MDL.fabricante = FAB.id ";
        $query .= "              UNION ALL                          ";
        $query .= "SELECT EQP.customer AS codigoCliente, CLI.cardName + ' (' + CLI.cardCode + ')' AS nomeCliente, EQP.insID AS codigoEquipamento, EQP.manufSN AS serieEquipamento, MDL.id AS codigoModelo, MDL.modelo AS tagModelo, FAB.nome AS fabricante, ";
        $query .= "PED.data AS dataDespesa, CAST(ITM.quantidade AS VARCHAR) + 'UN ' + ITM.nomeItem AS descricaoDespesa, PROD.AvgPrice AS precoMedio, PROD.U_Durability AS durabilidade, ITM.total AS totalDespesa ";
        $query .= "FROM MYSQL.addoncontratos..pedidoConsumivel PED ";
        $query .= "JOIN MYSQL.addoncontratos..solicitacaoItem ITM ON ITM.pedidoConsumivel_id = PED.id ";
        $query .= "JOIN OINS EQP ON PED.codigoCartaoEquipamento = EQP.insID ";
        $query .= "LEFT JOIN OITM PROD ON PROD.ItemCode = ITM.codigoItem COLLATE database_default ";
        $query .= "JOIN OCRD CLI ON EQP.customer = CLI.cardCode ";
        $query .= "JOIN MYSQL.addoncontratos..modeloEquipamento MDL ON EQP.U_Model = MDL.id ";
        $query .= "JOIN MYSQL.addoncontratos..fabricante FAB ON MDL.fabricante = FAB.id ";
        $query .= "              ) DESPESAS ";
        if (isset($filter) && (!empty($filter))) $query = $query." WHERE ".$filter;

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query." ORDER BY nomeCliente, dataDespesa, serieEquipamento");
        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new EquipmentExpenseDTO();
            $dto->codigoCliente      = $record["codigoCliente"];
            $dto->nomeCliente        = $record["nomeCliente"];
            $dto->codigoEquipamento  = $record["codigoEquipamento"];
            $dto->serieEquipamento   = $record["serieEquipamento"];
            $dto->codigoModelo       = $record["codigoModelo"];
            $dto->tagModelo          = $record["tagModelo"];
            $dto->fabricante         = $record["fabricante"]; 
            $dto->dataDespesa        = $record["dataDespesa"];
            $dto->descricaoDespesa   = $record["descricaoDespesa"];
            $dto->precoMedioUnitario = $record["precoMedio"];
            $dto->vidaUtil           = $record["durabilidade"];
            $dto->totalDespesa       = $record["totalDespesa"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
