<?php

#Create by LYK on 2009-05-20 @ 11:12AM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

ob_start();
ob_implicit_flush(0);

#Reset to become a javascript file type at http header
header("content-type: text/css");

#Exclude/Include file
$IFile = array();
$XFile = array();

#Load all css folder file. (exclude self)
if(!empty($XCSS))
    $XFile = explode(",", $XCSS);
if (!empty($ICSS))
    $IFile = explode(",", $ICSS);

$CSS_File = array();
$CSS_Dir = dir($Sys_Path['REAL']['CSS']);

#Start Listing Css File
while (false !== ($File = $CSS_Dir->read()))
    if ($File !== '.' && $File !== '..' && $File !== basename($Sys_Page['REAL']['Style']))
        if(in_array($File, $IFile))
            $CSS_File[] = $File;
        elseif(empty($IFile) && !in_array($File, $XFile) && strpos($File,".txt") === false)
                $CSS_File[] = $File;
$CSS_Dir->close();
clearstatcache();

sort($CSS_File, SORT_STRING);
foreach ($CSS_File as $File){
    include ("{$Sys_Path['REAL']['CSS']}{$File}");
    echo "\n";
}

$Sys->WebPut(ob_get_clean());
$Sys->WebOut();

?>