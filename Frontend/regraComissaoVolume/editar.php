<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CommissionPerVolumeDAO.php");
include_once("../../DataTransferObjects/CommissionPerVolumeDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$commissionPerVolumeDAO = new CommissionPerVolumeDAO($dataConnector->mysqlConnection);
$commissionPerVolumeDAO->showErrors = 1;


$id = 0;
$commissionRule = new CommissionPerVolumeDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $commissionRule = $commissionPerVolumeDAO->RetrieveRecord($id);
}

?>
    <h1>Regra de comissão (Por volume de contratos)</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >
        $(document).ready(function() {
            // Seta o formato de data do datepicker para manter compatibilidade com o formato do MySQL
            $('.datepick').datepicker({dateFormat: 'yy-mm-dd'});
         });
    </script>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <label>Categoria de contrato<br/>
        <select name="categoriaContrato" style="width: 350px;">
            <option value="1" <?php if ($commissionRule->categoriaContrato == 1) echo ' selected '; ?>>
                Outsourcing
            </option>
            <option value="2" <?php if ($commissionRule->categoriaContrato == 2) echo ' selected '; ?>>
                GED
            </option>
            <option value="3" <?php if ($commissionRule->categoriaContrato == 3) echo ' selected '; ?>>
                Gestão TI
            </option>
            <option value="4" <?php if ($commissionRule->categoriaContrato == 4) echo ' selected '; ?>>
                Assistência Técnica
            </option>
            <option value="5" <?php if ($commissionRule->categoriaContrato == 5) echo ' selected '; ?>>
                Venda de Ativo
            </option>
        </select>
        </label>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <div style="font-weight:bold; margin-right:10px; margin-top:10px;" >Quantidade de Contratos ( na faixa )<br/>
        <input type="text" name="quantContratosDe" size="25" value="<?php echo $commissionRule->quantContratosDe; ?>" />
        até
        <input type="text" name="quantContratosAte" size="25" value="<?php echo $commissionRule->quantContratosAte; ?>" />
        </div>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <div style="font-weight:bold; margin-right:10px; margin-top:10px;" >Valor dos faturamentos somados ( na faixa )<br/>
        <input type="text" name="valorFaturamentoDe" size="25" value="<?php echo $commissionRule->valorFaturamentoDe; ?>" />
        até
        <input type="text" name="valorFaturamentoAte" size="25" value="<?php echo $commissionRule->valorFaturamentoAte; ?>" />
        </div>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <label>Comissão<br/>
        <input type="text" name="comissao" size="65" value="<?php echo $commissionRule->comissao; ?>" />
        </label>
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
