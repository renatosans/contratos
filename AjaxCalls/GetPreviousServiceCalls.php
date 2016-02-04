<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/ServiceCallDAO.php");
    include_once("../DataTransferObjects/ServiceCallDTO.php");


    $equipmentCode = $_GET['equipmentCode'];
    $cutoffDate    = $_GET['cutoffDate'];


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('mySql');
    $dataConnector->OpenConnection();
    if ($dataConnector->mysqlConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Traz os três últimos chamados para o cartão de equipamento em questão partindo de dataDeCorte (chamados anteriores a dataDeCorte)
    $serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
    $serviceCallDAO->showErrors = 1;
    $filter = "cartaoEquipamento = ".$equipmentCode." AND status <> 1 AND dataAbertura < '".$cutoffDate."' ORDER BY dataAbertura DESC, id DESC LIMIT 0, 5";
    $serviceCallArray = $serviceCallDAO->RetrieveRecordArray($filter);
    if (sizeof($serviceCallArray) == 0){
        echo '<tr><td colspan="2" align="center" >Nenhum registro encontrado!</td></tr>';
    }
    foreach ($serviceCallArray as $serviceCall) {
    ?>
        <tr>
            <td onclick="javascript: BuscarDadosChamadoAnterior('<?php echo $serviceCall->id; ?>');" ><?php echo str_pad($serviceCall->id, 5, '0', STR_PAD_LEFT); ?></td>
            <td onclick="javascript: BuscarDadosChamadoAnterior('<?php echo $serviceCall->id; ?>');" ><?php echo $serviceCall->defeito; ?></td>
            <td ><?php echo $serviceCall->dataAbertura; ?></td>
            <td><a href="Frontend/chamados/editar.php?id=<?php echo $serviceCall->id; ?>" ><span class="ui-icon ui-icon-alert"></span></a></td>
        </tr>
    <?php
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
