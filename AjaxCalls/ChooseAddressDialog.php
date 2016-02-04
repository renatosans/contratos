<?php

include_once("../defines.php");
include_once("../ClassLibrary/DataConnector.php");
include_once("../DataAccessObjects/PartnerAddressDAO.php");
include_once("../DataTransferObjects/PartnerAddressDTO.php");


$businessPartnerCode = $_GET['businessPartnerCode'];

// Abre a conexao com o banco de dados
$dataConnector = new DataConnector('sqlServer');
$dataConnector->OpenConnection();
if ($dataConnector->sqlserverConnection == null) {
    echo 'Não foi possível se connectar ao bando de dados!';
    exit;
}

// Cria o objeto de mapeamento objeto-relacional
$partnerAddressDAO = new PartnerAddressDAO($dataConnector->sqlserverConnection);
$partnerAddressDAO->showErrors = 1;


// Busca os endereços do parceiro de negócios
$addressArray = $partnerAddressDAO->RetrieveRecordArray("CardCode = '".$businessPartnerCode."'");

?>

<label class="left" style="width:99%; text-align: left;">Endereço<br/>
    <select name="cmbAddress" style="width:100%;" >
    <?php
        foreach($addressArray as $address)
        {
            $fullAddress = $address->addressLabel."   Endereço: ".$address->addrType." ".$address->street." ".$address->streetNo." ".$address->building."   ";
            $fullAddress .= "CEP: ".$address->zipCode."   "."Bairro: ".$address->block."   ".$address->city." ".$address->state." ".$address->country;
            if (!empty($address->locationRef)) $fullAddress .= "Secretaria: ".$address->locationRef."   ";
        
            echo '<option value="'.$address->addressLabel.'" rel="'.$fullAddress.'" >'.$address->addressLabel.'</option>';
        }        
    ?>
    </select>
</label>
<div style="clear:both;">
    <br/>
</div>

<div class="left" style="width:99%; text-align: center;">
    <input id="btnOK" type="button" value="OK" style="width:50px; height:30px;"></input>
</div>

<?php
    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();
?>

<script type="text/javascript" >
    $("#btnOK").click(function() { OkButtonClicked(); });

    function OkButtonClicked() {
        var fullAddress = $("select[name=cmbAddress] option:selected").attr("rel");
        var comments = $("textarea[name=comments]").val();
        $("textarea[name=comments]").val(comments + ' ' + fullAddress);

        // Fecha o dialogo
        $("#popup").dialog('close');
    }
</script>
