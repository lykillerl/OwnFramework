<?php

#Create by LYK on 2009-06-04 @ 12:48PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

ob_start();
ob_implicit_flush(0);

#Reset to become a javascript file type at http header
header("Expires: Jan, 1 Thu 1970 00:00:00 GMT");
header("Last-Modified: Jan, 1 Thu 1970 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("content-type: application/x-javascript");

#Exclude/Include file
$IFile = array();
$XFile = array();

#Load all js folder file.
if (!empty($IJS))
    $_Js_File = explode(",", $IJS);
foreach ($_Js_File as $__File){
    include ("{$Sys_Path['REAL']['JS']}{$__File}");
    echo "\n";
}
$Sys->WebPut(ob_get_clean());
$Sys->WebOut();
?>