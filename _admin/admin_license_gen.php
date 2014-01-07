<?php

define('DIR_LEVEL', 1);
include_once ('../_init.php');

#-------------------------------------------------------
#Product License Information
#-------------------------------------------------------
$GLicense = array();
$GLicense['Product'] = 'Online Web Custom System';
$GLicense['Registered-To'] = 'Seven-Eleven';
$GLicense['RDate'] = '2012-06-05 17:30:00';
$GLicense['Expired'] = 'x-limit';
$GLicense['User'] = 'x-limit';

#-------------------------------------------------------
#Company Information
#-------------------------------------------------------
$GCompany = array();
$GCompany['Title'] = 'Maxis CS Tab';
$GCompany['Code'] = '-';
$GCompany['Address'] = 'Address';
$GCompany['Tel'] = 'Tel.';
$GCompany['Fax'] = 'Fax.';

#-------------------------------------------------------
#Database Information
#-------------------------------------------------------
$GDBInfor = array();
/*
$GDBInfor['Server'] = 'hotlinkstudented.cyihprj8zpyb.ap-southeast-1.rds.amazonaws.com';
$GDBInfor['Port'] = 3306;
$GDBInfor['User'] = 'dbbot';
$GDBInfor['Password'] = 'future2011!';
$GDBInfor['Database'] = 'sevenelevenvacancy';
$GDBInfor['Chareter'] = 'UTF8';
#*/
#/*
$GDBInfor['Default'] = false;
#*/

#-------------------------------------------------------
#License of Application List
#-------------------------------------------------------
$AppList = array();
#$AppList['ENDUSER'] = 'End User Publish Application';
#$AppList['MEMBERS'] = 'Member System';
#$AppList['PROFILE'] = 'Profile Application';
#$AppList['ACCOUNT'] = 'Account Application';
#$AppList['BILLING'] = 'Billing Application';

#-------------------------------------------------------
#Calcurate Serial Number
#-------------------------------------------------------
$GLicense['SN'] = strtoupper(md5(strrev($GLicense['Product'] . "|" . $GLicense['Registered-To'] .
    "|" . $GCompany['Title'] . "|" . $GCompany['Code'] . "|" . $GLicense['RDate'] .
    $GLicense['Expired'])));
$GLicense['SN'] = substr($GLicense['SN'], 0, 5) . "-" . substr($GLicense['SN'],
    7, 5) . "-" . substr($GLicense['SN'], 17, 5) . "-" . substr($GLicense['SN'], 27,
    5);

#-------------------------------------------------------
#Compare all information
#-------------------------------------------------------
$FLicense = array();
$FLicense['License'] = $GLicense;
$FLicense['Company'] = $GCompany;
$FLicense['Database'] = $GDBInfor;
$FLicense['AppList'] = $AppList;

#-------------------------------------------------------
#Ouput License File to download
#-------------------------------------------------------

#Store License to buffer
$Buffer = $Sys->Encrypt(serialize($FLicense), $License['Master']);

#Begin writing headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");

#Use the switch-generated Content-Type
header("Content-Type: text/plan");

#Force the download
header("Content-Disposition: attachment; filename=license.lc;");
header("Content-Transfer-Encoding: utf-8");
header("Content-Length: " . strlen($Buffer));
echo $Buffer; //*/


?>