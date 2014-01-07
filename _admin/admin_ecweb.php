<?php

#Create by LYK on 2013-03-29 @ 15:30PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

############################################################################################################
# Start Function of ECWeb
############################################################################################################

function Fix_CodeID($Code)
{
    $Result = '';
    if (empty($Code))
        return $Result;
    for ($i = 0; $i <= strlen($Code); $i++)
    {
        $Char = ord(substr($Code, $i, 1));
        if (($Char >= 48 && $Char <= 57) || ($Char >= 65 && $Char < 90) || ($Char >= 97 && $Char <
            123) || array_search($Char, array(
            45,
            46,
            91,
            93,
            95), true))
            $Result .= chr($Char);
    }
    return $Result;
}

function Resort_Item_Array(&$ECWeb, $GID = null, $PID = null)
{
    global $_ECW_GRP, $_ECW_PG;
    $Sort_EcItms = array();
    $GID = (empty($GID) ? $_ECW_GRP : $GID);
    $PID = (empty($PID) ? $_ECW_PG : $PID);
    if (array_key_exists($GID, $ECWeb) && array_key_exists($PID, $ECWeb[$GID]['PAGES']))
    {
        foreach ($ECWeb[$GID]['PAGES'][$PID]['ITMS'] as $Uniqid => $Row)
            foreach ($Row as $Key => $Value)
                $Sort_EcItms[$Key][$Uniqid] = $Value;
        array_multisort($Sort_EcItms['TYPE'], SORT_DESC, $Sort_EcItms['PARENT'], SORT_ASC, $Sort_EcItms['SEQ'],
            SORT_ASC, $ECWeb[$GID]['PAGES'][$PID]['ITMS']);
    }
    unset($Sort_EcItms);
    if (array_key_exists($GID, $ECWeb) && $ECWeb[$GID]['RESPONSIVE'] === true)
    {
        $Sort_EcPgs = array();
        foreach ($ECWeb[$GID]['PAGES'] as $Uniqid => $Row)
            foreach ($Row as $Key => $Value)
                if ($Key !== 'ITMS')
                    $Sort_EcPgs[$Key][$Uniqid] = $Value;
        array_multisort($Sort_EcPgs['WIDTH'], SORT_DESC, SORT_NATURAL, $ECWeb[$GID]['PAGES']);
        unset($Sort_EcPgs);
    }
}

############################################################################################################
# End Function of ECWeb
############################################################################################################

switch ($Action)
{
        #ECWeb Group Profile Register
    case "RegGrp":
        $Code = Fix_CodeID($Code);
        $Code = strtoupper($Code);
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        if (empty($Code) || empty($GName))
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("Please fill in the blank."), true));
        elseif ($ID !== $Code && array_key_exists($Code, $ECWeb))
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("The Group Code is already exists."), true));
        else
        {
            $UI = "GrpMsg";
            $Responsive = (!empty($Responsive) ? true : false);
            if (!is_array($ECWeb))
                $ECWeb = array();
            if (empty($ID))
                $ECWeb[$Code] = array(
                    "GNAME" => $GName,
                    "RESPONSIVE" => $Responsive,
                    "PAGES" => array());
            else
            {
                if ($ID !== $Code)
                {
                    $ECWeb[$Code] = $ECWeb[$ID];
                    unset($ECWeb[$ID]);
                    $ID = $Code;
                }
                $ECWeb[$ID]["GNAME"] = $GName;
            }
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning((!empty($ID) ? Lang("Failed to update the page group.") :
                    Lang("Failed to create new page group.")), true));
            else
                $Sys->Json_Respone('ok', $Sys->Success((!empty($ID) ? Lang("Successful update the page group.") :
                    Lang("Successful create new page group.")), true), array("Reloader" => array("Reload" => true,
                            "Config" => array("ShwLoad" => false))));
        }
        break;

        #ECWeb Page Profile Register
    case "RegPg":
        $Code = Fix_CodeID($Code);
        $Code = strtoupper($Code);
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        if (empty($Grp) || empty($Code) || empty($STitle) || empty($Width))
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("Please fill in the blank."), true));
        elseif ($ID !== $Code && array_key_exists($Code, $ECWeb[$Grp]['PAGES']))
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("The Page Code is already exists."), true));
        elseif ($ECWeb[$Grp]['RESPONSIVE'] && strpos($Width, "%") === true)
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("For responsive site, page width unit cannot be use percent(%)."), true));
        else
        {
            $MHPage = (!empty($MHPage) ? $MHPage : 'normal');
            $Width = (is_numeric($Width) ? "{$Width}px" : $Width);
            $Home = count($ECWeb[$Grp]['PAGES']) === 0 ? true : false;
            $CSS = str_replace(array(
                "\r",
                "\n",
                "\r\n"), " ", $CSS);
            $CSS = str_replace("\"", "\\\"", $CSS);
            if (!empty($ID))
            {
                if ($ID !== $Code)
                {
                    $Temp = $ECWeb[$Grp]['PAGES'][$ID];
                    unset($ECWeb[$Grp]['PAGES'][$ID]);
                    $ID = $Code;
                    $ECWeb[$Grp]['PAGES'][$ID] = $Temp;
                    unset($Temp);
                }
                $Temp = array(
                    "BOFFSET" => (int)$BOffset,
                    "CSS" => $CSS,
                    "DESC" => $Desc,
                    "JS" => $JS,
                    "MHPAGE" => $MHPage,
                    "STYLE" => $Style,
                    "TITLE" => $STitle,
                    "WIDTH" => $Width);
                $ECWeb[$Grp]['PAGES'][$ID] = array_merge($ECWeb[$Grp]['PAGES'][$ID], $Temp);
            }
            else
                $ECWeb[$Grp]['PAGES'][$Code] = array(
                    "BOFFSET" => (int)$BOffset,
                    "CSS" => $CSS,
                    "DESC" => $Desc,
                    "HOME" => array(
                        "DESKTOP" => $Home,
                        "MOBILE" => $Home,
                        "TABLET" => $Home),
                    "JS" => $JS,
                    "LAYERS" => 0,
                    "MHPAGE" => $MHPage,
                    "STYLE" => $Style,
                    "TITLE" => $STitle,
                    "WIDTH" => $Width,
                    "ITMS" => array());
            Resort_Item_Array($ECWeb, $Grp);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning((!empty($ID) ? Lang("Failed to update the ECWeb Page.") :
                    Lang("Sorry, we unable create the ECWeb Page now.")), true));
            else
                $Sys->Json_Respone('ok', $Sys->Success((!empty($ID) ? Lang("Successful update the ECWeb Page.") :
                    Lang("Successful create new a ECWeb Page.")), true), array("Reloader" => array(
                        "Reload" => true,
                        "Data" => array("Token" => $Sys->Token(array("UI" => 'ECPage', "Grp" => $Grp),
                                2)),
                        "Config" => array("Target" => "td#EcGrp_{$Grp}", "ShwLoad" => false))));
        }
        break;

        #Select Element Page Group
    case "SelGrp":
        if (!empty($ID))
        {
            $ID = strtoupper($ID);
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $Code = $ID;
            $GName = $ECWeb[$ID]['GNAME'];
            $Responsive = $ECWeb[$ID]['RESPONSIVE'];
        }
        break;

        #Select Element Page
    case "SelPg":
        if (!empty($Grp) && !empty($ID))
        {
            $Grp = strtoupper($Grp);
            $ID = strtoupper($ID);
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $Temp = $ECWeb[$Grp]['PAGES'][$ID];
            $Code = $ID;
            $STitle = $Temp['TITLE'];
            $Desc = $Temp['DESC'];
            $Width = $Temp['WIDTH'];
            $MHPage = $Temp['MHPAGE'];
            $BOffset = $Temp['BOFFSET'];
            $Style = $Temp['STYLE'];
            $CSS = $Temp['CSS'];
            $JS = $Temp['JS'];
            if ($MHPage === 'page')
                $MHPage = true;
            else
                $MHPage = false;
        }
        break;

    case "PgHome":
        if (!empty($Grp) && !empty($ID) && !empty($Home))
        {
            $UI = "PgMsg";
            $Home = strtoupper($Home);
            if ($Home !== 'MOBILE' && $Home !== 'TABLET')
                $Home = 'DESKTOP';
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            foreach ($ECWeb[$Grp]['PAGES'] as $PCode => $MD)
            {
                if ($PCode === $ID)
                    $ECWeb[$Grp]['PAGES'][$PCode]['HOME'][$Home] = true;
                else
                    $ECWeb[$Grp]['PAGES'][$PCode]['HOME'][$Home] = false;
            }
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Failed to update the home page setting."), true));
            else
                $Sys->Json_Respone('done', null, array("Reloader" => array(
                        "Reload" => true,
                        "Data" => array("UI" => "ECPage", "Grp" => $Grp),
                        "Config" => array("Target" => "td#EcGrp_{$Grp}", "ShwLoad" => false))));
        }
        break;

    case "DelPg":
        if (!empty($ID) && !empty($Grp))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            unset($ECWeb[$Grp]['PAGES'][$ID]);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, we unable delete the ECWeb Page now."), true));
            else
                $Sys->Json_Respone('done', null, array("Reloader" => array(
                        "Reload" => true,
                        "Data" => array("UI" => "ECPage", "Grp" => $Grp),
                        "Config" => array("Target" => "td#EcGrp_{$Grp}", "ShwLoad" => false))));
        }
        break;

    case "DelGrp":
        if (!empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            unset($ECWeb[$ID]);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, we unable delete the ECWeb Group now."), true));
            else
                $Sys->Json_Respone('done', null, array("Reloader" => array("Reload" => true,
                            "Config" => array("ShwLoad" => false))));
        }
        break;

}

switch ($UI)
{
    case "Confirm_DelPg":
        if (!empty($ID) && !empty($Grp))
        {
            $Sys->WebPut($Sys->Js_AskBox(Lang("Are you sure want to delete the ECWeb Page?"), array
                (Lang("Delete") => $Sys->Link_ActReqeust("{SPG_ECWEB}", array(
                    "Action" => "DelPg",
                    "Grp" => $Grp,
                    "ID" => $ID))), Lang("Confirm Delete")));
        }
        break;

    case "Confirm_DelGrp":
        if (!empty($ID))
        {
            $Sys->WebPut($Sys->Js_AskBox(Lang("Are you sure want to delete the ECWeb Group?"), array
                (Lang("Delete") => $Sys->Link_ActReqeust("{SPG_ECWEB}", array("Action" => "DelGrp",
                        "ID" => $ID))), Lang("Confirm Delete")));
        }
        break;

    case "RegGrp":
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("EasyWeb Composing System")));
        $SMode = (!empty($ID) ? $Sys->Inform($Sys->Strong(Lang("Modify Mode"))) : $Sys->Success(Lang
            ("Add New"), 1));
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("EasyWeb Group Register Form"), array(
            Lang("System Mode") => $SMode,
            Lang("Group Code") => $Sys->Text("Code", $Code, 'off') . $Sys->Notice("V_Code"),
            Lang("Group Name") => $Sys->Text("GName", $GName, 'off') . $Sys->Notice("V_GName"),
            Lang("Type") => (!empty($ID) ? ($Responsive === true ? $Sys->Success(Lang("Responsive"),
                1) : $Sys->Normal(Lang("Normal Site"))) : $Sys->Check("Responsive", 1,
                'Responsive Site')),
            Lang("Status") => $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"),
                "{SPG_ECWEB}", array('Action' => 'RegGrp', 'ID' => $ID), "#ECWeb_Group_Form",
                '#Status', array("Reload" => true))), 1, 'ECWeb_Group_Form'), Lang("EasyWeb Composing System")));
        $Sys->WebJs($Sys->Js_ObjFocus("#Code") . $Sys->Js_Validate("#Code", array(
            "Method" => "must",
            "Min" => 1,
            "Feedback" => array("#V_Code" => $Sys->Warning("!")))) . $Sys->Js_Validate("#GName",
            array(
            "Method" => "must",
            "Min" => 1,
            "Feedback" => array("#V_GName" => $Sys->Warning("!")))));
        break;

    case "RegPg":
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("EasyWeb Composing System")));
        $SMode = (!empty($ID) ? $Sys->Inform($Sys->Strong(Lang("Modify Mode"))) : $Sys->Success(Lang
            ("Add New"), 1));
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("EasyWeb Page Register Form"), array(
            Lang("System Mode") => $SMode,
            Lang("Page Code") => $Sys->Text("Code", $Code, 'off') . $Sys->Notice("V_Code"),
            Lang("Title") => $Sys->Text("STitle", $STitle, 'off') . $Sys->Notice("V_STitle"),
            Lang("Page Dimension") => Lang("Width") . " : " . $Sys->Text("Width", (!empty($Width) ?
                $Width : '810px'), 'off', null, 6, 3) . " " . $Sys->Notice("V_PWidth") . " " . Lang
                ("Bottom Offset") . " : " . $Sys->Text("BOffset", (!empty($BOffset) ? $BOffset : '5'),
                'off', null, 5, 3) . " <br />" . $Sys->Check("MHPage", 'page', Lang("Minimum page height"),
                (!is_null($MHPage) ? $MHPage : true)),
            Lang("Description") => $Sys->Textarea("Desc", $Desc, null, 50, 3),
            Lang("Page Style") => $Sys->Text("Style", $Style, 'off', null, null, 50),
            Lang("CSS File") => $Sys->Text("CSS", $CSS, 'off', null, null, 50),
            Lang("JS File") => $Sys->Text("JS", $JS, 'off', null, null, 50),
            Lang("Status") => $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"),
                "{SPG_ECWEB}", array(
                'Action' => $UI,
                'UI' => $UI,
                'Grp' => $Grp,
                'ID' => $ID), "#ECWeb_Page_Form", '#Status')), 1, 'ECWeb_Page_Form'), Lang("EasyWeb Composing System")));
        $Sys->WebJs($Sys->Js_ObjFocus("#Code") . $Sys->Js_Validate("#Code", array(array(
                "Method" => "must",
                "Min" => 1,
                "Feedback" => array("#V_Code" => $Sys->Warning("!"))))) . $Sys->Js_Validate("#STitle",
            array(
            "Method" => "must",
            "Min" => 1,
            "Feedback" => array("#V_STitle" => $Sys->Warning("!")))) . $Sys->Js_Validate("#Width",
            array(
            "Method" => "regexp",
            "Extend" => "{json_fnc}/^[+-]?[0-9]+\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/",
            "Min" => 1,
            "Feedback" => array("#V_Height" => $Sys->Warning("Width !")))));
        break;

        #Show Registerd Users List (Default Lister)
    case "ECPage":
        $iPos = (int)0;
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $Responsive = $ECWeb[$Grp]['RESPONSIVE'];
        if (count($ECWeb[$Grp]['PAGES']) > 0)
        {
            if ($Responsive)
            {
                $THCol = array(
                    Lang("No."),
                    Lang("Code"),
                    Lang("Title"),
                    Lang("Description"),
                    Lang("Width"),
                    Lang("Style"),
                    Lang("CSS"),
                    Lang("JS"),
                    $Sys->Normal(Lang("Edit"), 1),
                    $Sys->Warning(Lang("Delete"), 1));
            }
            else
            {
                $THCol = array(
                    Lang("No."),
                    Lang("Code"),
                    Lang("Title"),
                    Lang("Description"),
                    Lang("Width"),
                    Lang("Style"),
                    Lang("CSS"),
                    Lang("JS"),
                    Lang("Desktop"),
                    Lang("Mobile"),
                    Lang("Tablet"),
                    $Sys->Normal(Lang("Edit"), 1),
                    $Sys->Warning(Lang("Delete"), 1));
            }
            $TData = array();
            foreach ($ECWeb[$Grp]['PAGES'] as $ID => $MD)
            {
                $iPos++;
                if ($Responsive)
                {
                    $TData[] = array(
                        $iPos,
                        IfEmptyH($ID),
                        IfEmptyH($MD['TITLE']),
                        IfEmptyH($MD['DESC']),
                        IfEmptyH($MD['WIDTH']),
                        IfEmptyH($MD['STYLE']),
                        IfEmptyH($MD['CSS']),
                        IfEmptyH($MD['JS']),
                        $Sys->Ajax_Link_Fancy($Sys->Normal(Lang("Edit"), 1), "{SPG_ECWEB}", array(
                            'Action' => 'SelPg',
                            'UI' => 'RegPg',
                            'Grp' => $Grp,
                            'ID' => $ID)),
                        $Sys->Ajax_Link_Execute($Sys->Warning(Lang("Delete"), 1), "{SPG_ECWEB}",
                            array(
                            'UI' => 'Confirm_DelPg',
                            'Grp' => $Grp,
                            'ID' => $ID)));
                }
                else
                {
                    $TData[] = array(
                        $iPos,
                        IfEmptyH($ID),
                        IfEmptyH($MD['TITLE']),
                        IfEmptyH($MD['DESC']),
                        IfEmptyH($MD['WIDTH']),
                        IfEmptyH($MD['STYLE']),
                        IfEmptyH($MD['CSS']),
                        IfEmptyH($MD['JS']),
                        (!$MD['HOME']['DESKTOP'] ? $Sys->ActRequest_Link($Sys->Inform(Lang("Set Home"),
                            1), "{SPG_ECWEB}", array(
                            'Action' => 'PgHome',
                            'UI' => $UI,
                            'Grp' => $Grp,
                            'ID' => $ID,
                            'Home' => 'DESKTOP')) : $Sys->
                            Success(Lang("Home Now"), true)),
                        (!$MD['HOME']['MOBILE'] ? $Sys->ActRequest_Link($Sys->Inform(Lang("Set Home")),
                            "{SPG_ECWEB}", array(
                            'Action' => 'PgHome',
                            'UI' => $UI,
                            'Grp' => $Grp,
                            'ID' => $ID,
                            'Home' => 'MOBILE')) : $Sys->
                            Success(Lang("Home  Now"), true)),
                        (!$MD['HOME']['TABLET'] ? $Sys->ActRequest_Link($Sys->Inform(Lang("Set Home"),
                            1), "{SPG_ECWEB}", array(
                            'Action' => 'PgHome',
                            'UI' => $UI,
                            'Grp' => $Grp,
                            'ID' => $ID,
                            'Home' => 'TABLET')) : $Sys->
                            Success(Lang("Home Now"), true)),
                        $Sys->Ajax_Link_Fancy($Sys->Normal(Lang("Edit"), 1), "{SPG_ECWEB}", array(
                            'Action' => 'SelPg',
                            'UI' => 'RegPg',
                            'Grp' => $Grp,
                            'ID' => $ID)),
                        $Sys->Ajax_Link_Execute($Sys->Warning(Lang("Delete"), 1), "{SPG_ECWEB}",
                            array(
                            'UI' => 'Confirm_DelPg',
                            'Grp' => $Grp,
                            'ID' => $ID)));
                }
            }
            $Sys->FREE_RESULT($QLst);
        }
        $Sys->WebPut($Sys->TBTab($Sys->TBGrid($TData, Lang("Element Composing Page List"), $THCol,
            "System_User_Lister", Lang("No any registered user."), null, true), null, $Sys->
            Ajax_Button_Fancy(Lang("Register New Page"), "{SPG_ECWEB}", array('Grp' => $Grp, 'UI' =>
                'RegPg')), Lang("Element Composing System")));
        break;

    default:
        $THCol = array(
            Lang("No."),
            Lang("Code"),
            Lang("Group Name"),
            Lang("Type"),
            Lang("Pages"),
            $Sys->Normal(Lang("Edit"), 1),
            $Sys->Warning(Lang("Delete"), 1));
        $iPos = (int)0;
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        if (count($ECWeb) > 0)
        {
            $TData = array();
            foreach ($ECWeb as $ID => $MD)
            {
                $iPos++;
                $TData[] = array(
                    $iPos,
                    IfEmptyH($ID),
                    IfEmptyH($MD['GNAME']),
                    $MD['RESPONSIVE'] ? $Sys->Success(Lang("Responsive"), true) : $Sys->Normal(Lang
                        ("Normal Site"), true),
                    $Sys->Ajax_Link_Object(Lang("Pages"), "{SPG_ECWEB}", array('Grp' => $ID, 'UI' =>
                            'ECPage'), array("Target" => "td#EcGrp_{$ID}"), "_$('td#EcGrp_{$ID}').ToggleDisplay('table-cell');"),
                    $Sys->Ajax_Link_Fancy($Sys->Normal(Lang("Edit"), 1), "{SPG_ECWEB}", array(
                        'Action' => 'SelGrp',
                        'ID' => $ID,
                        'UI' => 'RegGrp')),
                    $Sys->Ajax_Link_Execute($Sys->Warning(Lang("Delete"), 1), "{SPG_ECWEB}", array('UI' =>
                            'Confirm_DelGrp', 'ID' => $ID)));
                $TData[] = array(array(
                        "id" => "EcGrp_{$ID}",
                        "colspan" => 8,
                        "style" => "display:none;"));
            }
            $Sys->FREE_RESULT($QLst);
        }
        $Sys->WebPut($Sys->TBTab($Sys->TBGrid($TData, Lang("Element Composing Group List"), $THCol,
            "System_Ec_Group", Lang("No any ec group"), null, true), null, $Sys->Ajax_Button_Fancy(Lang
            ("Register New Group"), "{SPG_ECWEB}", array('UI' => 'RegGrp')), Lang("Element Composing System")));
        break;
}
$Sys->WebOut();

?>