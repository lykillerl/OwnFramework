<?php

#Create by LYK on 2009-7-10 @ 2:18, modify by LYK 2012-12-12 11:40PM

#-------------------------------------------------------
#Framework Information
#-------------------------------------------------------
$Frame_Work = array();
$Frame_Work['Version']['Major'] = 3;
$Frame_Work['Version']['Minor'] = 0;
$Frame_Work['Version']['Revision'] = 0;
$Frame_Work['Name'] = 'LS_FRAME_WORK';

#-------------------------------------------------------
# Product Information for license validation Declare
#-------------------------------------------------------
$DLC_Infor = array();
$DLC_Infor['Product'] = "Online Web Custom System";
$DLC_Infor['Version'] = "1.0";
$DLC_Infor['Author'] = "LS Computer Technology - Low Yong Kang";
$DLC_Infor['Title'] = "Online Web Custom System";
$DLC_Infor['Copyright'] = "Copyrights &copy; 2012 by LS Computer Technology All rights reserved.";
$DLC_Infor['Language'] = "ENG";

#-------------------------------------------------------------------------------------------
# License Restrict Information Setup
#-------------------------------------------------------------------------------------------
$License['Master'] = md5('LSPRODUCT').md5('LYK848510SCC');
$License['System'] = true; #Allow passthru by system
$License['Trial'] = false; #Allow use but trial version (expired 30 day)

#-------------------------------------------------------------------------------------------
# Session Control
#-------------------------------------------------------------------------------------------
$Sys_Session['Name'] = 'sys_session'; #session cookie name
$Sys_Session['ID'] = strtoupper(md5("{$_SERVER['HTTP_USER_AGENT']}{$_SERVER['REMOTE_ADDR']}")); #session cookie identifine.
$Sys_Session['Expired'] = 180; #session expired time. (count in min.)
$Sys_Session['Cache'] = false; #Cache Limit control, handle who able cache the session id.

#-------------------------------------------------------
# Database Prefix Keyword
#-------------------------------------------------------
$Sys_TP = "sys"; #System

#-------------------------------------------------------
# SQL Table Naming Declare
#-------------------------------------------------------

#System Database table name
$Sys_TB = array();

#------------------------------------------------------
#Email Server Information
#-------------------------------------------------------

#System Main Internal Email Server Connection Information Declare
$Default_Email = array();
$Default_Email['Host'] = 'localhost';
$Default_Email['Port'] = 25;
$Default_Email['User'] = 'root';
$Default_Email['Password'] = 'password';
$Default_Email['From'] = $_SERVER["SERVER_ADMIN"];

#-------------------------------------------------------
#Display Formating String
#-------------------------------------------------------
$Format = array();
$Format['Currency'] = "RM%0.2f";
$Format['Date'] = "Y-m-d";
$Format['Time'] = "H:i:s";
$Format['DateTime'] = "Y-m-d H:i:s";

#-------------------------------------------------------------------------------------------
# Intializing Date & Default Time Zone
#-------------------------------------------------------------------------------------------
date_default_timezone_set("Asia/Singapore");
$RYrs = (!empty($RYrs) ? (int)$RYrs : (int)date("Y"));
$RMth = (!empty($RMth) ? (int)$RMth : (int)date("m"));

#-------------------------------------------------------
#Setup Common Infor Variable
#-------------------------------------------------------
$Sys_Infor = array();
$Sys_Infor['Common'] = array();
$Sys_Infor['Common']['Date'] = date("Y-m-d");
$Sys_Infor['Common']['Time'] = date("H:i:s");
$Sys_Infor['Common']['Now'] = date("Y-m-d H:i:s");
$Sys_Infor['Common']['HASHKEY'] = "Hash_{$Sys_Session['ID']}";

#-------------------------------------------------------
#Setup DB Infor Variable
#-------------------------------------------------------
$Sys_Infor['DB'] = array();
$Sys_Infor['DB']['Date'] = "CURRENT_TIME";
$Sys_Infor['DB']['Time'] = "CURRENT_DATE";
$Sys_Infor['DB']['Now'] = "now()";
$Sys_Infor['DB']['FNC'] = "\\dbfnc\\";

#-------------------------------------------------------
#Setup Js Infor Variable
#-------------------------------------------------------
$Sys_Infor['JS'] = array();
$Sys_Infor['JS']['AjaxSp'] = "\1\1\r\n\r\n";

#-------------------------------------------------------
#Inrialzing Declare system variable
#-------------------------------------------------------
$__Sys_Setting = array(); #System Setting from DB
$Sys_Command = ''; #Main system action command variable.
$Sub_Command = ''; #Sub system action command variable.
$Sys_Status = ''; #System channel status command variable.
$Status = ''; #Application status command variable.
$SType = ''; #Application form display command variable.
$RType = ''; #Application report display command variable.
$Main = ''; #Home page control variable.

#-------------------------------------------------------
#Hosting Information Declare
#-------------------------------------------------------
$Sys_Host = array();
$Sys_Host['Name'] = $_SERVER['HTTP_HOST'];
$Sys_Host['Client'] = $_SERVER['REMOTE_ADDR'];
$Sys_Host['Server'] = $_SERVER['SERVER_ADDR'];

#-------------------------------------------------------
#Getting URL Root Directory, Real Directory, and Current Path Name
#-------------------------------------------------------
$Sys_Path = array();
$Sys_Path['HTTP'] = $_SERVER['HTTP_HOST'];
$Sys_Path['BASE'] = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
$Sys_Path['BASE'] .= ((substr($Sys_Path['BASE'], -1) === "/") ? "" : "/");

#Web Server Real Path
$Sys_Path['REAL'] = array();
$Sys_Path['REAL']['PATH'] = ROOT;
$Sys_Path['REAL']['COMMON'] = "{$Sys_Path['REAL']['PATH']}_common/";
$Sys_Path['REAL']['CLASS'] = "{$Sys_Path['REAL']['PATH']}_class/";
$Sys_Path['REAL']['ADMIN'] = "{$Sys_Path['REAL']['PATH']}_admin/";
$Sys_Path['REAL']['Data'] = "{$Sys_Path['REAL']['PATH']}_data/";
$Sys_Path['REAL']['APP'] = "{$Sys_Path['REAL']['PATH']}_app/";
$Sys_Path['REAL']['LANG'] = "{$Sys_Path['REAL']['PATH']}_lang/";
$Sys_Path['REAL']['PLUGIN'] = "{$Sys_Path['REAL']['PATH']}_plugin/";
$Sys_Path['REAL']['PDF_Font'] = "{$Sys_Path['REAL']['PLUGIN']}pdffont/";
$Sys_Path['REAL']['LOG'] = "{$Sys_Path['REAL']['PATH']}log/";
$Sys_Path['REAL']['IMAGE'] = "{$Sys_Path['REAL']['PATH']}images/";
$Sys_Path['REAL']['IMGSYS'] = "{$Sys_Path['REAL']['IMAGE']}_system/";
$Sys_Path['REAL']['FONT'] = "{$Sys_Path['REAL']['PATH']}_font/";
$Sys_Path['REAL']['TEMPLATE'] = "{$Sys_Path['REAL']['PATH']}_template/";
$Sys_Path['REAL']['JS'] = "{$Sys_Path['REAL']['PATH']}js/";
$Sys_Path['REAL']['CSS'] = "{$Sys_Path['REAL']['PATH']}css/";

#Website Current Path
$Sys_Path['CURRENT'] = array();
$Sys_Path['CURRENT']['PATH'] = "/".str_replace($Sys_Path['BASE'], "", ROOT);
$Sys_Path['CURRENT']['COMMON'] = "{$Sys_Path['CURRENT']['PATH']}_common/";
$Sys_Path['CURRENT']['CLASS'] = "{$Sys_Path['CURRENT']['PATH']}_class/";
$Sys_Path['CURRENT']['ADMIN'] = "{$Sys_Path['CURRENT']['PATH']}_admin/";
$Sys_Path['CURRENT']['APP'] = "{$Sys_Path['CURRENT']['PATH']}_app/";
$Sys_Path['CURRENT']['IMAGE'] = "{$Sys_Path['CURRENT']['PATH']}images/";
$Sys_Path['CURRENT']['IMGSYS'] = "{$Sys_Path['CURRENT']['IMAGE']}_system/";
$Sys_Path['CURRENT']['FONT'] = "{$Sys_Path['REAL']['PATH']}_font/";
$Sys_Path['CURRENT']['TEMPLATE'] = "{$Sys_Path['CURRENT']['TEMPLATE']}_template/";
$Sys_Path['CURRENT']['LOG'] = "{$Sys_Path['CURRENT']['PATH']}log/";
$Sys_Path['CURRENT']['JS'] = "{$Sys_Path['CURRENT']['PATH']}js/";
$Sys_Path['CURRENT']['CSS'] = "{$Sys_Path['CURRENT']['PATH']}css/";

#-------------------------------------------------------
# Special System Page Information Declare
#-------------------------------------------------------
$Sys_Page = array();
$Sys_Page['URL'] = "{$_SERVER['SCRIPT_NAME']}";
$Sys_Page['SELF'] = "{$_SERVER['PHP_SELF']}";
$Sys_Page['SESSION'] = "{$_SERVER['PHP_SELF']}?SType={$SType}";
$Sys_Page['QUERY'] = $_SERVER["QUERY_STRING"];
$Sys_Page['NOW'] = $_SERVER["REQUEST_URI"];

#-------------------------------------------------------
# Real System Class File Name Declare
#-------------------------------------------------------
$Class_Page = array();

#Class for include purpose only.

#Database Class
$Class_Page['MYSQL'] = "{$Sys_Path['REAL']['CLASS']}class_mysql.php"; #alone mysql driver
$Class_Page['Mongo'] = "{$Sys_Path['REAL']['CLASS']}class_mongo.php"; #alone mongo driver
$Class_Page['SQLSRV'] = "{$Sys_Path['REAL']['CLASS']}class_sqlsrv.php"; #alone mssql driver new
$Class_Page['MSSQL'] = "{$Sys_Path['REAL']['CLASS']}class_mssql.php"; #alone mssql driver old

$Class_Page['GD'] = "{$Sys_Path['REAL']['CLASS']}class_gd.php"; #alone
$Class_Page['Chiper'] = "{$Sys_Path['REAL']['CLASS']}class_chiper.php"; #alone AES Encript Chiper
$Class_Page['ObjCls'] = "{$Sys_Path['REAL']['CLASS']}class_objcls.php"; #dependency clsdata, Dom element/JS/JSon
$Class_Page['ClsData'] = "{$Sys_Path['REAL']['CLASS']}class_data.php"; #dependency database driver
$Class_Page['XML'] = "{$Sys_Path['REAL']['CLASS']}class_xml.php"; #alone xml parser driver
$Class_Page['CURL'] = "{$Sys_Path['REAL']['CLASS']}class_curl.php"; #alone socket version.
$Class_Page['Email'] = "{$Sys_Path['REAL']['CLASS']}class_email.php"; #alone using imap to send mail
$Class_Page['IBrowser'] = "{$Sys_Path['REAL']['CLASS']}class_ibrowser.php"; #alone Browser Detection
$Class_Page['PDF'] = "{$Sys_Path['REAL']['CLASS']}class_pdf.php"; #alone Generate PDF Class

#-------------------------------------------------------
# Framework Real Data File Declare
#-------------------------------------------------------
$Data_File = array();

$Data_File['SysConfig'] = "{$Sys_Path['REAL']['Data']}data_sysconfig.dat"; #Admin Setting
$Data_File['SysUser'] = "{$Sys_Path['REAL']['Data']}data_sysuser.dat"; #Admin User
$Data_File['SysULog'] = "{$Sys_Path['REAL']['Data']}data_sysulog.dat"; #Admin User Log
$Data_File['SysUPermit'] = "{$Sys_Path['REAL']['Data']}data_sysupermit.dat"; #Admin User Permit
$Data_File['TagLinkTrace'] = "{$Sys_Path['REAL']['Data']}data_taglinktrace.dat"; #Element Composition Data File
$Data_File['ECWeb'] = "{$Sys_Path['REAL']['Data']}data_ecweb.dat"; #Element Composition Data File

#-------------------------------------------------------
# Framework Real System File Name Declare
#-------------------------------------------------------
$Sys_Page['REAL'] = array();

#Init System / Common File
$Sys_Page['REAL']['Common'] = "{$Sys_Path['REAL']['COMMON']}init_common.php";
$Sys_Page['REAL']['Function'] = "{$Sys_Path['REAL']['COMMON']}common_function.php";
$Sys_Page['REAL']['SysVar'] = "{$Sys_Path['REAL']['COMMON']}common_sysvar.php";
$Sys_Page['REAL']['AppVar'] = "{$Sys_Path['REAL']['COMMON']}common_appvar.php";
$Sys_Page['REAL']['FileList'] = "{$Sys_Path['REAL']['COMMON']}init_filelist.php";

#Framework Admin File
$Sys_Page['REAL']['User'] = "{$Sys_Path['REAL']['ADMIN']}admin_user.php";
$Sys_Page['REAL']['Config'] = "{$Sys_Path['REAL']['ADMIN']}admin_config.php";
$Sys_Page['REAL']['License'] = "{$Sys_Path['REAL']['ADMIN']}admin_license.php";
$Sys_Page['REAL']['PhpInfor'] = "{$Sys_Path['REAL']['ADMIN']}admin_phpinfor.php";
$Sys_Page['REAL']['UAcc'] = "{$Sys_Path['REAL']['ADMIN']}admin_uacc.php";
$Sys_Page['REAL']['Permit'] = "{$Sys_Path['REAL']['ADMIN']}admin_permit.php";
$Sys_Page['REAL']['Setup'] = "{$Sys_Path['REAL']['ADMIN']}admin_setup.php";
$Sys_Page['REAL']['ECWeb'] = "{$Sys_Path['REAL']['ADMIN']}admin_ecweb.php";
$Sys_Page['REAL']['Img'] = "{$Sys_Path['REAL']['IMGSYS']}_img.php";
$Sys_Page['REAL']['FECWeb'] = "{$Sys_Path['REAL']['COMMON']}common_ecweb.php";

#Framework CSS File
$Sys_Page['REAL']['Style'] = "{$Sys_Path['REAL']['CSS']}css_style.php";

#Frame Js File
$Sys_Page['REAL']['JScript'] = "{$Sys_Path['REAL']['JS']}js_jscript.php";

#Framework Log File
$Sys_Page['REAL']['Log'] = "{$Sys_Path['REAL']['LOG']}log.txt";
$Sys_Page['REAL']['Error'] = "{$Sys_Path['REAL']['LOG']}error.txt";
$Sys_Page['REAL']['Exception'] = "{$Sys_Path['REAL']['LOG']}exception.txt";

#Framework License File
$Sys_Page['REAL']['LFile'] = "{$Sys_Path['REAL']['COMMON']}license.lc";

#-------------------------------------------------------
# Framework Real System Language File Naming Declare
#-------------------------------------------------------
$Sys_Page['Lang'] = array();
$Sys_Page['Lang']['Master'] = "{$Sys_Path['REAL']['LANG']}sys_lang_master.php";
$Sys_Page['Lang']['ENG'] = "{$Sys_Path['REAL']['LANG']}sys_lang_eng.lang";
$Sys_Page['Lang']['CHT'] = "{$Sys_Path['REAL']['LANG']}sys_lang_cht.lang";
$Sys_Page['Lang']['CHS'] = "{$Sys_Path['REAL']['LANG']}sys_lang_chs.lang";
$Sys_Page['Lang']['MLY'] = "{$Sys_Path['REAL']['LANG']}sys_lang_mly.lang";
$Sys_Page['Lang']['HIN'] = "{$Sys_Path['REAL']['LANG']}sys_lang_hin.lang";

#-------------------------------------------------------
# Framework Relative System File Name Declare
#-------------------------------------------------------
$Sys_Page['CURRENT'] = array();

#Framework Admin File
$Sys_Page['CURRENT']['User'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_user.php";
$Sys_Page['CURRENT']['Config'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_config.php";
$Sys_Page['CURRENT']['PhpInfor'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_phpinfor.php";
$Sys_Page['CURRENT']['License'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_license.php";
$Sys_Page['CURRENT']['UAcc'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_uacc.php";
$Sys_Page['CURRENT']['Permit'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_permit.php";
$Sys_Page['CURRENT']['Setup'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_setup.php";
$Sys_Page['CURRENT']['ECWeb'] = "{$Sys_Path['CURRENT']['ADMIN']}admin_ecweb.php";
$Sys_Page['CURRENT']['Img'] = "{$Sys_Path['CURRENT']['IMGSYS']}_img.php";
$Sys_Page['CURRENT']['FECWeb'] = "{$Sys_Path['CURRENT']['COMMON']}common_ecweb.php";

#Frame CSS File
$Sys_Page['CURRENT']['Style'] = "{$Sys_Path['CURRENT']['CSS']}css_style.php";

#Frame Js File
$Sys_Page['CURRENT']['JScript'] = "{$Sys_Path['CURRENT']['JS']}js_jscript.php";

?>