<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/SubContractDAO.php");
    include_once("../DataTransferObjects/SubContractDTO.php");
    include_once("../DataAccessObjects/ContractTypeDAO.php");
    include_once("../DataTransferObjects/ContractTypeDTO.php");
    include_once("../DataAccessObjects/ContractItemDAO.php");
    include_once("../DataTransferObjects/ContractItemDTO.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");


    $contractId = $_GET['contractId'];
    if (empty($contractId)) $contractId = 0;


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
    $subContractDAO->showErrors = 1;


    function GetContractType($mysqlConnection, $id) {
        $result = "";

        $contractTypeDAO = new ContractTypeDAO($mysqlConnection);
        $contractTypeDAO->showErrors = 1;
        $contractType = $contractTypeDAO->RetrieveRecord($id);
        if ($contractType != null) {
            $result = $contractType->sigla;
        }

        return $result;
    }

    function GetSerialNumbers($mysqlConnection, $sqlserverConnection, $subContractId) {
        $serialNumbers = "";
    
        $contractItemDAO = new ContractItemDAO($mysqlConnection);
        $contractItemDAO->showErrors = 1;
        $itemArray = $contractItemDAO->RetrieveRecordArray("subContrato_id = ".$subContractId);
        foreach ($itemArray as $contractItem) {
            if (!empty($serialNumbers)) $serialNumbers = $serialNumbers.", ";
            $serialNumbers = $serialNumbers.EquipmentDAO::GetSerialNumber($sqlserverConnection, $contractItem->codigoCartaoEquipamento);
        }

        return $serialNumbers;
    }


    // Busca os subitems cadastrados para o contrato em questão
    $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id=".$contractId);
    if (sizeof($subContractArray) == 0) {
        echo "<tr>";
        echo "    <td colspan='2' align='center' >Nenhum registro encontrado!</td>";
        echo "</tr>";
        exit;
    }

    foreach($subContractArray as $subContract) {
        $contractType = GetContractType($dataConnector->mysqlConnection, $subContract->codigoTipoContrato);
        $serialNumbers = GetSerialNumbers($dataConnector->mysqlConnection, $dataConnector->sqlserverConnection, $subContract->id);
        if (empty($serialNumbers)) $serialNumbers = 'Nenhum item encontrado';
        ?>
        <tr>
            <td>
                <a href="Frontend/contrato/sub-contrato/editar.php?id=<?php echo $subContract->id; ?>" >
                  <?php echo $contractType; ?>
                </a>
            </td>
            <td>
                <a href="Frontend/contrato/sub-contrato/editar.php?id=<?php echo $subContract->id; ?>" >
                  <?php echo $serialNumbers; ?>
                </a>
            </td>
        </tr>
        <?php
    }

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
