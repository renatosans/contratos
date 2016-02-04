<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ReadingDAO.php");
include_once("../../DataTransferObjects/ReadingDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('mySql');
$dataConnector->OpenConnection();
if ($dataConnector->mysqlConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria o objeto de mapeamento objeto-relacional
$readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
$readingDAO->showErrors = 1;


if( $acao == "store" ) {
    if (!isset($_REQUEST["contagem"]) || empty($_REQUEST["contagem"])) {
        echo "Erro de preenchimento. É necessário preencher a leitura do contador!";
        exit;
    }

    $id = 0;
    $reading = new ReadingDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0) ) {
        $id = $_REQUEST["id"];
        $reading = $readingDAO->RetrieveRecord($id); 
    }
    if (isset($_REQUEST["equipmentCode"]))
        $reading->codigoCartaoEquipamento = $_REQUEST["equipmentCode"];
    if (isset($_REQUEST["chamadoServico_id"]))
        $reading->codigoChamadoServico = $_REQUEST["chamadoServico_id"];
    if (isset($_REQUEST["consumivel_id"]))
        $reading->codigoConsumivel = $_REQUEST["consumivel_id"];
    $reading->data                     = $_REQUEST["data"];
    $reading->hora                     = $_REQUEST["hora"];
    $reading->codigoContador           = $_REQUEST["contador_id"];
    $reading->contagem                 = $_REQUEST["contagem"];
    $reading->ajusteContagem           = $_REQUEST["ajusteContagem"];
    $reading->assinaturaDatacopy       = $_REQUEST["assinaturaDatacopy"];
    $reading->assinaturaCliente        = $_REQUEST["assinaturaCliente"];
    $reading->observacao               = $_REQUEST["obs"];
    $reading->origemLeitura            = $_REQUEST["origemLeitura_id"];
    $reading->formaLeitura             = $_REQUEST["formaLeitura_id"];
    $reading->reset = 0; if (isset($_REQUEST["reset"])) $reading->reset = 1;

    // Verifica se tem uma leitura prévia para este contador
    if ( ($reading->codigoChamadoServico != 0) || ($reading->codigoConsumivel != 0) ) {
        $firstCondition = 'chamadoServico_id IS NULL';  if ($reading->codigoChamadoServico != 0) $firstCondition = 'chamadoServico_id='.$reading->codigoChamadoServico;
        $secondCondition = 'consumivel_id IS NULL';  if ($reading->codigoConsumivel != 0) $secondCondition = 'consumivel_id='.$reading->codigoConsumivel;
        $filter = $firstCondition.' AND '.$secondCondition.' AND contador_id='.$reading->codigoContador;
        $previousReading = $readingDAO->RetrieveRecordArray($filter);
        if (sizeof($previousReading) > 0) {
            echo 'Já existe uma leitura para este contador!';
            exit;
        }
    }

    $recordId = $readingDAO->StoreRecord($reading);
    if($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if(!isset($_POST['reg'])){
        echo "Selecione os registros que deseja excluir";
        exit;        
    }

    foreach($_POST['reg'] as $key=>$reg){
        if (!$readingDAO->DeleteRecord($reg)){
            echo "Não foi possivel efetuar a operação...";
            exit;            
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
