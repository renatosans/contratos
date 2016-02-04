<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/EquipmentDAO.php");
    include_once("../DataTransferObjects/EquipmentDTO.php");


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Traz os equipamentos duplicados ( mesmo equipamento ativo em clientes diferentes )
    $duplicates = EquipmentDAO::GetDuplicates($dataConnector->sqlserverConnection);
    if ( sizeof($duplicates) == 0 ) {
        echo '<tr><td colspan="2" align="center">Nenhum registro encontrado!</td></tr>';
        exit;
    }
    foreach ($duplicates as $serial => $quantidade) {
        ?>
        <tr>
            <td>
                <a href="Frontend/equipamentos/editar.php?serial=<?php echo $serial; ?>" >
                    <?php echo $serial; ?>
                </a>
            </td>
            <td >
                <a href="Frontend/equipamentos/editar.php?serial=<?php echo $serial; ?>" >
                    <?php echo $quantidade; ?>
                </a>
            </td>
        </tr>
        <?php
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
