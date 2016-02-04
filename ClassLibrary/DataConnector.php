<?php

class DataConnector{
    var $databaseType;
    var $mysqlConnection;
    var $sqlserverConnection;


    # constructor
    function DataConnector($databaseType){
        $this->databaseType = $databaseType;
    }

    function OpenConnection(){
        global $mysqlAuthentication;
        global $sqlserverAuthentication;
        $host = null;
        $options = null;

        if ( isset($mysqlAuthentication)){
            $host = $mysqlAuthentication["host"];
            $options = $mysqlAuthentication;
        }

        if (($this->databaseType == 'mySql') || ($this->databaseType == 'both')) {
            $this->mysqlConnection = @mysql_connect($host, $options["username"], $options["password"]);
        }

        if ( isset($this->mysqlConnection) ){
            @mysql_set_charset("utf8");
            @mysql_select_db( $options["database"], $this->mysqlConnection ) ;
        }

        if (isset($sqlserverAuthentication)){
            $host = $sqlserverAuthentication["host"];
            $options = array("Database"=>$sqlserverAuthentication["database"], "CharacterSet" =>"UTF-8", "UID"=>$sqlserverAuthentication["username"], "PWD"=>$sqlserverAuthentication["password"]);
        }

        if (($this->databaseType == 'sqlServer') || ($this->databaseType == 'both')) {
            $this->sqlserverConnection = sqlsrv_connect($host, $options);
        }
    }

    function CloseConnection(){
        if (($this->databaseType == 'mySql') || ($this->databaseType == 'both')) {
            mysql_close($this->mysqlConnection);
        }

        if (($this->databaseType == 'sqlServer') || ($this->databaseType == 'both')) {
            sqlsrv_close($this->sqlserverConnection);
        }
    }

}

?>
