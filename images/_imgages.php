<?php

#Create by LYK on 2009-07-12 @ 18:00PM
define('DIR_LEVEL', 1);
include_once ("../_init.php");

//Getting Out Source Variable
$Img = array();
$Image = SysConvVar($Image);
$Width = (int)$Width;
$Height = (int)$Height;
$Type = strtolower($Type);
$Max_Width = (int)$Max_Width;
$Max_Height = (int)$Max_Height;
$Thumnail = (int)$Thumnail;
$Grayscale = strtolower($Grayscale);
if (!isset($Expire))
    $Expire = (int)$Sys->GetConfig("IMAGE_CACHE_TIME");
$Expire = (int)$Expire;

# Setup Default Thumnail Size
$Thumbnail_Width = 128;
$Thumbnail_Height = 96;

#Read and maintaine require value if image there.
if (!empty($Image)) {

    if (strpos($Image, "http") !== false) {
        $CURL = curl_init($Image);
        curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CURL, CURLOPT_TIMEOUT, (int)10);
        $Content = curl_exec($CURL);
        $Infor = @getimagesize($Image);
    } else {
        $Headers = apache_request_headers();
        $Image = str_replace(array("\\", "//"), "/", $Image);
        $Img['Real'] = str_replace("//", "/", "{$Sys_Path['BASE']}{$Image}");
        if (!empty($Expire)) {
            header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + $Expire), true);
            header("Cache-Control: max-age={$Expire}", true);
            header("Pragma: cache", true);
        } elseif (isset($Headers['If-Modified-Since']) && (strtotime($Headers['If-Modified-Since']) ==
        filemtime($Img['Real']))) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($Img['Real'])) .
                ' GMT', true, 304);
            exit;
        } else {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($Img['Real'])) .
                ' GMT', true, 200);
        }
    }
    $Content = file_get_contents($Img['Real']);
    $Infor = @getimagesize($Img['Real']);
    $SrcIM = imagecreatefromstring($Content);
    imagealphablending($SrcIM, false);

    #Calurate after resize image size, maintaince ratio
    if (!empty($Width)) {
        $Height = GetFixImageHeight(imagesx($SrcIM), imagesy($SrcIM), $Width);
    } elseif (!empty($Height)) {
        $Width = GetFixImageWidth(imagesx($SrcIM), imagesy($SrcIM), $Height);
    }

    #Setup Thumnail Size or Original Size if customize size is no define.
    if (!empty($Thumnail)) {
        if (empty($Width))
            $Width = $Thumbnail_Width;
        if (empty($Height))
            $Height = $Thumbnail_Height;
    } else {
        if (empty($Width))
            $Width = imagesx($SrcIM);
        if (empty($Height))
            $Height = imagesy($SrcIM);
    }

    #Control maxiumn width size
    if (!empty($Max_Width))
        if ((int)$Width > (int)$Max_Width) {
                $Height = GetFixImageHeight($Width, $Height, $Max_Width);
                $Width = (int)$Max_Width;
        }

    #Control maxiumn height size
    if (!empty($Max_Height))
        if ((int)$Height > (int)$Max_Height) {
                $Width = GetFixImageWidth($Width, $Height, $Max_Height);
                $Height = (int)$Max_Height;
        }

    #Create new Thumbnail
    $BufferIM = @imagecreatetruecolor($Width, $Height);
    imagealphablending($BufferIM, false);
    @imagecopyresized($BufferIM, $SrcIM, 0, 0, 0, 0, $Width, $Height, $Infor[0], $Infor[1]);

    if ($Grayscale == 'y') {
        $DstIM = @imagecreatetruecolor($Width, $Height);
        imagealphablending($DstIM, false);
        for ($i = 0; $i <= 255; $i++)
            $Palette[$i] = imagecolorallocate($DstIM, $i, $i, $i);
        function Grayscale($R, $G, $B)
        {
            return 0.199 * $R + 0.587 * $G + 0.114 * $B;
        }
        for ($X = 0; $X < $Width; $X++) {
            for ($Y = 0; $Y < $Height; $Y++) {
                $RGB = imagecolorat($BufferIM, $X, $Y);
                $R = ($RGB >> 16) & 0xFF;
                $G = ($RGB >> 8) & 0xFF;
                $B = $RGB & 0xFF;
                imagesetpixel($DstIM, $X, $Y, $Palette[Grayscale($R, $G, $B)]);
            }
        }
        @imagedestroy($BufferIM);
    } else {
        $DstIM = $BufferIM;
    }

    switch ($Type) {

        case "gif":
            header("content-type: image/gif");
            imagesavealpha($DstIM, true);
            @imagegif($DstIM);
            break;

        case "png":
            header("content-type: image/png");
            imagesavealpha($DstIM, true);
            @imagepng($DstIM, null, 0);
            break;

        case "bmp":
            header("content-type: image/bitmap");
            @imagewbmp($DstIM, null);
            break;

        case "xbm":
            header("content-type: image/xbm");
            @imagexbm($DstIM, null);
            break;

        case "jpg":
            header("content-type: image/jpeg");
            imageInterlace($DstIM, true);
            @imagejpeg($DstIM, null, 100);
            break;

        default:
            header("content-type: image/png");
            imageInterlace($DstIM, true);
            imagesavealpha($DstIM, true);
            imagepng($DstIM, null, 0);
    }
    @imagedestroy($DstIM);
}

?>