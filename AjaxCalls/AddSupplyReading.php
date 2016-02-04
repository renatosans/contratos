<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/SupplyRequestDAO.php");
    include_once("../DataTransferObjects/SupplyRequestDTO.php");
    include_once("../DataAccessObjects/CounterDAO.php");
    include_once("../DataTransferObjects/CounterDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../DataAccessObjects/EmployeeDAO.php");
    include_once("../DataTransferObjects/EmployeeDTO.php");


    $supplyRequestId = $_GET['supplyRequestId'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $supplyRequestDAO = new SupplyRequestDAO($dataConnector->mysqlConnection);
    $supplyRequestDAO->showErrors = 1;
    $equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
    $equipmentDAO->showErrors = 1;

    // Busca os dados da solicitação de consumível
    $supplyRequest = $supplyRequestDAO->RetrieveRecord($supplyRequestId);

    // Busca o número de série do equipamento e o nome do cliente
    $equipment = $equipmentDAO->RetrieveRecord($supplyRequest->codigoCartaoEquipamento);
    $shortDescription = '<b style="color:cadetblue;display:inline-block;" >'.EquipmentDAO::GetShortDescription($equipment).'</b>';
    $useInstructions = '<a style="display:inline-block;" class="useInstructions" rel="'.$equipment->itemCode.'" ><span class="ui-icon ui-icon-info"></span></a>';
    $clientName = BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $equipment->customer);

?>

<input type="hidden" name="pedidoConsumivel" value="<?php echo $supplyRequestId; ?>" />
<input type="hidden" name="data" value="<?php echo date("Y-m-d",time()); ?>" />
<input type="hidden" name="hora" value="<?php echo date("H:i",time()); ?>" />
<input type="hidden" name="cartaoEquipamento" value="<?php echo $supplyRequest->codigoCartaoEquipamento; ?>" />
<input type="hidden" name="codigoItem" value="<?php echo $equipment->itemCode; ?>" />

<div class="left" style="width:99%; text-align: center;" ><?php echo $shortDescription.'&nbsp;&nbsp;&nbsp;'.$useInstructions; ?></div>

<label class="left" style="width:99%; text-align: left;">Tipo do Contador<br/>
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
<label class="left" style="width:99%; text-align: left;">Contagem(Medição)<br/>
    <input type="text" name="contagem" style="width:98%;height:25px;" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>
<label class="left" style="width:99%; text-align: left;">Assinatura Datacopy<br/>
    <select name="assinaturaDatacopy" style="width:98%;" >
    <?php
        $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
        $employeeArray = $employeeDAO->RetrieveRecordArray();
        foreach ($employeeArray as $employee) { 
            echo "<option value=".$employee->empID.">".$employee->firstName." ".$employee->middleName." ".$employee->lastName."</option>";
        }
    ?>
    </select>
</label>
<label class="left" style="width:99%; text-align: left;">Assinatura do Cliente<br/><?php echo $clientName; ?><br/>
    <input type="text" name="assinaturaCliente" style="width:98%;height:25px;" ></input>
</label>
<div style="clear:both;">
    <br/>
</div>
<label class="left" style="width:99%; text-align: left;">Observação<br/>
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
        // feitas aqui são erraticas ( não ocorrem no inicio de cada mês como no caso do faturamento)
    }

    function GetUseInstructions() {
        var itemCode = $("input[name=codigoItem]").val();
        if (!itemCode) itemCode = 0;

        // Faz um chamada sincrona a página
        var targetUrl = "AjaxCalls/GetItemUseInstructions.php?itemCode=" + itemCode;
        $.ajax({ type: 'POST', url: targetUrl, success: function(response) { alert(response); }, async: false });
    }

    function OkButtonClicked() {
        var pedidoConsumivel = $("input[name=pedidoConsumivel]").val();
        var data = $("input[name=data]").val();
        var hora = $("input[name=hora]").val();
        var cartaoEquipamento = $("input[name=cartaoEquipamento]").val();

        var tipoContador = $("select[name=tipoContador]").val();
        var contagem = $("input[name=contagem]").val();
        var assinaturaDatacopy = $("select[name=assinaturaDatacopy]").val();
        var assinaturaCliente = $("input[name=assinaturaCliente]").val();
        var obs = $("input[name=obs]").val();
        if (!contagem) { alert('Preencher o valor da contagem!'); return false; }

        // Faz um chamada sincrona a página de inserção de leitura
        var targetUrl1 = 'Frontend/_leitura/acao.php';
        var callParameters1 = 'acao=store&equipmentCode=' + cartaoEquipamento + '&contador_id=' + tipoContador + '&contagem=' + contagem + '&ajusteContagem=0';
        callParameters1 = callParameters1 + '&assinaturaDatacopy=' + assinaturaDatacopy + '&assinaturaCliente=' + assinaturaCliente + '&origemLeitura_id=3&formaLeitura_id=2&consumivel_id=' + pedidoConsumivel;
        callParameters1 = callParameters1 + '&data=' + data + '&hora=' + hora + '&obs=' + obs;
        $.ajax({ type: 'POST', url: targetUrl1, data: callParameters1, success: function(response) { alert(response); }, async: false });

        // Fecha o dialogo
        $("#addDialog").dialog('close');

        // Recarrega os contadores do chamado
        var targetUrl2 = 'AjaxCalls/GetSupplyReadings.php?supplyRequestId=' + pedidoConsumivel;
        $("#supplyReadings").load(targetUrl2);

        return false;
    }

    // Limpa a contagem quando o tipo do contador é alterado
    $("select[name=tipoContador]").change(function() { $("input[name=contagem]").val(''); });

    // Adiciona uma verificação ao sair do campo contagem
    $("input[name=contagem]").blur(function() { CheckCounter("contagem"); });

    $(".useInstructions").click(function() { GetUseInstructions(); });

    $("#btnOK").click(function() { OkButtonClicked(); });

</script>
