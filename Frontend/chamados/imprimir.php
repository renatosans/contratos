<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
    include_once("../../defines.php");
    include_once("../../ClassLibrary/UnixTime.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../ClassLibrary/EquipmentOperator.php");
    include_once("../../DataAccessObjects/ServiceCallDAO.php");
    include_once("../../DataTransferObjects/ServiceCallDTO.php");
    include_once("../../DataAccessObjects/PartReplacementDAO.php");
    include_once("../../DataTransferObjects/PartReplacementDTO.php");
    include_once("../../DataAccessObjects/EquipmentDAO.php");
    include_once("../../DataTransferObjects/EquipmentDTO.php");
    include_once("../../DataAccessObjects/ReadingDAO.php");
    include_once("../../DataTransferObjects/ReadingDTO.php");
    include_once("../../DataAccessObjects/CounterDAO.php");
    include_once("../../DataTransferObjects/CounterDTO.php");
    include_once("../../DataAccessObjects/EmployeeDAO.php");
    include_once("../../DataTransferObjects/EmployeeDTO.php");
    include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../../DataAccessObjects/InventoryItemDAO.php");
    include_once("../../DataTransferObjects/InventoryItemDTO.php");
    include_once("../../DataAccessObjects/ContactPersonDAO.php");
    include_once("../../DataTransferObjects/ContactPersonDTO.php");
?>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery.min.js" ></script>
    <style type="text/css">
        @page { margin:0.8cm; }
        table{  border-left:1px solid black; border-top:1px solid black; width:90%; margin-left:auto; margin-right:auto; border-spacing:0;  }
        td{  border-right:1px solid black; border-bottom:1px solid black; margin:0; padding:0; text-align:center;  }
    </style>
    <title>Imprimir Chamado</title>
</head>
<body style="font-size: 14px;">
<?php

    $serviceCallId = $_GET['serviceCallId'];
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


    // Busca os dados do chamado
    $serviceCall = $serviceCallDAO->RetrieveRecord($serviceCallId);

    // Recupera dados do cartão de equipamento
    $equipment = $equipmentDAO->RetrieveRecord($serviceCall->codigoCartaoEquipamento);


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


    <div style="width:100%;height:100%; margin:0; padding:0; border:1px solid black;" id="page1Border" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <div style="clear:both;"><br/></div>
            <img src="<?php echo $pathImg; ?>/logo_datacopy.png" alt="Datacopy Trade" style="width:150px; height:50px; margin-top:10px; margin-left: 10px; margin-right: 10px; float:left;" />
            <div style="width:450px; height:50px; margin-top:10px; margin-left: 10px; float:left;">
            <h3 style="border:0; margin:0; max-width:95%;" >CHAMADO DE SERVIÇO</h3>
            <h3 style="border:0; margin:0; max-width:95%;" >Número: <?php echo str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT); ?>&nbsp;&nbsp;&nbsp;Defeito: <?php echo $serviceCall->defeito; ?></h3>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <span style="margin-left: 10px">
                Data: <?php echo $serviceCall->dataAbertura; ?>
            </span>
            <span style="margin-left: 30px">
                Hora: <?php echo $serviceCall->horaAbertura; ?>
            </span>
            <span style="margin-left: 30px">
                Aberto Por:
                <?php
                    $creator = "";
                    $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
                    $employeeDAO->showErrors = 1;
                    $employee = $employeeDAO->RetrieveRecord($serviceCall->abertoPor);
                    if ($employee != null) {
                        $creator = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
                    }
                    echo $creator;
                ?>
            </span>

            <br/>

            <span style="margin-left: 10px">
                Tipo do Chamado:
                <?php
                    $serviceCallType = "";
                    $callTypes = $serviceCallDAO->RetrieveServiceCallTypes($dataConnector->sqlserverConnection);
                    foreach ($callTypes as $key=>$value) {
                        if ($key == $serviceCall->tipo) {
                            $serviceCallType = $value;
                        }
                    }
                    echo $serviceCallType;
                ?>
            </span>
            <span style="margin-left: 30px">
                Nível de Serviço:
                <?php
                    $sla = " --- ";
                    if (!empty($equipment->sla)) $sla = $equipment->sla.' horas';
                    echo $sla;
                ?>
            </span>

            <hr/>

            <?php
                $equipmentOperator = new EquipmentOperator($dataConnector->sqlserverConnection, $serviceCall->businessPartnerCode, $serviceCall->codigoCartaoEquipamento);
            ?>
            <span style="margin-left: 10px">
                Cliente: <?php echo $equipmentOperator->businessPartnerName; ?>
            </span>
            <span style="margin-left: 30px">
                Telefone: <?php echo $equipmentOperator->telephoneNumber; ?>
            </span>
            <span style="margin-left: 30px">
                Contato: <?php echo $serviceCall->contato; ?>
            </span>

            <br/>

            <span style="margin-left: 10px">
                Endereço: <?php echo $equipment->addressType." ".$equipment->street." ".$equipment->streetNo." ".$equipment->building; ?>
            </span>
            <span style="margin-left: 20px">
                Bairro: <?php echo $equipment->block; ?>
            </span>
            <span style="margin-left: 20px">
                Cidade: <?php echo $equipment->city; ?>
            </span>
            <span style="margin-left: 20px">
                Estado: <?php echo $equipment->state; ?>
            </span>
            <span style="margin-left: 20px">
                Local: <?php echo $equipment->instLocation; ?>
            </span>

            <hr/>

            <span style="margin-left: 10px">
                Modelo: <?php echo $equipment->itemName; ?>
            </span>
            <br/>
            <span style="margin-left: 10px">
                Número de série: <?php echo EquipmentDAO::GetShortDescription($equipment); ?>
            </span>
            <div style="clear:both;"><br/></div>

            <?php
                $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCall->id);
                if (sizeof($readingArray) < 1) { // caso não exista nenhum contador/leitura para o chamado
                    // adiciona tags para o preenchimento a caneta
                    $counterArray = $counterDAO->RetrieveRecordArray("id > 0 LIMIT 0, 3"); // exibe apenas os 3 primeiros
                    foreach($counterArray as $counter) {
                        echo '<p style="margin:0; float:left;">&nbsp;&nbsp;'.$counter->nome.':</p>';
                        echo '<div style="width:15%;height:20px;float:left;" >';
                        echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                        echo '</div>';
                    }
                }

                foreach($readingArray as $reading) {
                    $counter = $counterDAO->RetrieveRecord($reading->codigoContador);
                    echo '<p style="margin:0; float:left;">&nbsp;&nbsp;'.$counter->nome.':</p>';
                    echo '<div style="width:15%;height:20px;float:left;" >';
                    $contagem = $reading->contagem;
                    if ($contagem == 0) {
                        echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                    }
                    else {
                        echo "&nbsp;&nbsp;".$contagem;
                    }
                    echo '</div>';
                }
            ?>
            <div style="clear:both;"><br/></div>

            <p style='margin:0; float:left;'>&nbsp;&nbsp;Sintoma:</p>
            <div style='width:80%;height:20px;float:left;'>
            <?php
                $symptom = $serviceCall->sintoma;
                if (empty($symptom)) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$symptom;
                }
            ?>
            </div>
            <div style="clear:both;"><br/></div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Causa:</p>
            <div style='width:80%;height:20px;float:left;'>
            <?php
                $cause = $serviceCall->causa;
                if (empty($cause)) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$cause;
                }
            ?>
            </div>
            <div style="clear:both;"><br/></div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Ação:</p>
            <div style='width:80%;height:20px;float:left;'>
            <?php
                $resolution = $serviceCall->acao;
                if (empty($resolution)) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$resolution;
                }
            ?>
            </div>
            <div style="clear:both;"><br/><br/></div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Técnico:</p>
            <div style='width:25%;height:20px;float:left;'>
            <?php
                if ($serviceCall->tecnico == 0) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    $technicianName = GetTechnicianName($dataConnector->sqlserverConnection, $serviceCall->tecnico);
                    echo "&nbsp;&nbsp;".$technicianName;
                }
            ?>
            </div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;&nbsp;Data:</p>
            <div style='width:12%;height:20px;float:left;'>
            <?php
                $assistanceDate = $serviceCall->dataAtendimento;
                if ($assistanceDate == null) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$assistanceDate;
                }
            ?>
            </div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;&nbsp;Horário Entrada:</p>
            <div style='width:8%;height:20px;float:left;'>
            <?php
                $assistanceTime = $serviceCall->horaAtendimento;
                if ($assistanceTime == null) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$assistanceTime;
                }
            ?>
            </div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;&nbsp;Horário Saída:</p>
            <div style='width:8%;height:20px;float:left;'>
            <?php
                $assistanceDuration = $serviceCall->tempoAtendimento;
                if (isset($assistanceDuration)) $split1 = explode(":", $assistanceDuration, 2); else $split1 = array(0, 0);
                $entryTime = $serviceCall->horaAtendimento;
                if (isset($entryTime)) $split2 = explode(":", $entryTime, 2); else $split2 = array(0, 0);
                $exitTime = ((int)$split1[0]) + ((int)$split1[1] / 60) + ((int)$split2[0]) + ((int)$split2[1] / 60);
                $exitTime = UnixTime::ConvertToTime($exitTime);
                if ($assistanceDuration == "00:00") {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$exitTime;
                }
            ?>
            </div>
            <div style="clear:both;"><br/></div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Observações:</p>
            <div style='width:80%;height:20px;float:left;'>
            <?php
                $comments = $serviceCall->observacaoTecnica;
                if (empty($comments)) {
                    echo "&nbsp;&nbsp;<hr style='margin:0;' />";
                }
                else {
                    echo "&nbsp;&nbsp;".$comments;
                }
            ?>
            </div>
            <div style="clear:both;"><br/><br/></div>

            <hr/>
            <h3 style="border:0; margin:0;" >&nbsp;&nbsp;&nbsp;REQUISIÇÃO DE PEÇAS</h3>
            <table id="requisicaoPecas" style="width:96%;" >
                <tr style="height:25px;" ><td>Código</td><td>Descrição do Item</td><td style="width:15%;" >Quantidade</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            </table>
            <br/>
        </div>

        <div style="width:96%; margin-top:1%; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <div style="clear:both;"><br/><br/><br/></div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Assinatura Cliente:</p>
            <div style='width:30%;height:20px;float:left;'>
                &nbsp;&nbsp;<hr style='margin:0;' />
            </div>
            <p style='margin:0; float:left;'>&nbsp;&nbsp;Assinatura Datacopy:</p>
            <div style='width:30%;height:20px;float:left;'>
                &nbsp;&nbsp;<hr style='margin:0;' />
            </div>
            <div style="clear:both;"><br/></div>
        </div>
        <div id="page1Bottom" style="height:12px;"></div>
    </div>
    <div style='width: 100%; text-align: center; page-break-after: always;'><span style='font-weight: bold; margin:0px auto;' ><!-- Page Number --></span></div>


    <div style="width:100%;height:100%; margin:0; padding:0; border:1px solid black;" id="page2Border" >
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <h3 style="border:0; margin:0;" >&nbsp;&nbsp;CHAMADOS ANTERIORES</h3>
            <?php
                $fillerCount = 0;
                $recordCount = 0;
                // Traz os 5 últimos chamados para o cartão de equipamento em questão partindo de dataDeCorte (chamados anteriores a dataDeCorte)
                $cutoffDate = $serviceCall->dataAbertura." ".$serviceCall->horaAbertura;
                $filter = "cartaoEquipamento = ".$serviceCall->codigoCartaoEquipamento." AND status <> 1 AND dataAbertura < '".$cutoffDate."' ORDER BY dataAbertura DESC, id DESC LIMIT 0, 5";
                $serviceCallArray = $serviceCallDAO->RetrieveRecordArray($filter);
                if (sizeof($serviceCallArray) > 0)
                {
                    foreach($serviceCallArray as $call){
                        echo "<hr/>";
                        echo "&nbsp;&nbsp;";
                        echo "Data: ".$call->dataAbertura."&nbsp;&nbsp;";
                        echo "Número: ".str_pad($call->id, 5, '0', STR_PAD_LEFT)."&nbsp;&nbsp;";
                        echo "Defeito: ".$call->defeito."&nbsp;&nbsp;";
                        $contatores = "";
                        $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$call->id);
                        foreach($readingArray as $reading) {
                            $counter = $counterDAO->RetrieveRecord($reading->codigoContador);
                            $contatores = $contatores."&nbsp;&nbsp;".$reading->contagem."(".$counter->nome.")";
                        }
                        echo "Contadores: ".$contatores."&nbsp;&nbsp;";
                        echo "Técnico: ".GetTechnicianName($dataConnector->sqlserverConnection, $call->tecnico)."<br/>";
                        echo "&nbsp;&nbsp;";
                        echo "Sintoma: ".$call->sintoma."<br/>";
                        echo "&nbsp;&nbsp;";
                        echo "Causa: ".$call->causa."<br/>";
                        echo "&nbsp;&nbsp;";
                        echo "Ação: ".$call->acao."<br/>";
                        echo "&nbsp;&nbsp;";
                        echo "Observações: ".$call->observacaoTecnica."<br/>";
                        $recordCount++;
                    }
                }
                $fillerCount = 5 - $recordCount;
                for ($filler = 1; $filler <= $fillerCount; $filler++) {
                    echo "<hr/>";
                    echo "&nbsp;&nbsp;"."Data: &nbsp;&nbsp; Número: &nbsp;&nbsp; Defeito: &nbsp;&nbsp; Contadores: &nbsp;&nbsp; Técnico:"."<br/>";
                    echo "&nbsp;&nbsp;"."Sintoma: "."<br/>";
                    echo "&nbsp;&nbsp;"."Causa: "."<br/>";
                    echo "&nbsp;&nbsp;"."Ação: "."<br/>";
                    echo "&nbsp;&nbsp;"."Observações: "."<br/>";
                }
            ?>
            <div style="clear:both;"><br/></div>
        </div>
        <div style="width:96%; margin-top:12px; margin-left:auto; margin-right:auto; border:1px solid black;" >
            <h3 style="border:0; margin:0;" >&nbsp;&nbsp;DURABILIDADE DE PEÇAS</h3>
            <table id="durabilidadePecas" style="width:96%;" >
                <tr style="height:25px;" ><td style="width:15%;" >Data da Troca</td><td>Peça</td><td style="width:15%;" >Durabilidade</td><td style="width:15%;" >Aferição na Troca</td><td style="width:15%;" >Páginas Extraídas</td></tr>
                <?php
                    $cutoffDate = $serviceCall->dataAbertura." ".$serviceCall->horaAbertura;

                    // Traz todos os chamados anteriores a este e que pertencem ao mesmo equipamento
                    $filter = "cartaoEquipamento = ".$serviceCall->codigoCartaoEquipamento." AND dataAbertura < '".$cutoffDate."' ORDER BY dataAbertura DESC";
                    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray($filter);

                    // Traz o chamado imediatamente anterior a este
                    $filter = "cartaoEquipamento = ".$serviceCall->codigoCartaoEquipamento." AND dataAbertura < '".$cutoffDate."' ORDER BY dataAbertura DESC LIMIT 0, 1";
                    $previousCallArray = $serviceCallDAO->RetrieveRecordArray($filter);
                    $previousCall = new ServiceCallDTO(); // Stub usado para evitar crashes ( objeto zerado )
                    if (sizeof($previousCallArray) == 1)
                        $previousCall = $previousCallArray[0];

                    $callEnumeration = "";
                    foreach($serviceCallArray as $call){
                        if (!empty($callEnumeration)) $callEnumeration = $callEnumeration.", ";
                        $callEnumeration = $callEnumeration.$call->id;
                    }
                    if (empty($callEnumeration)) $callEnumeration = "0";

                    $partReplacementDAO = new PartReplacementDAO($dataConnector->mysqlConnection);
                    $partReplacementDAO->showErrors = 1;
                    $inventoryItemDAO = new InventoryItemDAO($dataConnector->sqlserverConnection);
                    $inventoryItemDAO->showErrors = 1;
                    $partReplacementArray = $partReplacementDAO->RetrieveRecordArray("codigoChamado IN (".$callEnumeration.") AND codigoInsumo IS NULL ORDER BY dataAbertura DESC LIMIT 0, 6");
                    foreach ($partReplacementArray as $partReplacement) {
                        $inventoryItem = $inventoryItemDAO->RetrieveRecord($partReplacement->codigoItem);
                        $xml = simplexml_load_string($inventoryItem->serializedData);
                        $counterTotal = 0;
                        $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$previousCall->id);
                        foreach($readingArray as $reading) {
                            foreach ($xml as $element) {
                                if ($reading->codigoContador == $element["id"]) $counterTotal += $reading->contagem;
                            }
                        }
                        $counterTotalBefore = 0;
                        $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$partReplacement->codigoChamado);
                        foreach($readingArray as $reading) {
                            foreach ($xml as $element) {
                                if ($reading->codigoContador == $element["id"]) $counterTotalBefore += $reading->contagem;
                            }
                        }
                        $consumption = "";
                        if ($counterTotal >= $counterTotalBefore) $consumption = $counterTotal - $counterTotalBefore;
                        echo '<tr style="height:25px;" ><td>'.$partReplacement->dataChamado.'</td><td>'.$partReplacement->nomeItem.'</td><td>'.$inventoryItem->durability.'</td><td>'.$counterTotalBefore.'</td><td>'.$consumption.'</td></tr>';
                    }
                ?>
                <tr style="height:25px;" ><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            </table>
            <div style="clear:both;"><br/></div>
        </div>

        <div id="page2Bottom" style="height:12px;"></div>
    </div>

<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
</body>
</html>
