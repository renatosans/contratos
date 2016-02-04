<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/CommissionPerVolumeDAO.php");
include_once("../../DataTransferObjects/CommissionPerVolumeDTO.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");


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

// Traz a lista de regras de comissão
$commissionRuleArray = $commissionPerVolumeDAO->RetrieveRecordArray();

?>
    <h1>Regras de Comissão (Por volume de contratos)</h1><br/>
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
                    <th>&nbsp;Categoria</th>
                    <th>&nbsp;Quantidade de Contratos</th>
                    <th>&nbsp;Valor dos faturamentos</th>
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
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?id='.$commissionRule->id; ?>" >
                                <?php echo ContractDAO::GetCategoryAsText($commissionRule->categoriaContrato); ?>
                            </a>
                        </td>
                        <td >
                            <?php
                                $quantContratosDe  = $commissionRule->quantContratosDe;
                                $quantContratosAte = $commissionRule->quantContratosAte;
                                echo $quantContratosDe.' até '.$quantContratosAte;
                            ?>
                        </td>
                        <td >
                            <?php
                                $valorFaturamentoDe  = number_format($commissionRule->valorFaturamentoDe , 2, ',', '.');
                                $valorFaturamentoAte = number_format($commissionRule->valorFaturamentoAte, 2, ',', '.');
                                echo $valorFaturamentoDe.' até '.$valorFaturamentoAte;
                            ?>
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
