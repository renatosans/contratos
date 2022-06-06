<?php

session_start();
session_destroy();
include_once("defines.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pt-br" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" >
    <title><?php echo $appTitle; ?></title>

    <link href="<?php echo $pathCss; ?>/admin.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $pathCss; ?>/jquery-ui.css"  rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery.min.js" ></script>
    <script type="text/javascript" src="<?php echo $pathJs; ?>/jquery-ui.min.js"></script>
    <script src="<?php echo $pathJs; ?>/jquery.blockUI.js" type="text/javascript"></script>
    <script src="<?php echo $pathJs; ?>/jquery.form.js" type="text/javascript"></script>
    <script src="<?php echo $pathJs; ?>/jquery.validate.js" type="text/javascript"></script>


    <script type="text/javascript" >
        $(document).ready(function($){

            $("#btnLogin").button({ icons: { primary:'ui-icon-power' } });

            $("#btnChangePassword").button({ icons: { primary:'ui-icon-key' } }).click( function() {
                var targetUrl = 'AjaxCalls/ChangePassword.php';
                $("form[name=fLogin]").append("<div id='passwordDialog'></div>");
                $("#passwordDialog").load(targetUrl).dialog({modal:true, width: 280, height: 340, close: function(event, ui) { $(this).dialog('destroy').remove(); }});
            });

            var loader = '<div id="loader"></div>';
            $("body").append(loader).find("#loader").hide();

            $('input[type=text]').each(function(){
                $(this).addClass('flickTextBox');
            });
            $('input[type=password]').each(function(){
                $(this).addClass('flickTextBox');
            });

            $("#tryAgain").live("click",function(){
                location.reload();
            });


            $( "#dialog-modal" ).dialog({
                    modal:true,
                    resizable:false,
                    width: 650,
                    show: "fade",
                    open:function(event,ui){
                        $.unblockUI();
                    },
                    close:function(event,ui){
                        $("#dialog-modal").remove();
                    }
                });
                $(document).ajaxStart(function(){
        $.blockUI({
            message: loader,
            css:{left:"50%",width:"20px"}
        });
    }).ajaxStop($.unblockUI);
        $("form button[type='submit']").live("click",function(){
            $(this).parent().parent().parent().validate({
                submitHandler: function(form) {
                    $(form).ajaxSubmit({
                        datatype:'html',
                        clearForm: true,
                        success: function(data){
                            var resultBox = "<div class='resultBox'><div class='result'></div><div id='tryAgain'>Tentar com outro usuário</div></div>";
                            $(form).empty().append(resultBox).find(".result").append(data);
                        }
                    });
                },
                rules:{
                    username:"required",
                    password:"required"
                }
            });
        });
        });
    </script>

</head>
<body>

<div id="dialog-modal">
    <div id="login" class="corner">
        <div id="logo-cliente">
            <img src="<?php echo $pathImg; ?>/logo.png" alt="Addon de Contratos" />
        </div>
        <div id="login-form">
            <form name="fLogin" action="<?php echo $root; ?>/Frontend/login/acao.php" method="POST" >
                <fieldset>
                <legend>Login</legend>
                    <input type="hidden" name="acao" value="authenticate" />
                    <label>
                        <span>Username:</span>
                        <input type="text" name="username" />
                    </label>
                    <label>
                        <span>Password:</span>
                        <input type="password" name="password"/>
                    </label>
                    <div style="clear:both;">
                        <br/><br/>
                    </div>

                    <div style="width: 100%; text-align: center;">
                        <button id="btnLogin" type="submit" style="display:inline; margin:0px auto;">Logar</button>
                        <button id="btnChangePassword" type="button" style="display:inline; margin:0px auto;">Senha</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

</body>
</html>
