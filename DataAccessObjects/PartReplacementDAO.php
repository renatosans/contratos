<?php

class PartReplacementDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT DATE(CHAM.dataAbertura) as dataAbertura, DESP.* FROM despesaChamado DESP JOIN chamadoServico CHAM ON DESP.codigoChamado = CHAM.id";
        if (!empty($filter)) $query = $query." WHERE ".$filter;

        $recordSet = mysqli_query($this->mysqlConnection, $query);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysqli_error());
            echo '<br/><br/>';
        }
        $recordCount = mysqli_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysqli_fetch_array($recordSet) ){
            $dto = new PartReplacementDTO();
            $dto->dataChamado        = $record['dataAbertura'];
            $dto->codigoChamado      = $record['codigoChamado'];
            $dto->codigoItem         = $record['codigoItem'];
            $dto->nomeItem           = $record['nomeItem'];
            $dto->quantidade         = $record['quantidade'];
            $dto->totalDespesa       = $record['totalDespesa'];
            $dto->observacao         = $record['observacao'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysqli_free_result($recordSet);

        return $dtoArray;
    }

}

?>
