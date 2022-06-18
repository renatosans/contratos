<?php

session_start();

include_once("../../defines.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ServiceCallDAO.php");
include_once("../../DataTransferObjects/ServiceCallDTO.php");
include_once("../../DataAccessObjects/EquipmentDAO.php");
include_once("../../DataTransferObjects/EquipmentDTO.php");
include_once("../../DataAccessObjects/EquipmentModelDAO.php");
include_once("../../DataTransferObjects/EquipmentModelDTO.php");
include_once("../../DataAccessObjects/ManufacturerDAO.php");
include_once("../../DataTransferObjects/ManufacturerDTO.php");
include_once("../../DataAccessObjects/ActionLogDAO.php");
include_once("../../DataTransferObjects/ActionLogDTO.php");
include_once("../../DataAccessObjects/ReadingDAO.php");
include_once("../../DataTransferObjects/ReadingDTO.php");
include_once("../../DataAccessObjects/ExpenseDAO.php");
include_once("../../DataTransferObjects/ExpenseDTO.php");
include_once("../../DataAccessObjects/PartRequestDAO.php");
include_once("../../DataTransferObjects/PartRequestDTO.php");


if ( !isset($_REQUEST["acao"]) ) {
    echo "Erro no processamento da requisição.";
    exit;
}
$acao = $_REQUEST["acao"];


if ( !isset($_REQUEST["cartaoEquipamento"]) && ($acao == "store")) {
    echo "Favor informar o equipamento a que se refere o chamado.";
    exit;
}


// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('both');
$dataConnector->OpenConnection();
if (($dataConnector->mysqlConnection == null) || ($dataConnector->sqlserverConnection == null)) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;  
}

// Cria os objetos de mapeamento objeto-relacional
$serviceCallDAO = new ServiceCallDAO($dataConnector->mysqlConnection);
$serviceCallDAO->showErrors = 1;
$equipmentDAO = new EquipmentDAO($dataConnector->sqlserverConnection);
$equipmentDAO->showErrors = 1;
$equipmentModelDAO = new EquipmentModelDAO($dataConnector->mysqlConnection);
$equipmentModelDAO->showErrors = 1;


if( $acao == "store" ) {
    $id = 0;
    $serviceCall = new ServiceCallDTO();
    if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
        $id = $_REQUEST["id"];
        $serviceCall = $serviceCallDAO->RetrieveRecord($id);
    }

    $equipment = $equipmentDAO->RetrieveRecord($_REQUEST["cartaoEquipamento"]);
    $equipmentModel = $equipmentModelDAO->RetrieveRecord($equipment->model);
    if (!isset($equipmentModel)) {
        echo 'O equipamento '.$equipment->manufacturerSN.' possui um erro de cadastro.  Favor corrigir o modelo do equipamento.';
        exit;
    }
    $modelName = $equipmentModel->modelo;
    $manufacturerName = ManufacturerDAO::GetManufacturerName($dataConnector->mysqlConnection, $equipmentModel->fabricante);

    $serviceCall->defeito                 = $_REQUEST["defeito"];
    $serviceCall->dataAbertura            = $_REQUEST["dataAbertura"];
    $serviceCall->horaAbertura            = $_REQUEST["horaAbertura"];
    $serviceCall->dataFechamento          = $_REQUEST["dataFechamento"];
    $serviceCall->horaFechamento          = $_REQUEST["horaFechamento"];
    $serviceCall->dataAtendimento         = $_REQUEST["dataAtendimento"];
    $serviceCall->horaAtendimento         = $_REQUEST["horaAtendimento"];
    $serviceCall->tempoAtendimento        = $_REQUEST["tempoAtendimento"];
    $serviceCall->businessPartnerCode     = $_REQUEST["businessPartnerCode"];
    $serviceCall->contato                 = $_REQUEST["contato"];
    $serviceCall->status                  = $_REQUEST["status"];
    $serviceCall->tipo                    = $_REQUEST["tipo"];
    $serviceCall->abertoPor               = $_REQUEST["abertoPor"];
    $serviceCall->tecnico                 = $_REQUEST["tecnico"];
    $serviceCall->prioridade              = $_REQUEST["prioridade"];
    $serviceCall->codigoCartaoEquipamento = $_REQUEST["cartaoEquipamento"];
    $serviceCall->modelo                  = $modelName;
    $serviceCall->fabricante              = $manufacturerName;
    $serviceCall->observacaoTecnica       = $_REQUEST["observacaoTecnica"];
    $serviceCall->sintoma                 = $_REQUEST["sintoma"];
    $serviceCall->causa                   = $_REQUEST["causa"];
    $serviceCall->acao                    = $_REQUEST["acao_reparo"];

    if ((!empty($id)) && (!empty($serviceCall->dataAtendimento))) { // Se a data de atendimento foi informada
        // Verifica se é maior que a data de abertura
        if (strtotime($serviceCall->dataAtendimento) < strtotime($serviceCall->dataAbertura)){
            echo "Não foi possivel efetuar a operação.  Erro na data do atendimento";
            exit;
        }
    }

    $recordId = $serviceCallDAO->StoreRecord($serviceCall);
    if ($recordId == null) {
        echo "Não foi possivel efetuar a operação...";
        exit;
    }

    if(($id == 0) && isset($recordId)) { // Se inseriu um novo registro no banco
        // Grava no histórico a ação
        $actionLog = new ActionLogDTO();
        $actionLog->transacao = 'chamado número '.str_pad($recordId, 5, '0', STR_PAD_LEFT).' criado';
        $actionLog->tipoObjeto = "trace";

        $actionLogDAO = new ActionLogDAO($dataConnector->mysqlConnection);
        $actionLogDAO->showErrors = 1;
        $actionLogDAO->StoreRecord($actionLog);
    }

    echo "Operação efetuada com sucesso!";
}

if ( $acao == "remove" ) {
    if(!isset($_POST['reg'])){
        echo "Selecione os registros que deseja excluir";
        exit;
    }

    foreach($_POST['reg'] as $key=>$reg){
        // Verifica as dependências do chamado
        $serviceCallId = str_pad($reg, 5, '0', STR_PAD_LEFT);

        $readingDAO = new ReadingDAO($dataConnector->mysqlConnection);
        $readingDAO->showErrors = 1;
        $readingArray = $readingDAO->RetrieveRecordArray("chamadoServico_id=".$reg);
        if (sizeof($readingArray) > 0) {
            echo "O chamado ".$serviceCallId." não pode ser excluído pois está amarrado à leituras de contador. Exclua essas dependências primeiro.";
            exit;
        }

        $expenseDAO = new ExpenseDAO($dataConnector->mysqlConnection);
        $expenseDAO->showErrors = 1;
        $expenseArray = $expenseDAO->RetrieveRecordArray("codigoChamado = ".$reg);
        if (sizeof($expenseArray) > 0) {
            echo "O chamado ".$serviceCallId." não pode ser excluído pois está amarrado à despesas de chamado. Exclua essas dependências primeiro.";
            exit;
        }

        $partRequestDAO = new PartRequestDAO($dataConnector->mysqlConnection);
        $partRequestDAO->showErrors = 1;
        $partRequestArray = $partRequestDAO->RetrieveRecordArray("chamadoServico_id=".$reg);
        if(sizeof($partRequestArray) > 0) {
            echo "O chamado ".$serviceCallId." não pode ser excluído pois está amarrado à solicitações de peças. Exclua essas dependências primeiro.";
            exit;
        }

        if( !$serviceCallDAO->DeleteRecord($reg) ){
            echo "Não foi possivel efetuar a operação...";
            exit;
        }
    }

    echo "Operação efetuada com sucesso!";
}

// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();

?>
