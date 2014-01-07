<?php

#Create by LYK 2012-12-12 11:40AM

#-------------------------------------------------------------------------------------------
# System Error handling, set to silent error report (If production)
#-------------------------------------------------------------------------------------------
error_reporting(E_ALL - E_NOTICE);

#-------------------------------------------------------------------------------------------
# Including System Variable
#-------------------------------------------------------------------------------------------
include_once (ROOT . "_common/common_sysvar.php");

#-------------------------------------------------------------------------------------------
# Include Function
#-------------------------------------------------------------------------------------------
include_once ($Sys_Page['REAL']['Function']);

#----------------------------------------------------------------------------------------
# Setup Error/Exception Handle
#-------------------------------------------------------------------------------------------
set_error_handler("Sys_Error");
set_exception_handler("Sys_Exception");

#-------------------------------------------------------
# Include Application Variable
#-------------------------------------------------------
include_once ($Sys_Page['REAL']['AppVar']);

#----------------------------------------------------------------------------------------
# Declare Header, Setup not able to cache data
#-------------------------------------------------------------------------------------------
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); # Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); #Change Last Edit date to now.
header("Cache-Control: no-cache, must-revalidate"); # HTTP/1.1
header("Pragma: no-cache"); # HTTP/1.0
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

#-------------------------------------------------------------------------------------------
# Import Class
#-------------------------------------------------------------------------------------------
include_once ($Class_Page['GD']); #Class of Mongo Database
include_once ($Class_Page['Chiper']); #Class of Data Encript or descript System
include_once ($Class_Page['ObjCls']); #Class of Web Object Control System
include_once ($Class_Page['ClsData']); #Class of System Data Setting Query System
include_once ($Class_Page['MYSQL']); #Class of MYSQL (Server Query Language) Control System
include_once ($Class_Page['MSSQL']); #Class of MSSQL (Server Query Language) Control System
include_once ($Class_Page['SQLSRV']); #Class of MSSQL (Server Query Language) Control System
include_once ($Class_Page['CURL']); #Class of CURL (to access web.)
include_once ($Class_Page['XML']); #Class of XML (To parser xml document)
include_once ($Class_Page['Email']); #Class of Mail (Send and Receive mail) Mailing System
include_once ($Class_Page['IBrowser']); #Class of determine browser
include_once ($Class_Page['PDF']); #Class of pdf for generate pdf file.

#-------------------------------------------------------------------------------------------
# Intializing Class
#-------------------------------------------------------------------------------------------
$Sys = new MYSQL($License['Master']);
$Sys->Xml = new XML2Array;
$Sys->GD = new GDCls;
$Sys->Email = new Email;
$GD = new GDCls;
$IBrowser = new IBrowser;

#-------------------------------------------------------------------------------------------
# License Check.
#-------------------------------------------------------------------------------------------

#Variable Intializing
$LCF = array();
$Company = array();
$AppList = array();
$DBC_License = array();
$DEmail = array();
$License_Content = '';
$DEmail = $Default_Email;

#Retrive License from License File Information
if (file_exists($Sys_Page['REAL']['LFile'])) {
    $License_Content = $Sys->Decrypt(file_get_contents($Sys_Page['REAL']['LFile']));
    $LCF = unserialize($License_Content);
    if ($LCF !== false) {
        if (key_exists("Company", $LCF) && !empty($LCF['Company']))
            $Company = $LCF['Company'];
        if (key_exists("Email", $LCF) && !empty($LCF['Email']))
            $DEmail = $LCF['Email'];
        if (key_exists("License", $LCF) && !empty($LCF['License']))
            $License = $LCF['License'];
    }
    if ($DLC_Infor['Product'] == $License['Product'] || is_null($DLC_Infor['Product'])) {

        #Retrive License Information
        $License['Expired'] = (!empty($LCF['License']['Expired']) ? $LCF['License']['Expired'] :
            $DLC_Infor['Expired']);

        #Reformat Company Information
        $Company['Title'] = (!empty($Company['Title']) ? $Company['Title'] : $DLC_Infor['Title']);
        $Company['Code'] = (!empty($Company['Code']) ? $Company['Code'] : "-");
        $Company['Tel'] = (!empty($Company['Tel']) ? $Company['Tel'] : "-");
        $Company['Fax'] = (!empty($Company['Fax']) ? $Company['Fax'] : "-");
        $Company['Address'] = (!empty($Company['Address']) ? $Company['Address'] : "-");
        $Company['Web'] = (!empty($Company['Web']) ? $Company['Web'] : "-");
        $Company['Email'] = (!empty($Company['Email']) ? $Company['Email'] : "-");
        $Company['Logo'] = (!empty($Company['Logo']) ? $Company['Logo'] : "?");

        #Check License is expire
        if ($License['Expired'] != 'x-limit' && time() > strtotime($License['Expired']) &&
            $License['System'] == false)
            die($Sys->Box($Sys->Warning($Sys->Strong(Lang("Your license is expired, please renew your license."))),
                Lang("License Expired"), 30, 'center', '500px', '#FFFFFF'));
    } elseif ($License['System'] == false) {
        die($Sys->Box($Sys->Warning($Sys->Strong(Lang("Your License is invalid, system stopped."))),
            Lang("Invalid License"), 30, 'center', '500px', '#FFFFFF'));
    } else {
        $Company['Title'] = $DLC_Infor['Title'];
    }
} elseif ($License['System'] == false && $License['Trial'] == true && time() <
filectime($_SERVER['SCRIPT_FILENAME'])) {
    $Company['Title'] . "(" . Lang("Trial") . ")";
} elseif ($License['System'] == false && $License['Trial'] == false) {
    die($Sys->Box($Sys->Warning($Sys->Strong(Lang("License file was missing, system cannot start."))),
        Lang("License Missing"), 30, 'center', '500px', '#FFFFFF'));
} else
    $License['Product'] = $DLC_Infor['Product'];

#-------------------------------------------------------------------------------------------
# Import Language File
#-------------------------------------------------------------------------------------------
$Sys->Language = (!empty($Sys->Language) ? $Sys->Language : $DLC_Infor['Language']);
if (file_exists($Sys_Page['Lang']['Master']) && file_exists($Sys_Page['Lang'][$Sys->
    Language]) && is_array($Lang_Master)) {
    include_once ($Sys_Page['Lang']['Master']);
    $Lang_Loader = file($Sys_Page['Lang'][$Sys->Language]);
    $Language = array_combine($Lang_Master, $Lang_Loader);
    unset($Lang_Master);
    unset($Lang_Loader);
}

#-------------------------------------------------------------------------------------------
# Session & Variable Intializing
#-------------------------------------------------------------------------------------------
#session_name($Sys_Session['Name']);
#session_id($Sys_Session['ID']);
session_cache_expire($Sys_Session['Expired']);
session_cache_limiter($Sys_Session['Cache']);
session_start();

#-------------------------------------------------------------------------------------------
# Retrive System User/Setting From Data File
#-------------------------------------------------------------------------------------------
$__SysConfig = File_To_Data("{DATA_SYSCONFIG}");
$__SysUser = File_To_Data("{DATA_SYSUSER}");

$AppList = $Sys->GetConfig('APPLIST');
if (!is_array($AppList))
    $AppList = array();

$Lang_Bag = array( #'CHT' => Lang("Chinese Tranditional"),
        #'CHS' => Lang("Chinese Simplefy"),
    'ENG' => Lang("English") #'MLY' => Lang("Malay"),
        #'HIN' => Lang("Hindi")
    );


#-------------------------------------------------------------------------------------------
# Get DB Connector from setting
#-------------------------------------------------------------------------------------------
$DBC_Data = array(
    "HOST" => $Sys->GetConfig("DB_HOST"),
    "PORT" => $Sys->GetConfig("DB_PORT"),
    "USER" => $Sys->GetConfig("DB_USER"),
    "PASS" => $Sys->GetConfig("DB_PASS"),
    "NAME" => $Sys->GetConfig("DB_NAME"),
    "CHAR" => $Sys->GetConfig("DB_CHAR"),
    "DEFAULT" => $Sys->GetConfig("DB_DEFAULT"),
    );

#-------------------------------------------------------------------------------------------
# Session Intializing Variable
#-------------------------------------------------------------------------------------------
if (array_key_exists("UID", $_SESSION)) {
    $Sys->UID = (int)$_SESSION['UID'];
    $Sys->FBID = (int)$__SysUser[$Sys->UID]['FBID'];
    $Sys->Admin = (boolean)$__SysUser[$Sys->UID]['ADMIN'];
    $Sys->FName = $__SysUser[$Sys->UID]['FNAME'];
    $Sys->Lang = $__SysUser[$Sys->UID]['LANG'];
}

#-------------------------------------------------------------------------------------------
# Retive data from the super global variable/Encript Data to private variable
#-------------------------------------------------------------------------------------------
extract($_ISESSION, EXTR_OVERWRITE);
extract($_REQUEST, EXTR_OVERWRITE);
if (!empty($_REQUEST["Token"]))
    extract($Sys->DecryptVar($_REQUEST["Token"]), EXTR_OVERWRITE);

#-------------------------------------------------------------------------------------------
# Connect to database server following class module and information
#-------------------------------------------------------------------------------------------
if ($Sys->GetConfig("DB_USE") === true) {
    if ((!empty($DBC_Data) || !empty($DBC_Init)) && (!isset($Prevent_DB) || $Prevent_DB
        === false)) {
        if ($DBC_Data['DEFAULT'] === true && !empty($DBC_Data['HOST']) && !empty($DBC_Data['USER']) &&
            !empty($DBC_Data['NAME'])) {
            if ($Sys->SQLConnect($DBC_Data['HOST'], $DBC_Data['PORT'], $DBC_Data['USER'], $DBC_Data['PASS'])
                === false) {
                Sys_Log("DB Error: connect {$DBC_Data['Server']}. " . $Sys->DB_Error());
                $Sys_Status = $Sys->Warning(Lang("Unable to connect database server."));
            } elseif ($Sys->SelectDB($DBC_Data['NAME']) === false) {
                Sys_Log("DB Error: use {$DBC_Data['NAME']}. " . $Sys->DB_Error());
                $Status = $Sys->Warning(Lang("Unable to use database schema."));
            } elseif ($Sys->Query("SET NAMES '{$DBC_Data['CHAR']}'") === false) {
                Sys_Log("DB Error: SET NAMES {$DBC_Data['CHAR']}. " . $Sys->DB_Error());
                $Status = $Sys->Warning(Lang("Unable set database client chareterset."));
            }
        } else {
            if ($Sys->SQLConnect($DBC_Init['HOST'], $DBC_Init['PORT'], $DBC_Init['USER'], $DBC_Init['PASS'])
                === false) {
                Sys_Log("DB Error: connect {$DBC_Init['HOST']}. " . $Sys->DB_Error());
                $Sys_Status = $Sys->Warning(Lang("Unable to connect database server."));
            } elseif (!$Sys->SelectDB($DBC_Init['NAME'])) {
                Sys_Log("DB Error: use {$DBC_Init['NAME']}. " . $Sys->DB_Error());
                $Sys_Status = $Sys->Warning(Lang("Unable to use database schema."));
            } elseif (!$Sys->Query("SET NAMES '{$DBC_Init['CHAR']}'")) {
                Sys_Log("DB Error: SET NAMES {$DBC_Init['CHAR']}. " . $Sys->DB_Error());
                $Sys_Status = $Sys->Warning(Lang("Unable set database client chareterset."));
            }
        }
    } elseif (isset($Prevent_DB) && $Prevent_DB === true) {
        Sys_Log("System: No Database Connector.");
        $Sys_Status = $Sys->Warning(Lang("No Database Connector."));
    }
}

#-------------------------------------------------------------------------------------------
# Intializing Class Variable
#-------------------------------------------------------------------------------------------
$Sys->Language = (!empty($_SESSION['FName']) ? $_SESSION['FName'] : $DLC_Infor['Language']);
$Sys->Web_Compress = $Sys->GetConfig("WEB_COMPRESS");

#-------------------------------------------------------------------------------------------
#Proccess System Commond Request Login/Logout Session Register/Unregister
#-------------------------------------------------------------------------------------------
switch ($Sys_Command) {

        #Program Apply Register session value
    case "Register_Session":
        if (!empty($RegName) && !empty($RegData))
            $_SESSION[$RegName] = $RegData;
        break;

        #Program Apply Delete session value
    case "Delete_Session":
        if (!empty($RegName))
            unset($_SESSION[$RegName]);
        break;
}

#Load the System and Application Page List and information.
@include_once ($Sys_Page['REAL']['FileList']);

#Lock if security code failed.
$WCS_CPFile = strtolower(basename($_SERVER['SCRIPT_NAME']));

if (empty($Sys->UID)) {
    foreach ($SysPage as $WSC_File) {
        if ($WCS_CPFile == strtolower(basename(SysConvVar($WSC_File['File'])))) {
            if ($WSC_File['Permit'] != "Direct" && (!isset($WSC_File['Lock']) || $WSC_File['Lock'] !=
                'N')) {
                $Sys->WebPut(SysConvVar($Sys->Paragraph($Sys->Box($Sys->Warning(Lang("Sorry, please login first before can use."),
                    1), Lang("No login")))));
                $Sys->Ajax_Js_Work((($Frame_Loader === "User") ? $Sys->Js_Ajax_Object("{SPG_MENU}", null,
                    array("Target" => "{JS_MAINMENU}")) . $Sys->Js_Ajax_Object("{SPG_MENU}", array("SType" =>
                        "Sub_Menu"), array("Target" => "{JS_SUBMENU}")) . $Sys->Js_Ajax_Object("{SPG_FRAME}",
                    array("SType" => "User_Login", "Status" => $Status), array("Target" => "{JS_PANEL_USER}")) .
                    $Sys->Js_Ajax_Object("{SPG_MAIN}") : $Sys->Js_Ajax_Object("{SPG_MENU}", array("SType" =>
                        "Sub_Menu", "Code" => "ENDUSER"), array("Target" => "{JS_MAINMENU}")) . (array_key_exists
                    ("MEMBERS", $AppList) && $Sys->GetConfig("SHOW_MEMBER_LOGIN") ? $Sys->
                    Js_Ajax_Object("{SPG_FRAME}", array("SType" => "Member_Login", "Status" => $Status),
                    array("Target" => "{JS_PANEL_USER}")) : "") . $Sys->Js_Ajax_Object("{SPG_MENU}",
                    array("SType" => "Sub_Menu"), array("Target" => "{JS_SUBMENU}")) . $Sys->
                    Js_Ajax_Object("{SPG_ARTICLES}", array("SType" => "Show_Content", "Code" => $Sys->
                        GetConfig("HOME_PAGE", "ENDUSER")))));
                $Sys->WebOut(true);
                exit;
            } else
                break;
        }
    }
} else {
    if (empty($Sys->Admin)) {
        foreach ($SysPage as $WSC_File) {
            if ($WCS_CPFile == strtolower(basename(SysConvVar($WSC_File['File'])))) {
                if ($WSC_File['Permit'] == "Admin" && (!isset($WSC_File['Lock']) || $WSC_File['Lock'] !=
                    'N')) {
                    die("<p>" . $Sys->Box($Sys->Warning(Lang("Sorry, you are not administrator, unable to use this page."),
                        1), Lang("No Permission")) . "</p>");
                } else {
                    break;
                }
            }
        }
    }
}

#CODE PROTECTION AREA
if (!empty($WCS_Protect) && $l != md5(date("Y-m-d H:i")))
    die($Sys->Box($Sys->Warning($Sys->Strong(Lang("Protection of intellectual property rights is everyone's responsibility."))),
        Lang("Security Protection"), 30, 'center', '500px', '#FFFFFF'));

?>