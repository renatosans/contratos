<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/IndirectCostDAO.php");
include_once("../../DataTransferObjects/IndirectCostDTO.php");
include_once("../../DataAccessObjects/ProductionInputDAO.php");
include_once("../../DataTransferObjects/ProductionInputDTO.php");
include_once("../../DataAccessObjects/EmployeeDAO.php");
include_once("../../DataTransferObjects/EmployeeDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$indirectCostDAO = new IndirectCostDAO($dataConnector->mysqlConnection);
$indirectCostDAO->showErrors = 1;
$productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
$productionInputDAO->showErrors = 1;


$id = 0;
$indirectCost = new IndirectCostDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $indirectCost = $indirectCostDAO->RetrieveRecord($id);
}

?>

    <h1>Administração - Custo Indireto</h1>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function HandleOptionsReload(selected) {
            var targetUrl = 'AjaxCalls/GetProductionInputOptions.php?inputType=0&productionInputId=' + selected;
            $.get(targetUrl, function(response){ ReloadOptions(response); });
        }

        function ReloadOptions(options) {
            $("select[name=codigoInsumo]").empty();
            $("select[name=codigoInsumo]").append(options);
            $("select[name=codigoInsumo]").change();
        }

        function SetTipoInsumo()
        {
            var tipoInsumo = $("select[name=codigoInsumo] option:selected").attr("class");
            $("#tipoInsumo").text($("#tipoInsumo" + tipoInsumo).val());
            var unidadeMedida = $("#unidadeMedida" + tipoInsumo).val();
            $("#tituloMedicaoInicial").text(unidadeMedida + " Inicial");
            $("#tituloMedicaoFinal").text(unidadeMedida + " Final");
        }

        function Calcular(){
            var medicaoInicial = $("input[name=medicaoInicial]").val();
            var medicaoFinal = $("input[name=medicaoFinal]").val();
            var valorUnitario = $("select[name=codigoInsumo] option:selected").attr("alt");
            var total = (medicaoFinal - medicaoInicial) * valorUnitario;
            $("input[name=total]").val(total);
        }

        function GetServiceCalls() {
            var indirectCostId = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/GetDistributedExpenses.php?indirectCostId=' + indirectCostId;
            $("#serviceCalls").load(targetUrl);
        }

        function AddServiceCall() {
            var indirectCostId = $("input[name=id]").val();
            var targetUrl = 'AjaxCalls/AddDistributedExpense.php?indirectCostId=' + indirectCostId;
            $("form[name=fDados]").append("<div id='addDialog'></div>");
            $("#addDialog").load(targetUrl).dialog({modal:true, width: 360, height: 180, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
        }

        $(document).ready(function() {
            $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});

            $("select[name=codigoInsumo]").change(function() { SetTipoInsumo(); });
            $("input[name=btnCalcular]").click(function() { Calcular(); });
            $("#btnAdicionarChamado").button({ icons: {primary:'ui-icon-plus' } }).click( function() { AddServiceCall(); });

            HandleOptionsReload('<?php echo $indirectCost->codigoInsumo; ?>');
            GetServiceCalls();
        });

    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <?php
            $inputTypeArray = $productionInputDAO->RetrieveInputTypes();
            foreach ($inputTypeArray as $index=>$description) {
                echo "<input type='hidden' id='tipoInsumo".$index."' value='".$description."' />";
            }
            $measurementUnitArray = $productionInputDAO->RetrieveMeasurementUnits();
            foreach( $measurementUnitArray as $index=>$description ){
                echo "<input type='hidden' id='unidadeMedida".$index."' value='".$description."' />";
            }
        ?>

        <div class="left" style="font-weight:bold; margin-right:10px; margin-top:10px;">
        Data<br/>
        <input type="text" class="datepick" name="data" size="30" value="<?php echo empty($indirectCost->data)? "" : $indirectCost->data; ?>" />
        <input type="text" name="hora" size="10" value="<?php echo empty($indirectCost->hora)? "" : $indirectCost->hora; ?>" />
        &nbsp;&nbsp;&nbsp;
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Solicitante (Técnico ou atendente)<br/>
            <select name="solicitante" style="width: 350px;">
            <?php
                $employeeDAO = new EmployeeDAO($dataConnector->sqlserverConnection);
                $employeeDAO->showErrors = 1;
                $employeeArray = $employeeDAO->RetrieveRecordArray();
                foreach($employeeArray as $employee)
                {
                    $attributes = "";
                    if ($employee->empID == $indirectCost->solicitante) $attributes = "selected='selected'";
                    $employeeName = $employee->firstName." ".$employee->middleName." ".$employee->lastName;
                    echo "<option ".$attributes." value=".$employee->empID.">".$employeeName."</option>";
                }
            ?>
            </select>
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label>Informações do Solicitante (Exemplo: Placa do Veículo)<br/>
            <input type="text" name="infoSolicitante" size="80" value="<?php echo $indirectCost->infoSolicitante; ?>" />
        </label>

        <label>Insumo<br/>
            <select name="codigoInsumo"></select><br/>
            <b style="font-size:13px;color:red;margin:20px;" >Tipo: <span id="tipoInsumo" ></span></b>
        </label>

        <label><p id="tituloMedicaoInicial" >Medição Inicial</p><br/>
            <input type="text" name="medicaoInicial" size="65" value="<?php echo $indirectCost->medicaoInicial; ?>" />
        </label>

        <label><p id="tituloMedicaoFinal" >Medição Final</p><br/>
            <input type="text" name="medicaoFinal" size="65" value="<?php echo $indirectCost->medicaoFinal; ?>" />
        </label>

        <label>Total<br/>
            <input type="text" name="total" size="65" value="<?php echo $indirectCost->total; ?>" />
            <input type="button" name="btnCalcular" value="..." style="width: 30px; height: 30px;" />
        </label>

        <label>Observações<br/>
            <textarea name="observacao" style="width:460px;height:50px;" ><?php echo $indirectCost->observacao; ?></textarea>
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <!-- Só exibe o botão quando tiver id -->
        <div style="<?php echo $id == 0 ? "display:none;" : "display:inline;" ?>" >
            <button type="button" id="btnAdicionarChamado" >Adicionar Chamado</button>
        </div>
        <div style="clear:both;">
            <br/>
        </div>

        <fieldset style="width: 650px;" >
            <legend>Chamados</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
            <thead>
                <tr>
                    <th>&nbsp;Número</th>
                    <th>&nbsp;Defeito</th>
                    <th>&nbsp;Equipamento</th>
                    <th>&nbsp;Data de Abertura</th>
                    <th>&nbsp;Excluir</th>
                </tr>
            </thead>
            <tbody id="serviceCalls"> <!-- Populado através de chamada AJAX -->
                <tr>
                    <td colspan="5" align="center" >Nenhum registro encontrado!</td>
                </tr>
            </tbody>
           </table>
        </fieldset>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>
        <button type="submit" class="button" id="btnform">
            Salvar
        </button>
    </form>

<?php 
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
