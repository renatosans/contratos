<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/AdjustmentDAO.php");
include_once("../DataTransferObjects/AdjustmentDTO.php");

$contractId = $_GET['contractId'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$adjustmentDAO = new AdjustmentDAO($dataConnector->mysqlConnection);
$adjustmentDAO->showErrors = 1;

// Busca os reajustes do contrato
$adjustmentArray = $adjustmentDAO->RetrieveRecordArray("contrato_id=".$contractId);
if (sizeof($adjustmentArray) == 0) {
    echo "<tr>";
    echo "    <td colspan='3' align='center' >Nenhum registro encontrado!</td>";
    echo "</tr>";
    exit;
}

foreach($adjustmentArray as $adjustment) {
    ?>
    <tr>
        <td>
            <?php echo $adjustment->data; ?>
        </td>
        <td>
            <?php echo $adjustment->indiceUtilizado; ?>
        </td>
        <td>
            <?php echo $adjustment->aliquotaUtilizada.'%'; ?>
        </td>
    </tr>
    <?php
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
