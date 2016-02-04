<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/ExpenseDAO.php");
    include_once("../../DataTransferObjects/ExpenseDTO.php");
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
    <title>Histórico de troca de peças</title>
</head>
<body>
<?php

    $equipmentList = $_GET['equipmentList'];
    $sendToPrinter = null; if (isset($_GET['sendToPrinter'])) $sendToPrinter = $_GET['sendToPrinter'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
    $expenseDAO->showErrors = 1;

?>

    <script type='text/javascript'>
        $(document).ready(function() {
            <?php if (isset($sendToPrinter)) echo 'window.print();'; ?>
        });
    </script>

    <div style="width:99%;height:99%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" id="pageBorder" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <img src="http://www.datacount.com.br/Datacount/images/logo.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="height:50px; margin-top:10px; margin-left: 50px; float:left;">
            <h3 style="border:0; margin:0;" >HISTÓRICO DE TROCA DE PEÇAS</h3><br/>
            <h3 style="border:0; margin:0;" >Período: A partir da aquisição até a presente data</h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <table>
                <?php
                // Busca os dados dos equipamentos
                $equipmentArray = $equipmentDAO->RetrieveRecordArray("InsId IN (".$equipmentList.")");
                foreach($equipmentArray as $equipment)
                {
                    $equipmentModel = $equipment->itemName;
                    $serialNumber = EquipmentDAO::GetShortDescription($equipment);
                    $salesPersonName = SalesPersonDAO::GetSalesPersonName($dataConnector->sqlserverConnection, $equipment->salesPerson);
                    $spacing = '&nbsp;&nbsp;&nbsp;';
                    echo '<tr bgcolor=LIGHTGRAY ><td colspan=5 >Cartão Equipamento: '.$equipment->insID.$spacing.'Modelo: '.$equipmentModel.$spacing.'Série: '.$serialNumber.$spacing.'Departamento: '.$equipment->instLocation.$spacing.'Vendedor: '.$salesPersonName.'</td></tr>';
                    echo '<tr bgcolor=WHITE ><td>Chamado</td><td>Data da Troca</td><td>Código Item</td><td>Nome do Item</td><td>Quantidade</td></tr>';

                    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray("cartaoEquipamento = ".$equipment->insID);
                    foreach ($serviceCallArray as $serviceCall) {
                        $chamado = str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT);
                        $dataTroca = $serviceCall->dataAtendimento;

                        $expenseArray = $expenseDAO->RetrieveRecordArray("codigoChamado = ".$serviceCall->id." AND codigoInsumo IS NULL");
                        foreach ($expenseArray as $expense) {
                            echo '<tr bgcolor=WHITE ><td>'.$chamado.'</td><td>'.$dataTroca.'</td><td>'.$expense->codigoItem.'</td><td>'.$expense->nomeItem.'</td><td>'.$expense->quantidade.'</td></tr>';
                        }
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
