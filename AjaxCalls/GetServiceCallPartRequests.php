<?php

session_start();

include_once("../check.php");

include_once("../defines.php");
include_once("../ClassLibrary/Text.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/PartRequestDAO.php");
include_once("../DataTransferObjects/PartRequestDTO.php");
include_once("../DataAccessObjects/RequestItemDAO.php");
include_once("../DataTransferObjects/RequestItemDTO.php");


$serviceCallId = $_GET['serviceCallId'];
if (empty($serviceCallId)) $serviceCallId = 0;


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

$nivelAutorizacao = GetAuthorizationLevel($dataConnector->mysqlConnection, $functionalities["gerenciamentoChamados"]);
if ($nivelAutorizacao <= 1) {
    DisplayNotAuthorizedWarning();
    exit;
}

// Cria os objetos de mapeamento objeto-relacional
$partRequestDAO = new PartRequestDAO($dataConnector->mysqlConnection);
$partRequestDAO->showErrors = 1;
$requestItemDAO = new RequestItemDAO($dataConnector->mysqlConnection);
$requestItemDAO->showErrors = 1;

// Busca as solicitações de peças para o chamado em questão
$partRequestArray = $partRequestDAO->RetrieveRecordArray("chamadoServico_id=".$serviceCallId);
if (sizeof($partRequestArray) == 0) {
    echo "<tr>";
    echo "    <td colspan='3' align='center' >Nenhum registro encontrado!</td>";
    echo "</tr>";
    exit;
}


foreach ($partRequestArray as $partRequest) {
    $requestItemArray = $requestItemDAO->RetrieveRecordArray("pedidoPecaReposicao_id=".$partRequest->id);
    $description = "";
    foreach ($requestItemArray as $requestItem) {
        if (!empty($description)) $description.= ' , ';
        $description.= $requestItem->quantidade.' '.$requestItem->nomeItem;
    }
    if (empty($description)) $description = "Nenhum item encontrado";
    ?>
    <tr>
        <td >
            <a rel="<?php echo $partRequest->id; ?>" class="openPartRequest" >
                <?php echo $partRequest->data; ?>
            </a>
        </td>
        <td >
            <a rel="<?php echo $partRequest->id; ?>" class="openPartRequest" >
                <?php $requestDescription = new Text($description); echo $requestDescription->Truncate(60); ?>
            </a>
        </td>
        <td>
            <a rel="<?php echo $partRequest->id; ?>" class="excluirSolicitacaoPecas" >
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

    function ExcluirSolicitacaoPecas(link) {
        if(!confirm("Confirma exclusão?")) return false;

        // Faz um chamada sincrona a página de exclusão
        var partRequestId = link.attr("rel");
        var targetUrl = 'Frontend/_pecaReposicao/acao.php?acao=remove&id=' + partRequestId;
        $.ajax({ type: 'POST', url: targetUrl, data: '', success: function(response) { alert(response); }, async: false });

        // Recarrega as solicitações de peças associadas ao chamado
        GetServiceCallPartRequests();
    }

    function OpenPartRequest(link) {
        var partRequestId = link.attr("rel");
        if (!partRequestId) partRequestId = 0;

        var targetUrl = 'Frontend/_pecaReposicao/visualizar.php?id=' + partRequestId;
        LoadPage(targetUrl);
    }

    <?php
    if ($nivelAutorizacao < 3)
        echo '$(".excluirSolicitacaoPecas").addClass("ui-state-disabled");';
    else
        echo '$(".excluirSolicitacaoPecas").click( function() { ExcluirSolicitacaoPecas($(this)); } );'
    ?>

    $(".openPartRequest").css('text-decoration', 'underline');
    $(".openPartRequest").click( function() { OpenPartRequest($(this)); } );

</script>
