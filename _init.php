<?php

#Create by LYK on 2012-12-12 @ 10:12AM

#########################################################
#Defind Initializing Variable
#########################################################

#External Database Connector
switch ($_SERVER['HTTP_HOST'])
{
    default:
        $DBC_Init = array(
            'HOST' => 'localhost',
            'PORT' => 3306,
            'USER' => 'root',
            'PASS' => '',
            'NAME' => 'db_apps',
            'CHAR' => 'UTF8');
        break;
}

#########################################################
# Setup Relative Path
#########################################################
$Root_Path = '';
if (!defined('DIR_LEVEL'))
    $Relative = ".";
elseif (DIR_LEVEL === 1)
    $Relative = "..";
else
{
    $Relative = "";
    for ($i = 0; $i < DIR_LEVEL; $i++)
        $Relative .= ".." . (!empty($Relative) ? "/" : "");
}

$Root_Path = str_replace("\\", "/", realpath($Relative));
$Root_Path .= (substr($Root_Path, -1) === "/" ? "" : "/");
define("ROOT", $Root_Path, true);

#########################################################
# Include Main common file.
#########################################################
include_once (ROOT . "_common/init_common.php");
include_once (ROOT . "_app.config.php");

?>