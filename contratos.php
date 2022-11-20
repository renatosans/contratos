<?php

session_start();

include_once("check.php");
include_once("defines.php");
include_once("ClassLibrary/Text.php");
include_once("ClassLibrary/DataConnector.php");
include_once("DataAccessObjects/ContractDAO.php");
include_once("DataTransferObjects/ContractDTO.php");
include_once("DataAccessObjects/SubContractDAO.php");
include_once("DataTransferObjects/SubContractDTO.php");
include_once("DataAccessObjects/BusinessPartnerDAO.php");
include_once("DataTransferObjects/BusinessPartnerDTO.php");


$slpCode = $_REQUEST["slpCode"];

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
$subContractDAO = new SubContractDAO($dataConnector->mysqlConnection);
$subContractDAO->showErrors = 1;

// Busca os contratos pertencentes ao vendedor
$contractArray = $contractDAO->RetrieveRecordArray("vendedor=".$slpCode." AND id > 0 ORDER BY convert(numero, signed)");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="pt-br" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" >
<title><?php echo $appTitle; ?></title>

<link href="<?php echo $pathCss ?>/jquery-ui.css"  rel="stylesheet" type="text/css" />
<link href="<?php echo $pathCss ?>/ui.timepickr.css"  rel="stylesheet" type="text/css" />
<link href="<?php echo $pathCss ?>/jquery.multiselect.css"  rel="stylesheet" type="text/css" />
<link href="<?php echo $pathCss ?>/admin.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.min.js" ></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.form.js" ></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.check.js" ></script>
<script src="<?php echo $pathJs?>/jquery.blockUI.js" type="text/javascript"></script>
<script src="<?php echo $pathJs?>/jquery.validate.js" type="text/javascript"></script>

<script type="text/javascript" src="<?php echo $pathJs ?>/jquery-ui-timepicker.js" ></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery-ui-datepicker-pt-br.js" ></script>

<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.enhaceTable.js"></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.tableSorter.js"></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.tableSorter.filter.js"></script>
<script type="text/javascript" src="<?php echo $pathJs ?>/jquery.tableSorter.pager.js"></script>

<script type="text/javascript" src="<?php echo $pathJs ?>/admin.js"></script>
</head>
<body>
<input type="hidden" name="startPage" value="Frontend/contrato/listar.php" />

<script type="text/javascript" >
    $(document).ready(function() {
        $(".contractIcon").click(function() {
            var contractId = $(this).attr("rel");
            LoadPage('Frontend/contrato/editar.php?id=' + contractId);
        });
    });
</script>

<?php
    function GetContracts(){
        foreach ($contractArray as $contract) {
            $contractNum = str_pad($contract->numero, 5, '0', STR_PAD_LEFT);
            $clientName = new Text(BusinessPartnerDAO::GetClientName($dataConnector->sqlserverConnection, $contract->pn));
            $contractStatus = ContractDAO::GetStatusAsText($contract->status); 
            $subContractArray = $subContractDAO->RetrieveRecordArray("contrato_id=".$contract->id);
            $typeEnumeration = "";
            foreach ($subContractArray as $subContract) {
                if (!empty($typeEnumeration)) $typeEnumeration.= ', ';
                $typeEnumeration.= $subContract->siglaTipoContrato;
            }
            $contractTypes = new Text($typeEnumeration); 
        
            $tags = $contractNum.' '.$clientName->Truncate(50).' '.$contractTypes->Truncate(20).' '.$contractStatus;
            echo '<a style="float:left; text-align:center;" class="contractIcon" rel="'.$contract->id.'" rev="'.$tags.'" >';
            echo '<img src="'.$pathImg. '/document.png" alt="" style="width:50px; height:50px;" /><br/>';
            echo $contractNum;
            echo '</a>';
        }    
    }
?>

<div id="centro">
    <div id="cabecalho">
        <img src="<?php echo $pathImg; ?>/logo.png" />
    </div>

    <div id="conteudo" style="display: flex; flex-direction: row;">
        <div id="menu">
            <br/>
            <?php
            foreach($restrictedMenu as $menuItem=>$url){
                echo '<a href="'.$url.'" >'.$menuItem.'</a>';
            }
            ?>
            <br/>
            <a href="<?php echo $root.'/logout.php'; ?>" >
                Sair
            </a>
        </div>

        <div id="lista" class="corner ui-corner-all" >
            <img src="<?php echo $pathImg; ?>/loading.gif" />
            <?php GetContracts() ?>
        </div>
    </div>
</div>

</body>
</html>
