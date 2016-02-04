<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/IndirectCostDAO.php");
include_once("../../DataTransferObjects/IndirectCostDTO.php");
include_once("../../DataAccessObjects/ProductionInputDAO.php");
include_once("../../DataTransferObjects/ProductionInputDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$indirectCostDAO = new IndirectCostDAO($dataConnector->mysqlConnection);
$indirectCostDAO->showErrors = 1;
$productionInputDAO = new ProductionInputDAO($dataConnector->mysqlConnection);
$productionInputDAO->showErrors = 1;

// Traz os custos indiretos cadastrados
$indirectCostArray = $indirectCostDAO->RetrieveRecordArray();

?>
    <h1>Administração - Custos Indiretos</h1>
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
                    <input name="filter" id="filter-box" value="" maxlength="65" size="65" type="text"/>
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
                    <th>&nbsp;Data</th>
                    <th>&nbsp;Insumo</th>
                    <th>&nbsp;Medição Inicial</th>
                    <th>&nbsp;Medição Final</th>
                    <th>&nbsp;Total(R$)</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($indirectCostArray) == 0){
                    echo '<tr><td colspan="6" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($indirectCostArray as $indirectCost) {
                    ?>
                    <tr>
                        <td align="center" >
                            <input type="checkbox" value= "<?php echo $indirectCost->id; ?>" name="reg[]" />
                        </td>
                        <td >
                           <?php echo $indirectCost->data; ?>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$indirectCost->id; ?>" >
                            <?php
                                $productionInput = $productionInputDAO->RetrieveRecord($indirectCost->codigoInsumo);
                                echo $productionInput->descricao;
                            ?>
                            </a>
                        </td>
                        <td >
                           <?php echo $indirectCost->medicaoInicial; ?>
                        </td>
                        <td >
                           <?php echo $indirectCost->medicaoFinal; ?>
                        </td>
                        <td >
                           <?php echo number_format($indirectCost->total, 2, ',', '.'); ?>
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
