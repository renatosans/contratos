<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
    include_once("../../defines.php");
    include_once("../../ClassLibrary/UnixTime.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/EmployeeDAO.php");
    include_once("../../DataTransferObjects/EmployeeDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");
?>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery.min.js" ></script>
    <style type="text/css">
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 11px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Tempo em atendimento</title>
</head>
<body>
<?php

    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $equipmentList = $_GET['equipmentList'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }


    function GetTechnicianName($sqlserverConnection, $technicianId) {
        $technicianName = "";
        $employeeDAO = new EmployeeDAO($sqlserverConnection);
        $employeeDAO->showErrors = 1;
        $employee = $employeeDAO->RetrieveRecord($technicianId);
        if ($employee != null) {
            $technicianName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
        }
        return $technicianName;
    }


?>
    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <img src="<?php echo $pathImg; ?>/logo_datacopy.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >TEMPO EM ATENDIMENTO</h3><br/>
            <h3 style="border:0; margin:0;" >Data inicial: <?php echo $startDate; ?>&nbsp;&nbsp;&nbsp;Data final: <?php echo $endDate; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <table>
            <?php
            // Busca os dados dos equipamentos
            $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
            $equipmentDAO->showErrors = 1;
            $equipmentArray = $equipmentDAO->RetrieveRecordArray("InsId IN (".$equipmentList.")");
            foreach($equipmentArray as $equipment)
            {
                $equipmentModel = $equipment->itemName;
                $serialNumber = EquipmentDAO::GetShortDescription($equipment);
                $salesPersonName = SalesPersonDAO::GetSalesPersonName($dataConnector->sqlserverConnection, $equipment->salesPerson);
                $spacing = '&nbsp;&nbsp;&nbsp;';
                echo '<tr bgcolor=LIGHTGRAY ><td colspan=5 >Cartão Equipamento: '.$equipment->insID.$spacing.'Modelo: '.$equipmentModel.$spacing.'Série: '.$serialNumber.$spacing.'Departamento: '.$equipment->instLocation.$spacing.'Vendedor: '.$salesPersonName.'</td></tr>';

                $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
                $serviceCallDAO->showErrors = 1;
                $query = "cartaoEquipamento = ".$equipment->insID." AND dataAbertura >= '".$startDate." 00:00' AND dataAbertura <= '".$endDate." 23:59' ";
                $serviceCallArray = $serviceCallDAO->RetrieveRecordArray($query);
                $tempoTotalAtendimento = 0;
                if (sizeof($serviceCallArray) > 0)
                    echo '<tr bgcolor=WHITE ><td>Chamado</td><td>Defeito</td><td>Data Abertura</td><td>Técnico</td><td>Tempo Atendimento</td></tr>';
                else
                    echo '<tr bgcolor=WHITE ><td colspan=5 >Nenhum chamado encontrado</td></tr>';
                foreach ($serviceCallArray as $serviceCall) {
                    $dataAbertura = strtotime($serviceCall->dataAbertura); $dataAbertura = date("d/m/Y", $dataAbertura);
                    $tecnico = GetTechnicianName($dataConnector->sqlserverConnection, $serviceCall->tecnico);
                    $tempoAtendimento = $serviceCall->tempoAtendimento;
                    $parts = explode(":", $tempoAtendimento, 2);
                    $tempoTotalAtendimento += ((int)$parts[0]) +  ((int)$parts[1] / 60);

                    echo '<tr bgcolor=WHITE ><td>'.$serviceCall->id.'</td><td>'.$serviceCall->defeito.'</td><td>'.$dataAbertura.'</td><td>'.$tecnico.'</td><td>'.$tempoAtendimento.'</td></tr>';
                }
                if (sizeof($serviceCallArray) > 0) {
                    echo '<tr bgcolor=WHITE ><td colspan=5 >Tempo total de atendimento: '.UnixTime::ConvertToTime($tempoTotalAtendimento).'</td></tr>';
                }
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
