<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/AdjustmentRateDAO.php");
include_once("../../DataTransferObjects/AdjustmentRateDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$adjustmentRateDAO = new AdjustmentRateDAO($dataConnector->mysqlConnection);
$adjustmentRateDAO->showErrors = 1;

$id = 0;
$adjustmentRate = new AdjustmentRateDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $adjustmentRate = $adjustmentRateDAO->RetrieveRecord($id); 
}

?>

    <h1>Administração - Indice de Reajuste</h1>
    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <label>Sigla<br/>
        <input type="text" name="sigla" size="65" value="<?php echo $adjustmentRate->sigla; ?>" />
        </label>

        <label>Nome<br/>
        <input type="text" name="nome" size="65" maxlength="100" value="<?php echo $adjustmentRate->nome; ?>" />
        </label>

        <label>Aliquota<br/>
        <input type="text" name="aliquota" size="65" value="<?php echo $adjustmentRate->aliquota; ?>" />
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
