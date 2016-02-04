<html>
<body>
<?php

    $businessPartnerCode = $_GET['businessPartnerCode'];

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/BusinessPartnerDAO.php");
    include_once("../DataTransferObjects/BusinessPartnerDTO.php");
    include_once("../DataAccessObjects/IndustryDAO.php");
    include_once("../DataTransferObjects/IndustryDTO.php");
    include_once("../DataAccessObjects/ContactPersonDAO.php");
    include_once("../DataTransferObjects/ContactPersonDTO.php");


    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Cria o objeto de mapeamento objeto-relacional
    $businessPartnerDAO = new BusinessPartnerDAO($dataConnector->sqlserverConnection);
    $businessPartnerDAO->showErrors = 1;
    $industryDAO = new IndustryDAO($dataConnector->sqlserverConnection);
    $industryDAO->showErrors = 1;
    $contactPersonDAO = new ContactPersonDAO($dataConnector->sqlserverConnection);
    $contactPersonDAO->showErrors = 1;


    // Busca os dados do parceiro de negócios
    $businessPartner = $businessPartnerDAO->RetrieveRecord($businessPartnerCode);
    if ($businessPartner == null) {
        echo '<h3>Erro ao localizar parceiro de negócios.</h3>';
        exit;
    }
    $industryCode = $businessPartner->industry; if (empty($industryCode)) $industryCode = 0;

    // Busca os segmentos/ramos de atividade cadastrados no sistema
    $industryArray = array(0=>"");
    $tempArray = $industryDAO->RetrieveRecordArray();
    foreach ($tempArray as $industry) {
        $industryArray[$industry->id] = $industry->name;
    }

    // Busca os contatos cadastrados para o parceiro de negócios
    $contactPersonArray = $contactPersonDAO->RetrieveRecordArray("CardCode = '".$businessPartnerCode."'");
    $contactList = "";
    foreach($contactPersonArray as $contact) {
        if (!empty($contactList)) $contactList .= "<br/>";
        $additionalData = '';
        if (!empty($contact->phoneNumber)) $additionalData = ' ( RAMAL: '.$contact->phoneNumber.' )';
        if (!empty($contact->email) && empty($additionalData)) $additionalData = ' ( EMAIL: '.$contact->email.' )';
        $contactList .= $contact->name.$additionalData;
    }

    echo "<label style='width:99%;' >Código<br/>";
    echo "<input type='text' style='width:98%;' value='".$businessPartner->cardCode."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Razão Social<br/>";
    echo "<input type='text' style='width:98%;' value='".$businessPartner->cardName."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Nome Fantasia<br/>";
    echo "<input type='text' style='width:98%;' value='".$businessPartner->cardFName."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Segmento (Ramo de atividade)<br/>";
    echo "<input type='text' style='width:98%;' value='".$industryArray[$industryCode]."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Telefone<br/>";
    echo "<input type='text' style='width:98%;' value='".$businessPartner->telephoneNumber."' />";
    echo "</label>";
    echo "<div style='width:99%;' ><br/><h3>Contatos</h3>";
    echo "<div style='border:1px solid black; width:98%; min-height:80px;' >".$contactList."</div><br/>";
    echo "</div>";

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
</body>
</html>
