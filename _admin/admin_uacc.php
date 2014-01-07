<?php

#Create by LYK on 2009-12-12 @ 17:40PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");
$DPass = md5('1234');

switch ($Action)
{
    case "UFBReset":
        if (!empty($Sys->UID))
        {
            $__SysUser[$Sys->UID]['FBID'] = (int)0;
            Data_To_File("{DATA_SYSUSER}", $__SysUser);
        }
        break;

    case "ChgOwn":
        if (!empty($Sys->UID) && array_key_exists($Sys->UID, $__SysUser))
        {
            if (empty($FName) || empty($Lang))
                $Sys->Json_Respone('error', Lang("Please fill in the blank"));
            else
            {
                $__SysUser[$Sys->UID]['FNAME'] = $FName;
                $__SysUser[$Sys->UID]['LANG'] = $Lang;
                if (!Data_To_File("{DATA_SYSUSER}", $__SysUser))
                    $Sys->Json_Respone('error', Lang("Sorry, tempolary unable to change your profile."));
                else
                    $Sys->Json_Respone('ok', Lang("Successful update your profile."));
            }
        }
        break;

    case "ChgPass":
        if (!empty($Sys->UID) && array_key_exists($Sys->UID, $__SysUser))
        {
            $NPass = $__SysUser[$Sys->UID]['UPASS'];
            if ($NPass === md5($OPass) && $UPass === $CPass && $Sys->IsValid_Password($UPass))
                $__SysUser[$Sys->UID]['UPASS'] = md5($UPass);
            if (empty($OPass) || empty($UPass) || empty($CPass))
                $Sys->Json_Respone('respone', $Sys->Unknow(Lang("Please fill in the blank, thank you.")));
            elseif ($NPass !== md5($OPass))
                $Sys->Json_Respone('respone', $Sys->Warning(Lang("Sorry, your current password incorrect.")));
            elseif ($UPass !== $CPass)
                $Sys->Json_Respone('respone', $Sys->Warning(Lang("Your password is not same as confirm password.")));
            elseif ($Sys->IsValid_Password($UPass) === false)
                $Sys->Json_Respone('respone', $Sys->Warning(Lang("Your new password is no enough strong.")));
            elseif (!Data_To_File("{DATA_SYSUSER}", $__SysUser))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, tempolary unable to update your password.")));
            else
                $Sys->Json_Respone('ok', $Sys->Success(Lang("Successful updated, use new password on next login.")));
        }
        break;

    case "NewPass":
        if (!empty($Sys->UID) && array_key_exists($Sys->UID, $__SysUser))
        {
            $NPass = $__SysUser[$Sys->UID]['UPASS'];
            if ($UPass === $CPass && $Sys->IsValid_Password($UPass))
                $__SysUser[$Sys->UID]['UPASS'] = md5($UPass);
            if (empty($UPass) || empty($CPass))
                $Sys->Json_Respone('respone', $Sys->Unknow(Lang("Please fill in the blank, thank you.")));
            elseif ($UPass !== $CPass)
                $Sys->Json_Respone('respone', $Sys->Warning(Lang("Your password is not same as confirm password.")));
            elseif ($Sys->IsValid_Password($UPass) === false)
                $Sys->Json_Respone('respone', $Sys->Warning(Lang("Your new password is no enough strong.")));
            elseif (!Data_To_File("{DATA_SYSUSER}", $__SysUser))
                $Sys->Json_Respone('error', $Sys->Warning(Lang("Sorry, tempolary unable to update your password.")));
            else
                $Sys->Json_Respone('ok', $Sys->Success(Lang("Successful updated, use new password on next login.")));
        }
        break;

    case "UFBConnect":
        if (!empty($Sys->UID))
        {
            try
            {
                include_once SysConvVar('{SPTHR_PLUGIN}facebook/facebook.php');
                $config = array('appId' => '209366755915184', 'secret' =>
                        'b4ef743351db055d567b6b6f5c49303b');
                $facebook = new Facebook($config);
                if (array_key_exists('_FBAccess', $_REQUEST) && !empty($_REQUEST['_FBAccess']))
                    $facebook->setAccessToken($_REQUEST['_FBAccess']);
                $__SysUser[$Sys->UID]['FBID'] = (int)$facebook->getUser();
                Data_To_File("{DATA_SYSUSER}", $__SysUser);
                $Sys->WebPut(json_encode(array("Status" => "ok", "Msg" => $Sys->Success(Lang("Your account has been successfully linked with the currently logged Facebook account.")))));
            }
            catch (exception $e)
            {
                $Sys->WebPut(json_encode(array("Status" => "error", "Msg" => $Sys->Warning(Lang("Sorry, we have problems connecting to Facebook now, please try again later.")))));
            }
        }
        else
            $Sys->WebPut("error");
        $Sys->WebOut();
        exit;

    case "UFBLogin":
        $Msg = '';
        if (empty($Sys->UID))
        {
            try
            {
                include_once SysConvVar('{SPTHR_PLUGIN}facebook/facebook.php');
                $config = array('appId' => '209366755915184', 'secret' =>
                        'b4ef743351db055d567b6b6f5c49303b');
                $facebook = new Facebook($config);
                if (!empty($_REQUEST['_FBAccess']))
                    $facebook->setAccessToken($_REQUEST['_FBAccess']);
                $FBID = $facebook->getUser();
                if (!empty($FBID))
                {
                    foreach ($__SysUser as $_UID => $_User)
                    {
                        if ($_User['FBID'] === (int)$FBID && $_User['ACCESS'] === true)
                        {
                            $Sys->UID = $_UID;
                            $Sys->FName = $_User['FNAME'];
                            $Sys->Admin = $_User['ADMIN'];
                            $Sys->FBID = $_User['FBID'];
                            $Sys->Language = $_User['LANG'];
                        }
                    }
                    $_Sys_ULog = File_To_Data("{DATA_SYSULOG}");
                    if (!is_array())
                        $_Sys_ULog = array();
                    $_Sys_ULog[] = array(
                        "UID" => $_UID,
                        "IP" => $Sys_Host['Client'],
                        "TIME" => date("Y-m-d H:i:s"));
                    if (empty($Sys->UID))
                    {
                        unset($Sys->UID);
                        unset($Sys->FName);
                        unset($Sys->Admin);
                        $Sys->Language = $DLC_Infor['Language'];
                        $Msg = $Sys->Warning(Lang("Sorry, you facebook account was not connect to us."));
                    }
                    elseif (!Data_To_File("{DATA_SYSULOG}", $_Sys_ULog))
                    {
                        unset($Sys->UID);
                        unset($Sys->FName);
                        unset($Sys->Admin);
                        $Sys->Language = $DLC_Infor['Language'];
                        $Msg = $Sys->Warning(Lang("Sorry, tempolary unable to login, please contact us."));
                    }
                    else
                    {
                        $Sys->Admin = $Sys->Admin;
                        $_SESSION['UID'] = $Sys->UID;
                        $_SESSION['FName'] = $Sys->FName;
                        $_SESSION['Admin'] = $Sys->Admin;
                    }
                }
                else
                    $Msg = $Sys->Unknow(Lang("You are not login."));
            }
            catch (exception $e)
            {
                $Sys->WebPut("Sorry, we have problem with facebook login now.");
            }
        }
        else
            $Msg = $Sys->Success(Lang("You are already login."));
        $Sys->WebPut(json_encode(array(
            "Status" => (!empty($Msg) ? "error" : "ok"),
            "Msg" => $Msg,
            'UID' => (int)$Sys->UID,
            'FBID' => (int)$Sys->FBID,
            'Admin' => $Sys->Admin,
            'FName' => $Sys->FName,
            'Lang' => $__SysUser[$Sys->UID]['LANG'],
            'Execute' => ($__SysUser[$Sys->UID]['UPASS'] === $DPass ? 'Dashboard.NewPass();' : ''),
            'Profile' => $__SysUser[$Sys->UID]['PROFILE'])));
        $Sys->WebOut();
        exit;

        #Web User Login Process
    case "ULogin":
        $Msg = '';
        if (empty($Sys->UID))
        {
            if (!empty($UName) && !empty($UPass))
            {
                $UName = strtolower($UName);
                foreach ($__SysUser as $_UID => $_User)
                {
                    if ($_User['UNAME'] === $UName && $_User['UPASS'] === md5($UPass) && $_User['ACCESS']
                        === true)
                    {
                        $Sys->UID = $_UID;
                        $Sys->FName = $_User['FNAME'];
                        $Sys->Admin = (boolean)$_User['ADMIN'];
                        $Sys->FBID = $_User['FBID'];
                    }
                }
                $_Sys_ULog = File_To_Data("{DATA_SYSULOG}");
                if (!is_array())
                    $_Sys_ULog = array();
                $_Sys_ULog[] = array(
                    "UID" => $_UID,
                    "IP" => $Sys_Host['Client'],
                    "TIME" => date("Y-m-d H:i:s"));
                if (empty($Sys->UID))
                {
                    unset($Sys->UID);
                    unset($Sys->Admin);
                    $Sys->Lang = $DLC_Infor['Language'];
                    $Msg = $Sys->Warning(Lang("Sorry, your user name or password is wrong."));
                }
                elseif (!Data_To_File("{DATA_SYSULOG}", $_Sys_ULog))
                {
                    unset($Sys->UID);
                    unset($Sys->Admin);
                    $Sys->Lang = $DLC_Infor['Language'];
                    $Msg = $Sys->Warning(Lang("Sorry, tempolary unable to login, please contact us."));
                }
                else
                    $_SESSION['UID'] = $Sys->UID;
            }
            else
                $Msg = $Sys->Unknow(Lang("Please fill in the blank."));
        }
        else
            $Msg = $Sys->Success(Lang("You are already login."));
        $Sys->WebPut(json_encode(array(
            "Status" => (!empty($Msg) ? "error" : "ok"),
            "Msg" => $Msg,
            'UID' => (int)$Sys->UID,
            'FBID' => (int)$Sys->FBID,
            'Admin' => (int)$Sys->Admin,
            'FName' => $Sys->FName,
            'Lang' => $__SysUser[$Sys->UID]['LANG'],
            'Execute' => ($__SysUser[$Sys->UID]['UPASS'] === $DPass ? 'Dashboard.NewPass();' : ''),
            'Profile' => $__SysUser[$Sys->UID]['PROFILE'])));
        $Sys->WebOut();
        exit;

        #Web Login User/Member Logout Process
    case "ULogout":
        unset($_SESSION['UID']);
        unset($Sys->UID);
        unset($Sys->Admin);
        $Sys->Lang = $Default_Infor['Lang'];
        session_destroy();
        $Sys->WebPut("ok");
        $Sys->WebOut();
        exit;

    case "Status":
        $Sys->WebPut(json_encode(array(
            'UID' => (int)$Sys->UID,
            'FBID' => (int)$Sys->FBID,
            'Admin' => $Sys->Admin,
            'FName' => $Sys->FName,
            'Lang' => $__SysUser[$Sys->UID]['LANG'],
            'Execute' => ($__SysUser[$Sys->UID]['UPASS'] === $DPass ? 'Dashboard.NewPass();' : ''),
            'Profile' => $__SysUser[$Sys->UID]['PROFILE'])));
        $Sys->WebOut();
        exit;
}

switch ($UI)
{
    case "Msg":
        $Title = (!empty($Title) ? $Title : 'System Status');
        $Sys->WebJs("Dashboard.ActMsg('{$Status}', '{$Title}');");
        break;

    case "Menu":
        function GenMenu($SysLst, $UPermit) {
            global $Sys;
            $Menu = array();
            foreach ($SysLst as $Key => $Item)
            {
                $Permited = false;
                switch ($Item['Permit'])
                {
                    case "Login":
                        if (!empty($Sys->UID))
                            $Permited = true;
                        break;

                    case "Admin":
                        $Permited = (boolean)$Sys->Admin;
                        break;

                    case "Permit":
                        if (isset($UPermit[$Key]) || $Sys->Admin === true)
                            $Permited = true;
                        break;
                }
                if ($Permited === true)
                {
                    if (isset($Item["Sub"]))
                        $Menu[$Item['Title']] = GenMenu($Item['Sub'], $UPermit);
                    else
                    {
                        $Href = "Dashboard:MItem::" . ($Item['Type'] === "Ajax_Request" ? "Object" :
                            "Page") . "::{{$Item['FCode']}}::{$Item['ExtCmd']}";
                        $Menu[$Item['Title']] = $Href;
                    }
                }
            }
            return $Menu;
        }
        $UPermit = File_To_Data("{DATA_SYSUPERMIT}");
        $UPermit = $UPermit[$Sys->UID];
        if (!isset($UPermit))
            $UPermit = array();
        $Sys->WebPut(json_encode(GenMenu($Syslist, $UPermit)));
        break;

    case "ChgPass":
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("Change Your Password")));
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("Valid Password : Alphanumeric, 6 - 32 length"),
            array(
            Lang("Current Password") => $Sys->Password("OPass", '', 'off') . $Sys->Warning("*") . $Sys->
                Span("OPass_Status"),
            Lang("New Password") => $Sys->Password("UPass", '', 'off') . $Sys->Warning("*") . $Sys->
                Span("UPass_Status"),
            Lang("Confirm Password") => $Sys->Password("CPass", '', 'off') . $Sys->Warning("*") . $Sys->
                Span("CPass_Status"),
            Lang("Status") => $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"),
                "{SPG_UACC}", array('Action' => $UI), "#ChgPassForm", '#Status', null, 'btn_chgpass')),
            1, "ChgPassForm", 'center', 1, '400px'), Lang("Change Your Password")));
        $Sys->WebJs($Sys->Js_BindKey('enter', "function(){_$('#btn_chgpass').Click();}") . $Sys->
            Js_ObjFocus('#OPass') . $Sys->Js_Validate("#OPass", array(
            "Method" => "must",
            "Min" => 4,
            "Feedback" => array("#OPass_Status" => $Sys->Warning("Please fill the blank.")))) . $Sys->
            Js_Validate("#UPass", array(
            "Method" => "must",
            "Min" => 6,
            "Feedback" => array("#UPass_Status" => $Sys->Warning("Please fill at least 6 characters.")))) .
            $Sys->Js_Validate("#CPass", array(
            "Method" => "must",
            "Min" => 6,
            "Feedback" => array("#CPass_Status" => $Sys->Warning("Please fill at least 6 characters.")))));
        break;

    case "NewPass":
        $Status = (!empty($Status) ? $Status : $Sys->Normal(Lang("Update your new password here.")));
        $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("Valid Password : Alphanumeric, 6 - 32 length"),
            array(
            Lang("New Password") => $Sys->Password("UPass", '', 'off') . $Sys->Warning("*") . $Sys->
                Span("UPass_Status"),
            Lang("Confirm Password") => $Sys->Password("CPass", '', 'off') . $Sys->Warning("*") . $Sys->
                Span("CPass_Status"),
            Lang("Status") => $Sys->Span('Status', $Status)), array($Sys->ActRequest_Button(Lang("Submit"),
                "{SPG_UACC}", array('Action' => $UI, 'UI' => $UI), "#ChgPassForm", '#Status', null,
                'btn_chgpass')), 1, "ChgPassForm", 'center', 1, '400px'), Lang("Update New Password")));
        $Sys->WebJs($Sys->Js_BindKey('enter', "function(){_$('#btn_chgpass').Click();}") . $Sys->
            Js_ObjFocus('#UPass') . $Sys->Js_Validate("#UPass", array(
            "Method" => "must",
            "Min" => 6,
            "Feedback" => array("#UPass_Status" => $Sys->Warning("Please fill at least 6 characters.")))) .
            $Sys->Js_Validate("#CPass", array(
            "Method" => "must",
            "Min" => 6,
            "Feedback" => array("#CPass_Status" => $Sys->Warning("Please fill at least 6 characters.")))));
        break;

    case "main":
        if (!empty($Sys->UID))
            $Sys->Webput($Sys->Box("Please click the left top corner menu to start. ", "Welcome",
                '96%', '2%', 'max-width:50em;'));
        else
        {
            $Sys->Webput("<div id=\"login_form\" class=\"page\" style=\"width:20em; padding:20px; border: 1px solid #ccc; background:#fff;\">
        <div class=\"title\">Login</div>
        <div>
            <span class=\"field\">User Name</span>
            <span class=\"fieldtext\"><input type=\"text\" id=\"UName\" /></span>
        </div>
        <div>
            <span class=\"field\">Password</span>
            <span class=\"fieldtext\"><input type=\"password\" id=\"UPass\" /></span>
        </div>
        <div id=\"login_error\" style=\"margin-top:1em;\">&nbsp;</div>
        <div style=\"margin-top:1em; text-align:center;\">
            <input type=\"button\" class=\"btn\" value=\"" . Lang("Login") . "\" href=\"Dashboard:Login\" />
            <input type=\"reset\" class=\"btnlow\" value=\"" . Lang("Reset") . "\" href=\"Dashboard:Reset\" />
        </div>
        <div id=\"sec_facebook\" style=\"display:none;\">
            <hr style=\"margin:1em 0em;\" />
            <div class=\"facebook\" href=\"Dashboard:FBLogin\">&nbsp;</div>
        </div>
    </div>");
            $Sys->WebJs("Dashboard.Reset();");
        }
        break;

    default:
        if (!empty($Sys->UID))
        {
            $User = $__SysUser[$Sys->UID];
            $Sys->WebPut($Sys->Box($Sys->Build_Form(Lang("User Access"), array(
                Lang("User Name") => $Sys->Normal($User['UNAME']),
                Lang("Password") => $Sys->Ajax_Link_Fancy(Lang("Change Password"), "{SPG_UACC}",
                    array("UI" => "ChgPass")),
                Lang("Link to Facebook") => (empty($User['FBID']) ? $Sys->Unknow("(" . Lang("You have not link to facebook account.") .
                    ")") : $Sys->Normal(Lang("Linked to facebook") . "(" . $Sys->Bold($User['FBID'])) .
                    ") - " . $Sys->Ajax_Link_Object(Lang("Reset"), "{SPG_UACC}", array("Action" =>
                        "UFBReset"))) . " <div id=\"sys_fbconnect\" class=\"fbconnect\" href=\"Dashboard:FBConnectAsk::function(){Dashboard.AObj('{SPG_UACC}');}\">&nbsp;</div>")) .
                "<br />" . $Sys->Build_Form(Lang("User Profile"), array(Lang("Full Name") => $Sys->
                    Text('FName', $User['FNAME']), Lang("Language") => $Sys->SelStr("Lang", $Lang_Bag,
                    $User['LANG'])), array($Sys->ActRequest_Button(Lang("Update"), "{SPG_UACC}",
                    array("Action" => "ChgOwn"), "#ChgOwn")), 1, 'ChgOwn'), Lang("User Profile"),
                '50em'));
        }
        else
            $Sys->WebJs("Dashboard.MBox('" . $Sys->Warning(Lang("Please login and try agian.")) .
                "','" . Lang("Need Login") . "')");
        break;
}
$Sys->WebOut();

?>