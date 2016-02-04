<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ReadingDAO.php");
include_once("../../DataTransferObjects/ReadingDTO.php");
include_once("../../DataAccessObjects/CounterDAO.php");
include_once("../../DataTransferObjects/CounterDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/EmployeeDAO.php");
include_once("../../DataTransferObjects/EmployeeDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");


$equipmentCode = 0;
if (isset($_REQUEST["equipmentCode"]) && ($_REQUEST["equipmentCode"] != 0)) {
    $equipmentCode = $_REQUEST["equipmentCode"];
}
$subContract = 0;
if (isset($_REQUEST["subContract"]) && ($_REQUEST["subContract"] != 0)) {
    $subContract = $_REQUEST["subContract"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoLeituras"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
$readingDAO->showErrors = 1;
$counterDAO = new CounterDAO($dataConnector->mysqlConnection);
$counterDAO->showErrors = 1;
$equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
$equipmentDAO->showErrors = 1;
$equipment = $equipmentDAO->RetrieveRecord($equipmentCode);
$capacity = $equipment->capacity;
$serialNumber = $equipment->manufacturerSN." ( ".$equipment->internalSN." ) ";
$clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $equipment->customer));


$id = 0;
$reading = new ReadingDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $reading = $readingDAO->RetrieveRecord($id);
}

?>

    <h1>Administração - Leitura - <?php echo $serialNumber; ?></h1><br/>
    <h1><?php echo str_pad('_', 52, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

            <!-- Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL  -->
            <script type="text/javascript" >
                function CheckCounter(counter){
                    $("input[name=" + counter + "]").css('background-color', 'white');
                    var valorContadorAtual = $("input[name=" + counter + "]").val();
                    var valorContadorAnterior = 0;

                    // Faz um chamada sincrona a página que busca o último contador
                    var equipmentCode = $("input[name=equipmentCode]").val();
                    var counterId = $("select[name=contador_id]").val();
                    var cutoffDate = $("input[name=data]").val() + 'T' + $("input[name=hora]").val();
                    var targetUrl = 'AjaxCalls/GetPreviousReading.php?equipmentCode=' + equipmentCode + '&counterId=' + counterId + '&cutoffDate=' + cutoffDate;
                    $.ajax({ url: targetUrl, success: function(response) { valorContadorAnterior = response; }, async: false });

                    valorContadorAtual = parseInt(valorContadorAtual) || 0;
                    valorContadorAnterior = parseInt(valorContadorAnterior) || 0;
                    if (valorContadorAtual <= valorContadorAnterior){
                        $("input[name=" + counter + "]").css('background-color', 'orange');
                        alert('O valor ' + valorContadorAtual + ' está abaixo do esperado. O contador anterior é ' + valorContadorAnterior);
                    }

                    // Faz uma chamada sincrona a mesma página, desta vez restringindo a origem da leitura ao faturamento (ignora leituras intermediárias feitas no chamado de serviço)
                    var origemLeitura = 2;
                    targetUrl = targetUrl + '&origemLeitura=' + origemLeitura;
                    $.ajax({ url: targetUrl, success: function(response) { valorContadorAnterior = response; }, async: false });
                    valorContadorAnterior = parseInt(valorContadorAnterior) || 0;

                    // Compara as leituras de faturamento de hoje com a do mês anterior (ignora leituras intermediárias feitas no chamado de serviço)
                    var consumption = valorContadorAtual - valorContadorAnterior;
                    var capacity = parseInt('<?php echo $capacity; ?>') || 0;

                    if (consumption > capacity){
                        // $("input[name=" + counter + "]").css('background-color', 'orange');
                        // alert('Perigo! O consumo do equipamento está acima de sua capacidade. Consumo=' + consumption + ' Capacidade=' + capacity);
                    }
                }

                $(document).ready(function() {
                    $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

                    // Limpa a contagem quando o tipo do contador é alterado
                    $("select[name=contador_id]").change(function() { $("input[name=contagem]").val(''); });
                    // Adiciona uma verificação ao sair do campo contagem
                    $("input[name=contagem]").blur(function() { CheckCounter("contagem"); });
                });
            </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />
        <input type="hidden" name="equipmentCode" value="<?php echo $equipmentCode; ?>" />


        <label>Contador<br />
            <select name="contador_id">
            <?php
                $counterArray = $counterDAO->RetrieveRecordArray();
                foreach ($counterArray as $counter) {
                    $attributes = "";
                    if ($counter->id == $reading->codigoContador) $attributes = "selected='selected'";
                    echo '<option value='.$counter->id.' '.$attributes.' >'.$counter->nome.'</option>';
                }
            ?>
            </select>
        </label>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Data<br/>
        <input type="text" class="datepick" name="data" size="30" value="<?php echo empty($reading->data)? "" : $reading->data; ?>" />
        <input type="text" name="hora" size="10" value="<?php echo empty($reading->hora)? "" : $reading->hora; ?>" />
        &nbsp;&nbsp;&nbsp;
        </div>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label class="left" >Contagem(Leitura)<br/>
            <input type="text" name="contagem" value="<?php echo $reading->contagem; ?>" />
            &nbsp;&nbsp;&nbsp;
        </label>

        <label class="left" >Ajuste de Leitura<br/>
            <input type="text" name="ajusteContagem" value="<?php echo $reading->ajusteContagem; ?>" />
            (com sinal para abatimento)
            &nbsp;&nbsp;&nbsp;
        </label>

        <label class="left">Reset?<br/>
            <input type="checkbox" name="reset" <?php echo $reading->reset ? 'checked="checked"' : '' ; ?> />
        </label>
        <div style="clear:both;">
            &nbsp;
        </div>

        <label>Assinatura DataCopy<br />
            <select name="assinaturaDatacopy">
            <?php
                $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
                $employeeArray = $employeeDAO->RetrieveRecordArray();
                foreach ($employeeArray as $employee) {
                    $attributes = "";
                    if ($employee->empID == $reading->assinaturaDatacopy) $attributes = "selected='selected'";
                    echo "<option ".$attributes." value=".$employee->empID.">".$employee->firstName." ".$employee->middleName." ".$employee->lastName."</option>";
                }
            ?>
            </select>
        </label>

        <label>Assinatura <?php echo $clientName->Truncate(75); ?><br/>
            <input type="text" style="width:460px;" name="assinaturaCliente" value="<?php echo $reading->assinaturaCliente; ?>" />
        </label>

        <label>Observações<br/>
            <textarea name="obs" style="width:460px;height:50px;" ><?php echo $reading->observacao; ?></textarea>
        </label>

        <input type="hidden" name="origemLeitura_id" value="<?php echo empty($reading->origemLeitura)? '2' : $reading->origemLeitura; ?>"/>

        <label>Forma de Leitura<br/>
            <select name="formaLeitura_id">
            <?php
                $readingKindArray = $readingDAO->RetrieveReadingKinds();
                foreach ($readingKindArray as $key=>$value) {
                    $attributes = "";
                    if ($key == $reading->formaLeitura) $attributes = "selected='selected'";
                    echo '<option value='.$key.' '.$attributes.' >'.$value.'</option>';
                }
            ?>
            </select>
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform">
            Salvar
        </button>
    </form>
<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
