<?php

class EquipmentDAO{

    private $sqlserverConnection;
    private $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function StoreRecord($dto){
        $dataInstalacao = "'".$dto->installationDate."'";
        if (empty($dto->installationDate)) $dataInstalacao = "null";

        $dataDevolucao = "'".$dto->removalDate."'";
        if (empty($dto->removalDate)) $dataDevolucao = "null";

        // Alterar apenas campos de usuário aqui ( começados com U_ ), é proibido alterar campos do SAP B1
        $query = "UPDATE OINS SET U_InstallationDate = ".$dataInstalacao.", U_InstallationDocNum = '".$dto->installationDocNum."', U_BwPageCounter = '".$dto->counterInitialVal."', U_RemovalDate = ".$dataDevolucao.", U_RemovalDocNum = '".$dto->removalDocNum."', U_BwPageCounter2 = '".$dto->counterFinalVal."', U_Technician = ".$dto->technician.", U_Model = ".$dto->model.", U_Capacity = '".$dto->capacity."', U_SLA = '".$dto->sla."', U_Comments = '".$dto->comments."', U_SalesPerson = ".$dto->salesPerson." WHERE InsId = ".$dto->insID;

        $result = sqlsrv_query($this->sqlserverConnection, $query);
        if ($result) {
            return $dto->insID;
        }

        if ((!$result) && ($this->showErrors)) {
            print_r(sqlsrv_errors());
            echo '<br/>';
        }
        return null;
    }

    function RetrieveRecord($equipmentCode){
        $dto = null;

        $query = "SELECT * FROM OINS WHERE InsId = '".$equipmentCode."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new EquipmentDTO();
        $dto->insID = $record["insID"];
        $dto->manufacturerSN = $record["manufSN"];
        $dto->internalSN = $record["internalSN"];
        $dto->itemCode = $record["itemCode"];
        $dto->itemName = $record["itemName"];
        $dto->customer = $record["customer"];
        $dto->custmrName = $record["custmrName"];
        $dto->contactPerson = $record["contactCod"];
        $dto->addressType = $record["AddrType"];
        $dto->street = $record["street"];
        $dto->streetNo = $record["StreetNo"];
        $dto->building = $record["Building"];
        $dto->zip = $record["zip"];
        $dto->block = $record["block"];
        $dto->city = $record["city"];
        $dto->state = $record["state"];
        $dto->country = $record["country"];
        $dto->instLocation = $record["instLction"];
        $dto->status = $record["status"];
        $dto->installationDate = $record["U_InstallationDate"];
        $dto->installationDocNum = $record["U_InstallationDocNum"];
        $dto->counterInitialVal = $record["U_BwPageCounter"];
        $dto->removalDate = $record["U_RemovalDate"];
        $dto->removalDocNum = $record["U_RemovalDocNum"];
        $dto->counterFinalVal = $record["U_BwPageCounter2"];
        $dto->technician = $record["U_Technician"];
        $dto->model = $record["U_Model"];
        $dto->capacity = $record["U_Capacity"];
        $dto->sla = $record["U_SLA"];
        $dto->comments = $record["U_Comments"];
        $dto->salesPerson = $record["U_SalesPerson"];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT * FROM OINS WHERE ".$filter;
        if (empty($filter)) $query = "SELECT * FROM OINS ORDER BY manufSN";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new EquipmentDTO();
            $dto->insID = $record["insID"];
            $dto->manufacturerSN = $record["manufSN"];
            $dto->internalSN = $record["internalSN"];
            $dto->itemCode = $record["itemCode"];
            $dto->itemName = $record["itemName"];
            $dto->customer = $record["customer"];
            $dto->custmrName = $record["custmrName"];
            $dto->contactPerson = $record["contactCod"];
            $dto->addressType = $record["AddrType"];
            $dto->street = $record["street"];
            $dto->streetNo = $record["StreetNo"];
            $dto->building = $record["Building"];
            $dto->zip = $record["zip"];
            $dto->block = $record["block"];
            $dto->city = $record["city"];
            $dto->state = $record["state"];
            $dto->country = $record["country"];
            $dto->instLocation = $record["instLction"];
            $dto->status = $record["status"];
            $dto->installationDate = $record["U_InstallationDate"];
            $dto->installationDocNum = $record["U_InstallationDocNum"];
            $dto->counterInitialVal = $record["U_BwPageCounter"];
            $dto->removalDate = $record["U_RemovalDate"];
            $dto->removalDocNum = $record["U_RemovalDocNum"];
            $dto->counterFinalVal = $record["U_BwPageCounter2"];
            $dto->technician = $record["U_Technician"];
            $dto->model = $record["U_Model"];
            $dto->capacity = $record["U_Capacity"];
            $dto->sla = $record["U_SLA"];
            $dto->comments = $record["U_Comments"];
            $dto->salesPerson = $record["U_SalesPerson"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    static function GetSerialNumber($sqlserverConnection, $equipmentCode) {
        $serialNumber = "";
        if (empty($equipmentCode)) return $serialNumber;

        $equipmentDAO = new EquipmentDAO($sqlserverConnection);
        $equipmentDAO->showErrors = 1;
        $equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
        if ($equipment != null)
            $serialNumber = $equipment->manufacturerSN." (".$equipment->internalSN.") ";

        return $serialNumber;
    }

    // Monta uma breve descrição a partir do DTO (data transfer object) do equipamento
    static function GetShortDescription($equipment) {
        $serialNumber = $equipment->manufacturerSN." (".$equipment->internalSN.") ";
        $status = EquipmentDAO::GetStatusDescription($equipment->status);
        return $serialNumber.$status;
    }

    static function GetDuplicates($sqlserverConnection) {
        $duplicates = array();

        $query = "SELECT manufSN, status, COUNT(1) quantidade FROM OINS GROUP BY manufSN, status HAVING COUNT(1) > 1 AND status = 'A'";
        $recordSet = sqlsrv_query($sqlserverConnection, $query);
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $duplicates[$record["manufSN"]] = $record["quantidade"];
        }
        sqlsrv_free_stmt($recordSet);

        return $duplicates;
    }

    // Devolve a descrição do status a partir da sigla
    static function GetStatusDescription($status) {
        switch ($status)
        {
            case "R": return "Devolvido";
            case "T": return "Encerrado";
            case "L": return "Emprestado";
            case "I": return "Em reparo";
            default: return "Ativo";
        }
    }

}

?>
