<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/SalesPersonDAO.php");
include_once("../../DataTransferObjects/SalesPersonDTO.php");


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('sqlServer');
$dataConnector->OpenConnection();
if ($dataConnector->sqlserverConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$salesPersonDAO = new SalesPersonDAO($dataConnector->sqlserverConnection);
$salesPersonDAO->showErrors = 1;

// Traz a lista de vendedores cadastrados
$salesPersonArray = $salesPersonDAO->RetrieveRecordArray("SlpCode > 0");

?>
    <h1>Administração - Vendedores</h1>
    <h1><?php echo str_pad('_', 50, '_', STR_PAD_LEFT); ?></h1>
    <br/>

    <script type="text/javascript" >
        $(document).ready(function() {
            $("#btnCommissionReport").button({ icons: {primary:'ui-icon-print'} }).click( function() {
                var reportUrl = 'Frontend/vendedores/comissaoVendedoresExcel.php';
                var targetUrl = 'AjaxCalls/DateFilterDialog.php';
                $("form[name=fLista]").append("<div id='popup'></div>");
                $("#popup").load(targetUrl, {reportUrl:reportUrl, parameters:''}).dialog({modal:true, width: 210, height: 240, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });
        });
    </script>

    <form id="fLista" name="fLista" action="#" method="post" >
        <div class="clear">
            <fieldset>
                <legend>Ações:</legend>
                <button type="button" id="btnCommissionReport" >
                    Relatório de Comissão
                </button>
            </fieldset>

            <div class="filterOne">
                <fieldset>
                    <legend>Buscar:</legend>
                    <input name="filter" id="filter-box" value="" maxlength="70" size="70" type="text"/>
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
                    <th>&nbsp;Código</th>
                    <th>&nbsp;Nome</th>
                    <th>&nbsp;Comissão</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($salesPersonArray) == 0){
                    echo '<tr><td colspan="4" align="center" >Nenhum registro encontrado!</td></tr>';
                }
                foreach ($salesPersonArray as $salesPerson) {
                    $slpCode = $salesPerson->slpCode;
            ?>
                    <tr>
                        <td >
                            &nbsp;
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?slpCode='.$slpCode; ?>" >
                                <?php echo $salesPerson->slpCode; ?>
                            </a>
                        </td>
                        <td >
                            <a href="<?php echo 'Frontend/'.$currentDir.'/editar.php?slpCode='.$slpCode; ?>" >
                                <?php echo $salesPerson->slpName; ?>
                            </a>
                        </td>
                        <td >
                           <?php echo $salesPerson->commission; ?>
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
