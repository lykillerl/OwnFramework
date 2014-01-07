<?php

#################################################
#	Class    : Object Control Class             #
#	Author   : LYK                              #
#	Date     : 2007-09-13                       #
#	Rebuild  : 2012-12-25                       #
#	Function : Control all html Object          #
#################################################

class HJs extends Chiper
{

    public function Token($Data, $Method = 0)
    {
        global $Sys;
        $Method = (int)$Method;
        switch ($Method)
        {
            case 0:
                return "Token:'" . $Sys->EncryptVar($Data) . "'";
            case 1:
                return "Token=" . rawurlencode($Sys->EncryptVar($Data));
            case 2:
                return $Sys->EncryptVar($Data);
        }
    }

    private function CJ_StrFix($StrValue)
    {
        $StrValue = addcslashes($StrValue, "\"\n\r\t" . chr(8) . chr(12));
        $JStr = '';
        $Len = strlen($StrValue);
        for ($i = 0; $i < $Len; $i++)
        {
            $Char = $StrValue[$i];
            $CA = ord($Char);

            if ($CA < 128)
            {
                $JStr .= ($CA > 31) ? $Char : sprintf("\\u%04x", $CA);
                continue;
            }

            $CB = ord($StrValue[++$i]);
            if (($CA & 32) === 0)
            {
                $JStr .= sprintf("\\u%04x", ($CA - 192) * 64 + $CB - 128);
                continue;
            }
            $CC = ord($StrValue[++$i]);
            if (($CA & 16) === 0)
            {
                $JStr .= sprintf("\\u%04x", (($CA - 224) << 12) + (($CB - 128) << 6) + ($CC - 128));
                continue;
            }
            $CD = ord($StrValue[++$i]);
            if (($CA & 8) === 0)
            {
                $U = (($CA & 15) << 2) + (($CB >> 4) & 3) - 1;

                $WA = (54 << 10) + ($U << 6) + (($CB & 15) << 2) + (($CC >> 4) & 3);
                $WB = (55 << 10) + (($CC & 15) << 6) + ($CD - 128);
                $JStr .= sprintf("\\u%04x\\u%04x", $WA, $WB);
            }
        }
        return $JStr;
    }

    public function CJSon($Data)
    {
        $VType = gettype($Data);
        switch ($VType)
        {
            case 'string':
                $Data = $this->CJ_StrFix($Data);
                if (preg_match("/^function(\s*)\((.*)\)(\s*)\{(.*)\}$/", $Data) === 1 || strpos($Data,
                    '{json_fnc}') !== false)
                    return str_replace("{json_fnc}", "", $Data);
                else
                    return "\"{$Data}\"";
                break;

            case "array":
            case "object":
                $IsArray = is_array($Data) && (empty($Data) || array_keys($Data) === range(0, count
                    ($Data) - 1));
                if ($IsArray)
                    return "[" . implode(",", array_map(array($this, 'CJSon'), $Data)) . "]";
                else
                {
                    $Items = array();
                    foreach ($Data as $Key => $Value)
                        $Items[] = $this->CJSon("{$Key}") . ":" . $this->CJSon($Value);
                    return "{" . implode(",", $Items) . "}";
                }
                break;

            default:
                return strtolower(var_export($Data, true));
        }
    }

    public function Json_Respone($Status, $Msg, $Config = null)
    {
        global $Sys;
        $Respone = array("Status" => $Status, "Msg" => $Msg);
        if (is_array($Config))
        {
            if (array_key_exists('Title', $Config) && !empty($Config['Title']))
                $Respone['Title'] = $Config['Title'];
            if (array_key_exists('Html', $Config) && (!empty($Config['Html']) || $Config['Html'] ===
                '' || $Config['Html'] === 0))
                $Respone['Html'] = $Config['Html'];
            if (array_key_exists('Value', $Config) && (!empty($Config['Value']) || $Config['Value']
                === '' || $Config['Value'] === 0))
                $Respone['Value'] = $Config['Value'];
            if (array_key_exists('Reload', $Config) && !empty($Config['Reload']))
                $Respone['Reload'] = $Config['Reload'];
            if (array_key_exists('Reloader', $Config) && !empty($Config['Reloader']))
                $Respone['Reloader'] = $Config['Reloader'];
            if (array_key_exists('Callback', $Config) && !empty($Config['Callback']))
                $Respone['Callback'] = $Config['Callback'];
            if (array_key_exists('Execute', $Config) && !empty($Config['Execute']))
                $Respone['Execute'] = $Config['Execute'];

        }
        $Respone['Title'] = !empty($Respone['Title']) ? $Respone['Title'] : Lang("System Status");
        $Sys->WebPut($this->CJSon($Respone));
        $Sys->WebOut();
        exit;
    }

    private function Link_Encode($Protocol, $Params)
    {
        $Link = '';
        foreach ($Params as $Param)
        {
            $Param = SysConvVar($Param);
            if (is_string($Param) && !empty($Param) && $Param !== 'null')
                $Link .= (!empty($Link) ? "::" : "") . rawurlencode($Param);
            else
                $Link .= "::";
        }
        return rawurlencode($Protocol) . ":" . rtrim($Link, ":");
    }

    # Return the Ajax Request Object Link or script
    public function Link_ActReqeust($Url, $Token, $DataForm = '', $FeedbackObj = null, $Reload = null)
    {
        $Reload = $this->CJSon($Reload);
        if (is_array($Token))
            $Token = $this->Token($Token, 2);
        if (!is_string($DataForm))
            $DataForm = '';
        return $this->Link_Encode("Dashboard", array(
            "ActRequest",
            $Url,
            $Token,
            stripos($DataForm, ":") === false && !empty($DataForm) ? "_FORM_DATA:'{$DataForm}'" : $DataForm,
            $FeedbackObj,
            $Reload));
    }

    # Return the Ajax Request Object Link or script
    public function Link_AskBox($Msg, $Button, $Title = null)
    {
        if (is_array($Button))
            $Button = $this->CJSon($Button);
        return $this->Link_Encode("Dashboard", array(
            "AskBox",
            $Msg,
            $Button,
            $Title));
    }

    # Return the Ajax Request Object Link or script
    public function Link_Ajax($Flow, $URL, $Data, $Config = null)
    {
        $Config = $this->CJSon($Config);
        switch (gettype($Data))
        {
            case 'array':
            case 'object':
                if (!empty($Data))
                    $PData = $this->CJSon(array("Token" => $this->Token($Data, 2)));
                else
                    $PData = 'null';
                break;

            case 'string':
                $PData = "{{$Data}}";
                break;

            default:
                $PData = 'null';
        }
        return $this->Link_Encode("Dashboard", array(
            $Flow,
            $URL,
            $PData,
            $Config));
    }

    # Return the Ajax Request Object Link or script
    public function Js_ActReqeust($Url, $Token, $Data, $FeedbackObj = null, $Reload = null)
    {
        if (is_array($Token))
            $Token = $this->Token($Token, 2);
        $Data = $this->CJSon($Data);
        $Reload = $this->CJSon($Reload);
        return "Dashboard.ActRequest('{$Url}', '{$Token}', {$Data}, '{$ObjID}', {$Reload});";
    }

    # Return the Ajax Request Object Link or script
    public function Js_AskBox($Msg, $Button, $Title = null, $Js_Separator = ';')
    {
        if (is_array($Button))
            $Button = $this->CJSon($Button);
        return "Dashboard.AskBox('{$Msg}',{$Button}, '{$Title}'){$Js_Separator}";
    }

    public function Js_Ajax_Only($URL, $Data = null, $Config = null, $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
        $PData = (!empty($Data) ? ((is_array($Data)) ? $this->Token($Data, false) : $Data) : "");
        $PData = (!empty($PData) ? "{{$PData}}" : "null");
        return "Dashboard.AOnly('{$URL}',{$PData}, {$Config}){$Js_Separator}";
    }

    public function Js_Ajax_Execute($URL, $Data = null, $Config = null, $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
        $PData = (!empty($Data) ? ((is_array($Data)) ? $this->Token($Data, false) : $Data) : "");
        $PData = (!empty($PData) ? "{{$PData}}" : "null");
        return "Dashboard.AExec('{$URL}',{$PData}, {$Config}){$Js_Separator}";
    }

    public function Js_Ajax_Function($URL, $Data = null, $Config = null, $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
        $PData = (!empty($Data) ? ((is_array($Data)) ? $this->Token($Data, false) : $Data) : "");
        $PData = (!empty($PData) ? "{{$PData}}" : "null");
        return "Dashboard.AFnc('{$URL}',{$PData}, {$Config}){$Js_Separator}";
    }

    public function Js_Ajax_Object($URL, $Data = null, $Config = null, $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
        $PData = (!empty($Data) ? ((is_array($Data)) ? $this->Token($Data, false) : $Data) : "");
        $PData = (!empty($PData) ? "{{$PData}}" : "null");
        return "Dashboard.AObj('{$URL}' ,{$PData}, {$Config}){$Js_Separator}";
    }

    public function Js_Ajax_Fancy($URL, $Data = null, $Config = null, $Method = "POST", $Js_Separator =
        ";")
    {
        $Config = $this->CJSon($Config, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK);
        $PData = (!empty($Data) ? ((is_array($Data)) ? $this->Token($Data, false) : $Data) : "");
        $PData = (!empty($PData) ? "{{$PData}}" : "null");
        return "Dashboard.AFancy('{$URL}',{$PData}, {$Config}){$Js_Separator}";
    }

    public function Js_DataGet($SName, $Js_Separator = ";")
    {
        return "_\$.Data.Get('{$SName}', {$Value}, {$JsSend}, {$Global}){$Js_Separator}";
    }

    public function Js_DataSet($SName, $Value, $JsSend = true, $Global = false, $Js_Separator = ";")
    {
        if ($JsSend === (boolean)true)
            $JsSend = "true";
        else
            $JsSend = "false";
        if ($Global === (boolean)true)
            $Global = "true";
        else
            $Global = "false";
        return "_\$.Data.Set('{$SName}', {$Value}, {$JsSend}, {$Global}){$Js_Separator}";
    }

    public function Js_DataDel($SName, $Js_Separator = ";")
    {
        return "_\$.Data.Remove('{$SName}'){$Js_Separator}";
    }

    public function Js_Validate($ObjID, $V_Data, $Js_Separator = ";")
    {
        $V_Data = $this->CJSon($V_Data);
        return "_\$('{$ObjID}').Validate({$V_Data}){$Js_Separator}";
    }

    public function Js_Validate_Clear($Js_Separator = ";")
    {
        $V_Data = $this->CJSon($V_Data);
        return "_\$.Validate.Clear(){$Js_Separator}";
    }

    public function Js_Window($URL, $WWidth = null, $WHeight = null, $Target = "_blank", $Config =
        "", $Js_Separator = ";")
    {
        return "_\$.Window.Open('{$URL}','{$WWidth}','{$WHeight}','{$Target}','{$Config}'){$Js_Separator}";
    }

    public function Js_BindKey($Key, $Handle, $AltKey = false, $ShiftKey = false, $CtrlKey = false,
        $Js_Separator = ";")
    {
        if (preg_match("/^(function){1}([\ ]*)(\(){1}(.*)(\)){1}([\{]{1})(.*)([\}]{1})/", $Target) ==
            0)
            $Target = "'" . str_replace("'", "\\'", $Target) . "'";
        $AltKey = ((boolean)$AltKey ? 'true' : 'false');
        $ShiftKey = ((boolean)$ShiftKey ? 'true' : 'false');
        $CtrlKey = ((boolean)$CtrlKey ? 'true' : 'false');
        return "_\$.Keyboard.Bind('{$Key}', {$Handle}, {$AltKey}, {$ShiftKey}, {$CtrlKey}){$Js_Separator}";
    }

    public function Js_UnbindKey($Key, $Js_Separator = ";")
    {
        return "_\$.Keyboard.Unbind('{$Key}'){$Js_Separator}";
    }

    public function Js_ObjVal($ObjID, $Value, $Js_Separator = ";")
    {
        return "_\$('{$ObjID}').Value(" . (!empty($Value) ? ", '{$Value}'" : "") . "){$Js_Separator}";
    }

    public function Js_ObjHtml($ObjID, $HTMLStr, $Js_Separator = ";")
    {
        return "_\$('{$ObjID}').Html(" . (!empty($HTMLStr) ? ", '{$HTMLStr}'" : "") . "){$Js_Separator}";
    }

    public function Js_ObjFocus($ObjID, $Js_Separator = ";")
    {
        return "_\$('{$ObjID}').Focus(){$Js_Separator}";
    }

    public function JS_ObjDisable($ObjID, $Disable, $Js_Separator = ";")
    {
        $Disable = (boolean)$Disable;
        return "_\$('{$ObjID}').Disabled('{$ObjID}', " . (($Disable === (boolean)true) ? "true" :
            "false") . "){$Js_Separator}";
    }

    public function JS_ObjDisplay($ObjID, $Display = 'block', $Js_Separator = ";")
    {
        return "_\$('{$ObjID}').Style('{$ObjID}', {display:'{$Display}'}){$Js_Separator}";
    }

    public function Js_MsgBox($Msg, $Title, $Width = 0, $Height = 0, $Js_Separator = ";")
    {
        $Msg = str_replace("'", "\'", $Msg);
        $Title = str_replace("'", "\'", $Title);
        $Width = (int)$Width;
        $Height = (int)$Height;
        return "_\$.Fancy.MsgBox('{$Msg}','{$Title}', {$Width}, {$Height}){$Js_Separator}";
    }

    public function Js_Task_Register($Target, $Loop = 1, $Interval = 10, $Channel = 'Js_Channel', $Js_Separator =
        ";")
    {
        $Loop = (int)$Loop;
        if (empty($Loop))
            $Loop = (int)1;
        $Interval = (int)$Interval;
        if (empty($Interval))
            $Interval = (int)10;
        if (preg_match("/^(function){1}([\ ]*)(\(){1}(.*)(\)){1}([\{]{1})(.*)([\}]{1})/", $Action) ==
            0)
            $Action = "'" . str_replace("'", "\\'", $Action) . "'";
        return "_\$.Task.Register({$Action}, {$Loop}, {$Interval}, '{$Channel}'){$Js_Separator}";
    }

    public function Js_Task_Start($Channel = 'Js_Channel', $Js_Separator = ";")
    {
        return "_\$.Task.Start('{$Channel}'){$Js_Separator}";
    }

    public function Js_Job_Register($Action, $Loop = 1, $Interval, $Channel = '', $Js_Separator =
        ";")
    {
        $Loop = (int)$Loop;
        if (empty($Loop))
            $Loop = (int)1;
        $Interval = (int)$Interval;
        if (empty($Interval))
            $Interval = (int)10;
        if (preg_match("/^(function){1}([\ ]*)(\(){1}(.*)(\)){1}([\{]{1})(.*)([\}]{1})/", $Action) ==
            0)
            $Action = "'" . str_replace("'", "\\'", $Action) . "'";
        return "_\$.Job.Work({$Action}, {$Loop}, {$Interval}, '{$Channel}'){$Js_Separator}";
    }

    public function Js_Delay_Execute($Execute, $Interval = 300, $Js_Separator = ";")
    {
        $Interval = (int)$Interval;
        if (empty($Interval))
            $Interval = (int)300;
        if (preg_match("/^(function){1}([\ ]*)(\(){1}(.*)(\)){1}([\{]{1})(.*)([\}]{1})/", $Execute) ==
            0)
            $Execute = "'" . str_replace("'", "\\'", $Execute) . "'";
        return "_\$.Job.Work({$Execute} 1, {$Interval},''){$Js_Separator}";
    }

    public function Js_Fancy_Close($Channel = null, $Js_Separator = ";")
    {
        return "_\$.Fancy.Close(" . (!empty($Channel) ? "'{$Channel}'" : "") . "){$Js_Separator}{$Add_Js}";
    }

    public function Js_FancyBox($Box_Title, $Box_Message, $Add_Js = null, $Width = 0, $Height = 0, $Js_Separator =
        ";")
    {
        global $Sys;
        return "_\$.Fancy.MsgBox('" . $Sys->Escape_String($Box_Message) . "', '" . $Sys->
            Escape_String($Box_Title) . "', {$Width}, {$Height}){$Js_Separator}{$Add_Js}";
    }

    public function Js_Effect_BlinkColor($ObjID, $Config = null, $Channel = '', $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config);
        return "_\$('{$ObjID}').BlinkColor({$Config}, '{$Channel}'){$Js_Separator}";
    }

    public function Js_Effect_BlinkObj($ObjID, $Config = null, $Channel = '', $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config);
        return "_\$('{$ObjID}').BlinkObj( {$Config}, '{$Channel}'){$Js_Separator}";
    }

    public function Js_Effect_ShakeObj($ObjID, $Config = null, $Channel = '', $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config);
        return "_\$('{$ObjID}').ShakeObj({$Config}, '{$Channel}'){$Js_Separator}";
    }

    public function Js_Effect_Move($ObjID, $Config = null, $Channel = '', $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config);
        return "_\$('{$ObjID}').Move({$Config}, '{$Channel}'){$Js_Separator}";
    }

    public function Js_Effect_Fade($ObjID, $Config = null, $Channel = '', $Js_Separator = ";")
    {
        $Config = $this->CJSon($Config);
        return "_\$('{$ObjID}').Fade({$Config}, '{$Channel}'){$Js_Separator}";
    }
}

class HObj extends HJs
{
    #Class Declaration
    private $Web = ''; # For store html coding
    private $Web_Js = ''; # For store html coding
    private $Log_Data = '';
    private $BSChar = "::";
    private $Block_Ouput = false;

    #Return Random Color Code format like '#000000'
    public function RndColor($RandMin = 0, $RandMax = 255)
    {
        mt_srand();
        $HexR = dechex(round(mt_rand($RandMin, $RandMax)));
        $HexG = dechex(round(mt_rand($RandMin, $RandMax)));
        $HexB = dechex(round(mt_rand($RandMin, $RandMax)));
        $HexR = ((strlen($HexR) < 2) ? "0" . $HexR : $HexR);
        $HexG = ((strlen($HexG) < 2) ? "0" . $HexG : $HexG);
        $HexB = ((strlen($HexB) < 2) ? "0" . $HexB : $HexB);
        return strtoupper("#" . $HexR . $HexG . $HexB);
    }

    public function GetBrowser()
    {
        $User_Agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($User_Agent, 'msie') !== (boolean)false)
            return 'msie';
        if (strpos($User_Agent, 'chrome') !== (boolean)false)
            return 'chrome';
        if (strpos($User_Agent, 'seamonkey') !== (boolean)false)
            return 'seamonkey';
        if (strpos($User_Agent, 'firefox') !== (boolean)false)
            return 'firefox';
        if (strpos($User_Agent, 'safari') !== (boolean)false)
            return 'safari';
        if (strpos($User_Agent, 'opera') !== (boolean)false)
            return 'opera';
        return "unknown";
    }

    #Return onevent
    public function ObjEvent($Event, $Action)
    {
        $Action = str_replace('"', '\"', $Action);
        return "{$Event}=\"{$Action}\"";
    }

    #Return strong font.
    public function Header($TValue, $HNumber = 1, $ShwCls = null, $CStyle = null)
    {
        $HNumber = (int)$HNumber;
        if ($HNumber > 6 || $HNumber < 1)
            $HNumber = 1;
        return "<h{$HNumber}>{$TValue}</h{$HNumber}>";
    }

    #Return align paragraph.
    public function Paragraph($TValue, $Align = 'center', $ShwCls = '', $CStyle = null)
    {
        return "<p" . (!empty($Align) ? " align=\"{$Align}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" :
            "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . ">{$TValue}</p>";
    }

    #Return strong font.
    public function Strong($TValue = null, $ShwCls = null, $CStyle = null)
    {
        return "<span class=\"bold" . (!empty($ShwCls) ? " {$ShwCls}\"" : "") . "\"" . (!empty($CStyle) ?
            " style=\"{$CStyle}\"" : "") . ">{$TValue}</span>";
    }

    #Return strong font.
    public function Bold($TValue = null, $ShwCls = null, $CStyle = null)
    {
        return "<span class=\"bold" . (!empty($ShwCls) ? " {$ShwCls}\"" : "") . "\"" . (!empty($CStyle) ?
            " style=\"{$CStyle}\"" : "") . ">{$TValue}</span>";
    }

    #Return color font or color background text. color : green
    public function Success($TValue, $Bold = false)
    {
        $Bold = (boolean)$Bold;
        return "<span class=\"success" . ($Bold === true ? " bold" : "") . "\">{$TValue}</span>";
    }

    #Return color font or color background text. color : blue
    public function Normal($TValue, $Bold = false)
    {
        $Bold = (boolean)$Bold;
        return "<span class=\"normal" . ($Bold === true ? " bold" : "") . "\">{$TValue}</span>";
    }

    public function Warning($TValue, $Bold = false)
    {
        $Bold = (boolean)$Bold;
        return "<span class=\"warning" . ($Bold === true ? " bold" : "") . "\">{$TValue}</span>";
    }

    #Return color font or color background text. color : gray
    public function Unknow($TValue, $Dark = false)
    {
        $Bold = (boolean)$Bold;
        return "<span class=\"unknow" . ($Bold === true ? " bold" : "") . "\">{$TValue}</span>";
    }

    #Return color font or color background text. color : yellow
    public function Inform($TValue, $Bold = false)
    {
        $Bold = (boolean)$Bold;
        return "<span class=\"inform" . ($Bold === true ? " bold" : "") . "\">{$TValue}</span>";
    }

    #Use the table to create a box
    public function Box($Msg, $Title = null, $Width = null, $Padding = '20px', $CStyle = null)
    {
        $CStyle .= (!empty($CStyle) ? " " : "") . "margin:0% auto; padding: {$Padding};" . (!empty($Width) ?
            " width:{$Width};" : "");
        return "<div class=\"box\" style=\"{$CStyle}\">" . (!empty($Title) ? "<div class=\"box_title\">{$Title}</div>" :
            "") . "{$Msg}</div>";
    }

    #Return the form tag and some input hidden tag
    public function FORM($InitV = null, $Action = null, $Target = "", $SName = "", $EncType =
        "multipart/form-data", $OnEvent = null, $CStyle = null)
    {
        $WSel .= "<form" . (!empty($Target) ? " target=\"{$Target}\"" : "") . (!empty($SName) ?
            " name=\"{$SName}\" id=\"{$SName}\"" : "") . (!empty($Action) ? " action=\"{$Action}\"" :
            "") . " method=\"post\"" . (!empty($EncType) ? " enctype=\"{$EncType}\"" : "") . (!
            empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") .
            ">";
        if (is_array($InitV))
            foreach ($InitV as $iKey => $IValue)
                $WSel .= "<input type=\"hidden\" name=\"{$iKey}\" value=\"{$IValue}\" />";
        return $WSel;
    }

    #Return the form closing tag
    public function EForm()
    {
        return "</form>";
    }

    #Return the form closing tag
    public function Span($ObjID, $Content = "&nbsp;", $Front_Space = true, $OnEvent = null, $ShwCls = null,
        $CStyle = null)
    {
        return (($Front_Space === (boolean)true) ? " " : "") . "<span id=\"{$ObjID}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ?
            " style=\"{$CStyle}\"" : "") . ">{$Content}</span>";
    }

    #Return the form closing tag
    public function Div($ObjID, $Content = "&nbsp;", $OnEvent = null, $ShwCls = null, $CStyle = "")
    {
        return (($Front_Space === (boolean)true) ? " " : "") . "<div id=\"{$ObjID}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ?
            " style=\"{$CStyle}\"" : "") . ">{$Content}</div>";
    }

    #Return the form closing tag
    public function Notice($ObjID, $ShwCls = "form_alert", $CStyle = null, $Content = "&nbsp;")
    {
        return $this->Span($ObjID, $Content, true, null, $ShwCls, $CStyle);
    }

    # Return the hyper link or script link
    public function ALink($SValue, $URL, $SName = null, $Target = null, $OnEvent = null, $ShwCls =
        'link', $CStyle = null, $Disable = null)
    {
        return (($this->Print_Mode == false) ? "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . ((strtolower($Disable) == "true") ? " href=\"#{$SName}\"" : " href=\"{$URL}\"") .
            (!empty($Target) ? " target=\"{$Target}\"" : "") . (!empty($OnEvent) ? " {$OnEvent}" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">{$SValue}</a>" : $SValue);
    }

    #Return the some input hidden value
    public function Hidden($SValue)
    {
        if (is_array($SValue))
        {
            $WSel = '';
            foreach ($SValue as $FKey => $FValue)
            {
                $WSel .= ((!empty($FValue) && !empty($FKey)) ? "<input type=\"hidden\" id=\"{$FKey}\" name=\"{$FKey}\" value=\"{$FValue}\">" :
                    "");
            }
        }
        return $WSel;
    }

    # Return the text tag
    public function Text($SName, $TValue, $AutoComplete = 'off', $OnEvent = null, $MaxLength = null,
        $Size = null, $ShwCls = 'txt', $CStyle = null, $Disable = null)
    {
        return (($this->Print_Mode == false) ? "<input type=\"text\" name=\"{$SName}\" id=\"{$SName}\" value=\"{$TValue}\" placeholder=\"{$TValue}\" class=\"{$ShwCls}\"" .
            (!empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($AutoComplete) ? " autocomplete=\"{$AutoComplete}\"" :
            "") . (!empty($MaxLength) ? " maxlength=\"{$MaxLength}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">" : $TValue);
    }

    # Return the password tag
    public function Password($SName, $TValue, $AutoComplete = null, $OnEvent = null, $MaxLength = null,
        $Size = null, $ShwCls = 'txt', $CStyle = null, $Disable = null)
    {
        return (($this->Print_Mode == false) ? "<input type=\"password\" name=\"{$SName}\" id=\"{$SName}\" value=\"{$TValue}\" class=\"{$ShwCls}\"" .
            (!empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($AutoComplete) ? " autocomplete=\"{$AutoComplete}\"" :
            "") . (!empty($MaxLength) ? " maxlength=\"{$MaxLength}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">" : str_repeat("*", strlen($TValue)));
    }

    # Return the file tag
    public function File($SName, $OnEvent = null, $ShwCls = 'txt', $CStyle = null, $Disable = null)
    {
        return "<input type=\"file\" name=\"{$SName}\" id=\"{$SName}\"" . (!empty($OnEvent) ? " {$OnEvent}" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
    }

    # Return the textarea tag
    function Textarea($SName, $TValue, $OnEvent = null, $TCols = 60, $TRows = 5, $MaxLength = null,
        $ShwCls = 'txt', $CStyle = null, $Disable = null)
    {
        return (($this->Print_Mode == false) ? "<textarea name=\"{$SName}\" id=\"{$SName}\" cols=\"{$TCols}\" rows=\"{$TRows}\" class=\"{$ShwCls}\"" .
            (!empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($MaxLength) ? " maxlength=\"{$MaxLength}\"" :
            "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . ">{$TValue}</textarea>" : $TValue);
    }

    # Return the button tag
    public function Button($SName, $TValue, $OnEvent = null, $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        return "<input type=\"button\" name=\"{$SName}\" id=\"{$SName}\" value=\"{$TValue}\" class=\"{$ShwCls}\"" . (!
            empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
    }

    # Return the reset tag
    public function Reset($SName, $TValue, $OnEvent = null, $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        return "<input type=\"reset\" name=\"{$SName}\" id=\"{$SName}\" value=\"{$TValue}\" class=\"{$ShwCls}\"" . (!
            empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
    }

    # Return the Submit tag
    public function Submit($TValue, $OnEvent = null, $SName = null, $ShwCls = 'btn', $CStyle = null,
        $Disable = null)
    {
        return "<input type=\"submit\" name=\"{$SName}\" id=\"{$SName}\" value=\"{$TValue}\" class=\"{$ShwCls}\"" . (!
            empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
    }

    public function Check($SName, $TValue = null, $Lable = null, $Selected = false, $OnEvent = null,
        $ShwCls = 'check', $CStyle = null, $Disable = null)
    {
        if ($this->Print_Mode != false)
            $Disable = true;
        return "<input type=\"checkbox\" name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!
            empty($TValue) ? " value=\"{$TValue}\"" : "") . (!empty($OnEvent) ? " {$OnEvent}" : "") . (!
            empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . (!empty($Selected) ? " Checked" : "") . ">" . (!empty($Lable) ? "<label>{$Lable}</label>" :
            "");
    }

    # Return the Select Tag (number only)
    public function SelNum($SName, $SPos, $EPos, $SelPos, $OnEvent = null, $Size = null, $Multiple = null,
        $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        if (empty($SelPos))
            $SelPos = 0;
        $Disable = ($EPos >= $SPos ? "true" : $Disable);
        $Multiple = (!(boolean)$Multiple ? "" : "true");
        $WSel = "<select name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style={$CStyle}" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($Multiple) ? " multiple=\"{$Multiple}\"" : "") . ">";
        if ($EPos >= $SPos)
            for ($i = $SPos; $i <= $EPos; $i++)
            {
                $iPos++;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$i}\"" . (($SelPos === $i) ?
                    " Selected" : "") . ">{$i}</option>";
            }
        $WSel .= "</select>";
        return $WSel;
    }

    #Return the Select Tag (label : String  value : Number Only)
    public function SelNumStr($SName, $VArray, $SelPos, $OnEvent = null, $Size = null, $Multiple = null,
        $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        if (is_numeric($SelPos))
            $SelPos = (int)$SelPos;
        $Disable = (!is_array($VArray) ? "true" : $Disable);
        $Multiple = (!(boolean)$Multiple ? "" : "true");
        $WSel = "<select name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($Multiple) ? " multiple=\"{$Multiple}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
        if (is_array($VArray))
            foreach ($VArray as $Key => $FShow)
            {
                $iPos++;
                $Key = (int)$Key;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$Key}\"" . (($Key === $SelPos) ?
                    " Selected" : "") . ">{$FShow}</option>";
            }
        $WSel .= "</Select>";
        return $WSel;
    }

    #Return the Select Tag (label : Number Only  value : String)
    public function SelStrNum($SName, $VArray, $SelValue, $OnEvent = null, $Size = null, $Multiple = null,
        $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        $i = (int)0;
        $Disable = (!is_array($VArray) ? "true" : $Disable);
        $Multiple = (!(boolean)$Multiple ? "" : "true");
        $WSel = "<select name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($Multiple) ? " multiple=\"{$Multiple}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
        if (is_array($VArray))
            foreach ($VArray as $FValue)
            {
                $iPos++;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$FValue}\"" . (($SelValue === $i) ?
                    " Selected" : "") . ">{$i}</option>";
                $i++;
            }
        $WSel .= "</Select>";
        return $WSel;
    }

    # Return the Select tag (label and value is string)
    public function SelStr($SName, $FArray, $SelValue, $OnEvent = null, $Size = null, $Multiple = null,
        $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        $Disable = (!is_array($FArray) ? "true" : $Disable);
        $Multiple = (!(boolean)$Multiple ? "" : "true");
        $WSel = "<select name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($Multiple) ? " multiple=\"{$Multiple}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
        if (is_array($FArray))
            foreach ($FArray as $FValue => $FShow)
            {
                $iPos++;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$FValue}\"" . (($FValue === $SelValue) ?
                    " Selected" : "") . ">{$FShow}</option>";
            }
        $WSel .= "</Select>";
        return $WSel;
    }

    public function GSelStr($SName, $FArray, $SelValue, $OnEvent = null, $Size = null, $Multiple = null,
        $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        if (empty($SelPos))
            $SelPos = 0;
        $Disable = (!is_array($FArray) ? "true" : $Disable);
        $Multiple = (!(boolean)$Multiple ? "" : "true");
        $WSel = "<select name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($Multiple) ? " multiple=\"{$Multiple}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
        if (is_array($FArray))
            foreach ($FArray as $FTitle => $FGroup)
            {
                if (is_array($FGroup))
                {
                    $WSel .= "<optgroup class=\"highlight_0\" label=\"{$FTitle}\">";
                    foreach ($FGroup as $FValue => $FShow)
                    {
                        $iPos++;
                        $ShwCls = "row_" . ($iPos % 2);
                        $WSel .= "<option class=\"{$ShwCls}\" value=\"{$FValue}\"" . (($FValue === $SelValue) ?
                            " Selected" : "") . ">{$FShow}</option>";
                    }
                    $WSel .= "</optgroup>";
                }
                else
                {
                    $WSel .= "<option class=\"{$ShwCls}\" value=\"{$FTitle}\"" . (($FTitle === $SelValue) ?
                        " Selected" : "") . ">{$FGroup}</option>";
                }
            }
        $WSel .= "</Select>";
        return $WSel;
    }

    # Return the Select tag content sql data query from the given sql command and following sql class.
    public function SelSqlTb($SName, $QSQL, $TValue = null, $VCol = 0, $SCol = 1, $FOption = null, $OnEvent = null,
        $BOption = null, $Size = null, $Multiple = null, $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        if (is_resource($QSQL))
            $QSel = $QSQL;
        elseif (is_string($QSQL))
            $QSel = $this->QUERY($QSQL);
        else
            $QSel = false;
        $Disable = (($this->NUM_ROWS($QSel) === 0 && (!is_array($FOption) || empty($FOption)) && (!
            is_array($BOption) || empty($BOption))) ? "true" : $Disable);
        $Multiple = (!(boolean)$Multiple ? "" : "true");
        $WSel = "<select name=\"{$SName}\" id=\"{$SName}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Size) ?
            " size=\"{$Size}\"" : "") . (!empty($Multiple) ? " multiple=\"{$Multiple}\"" : "") . (!
            empty($Disable) ? " disabled=\"{$Disable}\"" : "") . ">";
        if (is_array($FOption) && !empty($FOption))
            foreach ($FOption as $FKey => $FValue)
            {
                $iPos++;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$FKey}\" " . (($FKey === $TValue) ?
                    " Selected" : "") . ">{$FValue}</option>";
            }
        if ($this->NUM_ROWS($QSel) > 0)
        {
            while ($SData = $this->FETCH_ROW($QSel))
            {
                $iPos++;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$SData[$VCol]}\" " . (($SData[$VCol]
                    === $TValue) ? " Selected" : "") . ">{$SData[$SCol]}</option>";
            }
            $this->Free_Result($QSel);
        }
        if (is_array($BOption) && !empty($BOption))
            foreach ($BOption as $BKey => $BValue)
            {
                $iPos++;
                $ShwCls = "row_" . ($iPos % 2);
                $WSel .= "<option class=\"{$ShwCls}\" value=\"{$BKey}\" " . (($BKey === $TValue) ?
                    " Selected" : "") . ">{$BValue}</option>";
            }
        $WSel .= "</select>";
        $this->FREE_RESULT($QSel);
        return $WSel;
    }

    # Return the 3 Select tag content the year, month and day, year rang is given year +-$DRange
    public function SelDate($SYear, $SMth, $SDay, $DYear = null, $DMth = null, $DDay = null, $URange =
        5, $DRange = 5, $ShwCls = 'Sel', $CStyle = null, $Disable = null)
    {
        $DYear = (int)$DYear;
        $DMth = (int)$DMth;
        $DDay = (int)$DDay;
        $URange = (int)$URange;
        $DRange = (int)$DRange;
        if ($URange >= 1970)
        {
            $Range['SYear'] = (int)$URange;
            $Range['EYear'] = (int)$DRange;
        }
        else
        {
            $Range['SYear'] = (int)date("Y") - (int)$URange;
            $Range['EYear'] = (int)date("Y") + (int)$DRange;
        }
        $WSel = "<select name=\"{$SYear}\" id=\"{$SYear}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">";
        for ($i = $Range['SYear']; $i <= $Range['EYear']; $i++)
        {
            $OptShwCls = "row_" . ($i % 2);
            $WSel .= "<option class=\"{$OptShwCls}\" value=\"{$i}\"" . (((!empty($DYear) ? (int)$DYear :
                (int)date("Y")) == (int)$i) ? " Selected" : "") . ">{$i}</option>";
        }
        $WSel .= "</select>&nbsp;";
        $WSel .= "<select name=\"{$SMth}\" id=\"{$SMth}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">";
        for ($i = 1; $i <= 12; $i++)
        {
            $OptShwCls = "row_" . ($i % 2);
            $WSel .= " <option class=\"{$OptShwCls}\" value=\"{$i}\"" . (((!empty($DMth) ? (int)$DMth :
                (int)date("m")) == (int)$i) ? " Selected" : "") . ">{$i}</option>";
        }
        $WSel .= "</select>&nbsp;";
        $WSel .= "<select name=\"{$SDay}\" id=\"{$SDay}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">";
        for ($i = 1; $i <= 31; $i++)
        {
            $OptShwCls = "row_" . ($i % 2);
            $WSel .= "<option class=\"{$OptShwCls}\" value=\"{$i}\"" . (((!empty($DDay) ? (int)$DDay :
                (int)date("d")) == (int)$i) ? " Selected" : "") . ">{$i}</option>";
        }
        $WSel .= "</select>";
        return $WSel;
    }

    # Return the 3 Select tag content the hour, minutes and second.
    public function SelTime($SHour, $SMin, $SSec, $DHour = null, $DMin = null, $DSec = null, $ShwCls =
        'Sel', $CStyle = null, $Disable = null)
    {
        $WSel = "<select name=\"{$SHour}\" id=\"{$SHour}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">";
        for ($i = 0; $i <= 23; $i++)
        {
            $WSel .= " <option value=\"{$i}\"" . (((is_numeric($DHour) ? (int)$DHour : (int)date("H")) ==
                (int)$i) ? " Selected" : "") . ">{$i}</option>";
        }
        $WSel .= "</select>";
        $WSel .= "<select name=\"{$SMin}\" id=\"{$SMin}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">";
        for ($i = 0; $i <= 59; $i++)
        {
            $WSel .= " <option value=\"{$i}\"" . (((is_numeric($DMin) ? (int)$DMin : (int)date("i")) ==
                (int)$i) ? " Selected" : "") . ">{$i}</option>";
        }
        $WSel .= "</select>";
        $WSel .= "<select name=\"{$SSec}\" id=\"{$SSec}\" class=\"{$ShwCls}\"" . (!empty($OnEvent) ?
            " {$OnEvent}" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ?
            " disabled=\"{$Disable}\"" : "") . ">";
        for ($i = 0; $i <= 59; $i++)
        {
            $WSel .= " <option value=\"{$i}\"" . (((is_numeric($DSec) ? (int)$DSec : (int)date("s")) ==
                (int)$i) ? " Selected" : "") . ">{$i}</option>";
        }
        $WSel .= "</select>";
        return $WSel;
    }

    public function Pagination($Pg_Count, $Total_Count, $Pg_Now, $Pg_Url, $Pg_Data = null, $CPName =
        "Pg_Now", $Pg_Range = 10, $Config = null, $AddJs = null, $ObjID = "{JS_MAIN}")
    {
        #Intializing Variable
        $Total_Count = (int)$Total_Count;
        $Pg_Count = (int)$Pg_Count;

        if ($Total_Count > $Pg_Count && !empty($Pg_Count))
        {
            #Intializing Variable
            $Pg_Now = (int)$Pg_Now;
            $Pg_Total = (int)0;
            $Pg_Range = (int)$Pg_Range;
            $WB_Pg_JScript = '';
            $WB_Pagination = '';
            $Pg_Begin = 0;
            $Pg_End = 0;

            #Pagination Calcurate
            $Pg_Total = (($Total_Count - ($Total_Count % $Pg_Count)) / $Pg_Count);
            $Pg_Total -= (($Total_Count % $Pg_Count === (int)0) ? (int)1 : (int)0);
            $Pg_Begin = (int)($Pg_Now % $Pg_Range);
            $Pg_Begin = (($Pg_Now - $Pg_Begin) / $Pg_Range) * $Pg_Range;
            $Pg_Begin--;
            $Pg_Begin = (($Pg_Begin < 0) ? 0 : $Pg_Begin);
            $Pg_End = (int)$Pg_Begin + (int)$Pg_Range + 1;
            $Pg_End = (($Pg_End > $Pg_Total) ? $Pg_Total : $Pg_End);

            #Draw Pagination
            $WB_Pagination = "<table cellspacing=\"5\" align=\"center\"><tr>";
            if ($Pg_Begin > 0 && $Pg_Now > $Pg_Range)
            {
                $WB_Pg_JScript = $AddJs . $this->Js_DataSet($CPName, 0);
                $WB_Pagination .= "<td class=\"pageination\">" . $this->Ajax_Link_Object("&laquo;",
                    $Pg_Url, $this->Token($Pg_Data), $Config, $WB_Pg_JScript, $ObjID) . "</td>";
            }
            if ($Pg_Now > 0 && $Pg_Now > $Pg_Range)
            {
                $WB_Pg_JScript = $AddJs . $this->Js_DataSet($CPName, (($Pg_Begin - $Pg_Range > 0) ?
                    $Pg_Begin : 0));
                $WB_Pagination .= "<td class=\"pageination\">" . $this->Ajax_Link_Object("&lsaquo;",
                    $Pg_Url, $this->Token($Pg_Data), $Config, $WB_Pg_JScript, $ObjID) . "</td>";
            }
            for ($i = (int)$Pg_Begin; $i <= (int)$Pg_End; $i++)
            {
                $WB_Pg_JScript = $AddJs . $this->Js_DataSet($CPName, $i);
                $WB_Pagination .= "<td class=\"" . (($i === $Pg_Now) ? "pageination_selected" :
                    "pageination") . "\">" . (($i === $Pg_Now) ? $Pg_Now + (int)1 : $this->
                    Ajax_Link_Object($i + 1, $Pg_Url, $this->Token($Pg_Data), $Config, $WB_Pg_JScript,
                    $ObjID)) . "</td>";
            }
            if ($Pg_Now < $Pg_Total && $Pg_Now < $Pg_Total - $Pg_Range)
            {
                $WB_Pg_JScript = $AddJs . $this->Js_DataSet($CPName, (($Pg_Range + $Pg_End > $Pg_Total) ?
                    $Pg_Total : $Pg_End));
                $WB_Pagination .= "<td class=\"pageination\">" . $this->Ajax_Link_Object("&rsaquo;",
                    $Pg_Url, $this->Token($Pg_Data), $Config, $WB_Pg_JScript, $ObjID) . "</td>";
            }
            if ($Pg_End < $Pg_Total && $Pg_Now < $Pg_Total - $Pg_Range)
            {
                $WB_Pg_JScript = $AddJs . $this->Js_DataSet($CPName, $Pg_Total);
                $WB_Pagination .= "<td class=\"pageination\">" . $this->Ajax_Link_Object("&raquo;",
                    $Pg_Url, $this->Token($Pg_Data), $Config, $WB_Pg_JScript, $ObjID) . "</td>";
            }
            $WB_Pagination .= "</tr></table>";
            return $WB_Pagination;
        }
    }

    public function TBTab($Content, $LTab_Data = null, $RTab_Data = null, $Title = null, $SName = null,
        $TWidth = "96%", $Align = "center", $ShwCls = "table", $CStyle = "margin:2%;")
    {
        $LTab_Sel = "";
        $RTab_Sel = "";
        $TabCls = "";
        $WLTab = "";
        $WRTab = "";
        $WTB = "";

        if (!empty($LTab_Data))
        {
            $WLTab = "<table class=\"tab\"><tr>";
            $TabCls = "";
            if (is_array($LTab_Data))
            {
                if (array_key_exists("TAB_SELECTED", $LTab_Data))
                {
                    $LTab_Sel = $LTab_Data["TAB_SELECTED"];
                    unset($LTab_Data["TAB_SELECTED"]);
                }
                foreach ($LTab_Data as $Tab_Key => $Tab)
                {
                    if (!empty($LTab_Sel) && $Tab_Key === $LTab_Sel)
                        $TabCls = "seltab";
                    else
                        $TabCls = "tab";
                    $WLTab .= "<td class=\"{$TabCls}\">{$Tab}</td>";
                }
            }
            else
            {
                $TabCls = "seltab";
                $WLTab .= "<td class=\"{$TabCls}\">{$LTab_Data}</td>";
            }
            $WLTab .= "</tr></table>";
        }

        if (!empty($RTab_Data))
        {
            $WRTab = "<table class=\"tab\"><tr>";
            $TabCls = "";
            if (is_array($RTab_Data))
            {
                if (array_key_exists("TAB_SELECTED", $RTab_Data))
                {
                    $RTab_Sel = $RTab_Data["TAB_SELECTED"];
                    unset($RTab_Data["TAB_SELECTED"]);
                }
                foreach ($RTab_Data as $Tab_Key => $Tab)
                {
                    if (!empty($RTab_Sel) && $Tab_Key === $RTab_Sel)
                        $TabCls = "seltab";
                    else
                        $TabCls = "tab";
                    $WRTab .= "<td class=\"{$TabCls}\">{$Tab}</td>";
                }
            }
            else
            {
                $TabCls = "seltab";
                $WRTab .= "<td class=\"{$TabCls}\">{$RTab_Data}</td>";
            }
            $WRTab .= "</tr></table>";
        }
        if (!empty($TWidth))
            $CStyle .= " width:{$TWidth};";

        $WTB = "<table" . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Align) ?
            " align=\"{$Align}\"" : "") . ">";
        $Colspan = (int)0;
        $WTAB .= "<tr>";
        if (!empty($WLTab))
        {
            $Colspan++;
            $WTAB .= "<td style=\"height:3em; border:none; padding-top:1em;\">{$WLTab}</td>";
        }
        if (!empty($WRTab))
        {
            $Colspan++;
            $WTAB .= "<td align=\"right\" style=\"height:3em;  border:none; padding-top:1em;\">{$WRTab}</td>";
        }
        $WTAB .= "</tr>";
        if (!empty($Title))
            $WTB .= "<tr><td " . (($Colspan > 1) ? " colspan=\"{$Colspan}\"" : "") . " style=\"border:none; border-bottom:1px #000000 solid;\">" .
                $this->Header($Title) . "</td></tr>";
        $WTB .= "{$WTAB}<tr><td " . (($Colspan > 1) ? " colspan=\"{$Colspan}\"" : "") . " valign=\"top\" style=\"border:none; padding-top:2px;\">{$Content}</td></tr>
        </table>";
        return $WTB;
    }

    # Return the full table content sql data from given sql command with the following class.
    public function TBGrid($TData, $Title = null, $THCol = null, $SName = null, $TBN_Data = null, $THAddon = null,
        $TCount = false, $TCAlign = 'auto', $TAlign = 'center', $TBorder = 1, $TWidth = '100%', $ShwCls =
        'table', $CStyle = null)
    {
        $WTB = "<table" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . (!empty($TAlign) ?
            " align=\"{$TAlign}\"" : "") . (!empty($TBorder) ? " border=\"{$TBorder}\"" : "") . (!
            empty($TWidth) ? " width=\"{$TWidth}\"" : "") . " class=\"{$ShwCls}\"" . (!empty($CStyle) ?
            " style=\"{$CStyle}\"" : "") . ">";

        #Get the column count
        $TColNo = (int)0;
        if (is_array($THCol) && !empty($THCol))
            foreach ($THCol as $Col => $TCell)
            {
                if (is_array($TCell) && array_key_exists("colspan", $TCell))
                {
                    $TColNo += (int)$TCell['colspan'];
                }
                else
                {
                    $TColNo++;
                }
            }
        if (is_array($TData) && !empty($TData))
            foreach ($TData as $Key => $TRow)
            {
                $TColRow = (int)0;
                foreach ($TRow as $Col => $TCell)
                {
                    if (is_array($TCell) && array_key_exists("colspan", $TCell))
                    {
                        $TColRow += (int)$TCell['colspan'];
                    }
                    else
                    {
                        $TColRow++;
                    }
                }
                if ($TColRow > $TColNo)
                    $TColNo = (int)$TColRow;
            }

        if (empty($TColNo))
            $TColNo = (int)1;

        #Show Table Title
        if (!empty($Title))
            $WTB .= "<tr><th colspan=\"{$TColNo}\" class=\"row_t\">{$Title}</th></tr>";

        #Setup Table Header addon, etc. filter
        if (!empty($THAddon) && is_array($THAddon))
            foreach ($THAddon as $Addon_Name => $Addon_Data)
            {
                $TValue = "&nbsp;";
                $TPData = array();
                $TPData['align'] = "right";
                $TPData['colspan'] = (int)$TColNo;
                $TPData['class'] = "row_t";
                if (is_array($Addon_Data))
                    foreach ($Addon_Data as $TPName => $TCData)
                        if (strtolower($TPName) === "content" || strtolower($TPName) === "value")
                        {
                            $TValue = $TCData;
                            unset($Addon_Data[$TPName]);
                        }
                        else
                            $TPData[strtolower($TPName)] = $TCData;
                    else
                        $TValue = $Addon_Data;
                $TDetail = '';
                foreach ($TPData as $PName => $PData)
                    $TDetail .= " {$PName}=\"{$PData}\"";
                $WTB .= "<tr><td{$TDetail}>" . (!is_int($Addon_Name) ? "{$Addon_Name} : " : "") . "{$TValue}</td></tr>";
            }

        #Setup the grid title and row count.
        if ($TCount)
            $WTB .= "<tr><td align=\"right\" colspan=\"{$TColNo}\" class=\"row_t\">" . Lang("Total") .
                " " . $this->Inform(count($TData), 0, false) . " " . Lang("Rows") . "</th></tr>";

        #Setup the grid column head
        if (is_array($THCol) && !empty($THCol))
        {
            $WTB .= "<tr class=\"row_h\">";
            foreach ($THCol as $Key => $HCol)
            {
                $TDetail = "";
                if (is_array($HCol))
                    foreach ($HCol as $PName => $PData)
                        if ($PName === "content" || $PName === "value")
                            $TValue = $PData;
                        else
                            $TDetail .= " {$PName}=\"{$PData}\"";
                    else
                        $TValue = $HCol;
                $WTB .= "<th{$TDetail}>" . (!empty($TValue) ? $TValue : "-") . "</th>";
            }
            $WTB .= "</tr>";
        }

        #List out grid data
        if (is_array($TData) && !empty($TData))
        {
            $iPos = (int)0;
            foreach ($TData as $Key => $TRow)
            {
                if (is_array($TRow))
                {
                    $WTB .= "<tr class=\"row_" . ($iPos % 3) . "\">";
                    foreach ($TRow as $Col => $TCell)
                    {
                        $TValue = "&nbsp;";
                        $TPData = array();
                        $TPData['align'] = "left";
                        $TPData['valign'] = "top";
                        if (is_array($TCell))
                            foreach ($TCell as $TPName => $TCData)
                                if (strtolower($TPName) === "content" || strtolower($TPName) ===
                                    "value")
                                {
                                    $TValue = $TCData;
                                    unset($TCell[$TPName]);
                                }
                                else
                                    $TPData[strtolower($TPName)] = $TCData;
                            else
                            {
                                $TValue = $TCell;
                                if ($TCAlign === 'auto' && is_int($TValue))
                                    $TPData['align'] = "center";
                                if ($TCAlign === 'auto' && is_double($TValue))
                                    $TPData['align'] = "right";
                                elseif ($TCAlign === 'auto' && is_string($TValue))
                                {
                                    if (strlen(strip_tags($TValue)) < 10)
                                        $TPData['align'] = "center";
                                    else
                                        $TPData['align'] = "left";
                                }
                            }
                            $TDetail = '';
                        foreach ($TPData as $PName => $PData)
                            $TDetail .= " {$PName}=\"{$PData}\"";
                        $WTB .= "<td{$TDetail}>{$TValue}</td>";
                    }
                    $WTB .= "</tr>";
                    $iPos++;
                }
            }
        }
        else
            $WTB .= "<tr><td align=\"center\" colspan=\"{$TColNo}\" style=\"padding:30px;\">" . $this->
                Unknow((!empty($TBN_Data) ? $TBN_Data : Lang("No any row data."))) . "</td></tr>";
        $WTB .= "</table>";
        return $WTB;
    }

    # Return the full table content sql data from given sql command with the following class.
    public function TBGridFixCol($TData, $TFixCol, $Title = null, $SName = null, $TBN_Data = null, $THAddon = null,
        $TCount = false, $TBorder = 1, $TWidth = '100%', $ShwCls = 'table', $CStyle = null)
    {
        $WTB = "<table" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . (!empty($TBorder) ?
            " border=\"{$TBorder}\"" : "") . (!empty($TWidth) ? " width=\"{$TWidth}\"" : "") .
            " class=\"{$ShwCls}\"" . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . ">";

        #Show Table Title
        if (!empty($Title))
            $WTB .= "<tr><th colspan=\"{$TFixCol}\" class=\"row_t\">{$Title}</th></tr>";

        #Setup Table Header addon, etc. filter
        if (!empty($THAddon) && is_array($THAddon))
        {
            foreach ($THAddon as $Addon_Name => $Addon_Data)
            {
                $WTB .= "<tr><td align=\"right\" colspan=\"{$TFixCol}\" class=\"row_t\">" . (!
                    is_int($Addon_Name) ? "{$Addon_Name} : " : "") . "{$Addon_Data}</th></tr>";
            }
        }

        #Setup the grid title and row count.
        if ($TCount)
            $WTB .= "<tr><td align=\"right\" colspan=\"{$TFixCol}\" class=\"row_t\">" . Lang("Total") .
                " " . $this->Inform(count($TData), 0, false) . " " . Lang("Records") . "</th></tr>";

        #Calcurate Exactly Total Column
        $TFixCol = (int)$TFixCol;
        if (is_array($TData) && !empty($TData))
        {
            $TColNow = (int)0;
            foreach ($TData as $Key => $TCell)
            {
                if (is_array($TCell) && array_key_exists("colspan", $TCell) && (int)$TCell['colspan'] >
                    1)
                {
                    if ((int)$TColNow % (int)$TFixCol < (int)$TCell['colspan'])
                        $TFixCol += (int)$TCell['colspan'] - ((int)$TColNow % (int)$TFixCol);
                }
                $TColNow++;
            }
        }

        #List out grid data
        if (is_array($TData) && !empty($TData))
        {
            $iPos = (int)0;
            $iRow = (int)0;
            $WTB .= "<tr>";
            foreach ($TData as $Key => $TCell)
            {
                if ($iPos > 0 && $iPos % $TFixCol == 0)
                {
                    $WTB .= "</tr><tr>";
                    $iRow++;
                }
                $TValue = "&nbsp;";
                $TPData = array();
                $TPData['align'] = "center";
                $TPData['class'] = "row_" . ($iPos % 3);
                if (is_array($TCell))
                {
                    foreach ($TCell as $TPName => $TCData)
                    {
                        if (strtolower($TPName) === "content" || strtolower($TPName) === "value")
                        {
                            $TValue = $TCData;
                            unset($TCell[$TPName]);
                        }
                        else
                        {
                            $TPData[strtolower($TPName)] = $TCData;
                        }
                    }
                }
                else
                {
                    $TValue = $TCell;
                }
                $TDetail = '';
                foreach ($TPData as $PName => $PData)
                    $TDetail .= " {$PName}=\"{$PData}\"";
                $WTB .= "<td{$TDetail}>{$TValue}</td>";
                if (is_array($TCell) && array_key_exists("colspan", $TCell))
                {
                    if ((int)$iPos % (int)$TFixCol > (int)$TCell['colspan'])
                    {
                        $iPos += (int)$TCell['colspan'];
                    }
                    else
                    {
                        $iPos += (int)$iPos % (int)$TFixCol;
                    }
                }
                else
                {
                    $iPos++;
                }
            }
        }
        else
        {
            $WTB .= "<tr><td align=\"center\" colspan=\"{$TFixCol}\" style=\"padding:30px;\">" . $this->
                Unknow((!empty($TBN_Data) ? $TBN_Data : Lang("No any raw data."))) . "</td></tr>";
        }
        $WTB .= "</table>";
        return $WTB;
    }

    # Return full table tag,optional content input tag, to become like a input form
    public function Build_Form($Form_Title = null, $Form_Control = null, $Form_Button = null, $Form_Line =
        1, $SName = null, $TWidth = '100%', $TAlign = 'center', $ShwCls = 'table', $CStyle =
        'border-collapse:collapse;')
    {
        if (!empty($Form_Title) || !empty($Form_Control) || !empty($Form_Button))
        {
            $Form_Line = (int)$Form_Line;
            if ($Form_Line <= 0)
                $Form_Line = (int)1;
            if (!empty($TWidth))
                $CStyle .= " width:{$TWidth};";
            $WSel = "<table" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . (!
                empty($TAlign) ? " align=\"center\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" :
                "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . ">" . (!empty($Form_Title) ?
                "<tr><th colspan=\"" . ($Form_Line * 2) . "\" class=\"form_title\">{$Form_Title}</th></tr>" :
                "");
            if (is_array($Form_Control) && !empty($Form_Control))
            {
                $iPos = (int)0;
                $iRow = (int)0;
                $iNxs = (int)0;
                $WSel .= "<tr>";
                $CKey = "";
                $Controller = array();
                foreach ($Form_Control as $FKey => $FControl)
                {
                    if (is_array($FControl) && !empty($FControl) && is_int($FKey))
                    {
                        $HData = array();
                        $TDetail = " valign=\"top\" style=\"white-space:nowrap;\"";
                        foreach ($FControl as $PName => $PData)
                        {
                            $PName = strtoupper($PName);
                            switch ($PName)
                            {
                                case "TYPE":
                                    $PData = strtoupper($PData);
                                    switch ($PData)
                                    {
                                        case "FORM_HEADER_CONTENT":
                                            $HData['control'] = (int)$Form_Line;
                                            $HData['type'] = "FORM_HEADER_CONTENT";
                                            $TDetail .= " class=\"form_header\"";
                                            break;
                                        case "FORM_CONTENT_ONLY":
                                            $HData['type'] = "FORM_CONTENT_ONLY";
                                            $HData['control'] = (int)1;
                                            $TDetail .= " class=\"form_line\"";
                                            break;
                                    }
                                    break;
                                case "VALUE":
                                case "CONTENT":
                                    $HData['content'] = $PData;
                                    break;
                                default:
                                    $TDetail .= " {$PName}=\"{$PData}\"";
                            }
                        }
                        $HData['detail'] = $TDetail;
                        $FKey = $HData['content'];
                        $CKey = $FKey;
                        $Controller[$FKey] = $HData;
                    }
                    elseif (is_string($FKey) && !empty($FControl))
                    {
                        $CKey = $FKey;
                        if (is_array($FControl))
                        {
                            $TDetail = " class=\"form_line\" style=\"white-space:nowrap;\"";
                            foreach ($FControl as $PName => $PData)
                            {
                                $PName = strtoupper($PName);
                                switch ($PName)
                                {
                                    case "VALUE":
                                    case "CONTENT":
                                        $Controller[$FKey] = $PData;
                                        break;
                                    default:
                                        $TDetail .= " {$PName}=\"{$PData}\"";
                                }
                                $Controller[$FKey]['control'] = (int)1;
                                $Controller[$FKey]['type'] = "FORM_CONTENT_DATA";
                                $Controller[$FKey]['detail'] = $TDetail;
                            }
                        }
                        else
                        {
                            $Controller[$FKey]['content'] = $FControl;
                            $Controller[$FKey]['control'] = (int)1;
                            $Controller[$FKey]['type'] = "FORM_CONTENT_DATA";
                            $Controller[$FKey]['detail'] = " class=\"form_line\" style=\"white-space:nowrap;\"";
                        }
                    }
                    elseif (empty($FControl) && is_int($FKey))
                    {
                        $Controller[$CKey]['control']++;
                    }
                }
                foreach ($Controller as $FKey => $FControl)
                {
                    if ((int)$iNxs >= (int)$Form_Line && $iNxs > 0)
                    {
                        $WSel .= "</tr><tr>";
                        $iRow++;
                        $iNxs = (int)0;
                    }
                    $iColspan = (int)1;
                    if ($FControl['control'] > (int)1)
                    {
                        if (($FControl['control'] > ($iNxs % $Form_Line) && $iNxs > 0) || ($iNxs ===
                            0 && $FControl['control'] > $Form_Line))
                        {
                            if ($iNxs % $Form_Line > 0)
                            {
                                $iColspan = ($Form_Line - $iNxs % $Form_Line) * 2 - 1;
                                $iNxs += (int)$FControl['control'] + ($iNxs % $Form_Line) - (int)1;
                            }
                        }
                        else
                        {
                            $iNxs += (int)$FControl['control'] - (int)1;
                            $iColspan = $FControl['control'] * 2 - 1;
                        }
                    }
                    else
                    {
                        $iColspan = (int)1;
                    }
                    if ($iPos === count($Controller) - 1)
                    {
                        $iColspan = ($Form_Line - $iNxs % $Form_Line) * 2 - 1;
                    }
                    switch ($FControl['type'])
                    {
                        case "FORM_HEADER_CONTENT":
                            $iColspan++;
                            $WSel .= "<td valign=\"top\" class=\"form_header\" style=\"white-space:nowrap;\"" . (($iColspan >
                                1) ? " colspan=\"{$iColspan}\"" : "") . ">{$FControl['content']}</td>";
                            break;
                        case "FORM_CONTENT_ONLY":
                            $iColspan++;
                            $WSel .= "<td valign=\"top\" class=\"form_line\" style=\"white-space:nowrap;\"" . (($iColspan >
                                1) ? " colspan=\"{$iColspan}\"" : "") . ">{$FControl['content']}</td>";
                            break;
                        default:
                            $WSel .= "<td valign=\"top\" class=\"form_lable\" style=\"white-space:nowrap;\">{$FKey}</td><td class=\"form_line\" style=\"white-space:nowrap;\"" . (($iColspan >
                                1) ? " colspan=\"{$iColspan}\"" : "") . ">{$FControl['content']}</td>";
                    }
                    $iPos++;
                    $iNxs++;
                }
                $WSel .= "</tr>";
            }
            if (!empty($Form_Button))
            {
                $WSel .= "<tr><td align=\"center\" colspan=\"" . ($Form_Line * 2) . "\" class=\"form_button\">";
                if (is_array($Form_Button))
                {
                    $iPos = 0;
                    foreach ($Form_Button as $FButton)
                    {
                        $WSel .= (($iPos > 0) ? " " : "") . $FButton;
                        $iPos++;
                    }
                }
                else
                {
                    $WSel .= $Form_Button;
                }
                $WSel .= "</td></tr>";
            }
            $WSel .= "</table>";
            return $WSel;
        }
    }

    public function Js_UpFile($Value, $SName, $FAction, $FInput = null, $OnComplete = null, $ShwCls =
        'link', $CStyle = null, $Disable = null)
    {
        return $this->JS_Link($Value, "document.getElementById('{$SName}').click();", "{JS_UPLOADNAME}_Link_{$SName}") .
            "<span id=\"{JS_UPLOADNAME}_Status_{$SName}\" style=\"display:none;\">&nbsp;</span>" . $this->
            FORM($FInput, $FAction, "{JS_UPLOADNAME}_Frame_{$SName}", "{JS_UPLOADNAME}_Form_{$SName}",
            "multipart/form-data", null, 'display: none;') . $this->File($SName, "onchange=\"document.getElementById('{JS_UPLOADNAME}_Frame_{$SName}').onload=function(){_\$.Obj.SetDisplay('{JS_UPLOADNAME}_Link_{$SName}','block');_\$.Obj.SetDisplay('{JS_UPLOADNAME}_Status_{$SName}','none');{$OnComplete}};_\$.Obj.SetDisplay('{JS_UPLOADNAME}_Link_{$SName}','none');_\$.Obj.SetDisplay('{JS_UPLOADNAME}_Status_{$SName}','block');_\$.Obj.SetHTML('{JS_UPLOADNAME}_Status_{$SName}','" .
            Lang("Loading") . "');document.getElementById('{JS_UPLOADNAME}_Form_{$SName}').submit();\"",
            $Disable, '', 'display:none;') . $this->EForm() . "<iframe id=\"{JS_UPLOADNAME}_Frame_{$SName}\" name=\"{JS_UPLOADNAME}_Frame_{$SName}\" style=\"display:none;\"></iframe>";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Button_Only($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . " href=\"" . $this->Link_Ajax("AOnly", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ?
            " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!
            empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " value=\"{$SValue}\">";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Button_Execute($SValue, $URL, $Data = null, $Config = null, $AddJs = null,
        $SName = null, $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . " href=\"" . $this->Link_Ajax("AExec", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ?
            " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!
            empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " value=\"{$SValue}\">";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Button_Function($SValue, $URL, $Data = null, $Config = null, $AddJs = null,
        $SName = null, $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . " href=\"" . $this->Link_Ajax("AFnc", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ?
            " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!
            empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " value=\"{$SValue}\">";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Button_Object($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . " href=\"" . $this->Link_Ajax("AObj", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ?
            " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!
            empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " value=\"{$SValue}\">";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Button_Fancy($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        global $GD, $_ImgBtn;
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        $TBox = $GD->GDTTFBox($SValue, $_ImgBtn['Font'], $_ImgBtn['FSize'], $_ImgBtn['MarginX'], $_ImgBtn['MarginY']);
        $MWidth = $TBox['Width'] + ($_ImgBtn['MarginX'] * 2);
        $MHeight = $TBox['Height'] + ($_ImgBtn['MarginY'] * 2);
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . " href=\"" . $this->Link_Ajax("AFancy", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ?
            " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!
            empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " value=\"{$SValue}\">";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Link_Only($SValue, $URL, $Data = null, $AddJs = null, $SName = null, $ShwCls =
        'link', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . " href=\"" . $this->
            Link_Ajax("AOnly", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " style=\"cursor:pointer; {$CStyle}\">{$SValue}</a>";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Link_Execute($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'link', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . " href=\"" . $this->
            Link_Ajax("AExec", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " style=\"cursor:pointer; {$CStyle}\">{$SValue}</a>";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Link_Function($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'link', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . " href=\"" . $this->
            Link_Ajax("AFnc", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . " style=\"cursor:pointer; {$CStyle}\">{$SValue}</a>";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Link_Object($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'link', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . " href=\"" . $this->
            Link_Ajax("AObj", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " style=\"cursor:pointer; {$CStyle}\">{$SValue}</a>";
    }

    # Return the Ajax Request Object Link or script
    public function Ajax_Link_Fancy($SValue, $URL, $Data = null, $Config = null, $AddJs = null, $SName = null,
        $ShwCls = 'link', $CStyle = null, $Disable = null)
    {
        $AddJs = str_replace("\"", "\\\"", $AddJs);
        return "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . " href=\"" . $this->
            Link_Ajax("AFancy", $URL, $Data, $Config) . "\"" . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " style=\"cursor:pointer; {$CStyle}\">{$SValue}</a>";
    }

    # Return the Ajax JS Page Loading Link or script link
    public function Ajax_Button_Close_Fancy($SValue, $AddJs = null, $SName = null, $ShwCls = 'btn',
        $CStyle = null, $Disable = null)
    {
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . "href=\"fancy:close\"" . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" : "") . (!
            empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . " value=\"{$SValue}\">";
    }

    # Return the Ajax JS Page Loading Link or script link
    public function Ajax_Link_Close_Fancy($SValue, $AddJs = null, $SName = null, $ShwCls = 'link', $CStyle = null,
        $Disable = null)
    {
        return "<a" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . " href=\"fancy:close\"" . (!
            empty($AddJs) ? " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . " style=\"cursor:pointer; {$CStyle}\">{$SValue}</a>";
    }

    public function Ajax_Ask_Box($Box_Title, $Box_Message, $Action_Button, $Width = '500px')
    {
        $WButton = "";
        if (is_array($Action_Button))
            foreach ($Action_Button as $FButton)
                $WButton .= (!empty($WButton) ? " " : "") . $FButton;
            else
                $WButton .= $Action_Button;
        return "<div style=\"padding:10px; width:{$Width};\"><table width=\"100%\" align=\"center\">
	<tr><th align=\"left\" style=\"padding:3px; padding-top:30px; border-bottom:1px solid;\">{$Box_Title}</th></tr>
	<tr><th align=\"left\" style=\"padding:50px 10px 50px 50px; border-bottom:1px solid;\">{$Box_Message}?</td></tr>
    <tr><th align=\"center\" style=\"padding:3px;\">{$WButton} " . $this->Ajax_Button_Close_Fancy(Lang
            ("Cancel")) . "</th></tr></table></div>";
    }

    # Return the Ajax Request Object Link or script
    public function ActRequest_Button($SValue, $Url, $Token, $DataForm, $FeedbackObj = null, $Reload = null,
        $SName = null, $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . " href=\"" . $this->
            Link_ActReqeust($Url, $Token, $DataForm, $FeedbackObj, $Reload) . "\" value=\"{$SValue}\">";
    }

    public function ActRequest_Link($SValue, $Url, $Token, $DataForm, $FeedbackObj = null, $Reload = null,
        $SName = null, $ShwCls = 'btn', $CStyle = null, $Disable = null)
    {
        return "<a type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" : "") . (!
            empty($ShwCls) ? " class=\"{$ShwCls}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" : "") . " href=\"" . $this->
            Link_ActReqeust($Url, $Token, $DataForm, $FeedbackObj, $Reload) . "\">{$SValue}</a>";
    }

    # Return the Ajax Request Object Link or script
    public function JS_Button($SValue, $AddJs, $Href = '#', $SName = null, $ShwCls = 'btn', $CStyle = null,
        $Disable = null)
    {
        return "<input type=\"button\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" :
            "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" :
            "") . (!empty($Href) ? "  href=\"{$Href}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . " value=\"{$SValue}\">";
    }

    # Return the Java script link
    public function JS_Link($SValue, $AddJs, $SName = null, $ShwCls = 'link', $CStyle = null, $Disable = null)
    {
        return (($this->Print_Mode == false) ? "<a href=\"#\"" . (!empty($SName) ? " name=\"{$SName}\" id=\"{$SName}\"" :
            "") . (!empty($AddJs) ? " onclick=\"{$AddJs}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" :
            "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . (!empty($Disable) ? " disabled=\"{$Disable}\"" :
            "") . ">{$SValue}</a>" : $SValue);
    }

    # Create HTML Image Object with thumnail system or self.
    public function Image($Config = null, $Title = null, $Align = null, $OnEvent = null, $SName = null,
        $ShwCls = 'Img', $CStyle = null)
    {
        global $Sys_Path; #Global univasal varilable for using.
        if (!array_key_exists("Image", $Config))
            $Image = "{SPTH_IMGSYS}no_picture.jpg";
        else
            $Image = SysConvVar($Config['Image']);
        if (strpos($Image, "http") === false)
        {
            if (strpos($Image, $Sys_Path['BASE']) !== false)
            {
                if (file_exists($Image) === false)
                    $Image = "{SPTH_IMGSYS}no_picture.jpg";
                $Image = str_replace(substr($Sys_Path['BASE'], 0, -1), "", $Image);
            }
            elseif (file_exists(substr($Sys_Path['BASE'], 0, -1) . $Image) === false)
                $Image = "{SPTH_IMGSYS}no_picture.jpg";
        }
        $Config['Image'] = $Image;
        $Title = strip_tags(str_replace("\"", "'", $Title));
        return "<img src=\"{SPG_IMG}?" . $this->Token($Config, true) . "\"" . (!empty($Title) ?
            " title=\"{$Title}\"" : "") . (!empty($Align) ? " align=\"{$Align}\"" : "") . (!empty($Config['Width']) ?
            " width=\"{$Config['Width']}\"" : "") . (!empty($Config['Height']) ? " align=\"{$Config['Height']}\"" :
            "") . (!empty($OnEvent) && !empty($OnEvent) ? " {$OnEvent}" : "") . (!empty($SName) ?
            " name=\"{$SName}\" id=\"{$SName}\"" : "") . (!empty($ShwCls) ? " class=\"{$ShwCls}\"" :
            "") . (!empty($CStyle) ? " style=\"{$CStyle}\"" : "") . " />";
    }

    public function WaveText($TValue, $MinFont = 5, $MaxFont = 20, $Split = 1)
    {
        if (strlen($TValue) == 0)
        {
            return '';
        }
        $FNow = $MinFont;
        $Direction = 0;
        for ($i = 0; $i <= strlen($TValue); $i++)
        {
            if ($FNow >= $MaxFont)
            {
                $Direction = -abs($Split);
            }
            elseif ($FNow <= $MinFont)
            {
                $Direction = abs($Split);
            }
            $SelWel .= "<font style=\"font-size:{$FNow}px;\">" . substr($TValue, $i, 1) . "</font>";
            $FNow += $Direction;
        }
        return $SelWel;
    }

    public function WebText($TValue, $FSize = null, $FFace = null, $FColor = null, $Backcolor = null)
    {
        if (strlen($TValue) == 0)
        {
            return '';
        }
        $StrCount = nl2br($TValue);
        if (!empty($FColor) || !empty($Backcolor) || !empty($FFace) || !empty($FSize))
        {
            return "<span style=\"" . (!empty($FColor) ? "color: {$FColor}; " : "") . (!empty($Backcolor) ?
                "background: {$Backcolor}; " : "") . (!empty($FName) ? "font-face: {$FFace};" : "") . (!
                empty($FSize) ? "font-size: {$FSize};" : "") . "\">{$StrCount}</span>";
        }
    }

    # Return the time(string) from hour, min and second
    public function TimetoStr($FHour, $FMin, $FSec)
    {
        $FHour = (int)$FHour;
        $FMin = (int)$FMin;
        $FSec = (int)$FSec;
        return (($FHour < 10) ? "0{$FHour}" : $FHour) . ":" . (($FMin < 10) ? "0{$FMin}" : $FMin) .
            ":" . (($FSec < 10) ? "0{$FSec}" : $FSec);
    }

    # Return the date(string) from year, month and day
    public function DatetoStr($FYear, $FMonth, $FDay)
    {
        $FYear = (int)$FYear;
        $FMonth = (int)$FMonth;
        $FDay = (int)$FDay;
        return (($FYear < 10) ? "0{$FYear}" : $FYear) . "-" . (($FMonth < 10) ? "0{$FMonth}" : $FMonth) .
            "-" . (($FDay < 10) ? "0{$FDay}" : $FDay);
    }

    # Return the Ajax Request Object Link or script
    public function WebJs($JScript)
    {
        $this->Web_Js .= (substr($this->Web_Js, -1) === ";" || empty($this->Web_Js) ? "" : ";") .
            SysConvVar($JScript);
    }

    public function WebPut($WebCode)
    {
        if (strlen($WebCode) > 0)
        {
            if (function_exists('SysConvVar'))
            {
                $this->Web .= SysConvVar($WebCode);
            }
            else
            {
                $this->Web .= $WebCode;
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function Log($SrtValue)
    {
        $this->Log_Data .= $StrValue . "\n";
    }

    public function GetLog()
    {
        return $this->Log_Data;
    }

    public function WebGet()
    {
        return $this->Web;
    }

    public function WebClear()
    {
        $this->Web = '';
    }

    public function WebOut($Clear = true)
    {
        if ($this->Block_Ouput === false)
        {
            if (!empty($this->Web_Js))
                $this->Web .= (strpos($this->Web, SysConvVar("{JS_AJAXSP}")) === (boolean)false ?
                    SysConvVar("{JS_AJAXSP}") : "") . $this->Web_Js;
            $HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"];
            $Content = $this->Web;
            $Encode = '';
            if (headers_sent())
                $Encode = '';
            elseif (strpos($HTTP_ACCEPT_ENCODING, 'gzip') !== (boolean)false)
                $Encode = 'gzip';
            elseif (strpos($HTTP_ACCEPT_ENCODING, 'deflate') !== (boolean)false)
                $Encode = 'deflate';
            $_Size = strlen($Content);
            if ($_Size < 1024)
                echo $Content;
            else
            {
                if ($this->Web_Compress === (boolean)false)
                    $Encode = '';
                switch ($Encode)
                {

                    case "deflate":
                        $Content = gzdeflate($Content, 9);
                        header("Content-Encoding: deflate");
                        header("Content-Length: " . strlen($Content));
                        break;

                    case "gzip":
                        $Content = gzcompress($Content, 9);
                        $Content = "\x1f\x8b\x08\x00\x00\x00\x00\x00{$Content}";
                        header("Content-Encoding: gzip");
                        header("Content-Length: " . strlen($Content));
                        break;
                }
                echo $Content;
            }
            if ($Clear === (boolean)true)
                $this->Web = (string )'';
            return true;
        }
    }

    public function Block_Ouput($Block_Ouput)
    {
        $this->Block_Ouput = $Block_Ouput;
        return $Block_Ouput;
    }

    public function SmartShow($Text)
    {
        return nl2br(htmlspecialchars($Text));
    }

    function StrFilter($StrValue)
    {
        while (ereg("[ ]{2,}", $StrValue, $reg))
        {
            $StrValue = str_replace($reg[0], " ", $StrValue);
        }
        while (ereg("\r\n{2,}", $StrValue, $reg))
        {
            $StrValue = str_replace($reg[0], "\r\n", $StrValue);
        }
        while (eregi("(<a+)(.*)(href+)(.*)(>+)(.*)(</a>+)", $StrValue, $reg))
        {
            $StrValue = str_replace($reg[0], "", $StrValue);
        }
        while (eregi("(http*)(s*)(://*)([w]{0,3})(.+)\.+(.+)\.(.{3,})", $StrValue, $reg))
        {
            $StrValue = str_replace($reg[0], "", $StrValue);
        }
        if (eregi("(.*)(naked*|nude*|nudist*|naughty*|adult dating*|porn*|adult video*|adult movie*|fuck*|sex)(.*)",
            $StrValue, $reg))
        {
            $StrValue = str_replace($StrValue, "", $StrValue);
        }
        $StrValue = $this->ESCAPE_STRING(strip_tags(trim($StrValue),
            "<li><ul><ol><font><b><u><i><sup><sub><pre><h1><h2><h3><h4><h5><h6><hr><br><dd><dt><dl>"));
        return $StrValue;
    }

    public function WebOut_to_file($FileName, $Encode = 'utf-8')
    {
        $F_Status = false;
        $FP = 0;
        $Web = mb_convert_encoding($this->Web, $Encode);
        if ($FP = fopen($FileName, "w+"))
        {
            if (fwrite($FP, $Web, strlen($this->Web)))
                $F_Status = true;
            fclose($FP);
        }
        return $F_Status;
    }
}

?>