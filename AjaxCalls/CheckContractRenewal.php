<?php
    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/ContractDAO.php");
    include_once("../DataTransferObjects/ContractDTO.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('both');
    $dataConnector->OpenConnection();
    if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria os objetos de mapeamento objeto-relacional
    $contractDAO = new ContractDAO($dataConnector->mysqlConnection);
    $contractDAO->showErrors = 1;
    $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
    $businessPartnerDAO->showErrors = 1;


    // Traz os contratos que estão na última parcela
    $contractArray = $contractDAO->RetrieveRecordArray("parcelaAtual=quantidadeParcelas AND status <> 3 AND status <> 4  AND categoria < 5");
    if ( sizeof($contractArray) == 0 ) {
        echo '<tr><td colspan="2" align="center">Nenhum registro encontrado!</td></tr>';
        exit;
    }
    foreach ($contractArray as $contract) {
        $businessPartner = $businessPartnerDAO->RetrieveRecord($contract->pn);
        ?>
        <tr>
            <td>
                <a href="Frontend/contrato/editar.php?id=<?php echo $contract->id; ?>" >
                    <?php echo str_pad($contract->numero, 5, '0', STR_PAD_LEFT); ?>
                </a>
            </td>
            <td >
                <a href="Frontend/contrato/editar.php?id=<?php echo $contract->id; ?>" >
                    <?php echo $businessPartner->cardName; ?>
                </a>
            </td>
        </tr>
        <?php
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
