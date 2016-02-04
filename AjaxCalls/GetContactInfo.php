<html>
<body>
<?php

    $contactCode = $_GET['contactCode'];

    include_once("../defines.php");
    include_once("../ClassLibrary/DataConnector.php");
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
    $contactPersonDAO = new ContactPersonDAO($dataConnector->sqlserverConnection);
    $contactPersonDAO->showErrors = 1;

    $name         = "";
    $phoneNumber  = "";
    $cellNumber   = "";
    $email        = "";
    $dto = $contactPersonDAO->RetrieveRecord($contactCode);
    if (!empty($dto)) {
        $name         = $dto->name;
        $phoneNumber  = $dto->phoneNumber;
        $cellNumber   = $dto->cellNumber;
        $email        = $dto->email;
    }

    // Fecha a conexão com o banco de dados
    $dataConnector->CloseConnection();

    echo "<label style='width:99%;' >Nome<br/>";
    echo "<input type='text' style='width:98%;' value='".$name."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Telefone<br/>";
    echo "<input type='text' style='width:98%;' value='".$phoneNumber."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Celular<br/>";
    echo "<input type='text' style='width:98%;' value='".$cellNumber."' />";
    echo "</label>";
    echo "<label style='width:99%;' >Email<br/>";
    echo "<input type='text' style='width:98%;' value='".$email."' />";
    echo "</label>";
?>
</body>
</html>
