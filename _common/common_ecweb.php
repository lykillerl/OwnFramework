<?php

#Create by LYK on 2013-03-29 @ 15:30PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

$Default_Pos = array(
    "SNAP" => 5,
    "X" => 0,
    "Y" => 0,
    "PX" => 0,
    "PY" => 0);

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

function Is_DuplicateID($Code, $Items, $Exception)
{
    if (count($Items) === 0 || $Code === '')
        return false;
    $Result = '';
    $CodeLst = array();
    foreach ($Items as $ID => $Item)
        if ($ID !== $Exception && $Code === $Item['ID'])
            return true;
    return false;
}

function GenID(&$ECWeb)
{
    global $_ECW_GRP, $_ECW_PG;
    while (true)
    {
        $iNow++;
        $Code = Gencode(8);
        if (!array_key_exists($Code, $ECWeb[$ECWeb]['PAGES'][$_ECW_PG]['ITMS']))
            break;
    }
    return strtoupper($Code);
}

function StrAttr($StrAttrib)
{
    $Attrib = array();
    $AttLst = array();
    $StrAry = str_split($StrAttrib);
    $Slash = false;
    $iNow = 0;
    $AttLst[$iNow] = '';
    foreach ($StrAry as $Char)
    {
        if ($Char === '\\' && $Slash === false)
        {
            $Slash = true;
            continue;
        }
        if ($Char === ',')
        {
            if ($Slash === true)
            {
                $Slash = false;
                $AttLst[$iNow] .= $Char;
            }
            else
            {
                $iNow++;
                $AttLst[$iNow] = '';
            }
        }
        else
            $AttLst[$iNow] .= $Char;
    }
    foreach ($AttLst as $Attr)
        if (strpos($Attr, ":") !== (boolean)false)
        {
            list($AttName, $AttValue) = explode(":", $Attr);
            $Attrib[trim($AttName)] = trim($AttValue);
        }
    return $Attrib;
}

function ECWItemName(&$ECWeb, $ItmID, $ShwID = false, $GID = null, $PID = null)
{
    global $_ECW_GRP, $_ECW_PG;
    if ($ItmID === '*' || $ItmID === '')
        return Lang("Main Section Object");
    $GID = (empty($GID) ? $_ECW_GRP : $GID);
    $PID = (empty($PID) ? $_ECW_PG : $PID);
    $ItmName = $ECWeb[$GID]['PAGES'][$PID]['ITMS'][$ItmID]['ID'];
    $ItmName = !empty($ItmName) ? $ItmName : $ItmID;
    if ($ShwID === true)
        $ItmName = $ItmID;
    $ItmName = (($ECWeb[$GID]['PAGES'][$PID]['ITMS'][$ItmID]['TYPE'] === 'SECTION') ? Lang("Section") :
        Lang("Item")) . "[#{$ItmName}]";
    return !empty($ItmName) ? $ItmName : $ItmID;
}

function Resort_Item_Seq(&$ECWeb, $GID = null, $PID = null)
{
    $iPos = 0;
    global $_ECW_GRP, $_ECW_PG;
    $GID = (empty($GID) ? $_ECW_GRP : $GID);
    $PID = (empty($PID) ? $_ECW_PG : $PID);
    $iLayer = (int)0;
    if (array_key_exists($GID, $ECWeb) && array_key_exists($PID, $ECWeb[$GID]['PAGES']))
    {
        foreach ($ECWeb[$GID]['PAGES'][$PID]['ITMS'] as $TID => $Item)
        {
            $iPos++;
            $ECWeb[$GID]['PAGES'][$PID]['ITMS'][$TID]['SEQ'] = $iPos;
            if ($ECWeb[$GID]['PAGES'][$PID]['ITMS'][$TID]['LAYER'] > $iLayer)
                $iLayer = $ECWeb[$GID]['PAGES'][$PID]['ITMS'][$TID]['LAYER'];
        }
        $ECWeb[$GID]['PAGES'][$PID]['LAYERS'] = $iLayer;
    }
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

function Resort_Item(&$ECWeb, $GID = null, $PID = null)
{
    global $_ECW_GRP, $_ECW_PG;
    $GID = (empty($GID) ? $_ECW_GRP : $GID);
    $PID = (empty($PID) ? $_ECW_PG : $PID);
    Resort_Item_Array($ECWeb);
    Resort_Item_Seq($ECWeb);
}

function Template_Import($XHTML)
{
    $Result = $XHTML;
    preg_match_all("/(\n?)<template(.*)?\/>/", $XHTML, $Templates, PREG_OFFSET_CAPTURE, 0);
    if (!empty($Templates[0]))
    {
        foreach ($Templates[2] as $TID => $AtbStr)
        {
            preg_match_all("/([a-z0-9_]+)\s*=\s*[\"\'](.*?)[\"\']/is", $AtbStr[0], $AttribSet);
            if (!empty($AttribSet[0]))
            {
                $Attribue = array();
                foreach ($AttribSet[1] as $Key => $Name)
                    $Attribue[strtolower($Name)] = $AttribSet[2][$Key];
                if (array_key_exists('src', $Attribue))
                {
                    $Data = array_key_exists('data', $Attribue) ? strtolower($Attribue['data']) : "";
                    $Type = array_key_exists('type', $Attribue) ? strtolower($Attribue['type']) : "";
                    parse_str($Data);
                    $Template = Exec_File("{SPTHR_TEMPLATE}{$Attribue['src']}");
                    unset($Attribue['src']);
                    unset($Attribue['type']);
                    unset($Attribue['data']);
                    if (!empty($Type))
                    {
                        $Result = "<{$Type}";
                        if (!empty($Attribue))
                        {
                            foreach ($Attribue as $PName => $PValue)
                                $Result .= " {$PName}=\"{$PValue}\"";
                        }
                        $Result .= ">{$Template}</{$Type}>";
                    }
                    else
                        $Result = "{$Template}";
                    $XHTML = substr_replace($XHTML, $Result, $Templates[0][$TID][1], strlen($Templates[0][$TID][0]));
                }
                else
                    $XHTML = substr_replace($XHTML, "", $Templates[0][$TID][1], strlen($Templates[0][$TID][0]));
            }
            else
                $XHTML = substr_replace($XHTML, "", $Templates[0][$TID][1], strlen($Templates[0][$TID][0]));
        }
    }
    return $XHTML;
}

############################################################################################################
# End Function of ECWeb
############################################################################################################

switch ($Action)
{
        #Web Section Register
    case "RegSec":
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $SecID = Fix_CodeID($SecID);
        if (empty($_ECW_GRP) || empty($_ECW_PG))
        {
            $UI = "WebMsg";
            $Status = $Sys->Warning(Lang("Please select a page before you start."), 1);
        }
        elseif (Is_DuplicateID($SecID, $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'], $ID))
            $Status = $Sys->Warning(Lang("ECWeb Element ID is already exists."), 1);
        else
        {
            $UI = "WebMsg";
            $Parent = (!empty($Parent) ? $Parent : "*");
            $Title = Lang("System Status");
            $Style = str_replace(array(
                "\r",
                "\n",
                "\r\n"), " ", $Style);
            $Style = str_replace("\"", "\\\"", $Style);
            $Size = array("WIDTH" => (is_numeric($Width) ? "{$Width}px" : $Width), "HEIGHT" => (is_numeric
                    ($Height) ? "{$Height}px" : $Height));
            if (!empty($ID))
            {
                $Temp = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];
                if ($Temp['ACCORDING']['X'] !== $XAccording)
                {
                    $Temp['ACCORDING']['X'] = $XAccording;
                    $Temp['POS']['X'] = 0;
                }

                if ($Temp['ACCORDING']['Y'] !== $YAccording)
                {
                    $Temp['ACCORDING']['Y'] = $YAccording;
                    $Temp['POS']['Y'] = 0;
                }
                $Temp['ID'] = $SecID;
                $Temp['ALIGN'] = $Align;
                $Temp['POS']['SNAP'] = (int)$Snap;
                $Temp['SIZE'] = $Size;
                $Temp['STYLE'] = $Style;
                $Temp['CLASS'] = $ObjCls;
                $Temp['HREF'] = $Href;
                $Temp['ATTRIB'] = $Attrib;
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID] = $Temp;
            }
            else
            {
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][GenID($ECWeb)] = array(
                    "ACCORDING" => array("X" => $XAccording, "Y" => $YAccording),
                    "ALIGN" => $Align,
                    "ATTRIB" => $Attrib,
                    "CLASS" => $ObjCls,
                    "HREF" => $Href,
                    "ID" => $SecID,
                    "LAYER" => ($Parent === '*' ? 0 : $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['LAYER'] +
                        1),
                    "PARENT" => $Parent,
                    "POS" => array(
                        "SNAP" => (int)$Snap,
                        "X" => 0,
                        "Y" => 0,
                        "PX" => 0,
                        "PY" => 0),
                    "SEQ" => count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) + 1,
                    "SIZE" => $Size,
                    "STYLE" => $Style,
                    "TYPE" => "SECTION");
                Resort_Item($ECWeb, $SecID);
            }
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable ") . (!
                    empty($ID) ? Lang("update the the section.") : Lang("create a new section."))));
            else
                $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                        array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
        }
        break;

        #ECWeb Item Register
    case "RegItm":
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $ItmID = Fix_CodeID($ItmID);
        if (empty($_ECW_GRP) || empty($_ECW_PG) || empty($Parent))
            $Sys->Json_Respone('error', $Sys->Warning(Lang("Please select a page and section")));
        elseif (Is_DuplicateID($ItmID, $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'], $ID))
            $Sys->Json_Respone('respone', $Sys->Warning(Lang("ECWeb Item ID is already exists.")));
        else
        {
            $UI = "WebMsg";
            $Title = Lang("System Status");
            $Style = str_replace(array(
                "\r",
                "\n",
                "\r\n"), " ", $Style);
            $Style = str_replace("\"", "\\\"", $Style);
            $Size = array("WIDTH" => (is_numeric($Width) ? "{$Width}px" : $Width), "HEIGHT" => (is_numeric
                    ($Height) ? "{$Height}px" : $Height));
            if (!empty($ID))
            {
                $Temp = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];

                if ($Temp['ACCORDING']['X'] !== $XAccording)
                {
                    $Temp['ACCORDING']['X'] = $XAccording;
                    $Temp['POS']['X'] = 0;
                }

                if ($Temp['ACCORDING']['Y'] !== $YAccording)
                {
                    $Temp['ACCORDING']['Y'] = $YAccording;
                    $Temp['POS']['Y'] = 0;
                }
                $Temp['ID'] = $ItmID;
                $Temp['CONTENT'] = $Content;
                $Temp['POS']['SNAP'] = (int)$Snap;
                $Temp['SIZE'] = $Size;
                $Temp['STYLE'] = $Style;
                $Temp['CLASS'] = $ObjCls;
                $Temp['HREF'] = $Href;
                $Temp['ATTRIB'] = $Attrib;
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID] = $Temp;
            }
            else
            {
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][GenID($ECWeb)] = array(
                    "ACCORDING" => array("X" => $XAccording, "Y" => $YAccording),
                    "ATTRIB" => $Attrib,
                    "CLASS" => $ObjCls,
                    "CONTENT" => $Content,
                    "HREF" => $Href,
                    'ID' => $ItmID,
                    "LAYER" => ($Parent === '*' ? 0 : $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['LAYER'] +
                        1),
                    "PARENT" => $Parent,
                    "POS" => array(
                        "SNAP" => (int)$Snap,
                        "X" => 0,
                        "Y" => 0,
                        "PX" => 0,
                        "PY" => 0),
                    "SEQ" => count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) + 1,
                    "SIZE" => $Size,
                    "STYLE" => $Style,
                    'TYPE' => 'ITEM');
                Resort_Item($ECWeb, $ItmID);
            }
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable ") . (!
                    empty($ID) ? Lang("update the the item.") : Lang("create a new item."))));
            else
                $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                        array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
        }
        break;

    case "UptBgImg":
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $ImgInfo = @getimagesize($_FILES["Photo"]["tmp_name"]);
        if (empty($_ECW_GRP) || empty($_ECW_PG) || empty($ID) || !array_key_exists('Photo', $_FILES))
            $Status = $Sys->Warning(Lang("Sorry, but no photo was uploaded."), 1);
        elseif (!array_key_exists($ID, $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']))
            $Status = $Sys->Warning(Lang("No Section/Item are selected."), 1);
        if ($ImgInfo === false)
            $Status = $Sys->Warning(Lang("Upload File only accept standard image (*).png, (*).jpg, (*).gif"),
                1);
        else
        {
            $FName = "{$Sys_Path['REAL']['IMAGE']}{$_FILES["Photo"]["name"]}";
            move_uploaded_file($_FILES["Photo"]["tmp_name"], "{$Sys_Path['REAL']['IMAGE']}{$_FILES["Photo"]["name"]}");
            chmod($FName, 0770);
            if ($ECWeb[$_ECW_GRP]['RESPONSIVE'])
            {
                $Parent = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['PARENT'];
                $PWidth = (double)$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['WIDTH'];
                if ($Parent !== '*')
                {
                    while ($Parent !== '*')
                    {
                        $SParent[] = $Parent;
                        $Parent = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['PARENT'];
                    }
                    $SParent = array_reverse($SParent);
                    foreach ($SParent as $Parent)
                        $PWidth = ($PWidth / 100) * (double)$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['SIZE']['WIDTH'];
                }
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SIZE']["WIDTH"] = (($ImgInfo[0] /
                    $PWidth) * 100) . "%";
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SIZE']["HEIGHT"] = round(($ImgInfo[1] /
                    $ImgInfo[0]) * 100) . "%";
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['STYLE'] =
                    "background:url('images/" . rawurlencode($_FILES["Photo"]["name"]) .
                    "') no-repeat; background-size:cover;";
            }
            else
            {
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SIZE']["WIDTH"] = "{$ImgInfo[0]}px";
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SIZE']["HEIGHT"] = "{$ImgInfo[1]}px";
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['STYLE'] =
                    "background: url('images/" . rawurlencode($_FILES["Photo"]["name"]) .
                    "') no-repeat;";
            }
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Status = $Sys->Warning(Lang("Sorry, currently unable update the item/section background image."),
                    1);
        }
        break;

    case "TransItm":
        if (empty($_ECW_GRP) || empty($_ECW_PG) || empty($Parent) || empty($ID))
            $Sys->Json_Respone('error', $Sys->Unknow(Lang("Please select a section to transfer you item/section.")));
        else
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $TempItm = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];
            $TempItm['PARENT'] = $Parent;
            $TempItm['SEQ'] = count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) + 1;
            $TempItm['LAYER'] = ($Parent === '*' ? 0 : $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['LAYER'] +
                1);
            $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID] = $TempItm;
            Resort_Item($ECWeb, $ID);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable transfer the section/item.")));
            else
                $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                        array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
        }
        break;

        #Select Element Profile
    case "SelSec":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $Temp = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];
            $SecID = $Temp['ID'];
            $ObjCls = $Temp['CLASS'];
            $Align = $Temp['ALIGN'];
            $XAccording = $Temp['ACCORDING']['X'];
            $YAccording = $Temp['ACCORDING']['Y'];
            $Size = $Temp['SIZE'];
            $Style = $Temp['STYLE'];
            $Href = $Temp['HREF'];
            $Attrib = $Temp['ATTRIB'];
            $Width = $Size['WIDTH'];
            $Height = $Size['HEIGHT'];
            $Parent = $Temp['PARENT'];
        }
        break;

        #Select Element Profile
    case "SelItm":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $Temp = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];
            $ItmID = $Temp['ID'];
            $ObjCls = $Temp['CLASS'];
            $XAccording = $Temp['ACCORDING']['X'];
            $YAccording = $Temp['ACCORDING']['Y'];
            $Content = $Temp['CONTENT'];
            $Size = $Temp['SIZE'];
            $Style = $Temp['STYLE'];
            $Href = $Temp['HREF'];
            $Attrib = $Temp['ATTRIB'];
            $Width = $Size['WIDTH'];
            $Height = $Size['HEIGHT'];
            $Snap = $Size['SNAP'];
            $Parent = $Temp['PARENT'];
        }
        break;

        #Copy Element
    case "CpySec":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $NewItms = array();
            $Parent = '';
            $NewID = GenID($ECWeb);
            if ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['TYPE'] !== 'SECTION')
                exit();
            if ($Mode === 'Insert')
                $SEQ = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SEQ'] + 0.5;
            else
                $SEQ = count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) + 1;
            $NewItm = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];
            $NewItm['ID'] = '';
            $NewItm['SEQ'] = $SEQ;
            $NewItms[$NewID] = $NewItm;

            function ECWebCpySec(&$ECWeb, &$NewItms, $SecID, $NewSecID)
            {
                global $_ECW_GRP, $_ECW_PG;
                foreach ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'] as $ItmID => $Item)
                    if ($Item['PARENT'] === $SecID)
                        switch ($Item['TYPE'])
                        {
                            case "ITEM":
                                $NewItm = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ItmID];
                                $NewItm['ID'] = '';
                                $NewItm['PARENT'] = $NewSecID;
                                $NewItms[GenID($ECWeb)] = $NewItm;
                                break;

                            case "SECTION":
                                $NewID = GenID($ECWeb);
                                $NewItm = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ItmID];
                                $NewItm['ID'] = '';
                                $NewItm['PARENT'] = $NewSecID;
                                $NewItms[$NewID] = $NewItm;
                                ECWebCpySec($ECWeb, $NewItms, $ItmID, $NewID);
                                break;
                        }
            }

            ECWebCpySec($ECWeb, $NewItms, $ID, $NewID);
            $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'] += $NewItms;
            Resort_Item($ECWeb, $NewID);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable duplicate new section item.")));
            else
                $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                        array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
        }
        break;

        #Copy Element
    case "CpyItm":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($Parent) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $iNow = 0;
            $NewID = GenID($ECWeb);
            $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$NewID] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID];
            $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$NewID]['ID'] = '';
            if ($Mode === 'Insert')
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$NewID]['SEQ'] += 0.5;
            else
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$NewID]['SEQ'] = count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) +
                    1;
            Resort_Item($ECWeb, $NewID);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable duplicate new item.")));
            else
                $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                        array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
        }
        break;

    case "ItmPos":
        if (!empty($ID) && !empty($_ECW_GRP) && !empty($_ECW_PG) && is_numeric($X) && is_numeric($Y) &&
            is_numeric($PX) && is_numeric($PY))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            if ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['TYPE'] === 'ITEM' || ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['TYPE']
                === 'SECTION' && $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['ALIGN'] ===
                'move'))
            {
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['POS'] = array(
                    "SNAP" => (int)$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['POS']['SNAP'],
                    "X" => (double)$X,
                    "Y" => (double)$Y,
                    "PX" => (double)$PX,
                    "PY" => (double)$PY);
                if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                    $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable update the item position.")));
            }
        }
        break;

    case "ItmDim":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($ID) && !empty($Width) && !empty($Height))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $Error = false;
            $Width = (int)$Width;
            $Height = (int)$Height;
            if (!empty($ID) && !empty($Width) && !empty($Height))
            {
                $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SIZE'] = array('WIDTH' => $Width,
                        'HEIGHT' => $Height);
                if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                    $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable update the item dimension.")));
            }
        }
        break;

    case "Layer":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($Type) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            switch (strtolower($Type))
            {
                case "back":
                    $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SEQ'] = 0;
                    break;

                case "backward":
                    $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SEQ'] -= 1.5;
                    break;

                case "forward":
                    $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SEQ'] += 0.5;

                case "front":
                    $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['SEQ'] = count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) +
                        1;
                    break;
            }
            Resort_Item($ECWeb, $ID);
            if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable update the item layer.")));
        }
        break;

    case "DelItm":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            if ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['TYPE'] === 'ITEM')
            {
                unset($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]);
                Resort_Item($ECWeb, $ID);
                if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                    $Sys->Json_Respone('error', $Sys->Warning(Lang("Failed to delete the item.")));
                else
                    $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                            array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
            }
        }
        break;

    case "DelSec":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG) && !empty($ID))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            if ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['TYPE'] === 'SECTION')
            {
                function ECWebDelSec(&$ECWeb, $SecID)
                {
                    global $_ECW_GRP, $_ECW_PG;
                    unset($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$SecID]);
                    foreach ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'] as $ItmID => $Item)
                        if ($Item['PARENT'] === $SecID)
                            switch ($Item['TYPE'])
                            {
                                case "ITEM":
                                    unset($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ItmID]);
                                    break;
                                case "SECTION":
                                    ECWebDelSec($ECWeb, $ItmID);
                                    break;
                            }
                }
                ECWebDelSec($ECWeb, $ID);
                Resort_Item_Array($ECWeb);
                if (!Data_To_File("{DATA_ECWEB}", $ECWeb))
                    $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, currently unable delete the section")));
                else
                    $Sys->Json_Respone('done', null, array("Execute" => $Sys->Js_Ajax_Function("{SPG_FECWEB}",
                            array("UI" => "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render"))));
            }
        }
        break;
}
$Sys->DBTranEnd();

switch ($UI)
{
    case "WebMsg":
        if ($Reload === true)
            $Sys->WebJs($Sys->Js_Ajax_Function("{SPG_FECWEB}", array("UI" => "ECWebLst"), array("Target" =>
                    "{json_fnc}_ECW.Render")));
        if (!empty($Status))
        {
            $Title = (!empty($Title) ? $Title : Lang('System Status'));
            $Sys->WebJs($Sys->Js_MsgBox($Status, $Title));
        }
        $Sys->WebJs($Sys->Js_Fancy_Close());
        break;

    case "BgImgMsg":
        $Sys->WebPut("<script>window.parent._$().Fancy.Close();window.parent." . $Sys->
            Js_Ajax_Function("{SPG_FECWEB}", array("UI" => "ECWebLst"), array("Target" => "{json_fnc}window.parent._ECW.Render")));
        if (!empty($Status))
        {
            $Title = (!empty($Title) ? $Title : Lang('System Status'));
            $Sys->WebPut("window.parent." . $Sys->Js_MsgBox($Status, $Title));
        }
        $Sys->WebPut("</script>");
        break;

    case "Confirm_DelItm":
        if (!empty($ID) && !empty($_ECW_PG) && !empty($_ECW_GRP))
        {
            $Sys->WebPut($Sys->Js_AskBox(Lang("Are you sure want to delete this item ?"), array(Lang
                    ("Delete") => $Sys->Link_ActReqeust("{SPG_FECWEB}", array("Action" => "DelItm",
                        "ID" => $ID))), Lang("Delete Item")));
        }
        break;

    case "Confirm_DelSec":
        if (!empty($ID) && !empty($_ECW_PG) && !empty($_ECW_GRP))
        {
            $Sys->WebPut($Sys->Js_AskBox(Lang("Are you sure want to delete the section element ?"),
                array(Lang("Delete") => $Sys->Link_ActReqeust("{SPG_FECWEB}", array("Action" =>
                        "DelSec", "ID" => $ID))), Lang("Delete Section")));
        }
        break;

    case "RegSec":
        if (empty($_ECW_GRP) || empty($_ECW_PG))
            $Sys->WebJs($Sys->Js_MsgBox($Sys->Warning(Lang("Please select a page before you start."),
                1), "System Status") . $Sys->Js_Fancy_Close());
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("EasyWeb Composing System")));
        $SMode = (!empty($ID) ? $Sys->Inform($Sys->Strong(Lang("Modify Mode"))) : $Sys->Success(Lang
            ("Add New"), 1));
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $Parent = (!empty($Parent) ? $Parent : '*');
        $PCode = ($Parent !== '*') ? "#{$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['ID']}" :
            Lang("Main Object");
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("EasyWeb Section Register Form"), array(
            Lang("System Mode") => $SMode,
            null,
            Lang("Parent") => $Sys->Strong($Sys->Normal(ECWItemName($ECWeb, $Parent))),
            Lang("Section ID") => $Sys->Strong($Sys->Success(ECWItemName($ECWeb, $ID, true))),
            Lang("Object ID") => $Sys->Text("SecID", $SecID, 'off'),
            Lang("Section Class") => $Sys->Text("ObjCls", $ObjCls, 'off', null, 50, 20),
            Lang("Position Method") => $Sys->SelStr("Align", array(
                "left" => Lang("Align Left"),
                "right" => Lang("Align Right"),
                "move" => Lang("Movable")), (!empty($Align) ? $Align : 'left')),
            Lang("Size") => Lang("Width") . " : " . $Sys->Text("Width", $Width, 'off', null, 10, 5) .
                $Sys->Notice("V_Width") . " " . Lang("Height") . " : " . $Sys->Text("Height", $Height,
                'off', null, 10, 5) . $Sys->Notice("V_Height"),
            Lang("Move According") => $Sys->SelStr("XAccording", array("left" => Lang("Left"),
                    "right" => Lang("Right")), (!empty($XAccording) ? $XAccording : 'left')) . $Sys->
                SelStr("YAccording", array("top" => Lang("Top"), "bottom" => Lang("Bottom")), (!
                empty($YAccording) ? $YAccording : 'top')) . " " . Lang("Snap") . " : " . $Sys->
                SelNum("Snap", 0, 10, (is_numeric($Snap) ? $Snap : 5)),
            null,
            Lang("Inline Style") => $Sys->Text("Style", $Style, 'off', null, null, 80),
            null,
            Lang("Hyper Link") => $Sys->Text("Href", $Href, 'off', null, null, 80),
            null,
            Lang("Attribute") => $Sys->Text("Attrib", $Attrib, 'off', null, null, 80),
            null,
            Lang("Status") => $Sys->Span("Status", $Status),
            null), array($Sys->ActRequest_Button(Lang("Submit"), "{SPG_FECWEB}", array(
                'Action' => 'RegSec',
                'UI' => $UI,
                'Parent' => $Parent,
                'ID' => $ID),  "#ECWeb_Section_Form", "#Status")),
            2, 'ECWeb_Section_Form'), Lang("EasyWeb Composing System")));
        $Sys->WebJs($Sys->Js_ObjFocus("#SecID") . $Sys->Js_Validate("#Width", array(
            "Method" => "regexp",
            "Extend" => "{json_fnc}/^[+-]?[0-9]+\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/",
            "Min" => 1,
            "Feedback" => array("#V_Width" => $Sys->Warning("Width !")))) . $Sys->Js_Validate("#Height",
            array(
            "Method" => "regexp",
            "Extend" => "{json_fnc}/^[+-]?[0-9]+\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/",
            "Min" => 1,
            "Feedback" => array("#V_Height" => $Sys->Warning("Height !")))));
        break;

    case "RegItm":
        if (empty($_ECW_GRP) || empty($_ECW_PG))
            $Sys->WebJs($Sys->Js_MsgBox($Sys->Warning(Lang("Please select a page and section before you start."),
                1), "System Status") . $Sys->Js_Fancy_Close());
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("EasyWeb Composing System")));
        $SMode = (!empty($ID) ? $Sys->Inform($Sys->Strong(Lang("Modify Mode"))) : $Sys->Success(Lang
            ("Add New"), 1));
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $Parent = (!empty($Parent) ? $Parent : '*');
        $PCode = ($Parent === '*') ? "#{$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$Parent]['ID']}" :
            Lang("Main Object");
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("EasyWeb Item Register Form"), array(
            Lang("System Mode") => $SMode,
            null,
            Lang("Parent") => $Sys->Strong($Sys->Normal(ECWItemName($ECWeb, $Parent))),
            Lang("Item ID") => $Sys->Strong($Sys->Success(ECWItemName($ECWeb, $ID, true))),
            Lang("Object ID") => $Sys->Text("ItmID", $ItmID, 'off', null, 50, 10),
            Lang("Class") => $Sys->Text("ObjCls", $ObjCls, 'off', null, 50, 10),
            Lang("Move According") => $Sys->SelStr("XAccording", array("left" => Lang("Left"),
                    "right" => Lang("Right")), (!empty($XAccording) ? $XAccording : 'left')) . $Sys->
                SelStr("YAccording", array("top" => Lang("Top"), "bottom" => Lang("Bottom")), (!
                empty($YAccording) ? $YAccording : 'top')) . " " . Lang("Snap") . " : " . $Sys->
                SelNum("Snap", 0, 10, (is_numeric($Snap) ? $Snap : 5)),
            Lang("Size") => Lang("Width") . " : " . $Sys->Text("Width", $Width, 'off', null, 10, 5) .
                $Sys->Notice("V_Width") . " " . Lang("Height") . " : " . $Sys->Text("Height", $Height,
                'off', null, 10, 5) . $Sys->Notice("V_Height"),
            Lang("Inline Style") => $Sys->Text("Style", $Style, 'off', null, null, 80),
            null,
            Lang("Content") => $Sys->Textarea("Content", htmlspecialchars($Content), null, 80, 10),
            null,
            Lang("Hyper Link") => $Sys->Text("Href", $Href, 'off', null, null, 80),
            null,
            Lang("Attribute") => $Sys->Text("Attrib", $Attrib, 'off', null, null, 80),
            null,
            Lang("Status") => $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"), "{SPG_FECWEB}",
                array(
                'Action' => 'RegItm',
                'UI' => $UI,
                'Parent' => $Parent,
                'ID' => $ID), "#ECWeb_Item_Form", "#Status")), 2,
            'ECWeb_Item_Form'), Lang("EasyWeb Composing System")));
        $Sys->WebJs($Sys->Js_ObjFocus("#ItmID") . $Sys->Js_Validate("#Width", array(
            "Target" => "#Width",
            "Method" => "regexp",
            "Extend" => "{json_fnc}/^[+-]?[0-9]+\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/",
            "Min" => 1,
            "Feedback" => array("#V_Width" => $Sys->Warning("Width !")))) . $Sys->Js_Validate("#Height",
            array(
            "Target" => "#Height",
            "Method" => "regexp",
            "Extend" => "{json_fnc}/^[+-]?[0-9]+\.?([0-9]+)?(px|em|ex|%|in|cm|mm|pt|pc)$/",
            "Min" => 1,
            "Feedback" => array("#V_Height" => $Sys->Warning("Height !")))));
        break;

    case "TransItm":
        if (empty($_ECW_GRP) || empty($_ECW_PG) || empty($ID))
            $Sys->WebJs($Sys->Js_MsgBox($Sys->Warning(Lang("Please select an item to transfer."), 1),
                "System Status") . $Sys->Js_Fancy_Close());
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $SecSel = array('' => "- Select One -", "*" => Lang("Main Object"));
        $Parent = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'][$ID]['PARENT'];
        foreach ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'] as $ItmID => $Item)
            if ($Item['TYPE'] === 'SECTION' && $ItmID !== $Parent)
                $SecSel[$ItmID] = ECWItemName($ECWeb, $ItmID);
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("EasyWeb Composing System")));
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("EasyWeb Transfer Section"), array(Lang("Section") =>
                $Sys->SelStr("Parent", $SecSel, '') . $Sys->Notice("V_TransSec"), Lang("Status") =>
                $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"), "{SPG_FECWEB}", array(
                'Action' => $UI,
                'UI' => $UI,
                'ID' => $ID), "#ECWeb_Transfer_Form", "#Status")), 1, 'ECWeb_Transfer_Form'),
            Lang("EasyWeb Composing System")));
        $Sys->WebJs($Sys->Js_ObjFocus("#TransSec") . $Sys->Js_Validate("#TransSec", array(
            "Method" => "must",
            "Min" => 1,
            "Feedback" => array("#V_TransSec" => $Sys->Warning("!")))));
        break;

    case "UptBgImg":
        if (empty($_ECW_GRP) || empty($_ECW_PG) || empty($ID))
            $Sys->WebJs($Sys->Js_MsgBox($Sys->Warning(Lang("Please select an item to transfer."), 1),
                "System Status") . $Sys->Js_Fancy_Close());
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("EasyWeb Composing System")));
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        $Sys->WebPut($Sys->Box($Sys->FORM(array("Token" => $Sys->Token(array(
                'Action' => $UI,
                "_ECW_GRP" => $_ECW_GRP,
                "_ECW_PG" => $_ECW_PG,
                "UI" => "BgImgMsg",
                'ID' => $ID), 2)), "{SPG_FECWEB}", "ecweb_photo", null, "multipart/form-data", $Sys->
            ObjEvent("onsubmit", "return _$().Validate.Check();")) . $Sys->Build_Form(Lang("EasyWeb Transfer Section"),
            array(
            Lang("Item") => $Sys->Strong($Sys->Success(ECWItemName($ECWeb, $ID))),
            Lang("Photo") => $Sys->File("Photo", null) . $Sys->Notice("V_Photo"),
            Lang("Status") => $Status), array($Sys->Submit(Lang("Submit"))), 1,
            'ECWeb_Transfer_Form'), Lang("EasyWeb Composing System")) . $Sys->EForm() . "<iframe id=\"ecweb_photo\" name=\"ecweb_photo\" style=\"border:none; position:absolute; left:-9999px; top:-9999px; width:0px; height:0px;\"></frame>");
        $Sys->WebJs($Sys->Js_ObjFocus("#Photo") . $Sys->Js_Validate("#Photo", array(
            "Target" => "#Photo",
            "Method" => "must",
            "Min" => 1,
            "Feedback" => array("#V_Photo" => $Sys->Warning("!")))));
        break;

    case "ECMenu":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG))
        {
            switch (strtolower($Type))
            {
                case "main":
                    $MenuObj = array(
                        Lang("Create Section") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}", array("UI" =>
                                "RegSec")),
                        Lang("Create Item") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}", array("UI" =>
                                "RegItm")),
                        Lang("Reload Page") => $Sys->Link_Ajax("function", "{SPG_FECWEB}", array("UI" =>
                                "ECWebLst"), array("Target" => "{json_fnc}_ECW.Render")),
                        Lang("Choose Page") => "function:_ECW.Edit");
                    break;

                case "section":
                    if (!empty($Parent) && !empty($Item))
                    {
                        $MenuObj = array(
                            Lang("Update Section") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}",
                                array(
                                "Action" => "SelSec",
                                "UI" => "RegSec",
                                "Parent" => $Parent,
                                "ID" => $Item)),
                            Lang("Create Item") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}", array("UI" =>
                                    "RegItm", "Parent" => $Item)),
                            Lang("Create Section") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}",
                                array("UI" => "RegSec", "Parent" => $Item)),
                            Lang("Set Background Image") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}",
                                array("UI" => "UptBgImg", "ID" => $Item)),
                            Lang("Transfer Section") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}",
                                array(
                                "UI" => "TransItm",
                                "Parent" => $Parent,
                                "ID" => $Item)),
                            Lang("Duplicate Section") => $Sys->Link_ActReqeust("{SPG_FECWEB}",
                                array(
                                "Action" => "CpySec",
                                "UI" => "WebMsg",
                                "ID" => $Item)),
                            Lang("Resolve Item Position") => "function:_ECW.Resolve.Position::{$Item}",
                            Lang("Bring to Front") => "function:_ECW.Layer::Front::{$Item}",
                            Lang("Bring Forward") => "function:_ECW.Layer::Forward::{$Item}",
                            Lang("Send Backward") => "function:_ECW.Layer::Backward::{$Item}",
                            Lang("Send to Back") => "function:_ECW.Layer::Back::{$Item}",
                            Lang("Delete Section") => $Sys->Link_Ajax("AExec", "{SPG_FECWEB}", array
                                ("UI" => "Confirm_DelSec", "ID" => $Item)));
                    }
                    break;

                case "item":
                    if (!empty($Parent) && !empty($Item))
                    {
                        $MenuObj = array(
                            Lang("Update Item") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}", array(
                                "Action" => "SelItm",
                                "UI" => "RegItm",
                                "Parent" => $Parent,
                                "ID" => $Item)),
                            Lang("Duplicate Item") => $Sys->Link_ActReqeust("{SPG_FECWEB}", array
                                ("Action" => "CpyItm",
                                "UI" => "WebMsg",
                                "Parent" => $Parent,
                                "ID" => $Item)),
                            Lang("Set Background Image") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}",
                                array("UI" => "UptBgImg", "ID" => $Item)),
                            Lang("Transfer Item") => $Sys->Link_Ajax("AFancy", "{SPG_FECWEB}", array
                                (
                                "UI" => "TransItm",
                                "Parent" => $Parent,
                                "ID" => $Item)),
                            Lang("Bring to Front") => "function:_ECW.Layer::Front::{$Item}",
                            Lang("Bring Forward") => "function:_ECW.Layer::Forward::{$Item}",
                            Lang("Send Backward") => "function:_ECW.Layer::Backward::{$Item}",
                            Lang("Send to Back") => "function:_ECW.Layer::Back::{$Item}",
                            Lang("Delete Item") => $Sys->Link_Ajax("AExec", "{SPG_FECWEB}", array(
                                "UI" => "Confirm_DelItm",
                                "Parent" => $Parent,
                                "ID" => $Item)));
                    }
                    break;
            }
        }
        $Sys->WebPut(json_encode($MenuObj));
        break;

    case "ECWebLst":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $_EcItms = array();
            if (count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) > 0)
            {
                foreach ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'] as $ID => $Item)
                {
                    if ($Item['TYPE'] === "SECTION")
                        $EcItms[$ID] = array(
                            'id' => $Item['ID'],
                            'Type' => $Item['TYPE'],
                            "Align" => $Item['ALIGN'],
                            "According" => $Item['ACCORDING'],
                            "Pos" => $Item['POS'],
                            "Dimension" => $Item['SIZE'],
                            "Class" => $Item['CLASS'],
                            "Style" => $Item['STYLE'],
                            "Layer" => $Item['LAYER'],
                            "Parent" => $Item['PARENT']);
                    elseif ($Item['TYPE'] === "ITEM")
                        $EcItms[$ID] = array(
                            'id' => $Item['ID'],
                            'Type' => $Item['TYPE'],
                            "According" => $Item['ACCORDING'],
                            "Pos" => $Item['POS'],
                            "Dimension" => $Item['SIZE'],
                            "Class" => $Item['CLASS'],
                            "Style" => $Item['STYLE'],
                            "Content" => $Item['CONTENT'],
                            "Layer" => $Item['LAYER'],
                            "Parent" => $Item['PARENT']);
                }
            }
            else
            {
                $SecID = Gencode(8);
                $ItmID = Gencode(8);
                $EcItms[$SecID] = array(
                    "id" => Gencode(8),
                    "Type" => "SECTION",
                    "Align" => 'left',
                    "Dimension" => false,
                    "Attrib" => array('ecweb' => 'main'),
                    "Style" => "margin:1%; width:98%; height:152px;",
                    "Layer" => 0,
                    "Parent" => "*");
                $EcItms[$ItmID] = array(
                    "id" => Gencode(8),
                    "Type" => "ITEM",
                    "According" => array("X" => "left", "Y" => "top"),
                    "Pos" => $Default_Pos,
                    "Dimension" => false,
                    "Attrib" => array('ecweb' => 'main'),
                    "Style" => "display:block; width:85%; padding:6%; font-weight:bold; font-size:20pt; color:#888; border:10px solid #888; text-align:center;",
                    "Content" => Lang("Create Section to start :D"),
                    "Layer" => 1,
                    "Parent" => $SecID);
            }
        }
        else
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $GSel = array('' => "- Select One -");
            if (count($ECWeb) > 0)
            {
                foreach ($ECWeb as $Grp => $Group)
                {
                    if (count($ECWeb[$Grp]['PAGES']) > 0)
                    {
                        $GSel[$Group['GNAME']] = array();
                        foreach ($ECWeb[$Grp]['PAGES'] as $Pg => $Page)
                            $GSel[$Group['GNAME']]["{$Grp}-{$Pg}"] = $Page['TITLE'];
                    }
                }
            }
            $SecID = Gencode(8);
            $ItmID = Gencode(8);
            $EcItms[$SecID] = array(
                "id" => Gencode(8),
                "Type" => "SECTION",
                "Align" => 'left',
                "Dimension" => false,
                "Attrib" => array('ecweb' => 'main'),
                "Style" => "margin:1%; width:98%; height:281px;",
                "Layer" => 0,
                "Parent" => "*");
            $EcItms[$ItmID] = array(
                "id" => Gencode(8),
                "Type" => "ITEM",
                "According" => array("X" => "left", "Y" => "top"),
                "Pos" => $Default_Pos,
                "Dimension" => false,
                "Attrib" => array('ecweb' => 'main'),
                "Style" => "display:block; width:86%; padding:5.6%; color:#000; border:10px solid #888; text-align:center;",
                "Content" => "<h1>" . Lang("ECWeb Page Select") . "</h1><hr />" . Lang("Page") .
                    " : " . $Sys->GSelStr("SelPg", $GSel, 0) . "<p style=\"text-align: center;\">" .
                    $Sys->JS_Button(Lang("Start Edit"), "_ECW.Edit(_$('#SelPg').Value());") . "</p>",
                "Layer" => 1,
                "Parent" => $SecID);
        }

        $Sys->WebPut(json_encode($EcItms));
        break;

    case "ECWebShw":
        if (!empty($_ECW_GRP) && !empty($_ECW_PG))
        {
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            $EcItms = array();
            if (count($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS']) > 0)
            {
                foreach ($ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['ITMS'] as $ID => $Item)
                {
                    $Attrib = StrAttr($Item['ATTRIB']);
                    if (!empty($Item['HREF']))
                        $Attrib['href'] = $Item['HREF'];

                    if ($Item['TYPE'] === "SECTION")
                        $EcItms[$ID] = array(
                            'id' => $Item['ID'],
                            'Type' => $Item['TYPE'],
                            "Class" => $Item['CLASS'],
                            "Align" => $Item['ALIGN'],
                            "According" => $Item['ACCORDING'],
                            "Pos" => $Item['POS'],
                            "Dimension" => $Item['SIZE'],
                            "Attrib" => $Attrib,
                            "Style" => $Item['STYLE'],
                            "Layer" => $Item['LAYER'],
                            "Parent" => $Item['PARENT']);
                    elseif ($Item['TYPE'] === "ITEM")
                        $EcItms[$ID] = array(
                            'id' => $Item['ID'],
                            'Type' => $Item['TYPE'],
                            "Class" => $Item['CLASS'],
                            "According" => $Item['ACCORDING'],
                            "Pos" => $Item['POS'],
                            "Dimension" => $Item['SIZE'],
                            "Attrib" => $Attrib,
                            "Style" => $Item['STYLE'],
                            "Content" => $Item['CONTENT'],
                            "Layer" => $Item['LAYER'],
                            "Parent" => $Item['PARENT']);
                    ksort($EcItms[$ID]);
                }
            }
            else
            {
                $SecID = Gencode(8);
                $ItmID = Gencode(8);
                $EcItms[$SecID] = array(
                    "id" => Gencode(8),
                    "Type" => "SECTION",
                    "Align" => 'left',
                    "Dimension" => false,
                    "Attrib" => array('ecweb' => 'main'),
                    "Style" => "margin:0 auto; width:100%; height:167px; margin:10px;",
                    "Layer" => 0,
                    "Parent" => "*");
                $EcItms[$ItmID] = array(
                    "id" => Gencode(8),
                    "Type" => "ITEM",
                    "According" => array("X" => "left", "Y" => "top"),
                    "Pos" => $Default_Pos,
                    "Dimension" => false,
                    "Attrib" => array('ecweb' => 'main'),
                    "Style" => "display:block; width:85%; padding:6%; font-weight:bold; font-size:20pt; color:#888; border:10px solid #888; text-align:center;",
                    "Content" => Lang("- PAGE HAVE NO CONTENT -"),
                    "Layer" => 1,
                    "Parent" => $SecID);
            }
        }
        else
        {
            $SecID = Gencode(8);
            $ItmID = Gencode(8);
            $EcItms[$SecID] = array(
                "id" => Gencode(8),
                "Type" => "SECTION",
                "Align" => 'left',
                "Dimension" => false,
                "Attrib" => array('ecweb' => 'main'),
                "Style" => "margin:0 auto; width:100%; height:281px; margin:10px;",
                "Parent" => "*");
            $EcItms[$ItmID] = array(
                "id" => Gencode(8),
                "Type" => "ITEM",
                "According" => array("X" => "left", "Y" => "top"),
                "Pos" => $Default_Pos,
                "Dimension" => false,
                "Attrib" => array('ecweb' => 'main'),
                "Style" => "display:block; width:82.5%; padding:50px; font-weight:bold; font-size:20pt; color:#888; border:10px solid #888; text-align:center;",
                "Content" => Lang("- NO PAGE SELECTED TO VIEW -"),
                "Parent" => $SecID);
        }
        $Sys->WebPut(json_encode($EcItms));
        break;

    case "ECWebPg":
        $_ECW_GRP = '';
        $_ECW_PG = '';
        if (!empty($ECWebCode))
        {
            $ECWebCode = strtoupper($ECWebCode);
            if (strpos($ECWebCode, "-") === false)
                $_ECW_GRP = $ECWebCode;
            else
                list($_ECW_GRP, $_ECW_PG) = explode("-", $ECWebCode);
            $ECWeb = File_To_Data("{DATA_ECWEB}");
            if (array_key_exists($_ECW_GRP, $ECWeb))
            {
                if (!array_key_exists($_ECW_PG, $ECWeb[$_ECW_GRP]['PAGES']))
                {
                    $IBrowser = new IBrowser;
                    $Home = ($IBrowser->isMobile() ? 'MOBILE' : ($IBrowser->isTablet() ? 'TABLET' :
                        'DESKTOP'));
                    $_ECW_PG = '';
                    if (count($ECWeb[$_ECW_GRP]['PAGES']) > 0)
                        foreach ($ECWeb[$_ECW_GRP]['PAGES'] as $ECPageID => $ECPage)
                            if ($ECPage['HOME'][$Home] === true)
                            {
                                $_ECW_PG = $ECPageID;
                                break;
                            }
                }
            }
            else
            {
                $_ECW_GRP = '';
                $_ECW_PG = '';
            }
        }

        if (!empty($_ECW_GRP) && !empty($_ECW_PG))
        {
            $PG_Infor = array();
            $PG_Infor['Grp'] = $_ECW_GRP;
            $PG_Infor['Pg'] = $_ECW_PG;
            $PG_Infor['GName'] = $ECWeb[$_ECW_GRP]['GNAME'];
            $PG_Infor['Responsive'] = $ECWeb[$_ECW_GRP]['RESPONSIVE'];
            $PG_Infor["Title"] = "{$PG_Infor['GName']} - {$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['TITLE']}";
            $PG_Infor['Width'] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['WIDTH'];
            $PG_Infor['Width'] = (!empty($PG_Infor['Width']) ? $PG_Infor['Width'] : '810px');
            $PG_Infor['MHPage'] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['MHPAGE'];
            $PG_Infor['Layers'] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['LAYERS'];
            $PG_Infor['BOffset'] = (int)$ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['BOFFSET'];
            $PG_Infor["Style"] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['STYLE'];
            $PG_Infor["CSS"] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['CSS'];
            $PG_Infor["JS"] = $ECWeb[$_ECW_GRP]['PAGES'][$_ECW_PG]['JS'];
        }
        else
        {
            $PG_Infor = array(
                "Grp" => '',
                "Pg" => '',
                "Title" => "Default",
                "Width" => '810px',
                "MHPage" => 'page',
                "BOffset" => 0,
                "Layers" => 1,
                "Style" => null,
                "JS" => null,
                "CSS" => null);
        }
        $PG_Infor = json_encode($PG_Infor);
        $Sys->WebPut($PG_Infor);
        break;

    default:
        $ECWeb = File_To_Data("{DATA_ECWEB}");
        if (strpos($ECWebCode, "-") === false)
            $_ECW_GRP = $ECWebCode;
        else
            list($_ECW_GRP, $_ECW_PG) = explode("-", $ECWebCode);
        if (array_key_exists($_ECW_GRP, $ECWeb))
        {
            $Grp_Infor = array();
            if (!array_key_exists($_ECW_PG, $ECWeb[$_ECW_GRP]['PAGES']))
            {
                $IBrowser = new IBrowser;
                $Home = ($IBrowser->isMobile() ? 'MOBILE' : ($IBrowser->isTablet() ? 'TABLET' :
                    'DESKTOP'));
                $_ECW_PG = '';
                if (count($ECWeb[$_ECW_GRP]['PAGES']) > 0)
                    foreach ($ECWeb[$_ECW_GRP]['PAGES'] as $ECPageID => $ECPage)
                        if ($ECPage['HOME'][$Home] === true)
                        {
                            $_ECW_PG = $ECPageID;
                            break;
                        }
            }
            $Grp_Infor['Grp'] = $_ECW_GRP;
            $Grp_Infor['Responsive'] = $ECWeb[$_ECW_GRP]['RESPONSIVE'];
            $Grp_Infor['GName'] = $ECWeb[$_ECW_GRP]['GNAME'];
            $Grp_Infor['CPage'] = $_ECW_PG;
            foreach ($ECWeb[$_ECW_GRP]['PAGES'] as $PID => $Page)
            {
                $PG_Infor = array();
                $PG_Infor['Pg'] = $PID;
                $PG_Infor["Title"] = "{$Grp_Infor['GName']} - {$ECWeb[$_ECW_GRP]['PAGES'][$PID]['TITLE']}";
                $PG_Infor['Width'] = $ECWeb[$_ECW_GRP]['PAGES'][$PID]['WIDTH'];
                $PG_Infor['Width'] = (!empty($PG_Infor['Width']) ? $PG_Infor['Width'] : '810px');
                if ($ECWeb[$_ECW_GRP]['RESPONSIVE'] === true)
                    $PG_Infor['Width'] = (int)$PG_Infor['Width'];
                $PG_Infor['MHPage'] = $ECWeb[$_ECW_GRP]['PAGES'][$PID]['MHPAGE'];
                $PG_Infor['Layers'] = (int)$ECWeb[$_ECW_GRP]['PAGES'][$PID]['LAYERS'];
                $PG_Infor['BOffset'] = (int)$ECWeb[$_ECW_GRP]['PAGES'][$PID]['BOFFSET'];
                $PG_Infor["Style"] = $ECWeb[$_ECW_GRP]['PAGES'][$PID]['STYLE'];
                $PG_Infor["CSS"] = $ECWeb[$_ECW_GRP]['PAGES'][$PID]['CSS'];
                $PG_Infor["JS"] = $ECWeb[$_ECW_GRP]['PAGES'][$PID]['JS'];

                if (count($ECWeb[$_ECW_GRP]['PAGES'][$PID]['ITMS']) > 0)
                {
                    $EcItms = array();
                    foreach ($ECWeb[$_ECW_GRP]['PAGES'][$PID]['ITMS'] as $ID => $Item)
                    {
                        $Attrib = StrAttr($Item['ATTRIB']);
                        if (!empty($Item['HREF']))
                            $Attrib['href'] = $Item['HREF'];

                        if ($Item['TYPE'] === "SECTION")
                            $EcItms[$ID] = array(
                                'id' => $Item['ID'],
                                'Type' => $Item['TYPE'],
                                "Class" => $Item['CLASS'],
                                "Align" => $Item['ALIGN'],
                                "According" => $Item['ACCORDING'],
                                "Pos" => $Item['POS'],
                                "Dimension" => $Item['SIZE'],
                                "Attrib" => $Attrib,
                                "Style" => $Item['STYLE'],
                                "Layer" => $Item['LAYER'],
                                "Parent" => $Item['PARENT']);
                        elseif ($Item['TYPE'] === "ITEM")
                            $EcItms[$ID] = array(
                                'id' => $Item['ID'],
                                'Type' => $Item['TYPE'],
                                "Class" => $Item['CLASS'],
                                "According" => $Item['ACCORDING'],
                                "Pos" => $Item['POS'],
                                "Dimension" => $Item['SIZE'],
                                "Attrib" => $Attrib,
                                "Style" => $Item['STYLE'],
                                "Content" => $Item['CONTENT'],
                                "Layer" => $Item['LAYER'],
                                "Parent" => $Item['PARENT']);
                    }
                }
                $PG_Infor['Itms'] = $EcItms;
                $Pages[$PID] = $PG_Infor;
            }
            $Grp_Infor['Pages'] = $Pages;
        }
        else
        {
            $Grp_Infor['Grp'] = '';
            $Grp_Infor['Responsive'] = false;
            $Grp_Infor['GName'] = 'Default';
            $Grp_Infor['CPage'] = '';
            $Grp_Infor['Pages'][''] = array(
                "Pg" => '',
                "Title" => "Default",
                "Width" => '810px',
                "Layers" => 1,
                "MHPage" => 'page',
                "BOffset" => 0,
                "Style" => null,
                "JS" => null,
                "CSS" => null);
        }
        $Sys->WebPut(json_encode($Grp_Infor));
        break;
}
$Sys->WebOut();

?>