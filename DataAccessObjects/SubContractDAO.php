<?php

class SubContractDAO{

    var $mysqlConnection;
    var $showErrors;

    #construtor
    function __construct($mysqlConnection){
        $this->mysqlConnection = $mysqlConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        // Monta a query dependendo do id como INSERT ou UPDATE
        $query = "INSERT INTO subcontrato VALUES (NULL, ".$dto->codigoContrato.", ".$dto->codigoTipoContrato.", 0);";
        if ($dto->id > 0)
            $query = "UPDATE subcontrato SET contrato_id = ".$dto->codigoContrato.", tipoContrato_id = ".$dto->codigoTipoContrato." WHERE id = ".$dto->id.";";

        $result = mysql_query($query, $this->mysqlConnection);
        if ($result) {
            $insertId = mysql_insert_id($this->mysqlConnection);
            if ($insertId == null) return $dto->id;
            return $insertId;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return null;
    }

    function DeleteRecord($id){
        $query = "UPDATE subcontrato SET removido = 1 WHERE id = ".$id;
        $result = mysql_query($query, $this->mysqlConnection);

        if ((!$result) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/>';
        }
        return $result;
    }

    function RetrieveRecord($id){
        $dto = null;

        $query = "SELECT SUBC.*, TIPC.sigla as siglaTipoContrato FROM subContrato SUBC JOIN tipoContrato TIPC ON SUBC.tipocontrato_id = TIPC.id WHERE SUBC.id = ".$id;
        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount != 1) return null;

        $record = mysql_fetch_array($recordSet);
        if (!$record) return null;
        $dto = new SubContractDTO();
        $dto->id                 = $record['id'];
        $dto->codigoContrato     = $record['contrato_id'];
        $dto->codigoTipoContrato = $record['tipoContrato_id'];
        $dto->siglaTipoContrato  = $record['siglaTipoContrato'];
        mysql_free_result($recordSet);

        return $dto;
    }

    // Retorna os subcontratos, exceto os registros marcados como "removido"
    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT SUBC.*, TIPC.sigla as siglaTipoContrato FROM subContrato SUBC JOIN tipoContrato TIPC ON SUBC.tipocontrato_id = TIPC.id WHERE removido = 0";
        if (!empty($filter)) $query = $query." AND ".$filter;

        $recordSet = mysql_query($query, $this->mysqlConnection);
        if ((!$recordSet) && ($this->showErrors)) {
            print_r(mysql_error());
            echo '<br/><br/>';
        }
        $recordCount = mysql_num_rows($recordSet);
        if ($recordCount == 0) return $dtoArray;

        $index = 0;
        while( $record = mysql_fetch_array($recordSet) ){
            $dto = new SubContractDTO();
            $dto->id                 = $record['id'];
            $dto->codigoContrato     = $record['contrato_id'];
            $dto->codigoTipoContrato = $record['tipoContrato_id'];
            $dto->siglaTipoContrato  = $record['siglaTipoContrato'];

            $dtoArray[$index] = $dto;
            $index++;
        }
        mysql_free_result($recordSet);

        return $dtoArray;
    }

    // Obtem uma enumeração de subcontratos pertencentes aos contratos informados
    static function GetSubcontractsByOwner($mysqlConnection, $contractEnumeration) {
        $subContractDAO = new SubContractDAO($mysqlConnection);
        $subContractDAO->showErrors = 1;

        $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id IN (".$contractEnumeration.")");
        $subContractEnumeration = "";
        foreach($subContractArray as $subContract) {
            if (!empty($subContractEnumeration)) $subContractEnumeration = $subContractEnumeration.", ";
            $subContractEnumeration = $subContractEnumeration.$subContract->id;
        }
         // Coloca 0 (zero) como item da enumeração caso a lista esteja vazia, de maneira que ela possa ser usada
         // em uma subquery com IN.   Exemplos:   WHERE subContractId IN (1, 2, 3)    WHERE subContractId IN (0) 
        if (empty($subContractEnumeration)) $subContractEnumeration = "0";

        return $subContractEnumeration;
    }

    // Obtem os equipamentos(numeros de série) contidos no subcontrato
    static function GetSerialNumbers($mysqlConnection, $sqlserverConnection, $subContractId) {
        $serialNumbers = "";

        $contractItemDAO = new ContractItemDAO($mysqlConnection);
        $contractItemDAO->showErrors = 1;
        $itemArray = $contractItemDAO->RetrieveRecordArray("subContrato_id = ".$subContractId);
        foreach ($itemArray as $contractItem) {
            if (!empty($serialNumbers)) $serialNumbers = $serialNumbers.", ";
            $serialNumbers = $serialNumbers.EquipmentDAO::GetSerialNumber($sqlserverConnection, $contractItem->codigoCartaoEquipamento);
        }

        return $serialNumbers;
    }

}

?>
