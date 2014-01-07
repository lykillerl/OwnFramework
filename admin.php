
<?php
include_once ("_init.php");

$_PTitle = $Sys->GetConfig('TITLE_NAME');
$_PTitle = (empty($_PTitle) ? $DLC_Infor['Title'] : $_PTitle);
$Sys->WebPut("<!DOCTYPE html>
<html>
    <head>
        <meta name=\"author\" content=\"{LC_AUTHOR}\" />
        <meta name=\"generator\" content=\"notepad\" />
        <meta name=\"copyright\" content=\"{LC_COPYRIGHT}\" />
        <meta name=\"robots\" content=\"all\" />
        <meta name=\"distribution\" content=\"Malaysia\" />
        <meta http-equiv=\"pragma\" content=\"no-cache\" />
        <meta http-equiv=\"cache-control\" content=\"no-cache, must-revalidate\" />
        <meta http-equiv=\"content-type\" content=\"application/x-www-form-urlencoded; charset=UTF-8\" />
        <meta http-equiv=\"windows-Target\" content=\"_top\" />
        <meta name=\"viewport\" content=\"width=device-width, minimum-scale=1\" />
        <link rel=\"shortcut icon\" href=\"{SPTH_IMGSYS}favicon.ico\" />
        <link type=\"text/css\" rel=\"stylesheet\" href=\"{SPG_STYLE}?ICSS=css_dashboard.css\" media=\"screen\" />
        <title>{$_PTitle}</title>
    </head>
    <body class=\"dashboard\" style=\"padding: 0px; margin: 0px;  border:none;\">
        <div class=\"header\">
            <div class=\"navicon\" href=\"Dashboard:Menu\">&nbsp;</div>
            <div class=\"title\">{$_PTitle}</div><div class=\"pinfor\"></div>
            <div style=\"clear:both;\"></div>
        </div>
        <div id=\"container\" class=\"container\"></div>
        <script type=\"text/javascript\" src=\"{SPG_JSCRIPT}?IJS=_js_sizzle.js,_js_main.js,js_dashboard.js,js_thirdparty.js\"></script>
        <script>
            _FB.Ready(function(){
                _$('div#sec_facebook').Style({display:'block'});
            });

            _$.Ready(function(){
                Dashboard.Init();
            });
        </script>
    </body>
</html>");
$Sys->WebOut(true);

?>