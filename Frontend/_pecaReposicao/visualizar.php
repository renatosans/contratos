<?php

    session_start();
    
    include_once("../../check.php");
    
    include_once("../../defines.php");
    include_once("../../ClassLibrary/DataConnector.php");
    include_once("../../DataAccessObjects/PartRequestDAO.php");
    include_once("../../DataTransferObjects/PartRequestDTO.php");
    include_once("../../DataAccessObjects/RequestItemDAO.php");
    include_once("../../DataTransferObjects/RequestItemDTO.php");


    $partRequestId = $_GET['id'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('mySql');
    $dataConnector->OpenConnection();
    if ($dataConnector->mysqlConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    $nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoEquipmtPecas"]);
    if ($nivelAutorizacao <= 1) {
        DisplayNotAuthorizedWarning();
        exit;
    }

    // Recupera os dados da solicitação
    $partRequestDAO = new PartRequestDAO($dataConnector->mysqlConnection);
    $partRequestDAO->showErrors = 1;
    $partRequest = $partRequestDAO->RetrieveRecord($partRequestId);
    if ($partRequest == null) {
        echo '<br/><h1>Solicitação não encontrada</h1><br/>';
        exit;
    }

    // Recupera os itens da solicitação
    $itens = "";
    $requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
    $requestItemDAO->showErrors = 1;
    $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoPecaReposicao_id=".$partRequestId);
    foreach ($requestItemArray as $requestItem) {
        if (!empty($itens)) $itens = $itens."<br/>";
        $itens = $itens.$requestItem->quantidade.' '.$requestItem->nomeItem;
    }

?>

    <br/><h1>SOLICITAÇÃO DE PEÇAS</h1><br/>
    <h1><?php echo str_pad('_', 60, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <b>Data da Soliticação: <?php echo $partRequest->data; ?></b>
    <br/>
    <b>Chamado de Serviço: <?php echo str_pad($partRequest->codigoChamadoServico, 5, '0', STR_PAD_LEFT); ?></b>
    <br/>
    <b>Itens</b>
    <div style="border:1px solid black; min-height:100px;" >
        <?php echo $itens; ?>
    </div>
    <div style="clear:both;">
        <br/><br/><br/>
    </div>

    <a href="<?php echo $_SESSION["lastPage"]; ?>" class="buttonVoltar" >
        Voltar
    </a>

<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>
