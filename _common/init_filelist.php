<?php

#Declare Global Child System File Variable
$Syslist = array("ADMIN" => array(
        'Title' => Lang("Admin"),
        'Permit' => "Permit",
        'Visible' => true,
        'Sub' => array(

            'ADMIN.CONFIG' => array(
                'FCode' => 'SPG_CONFIG',
                'ExtCmd' => null,
                'Type' => 'Ajax_Request',
                'Permit' => 'Admin',
                'Title' => Lang("System Configuration"),
                'Explain' => Lang("Setup system config"),
                'Visible' => true,
                ),

            'ADMIN.PHPINFOR' => array(
                'FCode' => 'SPG_PHPINFOR',
                'ExtCmd' => null,
                'Type' => 'Ajax_Request',
                'Permit' => 'Permit',
                'Title' => Lang("Server Information"),
                'Explain' => Lang("View the server information and default setting."),
                'Visible' => true,
                ),

            'ADMIN.ECSYS' => array(
                'FCode' => 'SPG_ECWEB',
                'ExtCmd' => null,
                'Type' => 'Ajax_Request',
                'Permit' => 'Permit',
                'Title' => Lang("Element Composing System"),
                'Explain' => Lang("View/Edit Element Composing System Page."),
                'Visible' => true,
                ),

            'ADMIN.USER' => array(
                'FCode' => 'SPG_USER',
                'ExtCmd' => null,
                'Type' => 'Ajax_Request',
                'Permit' => 'Admin',
                'Title' => Lang("User Control"),
                'Explain' => Lang("Register, update, diregister user and view user list."),
                'Visible' => true,
                ),

            'ADMIN.PERMIT' => array(
                'FCode' => 'SPG_PERMIT',
                'ExtCmd' => null,
                'Type' => 'Ajax_Request',
                'Permit' => 'Admin',
                'Title' => Lang("User Permission"),
                'Explain' => Lang("Setup user permission right."),
                'Visible' => true,
                ))));
?>