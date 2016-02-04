<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/ContractItemDAO.php");
include_once("../DataTransferObjects/ContractItemDTO.php");
include_once("../DataAccessObjects/EquipmentDAO.php");
include_once("../DataTransferObjects/EquipmentDTO.php");


$subContractId = 0;
if (isset($_REQUEST["subContractId"]) && ($_REQUEST["subContractId"] != 0)) {
    $subContractId = $_REQUEST["subContractId"];
}

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoContratos"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$contractItemDAO = new ContractItemDAO($dataConnector->mysqlConnection);
$contractItemDAO->showErrors = 1;
$equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
$equipmentDAO->showErrors = 1;

//localiza os itens do subcontrato
$itemArray = $contractItemDAO->RetrieveRecordArray("subcontrato_id=".$subContractId);

if (sizeof($itemArray) == 0){
    echo '<tr><td colspan="7" align="center" >Nenhum registro encontrado!</td></tr>';
}

foreach ($itemArray as $contractItem) {
    $shortDescription = '<span style="color: CadetBlue;">(Equipamento não encontrado)</span>';
    $sla = "";
    $comments = "";
    $equipment = $equipmentDAO->RetrieveRecord($contractItem->codigoCartaoEquipamento);
    if ($equipment != null) {
        $shortDescription = '<span style="color: CadetBlue;">'.EquipmentDAO::GetShortDescription($equipment).'</span>';
        if (!empty($equipment->sla)) $sla = $equipment->sla.' horas';
        if (!empty($equipment->comments)) $comments = $equipment->comments;
    }
    ?>
    <tr>
        <td >
            <a href="<?php echo 'Frontend/equipamentos/editar.php?equipmentCode='.$contractItem->codigoCartaoEquipamento.'&subContract='.$contractItem->codigoSubContrato; ?>" class="itemInfo" >
                <?php echo $shortDescription; ?>
            </a>
        </td>
        <td >
            <?php echo $comments; ?>
        </td>
        <td >
            <?php echo $sla; ?>
        </td>
        <td>
            <a href="<?php echo 'Frontend/_leitura/listar.php?equipmentCode='.$contractItem->codigoCartaoEquipamento.'&subContract='.$contractItem->codigoSubContrato; ?>" >
                <span class="ui-icon ui-icon-alert"></span>
            </a>
        </td>
        <td >
            <a href="<?php echo 'Frontend/chamados/listar.php?equipmentCode='.$contractItem->codigoCartaoEquipamento.'&subContract='.$contractItem->codigoSubContrato; ?>" >
                <span class="ui-icon ui-icon-alert"></span>
            </a>
        </td>
        <td>
            <a href="<?php echo 'Frontend/_consumivel/listar.php?equipmentCode='.$contractItem->codigoCartaoEquipamento.'&subContract='.$contractItem->codigoSubContrato; ?>" >
                <span class="ui-icon ui-icon-alert"></span>
            </a>
        </td>
        <td>
            <a rel="<?php echo $contractItem->codigoCartaoEquipamento; ?>" class="removeItem" >
                <span class="ui-icon ui-icon-closethick"></span>
            </a>
        </td>
    </tr>
    <?php
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>

<script type="text/javascript" >

    $(".itemInfo").css('text-decoration', 'underline');

    <?php
        if ($nivelAutorizacao < 3) 
            echo '$(".removeItem").addClass("ui-state-disabled");';
        else  
            echo '$(".removeItem").click( function() { RemoveSubContractItem($(this)); } );'
    ?>

</script>
