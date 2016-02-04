<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/Calendar.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/BillingDAO.php");
include_once("../DataTransferObjects/BillingDTO.php");
include_once("../DataAccessObjects/MailingDAO.php");
include_once("../DataTransferObjects/MailingDTO.php");
include_once("../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../DataTransferObjects/BusinessPartnerDTO.php");
include_once("../DataAccessObjects/ContractDAO.php");
include_once("../DataTransferObjects/ContractDTO.php");


$mailingId = 0;
if (isset($_REQUEST["mailingId"]) && ($_REQUEST["mailingId"] != 0)) {
    $mailingId = $_REQUEST["mailingId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$billingDAO = new BillingDAO($dataConnector->mysqlConnection);
$billingDAO->showErrors = 1;
$mailingDAO = new MailingDAO($dataConnector->mysqlConnection);
$mailingDAO->showErrors = 1;
$businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
$businessPartnerDAO->showErrors = 1;
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;


// Recupera o mailing correspondente ao faturamento
$mailing = $mailingDAO->RetrieveRecord($mailingId);

// Recupera o cliente
$businessPartner = $businessPartnerDAO->RetrieveRecord($mailing->businessPartnerCode);

// Recupera o contrato caso o mailing faça referencia a um
if ($mailing->contrato_id != 0)
    $contract = $contractDAO->RetrieveRecord($mailing->contrato_id);

// Calcula o valor de implantação a ser cobrado/parcelado
$acrescimoDesconto = "";
$observacoes = "";
if (isset($contract)) {
    if (($contract->valorImplantacao > 0) && ($contract->quantParcelasImplantacao > 0)) {
        if ($contract->parcelaAtual <= $contract->quantParcelasImplantacao) {
            $infoParcela = $contract->parcelaAtual."/".$contract->quantParcelasImplantacao;
            $acrescimoDesconto = $contract->valorImplantacao / $contract->quantParcelasImplantacao;
            $observacoes = "PARCELA ".$infoParcela." DE IMPLANTAÇÃO:  R$ ".number_format($acrescimoDesconto, 2, ',', '.');
        }
    }
}

?>

    <form name="fDados" action="Frontend/mailing/acaoFaturamento.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="businessPartnerCode" value="<?php echo $businessPartner->cardCode; ?>" />
        <input type="hidden" name="businessPartnerName" value="<?php echo $businessPartner->cardName; ?>" />
        <input type="hidden" name="mailingId" value="<?php echo $mailingId; ?>" />

        <label class="left" style="width:45%; text-align: left;">Data Inicial<br/>
            <input class="datepick" type="text" name="dataInicial" style="width:95%;height:25px;" value="" ></input>
        </label>
        <label class="left" style="width:45%; text-align: left;">Data Final<br/>
            <input class="datepick" type="text" name="dataFinal" style="width:95%;height:25px;" value="" ></input>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" style="width:45%; text-align: left;">Mês de Referência<br/>
            <select name="mesReferencia" style="width:95%;height:30px;" ><?php $calendar = new Calendar(); echo $calendar->GetMonthOptions(0); ?></select>
        </label>
        <label class="left" style="width:45%; text-align: left;">Ano de Referência<br/>
            <input type="text" name="anoReferencia" style="width:95%;height:30px;" value="" ></input>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" style="width:45%; text-align: left;">Multa Recisoria<br/>
            <input type="text" name="multaRecisoria" style="width:95%;height:25px;" value="" ></input>
        </label>
        <label class="left" style="width:45%; text-align: left;">Outros<br/>
            <input type="text" name="outros" style="width:95%;height:25px;" value="" ></input>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" style="width:99%; text-align: left;">Acréscimo/Desconto<br/>
            <input type="text" readonly="readonly" name="acrescimoDesconto" style="width:98%;height:25px;" value="<?php echo $acrescimoDesconto; ?>" ></input>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left" style="width:99%; text-align: left;">Observações<br/>
            <textarea name="obs" style="width:98%;height:50px;" ><?php echo $observacoes; ?></textarea>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <div class="left" style="width:99%; text-align: center;">
            <input id="btnOK" type="button" value="OK" style="width:80px; height:30px;"></input>
        </div>
    </form>

<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>


<script type="text/javascript" >

    // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
    $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

    function OkButtonClicked() {
        var startDate          = $("input[name=dataInicial]").val();
        var endDate            = $("input[name=dataFinal]").val();
        if (!(startDate) || !(endDate) || (startDate == '') || (endDate == '')) {
            alert('É necessário preencher as datas inicial e final!');
            return;
        }

        var anoReferencia = $("input[name=anoReferencia]").val();
        if (!(anoReferencia) || (anoReferencia == '')) {
            alert('É necessário preencher o mês e o ano de referência!');
            return;
        }

        var acrescimoDesconto = $("input[name=acrescimoDesconto]").val();
        acrescimoDesconto = parseFloat(acrescimoDesconto) || 0;
        $("input[name=acrescimoDesconto]").val(acrescimoDesconto);


        // Faz um chamada sincrona a página de inserção
        var targetUrl1 = 'Frontend/mailing/acaoFaturamento.php';
        $.ajax({ type: 'POST', url: targetUrl1, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega a lista de registros de faturamento
        var targetUrl2 = 'AjaxCalls/GetBillingRecords.php?mailingId=<?php echo $mailingId; ?>';
        $("#billingRecords").load(targetUrl2);
    }

    function TotalChanged()
    {
        var multaRecisoria = $("input[name=multaRecisoria]").val();
        multaRecisoria = parseFloat(multaRecisoria) || 0;
        var outros = $("input[name=outros]").val();
        outros = parseFloat(outros) || 0;

        $("input[name=acrescimoDesconto]").val(multaRecisoria + outros);
    }

    $("input[name=multaRecisoria]").keyup(function() { TotalChanged(); });
    $("input[name=outros]").keyup(function() { TotalChanged(); });

    $("#btnOK").button({ icons: {primary:'ui-icon-circle-check'} }).click(function() { OkButtonClicked(); });

</script>
