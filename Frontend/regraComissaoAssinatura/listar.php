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

// Traz a lista de regras de comissão
$commissionRuleArray = $commissionPerSignatureDAO->RetrieveRecordArray();


// Busca os segmentos/ramos de atividade cadastrados no sistema
$industryArray = array(0=>"Todos");
$tempArray = $industryDAO->RetrieveRecordArray();
foreach ($tempArray as $industry) {
    $industryArray[$industry->id] = $industry->name;
}

?>
    <h1>Regras de Comissão (Por assinatura dos contratos)</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <form id="fLista" name="fLista" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post">
        <div class="clear">
            <fieldset>
                <legend>Ações:</legend>
                <a href="#" id="checkall" class="button" >
                    Todos
                </a>
                <a href="#" id="uncheckall" class="button">
                    Nenhum
                </a>
                <a href="Frontend/<?php echo $currentDir; ?>/editar.php" class="button">
                    Novo
                </a>
                <button type="submit" id="btnExcluir" class="button">
                    Excluir
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="45" size="45" type="text"/>
                    <button id="filter-clear-button" type="submit" value="Clear">Clear</button>
                </fieldset>
            </div>
        </div>
        <br/>
        <input type="hidden" name="acao" value="remove" />
        <table border="0" cellpadding="0" cellspacing="0" class="sorTable">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;Segmento</th>
                    <th>&nbsp;De</th>
                    <th>&nbsp;Até</th>
                    <th>&nbsp;Comissão</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($commissionRuleArray) == 0){
                    echo '<tr><td colspan="5" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($commissionRuleArray as $commissionRule) {
            ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value= "<?php echo $commissionRule->id; ?>" name="reg[]"/>
                        </td>
                        <td>
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$commissionRule->id; ?>" >
                                <?php echo $industryArray[$commissionRule->segmento]; ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$commissionRule->id; ?>" >
                                <?php echo $commissionRule->dataAssinaturaDe; ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$commissionRule->id; ?>" >
                                <?php echo $commissionRule->dataAssinaturaAte; ?>
                            </a>
                        </td>
                        <td >
                            <?php echo $commissionRule->comissao.'%'; ?>
                        </td>
                    </tr>
            <?php
                }
            ?>
            </tbody>
            </table>
            <div class="pager pagerListar">
                    <span class="wraper">
                        <button class="first">First</button>
                    </span>
                    <span class="wraper">
                        <button class="prev">Prev</button>
                    </span>
                    <span class="wraper center">
                        <input type="text" class="pagedisplay"/>
                    </span>
                    <span class="wraper">
                        <button class="next">Next</button>
                    </span>
                    <span class="wraper">
                        <button class="last">Last</button>
                    </span>
                <input type="hidden" class="pagesize" value="10" />
            </div>
    </form>
<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
