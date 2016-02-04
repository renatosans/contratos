<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/Calendar.php");
include_once("../../ClassLibrary/UnixTime.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../../DataAccessObjects/SalesPersonDAO.php");
include_once("../../DataTransferObjects/SalesPersonDTO.php");
include_once("../../DataAccessObjects/AdjustmentRateDAO.php");
include_once("../../DataTransferObjects/AdjustmentRateDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;

$id = 0;
$contract = new ContractDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $contract = $contractDAO->RetrieveRecord($id);
}

?>

    <h1>Administração - Contrato</h1>

    <script type="text/javascript" >
    
        function ReloadFormData() {
            var businessPartnerCode = $("select[name=pn]").val(); // traz o valor selecionado no combo
            var contactPersonCode = <?php echo $contract->contato; ?>; // traz o último valor gravado no banco
            var targetUrl = 'AjaxCalls/GetContactPersonOptions.php?businessPartnerCode=' + businessPartnerCode + '&contactPersonCode=' + contactPersonCode;
            $.get(targetUrl, function(response){ $("select[name=contato]").html(response); });
        }

        function GetBusinessPartnerInfo() {
            var businessPartnerCode = $("select[name=pn]").val();

            var targetUrl = "AjaxCalls/GetBusinessPartnerInfo.php?businessPartnerCode=" + businessPartnerCode;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 460, height: 420, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        function GetContactInfo() {
            var contactCode = $("select[name=contato]").val();
            if (!contactCode) contactCode = 0;
    
            var targetUrl = "AjaxCalls/GetContactInfo.php?contactCode=" + contactCode;
            $("form[name=fDados]").append("<div id='popup'></div>");
            $("#popup").load(targetUrl).dialog({modal:true, width: 400, height: 250, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        function GetContractAdjustments()
        {
            var contractId = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetAdjustments.php?contractId=' + contractId;
            $("#adjustmentList").load(targetUrl);
        }

        function GetContractExpenses()
        {
            var contractId = $("input[name=id]").val();
            var period = $("#period").val();
    
            var targetUrl = 'AjaxCalls/GetContractExpenses.php?contractId=' + contractId + '&period=' + period;
            $("#expenseList").load(targetUrl);
        }

        function GetContractSubitems() {
            var contractId = $("input[name=id]").val();
    
            var targetUrl = 'AjaxCalls/GetContractSubitems.php?contractId=' + contractId;
            $("#subcontractList").load(targetUrl);
        }

        $(document).ready(function() {
            // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
            $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

            $(".btnRefresh").button({ icons: {primary:'ui-icon-arrowrefresh-1-n'} });

            $("#btnCopyAddress").button({ icons: {primary:'ui-icon-arrowreturnthick-1-s' } }).click( function() {
                var businessPartnerCode = $("select[name=pn]").val(); // traz o valor selecionado no combo
                var targetUrl = 'AjaxCalls/ChooseAddressDialog.php?businessPartnerCode=' + businessPartnerCode;
                $("form[name=fDados]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl).dialog({modal:true, width: 350, height: 180, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
                return false;
            });

            $("#pinAdjustments").click( function() {
                var className = $('#pinAdjustments').attr('class');
                if (className == 'ui-icon ui-icon-pin-s') {
                    $('#pinAdjustments').attr('class', 'ui-icon ui-icon-pin-w');
                    $('#divAdjustments').css('display', 'none');
                }
                if (className == 'ui-icon ui-icon-pin-w') {
                    $('#pinAdjustments').attr('class', 'ui-icon ui-icon-pin-s');
                    $('#divAdjustments').css('display', 'inline');
                }
            });

            $("#pinExpenses").click( function() {
                var className = $('#pinExpenses').attr('class');
                if (className == 'ui-icon ui-icon-pin-s') {
                    $('#pinExpenses').attr('class', 'ui-icon ui-icon-pin-w');
                    $('#divExpenses').css('display', 'none');
                }
                if (className == 'ui-icon ui-icon-pin-w') {
                    $('#pinExpenses').attr('class', 'ui-icon ui-icon-pin-s');
                    $('#divExpenses').css('display', 'inline');
                }
            });

            $("#btnBusinessPartnerInfo").click(function() { GetBusinessPartnerInfo(); });
            $("#btnContactInfo").click(function() { GetContactInfo(); });
            $("#period").change(function() { GetContractExpenses(); });
            $("select[name=pn]").change(function() { ReloadFormData(); });
            ReloadFormData(); // Carrega seleção prévia

            GetContractAdjustments();
            GetContractExpenses();
            GetContractSubitems();
         });

    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />


        <label class="left">Numero<br />
        <input type="text" name="numero" size="20" value="<?php echo $contract->numero; ?>" />
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Parceiro de Negócios<br/>
        <select name="pn" style="width: 350px;">
            <option selected='selected' value="0"></option>
            <?php
                $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
                $businessPartnerDAO->showErrors = 1;
                $clientArray = $businessPartnerDAO->RetrieveRecordArray("CardName IS NOT NULL ORDER BY CardName");
                foreach ($clientArray as $client) {
                    $attributes = "";
                    if ($client->cardCode == $contract->pn) $attributes = "selected='selected'";
                    $informacaoAdicional = "";
                    if ($client->cardName != $client->cardFName) $informacaoAdicional = " (".$client->cardFName.")";
                    $clientInfo = new Text($client->cardName.$informacaoAdicional);
                    echo "<option ".$attributes." value=".$client->cardCode.">".$clientInfo->Truncate(85)."</option>";
                }
            ?>
        </select>
        <input type="button" id="btnBusinessPartnerInfo" value="?" style="width: 30px; height: 30px;" ></input>
        </label>

        <label class="left">Divisão<br/>
        <input type="text" name="divisao" size="25" value="<?php echo $contract->divisao; ?>" />
        </label>   

        <label class="left">Contato<br/>
        <select name="contato" style="width: 180px;"></select>
        <input type="button" id="btnContactInfo" value="?" style="width: 30px; height: 30px;" ></input>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" >Vendedor<br />
        <select name="vendedor" style="width: 250px;">
            <?php
                $salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
                $salesPersonDAO->showErrors = 1;
                $salesPersonArray = $salesPersonDAO->RetrieveRecordArray();
                foreach($salesPersonArray as $salesPerson)
                {
                    $attributes = "";
                	if ($salesPerson->slpCode == $contract->vendedor) $attributes = "selected='selected'";
                    echo "<option ".$attributes." value=".$salesPerson->slpCode.">".$salesPerson->slpName."</option>";
                }
            ?>
        </select>
        </label>

        <label class="left">Indice de Reajuste<br />
        <select name="indicesReajuste_id" style="width: 320px;" >
        <?php
            $adjustmentRateDAO = new AdjustmentRateDAO($dataConnector->mysqlConnection);
            $adjustmentRateDAO->showErrors = 1;
            $adjustmentRateArray = $adjustmentRateDAO->RetrieveRecordArray();
            foreach ($adjustmentRateArray as $adjustmentRate)
            {
                $attributes = ($adjustmentRate->id == $contract->indiceReajuste) ? "selected" : "";
                echo '<option '.$attributes.' value='.$adjustmentRate->id.'>'.$adjustmentRate->sigla." - ".$adjustmentRate->nome.'</option>';
            }
        ?>
        </select>
        </label>

        <label class="left">Global?<br/>
        <input type="checkbox" name="global" <?php echo $contract->global ? 'checked="checked"' : '' ; ?> />
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Data de Assinatura<br/>
        <!-- Marca a data de assinatura como readonly caso esteja editando o contrato  -->
        <?php $attributes = "class='datepick'"; if ($id != 0) $attributes = "readonly='readonly'"; ?>
        <input type="text" <?php echo $attributes; ?> name="assinatura" size="30" value="<?php echo empty($contract->dataAssinatura)? date("Y-m-d",time()) : $contract->dataAssinatura; ?>" />
        </label>

        <label class="left">Data de Encerramento<br />
        <input class="datepick" type="text" name="encerramento" size="30" value="<?php echo empty($contract->dataEncerramento)? date("Y-m-d",time()) : $contract->dataEncerramento; ?>" />
        </label>

        <label class="left">Inicio do Atendimento<br />
        <input class="datepick" type="text" name="inicioAtendimento" size="30" value="<?php echo empty($contract->inicioAtendimento)? date("Y-m-d", time()) : $contract->inicioAtendimento; ?>" />
        </label>

        <label class="left">Fim do Atendimento<br />
        <input class="datepick" type="text" name="fimAtendimento" size="30" value="<?php echo empty($contract->fimAtendimento)? date("Y-m-d", time()) : $contract->fimAtendimento; ?>" />
        </label>
        <div style="clear:both;">
            <br/>
        </div>


        <?php
        $parcelaAtual = 0;
        $totalParcelas = 0;
        if ($id != 0) {
            $dataInicioAtendimento = empty($contract->inicioAtendimento)? time() : strtotime($contract->inicioAtendimento);
            $dataFimAtendimento = empty($contract->fimAtendimento)? time() : strtotime($contract->fimAtendimento);

            // Caso a data da renovação esteja vazia usa a data de inicio do atendimento
            $dataRenovacao = empty($contract->dataRenovacao)? $dataInicioAtendimento : strtotime($contract->dataRenovacao);
            // Caso a data da primeira parcela esteja vazia usa a data de inicio do atendimento
            $dataPrimeiraParcela  = empty($contract->primeiraParcela)? $dataInicioAtendimento : strtotime($contract->primeiraParcela);

            // Calcula a diferença de datas
            $tempoDecorrido = UnixTime::Diff(time(), $dataPrimeiraParcela);
            $duracaoContrato = UnixTime::Diff($dataFimAtendimento, $dataRenovacao);

            $parcelaAtual = $tempoDecorrido['year']*12 + $tempoDecorrido['month'] + 1;
            $totalParcelas = $duracaoContrato['year']*12 + $duracaoContrato['month'];
        }
        ?>

        <label class="left">Primeira Parcela<br/>
        <input class="datepick" type="text" name="primeiraParcela" size="30" value="<?php echo empty($contract->primeiraParcela)? date("Y-m-d", time()) : $contract->primeiraParcela; ?>" />
        </label>

        <label class="left">Parcela Atual<br/>
        <input type="text" name="parcelaAtual" size="10" value="<?php echo $contract->parcelaAtual; ?>" />
        </label>

        <label class="left">Mês de Referência<br/>
        <input type="hidden" name="mesReferencia" value="<?php echo $contract->mesReferencia; ?>" />
        <input type="text" readonly="readonly" size="20" value="<?php $calendar = new Calendar(); echo $calendar->GetMonthName($contract->mesReferencia); ?>" />
        </label>

        <label class="left">Ano de Referência<br/>
        <input type="text" name="anoReferencia" readonly="readonly" size="20" value="<?php echo $contract->anoReferencia; ?>" />
        </label>

        <label class="left">Quant. Parcelas<br/>
        <input type="text" name="quantidadeParcelas" size="10" value="<?php echo $contract->quantidadeParcelas; ?>" />
        </label>   
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" style="width: 210px;" >Status<br />
        <select name="status" style="width: 90%;" >
            <option value="1" <?php if ($contract->status == 1) echo ' selected '; ?>>
                Pendente
            </option>
            <option value="2" <?php if ($contract->status == 2) echo ' selected '; ?>>
                Vigente
            </option>
            <option value="3" <?php if ($contract->status == 3) echo ' selected '; ?>>
                Finalizado
            </option>
            <option value="4" <?php if ($contract->status == 4) echo ' selected '; ?>>
                Cancelado
            </option>
            <option value="5" <?php if ($contract->status == 5) echo ' selected '; ?>>
                Renovado
            </option>
            <option value="6" <?php if ($contract->status == 6) echo ' selected '; ?>>
                Reajustado
            </option>
        </select>
        </label>

        <label>Categoria<br/>
        <select name="categoria" style="width: 210px;">
            <option value="1" <?php if ($contract->categoria == 1) echo ' selected '; ?>>
                Outsourcing
            </option>
            <option value="2" <?php if ($contract->categoria == 2) echo ' selected '; ?>>
                GED
            </option>
            <option value="3" <?php if ($contract->categoria == 3) echo ' selected '; ?>>
                Gestão TI
            </option>
            <option value="4" <?php if ($contract->categoria == 4) echo ' selected '; ?>>
                Assistência Técnica
            </option>
            <option value="5" <?php if ($contract->categoria == 5) echo ' selected '; ?>>
                Venda de Ativo
            </option>
        </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <!-- Só exibe botão e data de renovação quando o contrato tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <label class="left">Data da Última Renovação<br/>
            <input class="datepick" type="text" name="dataRenovacao" size="30" value="<?php echo empty($contract->dataRenovacao)? "" : $contract->dataRenovacao; ?>" />
            </label>

            <div class="left" style="font-weight:bold; margin-right:10px; margin-top:23px;">
            <a href="Frontend/<?php echo $currentDir; ?>/renovar.php?id=<?php echo $contract->id; ?>" class="btnRefresh" >Renovar</a>
            </div>
        </div>

        <!-- Só exibe botão e data de reajuste quando o contrato tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <label class="left">Data do Último Reajuste<br/>
            <input class="datepick" type="text" name="dataReajuste" size="30" value="<?php echo empty($contract->dataReajuste)? "" : $contract->dataReajuste; ?>" />
            </label>

            <div class="left" style="font-weight:bold; margin-right:10px; margin-top:23px;">
            <a href="Frontend/<?php echo $currentDir; ?>/reajustar.php?id=<?php echo $contract->id; ?>" class="btnRefresh" >Reajustar</a>
            </div>
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Dia de Leitura<br/>
        <input type="text" name="diaLeitura" size="15" value="<?php echo $contract->diaLeitura; ?>" /><br/>
        <select name="referencialLeitura" style="width: 200px;">
            <option value="-1" <?php echo ($contract->referencialLeitura == -1) ? 'selected="selected"' : ''; ?> >No mês anterior</option>
            <option value="0"  <?php echo ($contract->referencialLeitura == 0)  ? 'selected="selected"' : ''; ?> >No mês do faturamento</option>
            <option value="1"  <?php echo ($contract->referencialLeitura == 1)  ? 'selected="selected"' : ''; ?> >No mês posterior</option>
        </select>
        &nbsp;&nbsp;&nbsp;
        </div>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Dia de Vencimento<br/>
        <input type="text" name="diaVencimento" size="15" value="<?php echo $contract->diaVencimento; ?>" /><br/>
        <select name="referencialVencimento" style="width: 200px;">
            <option value="-1" <?php echo ($contract->referencialVencimento == -1) ? 'selected="selected"' : ''; ?> >No mês anterior</option>
            <option value="0"  <?php echo ($contract->referencialVencimento == 0)  ? 'selected="selected"' : ''; ?> >No mês do faturamento</option>
            <option value="1"  <?php echo ($contract->referencialVencimento == 1)  ? 'selected="selected"' : ''; ?> >No mês posterior</option>
        </select>
        &nbsp;&nbsp;&nbsp;
        </div>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <label class="left">Valor Implantação<br/>
        <input type="text" name="valorImplantacao" size="25" value="<?php echo $contract->valorImplantacao; ?>" />
        </label>   

        <label>Parcelado em<br/>
        <select name="quantParcelasImplantacao" style="width: 210px;">
            <option value="1" <?php if ($contract->quantParcelasImplantacao == 1) echo ' selected '; ?>>1</option>
            <option value="2" <?php if ($contract->quantParcelasImplantacao == 2) echo ' selected '; ?>>2</option>
            <option value="3" <?php if ($contract->quantParcelasImplantacao == 3) echo ' selected '; ?>>3</option>
            <option value="4" <?php if ($contract->quantParcelasImplantacao == 4) echo ' selected '; ?>>4</option>
            <option value="5" <?php if ($contract->quantParcelasImplantacao == 5) echo ' selected '; ?>>5</option>
            <option value="6" <?php if ($contract->quantParcelasImplantacao == 6) echo ' selected '; ?>>6</option>
            <option value="7" <?php if ($contract->quantParcelasImplantacao == 7) echo ' selected '; ?>>7</option>
            <option value="8" <?php if ($contract->quantParcelasImplantacao == 8) echo ' selected '; ?>>8</option>
        </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Observação<br/>
            <textarea name="comments" style="width: 650px;" ><?php echo $contract->obs; ?></textarea>
        </label>
        <br/>
        <button type="button" id="btnCopyAddress" >Copiar Endereço</button>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <fieldset style="width: 650px;" >
            <legend>
                <span style="float:left;">Reajustes</span>
                <span id="pinAdjustments" style="float:left;" class="ui-icon ui-icon-pin-s"></span>
                <div style="clear:both;" />
            </legend>
            <div id="divAdjustments">
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th style="width: 20%;" >&nbsp;Data</th>
                    <th style="width: 60%;" >&nbsp;Indice</th>
                    <th style="width: 20%;" >&nbsp;Aliquota</th>
                </tr>
            </thead>
            <tbody id="adjustmentList">
                <tr>
                    <td colspan="3" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
            </div>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width: 650px;" >
            <legend>
                <span style="float:left;">Despesas do contrato</span>
                <span id="pinExpenses" style="float:left;" class="ui-icon ui-icon-pin-s"></span>
                <div style="clear:both;" />
            </legend>
            <div id="divExpenses">
            <label>Período<br/>
            <select id="period" style="width: 210px;" >
                <option value="1" >Mês passado</option>
                <option value="2" >Últimos 30 dias</option>
                <option value="3" >Desde a abertura do contrato</option>
            </select>
            </label>
            <div style="clear:both;"><br/></div>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th style="width: 15%;" >&nbsp;Data</th>
                    <th style="width: 25%;" >&nbsp;Equipamento</th>
                    <th style="width: 45%;" >&nbsp;Descrição</th>
                    <th style="width: 15%;" >&nbsp;Valor (R$)</th>
                </tr>
            </thead>
            <tbody id="expenseList">
                <tr>
                    <td colspan="4" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
            </table>
            </div>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>

        <!-- Só exibe o botão quando o contrato possuir um id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <a href="<?php echo 'Frontend/contrato/sub-contrato/editar.php?contractId='.$contract->id; ?>" class="addSub"  >
                Adicionar Sub-Contrato
            </a>
            <div style="clear:both;">
                &nbsp;
            </div>
        </div>
        
        <fieldset style="width: 650px;" >
            <legend>Sub-Contratos</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;Tipo</th>
                    <th>&nbsp;Itens</th>
                </tr>
            </thead>
            <tbody id="subcontractList">
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
        </fieldset>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>


        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform" >
            Salvar
        </button>

    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
