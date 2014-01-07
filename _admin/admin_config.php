<?php

#Create by LYK on 2012-06-23 @ 09:38AM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

$Enum_Var_Type = array(
    "array" => Lang("Array"),
    "int" => Lang("Interger"),
    "double" => Lang("Float Number"),
    "boolean" => Lang("Boolean"),
    "string" => Lang("String"),
    "password" => Lang("Password"),
    "select" => Lang("Selection"),
    "email" => Lang("Email Address"),
    "ipv4" => Lang("IP Address (IPV4)"),
    "ipv6" => Lang("IP Address (IPV6)"),
    "date" => Lang("Date"),
    "time" => Lang("Time"),
    "datetime" => Lang("Date/Time"));

$Enum_Boolean = array("Y" => Lang("True"), "N" => Lang("False"));

switch ($Action)
{
    case "RegConfig":
        if (empty($Reg_App) || empty($SName) || empty($SDesc))
            $Sys->Json_Respone('respone', $Sys->Unknow(Lang("Please fill in the blank, thank you.")));
        elseif ($Type === "select" && empty($SExtend))
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("Must fill in the extend data with selection type E.g: value1:label1,value2:lable2")));
        else
        {
            $SName = str_replace(" ", "", strtoupper($SName));
            $SData = str_replace(" ", "", strtoupper($SData));
            switch ($Type)
            {
                case "array":
                    $SArray = explode(",", $SData);
                    $SData = array();
                    foreach ($SArray as $SValue)
                    {
                        if (strpos($SValue, ":") !== (boolean)false)
                        {
                            list($PName, $PValue) = explode(":", $SValue, 2);
                            $PName = trim($PName);
                            $PValue = trim($PValue);
                            $SData[$PName] = $PValue;
                        }
                        else
                            $SData[] = $SValue;
                    }
                    break;
                case "int":
                    $SData = (int)$SData;
                    break;
                case "double":
                    $SData = (double)$SData;
                    break;
                case "boolean":
                    $SData = (($SData === "Y") ? true : false);
                    break;
                case "date":
                    if (strtotime($SData) < (int)0)
                        $SData = (boolean)false;
                    else
                        $SData = date("Y-m-d", strtotime($SData));
                    break;
                case "time":
                    if (strtotime($SData) < (int)0)
                        $SData = (boolean)false;
                    else
                        $SData = date("H:i:s", strtotime($SData));
                    break;
                case "datetime":
                    if (strtotime($SData) < (int)0)
                        $SData = date("Y-m-d H:i:s");
                    else
                        $SData = date("Y-m-d H:i:s", strtotime($SData));
                    break;
                case "select":
                    if (strpos($SExtend, ",") !== (boolean)false)
                        $_SExtend = explode(",", $SExtend);
                    $SExtend = array();
                    foreach ($_SExtend as $Key => $Value)
                    {
                        if (strpos($Value, ":") !== (boolean)false)
                        {
                            list($PName, $PData) = explode(":", $Value);
                            $PName = trim($PName);
                            $PData = trim($PData);
                            $KeyName = !empty($PName) ? $PName : $Key;
                            $SExtend[$KeyName] = $PData;
                            if (empty($SData) && $Key < 1)
                                $SData = $KeyName;
                        }
                    }
                    break;
            }
            if (!is_array($__SysConfig))
                $__SysConfig = array();
            if (array_key_exists($SName, $__SysConfig[$Reg_App]))
                $Sys->Json_Respone('respone', $Sys->Warning(Lang("Sorry, the config name of this application is exist."),
                    1), array("Title" => Lang('System Error')));
            $__SysConfig[$Reg_App][$SName] = array(
                "TYPE" => $Type,
                "DATA" => $SData,
                "DESC" => $SDesc,
                "SEXTEND" => $SExtend);
            if (!Data_To_File("{DATA_SYSCONFIG}", $__SysConfig))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, unable to insert the data."),
                    1), array("Title" => Lang('System Error')));
            else
                $Sys->Json_Respone('ok', $Sys->Success(Lang("Congratulation, you are successful register new config.")), array("Title" => Lang("Successful Register"), "Execute" => $Sys->Js_DataSet("App", "_$('#Reg_App').Value()") . $Sys->Js_Ajax_Object("{SPG_CONFIG}")));
        }
        break;

    case "SaveConfig":
        if (!empty($App) && !empty($SName) && !empty($Type))
        {
            $Done = false;
            switch ($Type)
            {
                case "array":
                    if (!empty($SData))
                    {
                        $SArray = explode(",", $SData);
                        $SData = array();
                        foreach ($SArray as $SValue)
                        {
                            if (strpos($SValue, ":") !== (boolean)false)
                            {
                                list($PName, $PValue) = explode(":", $SValue, 2);
                                $SData[$PName] = $PValue;
                            }
                            else
                                $SData[] = $SValue;
                        }
                    }
                    else
                        $SData = array();
                    break;
                case "int":
                    $SData = (int)$SData;
                    break;
                case "double":
                    $SData = (double)$SData;
                    break;
                case "boolean":
                    $SData = (($SData === "Y") ? true : false);
                    break;
                case "date":
                    if (strtotime($SData) < (int)0)
                        $SData = (boolean)false;
                    else
                        $SData = date("Y-m-d", strtotime($SData));
                    break;
                case "time":
                    if (strtotime($SData) < (int)0)
                        $SData = (boolean)false;
                    else
                        $SData = date("H:i:s", strtotime($SData));
                    break;
                case "datetime":
                    if (strtotime($SData) < (int)0)
                        $SData = date("Y-m-d H:i:s");
                    else
                        $SData = date("Y-m-d H:i:s", strtotime($SData));
                    break;
            }
            if (array_key_exists($App, $__SysConfig) && array_key_exists($SName, $__SysConfig[$App]))
                $__SysConfig[$App][$SName]['DATA'] = $SData;
            if ($Type === "email" && !$Sys->IsValid_Email($SData))
                $Status = $Sys->Warning(Lang("The email address is invalid."));
            elseif ($Type === "ipv4" && !$Sys->IsValid_IP($SData))
                $Status = $Sys->Warning(Lang("The IP Address is invalid."));
            elseif ($Type === "date" && $SData === (boolean)false)
                $Status = $Sys->Warning(Lang("Unable recognize date format."));
            elseif ($Type === "time" && $SData === (boolean)false)
                $Status = $Sys->Warning(Lang("Unable recognize time format."));
            elseif ($Type === "datetime" && $SData === (boolean)false)
                $Status = $Sys->Warning(Lang("Unable recognize date/time format."));
            elseif (!array_key_exists($App, $__SysConfig) || !array_key_exists($SName, $__SysConfig[$App]))
                $Status = $Sys->Warning(Lang("Unknow Application or config name."));
            elseif (!Data_To_File("{DATA_SYSCONFIG}", $__SysConfig))
                $Status = $Sys->Warning(Lang("Sorry, tempolary unable to save the config"));
            else
            {
                $Done = true;
                $Status = $Sys->Success(Lang("Successful save the config."));
            }
        }
        else
            $Status = $Sys->Warning(Lang("Please fill/select the config value."));
        if (!empty($__SysConfig[$App][$SName]['DATA']))
        {
            $SData = $__SysConfig[$App][$SName]['DATA'];

            switch ($__SysConfig[$App][$SName]['TYPE'])
            {
                case "array":
                    $Data = '';
                    foreach ($SData as $PName => $PValue)
                    {
                        if (is_string($PName))
                            $Data .= (!empty($Data) ? "," : "") . "{$PName}:{$PValue}";
                        else
                            $Data .= (!empty($Data) ? "," : "") . $PValue;
                    }
                    $SData = $Data;
                    break;

                case "boolean":
                    $SData = ($SData === true) ? "Y" : "N";
                    break;
            }
        }
        $Sys->Json_Respone($Done === true ? "ok" : "error", $Status, array("Title" => Lang("Save Config"),
                "Value" => $SData));
        break;

    case "DelConfig":
        if (array_key_exists($App, $__SysConfig) && array_key_exists($SName, $__SysConfig[$App]) && ($App !== 'SYSTEM' || ($App === 'SYSTEM' && $Sys->GetConfig('SYSTEM_PROTECT') === false)))
        {
            unset($__SysConfig[$App][$SName]);
            if (!Data_To_File("{DATA_SYSCONFIG}", $__SysConfig))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, we unable delete the app config."), true));
            else
                $Sys->Json_Respone('ok', $Sys->Success(Lang("Successful deleted the app config.")),
                    array("Reload" => true));
        }
        elseif($App === 'SYSTEM' && $Sys->GetConfig('SYSTEM_PROTECT'))
            $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, config protected by the system, it cannot be delete."), true));
        else
            $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, we unable found the app config."), true));
        break;

    case "DelAppSet":
        if (array_key_exists($App, $__SysConfig) && $App !== 'SYSTEM')
        {
            unset($__SysConfig[$App]);
            if (!Data_To_File("{DATA_SYSCONFIG}", $__SysConfig))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, we unable delete entire app config."), true));
            else
                $Sys->Json_Respone('ok', $Sys->Success(Lang("Successful deleted entire app config.")),
                    array("Execute" => $Sys->Js_DataSet("App", "'SYSTEM'") . $Sys->
                        Js_Ajax_Object("{SPG_CONFIG}")));
        }
        break;
}

switch ($UI)
{
    case "Confirm_DelAppSet":
        if (!empty($App))
        {
            $Sys->WebPut($Sys->Js_AskBox(Lang("Are you sure want to delete this apps ") . $Sys->
                Warning($App, true) . "?", array(Lang("Delete") => $Sys->Link_ActReqeust("{SPG_CONFIG}",
                    $Sys->Token(array("Action" => "DelAppSet"), 2))), Lang("Confirm Delete")));
        }
        break;

    case "Confirm_DelConfig":
        if (!empty($App) && !empty($SName))
        {
            $Sys->WebPut($Sys->Js_AskBox(Lang("Are you sure want to delete the apps ") . $Sys->
                Normal($App, true) . Lang(" config ") . $Sys->Warning($SName, true) . "?", array(Lang
                    ("Delete") => $Sys->Link_ActReqeust("{SPG_CONFIG}", $Sys->Token(array("Action" =>
                        "DelConfig", "SName" => $SName), 2))), Lang("Confirm Delete")));
        }
        break;

        #display worker leave form
    case "RegConfig":
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("Config Register System")));
        $FMode = (!empty($ID) ? $Sys->Inform(Lang("Edit Mode"), 1) : $Sys->Success(Lang("Add Mode"),
            1));
        if(!$Sys->GetConfig('SYSTEM_PROTECT'))
            $AppList["SYSTEM"] = Lang("System Config");
        ksort($AppList);
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("Config Register Form"), array(
            Lang("System Mode") => $FMode,
            Lang("Application") => $Sys->SelStr("Reg_App", $AppList, (is_null($Reg_App) ? $App : $Reg_App)) .
                $Sys->Warning("*"),
            Lang("Config Name") => $Sys->Text("SName", $SName, 'off', null, 32, 30) . $Sys->
                Warning("*"),
            Lang("Config Type") => $Sys->SelStr("Type", $Enum_Var_Type, $Type, $Sys->ObjEvent("onchange",
                "if(this.value==='select'){" . $Sys->JS_ObjDisable('SExtend', false) . $Sys->
                Js_ObjFocus("SExtend") . "}else{" . $Sys->JS_ObjDisable('SExtend', true) . "}")) . $Sys->
                Warning("*"),
            Lang("Config Extend") => $Sys->Text("SExtend", $SExtend, 'off', null, null, 50, true) .
                $Sys->Warning("**"),
            Lang("Description") => $Sys->Text("SDesc", $SDesc, 'off', null, 50, 50) . $Sys->Warning
                ("*"),
            Lang("System Satus") => $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"),
                "{SPG_CONFIG}", array(
                'Action' => 'RegConfig',
                'UI' => $UI,
                'ID' => $ID), "#Sys_Config_Form", "#Status")), 1,
            "Sys_Config_Form")));
        $Sys->WebJs($Sys->Js_ObjFocus('SName') . $Sys->JS_ObjDisable('SExtend', true));
        break;

        #list out the all worker leave
    default:
        $App_Tab = array();
        if (empty($App))
        {
            $App = "SYSTEM";
            $Sys->WebJs($Sys->Js_DataSet("App", "'SYSTEM'"));
        }
        $App_Tab['TAB_SELECTED'] = $App;
        foreach ($__SysConfig as $_App => $Apps)
        {
            if ($_App === "SYSTEM" || array_key_exists($_App, $AppList))
                $App_Tab[$_App] = ($_App === $App ? (array_key_exists($_App, $AppList) ? $AppList[$_App] :
                    $_App) : $Sys->Ajax_Link_Object((array_key_exists($_App, $AppList) ? $AppList[$_App] :
                    $_App), "{SPG_CONFIG}", null, "{JS_MAIN}", $Sys->Js_DataSet("App", "'{$_App}'"))) . (($_App
                    === $App && $App !== 'SYSTEM') ? " (" . $Sys->Ajax_Link_Execute($Sys->Warning(Lang
                    ("Delete"), true), "{SPG_CONFIG}", array("UI" => "Confirm_DelAppSet")) . ")" :
                    "");
        }

        $Pg_Now = (int)$Pg_Now;
        $Pg_Count = 20;
        $TCount = (array_key_exists($App, $__SysConfig) ? count($__SysConfig[$App]) : 0);
        if ($TCount > 0)
        {
            $TData = array();
            $iPos = 0;
            foreach ($__SysConfig[$App] as $SName => $Config)
            {
                $iPos++;
                if ($iPos <= $Pg_Now * $Pg_Count)
                    continue;
                if ($iPos > $Pg_Now * $Pg_Count + $Pg_Count)
                    break;
                switch ($Config['TYPE'])
                {
                    case "array":
                        $Data = '';
                        if (is_array($Config['DATA']))
                            foreach ($Config['DATA'] as $PName => $PValue)
                                if (is_string($PName))
                                    $Data .= (!empty($Data) ? "," : "") . "{$PName}:{$PValue}";
                                else
                                    $Data .= (!empty($Data) ? "," : "") . "{$PValue}";
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Data, "off", null, 255, 100);
                        $ShwVarDesc = Lang("Please fill array value, data and split with ','");
                        break;
                    case "int":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            15, 12);
                        $ShwVarDesc = Lang("Please fill in only intiger value.");
                        break;
                    case "double":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            20, 17);
                        $ShwVarDesc = Lang("Please fill in numeric value.");
                        break;
                    case "boolean":
                        $Data = ($Config['DATA']) ? "Y" : "N";
                        $FillObj = $Sys->SelStr("SData_{$App}_{$SName}", $Enum_Boolean, $Data);
                        $ShwVarDesc = Lang("Please select the boolean value true/false.");
                        break;
                    case "string":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            50, 47);
                        $ShwVarDesc = Lang("Please fill in the blank.");
                        break;
                    case "password":
                        $FillObj = $Sys->Password("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            50, 23);
                        $ShwVarDesc = Lang("Please fill in the password.");
                        break;
                    case "date":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            10, 7);
                        $ShwVarDesc = Lang("Please fill in the date.");
                        break;
                    case "time":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            8, 5);
                        $ShwVarDesc = Lang("Please fill in the date.");
                        break;
                    case "datetime":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $MD['SData'], "off", null, 19,
                            16);
                        $ShwVarDesc = Lang("Please fill in the date/time.");
                        break;
                    case "ipv4":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            15, 13);
                        $ShwVarDesc = Lang("Please fill in the ip address (IPV4).");
                        break;
                    case "ipv6":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            32, 29);
                        $ShwVarDesc = Lang("Please fill in the ip address (IPV6).");
                        break;
                    case "email":
                        $FillObj = $Sys->Text("SData_{$App}_{$SName}", $Config['DATA'], "off", null,
                            50, 47);
                        $ShwVarDesc = Lang("Please fill in the email address.");
                        break;
                    case "select":
                        $Selection = array();
                        if (empty($Config['DATA']))
                            $Selection[''] = Lang("Please Select");
                        if (is_array($Config['SEXTEND']))
                            foreach ($Config['SEXTEND'] as $PName => $PValue)
                                $Selection[$PName] = $PValue;
                        $FillObj = $Sys->SelStr("SData_{$App}_{$SName}", $Selection, $Config['DATA']);
                        $ShwVarDesc = Lang("Please select one.");
                        break;
                }
                $TData[] = array(
                    $iPos,
                    array("align" => "left", "content" => $Sys->Strong($Sys->Normal($SName, 1)) .
                            " - {$Enum_Var_Type[$Config['TYPE']]}" . (($App === "SYSTEM" && $Sys->GetConfig('SYSTEM_PROTECT')) ? "" :
                            " (" . $Sys->Ajax_Link_Execute($Sys->Warning(Lang("Delete"), true), "{SPG_CONFIG}",
                            array("UI" => "Confirm_DelConfig", "SName" => $SName)) . ")") .
                            "<br />" . $Sys->Unknow($Config['DESC'], 1)),
                    array("align" => "left", "content" => $FillObj . "<br />" . $Sys->Unknow($ShwVarDesc,
                            1)),
                    $Sys->ActRequest_Button(Lang("Save Config"), "{SPG_CONFIG}", array(
                        "Action" => "SaveConfig",
                        "SName" => $SName,
                        "Type" => $Config['TYPE']), "SData:_$('#SData_{$App}_{$SName}').Value()",
                        "#SData_{$App}_{$SName}"));
            }
            $Sys->FREE_RESULT($QLst);
        }
        $License['System'] = true;
        $THCol = array(
            Lang("No."),
            Lang("Config Detail"),
            Lang("User Allow"),
            array("colspan" => 2, "content" => Lang("Config Value")));
        $Sys->WebPut($Sys->TBTab($Sys->TBGrid($TData, $Sys->Normal($App, true) . " " . Lang("Configuration"),
            $THCol, "Config_Lister", Lang("Please select tab of application config.")) . $Sys->
            Pagination($Pg_Count, $TCount, $Pg_Now, "{SPG_CONFIG}"), $App_Tab, ($License['System']
            === (boolean)true ? $Sys->Ajax_Button_Fancy(Lang("New Config"), "{SPG_CONFIG}", $Sys->
            Token(array('UI' => 'RegConfig'))) : null), Lang("System Configuration")));
}
$Sys->WebOut();

?>