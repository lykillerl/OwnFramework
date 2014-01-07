<?php

#Create by LYK on 2010-12-12 @ 18:01PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

$_Sys_UPermit = File_To_Data("{DATA_SYSUPERMIT}");
if (!is_array($_Sys_UPermit))
    $_Sys_UPermit = array();

$SysUsers = array();
foreach ($__SysUser as $ID => $User)
    if ($User['ADMIN'] === false)
        $SysUsers[$ID] = $User['FNAME'];

switch ($Action)
{
    case "ChkPermit":
        var_dump($_Sys_UPermit);
        exit;

    case "CpyPermit":
        if (array_key_exists($CpID, $_Sys_UPermit) && !empty($UID) && $CpID !== $UID)
        {
            $_Sys_UPermit[$UID] = $_Sys_UPermit[$CpID];
            if (!Data_To_File("{DATA_SYSUPERMIT}", $_Sys_UPermit))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable copy user permission.")));
            else
                $Sys->Json_Respone('ok', $Sys->Success(Lang("Successful copy user permission.")));
        }
        else
            $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, source user is not valid.")));
        break;

    case "Allow":
        if (!empty($UID) && !empty($Code))
        {
            if (!isset($_Sys_UPermit[$UID]))
                $_Sys_UPermit[$UID] = array();
            if (!isset($_Sys_UPermit[$UID][$Code]))
                $_Sys_UPermit[$UID][$Code] = true;
            if (!Data_To_File("{DATA_SYSUPERMIT}", $_Sys_UPermit))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, tempolary setup user permission.")));
            else
                $Sys->Json_Respone('done', null, array("Reloader" => array("Reload" => true, "Data" =>
                                array("UI" => "PList"), "Config" => array("Target" => "#PContent",
                                    "ShwLoad" => false))));
        }
        break;

    case "Deny":
        if (isset($_Sys_UPermit[$UID]) && !empty($Code))
        {
            foreach ($_Sys_UPermit[$UID] as $Key => $Value)
                if (strpos($Key, $Code) !== false)
                    unset($_Sys_UPermit[$UID][$Key]);
            if (!Data_To_File("{DATA_SYSUPERMIT}", $_Sys_UPermit))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, tempolary unable to clear page permission.")));
            else
                $Sys->Json_Respone('done', null, array("Reloader" => array("Reload"=>true, "Data" =>
                                array("UI" => "PList"), "Config" => array("Target" => "#PContent",
                                    "ShwLoad" => false))));
        }
        break;
}

switch ($UI)
{
    case "CPPermit":
        unset($SysUsers[$UID]);
        if (!empty($SysUsers))
            $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("Copy Permission"), array(Lang("Target") =>
                    $Sys->SelStr("CopyID", $SysUsers, null)), array($Sys->ActRequest_Button(Lang("Copy Permission"),
                    "{SPG_PERMIT}", array('UI' => 'Confirm_CpyPermit', 'UID' => $UID),
                    "#CpyPermit_Form")), 1, 'CpyPermit_Form', '50em'), Lang("User Permission")));
        else
            $Sys->WebJs($Sys->Js_MsgBox(Lang("There is no other user/permissions for this user to copy"),
                Lang("No other user")));
        break;

    case "PList":
        function PermitLst($List, $UPermit) {
            global $Sys;
            $Web = "<ul class=\"permit\">";
            foreach ($List as $Code => $Item)
            {
                $Item['Explain'] = (!empty($Item['Explain']) ? $Item['Explain'] :
                    '- No Description -');
                switch($Item['Permit']){

                case 'Login':
                    $Web .= "<li class=\"unknow\"><div class=\"nav\">".Lang("Login")."</div><div>{$Item['Title']}<br /><sup>{$Item['Explain']}</sup></div></li>";
                    if (!empty($Item['Sub']))
                        $Web .= PermitLst($Item['Sub'], $UPermit);
                break;

                case 'Admin':
                    $Web .= "<li class=\"unknow\"><div class=\"nav\">".Lang("Admin")."</div><div>{$Item['Title']}<br /><sup>{$Item['Explain']}</sup></div></li>";
                    if (!empty($Item['Sub']))
                        $Web .= PermitLst($Item['Sub'], $UPermit);
                break;

                case 'Permit':
                    if (isset($UPermit[$Code]))
                    {
                        $Web .= "<li class=\"allow\" href=\"" . $Sys->Link_ActReqeust("{SPG_PERMIT}",
                            array(
                            "Action" => "Deny",
                            "UI" => $UI,
                            "Code" => $Code)) . "\"><div class=\"nav\">".Lang("Allow")."</div><div>{$Item['Title']}<br /><sup>{$Item['Explain']}</div></sup></div></li>";
                        if (!empty($Item['Sub']))
                            $Web .= PermitLst($Item['Sub'], $UPermit);
                    }
                    else
                        $Web .= "<li class=\"deny\" href=\"" . $Sys->Link_ActReqeust("{SPG_PERMIT}",
                            array(
                            "Action" => "Allow",
                            "UI" => $UI,
                            "Code" => $Code)) . "\"><div class=\"nav\">".Lang("Deny")."</div><div>{$Item['Title']}<br /><sup>{$Item['Explain']}</sup></li>";
                    break;
                }
            }
            $Web .= "</ul>";
            return $Web;
        }
        $UPermit = isset($_Sys_UPermit[$UID]) ? $_Sys_UPermit[$UID] : array();
        $Sys->WebPut(PermitLst($Syslist, $UPermit));
        break;

    default:
        if (!empty($UID))
        {
            $Web = '';
            $Sys->WebPut($Sys->TBTab($Sys->Box("<div id=\"PContent\"></div>", Lang("User Permission")) .
                $Sys->Paragraph($Sys->Ajax_Link_Object(Lang("Change User"), "{SPG_PERMIT}", null, "{JS_MAIN}",
                $Sys->Js_DataDel("UID"))), array(
                'TAB_SELECTED' => 'PERMIT',
                'PERMIT' => Lang("Setup User Permit"),
                'COPY' => $Sys->Ajax_Link_Fancy(Lang("Copy Permission"), "{SPG_PERMIT}", array("UI" =>
                        "CPPermit"))), Lang("User") . " : " . $SysUsers[$UID], Lang("User Permission Control")));
            $Sys->WebJs($Sys->Js_Ajax_Object("{SPG_PERMIT}", array("UI" => "PList"), array("Target" =>
                    "#PContent")));
        }
        else
        {
            if (!empty($SysUsers))
            {
                $Sys->WebPut($Sys->TBTab($Sys->Build_Form(Lang("Setup User Permission"), array(Lang
                        ("User") => $Sys->SelStr("PUID", $SysUsers, $UID)), array($Sys->
                        Ajax_Button_Object(Lang("Submit"), "{SPG_PERMIT}", null, null, $Sys->
                        Js_DataSet("UID", $Sys->Js_ObjVal('#PUID', null, ''))))), null, null, Lang("User Permission Control"), null,
                    'center', '50em'));
            }
            else
            {
                $Sys->WebPut($Sys->Box($Sys->Unknow(Lang("No available user to setup permission, please register/enable a user first.")),
                    Lang("No available user"), '50em'));
            }
        }
}
$Sys->WebOut();

?>