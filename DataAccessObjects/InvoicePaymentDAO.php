<?php

class InvoicePaymentDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function InvoicePaymentDAO($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrievePaymentsByBillOfExchange($filter = null){
        $dtoArray = array();

        $query = "SELECT OINV.Serial, INV1.Usage, OINV.CardCode, OINV.CardName, OINV.DocTotal, OBOE.BoeNum, ORCT.BoeSum, OBOE.U_ReceivedAmount, OBOE.U_PaymentDate, OSLP.SlpCode, OSLP.SlpName, OINV.U_demFaturamento FROM ORCT ";
        $query .= "JOIN RCT2 ON RCT2.DocNum = ORCT.DocEntry ";
        $query .= "JOIN OINV ON OINV.DocEntry = RCT2.DocEntry ";
        $query .= "JOIN INV1 ON INV1.DocEntry = OINV.DocEntry ";
        $query .= "JOIN OSLP ON OSLP.SlpCode = OINV.SlpCode ";
        $query .= "JOIN OBOE ON OBOE.BoeNum = ORCT.BoeNum ";

        if (!empty($filter)) $query .= " WHERE ".$filter;

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new InvoicePaymentDTO();
            $dto->serial          = $record['Serial'];
            $dto->tipo            = $record['Usage'];
            $dto->cardCode        = $record['CardCode'];
            $dto->cardName        = $record['CardName'];
            $dto->valorNotaFiscal = $record['DocTotal'];
            $dto->numeroBoleto    = $record['BoeNum'];
            $dto->valorBoleto     = $record['BoeSum'];
            $dto->quantiaRecebida = $record['U_ReceivedAmount'];
            $dto->date            = $record['U_PaymentDate'];
            $dto->slpCode         = $record['SlpCode'];
            $dto->slpName         = $record['SlpName'];
            $dto->demFaturamento  = $record['U_demFaturamento'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    function RetrieveOther($filter = null){
        $dtoArray = array();

        $query = "SELECT OINV.Serial, INV1.Usage, OINV.CardCode, OINV.CardName, OINV.DocTotal, ORCT.CashSum, ORCT.CheckSum, ORCT.TrsfrSum, ORCT.DocDueDate, OSLP.SlpCode, OSLP.SlpName, OINV.U_demFaturamento FROM ORCT ";
        $query .= "JOIN RCT2 ON RCT2.DocNum = ORCT.DocEntry ";
        $query .= "JOIN OINV ON OINV.DocEntry = RCT2.DocEntry ";
        $query .= "JOIN INV1 ON INV1.DocEntry = OINV.DocEntry ";
        $query .= "JOIN OSLP ON OSLP.SlpCode = OINV.SlpCode ";

        if (!empty($filter)) $query .= " WHERE ".$filter;

        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new InvoicePaymentDTO();
            $dto->serial          = $record['Serial'];
            $dto->tipo            = $record['Usage'];
            $dto->cardCode        = $record['CardCode'];
            $dto->cardName        = $record['CardName'];
            $dto->valorNotaFiscal = $record['DocTotal'];
            $dto->valorDinheiro   = $record['CashSum'];
            $dto->valorCheque     = $record['CheckSum'];
            $dto->valorDeposito   = $record['TrsfrSum'];
            $dto->date            = $record['DocDueDate'];
            $dto->slpCode         = $record['SlpCode'];
            $dto->slpName         = $record['SlpName'];
            $dto->demFaturamento  = $record['U_demFaturamento'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
