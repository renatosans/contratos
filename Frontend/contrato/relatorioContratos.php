<?php

    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/ContractDAO.php");
    include_once("../../DataTransferObjects/ContractDTO.php");
    include_once("../../DataAccessObjects/SubContractDAO.php");
    include_once("../../DataTransferObjects/SubContractDTO.php");
    include_once("../../DataAccessObjects/ContractItemDAO.php");
    include_once("../../DataTransferObjects/ContractItemDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/EquipmentModelDAO.php");
    include_once("../../DataTransferObjects/EquipmentModelDTO.php");
    include_once("../../DataAccessObjects/SalesPersonDAO.php");
    include_once("../../DataTransferObjects/SalesPersonDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $model = $_GET['model'];
    $equipmentCode = $_GET['equipmentCode'];
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
    $contractType = $_GET['contractType'];
    $contractStatus = $_GET['contractStatus'];
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
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
    $subContractDAO->showErrors = 1;
    $contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
    $contractItemDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;
    $equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
    $equipmentModelDAO->showErrors = 1;
    $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
    $salesPersonDAO->showErrors = 1;


    // Busca os contratos que se enquadram no filtro aplicado
    $contractArray = array();
    if (($searchMethod == 0) || ($searchMethod == 2)) {
        $filter = "contrato.pn='".$businessPartnerCode."' AND contrato.encerramento >= '".$startDate." 00:00' AND contrato.encerramento <= '".$endDate." 23:59'";
        if ($contractType > 0) $filter = $filter." AND subcontrato.tipoContrato_id=".$contractType;
        if (!empty($contractStatus)) $filter = $filter." AND contrato.status IN (".$contractStatus.")";
        $joins = "JOIN subContrato ON contrato.id = subContrato.contrato_id JOIN itens ON contrato.id = itens.contrato_id";
        $contractArray = $contractDAO->RetrieveRecordArray2($filter, $joins);
    }
    if ($searchMethod == 1) {
        $equipmentArray = $equipmentDAO->RetrieveRecordArray("ItemName LIKE '%".$model."%'");
        $equipmentEnumeration = "";
        foreach($equipmentArray as $equipment)
        {
            if (!empty($equipmentEnumeration)) $equipmentEnumeration = $equipmentEnumeration.", ";
            $equipmentEnumeration = $equipmentEnumeration.$equipment->insID;
        }
        if (empty($equipmentEnumeration)) $equipmentEnumeration = "0"; // evita o crash da query, quando a lista está vazia

        $filter = "itens.codigoCartaoEquipamento IN (".$equipmentEnumeration.") AND contrato.encerramento >= '".$startDate." 00:00' AND contrato.encerramento <= '".$endDate." 23:59'";
        if ($contractType > 0) $filter = $filter." AND subcontrato.tipoContrato_id=".$contractType;
        if (!empty($contractStatus)) $filter = $filter." AND contrato.status IN (".$contractStatus.")";
        $joins = "JOIN subContrato ON contrato.id = subContrato.contrato_id JOIN itens ON contrato.id = itens.contrato_id";
        $contractArray = $contractDAO->RetrieveRecordArray2($filter, $joins);
    }
    if ($searchMethod == 3) {
        $filter = "itens.codigoCartaoEquipamento=".$equipmentCode." AND contrato.encerramento >= '".$startDate." 00:00' AND contrato.encerramento <= '".$endDate." 23:59'";
        if ($contractType > 0) $filter = $filter." AND subcontrato.tipoContrato_id=".$contractType;
        if (!empty($contractStatus)) $filter = $filter." AND contrato.status IN (".$contractStatus.")";
        $joins = "JOIN subContrato ON contrato.id = subContrato.contrato_id JOIN itens ON contrato.id = itens.contrato_id";
        $contractArray = $contractDAO->RetrieveRecordArray2($filter, $joins);
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
        table{  border-left:1px solid black; border-top:1px solid black; width:98%; margin-left:auto; margin-right:auto; border-spacing:0; font-size: 11px; }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
        th{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Relatório de Contratos</title>
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
            <h3 style="border:0; margin:0;" >RELATÓRIO DE CONTRATOS</h3><br/>
            <h3 style="border:0; margin:0;" >Encerramento de: <?php echo $startDate; ?>&nbsp;&nbsp;&nbsp;Encerramento até: <?php echo $endDate; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <hr/>
            <div style="clear:both;"><br/></div>
            <table>
            <tr bgcolor="YELLOW" style="height:30px;" ><td>Número</td><td>Detalhes</td><td>Assinatura</td><td>Encerramento</td><td>Inicio do Atendimento</td><td>Fim do Atendimento</td><td>Parcela</td><td>Cliente</td><td>Status</td><td>Global(S OU N)</td></tr>
            <?php
                // Busca os modelos de equipamento cadastrados no sistema
                $modelArray = array(0=>"");
                $equipmentModelArray = $equipmentModelDAO->RetrieveRecordArray();
                foreach($equipmentModelArray as $modelDTO) {
                    $modelArray[$modelDTO->id] = $modelDTO->modelo;
                }

                // Busca os vendedores cadastrados no sistema
                $retrievedArray = $salesPersonDAO->RetrieveRecordArray();
                $salesPersonArray = array();
                foreach ($retrievedArray as $salesPersonDTO) {
                    $salesPersonArray[$salesPersonDTO->slpCode] = $salesPersonDTO->slpName;
                }

                // Gera as linhas da tabela
                $identifierArray = array();
                foreach ($contractArray as $contract) {
                    if (array_key_exists($contract->id, $identifierArray)) continue; // contrato repetido, pula para o próximo registro

                    $numero = str_pad($contract->numero, 5, '0', STR_PAD_LEFT);
                    $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $contract->pn);
                    $salesPersonName = $salesPersonArray[$contract->vendedor];
                    $status = $contractDAO->GetStatusAsText($contract->status);
                    $parcela = $contract->parcelaAtual.'/'.$contract->quantidadeParcelas;
                    $global = ($contract->global == 0)? 'N' : 'S';
                    $details = $clientName.' Vendedor '.$salesPersonName;
                    $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id=".$contract->id);
                    foreach ($subContractArray as $subContract) {
                        if (!empty($details)) $details = $details.'<br/>';
                        $details = $details.$subContract->siglaTipoContrato;

                        $itemArray = $contractItemDAO->RetrieveRecordArray("subContrato_id=".$subContract->id);
                        foreach ($itemArray as $contractItem) {
                            $equipment = $equipmentDAO->RetrieveRecord($contractItem->codigoCartaoEquipamento);
                            $installationDate = empty($equipment->installationDate) ? '' : $equipment->installationDate->format('d/m/Y');

                            // filtra apenas os items ativos e emprestados
                            if (($equipment->status == 'A') || ($equipment->status == 'L')) {
                                if (!empty($details)) $details = $details.'<br/>';
                                $equipmentModel = ""; if (array_key_exists($equipment->model, $modelArray)) $equipmentModel = $modelArray[$equipment->model];
                                $details = $details.$equipmentModel.' Série '.$equipment->manufacturerSN.' Data Instalação '.$installationDate;
                            }
                        }
                    }
                    if (($searchMethod == 1) || ($searchMethod == 2)) {
                        if (!empty($model)) {
                            $modelMatched = false;
                            if (strpos($details, $model)) $modelMatched = true;
                            if (!$modelMatched) continue;
                        }
                    }

                    echo '<tr bgcolor="WHITE" ><td>'.$numero.'</td><td>'.$details.'</td><td>'.$contract->dataAssinatura.'</td><td>'.$contract->dataEncerramento.'</td><td>'.$contract->inicioAtendimento.'</td><td>'.$contract->fimAtendimento.'</td><td>'.$parcela.'</td><td>'.$contract->pn.'</td><td>'.$status.'</td><td>'.$global.'</td></tr>';
                    $identifierArray[$contract->id] = $contract->numero;
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
