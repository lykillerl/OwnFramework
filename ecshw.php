<?php

#Create by LYK on 2013-04-01 @ 11:00PM
include_once ("_init.php");

$page = strtoupper($page);
$Sys->WebPut("<!doctype html>
<html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf8\" />
        <link type=\"text/css\" rel=\"stylesheet\" href=\"{SPG_STYLE}?ICSS=_css_main.css\" media=\"screen\" />
    </head>
    <body style=\"background:#888;\"></body>
</html>
<script type=\"text/javascript\" id=\"\" src=\"{SPG_JSCRIPT}?IJS=_js_main.js,_js_ecweb.js\"></script>
<script>_ECW.View('{$page}');</script>");
$Sys->WebOut();
?>