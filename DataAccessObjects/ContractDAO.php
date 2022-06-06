<?php

class ContractDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $dataRenovacao = "'".$dto->dataRenovacao."'";
        if (empty($dto->dataRenovacao)) $dataRenovacao = "null";
        $dataReajuste = "'".$dto->dataReajuste."'";
        if (empty($dto->dataReajuste)) $dataReajuste = "null";

        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO contrato VALUES (NULL, '".$dto->numero."', '".$dto->pn."', '".$dto->divisao."', ".$dto->contato.", ".$dto->status.", ".$dto->categoria.", '".$dto->dataAssinatura."', '".$dto->dataEncerramento."', '".$dto->inicioAtendimento."', '".$dto->fimAtendimento."', '".$dto->primeiraParcela."', ".$dto->parcelaAtual.", ".$dto->mesReferencia.", ".$dto->anoReferencia.", ".$dto->quantidadeParcelas.", ".$dto->global.", ".$dto->vendedor.", ".$dto->diaVencimento.", ".$dto->referencialVencimento.", ".$dto->diaLeitura.", ".$dto->referencialLeitura.", ".$dto->indiceReajuste.", ".$dataRenovacao.", ".$dataReajuste.", ".$dto->valorImplantacao.", ".$dto->quantParcelasImplantacao.", '".$dto->obs."', 0);";
        if ($dto->id > 0)
            $query = "UPDATE contrato SET numero = '".$dto->numero."', pn = '".$dto->pn."', divisao = '".$dto->divisao."', contato = ".$dto->contato.", status = ".$dto->status.", categoria = ".$dto->categoria.", assinatura = '".$dto->dataAssinatura."', encerramento = '".$dto->dataEncerramento."', inicioAtendimento = '".$dto->inicioAtendimento."', fimAtendimento = '".$dto->fimAtendimento."', primeiraParcela = '".$dto->primeiraParcela."', parcelaAtual = ".$dto->parcelaAtual.", mesReferencia = ".$dto->mesReferencia.", anoReferencia = ".$dto->anoReferencia.", quantidadeParcelas = ".$dto->quantidadeParcelas.", global = ".$dto->global.", vendedor = ".$dto->vendedor.", diaVencimento = ".$dto->diaVencimento.", referencialVencimento = ".$dto->referencialVencimento.", diaLeitura = ".$dto->diaLeitura.", referencialLeitura = ".$dto->referencialLeitura.", indicesReajuste_id = ".$dto->indiceReajuste.", dataRenovacao = ".$dataRenovacao.", dataReajuste = ".$dataReajuste.", valorImplantacao = ".$dto->valorImplantacao.", quantParcelasImplantacao = ".$dto->quantParcelasImplantacao.", obs = '".$dto->obs."' WHERE id = ".$dto->id.";";

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
        $query = "UPDATE contrato SET removido = 1 WHERE id = ".$id;
        $result = mysqli_query($this->mysqlConnection, $query);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $fieldList = "id, numero, pn, divisao, contato, status, categoria, DATE(assinatura) as assinatura, DATE(encerramento) as encerramento, ";
        $fieldList = $fieldList."DATE(inicioAtendimento) as inicioAtendimento, DATE(fimAtendimento) as fimAtendimento, DATE(primeiraParcela) as primeiraParcela, parcelaAtual, mesReferencia, anoReferencia, quantidadeParcelas, ";
        $fieldList = $fieldList."global, vendedor, diaVencimento, referencialVencimento, diaLeitura, referencialLeitura, indicesReajuste_id, DATE(dataRenovacao) as dataRenovacao, dataReajuste, valorImplantacao, quantParcelasImplantacao, obs";

        $query = "SELECT ".$fieldList." FROM contrato WHERE id = ".$id;
        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysqli_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new ContractDTO();
        $dto->id                        = $record['id'];
        $dto->numero                    = $record['numero'];
        $dto->pn                        = $record['pn'];
        $dto->divisao                   = $record['divisao'];
        $dto->contato                   = $record['contato'];
        $dto->status                    = $record['status'];
        $dto->categoria                 = $record['categoria'];
        $dto->dataAssinatura            = $record['assinatura'];
        $dto->dataEncerramento          = $record['encerramento'];
        $dto->inicioAtendimento         = $record['inicioAtendimento'];
        $dto->fimAtendimento            = $record['fimAtendimento'];
        $dto->primeiraParcela           = $record['primeiraParcela'];
        $dto->parcelaAtual              = $record['parcelaAtual'];
        $dto->mesReferencia             = $record['mesReferencia'];
        $dto->anoReferencia             = $record['anoReferencia'];
        $dto->quantidadeParcelas        = $record['quantidadeParcelas'];
        $dto->global                    = $record['global'];
        $dto->vendedor                  = $record['vendedor'];
        $dto->diaVencimento             = $record['diaVencimento'];
        $dto->referencialVencimento     = $record['referencialVencimento'];
        $dto->diaLeitura                = $record['diaLeitura'];
        $dto->referencialLeitura        = $record['referencialLeitura'];
        $dto->indiceReajuste            = $record['indicesReajuste_id'];
        $dto->dataRenovacao             = $record['dataRenovacao'];
        $dto->dataReajuste              = $record['dataReajuste'];
        $dto->valorImplantacao          = $record['valorImplantacao'];
        $dto->quantParcelasImplantacao  = $record['quantParcelasImplantacao'];
        $dto->obs                       = $record['obs'];
        mysqli_free_result($recordSet);

        return $dto;
    }

    // Retorna os contratos, exceto os registros marcados como "removido"
    function RetrieveRecordArray($filter = null){
        $fieldList = "id, numero, pn, divisao, contato, status, categoria, DATE(assinatura) as assinatura, DATE(encerramento) as encerramento, ";
        $fieldList = $fieldList."DATE(inicioAtendimento) as inicioAtendimento, DATE(fimAtendimento) as fimAtendimento, DATE(primeiraParcela) as primeiraParcela, parcelaAtual, mesReferencia, anoReferencia, quantidadeParcelas, ";
        $fieldList = $fieldList."global, vendedor, diaVencimento, referencialVencimento, diaLeitura, referencialLeitura, indicesReajuste_id, dataRenovacao, dataReajuste, valorImplantacao, quantParcelasImplantacao, obs";

        $query = "SELECT ".$fieldList." FROM contrato WHERE removido = 0 AND ".$filter;
        if (empty($filter)) $query = "SELECT ".$fieldList." FROM contrato WHERE removido = 0";
        $dtoArray = $this->FetchArray($query);

        return $dtoArray;
    }

    function RetrieveRecordArray2($filter = null, $joins = null){
        // Verifica os parâmetros passados
        if ((empty($filter)) || (empty($joins))) return array();

        $fieldList = "contrato.id, contrato.numero, contrato.pn, contrato.divisao, contrato.contato, contrato.status, contrato.categoria, DATE(contrato.assinatura) as assinatura, DATE(contrato.encerramento) as encerramento, ";
        $fieldList = $fieldList."DATE(contrato.inicioAtendimento) as inicioAtendimento, DATE(contrato.fimAtendimento) as fimAtendimento, DATE(contrato.primeiraParcela) as primeiraParcela, contrato.parcelaAtual, contrato.quantidadeParcelas, ";
        $fieldList = $fieldList."contrato.mesReferencia, contrato.anoReferencia, contrato.global, contrato.vendedor, contrato.diaVencimento, contrato.referencialVencimento, contrato.diaLeitura, contrato.referencialLeitura, ";
        $fieldList = $fieldList."contrato.indicesReajuste_id, contrato.dataRenovacao, contrato.dataReajuste, contrato.valorImplantacao, contrato.quantParcelasImplantacao, contrato.obs";
        $query = "SELECT ".$fieldList." FROM contrato ".$joins." WHERE contrato.removido = 0 AND ".$filter." ORDER BY contrato.encerramento";
        $dtoArray = $this->FetchArray($query);

        return $dtoArray;
    }

    function FetchArray($query){
        $dtoArray = array();

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new ContractDTO();
            $dto->id                        = $record['id'];
            $dto->numero                    = $record['numero'];
            $dto->pn                        = $record['pn'];
            $dto->divisao                   = $record['divisao'];
            $dto->contato                   = $record['contato'];
            $dto->status                    = $record['status'];
            $dto->categoria                 = $record['categoria'];
            $dto->dataAssinatura            = $record['assinatura'];
            $dto->dataEncerramento          = $record['encerramento'];
            $dto->inicioAtendimento         = $record['inicioAtendimento'];
            $dto->fimAtendimento            = $record['fimAtendimento'];
            $dto->primeiraParcela           = $record['primeiraParcela'];
            $dto->parcelaAtual              = $record['parcelaAtual'];
            $dto->mesReferencia             = $record['mesReferencia'];
            $dto->anoReferencia             = $record['anoReferencia'];
            $dto->quantidadeParcelas        = $record['quantidadeParcelas'];
            $dto->global                    = $record['global'];
            $dto->vendedor                  = $record['vendedor'];
            $dto->diaVencimento             = $record['diaVencimento'];
            $dto->referencialVencimento     = $record['referencialVencimento'];
            $dto->diaLeitura                = $record['diaLeitura'];
            $dto->referencialLeitura        = $record['referencialLeitura'];
            $dto->indiceReajuste            = $record['indicesReajuste_id'];
            $dto->dataRenovacao             = $record['dataRenovacao'];
            $dto->dataReajuste              = $record['dataReajuste'];
            $dto->valorImplantacao          = $record['valorImplantacao'];
            $dto->quantParcelasImplantacao  = $record['quantParcelasImplantacao'];
            $dto->obs                       = $record['obs'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

    static function GetStatusArray() {
        return array(1=>'Pendente', 2=>'Vigente', 3=>'Finalizado', 4=>'Cancelado', 5=>'Renovado', 6=>'Reajustado');
    }

    static function GetStatusAsText($status) {
        switch ($status)
        {
            case 1: return "Pendente";
            case 2: return "Vigente";
            case 3: return "Finalizado";
            case 4: return "Cancelado";
            case 5: return "Renovado";
            case 6: return "Reajustado";
            default: return "Pendente";
        }
    }

    static function GetCategoryAsText($category) {
        switch ($category)
        {
            case 1: return "Outsourcing";
            case 2: return "GED";
            case 3: return "Gestão TI";
            case 4: return "Assistência Técnica";
            case 5: return "Venda de Ativo";
            default: return "-Nenhuma Categoria-";
        }
    }

    // Obtem o período de cobertura do contrato e a parcela atual
    static function GetContractCoveragePeriod($mysqlConnection, $contractId) {
        $period = array();

        $fieldList = "DATE_FORMAT(inicioAtendimento,'%d/%m/%Y') as inicioAtendimento, DATE_FORMAT(fimAtendimento,'%d/%m/%Y') as fimAtendimento, CONCAT(parcelaAtual, '/', quantidadeParcelas) as parcelaAtual";
        $query = "SELECT ".$fieldList." FROM contrato WHERE id = ".$contractId;
        $recordSet = mysqli_query($mysqlConnection, $query);
        if (!$recordSet) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return null;

        while( $record = mysqli_fetch_array($recordSet) ) {
            $period['inicioAtendimento'] = $record['inicioAtendimento'];
            $period['fimAtendimento']    = $record['fimAtendimento'];
            $period['parcelaAtual']      = $record['parcelaAtual'];
        }
        mysqli_free_result($recordSet);

        return $period;
    }

}

?>
