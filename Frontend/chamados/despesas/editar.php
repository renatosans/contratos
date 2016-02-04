<?php

session_start();

include_once("../../../check.php");

include_once("../../../defines.php");
include_once("../../../ClassLibrary/DataConnector.php");
include_once("../../../DataAccessObjects/ExpenseDAO.php");
include_once("../../../DataTransferObjects/ExpenseDTO.php");
include_once("../../../DataAccessObjects/ProductionInputDAO.php");
include_once("../../../DataTransferObjects/ProductionInputDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
$expenseDAO->showErrors = 1;
$productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
$productionInputDAO->showErrors = 1;

$id = 0;
$expense = new ExpenseDTO();
// O cadastro de despesa está formatado para INSERTS apenas


$serviceCallId = 0;
if (isset($_REQUEST["chamado"]) && ($_REQUEST["chamado"] != 0)) {
    $serviceCallId = $_REQUEST["chamado"];
}

?>

    <h1>Administração - Despesa de chamado</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >

        function HandleItemReload(sortedBy) {
            var targetUrl = 'AjaxCalls/GetInventoryItemOptions.php?sortedBy=' + sortedBy + '&defaultItemCode=<?php echo $expense->codigoItem; ?>';
            $.get(targetUrl, function(response){ ReloadItem(response); });
        }

        function ReloadItem(options) {
            $("select[name=codigoItem]").empty();
            $("select[name=codigoItem]").append(options);
            $("select[name=codigoItem]").trigger("change");
        }

        function UpdateItem()
        {
            // Atualiza o nome do item
            var nomeItem = $("select[name=codigoItem] option:selected").text();
            $("input[name=nomeItem]").val(nomeItem);

            // Zera a quantidade e o total 
            $("input[name=quantidade]").val('0');
            $("input[name=totalDespesa]").val('0');
        }
    
        function SetTipoCusto()
        {
            var tipoCusto = $("select[name=tipoCusto]").val();
            if (tipoCusto == 0)
            {
                $("select[name=codigoInsumo]").empty();
    
                $("#itensDespesa").show();
                $("#medicaoCusto").hide();
            }
            else
            {
                var unidadeMedida = $("#unidadeMedida" + tipoCusto).val();
                $("#tituloMedicaoInicial").text(unidadeMedida + " Inicial");
                $("#tituloMedicaoFinal").text(unidadeMedida + " Final");
                $("#tituloValorUnitario").text("Valor/" + unidadeMedida);
                var targetUrl = 'AjaxCalls/GetProductionInputOptions.php?inputType=' + tipoCusto + '&productionInputId=<?php echo $expense->codigoInsumo; ?>';
                $.get(targetUrl, function(options){
                    $("select[name=codigoInsumo]").empty();
                    $("select[name=codigoInsumo]").append(options);
                    $("select[name=codigoInsumo]").change();
                });

                $("#itensDespesa").hide();
                $("#medicaoCusto").show();
            }
        }

        function SetCalculationParams(){
            var valorUnitario = $("select[name=codigoInsumo] option:selected").attr("alt");
            $("#valorUnitario").val(valorUnitario);

            $("input[name=totalDespesa]").val('0');
        }

        function Calcular(){
            var tipoCusto = $("select[name=tipoCusto]").val();
            if (tipoCusto == 0){
                var precoMedioItem = $("select[name=codigoItem] option:selected").attr("alt");
                var quantidade = $("input[name=quantidade]").val();
                var total = precoMedioItem * quantidade;
                $("input[name=totalDespesa]").val(total);
                return;
            }

            var medicaoInicial = $("input[name=medicaoInicial]").val();
            var medicaoFinal = $("input[name=medicaoFinal]").val();
            var valorUnitario = $("#valorUnitario").val();
            var totalDespesa = (medicaoFinal - medicaoInicial) * valorUnitario;
            $("input[name=totalDespesa]").val(totalDespesa);
        }

        function GetServiceCallExpenses() {
            var serviceCallId = $("input[name=chamado]").val();
            var targetUrl = 'AjaxCalls/GetServiceCallExpenses.php?serviceCallId=' + serviceCallId;
            $("#despesas").load(targetUrl);
        }


        $(document).ready(function() {
            $("#btnAdd").button({ icons: {primary:'ui-icon-circle-check'} }).click( function() {            
                // Faz um chamada sincrona a página de gravação
                var targetUrl = 'Frontend/chamados/despesas/acao.php';
                $.ajax({ type: 'POST', url: targetUrl, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

                // Recarrega as despesas do chamado
                GetServiceCallExpenses();
            });

        	$("select[name=codigoItem]").change(function() { UpdateItem(); });
            $("select[name=tipoCusto]").change(function() { SetTipoCusto(); });
            $("select[name=codigoInsumo]").change(function() { SetCalculationParams(); });
            $("input[name=btnCalcular]").click(function() { Calcular(); });
            $("input[name=rdioSortedBy]").click( function() {
                var sortedBy = $("input[name=rdioSortedBy]:checked").val();
                HandleItemReload(sortedBy);
            });
            HandleItemReload(2); // Carrega o combo de itens, deixa o item default selecionado
            UpdateItem(); // Atualiza os dados do item baseado no combo de seleção de item
            SetTipoCusto(); // Oculta ou exibe o painel de medição de custos
            GetServiceCallExpenses(); // Carrega a lista de despesas
        });
    </script>

    <form name="fDados" action="Frontend/chamados/despesas/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="0" /> <!-- O cadastro de despesa está formatado para INSERTS apenas -->
        <input type="hidden" name="chamado" value="<?php echo $serviceCallId; ?>" />


        <label>Tipo do Custo<br/>
            <select name="tipoCusto" style="width: 350px;">
                <option value=0 >-- Nenhum --</option>
                <?php
                    $inputTypeId = 0;
                    if (!empty($expense->codigoInsumo)){
                        $productionInput = $productionInputDAO->RetrieveRecord($expense->codigoInsumo);
                        $inputTypeId = $productionInput->tipoInsumo;
                    }
                    $inputTypeArray = $productionInputDAO->RetrieveInputTypes();
                    foreach($inputTypeArray as $key=>$value){
        	            $attributes = "";
        	            if ($key == $inputTypeId) $attributes = "selected='selected'";
                        echo "<option ".$attributes." value=".$key.">".$value."</option>";
                    }
                ?>
            </select>
        </label>
        <?php
            $measurementUnitArray = $productionInputDAO->RetrieveMeasurementUnits();
            foreach( $measurementUnitArray as $key=>$value ){
                echo "<input type=hidden id='unidadeMedida".$key."' value=".$value." />";
            }
        ?>
        <div style="clear:both;">
            <br/>
        </div>

        <div id="itensDespesa">
        <fieldset style="width: 650px;" >
        <legend>Itens da Despesa</legend>        
            <fieldset>
                <legend style="font-size: 15px;">Ordenação de itens</legend>
                <input type="radio" name="rdioSortedBy" value="1" >&nbsp;Por nome&nbsp;</input><br/>
                <input type="radio" name="rdioSortedBy" value="2" checked="checked" >&nbsp;Por código&nbsp;</input><br/>
            </fieldset>
            <div style="clear:both;">
                &nbsp;
            </div>
    
            <label>Item<br/>
            <select name="codigoItem" style="width: 350px;"></select>
            <br/>
            <input type="hidden" name="nomeItem" value="<?php echo $expense->nomeItem; ?>" />
            </label>
    
            <label>Quantidade<br/>
                <input type="text" name="quantidade" size="65" value="<?php echo $expense->quantidade; ?>" />
            </label>
            <br/><br/>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>
        </div>

        <div id="medicaoCusto">
        <fieldset style="width: 650px;" >
        <legend>Medição do Custo</legend>
        <label><p id="tituloMedicaoInicial" >Medição Inicial</p><br />
            <input type="text" name="medicaoInicial" size="65" value="<?php echo $expense->medicaoInicial; ?>" />
        </label>

        <label><p id="tituloMedicaoFinal" >Medição Final</p><br />
            <input type="text" name="medicaoFinal" size="65" value="<?php echo $expense->medicaoFinal; ?>" />
        </label>

        <label><p id="tituloValorUnitario" >Valor Unitário</p><br />
            <select name="codigoInsumo" style="width: 350px;" ></select>
            <input type="hidden" id="valorUnitario" value="1" />
        </label>
        <br/><br/>
        </fieldset>
        <div style="clear:both;">
            <br/>
        </div>
        </div>

        <label>Total da Despesa (R$)<br />
            <input type="text" name="totalDespesa" size="65" value="<?php echo $expense->totalDespesa; ?>" />
            <input type="button" name="btnCalcular" value="..." style="width: 30px; height: 30px;" />
        </label>

        <label>Observação<br />
            <input type="text" name="observacao" size="80" value="<?php echo $expense->observacao; ?>" />
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <fieldset style="width: 650px;" >
            <legend>Despesas</legend>
            <table border="0" cellpadding="0" cellspacing="0" class="sorTable" >
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
            <br/><br/>
        </div>


        <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
            Voltar
        </a>
        <!-- Salva através de chamada AJAX para que o usuário possa cadastrar todas as despesas sem sair da tela -->
        <button type="button" id="btnAdd" > 
            Adicionar
        </button>

    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
