<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ServiceCallDAO.php");
include_once("../../DataTransferObjects/ServiceCallDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../../DataAccessObjects/EmployeeDAO.php");
include_once("../../DataTransferObjects/EmployeeDTO.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoChamados"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
$serviceCallDAO->showErrors = 1;

$id = 0;
$serviceCall = new ServiceCallDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $serviceCall = $serviceCallDAO->RetrieveRecord($id);
    if ($serviceCall == null) {
        echo '<br/><h1>Chamado não encontrado</h1><br/>';
        exit;
    }
}

$defaultEmailAddress = ConfigDAO::GetConfigurationParam($dataConnector->mysqlConnection, "emailPadrao");

?>
    <h1>Chamado de Serviço</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function ReloadEquipments(options)
        {
            $("select[name=cartaoEquipamento]").empty();
            $("select[name=cartaoEquipamento]").append(options);
            $("select[name=cartaoEquipamento]").trigger("change");
        }

        function RecarregarUltimosChamados()
        {
            var selectedEquipment = $("select[name=cartaoEquipamento]").val(); // traz o valor selecionado no combo
            var dataAbertura = $("input[name=dataAbertura]").val() + 'T' + $("input[name=horaAbertura]").val(); // traz o valor do datepicker concatenado com a hora

            $("#ultimosChamados").load('AjaxCalls/GetPreviousServiceCalls.php?equipmentCode=' + selectedEquipment + '&cutoffDate=' + dataAbertura);
        }

        function ReloadFormData()
        {
            var businessPartnerCode = $("select[name=businessPartnerCode]").val(); // traz o valor selecionado no combo
            var equipmentCode = <?php echo $serviceCall->codigoCartaoEquipamento; ?>; // traz o último valor gravado no banco
            var targetUrl2 = 'AjaxCalls/GetEquipmentOptions.php?businessPartnerCode=' + businessPartnerCode + '&equipmentCode=' + equipmentCode;
            $.get(targetUrl2, function(response){ ReloadEquipments(response); });
            CheckInactive(businessPartnerCode);
        }

        function CheckInactive(businessPartnerCode) {
            var inativo = $("select[name=businessPartnerCode] option:selected").attr("alt");
        
            if (inativo == 'Y'){
                $("select[name=businessPartnerCode] option:selected").css('background-color', 'orange');
                alert('Este cliente está inativo (vide cadastro de Parceiros de Negócio)\n' + 'Pode ter sido inativado por inadimplência.');
            }
        }

        function GetBusinessPartnerInfo() {
            var businessPartnerCode = $("select[name=businessPartnerCode]").val();

            var targetUrl = "AjaxCalls/GetBusinessPartnerInfo.php?businessPartnerCode=" + businessPartnerCode;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 460, height: 420, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        function GetEquipmentInfo() {
            var equipmentCode = $("select[name=cartaoEquipamento]").val();
            if (!equipmentCode) equipmentCode = 0;
        
            var targetUrl = "AjaxCalls/GetEquipmentInfo.php?equipmentCode=" + equipmentCode + "&showSla=true";
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 560, height: 380, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        function GetServiceCallReadings() {
            var chamado = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetServiceCallReadings.php?serviceCallId=' + chamado;
            $("#contadores").load(targetUrl);
        }

        function GetServiceCallExpenses() {
            var chamado = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetServiceCallExpenses.php?serviceCallId=' + chamado;
            $("#despesas").load(targetUrl);
        }

        function GetServiceCallPartRequests() {
            var chamado = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetServiceCallPartRequests.php?serviceCallId=' + chamado;
            $("#solicitacoesAnteriores").load(targetUrl);
        }

        function BuscarDadosChamadoAnterior(codigoChamadoAnterior)
        {
            var targetUrl = "AjaxCalls/GetServiceCallInfo.php?serviceCallId=" + codigoChamadoAnterior;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 500, height: 520, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        function ExcluirContador(counter)
        {
            if(!confirm("Confirma exclusão?")) return false;

            // Faz um chamada sincrona a página que exclui o contador
            var leitura = counter.attr("rel");
            var targetUrl = 'Frontend/_leitura/acao.php?acao=remove';
            var callParameters = {'reg[]': leitura};
            $.ajax({ type: 'POST', url: targetUrl, data: callParameters, success: function(response) { alert(response); }, async: false });

            // Recarrega os contadores
            var chamado = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetServiceCallReadings.php?serviceCallId=' + chamado;
            $("#contadores").load(targetUrl);
        }

        $(document).ready(function() {
            // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
            $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

            GetServiceCallReadings();
            GetServiceCallExpenses();
            GetServiceCallPartRequests();

            $("#btnBusinessPartnerInfo").click(function() { GetBusinessPartnerInfo(); });
            $("#btnEquipmentInfo").click(function() { GetEquipmentInfo(); });
            $("#btnCalcular").click( function() {
                var targetUrl = 'AjaxCalls/TimeFilterDialog.php?target=tempoAtendimento';
                $("form[name=fDados]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl).dialog({modal:true, width: 200, height: 230, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#btnAdicionarMaoDeObra").button({icons: {primary:'ui-icon-plus'}}).click( function() {
                var serviceCallId = $("input[name=id]").val();
                var dataAtendimento = $("input[name=dataAtendimento]").val();
                if (!dataAtendimento) { alert('Preencher a data de atendimento!'); return false; }
                var horaAtendimento = $("input[name=horaAtendimento]").val();
                if (!horaAtendimento) { alert('Preencher a hora do atendimento!'); return false; }
                var tempoAtendimento = $("input[name=tempoAtendimento]").val();
                if ((!tempoAtendimento) || (tempoAtendimento == '00:00')) { alert('Preencher o tempo de atendimento!'); return false; }

                var targetUrl = 'AjaxCalls/AddServiceCallLaborCost.php?serviceCallId=' + serviceCallId + '&assistanceDate=' + dataAtendimento + '&assistanceTime=' + horaAtendimento + '&assistanceDuration=' + tempoAtendimento;
                $("form[name=fDados]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl).dialog({modal:true, width: 300, height: 180, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                return false;
            });
            $("#btnAdicionarContador").button({ icons: {primary:'ui-icon-plus' } }).click( function() {
                var serviceCallId = $("input[name=id]").val();
                var technician = $("select[name=tecnico]").val();
                var dataAtendimento = $("input[name=dataAtendimento]").val();
                var horaAtendimento = $("input[name=horaAtendimento]").val();
                var targetUrl = 'AjaxCalls/AddServiceCallReading.php?serviceCallId=' + serviceCallId + '&technician=' + technician + '&dataAtendimento=' + dataAtendimento + '&horaAtendimento=' + horaAtendimento;
                $("form[name=fDados]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl).dialog({modal:true, width: 350, height: 320, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                return false;
            });
            $("#btnEnviarSolicitacao").button({ icons: {primary:'ui-icon-mail-closed'} }).click( function() {
                $("#btnEnviarSolicitacao").attr('disabled', 'disabled');
                var recipients = $("input[name=recipients]").val();
                var targetUrl = 'AjaxCalls/RequestEquipmentParts.php?recipients=' + recipients + '&serviceCallId=<?php echo $serviceCall->id; ?>';
                $.ajax({ url: targetUrl, success: function(response) { $("#btnEnviarSolicitacao").removeAttr('disabled'); alert(response); }, async: false });
                GetServiceCallPartRequests();
            });
            $("#btnImprimirFormulario").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                var reportUrl = 'Frontend/<?php echo $currentDir; ?>/formularioSolicitacaoPecas.php?serviceCallId=<?php echo $serviceCall->id; ?>&sendToPrinter=true';
                window.open(reportUrl);
            });
            $("#btnImprimirChamado").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                window.open( $(this).attr("href") );
                return false;
            });

            $("select[name=businessPartnerCode]").change(function() { ReloadFormData(); });
            ReloadFormData(); // Carrega seleção prévia
        });

    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="chamado" value="<?php echo $id; ?>" />

        <?php
            $btnAttributes = '';
            $url = 'Frontend/chamados/despesas/editar.php?id=0&chamado='.$serviceCall->id;
            if ($nivelAutorizacao < 3) {
                $btnAttributes = 'disabled="disabled"';
                $url = '#';
            }
        ?>

        <!-- Só exibe a caixa com o número quando o chamado tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <label>Número do Chamado<br /> <!-- exibe o número do chamado para o usuário, o campo "id" é um input hidden -->
            <input type="text" readonly="readonly" size="30" value="<?php echo str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT); ?>" />
            </label>
        </div>

        <label>Defeito<br />
        <input type="text" name="defeito" size="120" value="<?php echo $serviceCall->defeito; ?>" />
        </label>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Data de Abertura<br/>
        <input type="text" name="dataAbertura" size="30" readonly="readonly" value="<?php echo empty($serviceCall->dataAbertura)? date("Y-m-d",time()) : $serviceCall->dataAbertura ?>" />
        <input type="text" name="horaAbertura" size="10" readonly="readonly" value="<?php echo empty($serviceCall->horaAbertura)? date("H:i",time()) : $serviceCall->horaAbertura ?>" />
        &nbsp;&nbsp;&nbsp;
        </div>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Data de Fechamento<br/>
        <input class="datepick" type="text" name="dataFechamento" size="30" value="<?php echo empty($serviceCall->dataFechamento)? "" : $serviceCall->dataFechamento ?>" />
        <input type="text" name="horaFechamento" size="10" value="<?php echo empty($serviceCall->horaFechamento)? "" : $serviceCall->horaFechamento ?>" />
        </div>
        <div style="clear:both;">
            &nbsp;
        </div>

        <!-- Só exibe informações de atendimento quando o chamado tiver um número (id) -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
            Data de Atendimento<br/>
            <input class="datepick" type="text" name="dataAtendimento" size="30" value="<?php echo empty($serviceCall->dataAtendimento)? "" : $serviceCall->dataAtendimento ?>" />
            <input type="text" name="horaAtendimento" size="10" value="<?php echo empty($serviceCall->horaAtendimento)? "" : $serviceCall->horaAtendimento ?>" />
            &nbsp;&nbsp;&nbsp;
            </div>

            <label class="left">Tempo de Atendimento<br />
            <input type="text" name="tempoAtendimento" size="25" value="<?php echo $serviceCall->tempoAtendimento; ?>" />
            <input type="button" id="btnCalcular" value="..." style="width: 30px; height: 30px;" />
            </label>
            <label class="left">Despesa com Mão de obra<br />
            <button type="button" <?php echo $btnAttributes; ?> style="height: 30px;" id="btnAdicionarMaoDeObra" >Adicionar Despesa</button>
            </label>
            <div style="clear:both;">
                <br/>
            </div>
        </div>

        <label class="left">Parceiro de Negócio<br />
        <select name="businessPartnerCode" style="width: 350px;">
            <option selected='selected' value="0"></option>
        	<?php
                $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
                $businessPartnerDAO->showErrors = 1;
                $clientArray = $businessPartnerDAO->RetrieveRecordArray("CardName IS NOT NULL ORDER BY CardName");
                foreach ($clientArray as $client) {
                    $attributes = "";
                    if ($client->cardCode == $serviceCall->businessPartnerCode) $attributes = "selected='selected'";
                    $informacaoAdicional = "";
                    if ($client->cardName != $client->cardFName) $informacaoAdicional = " (".$client->cardFName.")";
                    $clientInfo = new Text($client->cardName.$informacaoAdicional);
                    echo "<option ".$attributes." value=".$client->cardCode." alt=".$client->inactive." >".$clientInfo->Truncate(85)."</option>";
                }
        	?>
        </select>
        <input type="button" id="btnBusinessPartnerInfo" value="?" style="width: 30px; height: 30px;" />
        </label>

        <label class="left">Contato<br />
        <input type="text" name="contato" size="60" value="<?php echo $serviceCall->contato; ?>" />
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label class="left">Status<br />
        <select name="status" style="width: 350px;">
        	<?php
        	    $statusArray = $serviceCallDAO->RetrieveServiceCallStatuses($dataConnector->sqlserverConnection);
        	    foreach($statusArray as $key=>$value)
        	    {
        	    	$attributes = "";
        	    	if ($key == $serviceCall->status) $attributes = "selected='selected'";
                    echo "<option ".$attributes." value=".$key.">".$value."</option>";
        		}
        	?>
        </select>
        </label>

        <label class="left">Tipo<br />
        <select name="tipo" style="width: 350px;">
        	<?php
        	    $callTypeArray = $serviceCallDAO->RetrieveServiceCallTypes($dataConnector->sqlserverConnection);
        	    foreach($callTypeArray as $key=>$value)
        	    {
        	    	$attributes = "";
        	    	if ($key == $serviceCall->tipo) $attributes = "selected='selected'";
                    echo "<option ".$attributes." value=".$key.">".$value."</option>";
        		}
        	?>
        </select>
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label class="left">Aberto por<br />
        <select name="abertoPor" style="width: 350px;">
        	<?php
        	    $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
                $employeeDAO->showErrors = 1;
                $employeeArray = $employeeDAO->RetrieveEmployeesByPosition('Assistente', 'Auxiliar');
                foreach($employeeArray as $employee)
                {
        	    	$attributes = "";
        	    	if ($employee->empID == $serviceCall->abertoPor) $attributes = "selected='selected'";
        	    	$employeeName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
                    echo "<option ".$attributes." value=".$employee->empID.">".$employeeName."</option>";
        		}
        	?>
        </select>
        </label>

        <label class="left">Técnico<br />
        <select name="tecnico" style="width: 350px;">
            <option value=0 >-- Nenhum --</option>
        	<?php
                $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
                $employeeDAO->showErrors = 1;
                $employeeArray = $employeeDAO->RetrieveEmployeesByPosition('Técnico', 'Tecnico');
                foreach($employeeArray as $employee)
                {
                    $attributes = "";
                    if ($employee->empID == $serviceCall->tecnico) $attributes = "selected='selected'";
                    $employeeName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
                    echo "<option ".$attributes." value=".$employee->empID.">".$employeeName."</option>";
        		}
        	?>
        </select>
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label class="left">Prioridade<br />
        <select name="prioridade" style="width: 350px;">
            <option value="1" <?php echo ($serviceCall->prioridade == 1)? 'selected="selected"' : ''; ?> >1- Baixa</option>
            <option value="2" <?php echo ($serviceCall->prioridade == 2)? 'selected="selected"' : ''; ?> >2- Média</option>
            <option value="3" <?php echo ($serviceCall->prioridade == 3)? 'selected="selected"' : ''; ?> >3- Alta</option>
            <option value="4" <?php echo ($serviceCall->prioridade == 4)? 'selected="selected"' : ''; ?> >4- Urgente</option>
        </select>
        </label>

        <label class="left">Cartão do Equipamento<br/>
        <select name="cartaoEquipamento" style="width: 300px;" onchange="javascript:RecarregarUltimosChamados();"></select>
        <input type="button" id="btnEquipmentInfo" value="?" style="width: 30px; height: 30px;" />
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label>Observações<br />
        <input type="text" name="observacaoTecnica" size="120" value="<?php echo $serviceCall->observacaoTecnica; ?>" />
        </label>

        <label>Sintoma<br />
        <input type="text" name="sintoma" size="120" value="<?php echo $serviceCall->sintoma ?>" />
        </label>

        <label>Causa<br />
        <input type="text" name="causa" size="120" value="<?php echo $serviceCall->causa ?>" />
        </label>

        <label>Açao<br />
        <input type="text" name="acao_reparo" size="120" value="<?php echo $serviceCall->acao ?>" />
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <fieldset>
            <legend>Últimos Chamados</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" style="width: 650px;">
            <thead>
                <tr>
                    <th>&nbsp;Número</th>
                    <th>&nbsp;Defeito</th>
                    <th>&nbsp;Data de abertura</th>
                    <th>&nbsp;Link</th>
                </tr>
            </thead>
            <tbody id="ultimosChamados"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="2" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
           </table>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <!-- Só exibe o botão quando o chamado tiver um número (id) -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" <?php echo $btnAttributes; ?> id="btnAdicionarContador" >Adicionar Contador</button>
        </div>
        <div style="clear:both;">
            &nbsp;
        </div>

        <fieldset>
            <legend>Contadores</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" style="width: 650px;">
            <thead>
                <tr>
                    <th>&nbsp;Tipo do Contador</th>
                    <th>&nbsp;Contagem(Leitura)</th>
                    <th>&nbsp;Observação</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="contadores"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="4" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
           </table>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <!-- Só exibe o botão quando o chamado tiver um número (id) -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <a href="<?php echo $url; ?>" <?php echo $btnAttributes; ?> class="addDespesa" >
                Adicionar Despesa
            </a>
        </div>
        <div style="clear:both;">
            &nbsp;
        </div>

        <fieldset>
            <legend>Despesas</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" style="width: 650px;">
            <thead>
                <tr>
                    <th>&nbsp;Descrição</th>
                    <th>&nbsp;Valor (R$)</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="despesas"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="3" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <!-- Só exibe a caixa de solicitação de peças quando o chamado tiver um número (id) -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
        <fieldset>
            <legend>Solicitação de Peças</legend>
            <div id="solicitacaoPecas" style="width: 650px;">
                <label class="left">Destinatários (emails separados por vírgula)<br/>
                <input type="text" name="recipients" style="width: 280px;" value="<?php echo $defaultEmailAddress; ?>" />
                </label>
                <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;" >
                <br/>
                &nbsp;<button type="button" <?php echo $btnAttributes; ?> style="height: 30px;" id="btnEnviarSolicitacao" >Enviar Solicitação</button>
                &nbsp;<button type="button" <?php echo $btnAttributes; ?> style="height: 30px;" id="btnImprimirFormulario" >Imprimir Formulário</button>
                </div>
            </div>
            <div style="clear:both;">
                <br/>
            </div>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" style="width: 650px;">
            <thead>
                <tr>
                    <th>&nbsp;Data da Solicitação</th>
                    <th>&nbsp;Descrição (Itens)</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="solicitacoesAnteriores"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="3" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>
        </div>

        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo NavigateBackTarget(); ?>" class="buttonVoltar" >
            Voltar
        </a>

        <button type="submit" class="button" id="btnform">
            Salvar
        </button>

        <!-- Só exibe o botão quando o chamado tiver um número (id) -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" href="<?php echo 'Frontend/'.$currentDir.'/imprimir.php?serviceCallId='.$serviceCall->id.'&sendToPrinter=true'; ?>" style="float:right;" id="btnImprimirChamado" >
                Imprimir
            </button>
        </div>

    </form>
<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
