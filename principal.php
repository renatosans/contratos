<?php

session_start();

include_once("check.php");
include_once("defines.php");


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
<input type="hidden" name="startPage" value="principal.inc.php" />

<div id="centro">
    <div id="cabecalho">
        <img src="<?php echo $pathImg; ?>/logo.png" />
    </div>

    <div id="menu">
        <a href="<?php echo 'principal.inc.php'; ?>" >
            Principal
        </a>
        <br/>
        <?php
        foreach($sideMenu as $menuItem=>$url){
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
    </div>
</div>

</body>
</html>
