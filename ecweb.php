<?php

#Create by LYK on 2013-04-01 @ 11:00PM
include_once ("_init.php");

$Sys->WebPut("<!doctype html>
<html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf8\" />
        <link type=\"text/css\" rel=\"stylesheet\" href=\"{SPG_STYLE}?ICSS=css_dashboard.css\" media=\"screen\" />
    </head>
    <body style=\"margin:0px; border:none; padding:0px; background:#888; overflow:auto; border-spacing: 0px;\"></body>
</html>
<script type=\"text/javascript\" id=\"\" src=\"{SPG_JSCRIPT}?IJS=_js_main.js,js_dashboard.js,_js_ecweb.js\"></script>
<script>_ECW.Edit();</script>");
$Sys->WebOut();
?>