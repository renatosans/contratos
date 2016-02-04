<?php

header("Cache-Control: no-cache");

session_start();

include_once("check.php");

include_once("defines.php");
include_once("ClassLibrary/Text.php");
include_once("ClassLibrary/DataConnector.php");
include_once("DataAccessObjects/LoginDAO.php");
include_once("DataTransferObjects/LoginDTO.php");
include_once("DataAccessObjects/ActionLogDAO.php");
include_once("DataTransferObjects/ActionLogDTO.php");
include_once("DataAccessObjects/SupplyRequestDAO.php");
include_once("DataTransferObjects/SupplyRequestDTO.php");
include_once("DataAccessObjects/RequestItemDAO.php");
include_once("DataTransferObjects/RequestItemDTO.php");
include_once("DataAccessObjects/CounterDAO.php");
include_once("DataTransferObjects/CounterDTO.php");
include_once("DataAccessObjects/EquipmentDAO.php");
include_once("DataTransferObjects/EquipmentDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

?>
<script>
    function CheckContractRenewal() {
        var targetUrl = 'AjaxCalls/CheckContractRenewal.php';
        $("#contractRenewal").load(targetUrl);
    }

    function CheckPricingAdjustments() {
        var targetUrl = 'AjaxCalls/CheckPricingAdjustments.php';
        $("#pricingAdjustments").load(targetUrl);
    }

    function CheckDuplicates() {
        var targetUrl = 'AjaxCalls/CheckDuplicates.php';
        $("#duplicates").load(targetUrl);
    }

    function CheckPendingReadings() {
        var dayOffset = $("#dayOffset").val();
        var counterId = $("#counterId").val();
        var targetUrl = 'AjaxCalls/CheckPendingReadings.php?dayOffset=' + dayOffset + '&counterId=' + counterId;
        $("#pendingReadings").load(targetUrl);
    }

    function GetEquipmentInfo(equipmentLink) {
        var equipmentCode = equipmentLink.attr("rel");
        if (!equipmentCode) equipmentCode = 0;
    
        var targetUrl = "AjaxCalls/GetEquipmentInfo.php?equipmentCode=" + equipmentCode;
        $("#pendingSupplies").append("<div id='popup'></div>");
        $("#popup").load(targetUrl).dialog({modal:true, width: 560, height: 340, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
    }

    $(document).ready(function() {

        $( ".column" ).sortable({
                connectWith: ".column",
                items: ".portlet:not(.fixedPortlet)"
        });

        $( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
                .find( ".portlet-header" )
                        .addClass( "ui-widget-header ui-corner-all" )
                        .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
                        .end()
                .find( ".portlet-content" );

        $( ".portlet-header .ui-icon" ).click(function() {
                $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
                $( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
        });

        $( ".column" ).disableSelection();

        CheckContractRenewal();
        CheckPricingAdjustments();
        CheckDuplicates();

        $("#dayOffset").change(function() { CheckPendingReadings(); });
        $("#counterId").change(function() { CheckPendingReadings(); });
        CheckPendingReadings();

        $(".detailsLink").click( function() { GetEquipmentInfo($(this)); } );
    });
</script>

<div class="column">

    <div class="portlet fixedPortlet">
        <div class="portlet-header">Boas vindas</div>
        <div class="portlet-content">
            <h1>Olá <?php echo $_SESSION["nomeUsr"]; ?></h1>
            <p>Bem vindo!</p><br/>
        </div>
    </div>
    <div class="portlet">
        <div class="portlet-header">Contratos a renovar</div>
        <div class="portlet-content">
            <div style="clear:both;"><br/></div>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;Contrato</th>
                    <th>&nbsp;Parceiro de Negócios</th>
                </tr>
            </thead>
            <tbody id="contractRenewal" >
                <tr>
                    <td colspan="2" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
            <div class="pager pagerListar">
                <span class="wraper">
                    <button class="first">First</button>
                </span>
                <span class="wraper">
                    <button class="prev">Prev</button>
                </span>
                <span class="wraper center">
                    <input type="text" class="pagedisplay"/>
                </span>
                <span class="wraper">
                    <button class="next">Next</button>
                </span>
                <span class="wraper">
                    <button class="last">Last</button>
                </span>
                <input type="hidden" class="pagesize" value="10" />
            </div>
        </div>
    </div>
    <div class="portlet" id="leituras" >
        <div class="portlet-header">Leituras Pendentes</div>
        <div class="portlet-content">
            <label style="float:left; width: 40%;" >Dia da leitura<br/>
            <select id="dayOffset" style="width:99%" >
                <option value="-3" >Daqui a 3 dias</option>
                <option value="-2" >Daqui a 2 dias</option>
                <option value="-1" >Amanhã</option>
                <option value="0" selected="selected" >Hoje</option>
                <option value="1" >Ontem</option>
                <option value="2" >2 dias atrás</option>
                <option value="3" >3 dias atrás</option>
            </select>
            </label>
            <label style="float:left; width: 40%;" >Contador<br/>
            <select id="counterId" style="width:99%" >
            <?php
                $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
                $counterDAO->showErrors = 1;
                $counterArray = $counterDAO->RetrieveRecordArray();
                foreach ($counterArray as $counter) {
                    echo '<option value="'.$counter->id.'" >'.$counter->nome.'</option>';
                }
            ?>
            </select>
            </label>
            <div style="clear:both;"><br/></div>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;Equipamento</th>
                    <th>&nbsp;Telefone Contato</th>
                    <th>&nbsp;Leitura</th>
                </tr>
            </thead>
            <tbody id="pendingReadings" >
                <tr>
                    <td colspan="3" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
        </div>
    </div>
</div>
<div class="column">
    <div class="portlet">
        <div class="portlet-header">Histórico de ações</div>
        <div class="portlet-content">
        <?php
            $actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
            $actionLogDAO->showErrors = 1;
            $loginDAO = new LoginDAO($dataConnector->mysqlConnection);
            $loginDAO->showErrors = 1;

            // traz o histórico dos dez últimos minutos
            $actionArray = $actionLogDAO->RetrieveRecordArray("transacao <> '' AND tipoObjeto = 'trace' AND data > DATE_SUB( NOW(), INTERVAL 10 MINUTE)");
            foreach ($actionArray as $action) {
                $login = $loginDAO->RetrieveRecord($action->login_id);
                $username = $login->nome;
                $dataHora = $action->data.' '.$action->hora;
                $transacao = $action->transacao;

                echo $username.' '.$dataHora.' - '.$transacao."<br/>";
            }
        ?>
        </div>
    </div>
    <div class="portlet" id="pendingSupplies" >
        <div class="portlet-header">Solicitações de consumível pendentes</div>
        <div class="portlet-content">
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th style="width:25%;" >&nbsp;Data</th>
                    <th style="width:65%;" >&nbsp;Descrição</th>
                    <th style="width:10%;" >&nbsp;Equip.</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
                $supplyRequestDAO->showErrors = 1;
                $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
                $requestItemDAO->showErrors = 1;

                // Busca as solicitações de consumível pendentes
                $supplyRequestArray = $supplyRequestDAO->RetrieveRecordArray("status = 2"); // 2 = 'em espera'
                if ( sizeof($supplyRequestArray) == 0 ) {
                    echo '<tr><td colspan="3" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($supplyRequestArray as $supplyRequest) {
                    $equipmentCode = $supplyRequest->codigoCartaoEquipamento;
                    $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoConsumivel_id=".$supplyRequest->id);
                    $description = "";
                    foreach ($requestItemArray as $requestItem) {
                        if (!empty($description)) $description.= ' , ';
                        $description.= $requestItem->quantidade.' '.$requestItem->nomeItem;
                    }
                    if (empty($description)) $description = "Nenhum item encontrado";
                    $requestDescription = new Text($description);

                    echo '<tr>';
                    echo '<td><a href="Frontend/_consumivel/editar.php?id='.$supplyRequest->id.'&equipmentCode='.$equipmentCode.'" >'.$supplyRequest->data.'</a></td>';
                    echo '<td><a href="Frontend/_consumivel/editar.php?id='.$supplyRequest->id.'&equipmentCode='.$equipmentCode.'" >'.$requestDescription->Truncate(30).'</a></td>';
                    echo '<td><a rel="'.$equipmentCode.'" class="detailsLink" ><span class="ui-icon ui-icon-info"></span></a></td>';
                    echo '</tr>';
                }
            ?>
            </tbody>
            </table>
        </div>
    </div>
    <div class="portlet">
        <div class="portlet-header">Contratos a reajustar</div>
        <div class="portlet-content">
            <div style="clear:both;"><br/></div>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;Contrato</th>
                    <th>&nbsp;Parceiro de Negócios</th>
                </tr>
            </thead>
            <tbody id="pricingAdjustments" >
                <tr>
                    <td colspan="2" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
            <div class="pager pagerListar">
                <span class="wraper">
                    <button class="first">First</button>
                </span>
                <span class="wraper">
                    <button class="prev">Prev</button>
                </span>
                <span class="wraper center">
                    <input type="text" class="pagedisplay"/>
                </span>
                <span class="wraper">
                    <button class="next">Next</button>
                </span>
                <span class="wraper">
                    <button class="last">Last</button>
                </span>
                <input type="hidden" class="pagesize" value="10" />
            </div>
        </div>
    </div>
    <div class="portlet">
        <div class="portlet-header">Equipamentos duplicados</div>
        <div class="portlet-content">
            <div style="clear:both;"><br/></div>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;Serial Fabricante</th>
                    <th>&nbsp;Repetições</th>
                </tr>
            </thead>
            <tbody id="duplicates" >
                <tr>
                    <td colspan="2" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
            <div class="pager pagerListar">
                <span class="wraper">
                    <button class="first">First</button>
                </span>
                <span class="wraper">
                    <button class="prev">Prev</button>
                </span>
                <span class="wraper center">
                    <input type="text" class="pagedisplay"/>
                </span>
                <span class="wraper">
                    <button class="next">Next</button>
                </span>
                <span class="wraper">
                    <button class="last">Last</button>
                </span>
                <input type="hidden" class="pagesize" value="10" />
            </div>
        </div>
    </div>
</div>
