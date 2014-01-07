<?php

#Create by LYK on 2012-12-12 @ 10:12AM
$page = strtoupper($page);

#Initializing App Variable Variable
$_APPINFOR = array();

#Facebook Intial Value.
$_APPINFOR['_FB_APP_ID'] = false;
$_APPINFOR['_FB_APP_SECRET'] = false;
$_APPINFOR['_FB_SHARE_LINK'] = false;
$_APPINFOR['_FB_ISHARE_LINK'] = false;
$_APPINFOR['_GA_TRACK_CODE'] = false;
$_APPINFOR['_FB_PERMIT'] = '';
$_APPINFOR['_FB_SHARE_CAPTION'] = false;
$_APPINFOR['_FB_SHARE_NAME'] = false;
$_APPINFOR['_FB_SHARE_MSG'] = false;

#System resquest page.
$_APPINFOR['_REQUEST_URL'] = "{$Sys_Path['CURRENT']['PATH']}request.php";

function _APPINFOR($Index, $Internal)
{
    global $_APPINFOR;
    if (array_key_exists($Index, $_APPINFOR)) {
        switch (gettype($_APPINFOR[$Index])) {

            case 'array':
            case 'object':
                return json_encode($_APPINFOR[$Index]);
                break;

            case 'bolean':
                return ($_APPINFOR[$Index]) ? 'true' : 'false';
                break;

            case 'float':
            case 'double':
            case 'integer':
                return $_APPINFOR[$Index];
                break;

            default:
                if ($Internal === true)
                    return $_APPINFOR[$Index];
                else
                    return "'{$_APPINFOR[$Index]}'";
                break;
        }
    } else
        return 'false';
}
#########################################################
#Defind Initializing Variable
#########################################################
switch ($_SERVER['HTTP_HOST']) {
    default:
        $_APPINFOR['_FB_APP_ID'] = '';
        $_APPINFOR['_FB_APP_SECRET'] = '';
    break;
}

#########################################################
# Defind Facebook nessery variable.
#########################################################

#FB Plantform declare
$_APPINFOR['_FB_PERMIT'] =
    "email"; #,user_birthday,user_hometown,user_location,read_stream,publish_stream,publish_actions
$_APPINFOR['_FB_SHARE_CAPTION'] = '';
$_APPINFOR['_FB_SHARE_NAME'] = '';
$_APPINFOR['_FB_SHARE_MSG'] = "";

#Capture Facebook Infor Signal
if (!empty($signed_request)) {
    list($ESig, $Payload) = explode('.', $signed_request, 2);
    $FBSginal = json_decode(base64_decode(strtr($Payload, '-_', '+/')), true);
}
?>