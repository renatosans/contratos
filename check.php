<?php

include_once("defines.php");
include_once("DataAccessObjects/AuthorizationDAO.php");
include_once("DataTransferObjects/AuthorizationDTO.php");


// Valida a sessão
if(!(isset($_SESSION["usrID"]))){
    echo "<script>window.location='".$root."/index.php'</script>";
    exit;
}

$isAjaxCall = false;
if (strpos($_SERVER["REQUEST_URI"], 'AjaxCalls')) $isAjaxCall = true;
if (!$isAjaxCall) {
    $_SESSION["lastButOnePage"] = $_SESSION["lastPage"];
    $_SESSION["lastPage"] = $_SESSION["currentPage"];
    $_SESSION["currentPage"] = $_SERVER["REQUEST_URI"];
}


function NavigateBackTarget() {
    $currentPage = explode("?", $_SESSION["currentPage"], 2);
    $lastPage = explode("?", $_SESSION["lastPage"], 2);
    if ($lastPage[0] == $currentPage[0])
        return $_SESSION["lastButOnePage"];
    else
        return $_SESSION["lastPage"];
}

// Verifica o nível de acesso do usuário para a funcionalidade especificada
function GetAuthorizationLevel($mysqlConnection, $functionality) {
    $loginId = 0;
    if(isset($_SESSION["usrID"])) $loginId = $_SESSION["usrID"];

    $authorizationDAO = new AuthorizationDAO($mysqlConnection);
    $authorizationDAO->showErrors = 1;
    $authorizationArray = $authorizationDAO->RetrieveRecordArray("login_id=".$loginId." AND funcionalidade=".$functionality);
    if (sizeof($authorizationArray) == 1) {
        $authorization = $authorizationArray[0];
        return $authorization->nivelAutorizacao;
    }

    return 1; // Sem acesso
}

function DisplayNotAuthorizedWarning() {
    echo '<div style="clear:both;">';
    echo '    <br/><br/><br/>';
    echo '</div>';
    echo '<div style="background-color:LightYellow; border:1px solid red; margin-left: auto; margin-right: auto; text-align: center;" >';
    echo '<span style="color: red; font-size: 18px; margin: 50px;" >Você não possui autorização para acessar esta página.</span><br/>';
    echo '<span style="color: red; font-size: 18px; margin: 50px;" >Consulte o administrador do sistema para mais informações.</span>';
    echo '</div>';
    echo '<div style="clear:both;">';
    echo '    <br/><br/><br/>';
    echo '</div>';
}

?>
