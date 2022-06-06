<?php

class EmployeeDAO{

    var $sqlserverConnection;
    var $showErrors;

    #construtor
    function __construct($sqlserverConnection){
        $this->sqlserverConnection = $sqlserverConnection;
        $this->showErrors = 0;
    }

    function RetrieveRecord($employeeID){
        $dto = null;

        $query = "SELECT empID, firstName, middleName, lastName FROM OHEM WHERE empID = '".$employeeID."'";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC);
        if (!$record) return null;

        $dto = new EmployeeDTO();
        $dto->empID = $record["empID"];
        $dto->firstName = $record["firstName"];
        $dto->middleName = $record["middleName"];
        $dto->lastName = $record["lastName"];
        sqlsrv_free_stmt($recordSet);

        return $dto;
    }

    function RetrieveRecordArray($filter = null){
        $dtoArray = array();

        $query = "SELECT empID, firstName, middleName, lastName FROM OHEM WHERE ".$filter;
        if (empty($filter)) $query = "SELECT empID, firstName, middleName, lastName FROM OHEM ORDER BY firstName";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new EmployeeDTO();
            $dto->empID = $record["empID"];
            $dto->firstName = $record["firstName"];
            $dto->middleName = $record["middleName"];
            $dto->lastName = $record["lastName"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

    // SELECT OHEM.empID id, (OHEM.firstName + ' ' + OHEM.lastName) nome FROM HEM6
    // JOIN OHEM ON HEM6.empID = OHEM.empID
    // JOIN OHTY ON HEM6.roleID = OHTY.typeID WHERE roleID = -2   -- Técnico

    function RetrieveEmployeesByPosition($employeePosition1 = null, $employeePosition2 = null, $employeePosition3 = null) {
        $dtoArray = array();

        $subQuery = "SELECT posId FROM OHPS";
        if (!empty($employeePosition1)) $subQuery = $subQuery." WHERE name LIKE '%".$employeePosition1."%'";
        if (!empty($employeePosition2)) $subQuery = $subQuery." OR name LIKE '%".$employeePosition2."%'";
        if (!empty($employeePosition3)) $subQuery = $subQuery." OR name LIKE '%".$employeePosition3."%'";

        $query = "SELECT empID, firstName, middleName, lastName FROM OHEM WHERE position IN (".$subQuery.") ORDER BY firstName DESC";
        $recordSet = sqlsrv_query($this->sqlserverConnection, $query);

        $index = 0;
        while( $record = sqlsrv_fetch_array($recordSet, SQLSRV_FETCH_ASSOC) ){
            $dto = new EmployeeDTO();
            $dto->empID = $record["empID"];
            $dto->firstName = $record["firstName"];
            $dto->middleName = $record["middleName"];
            $dto->lastName = $record["lastName"];

            $dtoArray[$index] = $dto;
            $index++;
        }
        sqlsrv_free_stmt($recordSet);

        return $dtoArray;
    }

}

?>
