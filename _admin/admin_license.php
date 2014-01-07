<?php

#Create by LYK on 2009-11-26 @ 16:22PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

switch ($SType)
{

    default:
        $LAppLst = "";
        foreach ($AppList as $AppName)
            $LAppLst .= (!empty($LAppLst) ? "<br />" : "") . $AppName;
        $Sys->WebPut($Sys->TBTab($Sys->Build_Form(Lang("License Information"), array(
            Lang("Product Name") => $Sys->Normal(IfEmptyH($License['Product'])),
            Lang("Product Version") => $Sys->Normal(IfEmptyH($DLC_Infor['Version'])),
            Lang("Author") => $Sys->Normal(IfEmptyH($DLC_Infor['Author'])),
            Lang("Register Title") => $Sys->Normal(IfEmptyH($Company['Title'])),
            Lang("Register To") => $Sys->Normal(IfEmptyH($License['Registered-To'])),
            Lang("Register Date") => $Sys->Normal(IfEmptyH($License['RDate'])),
            Lang("Expired Date") => $Sys->Normal(IfEmptyH(($License['Expired'] != 'x-limit') ? $License['Expired'] :
                Lang("Permanent"))),
            Lang("Serial Number") => $Sys->Normal(IfEmptyH($License['SN'])),
            Lang("Application") => $Sys->Strong($LAppLst)), null, 1, null, 1, 'center', '500px',
            'table', 'padding:10px;'), null, null, Lang("License Information")));
        break;
}
$Sys->WebOut();

?>