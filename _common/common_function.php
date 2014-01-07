<?php

#  Create by LYK on 2009-7-15 @ 15:18
#-------------------------------------------------------------------------------------------
#Function Delare
#-------------------------------------------------------------------------------------------

#Replace System Keyword to output value
function SysConvVar($StrValue = '')
{
    #Query Global value
    global $_REQUEST, $_SESSION, $Format, $Sys_Host, $Sys_Infor, $DLC_Infor, $Sys_TB, $App_TB, $Sys_Path,
        $Class_Page, $Sys_Page, $App_Path, $App_Page, $Data_File;

    #Setup if Target is not array
    if (!is_array($_REQUEST)) $_REQUEST = array();
    if (!is_array($_SESSION)) $_SESSION = array();

    if (!is_array($Sys_Infor)) $Sys_Infor = array();
    if (!is_array($Sys_Infor['Common'])) $Sys_Infor['Common'] = array();
    if (!is_array($Sys_Infor['DB'])) $Sys_Infor['DB'] = array();
    if (!is_array($Sys_Infor['JS'])) $Sys_Infor['JS'] = array();

    if (!is_array($Sys_Host)) $Sys_Host = array();
    if (!is_array($DLC_Infor)) $DLC_Infor = array();

    if (!is_array($Sys_TB)) $Sys_TB = array();
    if (!is_array($App_TB)) $App_TB = array();

    if (!is_array($Sys_Path)) $Sys_Path = array();
    if (!is_array($Sys_Path['REAL'])) $Sys_Path['REAL'] = array();
    if (!is_array($Sys_Path['CURRENT'])) $Sys_Path['CURRENT'] = array();

    if (!is_array($App_Path)) $App_Path = array();
    if (!is_array($App_Path['REAL'])) $App_Path['REAL'] = array();
    if (!is_array($App_Path['CURRENT'])) $App_Path['CURRENT'] = array();

    if (!is_array($Class_Page)) $Class_Page = array();
    if (!is_array($Sys_Page)) $Sys_Page = array();
    if (!is_array($Sys_Page['REAL'])) $Sys_Page['REAL'] = array();
    if (!is_array($Sys_Page['CURRENT'])) $Sys_Page['CURRENT'] = array();
    if (!is_array($App_Page)) $App_Page = array();
    if (!is_array($App_Page['REAL'])) $App_Page['REAL'] = array();
    if (!is_array($App_Page['CURRENT'])) $App_Page['CURRENT'] = array();

    if (!is_array($Data_File)) $Data_File = array();

    #Setup Array to string key
    $_Data = array();

    #Database Table
    foreach ($Sys_TB as $_Key => $_Value) $_Data["STB_{$_Key}"] = $_Value;
    foreach ($App_TB as $_Key => $_Value) $_Data["ATB_{$_Key}"] = $_Value;

    foreach ($Sys_Path['CURRENT'] as $_Key => $_Value) $_Data["SPTH_{$_Key}"] = $_Value;
    foreach ($Sys_Path['REAL'] as $_Key => $_Value) $_Data["SPTHR_{$_Key}"] = $_Value;

    foreach ($App_Path['CURRENT'] as $_Key => $_Value) $_Data["APTH_{$_Key}"] = $_Value;
    foreach ($App_Path['REAL'] as $_Key => $_Value) $_Data["APTHR{$_Key}"] = $_Value;

    #Page File List.
    foreach ($Class_Page as $_Key => $_Value) $_Data["CPG_{$_Key}"] = $_Value;
    foreach ($Sys_Page['REAL'] as $_Key => $_Value) $_Data["SPGR_{$_Key}"] = $_Value;
    foreach ($Sys_Page['CURRENT'] as $_Key => $_Value) $_Data["SPG_{$_Key}"] = $_Value;
    foreach ($App_Page['REAL'] as $_Key => $_Value) $_Data["APGR_{$_Key}"] = $_Value;
    foreach ($App_Page['CURRENT'] as $_Key => $_Value) $_Data["APG_{$_Key}"] = $_Value;

    #Data File List.
    foreach ($Data_File as $_Key => $_Value) $_Data["DATA_{$_Key}"] = $_Value;

    #Envoirment Infor
    foreach ($Sys_Host as $_Key => $_Value) $_Data["HOST_{$_Key}"] = $_Value;
    foreach ($DLC_Infor as $_Key => $_Value) $_Data["LC_{$_Key}"] = $_Value;

    foreach ($Format as $_Key => $_Value) $_Data["FMT_{$_Key}"] = $_Value;

    foreach ($Sys_Infor['Common'] as $_Key => $_Value) $_Data["INFOR_{$_Key}"] = $_Value;
    foreach ($Sys_Infor['DB'] as $_Key => $_Value) $_Data["DB_{$_Key}"] = $_Value;
    foreach ($Sys_Infor['JS'] as $_Key => $_Value) $_Data["JS_{$_Key}"] = $_Value;

    #Merge array and replace value
    foreach ($_Data as $Key => $Value)
    {
        $EKeyArray[] = "{".strtolower($Key)."}";
        $EValArray[] = $Value;
    }
    return str_ireplace($EKeyArray, $EValArray, $StrValue);
}

function Inc_File($File, $Include_Once = true)
{
    if ($Include_Once === true) include_once (SysConvVar($File));
    else  include (SysConvVar($File));
}

function Array_Is_Assoc($Array)
{
    return (bool)count(array_filter(array_keys($Array), 'is_string'));
}

#return language system language, currently no function
function Lang($OriTxt, $Tran = true)
{
    if ($Tran == true)
    {
        global $Sys, $Sys_Page, $Language, $DLC_Infor;
        if (file_exists($Sys_Page['Lang']['Master']) && file_exists($Sys_Page['Lang'][$Sys->Language]) &&
            is_array($Lang_Master))
        {
            $Sys->Language = (!empty($Sys->Language) ? $Sys->Language : $DLC_Infor['Language']);
            $Value = $Language[$Sys->Language][$OriTxt];
            return (!empty($Value) ? $Value : $OriTxt);
        }
        else
        {
            return $OriTxt;
        }
    }
    else
    {
        return $OriTxt;
    }
}

function Gen_UUID()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

function uuid($Namespace = '', $Name = '', $Category = '')
{
    if (empty($Namespace)) $Namespace = Gen_UUID();
    if (empty($Name)) $Name = substr($Namespace, 20, 12);
    if (empty($Category)) $Category = substr($Namespace, 0, 8);
    $UUID = array(
        substr(md5("{{$Namespace}-{$Name}}"), 0, 8),
        substr(md5($Category), 8, 4),
        substr(md5(date("YmdHis")), 12, 4),
        sprintf('%04x', mt_rand(0, 0xffff)),
        substr(md5("{{$_SERVER['HTTP_USER_AGENT']}-{$_SERVER['REMOTE_ADDR']}-".microtime(true)."}"), 20, 12));
    return implode('-', $UUID);
}

#return after resize image size, fix with ratio
function GetFixImageHeight($Img_Width, $Img_Height, $Fix_Size)
{
    #Calurate after resize image size, maintaince ratio
    $Ratio_Height = $Img_Height / $Img_Width;
    $Ratio_Width = $Img_Width / $Img_Height;
    $ImgRatio = min($Ratio_Width, $Ratio_Width);
    return floor(abs($Fix_Size / $ImgRatio));
}

#return after resize image size, fix with ratio
function GetFixImageWidth($Img_Width, $Img_Height, $Fix_Size)
{
    #Calurate after resize image size, maintaince ratio
    $Ratio_Height = $Img_Height / $Img_Width;
    $Ratio_Width = $Img_Width / $Img_Height;
    $ImgRatio = min($Ratio_Width, $Ratio_Width);
    return floor(abs($Fix_Size * $ImgRatio));
}

#return after resize image size, fix with ratio
function GetThumbnailSize($Img_Width, $Img_Height, $Need_Width = 0, $Need_Height = 0)
{
    $Need_Width = ($Need_Width === 0) ? $Img_Width : $Need_Width;
    $Need_Height = ($Need_Height === 0) ? $Img_Height : $Need_Height;

    if (($Img_Width > $Need_Width) || ($Img_Height > $Need_Height))
    {
        while (($Need_Width < $Img_Width) || ($Need_Height < $Img_Height))
        {
            if ($Need_Width < $Img_Width)
            {
                $Img_Height = floor((($Need_Width * $Img_Height) / $Img_Width));
                $Img_Width = $Need_Width;
            }
            if ($Need_Height < $Img_Height)
            {
                $Img_Width = floor((($Need_Height * $Img_Width) / $Img_Height));
                $Img_Height = $Need_Height;
            }
        }
    }
    return array($Img_Width, $Img_Height);
}

#Function Checking Image Size, and compute
function ComputeImageSize($Image_Path, $Fix_Width = 240)
{
    global $Sys_Path;
    if (is_array($Sys_Path))
    {
        $Image_RealPath = substr($Sys_Path['BASE'], 0, -1)."{$Image_Path}";
        $Image = getimagesize($Image_RealPath);

        #Calurate after resize image size, maintaince ratio
        $ImgRatio = $Image[0] / $Image[1];
        $Img['Height'] = floor(abs($Fix_Width / $ImgRatio));
        return array(
            $Image[0],
            $Image[1],
            $Fix_Width,
            $Img['Height']);
    }
    else
    {
        return false;
    }
}

#Image SHow
function ShowImage($Image, $Real_Width, $Real_Height, $Width = null, $Height = null)
{
    global $Sys, $Page, $Sys_Path;
    if (is_object($Sys) && is_array($Sys_Path) && is_array($Page))
    {
        return $Sys->Ajax_Page_Link("<image style=\"border:none;\" src=\"{$Sys_Path['CURRENT']}{$Page['Thumbnail']}?Image=$Image".
            (!empty($Width) ? "&Width={$Width}" : "").(!empty($Height) ? "&Height={$Height}" : "")."\">", "{$Sys_Path['CURRENT']}{$Page['PicView']}?Ajax=1&Image={$Image}",
            $Real_Width, $Real_Height + 15);
    }
    else
    {
        return false;
    }
}

#Convert Currency to Word like 00.00-> Zero And Zero Cent
function Currency2Word($numval)
{
    $moneystr = "";
    $milval = (integer)($numval / 1000000);
    if ($milval > 0)
    {
        $moneystr = GetNumWords($milval)." ".Lang("Million");
    }
    $workval = $numval - ($milval * 1000000);
    $thouval = (integer)($workval / 1000);
    if ($thouval > 0)
    {
        $workword = GetNumWords($thouval);
        if ($moneystr == "") $moneystr = $workword." ".Lang("Thousand");
        else  $moneystr .= " ".$workword." ".Lang("Thousand");
    }
    $workval = $workval - ($thouval * 1000);
    $tensval = (integer)($workval);
    if ($moneystr == "")
        if ($tensval > 0) $moneystr = GetNumWords($tensval);
        else  $moneystr = "Zero";
    else
    {
        $workword = GetNumWords($tensval);
        $moneystr .= " ".$workword;
    }
    $workval = (integer)($numval);
    $workstr = sprintf("%3.2f", $numval);
    $intstr = substr($workstr, strlen - 2, 2);
    $workint = (integer)($intstr);
    if ($workint != 0)
    {
        if ($workint == 1) $moneystr .= " ".Lang("And Cent")." ";
        else  $moneystr .= " ".Lang("And Cents")." ";
        $moneystr .= GetNumWords($workint);
    }
    else  $moneystr .= " ".Lang("Only");
    return $moneystr;
}

#Convert Number to Word like 0 -> Zero
function GetNumWords($workval)
{
    $numwords = array(
        1 => Lang("One"),
        2 => Lang("Two"),
        3 => Lang("Three"),
        4 => Lang("Four"),
        5 => Lang("Five"),
        6 => Lang("Six"),
        7 => Lang("Seven"),
        8 => Lang("Eight"),
        9 => Lang("Nine"),
        10 => Lang("Ten"),
        11 => Lang("Eleven"),
        12 => Lang("Twelve"),
        13 => Lang("Thirteen"),
        14 => Lang("Fourteen"),
        15 => Lang("Fifteen"),
        16 => Lang("Sixteen"),
        17 => Lang("Seventeen"),
        18 => Lang("Eightteen"),
        19 => Lang("Nineteen"),
        20 => Lang("Twenty"),
        30 => Lang("Thirty"),
        40 => Lang("Forty"),
        50 => Lang("Fifty"),
        60 => Lang("Sixty"),
        70 => Lang("Seventy"),
        80 => Lang("Eighty"),
        90 => Lang("Ninety"));

    $retstr = "";
    $hundval = (integer)($workval / 100);
    if ($hundval > 0) $retstr = $numwords[$hundval]." ".Lang("Hundred");

    $workstr = "";
    $tensval = $workval - ($hundval * 100);
    if (($tensval < 20) && ($tensval > 0)) $workstr = $numwords[$tensval];
    else
    {
        $tempval = ((integer)($tensval / 10)) * 10;
        $workstr = $numwords[$tempval];
        $unitval = $tensval - $tempval;
        if ($unitval > 0) $workstr .= " ".$numwords[$unitval];
    }
    if ($workstr != "")
    {
        if ($retstr != "") $retstr .= " ".$workstr;
        else  $retstr = $workstr;
    }
    return $retstr;
}

#Calurate Age from birthday (format : yyyy-mm-dd)
function GetAge($BirthDay)
{
    list($Year, $Month, $Day) = explode("-", $BirthDay);
    $Year_diff = date("Y") - $Year;
    $Month_diff = date("m") - $Month;
    $Day_diff = date("d") - $Day;
    if ($Day_diff < 0 || $Month_diff < 0) $Year_diff--;
    return $Year_diff;
}

function IfEmptyH($StrValue, $Show = '-', $Method = 'H')
{
    global $Sys;
    return (!empty($StrValue) ? $StrValue : (($Method == "H") ? $Sys->Unknow($Show) : $Show));
}

if (!function_exists('mime_content_type'))
{

    function mime_content_type($filename)
    {

        $mime_types = array(

            #Web content file
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            # images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            # archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            # audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            # adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            # ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            # open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) return $mime_types[$ext];
        elseif (function_exists('finfo_open'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else  return 'application/octet-stream';
    }
}

#Save Data to File
function File_Contents($DFile, $Data = null)
{
    if (!$Data) return file_get_contents(SysConvVar($DFile));
    else  return file_put_contents(SysConvVar($DFile), $Data);
}

#Save Data to File
function Data_To_File($DFile, $Data)
{
    $Data = gzcompress(json_encode($Data), 9);
    return File_Contents($DFile, $Data);
}

function File_To_Data($DFile)
{
    return json_decode(gzuncompress(File_Contents($DFile)), true);
}

#Generate Random Code with request len
function Gencode($Len = 8)
{
    $Len = (int)$Len;
    $Len = !empty($Len) ? $Len : 8;
    $Result = '';
    for ($i = 0; $i < $Len; $i++)
    {
        switch (rand(1, 3))
        {
            case 1:
                $Result .= chr(rand(48, 57));
                break;
            case 2:
                $Result .= chr(rand(65, 90));
                break;
            case 3:
                $Result .= chr(rand(97, 122));
                break;
        }
    }
    return $Result;
}

#Generate and sent download header
function Download_Headers($File)
{
    $Now = gmdate("D, d M Y H:i:s");
    header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$Now} GMT");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename={$File}");
    header("Content-Transfer-Encoding: binary");
}

#Log Message
function Sys_Log($Output, $Clear = false)
{
    global $_SERVER, $Sys_Page;
    $FP = fopen($Sys_Page['REAL']['Log'], (($Clear == true) ? "w+" : "a+"));
    $Content = date("Y-m-d H:i:s")." {$Output}\n";
    fwrite($FP, $Content, strlen($Content));
    fclose($FP);
}

#Log Message
function Sys_Error($ErrNo, $ErrMsg, $ErrFile, $ErrLine, $Clear = true)
{
    global $_SERVER, $Sys_Page;
    if ($ErrNo !== 8)
    {
        $FP = fopen($Sys_Page['REAL']['Error'], (($Clear == true) ? "w+" : "a+"));
        $Content = "Error Time    : ".date("Y-m-d H:i:s")."\n"."Error No.     : {$ErrNo}\n".
            "Error Message : {$ErrMsg}\n"."Error File    : {$ErrFile}\n"."Error Line    : {$ErrLine}\n";
        fwrite($FP, $Content, strlen($Content));
        fclose($FP);
    }
    return true;
}

function WebTag_Trace($Tag)
{
    $_Tag_Link_Trace = File_To_Data("{DATA_TAGLINKTRACE}");
    if (!is_array($_Tag_Link_Trace)) $_Tag_Link_Trace = array();
    $_Tag_Link_Trace[] = array(
        "TAG" => $Tag,
        "IP" => $_SERVER['REMOTE_ADDR'],
        "REFRENCE" => $_SERVER['HTTP_REFERER'],
        "AGENT" => $_SERVER['HTTP_USER_AGENT'],
        "ADD_TIME" => date("Y-m-d H:i:s"));
    Data_To_File("{DATA_TAGLINKTRACE}", $_Tag_Link_Trace);
}

function WebTag_Trace_Report($Tag)
{
    global $Sys;
    $_Tag_Link_Trace = File_To_Data("{DATA_TAGLINKTRACE}");
    return $Sys->TBGrid($_Tag_Link_Trace, "Web Tag Trace Report", array(
        "Tag",
        "IP",
        "Refrence",
        "User Agent",
        "Time"), null, Lang("No, Web Trace Record"), null, true);
}

#Log Message
function Sys_Exception($Exception, $Clear = true)
{
    global $_SERVER, $Sys_Page;
    $FP = fopen($Sys_Page['REAL']['Exception'], (($Clear == true) ? "w+" : "a+"));
    $Content = "Exception Time   : ".date("Y-m-d H:i:s")."\n"."Exception class. : ".get_class($Exception).
        "\n"."Error Message    : ".$Exception->getMessage()."\n"."Error File       : ".$Exception->getFile().
        "\n"."Error Line       : ".$Exception->getLine()."\n".".\n";
    fwrite($FP, $Content, strlen($Content));
    fclose($FP);
}

?>