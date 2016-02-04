<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/ServiceCallDAO.php");
    include_once("../DataTransferObjects/ServiceCallDTO.php");
    include_once("../DataAccessObjects/CounterDAO.php");
    include_once("../DataTransferObjects/CounterDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");


    $serviceCallId = $_GET['serviceCallId'];
    $technician = $_GET['technician'];
    $data = $_GET['dataAtendimento'];
    $hora = $_GET['horaAtendimento'];

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

    // Busca os dados do chamado
    $serviceCall = $serviceCallDAO->RetrieveRecord($serviceCallId);
    // Se a data de atendimento é menor que a data de abertura usa esta no lugar
    if (strtotime($data) < strtotime($serviceCall->dataAbertura)){
        $data = $serviceCall->dataAbertura;
        $hora = $serviceCall->horaAbertura;
    }

    // Busca os dados do equipamento
    $equipment = $equipmentDAO->RetrieveRecord($serviceCall->codigoCartaoEquipamento);
    $shortDescription = '<b style="color:cadetblue;display:inline-block;" >'.EquipmentDAO::GetShortDescription($equipment).'</b>';
    $useInstructions = '<a style="display:inline-block;" class="useInstructions" rel="'.$equipment->itemCode.'" ><span class="ui-icon ui-icon-info"></span></a>';

?>

<input type="hidden" name="chamado" value="<?php echo $serviceCallId; ?>" />
<input type="hidden" name="data" value="<?php echo $data; ?>" />
<input type="hidden" name="hora" value="<?php echo $hora; ?>" />
<input type="hidden" name="cartaoEquipamento" value="<?php echo $serviceCall->codigoCartaoEquipamento; ?>" />
<input type="hidden" name="codigoItem" value="<?php echo $equipment->itemCode; ?>" />
<input type="hidden" name="assinaturaTecnico" value="<?php echo $technician; ?>" />


<div class="left" style="width:99%; text-align:center;" ><?php echo $shortDescription.'&nbsp;&nbsp;&nbsp;'.$useInstructions; ?></div>

<label class="left" style="width:99%; text-align: left;">Tipo do Contador<br />
    <select name="tipoContador" style="width:98%;" >
    <?php
        $counterDAO = new CounterDAO($dataConnector->mysqlConnection);
        $counterArray = $counterDAO->RetrieveRecordArray();
        $isFirst = true;
        foreach($counterArray as $counter) {
            $attributes = "";
            if ($isFirst) $attributes = "selected='selected'";
            echo "<option ".$attributes." value=".$counter->id." >".$counter->nome."</option>";
            $isFirst = false;
        }
    ?>
    </select>
</label>
<div style="clear:both;">
    <br/>
</div>
<label class="left" style="width:99%; text-align: left;">Contagem(Medição)<br />
    <input type="text" name="contagem" style="width:98%;height:25px;" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>
<label class="left" style="width:99%; text-align: left;">Observação<br />
    <input type="text" name="obs" style="width:98%;height:25px;" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>

<div class="left" style="width:99%; text-align: center;">
    <input id="btnOK" type="button" value="OK" style="width:50px; height:30px;"></input>
</div>

<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>

<script type="text/javascript" >

    function CheckCounter(counter){
        $("input[name=" + counter + "]").css('background-color', 'white');
        var valorContadorAtual = $("input[name=" + counter + "]").val();
        var valorContadorAnterior = 0;

        // Faz um chamada sincrona a página que busca o último contador
        var equipmentCode = $("input[name=cartaoEquipamento]").val();
        var counterId = $("select[name=tipoContador]").val();
        var targetUrl = 'AjaxCalls/GetPreviousReading.php?equipmentCode=' + equipmentCode + '&counterId=' + counterId;
        $.ajax({ url: targetUrl, success: function(response) { valorContadorAnterior = response; }, async: false });

        valorContadorAtual = parseInt(valorContadorAtual) || 0;
        valorContadorAnterior = parseInt(valorContadorAnterior) || 0;
        if (valorContadorAtual <= valorContadorAnterior){
            $("input[name=" + counter + "]").css('background-color', 'orange');
            alert('O valor ' + valorContadorAtual + ' está abaixo do esperado. O contador anterior é ' + valorContadorAnterior);
        }
        // Não verifica se a capacidade do equipamento foi excedida aqui pois as leituras
        // feitas nos chamados são erraticas ( não ocorrem no inicio de cada mês como no caso do faturamento)
    }

    function GetUseInstructions() {
        var itemCode = $("input[name=codigoItem]").val();
        if (!itemCode) itemCode = 0;

        // Faz um chamada sincrona a página
        var targetUrl = "AjaxCalls/GetItemUseInstructions.php?itemCode=" + itemCode;
        $.ajax({ type: 'POST', url: targetUrl, success: function(response) { alert(response); }, async: false });
    }

    function OkButtonClicked() {
        var chamado = $("input[name=chamado]").val();
        var data = $("input[name=data]").val();
        var hora = $("input[name=hora]").val();
        var cartaoEquipamento = $("input[name=cartaoEquipamento]").val();
        var assinaturaTecnico = $("input[name=assinaturaTecnico]").val();

        var tipoContador = $("select[name=tipoContador]").val();
        var contagem = $("input[name=contagem]").val();
        var obs = $("input[name=obs]").val();
        if (!contagem) { alert('Preencher o valor da contagem!'); return false; }

        // Faz um chamada sincrona a página de inserção de leitura
        var targetUrl1 = 'Frontend/_leitura/acao.php';
        var callParameters1 = 'acao=store&equipmentCode=' + cartaoEquipamento + '&contador_id=' + tipoContador + '&contagem=' + contagem + '&ajusteContagem=0';
        callParameters1 = callParameters1 + '&assinaturaDatacopy=' + assinaturaTecnico + '&assinaturaCliente=%20&origemLeitura_id=1&formaLeitura_id=1&chamadoServico_id=' + chamado;
        callParameters1 = callParameters1 + '&data=' + data + '&hora=' + hora + '&obs=' + obs;
        $.ajax({ type: 'POST', url: targetUrl1, data: callParameters1, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#popup").dialog('close');

        // Recarrega os contadores do chamado
        var targetUrl2 = 'AjaxCalls/GetServiceCallReadings.php?serviceCallId=' + chamado;
        $("#contadores").load(targetUrl2);

        return false;
    }

    // Limpa a contagem quando o tipo do contador é alterado
    $("select[name=tipoContador]").change(function() { $("input[name=contagem]").val(''); });

    // Adiciona uma verificação ao sair do campo contagem
    $("input[name=contagem]").blur(function() { CheckCounter("contagem"); });

    $(".useInstructions").click(function() { GetUseInstructions(); });

    $("#btnOK").click(function() { OkButtonClicked(); });

</script>
