<?php

session_start();

include_once("../../check.php");

include_once("../../defines.php");
include_once("../../ClassLibrary/Text.php");
include_once("../../ClassLibrary/UnixTime.php");
include_once("../../ClassLibrary/DataConnector.php");
include_once("../../DataAccessObjects/ContractDAO.php");
include_once("../../DataTransferObjects/ContractDTO.php");
include_once("../../DataAccessObjects/BusinessPartnerDAO.php");
include_once("../../DataTransferObjects/BusinessPartnerDTO.php");


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

// Cria o objeto de mapeamento objeto-relacional
$contractDAO = new ContractDAO($dataConnector->mysqlConnection);
$contractDAO->showErrors = 1;


$id = 0;
$contract = new ContractDTO();

// cliente não precisa assinar o contrato na renovação, o contrato é renovado automaticamente por nossa empresa
$novaAssinatura = 0; 
$novoEncerramento = 0;
$novaPrimeiraParcela = 0;

if ( isset($_REQUEST["id"]) && ($_REQUEST["id"] != 0)) {
    $id = $_REQUEST["id"];
    $contract = $contractDAO->RetrieveRecord($id);

    // Busca os dados atuais do contrato
    $parcelaAtual = $contract->parcelaAtual;
    $quantidadeParcelas = $contract->quantidadeParcelas;
    $primeiraParcela = empty($contract->primeiraParcela)? time() : strtotime($contract->primeiraParcela);
    $dataEncerramento = empty($contract->dataEncerramento)? time() : strtotime($contract->dataEncerramento);

    // Define as novas datas do contrato
    $renovacaoContrato = new UnixTime($dataEncerramento);
    $parcelaInicial = new UnixTime($primeiraParcela);
    $novaAssinatura = $renovacaoContrato->value;
    $novoEncerramento = $renovacaoContrato->AddMonths($quantidadeParcelas);
    $novaPrimeiraParcela = $parcelaInicial->AddMonths($quantidadeParcelas);
}

$clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $contract->pn));

?>

    <h1>Renovação de contrato</h1><br/>
    <h1><?php echo str_pad('_', 52, '_', STR_PAD_LEFT); ?></h1>
    <div style="clear:both;">
        <br/><br/>
    </div>

    <form name="fDados" action="Frontend/<?php echo $currentDir; ?>/acao.php" method="post" >
        <input type="hidden" name="acao" value="renew" />
        <input type="hidden" name="id" value="<?php echo $id; ?>" />

        <label class="left">Número<br />
            <input type="text" readonly="readonly" name="numero" size="20" value="<?php echo $contract->numero; ?>" />
        </label>

        <label class="left">Parceiro de Negócios<br />
        <input type="text" name="cliente" size="65" readonly="readonly" value="<?php echo $clientName->Truncate(50); ?>" />
        </label>
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Data de Renovação<br/>
        <input type="text" name="dataRenovacao" size="30" readonly="readonly" value="<?php echo date("Y-m-d", $novaAssinatura); ?>" />
        </label>

        <label class="left">Data de Encerramento<br/>
        <input type="text" name="encerramento" size="30" readonly="readonly" value="<?php echo date("Y-m-d", $novoEncerramento); ?>" />
        </label >
        <div style="clear:both;">
            <br/>
        </div>

        <label class="left">Primeira Parcela<br/>
        <input class="datepick" type="text" name="primeiraParcela" size="30" value="<?php echo date("Y-m-d", $novaPrimeiraParcela); ?>" />
        </label>

        <label class="left">Parcela Atual<br/>
        <input type="text" name="parcelaAtual" size="10" value="0" />
        </label>

        <label class="left">Quant. Parcelas<br/>
        <input type="text" name="quantidadeParcelas" size="10" value="<?php echo $contract->quantidadeParcelas; ?>" />
        </label>   

        <label class="left">Fim do Atendimento<br/>
        <input class="datepick" type="text" name="fimAtendimento" size="30" value="<?php echo date("Y-m-d", $novoEncerramento); ?>" />
        </label>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <div style="max-width:650px;" >
        <span style="color:red; font-weight:bold;" >
            Observação: Posicionar a "parcela atual" em 0(zero) ao renovar, o sistema mudará para 1 quando enviar o primeiro faturamento.
        </span>
        </div>
        <div style="clear:both;">
            <br/><br/><br/>
        </div>

        <a href="Frontend/<?php echo $currentDir; ?>/editar.php?id=<?php echo $id; ?>" class="buttonVoltar" >
            Voltar
        </a>

        <?php
            $attributes = '';
            if ($nivelAutorizacao < 3) $attributes = 'disabled="disabled"';
        ?>
        <button type="submit" <?php echo $attributes; ?> class="button" id="btnform" >
            Salvar
        </button>
    </form>

<?php
// Fecha a conexão com o banco de dados
$dataConnector->CloseConnection();
?>
