<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ProductionInputDAO.php");
include_once("../../DataTransferObjects/ProductionInputDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
$productionInputDAO->showErrors = 1;

$id = 0;
$productionInput = new ProductionInputDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $productionInput = $productionInputDAO->RetrieveRecord($id); 
}

?>

    <h1>Administração -  Insumo</h1>
    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="store" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <label>Descrição<br />
        <input type="text" name="descricao" size="65" value="<?php echo $productionInput->descricao; ?>" />
        </label>

        <label>Tipo do Insumo<br />
            <select name="tipoInsumo" style="width: 350px;">
                <?php
                    $inputTypeArray = $productionInputDAO->RetrieveInputTypes();
                    foreach ($inputTypeArray as $key=>$value) {
                        $attributes = "";
                        if ($key == $productionInput->tipoInsumo) $attributes = "selected='selected'";
                        echo "<option ".$attributes." value=".$key.">".$value."</option>";
                    }
                ?>
            </select>
        </label>

        <label>Valor (R$)<br />
        <input type="text" name="valor" size="65" value="<?php echo $productionInput->valor; ?>" />
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
