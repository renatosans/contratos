<?php

    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/ReadingDAO.php");
    include_once("../../DataTransferObjects/ReadingDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");
    include_once("../../DataAccessObjects/EmployeeDAO.php");
    include_once("../../DataTransferObjects/EmployeeDTO.php");

    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $modelName = $_GET['modelName'];
    $equipmentCode = $_GET['equipmentCode'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $technician = $_GET['technician'];
    $status = $_GET['status'];
    $orderBy = $_GET['orderBy'];
    $searchMethod = $_GET['searchMethod'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
    $readingDAO->showErrors = 1;
    $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
    $counterDAO->showErrors = 1;
    $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
    $employeeDAO->showErrors = 1;

    // Busca os chamados que se enquadram no filtro aplicado
    if ($searchMethod == 0) {
        $filter = "businessPartnerCode='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter = "businessPartnerCode <> ''"; // qualquer cliente
    }
    if ($searchMethod == 1) {
        $filter = "modelo='".$modelName."'";
        if (empty($model)) $filter = "modelo <> ''"; // qualquer modelo
    }
    if ($searchMethod == 2) {
        $filter1 = "businessPartnerCode='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter1 = "businessPartnerCode <> ''"; // qualquer cliente
        $filter2 = "modelo='".$modelName."'";
        if (empty($model)) $filter2 = "modelo <> ''"; // qualquer modelo
        $filter = $filter1." AND ".$filter2;
    }
    if ($searchMethod == 3) {
        $filter1 = "businessPartnerCode='".$businessPartnerCode."'";
        if (empty($businessPartnerCode)) $filter1 = "businessPartnerCode <> ''"; // qualquer cliente
        $equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
        $manufSN = $equipment->manufacturerSN;
        $equipmentArray = $equipmentDAO->RetrieveRecordArray("manufSN='".$manufSN."'");
        $equipmentEnumeration = "";
        foreach($equipmentArray as $equipment)
        {
            if (!empty($equipmentEnumeration)) $equipmentEnumeration = $equipmentEnumeration.", ";
            $equipmentEnumeration = $equipmentEnumeration.$equipment->insID;
        }
        if (empty($equipmentEnumeration)) $equipmentEnumeration = "0"; // evita o crash da query, quando a lista está vazia
        $filter2 = "cartaoEquipamento IN (".$equipmentEnumeration.")";
        $filter = $filter1." AND ".$filter2;
    }
    $filter .= " AND dataAbertura >= '".$startDate." 00:00' AND dataAbertura <= '".$endDate." 23:59'";
    if ($technician != 0) $filter = $filter." AND tecnico=".$technician;
    if ($status != 0) $filter = $filter." AND status=".$status;
    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray($filter." ORDER BY ".$orderBy.", fabricante, modelo");


    // Busca os Status de chamado cadastrados no sistema
    $statusArray = ServiceCallDAO::RetrieveServiceCallStatuses($dataConnector->sqlserverConnection);

    // Busca os contadores cadastrados no sistema
    $retrievedArray = $counterDAO->RetrieveRecordArray();
    $counterArray = array();
    foreach ($retrievedArray as $counter) {
        $counterArray[$counter->id] = $counter->nome;
    }

    // Busca os funcionários cadastrados no sistema
    $retrievedArray = $employeeDAO->RetrieveRecordArray();
    $employeeArray = array();
    foreach ($retrievedArray as $employee) {
        $employeeArray[$employee->empID] = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
    }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >
    <link href="<?php echo $pathCss; ?>/jquery-ui.css"  rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery.min.js" ></script>
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery-ui.min.js" ></script>
    <style type="text/css">
        @page { margin:0.8cm; size: landscape; }
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 8px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Relatório de Chamados</title>
</head>
<body>
    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >RELATÓRIO DE CHAMADOS</h3><br/>
            <h3 style="border:0; margin:0;" >Data inicial: <?php echo $startDate; ?>&nbsp;&nbsp;&nbsp;Data final: <?php echo $endDate; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Data Abertura</td><td>Nº do chamado</td><td>Status</td><td>Cliente</td><td>Depto.</td><td>Modelo</td><td>Série</td><td>Defeito</td><td>Data Atendimento</td><td>Horário/Duração</td><td>Sintoma</td><td>Causa</td><td>Ação</td><td>Contadores</td><td>Técnico</td></tr>
            <?php
                foreach ($serviceCallArray as $serviceCall) {
                    $equipment = $equipmentDAO->RetrieveRecord($serviceCall->codigoCartaoEquipamento);
                    $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCall->id);
                    $counters = "";
                    foreach($readingArray as $reading) {
                        $counters = $counters.$counterArray[$reading->codigoContador].' '.$reading->contagem.'<br/>';
                    }
                    $serviceCallId = str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT);
                    $serviceCallStatus = $statusArray[$serviceCall->status];
                    $assistanceTime = $serviceCall->horaAtendimento." (Duração ".$serviceCall->tempoAtendimento.")";
                    $technicianName = " - "; if ($serviceCall->tecnico > 0) $technicianName = $employeeArray[$serviceCall->tecnico];
                    echo '<tr bgcolor="WHITE" ><td>'.$serviceCall->dataAbertura.'</td><td>'.$serviceCallId.'</td><td>'.$serviceCallStatus.'</td><td>'.$equipment->custmrName.'</td><td>'.$equipment->instLocation.'</td><td>'.$serviceCall->modelo.'</td><td>'.$equipment->manufacturerSN.'</td><td>'.$serviceCall->defeito.'</td><td>'.$serviceCall->dataAtendimento.'</td><td>'.$assistanceTime.'</td><td>'.$serviceCall->sintoma.'</td><td>'.$serviceCall->causa.'</td><td>'.$serviceCall->acao.'</td><td>'.$counters.'</td><td>'.$technicianName.'</td></tr>';
                }
            ?>
            </table>
            <div style="clear:both;"><br/></div>
        </div>
        <div style="clear:both;"><br/></div>

        <div id="pageBottom" style="height:12px;"></div>
    </div>
<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
</body>
</html>
