<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Calendar.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ConfigDAO.php");
include_once("../../DataTransferObjects/ConfigDTO.php");


$action = "";
if (isset($_REQUEST["action"]) && ($_REQUEST["action"] != "")) {
    $action = $_REQUEST["action"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["administracaoSistema"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$configDAO = new ConfigDAO($dataConnector->mysqlConnection);
$configDAO->showErrors = 1;

// Recupera as configurações armazenadas
$paramArray = $configDAO->RetrieveRecordArray();

if ($action == "store") {
    foreach ($paramArray as $configParam) {
        if (isset($_REQUEST["parametro".$configParam->id])) {
            $configParam->valor = $_REQUEST["parametro".$configParam->id];
            $configDAO->StoreRecord($configParam);
        }
    }
    echo 'Operação efetuada com sucesso!';
    exit;
}


function GetInputField($paramDTO) {
    $calendar = new Calendar();

    switch ($paramDTO->tipoParametro)
    {
        // case 1 INTEIRO - input simples, cai no caso default
        // case 2 STRING  - input simples, cai no caso default
        case 3: $properties = 'style="width:50%;"';     // DATETIME  - cria um datetime picker
                return  '<input '.$properties.' class="datepick" type="text" readonly="readonly" name="parametro'.$paramDTO->id.'" value="'.$paramDTO->valor.'" />';
        case 4: $properties = 'style="width:50%;"';     // BOOLEAN  - cria um combobox com opções sim e não
                $trueSelected = ($paramDTO->valor == 'true')  ? 'selected="selected"' : ''; $falseSelected = ($paramDTO->valor == 'false')  ? 'selected="selected"' : '';
                return '<select '.$properties.' name="parametro'.$paramDTO->id.'" ><option '.$trueSelected.' value="true" >Sim</option><option '.$falseSelected.' value="false" >Não</option></select>';
        case 5: $properties = 'style="width:50%;"';     // MÊS      - cria um combobox com os meses
                return '<select '.$properties.' name="parametro'.$paramDTO->id.'" >'.$calendar->GetMonthOptions($paramDTO->valor).'</select>';
        default: $properties = 'style="width:50%;"';
                 return '<input '.$properties.' type="text" name="parametro'.$paramDTO->id.'" value="'.$paramDTO->valor.'" />';
    }
}

?>

    <h1>Configurações do sistema</h1>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <script type="text/javascript" >
        $(document).ready(function() {
            // Seta o formato de data do datepicker para manter compatibilidade com o formato do SQL Server
            $('.datepick').datepicker({dateFormat: 'dd/mm/yy'});

            $("#btnSalvar").button({ icons: {primary:'ui-icon-circle-check'} }).click( function() {
                var targetUrl = 'Frontend/<?php echo $currentDir; ?>/gerenciar.php?action=store';
                $.ajax({ type: 'POST', url: targetUrl, data: $("form").serialize(), success: function(response) { alert(response); }, async: false });

                // Recarrega a página
                LoadPage('Frontend/<?php echo $currentDir; ?>/gerenciar.php');
            });
        });
    </script>

    <form name="frmConfig" action="Frontend/<?php echo $currentDir; ?>/gerenciar.php" method="post" >
        <div style="width: 650px;">
        <table cellpadding="0" cellspacing="0" style="border:1px solid black;" >
            <thead style="font-size: 15px; font-weight:bold; background:#DDD;" >
                <tr>
                    <th style="width:5%;"  >&nbsp;</th>
                    <th style="width:30%;" >&nbsp;Parâmetros</th>
                    <th style="width:65%;" >&nbsp;</th>
                </tr>
            </thead>
            <tbody>
            <?php
                if (sizeof($paramArray) == 0){
                    echo '<tr><td colspan="3" align="center" >Nenhum registro encontrado!</td></tr>';
                }

                foreach ($paramArray as $configParam) {
                    ?>
                    <tr>
                        <td>
                        </td>
                        <td>
                           <?php echo $configParam->descricao; ?>
                        </td>
                        <td>
                           <?php echo GetInputField($configParam); ?>
                        </td>
                    </tr>
                    <?php
                }
            ?>
            </tbody>
        </table>
        </div>
        <div style="clear:both;">
            <br/><br/>
        </div>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="button" <?php echo $attributes; ?> id="btnSalvar" >
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
