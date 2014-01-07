//Relative Path
_$.Data.Set('_HOST', '<?php echo SysConvVar("{HOST_NAME}"); ?>');
_$.Data.Set('_ROOT', '<?php echo SysConvVar("{SPTH_PATH}"); ?>');
_$.Data.Set('_IMG', '<?php echo SysConvVar("{SPTH_IMAGE}"); ?>');

//API Request Url
_$.Data.Set('_REQUEST_URL', <?php echo _APPINFOR('_REQUEST_URL'); ?>);

//Declare special variable
_$.Data.Set('_GA_TRACK_CODE', <?php echo _APPINFOR('_GA_TRACK_CODE'); ?>);

_$.Data.Set('_FB_APP_ID', <?php echo _APPINFOR('_FB_APP_ID'); ?>);
_$.Data.Set('_FB_APP_INIT_PERMIT', <?php echo _APPINFOR('_FB_PERMIT'); ?>);

//FB Share Infor
_$.Data.Set('_FB_SHARE_CAPTION', <?php echo _APPINFOR('_FB_SHARE_CAPTION'); ?>);
_$.Data.Set('_FB_SHARE_NAME', <?php echo _APPINFOR('_FB_SHARE_NAME'); ?>);
_$.Data.Set('_FB_SHARE_LINK', <?php echo _APPINFOR('_FB_SHARE_LINK'); ?>);
_$.Data.Set('_FB_SHARE_MSG', <?php echo _APPINFOR('_FB_SHARE_MSG'); ?>);
