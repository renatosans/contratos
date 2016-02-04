<?php

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
    include_once("../DataAccessObjects/ContactPersonDAO.php");
    include_once("../DataTransferObjects/ContactPersonDTO.php");


    $businessPartnerCode = $_GET['businessPartnerCode'];
    $contactPersonCode = $_GET['contactPersonCode'];

    // Abre a conexao com o banco de dados
    $dataConnector = new DataConnector('sqlServer');
    $dataConnector->OpenConnection();
    if ($dataConnector->sqlserverConnection == null) {
        echo 'Não foi possível se connectar ao bando de dados!';
        exit;
    }

    // Busca os contatos cadastrados para o parceiro de negócios
    $contactPersonDAO = new ContactPersonDAO($dataConnector->sqlserverConnection);
    $contactPersonDAO->showErrors = 1;
    $contactPersonArray = $contactPersonDAO->RetrieveRecordArray("CardCode = '".$businessPartnerCode."'");
    foreach($contactPersonArray as $contact) {
        $attributes = "";
        if ($contact->cntctCode == $contactPersonCode) $attributes = "selected='selected'";
        echo "<option ".$attributes." value=".$contact->cntctCode.">".$contact->name."</option>";
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

?>
