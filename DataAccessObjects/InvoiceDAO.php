<?php

class InvoiceDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Alterar apenas campos de usuário aqui (começados com U_ ), é proibido alterar campos do SAP B1
        $query = "UPDATE OINV SET U_demFaturamento = ".$dto->demFaturamento." WHERE DocNum = '".$dto->docNum."'";

        $result = sqlsrv_query($this->sqlserverConnection, $query);
        if ($result) {
            return $dto->docNum;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/>';
        }
        return null;
    }

    function RetrieveRecord($docNum) {
        $dto = null;

        $fieldList = "OINV.DocNum, OINV.Serial, OINV.DocDate, OINV.CardCode, OINV.CardName, OINV.Comments, OINV.DocDueDate, OINV.DocTotal, OINV.U_demFaturamento";
        $query = "SELECT ".$fieldList." FROM OINV WHERE DocNum = '".$docNum."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new InvoiceDTO();
        $dto->docNum         = $record["DocNum"];
        $dto->serial         = $record["Serial"];
        $dto->docDate        = $record['DocDate'];
        $dto->cardCode       = $record['CardCode'];
        $dto->cardName       = $record['CardName'];
        $dto->comments       = $record['Comments'];
        $dto->docDueDate     = $record['DocDueDate'];
        $dto->docTotal       = $record['DocTotal'];
        $dto->demFaturamento = $record['U_demFaturamento'];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($joins = null, $filter = null){
        $dtoArray = array();
        if (empty($filter)) return $dtoArray(); // retorna a lista vazia caso não especifique um filtro

        $fieldList = "OINV.DocNum, OINV.Serial, OINV.DocDate, OINV.CardCode, OINV.CardName, OINV.Comments, OINV.DocDueDate, OINV.DocTotal, OINV.U_demFaturamento";
        $query = "SELECT ".$fieldList." FROM OINV ".$joins." WHERE ".$filter;

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new InvoiceDTO();
            $dto->docNum             = $record['DocNum'];
            $dto->serial             = $record['Serial'];
            $dto->docDate            = $record['DocDate'];
            $dto->cardCode           = $record['CardCode'];
            $dto->cardName           = $record['CardName'];
            $dto->comments           = $record['Comments'];
            $dto->docDueDate         = $record['DocDueDate'];
            $dto->docTotal           = $record['DocTotal'];
            $dto->demFaturamento     = $record['U_demFaturamento'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    function RetrieveReturnedInvoices($filter = null){
        $dtoArray = array();
        if (empty($filter)) return $dtoArray(); // retorna a lista vazia caso não especifique um filtro

        $query = "SELECT DISTINCT T3.DocNum, T3.Serial, T3.DocDate, T3.CardCode, T3.CardName, T3.Comments, T3.DocDueDate, T3.DocTotal, T3.U_demFaturamento FROM ";
        $query = $query."ORIN T0 INNER JOIN RIN1 T1 ON T0.DocEntry = T1.DocEntry LEFT JOIN ";
        $query = $query."INV1 T2 ON T1.BaseEntry = T2.DocEntry AND T1.BaseLine = T2.LineNum AND T1.BaseType = 13 INNER JOIN ";
        $query = $query."OINV T3 ON T2.DocEntry = T3.DocEntry WHERE ".$filter;

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new InvoiceDTO();
            $dto->docNum             = $record['DocNum'];
            $dto->serial             = $record['Serial'];
            $dto->docDate            = $record['DocDate'];
            $dto->cardCode           = $record['CardCode'];
            $dto->cardName           = $record['CardName'];
            $dto->comments           = $record['Comments'];
            $dto->docDueDate         = $record['DocDueDate'];
            $dto->docTotal           = $record['DocTotal'];
            $dto->demFaturamento     = $record['U_demFaturamento'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    function RetrieveInvoiceItems($docNum) {
        $dtoArray = array();

        $query = "SELECT * FROM INV1 JOIN OUSG ON INV1.Usage = OUSG.ID WHERE DocEntry=".$docNum;
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new InvoiceItemDTO();
            $dto->itemCode     = $record['ItemCode'];
            $dto->description  = $record['Dscription'];
            $dto->quantity     = $record['Quantity'];
            $dto->lineTotal    = $record['LineTotal'];
            $dto->usage        = $record['Usage'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetUsages($sqlServerConnection) {
        $usageArray = array();

        $query = "SELECT ID, Usage FROM OUSG ORDER BY ID";
        $recordSet = sqlsrv_query($sqlServerConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/><br/>';
        }

        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $usageArray[$record['ID']] = $record['Usage'];
        }
        sqlsrv_free_stmt($recordSet);

        return $usageArray;
    }

}

?>
