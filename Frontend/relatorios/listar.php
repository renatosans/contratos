<?php

    session_start();

    include_once("../../check.php");
    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    $nivelAutorizacaoEquipmts = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoEquipmtPecas"]);
    $nivelAutorizacaoChamados = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoChamados"]);
    $nivelAutorizacaoContratos = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
    $nivelAutorizacaoFaturamentos = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["sinteseFaturamento"]);
    $nivelAutorizacaoLeituras = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoLeituras"]);

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>

    <h1>Relatórios</h1><br/>
    <h1><?php echo str_pad('_', 64, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >
        $(document).ready(function() {
            $("#rptChamados").click( function() {
                var reportUrl = 'Frontend/chamados/relatorioChamados.php';
                if ($("#chkExportToExcel").is(":checked"))  reportUrl = 'Frontend/chamados/relatorioChamadosExcel.php';
                var targetUrl = 'AjaxCalls/ServiceCallFilterDialog.php';
                var params = '&searchMethod=' + $("#searchMethod").val();

                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:params}).dialog({modal:true, width: 410, height: 410, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#rptContratos").click( function() {
                var reportUrl = 'Frontend/contrato/relatorioContratos.php';
                if ($("#chkExportToExcel").is(":checked"))  reportUrl = 'Frontend/contrato/relatorioContratosExcel.php';
                var targetUrl = 'AjaxCalls/ContractFilterDialog.php';
                var params = '&searchMethod=' + $("#searchMethod").val();

                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:params}).dialog({modal:true, width: 450, height: 500, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#rptMaoDeObra").click( function() {
                var reportUrl = 'Frontend/equipamentos/relatorioMaoDeObra.php';
                if ($("#chkExportToExcel").is(":checked"))  reportUrl = 'Frontend/equipamentos/relatorioMaoDeObraExcel.php';
                var targetUrl = 'AjaxCalls/LaborExpenseFilterDialog.php';
                var params = '&searchMethod=' + $("#searchMethod").val();

                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:params}).dialog({modal:true, width: 410, height: 360, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#rptFaturamento").click( function() {
                var reportUrl = 'Frontend/faturamento/relatorioFaturamento.php';
                if ($("#chkExportToExcel").is(":checked"))  reportUrl = 'Frontend/faturamento/relatorioFaturamentoExcel.php';
                var targetUrl = 'AjaxCalls/BillingFilterDialog.php';
                var params = '&searchMethod=' + $("#searchMethod").val();

                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:params}).dialog({modal:true, width: 410, height: 380, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#rptNotasFiscais").click( function() {
                var reportUrl = 'Frontend/faturamento/relatorioNFs.php';
                if ($("#chkExportToExcel").is(":checked"))  reportUrl = 'Frontend/faturamento/relatorioNFsExcel.php';
                var targetUrl = 'AjaxCalls/InvoiceFilterDialog.php';
                var params = '&searchMethod=' + $("#searchMethod").val();

                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:params}).dialog({modal:true, width: 410, height: 380, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
            $("#rptLeituras").click( function() {
                var reportUrl = 'Frontend/_leitura/relatorioLeituras.php';
                if ($("#chkExportToExcel").is(":checked"))  reportUrl = 'Frontend/_leitura/relatorioLeiturasExcel.php';
                var targetUrl = 'AjaxCalls/ReadingFilterDialog.php';
                var params = '&searchMethod=' + $("#searchMethod").val();

                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:params}).dialog({modal:true, width: 410, height: 380, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
        });
    </script>

    <form name="fLista" >
        <label style="float:left; width: 40%;" >Método de Busca<br/>
        <select id="searchMethod" style="width:99%" >
            <option value="0" >Por cliente</option>
            <option value="1" >Por modelo de equipamento</option>
            <option value="2" >Por cliente/modelo de equip.</option>
            <option value="3" >Por cliente/série de equip.</option>
        </select>
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <h3>Chamados</h3><br/>
        <div style="margin-left: 50px;" >
            <?php
                if ($nivelAutorizacaoChamados > 1)
                    echo '<a href="#" id="rptChamados" >Relatório</a>';
                else
                    echo '<span style="color:red;font-weight:bold;" >Sem autorização</span>';
            ?>
        </div><br/>
        <h3>Contratos</h3><br/>
        <div style="margin-left: 50px;" >
            <?php
                if ($nivelAutorizacaoContratos > 1)
                    echo '<a href="#" id="rptContratos" >Relatório</a>';
                else
                    echo '<span style="color:red;font-weight:bold;" >Sem autorização</span>';
            ?>
        </div><br/>
        <h3>Despesas com Mão de Obra</h3><br/>
        <div style="margin-left: 50px;" >
            <?php
                if ($nivelAutorizacaoEquipmts > 1)
                    echo '<a href="#" id="rptMaoDeObra" >Relatório</a>';
                else
                    echo '<span style="color:red;font-weight:bold;" >Sem autorização</span>';
            ?>
        </div><br/>
        <h3>Faturamento</h3><br/>
        <div style="margin-left: 50px;" >
            <?php
                if ($nivelAutorizacaoFaturamentos > 1)
                    echo '<a href="#" id="rptFaturamento" >Relatório</a>';
                else
                    echo '<span style="color:red;font-weight:bold;" >Sem autorização</span>';
            ?>
        </div><br/>
        <h3>NFs de Fatura</h3><br/>
        <div style="margin-left: 50px;" >
            <?php
                if ($nivelAutorizacaoFaturamentos > 1)
                    echo '<a href="#" id="rptNotasFiscais" >Relatório</a>';
                else
                    echo '<span style="color:red;font-weight:bold;" >Sem autorização</span>';
            ?>
        </div><br/>
        <h3>Leituras</h3><br/>
        <div style="margin-left: 50px;" >
            <?php
                if ($nivelAutorizacaoLeituras > 1)
                    echo '<a href="#" id="rptLeituras" >Relatório</a>';
                else
                    echo '<span style="color:red;font-weight:bold;" >Sem autorização</span>';
            ?>
        </div><br/>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <input type="checkbox" id="chkExportToExcel" ><b>&nbsp;Exportar para Excel&nbsp;</b></input>
        <div style="clear:both;">
            <br/><br/>
        </div>
    </form>
