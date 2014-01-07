<?php

#-----------------------------------------------------------------------#
# PHP CLASS
# CLASS AUTHOR : LYK
# DESCRIPTION : Draw some image graphic effects.
# DATABASE API : GD Effect Class
# DEVOLOPMENT DATE : 2013-09-20
# Refference : http://www.tuxradar.com/practicalphp/11/2/(1-30)
#-----------------------------------------------------------------------#

class GDCls
{

    private $Img, $Width, $Height;

    public function __construct()
    {
        register_shutdown_function(array(&$this, '__destruct'));
    }

    ##########################################################################################
    # Section: Intial Load and Intial Save and Check Resource
    ##########################################################################################

    private function Initload($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        imagealphablending($Img, true);
        imagesavealpha($Img, true);
        $this->Width = imagesx($Img);
        $this->Height = imagesy($Img);
    }

    private function Initsave($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        imagesavealpha($Img, true);
    }

    private function Isgdres($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!is_resource($Img)) return false;
        if (get_resource_type($Img) === 'gd') return true;
        return true;
    }

    ##########################################################################################
    # Section: Special Proccess
    ##########################################################################################

    private function SmoothArcDrawSegment($Img, $cx, $cy, $a, $b, $aaAngleX, $aaAngleY, $fillColor, $start,
        $stop, $seg)
    {
        $color = array_values(imagecolorsforindex($Img, $fillColor));
        $xStart = abs($a * cos($start));
        $yStart = abs($b * sin($start));
        $xStop = abs($a * cos($stop));
        $yStop = abs($b * sin($stop));
        $dxStart = 0;
        $dyStart = 0;
        $dxStop = 0;
        $dyStop = 0;
        if ($xStart != 0) $dyStart = $yStart / $xStart;
        if ($xStop != 0) $dyStop = $yStop / $xStop;
        if ($yStart != 0) $dxStart = $xStart / $yStart;
        if ($yStop != 0) $dxStop = $xStop / $yStop;
        if (abs($xStart) >= abs($yStart)) $aaStartX = true;
        else  $aaStartX = false;
        if ($xStop >= $yStop) $aaStopX = true;
        else  $aaStopX = false;
        for ($x = 0; $x < $a; $x += 1)
        {
            $_y1 = $dyStop * $x;
            $_y2 = $dyStart * $x;
            if ($xStart > $xStop)
            {
                $error1 = $_y1 - (int)($_y1);
                $error2 = 1 - $_y2 + (int)$_y2;
                $_y1 = $_y1 - $error1;
                $_y2 = $_y2 + $error2;
            }
            else
            {
                $error1 = 1 - $_y1 + (int)$_y1;
                $error2 = $_y2 - (int)($_y2);
                $_y1 = $_y1 + $error1;
                $_y2 = $_y2 - $error2;
            }
            if ($seg == 0 || $seg == 2)
            {
                $i = $seg;
                if (!($start > $i * M_PI / 2 && $x > $xStart))
                {
                    if ($i == 0)
                    {
                        $xp = + 1;
                        $yp = -1;
                        $xa = + 1;
                        $ya = 0;
                    }
                    else
                    {
                        $xp = -1;
                        $yp = + 1;
                        $xa = 0;
                        $ya = + 1;
                    }
                    if ($stop < ($i + 1) * (M_PI / 2) && $x <= $xStop)
                    {
                        $diffColor1 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error1);
                        $y1 = $_y1;
                        if ($aaStopX) imageSetPixel($Img, $cx + $xp * ($x) + $xa, $cy + $yp * ($y1 + 1) + $ya, $diffColor1);
                    }
                    else
                    {
                        $y = $b * sqrt(1 - ($x * $x) / ($a * $a));
                        $error = $y - (int)($y);
                        $y = (int)($y);
                        $diffColor = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) * $error);
                        $y1 = $y;
                        if ($x < $aaAngleX) imageSetPixel($Img, $cx + $xp * $x + $xa, $cy + $yp * ($y1 + 1) + $ya, $diffColor);
                    }
                    if ($start > $i * M_PI / 2 && $x <= $xStart)
                    {
                        $diffColor2 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error2);
                        $y2 = $_y2;
                        if ($aaStartX) imageSetPixel($Img, $cx + $xp * $x + $xa, $cy + $yp * ($y2 - 1) + $ya, $diffColor2);
                    }
                    else  $y2 = 0;
                    if ($y2 <= $y1) imageLine($Img, $cx + $xp * $x + $xa, $cy + $yp * $y1 + $ya, $cx + $xp * $x + $xa, $cy +
                            $yp * $y2 + $ya, $fillColor);
                }
            }

            if ($seg == 1 || $seg == 3)
            {
                $i = $seg;
                if (!($stop < ($i + 1) * M_PI / 2 && $x > $xStop))
                {
                    if ($i == 1)
                    {
                        $xp = -1;
                        $yp = -1;
                        $xa = 0;
                        $ya = 0;
                    }
                    else
                    {
                        $xp = + 1;
                        $yp = + 1;
                        $xa = 1;
                        $ya = 1;
                    }
                    if ($start > $i * M_PI / 2 && $x < $xStart)
                    {
                        $diffColor2 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error2);
                        $y1 = $_y2;
                        if ($aaStartX) imageSetPixel($Img, $cx + $xp * $x + $xa, $cy + $yp * ($y1 + 1) + $ya, $diffColor2);

                    }
                    else
                    {
                        $y = $b * sqrt(1 - ($x * $x) / ($a * $a));
                        $error = $y - (int)($y);
                        $y = (int)$y;
                        $diffColor = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) * $error);
                        $y1 = $y;
                        if ($x < $aaAngleX) imageSetPixel($Img, $cx + $xp * $x + $xa, $cy + $yp * ($y1 + 1) + $ya, $diffColor);
                    }
                    if ($stop < ($i + 1) * M_PI / 2 && $x <= $xStop)
                    {
                        $diffColor1 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error1);
                        $y2 = $_y1;
                        if ($aaStopX) imageSetPixel($Img, $cx + $xp * $x + $xa, $cy + $yp * ($y2 - 1) + $ya, $diffColor1);
                    }
                    else  $y2 = 0;
                    if ($y2 <= $y1) imageLine($Img, $cx + $xp * $x + $xa, $cy + $yp * $y1 + $ya, $cx + $xp * $x + $xa, $cy +
                            $yp * $y2 + $ya, $fillColor);
                }
            }
        }
        for ($y = 0; $y < $b; $y += 1)
        {
            $_x1 = $dxStop * $y;
            $_x2 = $dxStart * $y;
            if ($yStart > $yStop)
            {
                $error1 = $_x1 - (int)($_x1);
                $error2 = 1 - $_x2 + (int)$_x2;
                $_x1 = $_x1 - $error1;
                $_x2 = $_x2 + $error2;
            }
            else
            {
                $error1 = 1 - $_x1 + (int)$_x1;
                $error2 = $_x2 - (int)($_x2);
                $_x1 = $_x1 + $error1;
                $_x2 = $_x2 - $error2;
            }
            if ($seg == 0 || $seg == 2)
            {
                $i = $seg;
                if (!($start > $i * M_PI / 2 && $y > $yStop))
                {
                    if ($i == 0)
                    {
                        $xp = + 1;
                        $yp = -1;
                        $xa = 1;
                        $ya = 0;
                    }
                    else
                    {
                        $xp = -1;
                        $yp = + 1;
                        $xa = 0;
                        $ya = 1;
                    }
                    if ($stop < ($i + 1) * (M_PI / 2) && $y <= $yStop)
                    {
                        $diffColor1 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error1);
                        $x1 = $_x1;
                        if (!$aaStopX) imageSetPixel($Img, $cx + $xp * ($x1 - 1) + $xa, $cy + $yp * ($y) + $ya, $diffColor1);
                    }
                    if ($start > $i * M_PI / 2 && $y < $yStart)
                    {
                        $diffColor2 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error2);
                        $x2 = $_x2;
                        if (!$aaStartX) imageSetPixel($Img, $cx + $xp * ($x2 + 1) + $xa, $cy + $yp * ($y) + $ya, $diffColor2);
                    }
                    else
                    {
                        $x = $a * sqrt(1 - ($y * $y) / ($b * $b));
                        $error = $x - (int)($x);
                        $x = (int)($x);
                        $diffColor = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) * $error);
                        $x1 = $x;
                        if ($y < $aaAngleY && $y <= $yStop) imageSetPixel($Img, $cx + $xp * ($x1 + 1) + $xa, $cy + $yp * $y +
                                $ya, $diffColor);
                    }
                }
            }
            if ($seg == 1 || $seg == 3)
            {
                $i = $seg;
                if (!($stop < ($i + 1) * M_PI / 2 && $y > $yStart))
                {
                    if ($i == 1)
                    {
                        $xp = -1;
                        $yp = -1;
                        $xa = 0;
                        $ya = 0;
                    }
                    else
                    {
                        $xp = + 1;
                        $yp = + 1;
                        $xa = 1;
                        $ya = 1;
                    }
                    if ($start > $i * M_PI / 2 && $y < $yStart)
                    {
                        $diffColor2 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error2);
                        $x1 = $_x2;
                        if (!$aaStartX) imageSetPixel($Img, $cx + $xp * ($x1 - 1) + $xa, $cy + $yp * $y + $ya, $diffColor2);
                    }
                    if ($stop < ($i + 1) * M_PI / 2 && $y <= $yStop)
                    {
                        $diffColor1 = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) *
                            $error1);
                        $x2 = $_x1;
                        if (!$aaStopX) imageSetPixel($Img, $cx + $xp * ($x2 + 1) + $xa, $cy + $yp * $y + $ya, $diffColor1);
                    }
                    else
                    {
                        $x = $a * sqrt(1 - ($y * $y) / ($b * $b));
                        $error = $x - (int)($x);
                        $x = (int)($x);
                        $diffColor = imageColorExactAlpha($Img, $color[0], $color[1], $color[2], 127 - (127 - $color[3]) * $error);
                        $x1 = $x;
                        if ($y < $aaAngleY && $y < $yStart) imageSetPixel($Img, $cx + $xp * ($x1 + 1) + $xa, $cy + $yp * $y +
                                $ya, $diffColor);
                    }
                }
            }
        }
    }

    private function TTFBTxt($Size, $Angle, $X, $Y, $Color, $Font, $Text, $Blur = 0, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Angle = (double)$Angle;
        $X = (int)$X;
        $Y = (int)$Y;
        $Color = (int)$Color;
        $Blur = (int)$Blur;
        if ($Blur > 0)
        {
            $TxtImg = imagecreatetruecolor(imagesx($Img), imagesy($Img));
            imagefill($TxtImg, 0, 0, imagecolorallocate($TxtImg, 0x00, 0x00, 0x00));
            imagettftext($TxtImg, $Size, $Angle, $X, $Y, imagecolorallocate($TxtImg, 0xFF, 0xFF, 0xFF), $Font, $Text);
            for ($i = 1; $i <= $Blur; $i++) imagefilter($TxtImg, IMG_FILTER_GAUSSIAN_BLUR);
            for ($XOff = 0; $XOff < imagesx($TxtImg); $XOff++)
                for ($YOff = 0; $YOff < imagesy($TxtImg); $YOff++)
                {
                    $Visible = (imagecolorat($TxtImg, $XOff, $YOff) & 0xFF) / 255;
                    if ($Visible > 0) imagesetpixel($Img, $XOff, $YOff, imagecolorallocatealpha($Img, ($Color >> 16) &
                            0xFF, ($Color >> 8) & 0xFF, $Color & 0xFF, (1 - $Visible) * 127));
                }
            imagedestroy($TxtImg);
        }
        else  return imagettftext($Img, $Size, $Angle, $X, $Y, $Color, $Font, $Text);
    }

    ##########################################################################################
    # Section: Load or create image
    ##########################################################################################

    public function GDCreate($Width, $Height)
    {
        $Width = (int)$Width;
        $Height = (int)$Height;
        if (!empty($Width) && !empty($Height))
        {
            $this->Img = imagecreatetruecolor($Width, $Height);
            imagefill($this->Img, 0, 0, imagecolorallocatealpha($this->Img, 255, 255, 255, 127));
            $this->Initload();
            return $this->Img;
        }
        else  return false;
    }

    public function GDLoad($File = '')
    {
        $Img = false;
        if ($this->Isgdres($File))
        {
            $this->Img = $File;
            $this->Initload();
            return $this->Img;
        }
        elseif (file_exists($File))
        {
            if (function_exists('exif_imagetype')) $ImgType = exif_imagetype($File);
            else
            {
                $ImgInfo = getImageSize($File);
                $ImgType = $ImgInfo[2];
            }
            switch ($ImgType)
            {
                case IMAGETYPE_GIF:
                    if (imagetypes() & IMG_GIF) $Img = @imagecreatefromgif($File);
                    break;
                case IMAGETYPE_JPEG:
                    if (imagetypes() & IMG_JPG) $Img = @imagecreatefromjpeg($File);
                    break;
                case IMAGETYPE_PNG:
                    if (imagetypes() & IMG_PNG) $Img = @imagecreatefrompng($File);
                    break;
                case IMAGETYPE_XBM:
                    if (imagetypes() & IMAGETYPE_XBM) $Img = @imagecreatefromxbm($File);
                    break;
                case IMAGETYPE_WBMP:
                    if (imagetypes() & IMG_WBMP) $Img = @imagecreatefromwbmp($File);
                    break;
                default: #Try load the to image
                    $Img = @imagecreatefromstring(file_get_contents($File));
            }
            if (!$this->Isgdres($Img)) return false;
            else
            {
                $this->Img = $Img;
                $this->Initload();
                return $this->Img;
            }
        }
        else  return false;
    }

    public function GDInput($Img = null)
    {
        if ($this->Isgdres($Img))
        {
            $this->Img = $Img;
            $this->Initload();
            return true;
        }
        elseif (is_string($Img))
        {
            $Img = @imagecreatefromstring($Img);
            if ($Img !== false)
            {
                $this->Img = $Img;
                return true;
            }
            else  return false;
        }
        else  return false;
    }

    ##########################################################################################
    # Section: Graphic Tool
    ##########################################################################################

    public function GDColor($Red = 0, $Green = 0, $Blue = 0, $Alpha = 0, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Red = (int)$Red;
        $Green = (int)$Green;
        $Blue = (int)$Blue;
        $Alpha = (int)$Alpha;
        return @imagecolorallocatealpha($Img, $Red, $Green, $Blue, $Alpha);
    }

    public function GDFill($Color = 0, $X = 0, $Y = 0, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $X = (int)$X;
        $Y = (int)$Y;
        $Color = (int)$Color;
        return @imagefill($Img, $X, $Y, $Color);
    }

    public function GDPixel($X = 0, $Y = 0, $Assoc = false, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $X = (int)$X;
        $Y = (int)$Y;
        $Assoc = (boolean)$Assoc;
        $Color = imagecolorat($Img, $X, $Y);
        if ($Assoc !== true) return $Color;
        else  return imagecolorsforindex($Img, $Color);
    }

    public function GDWidth($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        return imagesx($Img);
    }

    public function GDHeight($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        return imagesy($Img);
    }

    ##########################################################################################
    # Section: Graphic Common Job
    ##########################################################################################

    public function GDCopy($File, $Rect = null, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!is_array($Rect)) $Rect = array();
        if (!array_key_exists('L', $Rect)) $Rect['L'] = (int)0;
        if (!array_key_exists('T', $Rect)) $Rect['T'] = (int)0;
        $ImgSrc = $this->GDLoad($File);
        if (!array_key_exists('W', $Rect) || empty($Rect['W'])) $Rect['W'] = (int)imagesx($ImgSrc);
        if (!array_key_exists('H', $Rect) || empty($Rect['H'])) $Rect['H'] = (int)imagesy($ImgSrc);
        $this->Img = $Img;
        $this->Initload($Img);
        $Rect['L'] = (int)$Rect['L'];
        $Rect['T'] = (int)$Rect['T'];
        $Rect['W'] = (int)$Rect['W'];
        $Rect['H'] = (int)$Rect['H'];

        if ($ImgSrc !== false)
        {
            imagecopyresampled($Img, $ImgSrc, $Rect['L'], $Rect['T'], 0, 0, $Rect['W'], $Rect['H'], imagesx($ImgSrc),
                imagesy($ImgSrc));
            imagealphablending($Img, true);
            imagedestroy($ImgSrc);
            return true;
        }
        else  return false;
    }

    public function GDMerge($File, $Rect = null, $Percent = 100, $Crop = '', $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Percent = (int)$Percent;
        if (!is_array($Rect)) $Rect = array();
        if (!array_key_exists('X', $Rect)) $Rect['X'] = (int)0;
        if (!array_key_exists('Y', $Rect)) $Rect['Y'] = (int)0;
        if (!array_key_exists('W', $Rect) || empty($Rect['W'])) $Rect['W'] = (int)imagesx($Img);
        if (!array_key_exists('H', $Rect) || empty($Rect['H'])) $Rect['H'] = (int)imagesy($Img);
        $ImgSrc = $this->GDLoad($File);
        $ImgSrc = $this->GDSizeto($Rect['W'], $Rect['H'], $Crop, $ImgSrc);
        $this->Img = $Img;
        $this->Initload($Img);
        $Rect['X'] = (int)$Rect['X'];
        $Rect['Y'] = (int)$Rect['Y'];
        $Rect['W'] = (int)$Rect['W'];
        $Rect['H'] = (int)$Rect['H'];

        if ($ImgSrc !== false)
        {
            imagecopymerge($Img, $ImgSrc, $Rect['X'], $Rect['Y'], 0, 0, $Rect['W'], $Rect['H'], $Percent);
            imagedestroy($ImgSrc);
            return true;
        }
        else  return false;
    }

    public function GDRotate($Angle = 0, $Background = 0, $IgnoreTrans = false, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Angle = (double)$Angle;
        $Background = (int)$Background;
        $IgnoreTrans = (boolean)$IgnoreTrans;
        if (!empty($Angle))
        {
            $this->Initsave($Img);
            $Result = imagerotate($this->Img, $Angle, $Background, $IgnoreTrans);
            imagedestroy($Img);
            $this->Initload($Result);
            $this->Img = $Result;
            $Img = $Result;
            return $this->Img = $Result;
        }
    }

    public function GDSizeto($Width = 0, $Height = 0, $Crop = false, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Width = (int)$Width;
        $Height = (int)$Height;
        if (empty($Width) && empty($Height)) return $Img;
        $this->Initload($Img);
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        $SARatio = $ImageX / $ImageY;
        $RARatio = $Width / $Height;
        if (($Crop !== false && $RARatio < $SARatio) || ($Crop === false && $RARatio > $SARatio))
        {
            $RWidth = (int)($Height * $SARatio);
            $RHeight = $Height;
        }
        else
        {
            $RWidth = $Width;
            $RHeight = (int)($Width / $SARatio);
        }
        $New = $this->GDCreate($Width, $Height);
        if ($Crop === false) $Crop = 'lt';
        elseif ($Crop === true) $Crop = 'cc';

        switch (strtolower($Crop))
        {
            case 'lt':
                $X = 0;
                $Y = 0;
                break;

            case 'ct':
                $X = ($Width - $RWidth) / 2;
                $Y = $Height - $RHeight;
                break;

            case 'rt':
                $X = $Width - $RWidth;
                $Y = 0;
                break;

            case 'lb':
                $X = 0;
                $Y = $Height - $RHeight;
                break;

            case 'cb':
                $X = ($Width - $RWidth) / 2;
                $Y = $Height - $RHeight;
                break;

            case 'rb':
                $X = $Width - $RWidth;
                $Y = $Height - $RHeight;
                break;

            default:
                $X = ($Width - $RWidth) / 2;
                $Y = ($Height - $RHeight) / 2;
        }
        imagecopyresampled($New, $Img, $X, $Y, 0, 0, $RWidth, $RHeight, $ImageX, $ImageY);
        imagedestroy($Img);
        return $this->Img;
    }

    public function GDCanvasSize($Width = 0, $Height = 0, $Crop = false, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Width = (int)$Width;
        $Height = (int)$Height;
        $Crop = (boolean)$Crop;
        if (empty($Width) && empty($Height)) return $Img;
        $this->Initload($Img);
        $RWidth = $this->Width;
        $RHeight = $this->Height;
        $SARatio = $ImageX / $ImageY;
        $RARatio = $Width / $Height;
        $New = $this->GDCreate($Width, $Height);
        switch ($Crop)
        {
            case false:
            case 'lt':
                $X = 0;
                $Y = 0;
                break;

            case 'ct':
                $X = ($Width - $RWidth) / 2;
                $Y = $Height - $RHeight;
                break;

            case 'rt':
                $X = $Width - $RWidth;
                $Y = 0;
                break;

            case 'lb':
                $X = 0;
                $Y = $Height - $RHeight;
                break;

            case 'cb':
                $X = ($Width - $RWidth) / 2;
                $Y = $Height - $RHeight;
                break;

            case 'rb':
                $X = $Width - $RWidth;
                $Y = $Height - $RHeight;
                break;

            default:
                $X = ($Width - $RWidth) / 2;
                $Y = ($Height - $RHeight) / 2;
        }
        imagecopyresampled($New, $Img, $X, $Y, 0, 0, $RWidth, $RHeight, $ImageX, $ImageY);
        imagedestroy($Img);
        return $this->Img;
    }

    public function GDResize($Width = 0, $Height = 0, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Width = (int)$Width;
        $Height = (int)$Height;
        if (empty($Width) && empty($Height)) return $Img;
        $this->Initload($Img);
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        if (!empty($Width) && empty($Height)) $Height = $ImageY / ($ImageX / $Width);
        if (empty($Width) && !empty($Height)) $Width = $ImageX / ($ImageY / $Height);
        $New = $this->GDCreate($Width, $Height);
        imagecopyresampled($New, $Img, 0, 0, 0, 0, $Width, $Height, $ImageX, $ImageY);
        imagedestroy($Img);
        return $this->Img;
    }

    public function GDFlip($Vertical = false, $Horizontal = false, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (empty($Vertical) && empty($Horizontal)) return;
        $StartX = 0;
        $StartY = 0;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        if ($Horizontal === true)
        {
            $StartX = $ImageX - 1;
            $ImageX *= -1;
        }
        if ($Vertical === true)
        {
            $StartY = $ImageY - 1;
            $ImageY *= -1;
        }
        $Result = imagecreatetruecolor($this->Width, $this->Height);
        imagecopyresampled($Result, $Img, 0, 0, $StartX, $StartY, $this->Width, $this->Height, $ImageX, $ImageY);
        imagedestroy($Img);
        $this->Img = $Result;
        $this->Initload($Result);
        return $Result;
    }

    public function GDMaskAlpha($MaskImage, $Crop = true, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;

        $Mask = $this->GDLoad($MaskImage);
        if (!$this->Isgdres($Mask)) return false;

        $ImageX = $this->GDWidth($Mask);
        $ImageY = $this->GDHeight($Mask);
        $Resize = $this->GDSizeto($ImageX, $ImageY, $Crop, $Img);
        $Result = $this->GDCreate($ImageX, $ImageY);

        for ($X = 0; $X < $ImageX; $X++)
            for ($Y = 0; $Y < $ImageY; $Y++)
            {
                $OColor = imagecolorsforindex($Resize, imagecolorat($Resize, $X, $Y));
                $MColor = imagecolorsforindex($Mask, imagecolorat($Mask, $X, $Y));
                if ($MColor['alpha'] > 0) imagesetpixel($Result, $X, $Y, imagecolorallocatealpha($Result, $OColor['red'],
                        $OColor['green'], $OColor['blue'], 127 - $MColor['alpha']));
            }
        imagedestroy($Resize);
        imagedestroy($Mask);
        return $Result;
    }

    public function GDMaskColor($MaskImage, $Color, $MatchAlpha = true, $Crop = true, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;

        $Mask = $this->GDLoad($MaskImage);
        if (!$this->Isgdres($Mask)) return false;

        $ImageX = $this->Width;
        $ImageY = $this->Height;
        $Resize = $this->GDSizeto($ImageX, $ImageY, $Crop, $Img);
        $Result = $this->GDCreate($ImageX, $ImageY);

        if (!is_array($Color)) $SColor = array(
                'red' => (int)$Color,
                'green' => (int)$Color,
                'blue' => (int)$Color,
                'alpha' => (int)$Color);
        else  $SColor = array(
                'red' => array_key_exists('red', $Color) ? (int)$Color['red'] : 0,
                'green' => array_key_exists('green', $Color) ? (int)$Color['green'] : 0,
                'blue' => array_key_exists('blue', $Color) ? (int)$Color['blue'] : 0,
                'alpha' => array_key_exists('alpha', $Color) ? (int)$Color['alpha'] : 0);

        for ($X = 0; $X < $ImageX; $X++)
            for ($Y = 0; $Y < $ImageY; $Y++)
            {
                $OColor = imagecolorsforindex($Resize, imagecolorat($Resize, $X, $Y));
                $MColor = imagecolorsforindex($Mask, imagecolorat($Mask, $X, $Y));
                if (($MatchAlpha === true && $SColor === $MColor) || ($MatchAlpha === false && $SColor['red'] === $MColor['red'] &&
                    $SColor['green'] === $MColor['green'] && $SColor['blue'] === $MColor['blue'])) imagesetpixel($Result,
                        $X, $Y, imagecolorallocatealpha($Result, $OColor['red'], $OColor['green'], $OColor['blue'], $MColor['alpha']));
            }
        imagedestroy($Resize);
        imagedestroy($Mask);
        return $Result;
    }

    private function ConerFill($ImgConer, $Img, $Radius, $StartX, $StartY, $BG)
    {
        $Trans = imagecolorallocatealpha($Img, 255, 255, 255, 127);
        for ($Y = $StartY; $Y < $StartY + $Radius; $Y++)
            for ($X = $StartX; $X < $StartX + $Radius; $X++)
            {
                $Color = imagecolorsforindex($Img, imagecolorat($ImgConer, $X - $StartX, $Y - $StartY));
                if ($Color['red'] > 230 && $Color['green'] > 0 && $Color['blue'] > 0) imagesetpixel($Img, $X, $Y,
                        imagecolorallocatealpha($Img, 255, 255, 255, 127));
            }
    }

    public function GDRoundCorner($Radius = 10, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Radius = (int)$Radius;
        if (empty($Radius)) return false;

        $ImageX = $this->Width;
        $ImageY = $this->Height;
        imagealphablending($Img, false);

        $ImgConer = imagecreatetruecolor($Radius, $Radius);
        $BG = imagecolorallocatealpha($ImgConer, 255, 255, 255, 0);
        imagefill($ImgConer, 0, 0, $BG);

        $this->GDSmoothArc($Radius, $Radius, $Radius * 2, $Radius * 2, 0, 0, 360, $ImgConer);

        $this->ConerFill($ImgConer, $Img, $Radius, 0, 0, $BG);

        $ImgConer = imagerotate($ImgConer, 90, 0);
        $this->ConerFill($ImgConer, $Img, $Radius, 0, $ImageY - $Radius, $BG);

        $ImgConer = imagerotate($ImgConer, 90, 0);
        $this->ConerFill($ImgConer, $Img, $Radius, $ImageX - $Radius, $ImageY - $Radius, $BG);

        $ImgConer = imagerotate($ImgConer, 90, 0);
        $this->ConerFill($ImgConer, $Img, $Radius, $ImageX - $Radius, 0, $BG);

        imagealphablending($Img, true);
        return $Img;
    }

    public function GDGammaCorrect($Gamma = 100, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Gamma = (double)$Gamma;
        if (empty($Gamma)) return;
        imagegammacorrect($Img, 100, $Gamma);
        return $Img;
    }

    public function GDSmoothArc($CX, $CY, $Width, $Height, $Color = 0, $Start = 0, $Stop = 360, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Start = deg2rad($Start);
        $Stop = deg2rad($Stop);
        $Color = (int)$Color;
        while ($Start < 0) $Start += 2 * M_PI;
        while ($Stop < 0) $Stop += 2 * M_PI;
        while ($Start > 2 * M_PI) $Start -= 2 * M_PI;
        while ($Stop > 2 * M_PI) $Stop -= 2 * M_PI;
        if ($Start > $Stop)
        {
            $this->GDSmoothArc($CX, $CY, $Width, $Height, $Color, rad2deg($Start), 2 * M_PI, $Img);
            $this->GDSmoothArc($CX, $CY, $Width, $Height, $Color, 0, rad2deg($Stop), $Img);
            return;
        }
        $a = 1.0 * round($Width / 2);
        $b = 1.0 * round($Height / 2);
        $CX = 1.0 * round($CX);
        $CY = 1.0 * round($CY);
        $aaAngle = atan(($b * $b) / ($a * $a) * tan(0.25 * M_PI));
        $aaAngleX = $a * cos($aaAngle);
        $aaAngleY = $b * sin($aaAngle);
        $a -= 0.5;
        $b -= 0.5;
        for ($i = 0; $i < 4; $i++)
            if ($Start < ($i + 1) * M_PI / 2)
                if ($Start > $i * M_PI / 2)
                {
                    if ($Stop > ($i + 1) * M_PI / 2) $this->SmoothArcDrawSegment($Img, $CX, $CY, $a, $b, $aaAngleX, $aaAngleY,
                            $Color, $Start, ($i + 1) * M_PI / 2, $i);
                    else
                    {
                        $this->SmoothArcDrawSegment($Img, $CX, $CY, $a, $b, $aaAngleX, $aaAngleY, $Color, $Start, $Stop, $i);
                        break;
                    }
                }
                else
                {
                    if ($Stop > ($i + 1) * M_PI / 2) $this->SmoothArcDrawSegment($Img, $CX, $CY, $a, $b, $aaAngleX, $aaAngleY,
                            $Color, $i * M_PI / 2, ($i + 1) * M_PI / 2, $i);
                    else
                    {
                        $this->SmoothArcDrawSegment($Img, $CX, $CY, $a, $b, $aaAngleX, $aaAngleY, $Color, $i * M_PI / 2, $Stop,
                            $i);
                        break;
                    }
                }
    }

    ##########################################################################################
    # Section: Special FX Effect
    ##########################################################################################

    public function GDFXBlur($Level = 0, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Level = (int)$Level;
        if (!function_exists('imagefilter'))
            for ($i = 0; $i < $Level; $i++) imagefilter($Img, IMG_FILTER_SELECTIVE_BLUR);
            else  return false;
        return $Img;
    }

    public function GDFXContrast($Level = 0, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_CONTRAST, $Level)) return false;
        return $Img;
    }

    public function GDFXBriness($Level = 0, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_BRIGHTNESS, $Level)) return false;
        return $Img;
    }

    public function GDFXSmooth($Level = 0, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_SMOOTH, $Level)) return false;
        return $Img;
    }

    public function GDFXSketchy(&$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_MEAN_REMOVAL)) return false;
        return $Img;
    }

    public function GDFXEmboss(&$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_EMBOSS)) return false;
        return $Img;
    }

    public function GDFXEdge(&$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_EDGEDETECT)) return false;
        return $Img;
    }

    public function GDFXInvert(&$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_NEGATE)) return false;
        return $Img;
    }

    public function GDFXInterlace($Color = 0, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Color = (int)$Color;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        for ($Y = 1; $Y < $ImageY; $Y += 2) imageline($Img, 0, $Y, $ImageX, $Y, $Color);
        return $Img;
    }

    public function GDFXGreyscale(&$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_GRAYSCALE))
        {
            $ImageX = $this->Width;
            $ImageY = $this->Height;
            for ($Y = 0; $Y < $ImageY; ++$Y)
                for ($X = 0; $X < $ImageX; ++$X)
                {
                    $Color = imagecolorsforindex($Img, imagecolorat($Img, $X, $Y));
                    $Grey = (int)(($Color['red'] + $Color['green'] + $Color['blue']) / 3);
                    imagesetpixel($Img, $X, $Y, imagecolorallocatealpha($Img, $Grey, $Grey, $Grey, $Color['alpha']));
                }
        }
        return $Img;
    }

    public function GDFXColorFilter($Red = false, $Green = false, $Blue = false, $Compare = 127, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        $Type = ($Red === true ? 'Y' : 'N').($Green === true ? 'Y' : 'N').($Blue === true ? 'Y' : 'N');
        for ($Y = 0; $Y < $ImageY; ++$Y)
            for ($X = 0; $X < $ImageX; ++$X)
            {
                $Color = imagecolorsforindex($Img, imagecolorat($Img, $X, $Y));
                $Greyscale = true;
                switch ($Type)
                {

                    case 'YNN':
                        if ($Color['red'] - $Color['green'] > $Compare && $Color['red'] - $Color['blue'] > $Compare) $Greyscale = false;
                        break;

                    case 'NYN':
                        if ($Color['green'] - $Color['red'] > $Compare && $Color['green'] - $Color['blue'] > $Compare) $Greyscale = false;
                        break;

                    case 'NNY':
                        if ($Color['blue'] - $Color['red'] > $Compare && $Color['blue'] - $Color['green'] > $Compare) $Greyscale = false;
                        break;

                    case 'YYN':
                        if ($Color['red'] - $Color['blue'] > $Compare && $Color['green'] - $Color['blue'] > $Compare) $Greyscale = false;
                        break;

                    case 'YNY':
                        if ($Color['red'] - $Color['green'] > $Compare && $Color['blue'] - $Color['green'] > $Compare) $Greyscale = false;
                        break;

                    case 'NYY':
                        if ($Color['blue'] - $Color['red'] > $Compare && $Color['green'] - $Color['red'] > $Compare) $Greyscale = false;
                        break;

                    default:
                        $Greyscale = false;
                }
                if ($Greyscale === true)
                {
                    $Grey = (int)(($Color['red'] + $Color['green'] + $Color['blue']) / 3);
                    imagesetpixel($Img, $X, $Y, imagecolorallocatealpha($Img, $Grey, $Grey, $Grey, $Color['alpha']));
                }
            }
        return $Img;
    }

    public function GDFXColorize($Red = 0, $Green = 0, $Blue = 0, $Alpha = 0, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Red = (int)$Red;
        $Green = (int)$Green;
        $Blue = (int)$Blue;
        if (empty($Red) && empty($Green) && empty($Blue) && empty($Blue)) return;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_COLORIZE, $Red, $Green, $Blue,
            $Alpha))
        {
            $ImageX = $this->Width;
            $ImageY = $this->Height;
            for ($Y = 0; $Y < $ImageY; ++$Y)
                for ($X = 0; $X < $ImageX; ++$X)
                {
                    $Color = imagecolorsforindex($Img, imagecolorat($Img, $X, $Y));
                    $iRed = $Color['red'] + $Red;
                    $iGreen = $Color['green'] + $Green;
                    $iBlue = $Color['blue'] + $Blue;
                    $iAlpha = $Color['alpha'] + $Alpha;
                    if ($iRed > 255) $iRed = 255;
                    if ($iGreen > 255) $iGreen = 255;
                    if ($iBlue > 255) $iBlue = 255;
                    if ($iAlpha > 255) $iAlpha = 255;
                    if ($iRed < 0) $iRed = 0;
                    if ($iGreen < 0) $iGreen = 0;
                    if ($iBlue < 0) $iBlue = 0;
                    if ($iAlpha < 0) $iBlue = 0;
                    imagesetpixel($Img, $X, $Y, imagecolorallocatealpha($Img, $iRed, $iGreen, $iBlue, $iAlpha));
                }
        }
        return $Img;
    }

    public function GDFXNoise($Noise = 50, $Level = 20, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Level = (int)$Level;
        $Noise = (int)$Noise;
        if (empty($Level) && empty($Noise)) return;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        for ($X = 0; $X < $ImageX; $X++)
            for ($Y = 0; $Y < $ImageY; $Y++)
                if (rand(0, 100) <= $Noise)
                {
                    $Color = imagecolorsforindex($Img, imagecolorat($Img, $X, $Y));
                    $Modifier = rand($Level * -1, $Level);
                    $Red = $Color['red'] + $Modifier;
                    $Green = $Color['green'] + $Modifier;
                    $Blue = $Color['blue'] + $Modifier;
                    if ($Red > 255) $Red = 255;
                    if ($Green > 255) $Green = 255;
                    if ($Blue > 255) $Blue = 255;
                    if ($Red < 0) $Red = 0;
                    if ($Green < 0) $Green = 0;
                    if ($Blue < 0) $Blue = 0;
                    imagesetpixel($Img, $X, $Y, imagecolorallocatealpha($Img, $Red, $Green, $Blue, $Color['alpha']));
                }
        return $Img;
    }

    public function GDFXScatter($Level = 4, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Level = (int)$Level;
        if (empty($Level)) return;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        for ($Y = 0; $Y < $ImageY; $Y++)
            for ($X = 0; $X < $ImageX; $X++)
            {
                $DistX = rand(-($Level), $Level);
                $DistY = rand(-($Level), $Level);
                if ($X + $DistX >= $ImageX) continue;
                if ($X + $DistX < 0) continue;
                if ($Y + $DistY >= $ImageY) continue;
                if ($Y + $DistY < 0) continue;
                $Oldcol = imagecolorat($Img, $X, $Y);
                $NewCol = imagecolorat($Img, $X + $DistX, $Y + $DistY);
                imagesetpixel($Img, $X, $Y, $NewCol);
                imagesetpixel($Img, $X + $DistX, $Y + $DistY, $Oldcol);
            }
        return $Img;
    }

    public function GDFXPixelate($Level = 12, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Level = (int)$Level;
        if (empty($Level)) return;
        if (!function_exists('imagefilter') || !imagefilter($Img, IMG_FILTER_PIXELATE, $Level, true))
        {
            $ImageX = $this->Width;
            $ImageY = $this->Height;
            $BSize = (int)$Level;

            for ($X = 0; $X < $ImageX; $X += $BSize)
                for ($Y = 0; $Y < $ImageY; $Y += $BSize)
                {
                    $TCol = imagecolorat($Img, $X, $Y);
                    $NCol = array(
                        'r' => 0,
                        'g' => 0,
                        'b' => 0,
                        'a' => 0);
                    $Cols = array();
                    for ($L = $Y; $L < $Y + $BSize; ++$L)
                        for ($K = $X; $K < $X + $BSize; ++$K)
                        {
                            if ($K < 0)
                            {
                                $Cols[] = $TCol;
                                continue;
                            }
                            if ($K >= $ImageX)
                            {
                                $Cols[] = $TCol;
                                continue;
                            }
                            if ($L < 0)
                            {
                                $Cols[] = $TCol;
                                continue;
                            }
                            if ($L >= $ImageY)
                            {
                                $Cols[] = $TCol;
                                continue;
                            }
                            $Cols[] = imagecolorat($Img, $K, $L);
                        }
                    foreach ($Cols as $Col)
                    {
                        $Color = imagecolorsforindex($Img, $Col);
                        $NCol['r'] += $Color['red'];
                        $NCol['g'] += $Color['green'];
                        $NCol['b'] += $Color['blue'];
                        $NCol['a'] += $Color['alpha'];
                    }
                    $BCount = count($Cols);
                    $NCol['r'] /= $BCount;
                    $NCol['g'] /= $BCount;
                    $NCol['b'] /= $BCount;
                    $NCol['a'] /= $BCount;
                    $NCol['Result'] = imagecolorallocatealpha($Img, $NCol['r'], $NCol['g'], $NCol['b'], $NCol['a']);
                    imagefilledrectangle($Img, $X, $Y, $X + $BSize - 1, $Y + $BSize - 1, $NCol['Result']);
                }
        }
        return $Img;
    }

    public function GDFXGaussianBlur($Level = 1, &$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Level = (int)$Level;
        if (empty($Level)) return;
        $Gaussian = array(
            array(
                1.0,
                2.0,
                1.0),
            array(
                2.0,
                4.0,
                2.0),
            array(
                1.0,
                2.0,
                1.0));
        for ($i = 0; $i < $Level; $i++) imageconvolution($Img, $Gaussian, 16, 0);
        return $Img;
    }

    public function GDFXFisheye(&$Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        $CImageX = $ImageX / 2; //Source middle
        $CImageY = $ImageY / 2;
        if ($ImageX > $ImageY) $OW = 2 * $ImageY / pi(); //Width for the destionation image
        else  $OW = 2 * $ImageX / pi();
        $New = imagecreatetruecolor($OW + 1, $OW + 1);
        imagefill($New, 0, 0, imagecolorallocatealpha($New, 255, 255, 255, 0));
        $OM = $OW / 2;
        for ($Y = 0; $Y <= $OW; ++$Y)
            for ($X = 0; $X <= $OW; ++$X)
            {
                $OTX = $X - $OM;
                $OTY = $Y - $OM; //Y in relation to the middle
                $OH = hypot($OTX, $OTY); //distance
                $Arc = (2 * $OM * asin($OH / $OM)) / (2);
                $Factor = $Arc / $OH;
                if ($OH <= $OM) imagesetpixel($New, $X, $Y, imagecolorat($Img, round($OTX * $Factor + $CImageX),
                        round($OTY * $Factor + $CImageY)));
            }
        imagedestroy($Img);
        $this->Img = $New;
        $this->Initload();
        return $this->Img;
    }

    public function GDFXDream($Pecent = 30, $Type = 0, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $ImageX = $this->Width;
        $ImageY = $this->Height;
        $Effect = $this->GDCreate(255, 255);
        $Type = is_int($Type) ? $Type : rand(0, 5);
        for ($X = 0; $X <= 255; $X++)
            for ($Y = 0; $Y <= 255; $Y++)
            {
                switch ($Type)
                {
                    case 1:
                        $Col = imagecolorallocate($Effect, 255, $Y, $X);
                        break;

                    case 2:
                        $Col = imagecolorallocate($Effect, $Y, 255, $X);
                        break;

                    case 3:
                        $Col = imagecolorallocate($Effect, $X, 255, $Y);
                        break;

                    case 4:
                        $Col = imagecolorallocate($Effect, $X, $Y, 255);
                        break;

                    case 5:
                        $Col = imagecolorallocate($Effect, $Y, $X, 255);
                        break;

                    default:
                        $Col = imagecolorallocate($Effect, 255, $X, $Y);
                }
                imagesetpixel($Effect, $X, $Y, $Col);
            }
        $Effect = $this->GDResize($ImageX, $ImageY, $Effect);
        imagecopymerge($Img, $Effect, 0, 0, 0, 0, $ImageX, $ImageY, $Pecent);
        imagedestroy($Effect);
        $this->Img = $Img;
        $this->Initload();
        return $this->Img;
    }

    ##########################################################################################
    # Section: Special FX Font Effect
    ##########################################################################################

    private function IsTTFBox($TTFBox)
    {
        if (array_key_exists('X', $TTFBox) && array_key_exists('Y', $TTFBox) && array_key_exists('Width', $TTFBox) &&
            array_key_exists('Height', $TTFBox) && array_key_exists('Font', $TTFBox) && array_key_exists('Size',
            $TTFBox) && array_key_exists('Color', $TTFBox) && array_key_exists('Angle', $TTFBox) &&
            array_key_exists('Content', $TTFBox) && !empty($TTFBox['Content']) && !empty($TTFBox['Font']) && !
            empty($TTFBox['Size'])) return true;
        return false;
    }

    public function GDTTFBox($Content, $Font, $Size = 10, $X = 0, $Y = 0, $Color = 0, $Angle = 0)
    {
        $Size = (int)$Size;
        $X = (int)$X;
        $Y = (int)$Y;
        $Angle = (double)$Angle;
        $Color = (int)$Color;
        $TTFBox = array();
        if (function_exists('SysConvVar')) $Font = SysConvVar("{SPTHR_FONT}{$Font}");
        $TBox = imagettfbbox($Size, $Angle, $Font, $Content);
        $TTFBox = array(
            'X' => $X,
            'Y' => $Y + abs($TBox[5]) - (abs($TBox[1]) / 2),
            'Width' => abs($TBox[4] - $TBox[0]),
            'Height' => abs($TBox[5]),
            'Font' => $Font,
            'Size' => $Size,
            'Color' => $Color,
            'Angle' => $Angle,
            'Content' => $Content);
        return $TTFBox;
    }

    public function GDTTFText($TTFBox, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!$this->IsTTFBox($TTFBox)) return false;
        $this->TTFBTxt($TTFBox['Size'], $TTFBox['Angle'], $TTFBox['X'], $TTFBox['Y'], $TTFBox['Color'], $TTFBox['Font'],
            $TTFBox['Content']);
    }

    public function GDTTFTxtGrow($TTFBox, $Grow = 10, $GColor = 0, $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!$this->IsTTFBox($TTFBox)) return false;
        $Grow = (int)$Grow;
        $GColor = (int)$GColor;
        imagealphablending($Img, true);
        $this->TTFBTxt($TTFBox['Size'], $TTFBox['Angle'], $TTFBox['X'], $TTFBox['Y'], $GColor, $TTFBox['Font'],
            $TTFBox['Content'], $Grow, $Img);
        $this->TTFBTxt($TTFBox['Size'], $TTFBox['Angle'], $TTFBox['X'], $TTFBox['Y'], $TTFBox['Color'], $TTFBox['Font'],
            $TTFBox['Content'], 0, $Img);
    }

    public function GDTTFTxtShadow($TTFBox, $Shadow = 10, $GColor = 0, $Direction = 'rb', $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if (!$this->IsTTFBox($TTFBox)) return false;
        $GColor = (int)$GColor;
        $ShadowX = (int)$TTFBox['X'];
        $ShadowY = (int)$TTFBox['Y'];
        switch ($Direction)
        {

            case 'lt':
                $ShadowX -= ($Shadow / 2);
                $ShadowY -= ($Shadow / 2);
                break;

            case 'ct':
                $ShadowY -= ($Shadow / 2);
                break;

            case 'rt':
                $ShadowX += ($Shadow / 2);
                $ShadowY -= ($Shadow / 2);
                break;

            case 'lc':
                $ShadowX -= ($Shadow / 2);
                break;

            case 'rc':
                $ShadowX += ($Shadow / 2);
                break;

            case 'lb':
                $ShadowX -= ($Shadow / 2);
                $ShadowY += ($Shadow / 2);
                break;

            case 'cb':
                $ShadowY += ($Shadow / 2);
                break;

            default:
                $ShadowX += ($Shadow / 2);
                $ShadowY += ($Shadow / 2);
        }
        $this->TTFBTxt($TTFBox['Size'], $TTFBox['Angle'], $ShadowX, $ShadowY, $GColor, $TTFBox['Font'], $TTFBox['Content'],
            $Shadow, $Img);
        $this->TTFBTxt($TTFBox['Size'], $TTFBox['Angle'], $TTFBox['X'], $TTFBox['Y'], $TTFBox['Color'], $TTFBox['Font'],
            $TTFBox['Content'], 0, $Img);
    }

    ##########################################################################################
    # Section: Output or save image
    ##########################################################################################

    public function GDHandle()
    {
        return $this->Img;
    }

    public function GDDuplicate($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $Width = imagesx($Img);
        $Height = imagesy($Img);
        $New = imagecreatetruecolor($Width, $Height);
        imagealphablending($New, false);
        imagesavealpha($New, true);
        imagecopyresampled($New, $Img, 0, 0, 0, 0, $Width, $Height, $Width, $Height);
        return $New;
    }

    public function GDOutput($Output = 'o', $IType = 'png', $File = '', $Params = array(), $Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        $this->Initsave($Img);
        switch (strtolower($IType))
        {
            case 'gif':
                $FncNm = 'imagegif';
                $Mime = 'gif';
                break;
            case 'jpg':
                $FncNm = 'imagejpeg';
                $Mime = 'jpg';
                break;
            case 'png':
                $FncNm = 'imagepng';
                $Mime = 'png';
                break;
            case 'wbmp':
                $FncNm = 'imagewbmp';
                $Mime = 'vnd.wap.wbmp';
                break;
            default:
                return $Img;
        }
        if (!is_array($Params)) $Params = array();
        $Params = array_values($Params);
        switch ($Output)
        {
            case 'o':
                header("Content-Type: image/{$Mime}");
                call_user_func_array($FncNm, array($Img, null) + $Params);
                break;

            case 'f':
                call_user_func_array($FncNm, array($Img, $File) + $Params);
                break;

            case 'd':
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"".basename($File)."\"");
                call_user_func_array($FncNm, array($Img, null) + $Params);
                break;

            case 'r':
                return $Img;
        }
    }

    public function GDClose($Img = null)
    {
        if ($Img === null) $Img = $this->Img;
        if (!$this->Isgdres($Img)) return false;
        if ($Img === $this->Img) $this->Img = null;
        return imagedestroy($Img);
    }

    public function __destruct()
    {
        if ($this->Isgdres($this->Img)) imagedestroy($this->Img);
    }
}

?>