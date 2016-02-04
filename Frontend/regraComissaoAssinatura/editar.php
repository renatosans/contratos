<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CommissionPerSignatureDAO.php");
include_once("../../DataTransferObjects/CommissionPerSignatureDTO.php");
include_once("../../DataAccessObjects/IndustryDAO.php");
include_once("../../DataTransferObjects/IndustryDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$commissionPerSignatureDAO = new CommissionPerSignatureDAO($dataConnector->mysqlConnection);
$commissionPerSignatureDAO->showErrors = 1;
$industryDAO = new IndustryDAO($dataConnector->sqlserverConnection);
$industryDAO->showErrors = 1;


$id = 0;
$commissionRule = new CommissionPerSignatureDTO();
if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];    
    $commissionRule = $commissionPerSignatureDAO->RetrieveRecord($id);
}

?>
    <h1>Regra de comissão (Por assinatura dos contratos)</h1><br/>
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

        <label>Segmento<br/>
            <select name="segmento" style="width: 350px;">
                <option value=0>Todos</option>
                <?php
                $industryArray = $industryDAO->RetrieveRecordArray();
                foreach ($industryArray as $industry) {
                    $attributes = "";
                    if ($commissionRule->segmento == $industry->id) $attributes = "selected='selected'";
                    echo '<option '.$attributes.' value='.$industry->id.'>'.$industry->name.'</option>';
                }
                ?>
            </select>
        </label>

        <label class="left">De<br/>
        <input class="datepick" type="text" name="dataAssinaturaDe" size="30" value="<?php echo empty($commissionRule->dataAssinaturaDe)? date("Y-m-d", time()) : $commissionRule->dataAssinaturaDe; ?>" />
        </label>

        <label class="left">Até<br/>
        <input class="datepick" type="text" name="dataAssinaturaAte" size="30" value="<?php echo empty($commissionRule->dataAssinaturaAte)? date("Y-m-d", time()) : $commissionRule->dataAssinaturaAte; ?>" />
        </label>
        <div style="clear:both;">
            <br/>
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
