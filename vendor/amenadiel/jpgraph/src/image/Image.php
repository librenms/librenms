<?php

namespace Amenadiel\JpGraph\Image;

use \Amenadiel\JpGraph\Text\LanguageConv;
use \Amenadiel\JpGraph\Text\TTF;
use \Amenadiel\JpGraph\Util;
use \Amenadiel\JpGraph\Util\ErrMsgText;

//=======================================================================
// File:        GD_IMAGE.INC.PHP
// Description: PHP Graph Plotting library. Low level image drawing routines
// Created:     2001-01-08, refactored 2008-03-29
// Ver:         $Id: gd_image.inc.php 1922 2010-01-11 11:42:50Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================

//========================================================================
// CLASS Image
// Description: The very coor image drawing class that encapsulates all
//              calls to the GD library
//              Note: The class used by the library is the decendant
//              class RotImage which extends the Image class with transparent
//              rotation.
//=========================================================================
class Image
{
    public $img = null;
    public $rgb = null;
    public $img_format;
    public $ttf = null;
    public $line_style = LINESTYLE_SOLID;
    public $current_color, $current_color_name;
    public $original_width = 0, $original_height = 0;
    public $plotwidth = 0, $plotheight = 0;

    // for __get, __set
    private $_left_margin = 30, $_right_margin = 30, $_top_margin = 20, $_bottom_margin = 30;
    //private $_plotwidth=0,$_plotheight=0;
    private $_width = 0, $_height = 0;
    private $_line_weight = 1;

    protected $expired = true;
    protected $lastx = 0, $lasty = 0;
    protected $obs_list = array();
    protected $font_size = 12, $font_family = FF_DEFAULT, $font_style = FS_NORMAL;
    protected $font_file = '';
    protected $text_halign = "left", $text_valign = "bottom";
    protected $use_anti_aliasing = false;
    protected $quality = null;
    protected $colorstack = array(), $colorstackidx = 0;
    protected $canvascolor = 'white';
    protected $langconv = null;
    protected $iInterlace = false;
    protected $bbox_cache = array(); // STore the last found tetx bounding box
    protected $ff_font0;
    protected $ff_font0_bold;
    protected $ff_font1;
    protected $ff_font1_bold;
    protected $ff_font2;
    protected $ff_font2_bold;

    //---------------
    // CONSTRUCTOR
    public function __construct($aWidth = 0, $aHeight = 0, $aFormat = DEFAULT_GFORMAT, $aSetAutoMargin = true)
    {

        $this->original_width = $aWidth;
        $this->original_height = $aHeight;
        $this->CreateImgCanvas($aWidth, $aHeight);

        if ($aSetAutoMargin) {
            $this->SetAutoMargin();
        }

        if (!$this->SetImgFormat($aFormat)) {
            Util\JpGraphError::RaiseL(25081, $aFormat); //("JpGraph: Selected graphic format is either not supported or unknown [$aFormat]");
        }
        $this->ttf = new TTF();
        $this->langconv = new LanguageConv();

        $this->ff_font0 = imageloadfont(dirname(dirname(__FILE__)) . "/fonts/FF_FONT0.gdf");
        $this->ff_font1 = imageloadfont(dirname(dirname(__FILE__)) . "/fonts/FF_FONT1.gdf");
        $this->ff_font2 = imageloadfont(dirname(dirname(__FILE__)) . "/fonts/FF_FONT2.gdf");
        $this->ff_font1_bold = imageloadfont(dirname(dirname(__FILE__)) . "/fonts/FF_FONT1-Bold.gdf");
        $this->ff_font2_bold = imageloadfont(dirname(dirname(__FILE__)) . "/fonts/FF_FONT2-Bold.gdf");
    }

    // Enable interlacing in images
    public function SetInterlace($aFlg = true)
    {
        $this->iInterlace = $aFlg;
    }

    // Should we use anti-aliasing. Note: This really slows down graphics!
    public function SetAntiAliasing($aFlg = true)
    {
        $this->use_anti_aliasing = $aFlg;
        if (function_exists('imageantialias')) {
            imageantialias($this->img, $aFlg);
        }/* else {
            Util\JpGraphError::RaiseL(25128); //('The function imageantialias() is not available in your PHP installation. Use the GD version that comes with PHP and not the standalone version.')
        }*/
    }

    public function GetAntiAliasing()
    {
        return $this->use_anti_aliasing;
    }

    public function CreateRawCanvas($aWidth = 0, $aHeight = 0)
    {

        $aWidth *= SUPERSAMPLING_SCALE;
        $aHeight *= SUPERSAMPLING_SCALE;

        if ($aWidth <= 1 || $aHeight <= 1) {
            Util\JpGraphError::RaiseL(25082, $aWidth, $aHeight); //("Illegal sizes specified for width or height when creating an image, (width=$aWidth, height=$aHeight)");
        }

        $this->img = @imagecreatetruecolor($aWidth, $aHeight);
        if ($this->img < 1) {
            Util\JpGraphError::RaiseL(25126);
            //die("Can't create truecolor image. Check that you really have GD2 library installed.");
        }
        $this->SetAlphaBlending();

        if ($this->iInterlace) {
            imageinterlace($this->img, 1);
        }
        if ($this->rgb != null) {
            $this->rgb->img = $this->img;
        } else {
            $this->rgb = new RGB($this->img);
        }
    }

    public function CloneCanvasH()
    {
        $oldimage = $this->img;
        $this->CreateRawCanvas($this->width, $this->height);
        imagecopy($this->img, $oldimage, 0, 0, 0, 0, $this->width, $this->height);
        return $oldimage;
    }

    public function CreateImgCanvas($aWidth = 0, $aHeight = 0)
    {

        $old = array($this->img, $this->width, $this->height);

        $aWidth = round($aWidth);
        $aHeight = round($aHeight);

        $this->width = $aWidth;
        $this->height = $aHeight;

        if ($aWidth == 0 || $aHeight == 0) {
            // We will set the final size later.
            // Note: The size must be specified before any other
            // img routines that stroke anything are called.
            $this->img = null;
            $this->rgb = null;
            return $old;
        }

        $this->CreateRawCanvas($aWidth, $aHeight);
        // Set canvas color (will also be the background color for a
        // a pallett image
        $this->SetColor($this->canvascolor);
        $this->FilledRectangle(0, 0, $this->width - 1, $this->height - 1);

        return $old;
    }

    public function CopyCanvasH($aToHdl, $aFromHdl, $aToX, $aToY, $aFromX, $aFromY, $aWidth, $aHeight, $aw = -1, $ah = -1)
    {
        if ($aw === -1) {
            $aw = $aWidth;
            $ah = $aHeight;
            $f = 'imagecopyresized';
        } else {
            $f = 'imagecopyresampled';
        }
        $f($aToHdl, $aFromHdl, $aToX, $aToY, $aFromX, $aFromY, $aWidth, $aHeight, $aw, $ah);
    }

    public function Copy($fromImg, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth = -1, $fromHeight = -1)
    {
        $this->CopyCanvasH($this->img, $fromImg, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth, $fromHeight);
    }

    public function CopyMerge($fromImg, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth = -1, $fromHeight = -1, $aMix = 100)
    {
        if ($aMix == 100) {
            $this->CopyCanvasH($this->img, $fromImg,
                $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth, $fromHeight);
        } else {
            if (($fromWidth != -1 && ($fromWidth != $toWidth)) || ($fromHeight != -1 && ($fromHeight != $fromHeight))) {
                // Create a new canvas that will hold the re-scaled original from image
                if ($toWidth <= 1 || $toHeight <= 1) {
                    Util\JpGraphError::RaiseL(25083); //('Illegal image size when copying image. Size for copied to image is 1 pixel or less.');
                }

                $tmpimg = @imagecreatetruecolor($toWidth, $toHeight);

                if ($tmpimg < 1) {
                    Util\JpGraphError::RaiseL(25084); //('Failed to create temporary GD canvas. Out of memory ?');
                }
                $this->CopyCanvasH($tmpimg, $fromImg, 0, 0, 0, 0,
                    $toWidth, $toHeight, $fromWidth, $fromHeight);
                $fromImg = $tmpimg;
            }
            imagecopymerge($this->img, $fromImg, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $aMix);
        }
    }

    public static function GetWidth($aImg = null)
    {
        if ($aImg === null) {
            $aImg = $this->img;
        }
        return imagesx($aImg);
    }

    public static function GetHeight($aImg = null)
    {
        if ($aImg === null) {
            $aImg = $this->img;
        }
        return imagesy($aImg);
    }

    public static function CreateFromString($aStr)
    {
        $img = imagecreatefromstring($aStr);
        if ($img === false) {
            Util\JpGraphError::RaiseL(25085);
            //('An image can not be created from the supplied string. It is either in a format not supported or the string is representing an corrupt image.');
        }
        return $img;
    }

    public function SetCanvasH($aHdl)
    {
        $this->img = $aHdl;
        $this->rgb->img = $aHdl;
    }

    public function SetCanvasColor($aColor)
    {
        $this->canvascolor = $aColor;
    }

    public function SetAlphaBlending($aFlg = true)
    {
        ImageAlphaBlending($this->img, $aFlg);
    }

    public function SetAutoMargin()
    {
        $min_bm = 5;
        $lm = min(40, $this->width / 7);
        $rm = min(20, $this->width / 10);
        $tm = max(5, $this->height / 7);
        $bm = max($min_bm, $this->height / 6);
        $this->SetMargin($lm, $rm, $tm, $bm);
    }

    //---------------
    // PUBLIC METHODS

    public function SetFont($family, $style = FS_NORMAL, $size = 10)
    {
        $this->font_family = $family;
        $this->font_style = $style;
        $this->font_size = $size * SUPERSAMPLING_SCALE;
        $this->font_file = '';
        if (($this->font_family == FF_FONT1 || $this->font_family == FF_FONT2) && $this->font_style == FS_BOLD) {
            ++$this->font_family;
        }
        if ($this->font_family > FF_FONT2 + 1) {
            // A TTF font so get the font file

            // Check that this PHP has support for TTF fonts
            if (!function_exists('imagettfbbox')) {
                // use internal font when php is configured without '--with-ttf'
                $this->font_family = FF_FONT1;
                //  Util\JpGraphError::RaiseL(25087);//('This PHP build has not been configured with TTF support. You need to recompile your PHP installation with FreeType support.');
            } else {
                $this->font_file = $this->ttf->File($this->font_family, $this->font_style);
            }
        }
    }

    // Get the specific height for a text string
    public function GetTextHeight($txt = "", $angle = 0)
    {
        $tmp = preg_split('/\n/', $txt);
        $n = count($tmp);
        $m = 0;
        for ($i = 0; $i < $n; ++$i) {
            $m = max($m, strlen($tmp[$i]));
        }

        if ($this->font_family <= FF_FONT2 + 1) {
            if ($angle == 0) {
                $h = imagefontheight($this->font_family);
                if ($h === false) {
                    Util\JpGraphError::RaiseL(25088); //('You have a misconfigured GD font support. The call to imagefontwidth() fails.');
                }

                return $n * $h;
            } else {
                $w = @imagefontwidth($this->font_family);
                if ($w === false) {
                    Util\JpGraphError::RaiseL(25088); //('You have a misconfigured GD font support. The call to imagefontwidth() fails.');
                }

                return $m * $w;
            }
        } else {
            $bbox = $this->GetTTFBBox($txt, $angle);
            return $bbox[1] - $bbox[5] + 1;
        }
    }

    // Estimate font height
    public function GetFontHeight($angle = 0)
    {
        $txt = "XOMg";
        return $this->GetTextHeight($txt, $angle);
    }

    // Approximate font width with width of letter "O"
    public function GetFontWidth($angle = 0)
    {
        $txt = 'O';
        return $this->GetTextWidth($txt, $angle);
    }

    // Get actual width of text in absolute pixels. Note that the width is the
    // texts projected with onto the x-axis. Call with angle=0 to get the true
    // etxt width.
    public function GetTextWidth($txt, $angle = 0)
    {

        $tmp = preg_split('/\n/', $txt);
        $n = count($tmp);
        if ($this->font_family <= FF_FONT2 + 1) {

            $m = 0;
            for ($i = 0; $i < $n; ++$i) {
                $l = strlen($tmp[$i]);
                if ($l > $m) {
                    $m = $l;
                }
            }

            if ($angle == 0) {
                $w = @imagefontwidth($this->font_family);
                if ($w === false) {
                    Util\JpGraphError::RaiseL(25088); //('You have a misconfigured GD font support. The call to imagefontwidth() fails.');
                }
                return $m * $w;
            } else {
                // 90 degrees internal so height becomes width
                $h = @imagefontheight($this->font_family);
                if ($h === false) {
                    Util\JpGraphError::RaiseL(25089); //('You have a misconfigured GD font support. The call to imagefontheight() fails.');
                }
                return $n * $h;
            }
        } else {
            // For TTF fonts we must walk through a lines and find the
            // widest one which we use as the width of the multi-line
            // paragraph
            $m = 0;
            for ($i = 0; $i < $n; ++$i) {
                $bbox = $this->GetTTFBBox($tmp[$i], $angle);
                $mm = $bbox[2] - $bbox[0];
                if ($mm > $m) {
                    $m = $mm;
                }

            }
            return $m;
        }
    }

    // Draw text with a box around it
    public function StrokeBoxedText($x, $y, $txt, $dir = 0, $fcolor = "white", $bcolor = "black",
        $shadowcolor = false, $paragraph_align = "left",
        $xmarg = 6, $ymarg = 4, $cornerradius = 0, $dropwidth = 3) {

        $oldx = $this->lastx;
        $oldy = $this->lasty;

        if (!is_numeric($dir)) {
            if ($dir == "h") {
                $dir = 0;
            } elseif ($dir == "v") {
                $dir = 90;
            } else {
                Util\JpGraphError::RaiseL(25090, $dir);
            }
            //(" Unknown direction specified in call to StrokeBoxedText() [$dir]");
        }

        if ($this->font_family >= FF_FONT0 && $this->font_family <= FF_FONT2 + 1) {
            $width = $this->GetTextWidth($txt, $dir);
            $height = $this->GetTextHeight($txt, $dir);
        } else {
            $width = $this->GetBBoxWidth($txt, $dir);
            $height = $this->GetBBoxHeight($txt, $dir);
        }

        $height += 2 * $ymarg;
        $width += 2 * $xmarg;

        if ($this->text_halign == "right") {
            $x -= $width;
        } elseif ($this->text_halign == "center") {
            $x -= $width / 2;
        }

        if ($this->text_valign == "bottom") {
            $y -= $height;
        } elseif ($this->text_valign == "center") {
            $y -= $height / 2;
        }

        $olda = $this->SetAngle(0);

        if ($shadowcolor) {
            $this->PushColor($shadowcolor);
            $this->FilledRoundedRectangle($x - $xmarg + $dropwidth, $y - $ymarg + $dropwidth,
                $x + $width + $dropwidth, $y + $height - $ymarg + $dropwidth,
                $cornerradius);
            $this->PopColor();
            $this->PushColor($fcolor);
            $this->FilledRoundedRectangle($x - $xmarg, $y - $ymarg,
                $x + $width, $y + $height - $ymarg,
                $cornerradius);
            $this->PopColor();
            $this->PushColor($bcolor);
            $this->RoundedRectangle($x - $xmarg, $y - $ymarg,
                $x + $width, $y + $height - $ymarg, $cornerradius);
            $this->PopColor();
        } else {
            if ($fcolor) {
                $oc = $this->current_color;
                $this->SetColor($fcolor);
                $this->FilledRoundedRectangle($x - $xmarg, $y - $ymarg, $x + $width, $y + $height - $ymarg, $cornerradius);
                $this->current_color = $oc;
            }
            if ($bcolor) {
                $oc = $this->current_color;
                $this->SetColor($bcolor);
                $this->RoundedRectangle($x - $xmarg, $y - $ymarg, $x + $width, $y + $height - $ymarg, $cornerradius);
                $this->current_color = $oc;
            }
        }

        $h = $this->text_halign;
        $v = $this->text_valign;
        $this->SetTextAlign("left", "top");

        $debug = false;
        $this->StrokeText($x, $y, $txt, $dir, $paragraph_align, $debug);

        $bb = array($x - $xmarg, $y + $height - $ymarg, $x + $width, $y + $height - $ymarg,
            $x + $width, $y - $ymarg, $x - $xmarg, $y - $ymarg);
        $this->SetTextAlign($h, $v);

        $this->SetAngle($olda);
        $this->lastx = $oldx;
        $this->lasty = $oldy;

        return $bb;
    }

    // Draw text with a box around it. This time the box will be rotated
    // with the text. The previous method will just make a larger enough non-rotated
    // box to hold the text inside.
    public function StrokeBoxedText2($x, $y, $txt, $dir = 0, $fcolor = "white", $bcolor = "black",
        $shadowcolor = false, $paragraph_align = "left",
        $xmarg = 6, $ymarg = 4, $cornerradius = 0, $dropwidth = 3) {

        // This version of boxed text will stroke a rotated box round the text
        // thta will follow the angle of the text.
        // This has two implications:
        // 1) This methos will only support TTF fonts
        // 2) The only two alignment that makes sense are centered or baselined

        if ($this->font_family <= FF_FONT2 + 1) {
            Util\JpGraphError::RaiseL(25131); //StrokeBoxedText2() Only support TTF fonts and not built in bitmap fonts
        }

        $oldx = $this->lastx;
        $oldy = $this->lasty;
        $dir = $this->NormAngle($dir);

        if (!is_numeric($dir)) {
            if ($dir == "h") {
                $dir = 0;
            } elseif ($dir == "v") {
                $dir = 90;
            } else {
                Util\JpGraphError::RaiseL(25090, $dir);
            }
            //(" Unknown direction specified in call to StrokeBoxedText() [$dir]");
        }

        $width = $this->GetTextWidth($txt, 0) + 2 * $xmarg;
        $height = $this->GetTextHeight($txt, 0) + 2 * $ymarg;
        $rect_width = $this->GetBBoxWidth($txt, $dir);
        $rect_height = $this->GetBBoxHeight($txt, $dir);

        $baseline_offset = $this->bbox_cache[1] - 1;

        if ($this->text_halign == "center") {
            if ($dir >= 0 && $dir <= 90) {

                $x -= $rect_width / 2;
                $x += sin($dir * M_PI / 180) * $height;
                $y += $rect_height / 2;

            } elseif ($dir >= 270 && $dir <= 360) {

                $x -= $rect_width / 2;
                $y -= $rect_height / 2;
                $y += cos($dir * M_PI / 180) * $height;

            } elseif ($dir >= 90 && $dir <= 180) {

                $x += $rect_width / 2;
                $y += $rect_height / 2;
                $y += cos($dir * M_PI / 180) * $height;

            } else {
                // $dir > 180 &&  $dir < 270
                $x += $rect_width / 2;
                $x += sin($dir * M_PI / 180) * $height;
                $y -= $rect_height / 2;
            }
        }

        // Rotate the box around this point
        $this->SetCenter($x, $y);
        $olda = $this->SetAngle(-$dir);

        // We need to use adjusted coordinats for the box to be able
        // to draw the box below the baseline. This cannot be done before since
        // the rotating point must be the original x,y since that is arounbf the
        // point where the text will rotate and we cannot change this since
        // that is where the GD/GreeType will rotate the text

        // For smaller <14pt font we need to do some additional
        // adjustments to make it look good
        if ($this->font_size < 14) {
            $x -= 2;
            $y += 2;
        } else {
            //  $y += $baseline_offset;
        }

        if ($shadowcolor) {
            $this->PushColor($shadowcolor);
            $this->FilledRectangle($x - $xmarg + $dropwidth, $y + $ymarg + $dropwidth - $height,
                $x + $width + $dropwidth, $y + $ymarg + $dropwidth);
            //$cornerradius);
            $this->PopColor();
            $this->PushColor($fcolor);
            $this->FilledRectangle($x - $xmarg, $y + $ymarg - $height,
                $x + $width, $y + $ymarg);
            //$cornerradius);
            $this->PopColor();
            $this->PushColor($bcolor);
            $this->Rectangle($x - $xmarg, $y + $ymarg - $height,
                $x + $width, $y + $ymarg);
            //$cornerradius);
            $this->PopColor();
        } else {
            if ($fcolor) {
                $oc = $this->current_color;
                $this->SetColor($fcolor);
                $this->FilledRectangle($x - $xmarg, $y + $ymarg - $height, $x + $width, $y + $ymarg); //,$cornerradius);
                $this->current_color = $oc;
            }
            if ($bcolor) {
                $oc = $this->current_color;
                $this->SetColor($bcolor);
                $this->Rectangle($x - $xmarg, $y + $ymarg - $height, $x + $width, $y + $ymarg); //,$cornerradius);
                $this->current_color = $oc;
            }
        }

        if ($this->font_size < 14) {
            $x += 2;
            $y -= 2;
        } else {

            // Restore the original y before we stroke the text
            // $y -= $baseline_offset;

        }

        $this->SetCenter(0, 0);
        $this->SetAngle($olda);

        $h = $this->text_halign;
        $v = $this->text_valign;
        if ($this->text_halign == 'center') {
            $this->SetTextAlign('center', 'basepoint');
        } else {
            $this->SetTextAlign('basepoint', 'basepoint');
        }

        $debug = false;
        $this->StrokeText($x, $y, $txt, $dir, $paragraph_align, $debug);

        $bb = array($x - $xmarg, $y + $height - $ymarg,
            $x + $width, $y + $height - $ymarg,
            $x + $width, $y - $ymarg,
            $x - $xmarg, $y - $ymarg);

        $this->SetTextAlign($h, $v);
        $this->SetAngle($olda);

        $this->lastx = $oldx;
        $this->lasty = $oldy;

        return $bb;
    }

    // Set text alignment
    public function SetTextAlign($halign, $valign = "bottom")
    {
        $this->text_halign = $halign;
        $this->text_valign = $valign;
    }

    public function _StrokeBuiltinFont($x, $y, $txt, $dir, $paragraph_align, &$aBoundingBox, $aDebug = false)
    {

        if (is_numeric($dir) && $dir != 90 && $dir != 0) {
            Util\JpGraphError::RaiseL(25091);
        }
        //(" Internal font does not support drawing text at arbitrary angle. Use TTF fonts instead.");

        $h = $this->GetTextHeight($txt);
        $fh = $this->GetFontHeight();
        $w = $this->GetTextWidth($txt);

        if ($this->text_halign == "right") {
            $x -= $dir == 0 ? $w : $h;
        } elseif ($this->text_halign == "center") {
            // For center we subtract 1 pixel since this makes the middle
            // be prefectly in the middle
            $x -= $dir == 0 ? $w / 2 - 1 : $h / 2;
        }
        if ($this->text_valign == "top") {
            $y += $dir == 0 ? $h : $w;
        } elseif ($this->text_valign == "center") {
            $y += $dir == 0 ? $h / 2 : $w / 2;
        }

        $use_font = $this->font_family;

        if ($dir == 90) {
            imagestringup($this->img, $use_font, $x, $y, $txt, $this->current_color);
            $aBoundingBox = array(round($x), round($y), round($x), round($y - $w), round($x + $h), round($y - $w), round($x + $h), round($y));
            if ($aDebug) {
                // Draw bounding box
                $this->PushColor('green');
                $this->Polygon($aBoundingBox, true);
                $this->PopColor();
            }
        } else {
            if (preg_match('/\n/', $txt)) {
                $tmp = preg_split('/\n/', $txt);
                for ($i = 0; $i < count($tmp); ++$i) {
                    $w1 = $this->GetTextWidth($tmp[$i]);
                    if ($paragraph_align == "left") {
                        imagestring($this->img, $use_font, $x, $y - $h + 1 + $i * $fh, $tmp[$i], $this->current_color);
                    } elseif ($paragraph_align == "right") {
                        imagestring($this->img, $use_font, $x + ($w - $w1), $y - $h + 1 + $i * $fh, $tmp[$i], $this->current_color);
                    } else {
                        imagestring($this->img, $use_font, $x + $w / 2 - $w1 / 2, $y - $h + 1 + $i * $fh, $tmp[$i], $this->current_color);
                    }
                }
            } else {
                //Put the text
                imagestring($this->img, $use_font, $x, $y - $h + 1, $txt, $this->current_color);
            }
            if ($aDebug) {
                // Draw the bounding rectangle and the bounding box
                $p1 = array(round($x), round($y), round($x), round($y - $h), round($x + $w), round($y - $h), round($x + $w), round($y));

                // Draw bounding box
                $this->PushColor('green');
                $this->Polygon($p1, true);
                $this->PopColor();

            }
            $aBoundingBox = array(round($x), round($y), round($x), round($y - $h), round($x + $w), round($y - $h), round($x + $w), round($y));
        }
    }

    public function AddTxtCR($aTxt)
    {
        // If the user has just specified a '\n'
        // instead of '\n\t' we have to add '\r' since
        // the width will be too muchy otherwise since when
        // we print we stroke the individually lines by hand.
        $e = explode("\n", $aTxt);
        $n = count($e);
        for ($i = 0; $i < $n; ++$i) {
            $e[$i] = str_replace("\r", "", $e[$i]);
        }
        return implode("\n\r", $e);
    }

    public function NormAngle($a)
    {
        // Normalize angle in degrees
        // Normalize angle to be between 0-360
        while ($a > 360) {
            $a -= 360;
        }

        while ($a < -360) {
            $a += 360;
        }

        if ($a < 0) {
            $a = 360 + $a;
        }

        return $a;
    }

    public function imagettfbbox_fixed($size, $angle, $fontfile, $text)
    {

        if (!USE_LIBRARY_IMAGETTFBBOX) {

            $bbox = @imagettfbbox($size, $angle, $fontfile, $text);
            if ($bbox === false) {
                Util\JpGraphError::RaiseL(25092, $this->font_file);
                //("There is either a configuration problem with TrueType or a problem reading font file (".$this->font_file."). Make sure file exists and is in a readable place for the HTTP process. (If 'basedir' restriction is enabled in PHP then the font file must be located in the document root.). It might also be a wrongly installed FreeType library. Try uppgrading to at least FreeType 2.1.13 and recompile GD with the correct setup so it can find the new FT library.");
            }
            $this->bbox_cache = $bbox;
            return $bbox;
        }

        // The built in imagettfbbox is buggy for angles != 0 so
        // we calculate this manually by getting the bounding box at
        // angle = 0 and then rotate the bounding box manually
        $bbox = @imagettfbbox($size, 0, $fontfile, $text);
        if ($bbox === false) {
            Util\JpGraphError::RaiseL(25092, $this->font_file);
            //("There is either a configuration problem with TrueType or a problem reading font file (".$this->font_file."). Make sure file exists and is in a readable place for the HTTP process. (If 'basedir' restriction is enabled in PHP then the font file must be located in the document root.). It might also be a wrongly installed FreeType library. Try uppgrading to at least FreeType 2.1.13 and recompile GD with the correct setup so it can find the new FT library.");
        }

        $angle = $this->NormAngle($angle);

        $a = $angle * M_PI / 180;
        $ca = cos($a);
        $sa = sin($a);
        $ret = array();

        // We always add 1 pixel to the left since the left edge of the bounding
        // box is sometimes coinciding with the first pixel of the text
        //$bbox[0] -= 1;
        //$bbox[6] -= 1;

        // For roatated text we need to add extra width for rotated
        // text since the kerning and stroking of the TTF is not the same as for
        // text at a 0 degree angle

        if ($angle > 0.001 && abs($angle - 360) > 0.001) {
            $h = abs($bbox[7] - $bbox[1]);
            $w = abs($bbox[2] - $bbox[0]);

            $bbox[0] -= 2;
            $bbox[6] -= 2;
            // The width is underestimated so compensate for that
            $bbox[2] += round($w * 0.06);
            $bbox[4] += round($w * 0.06);

            // and we also need to compensate with increased height
            $bbox[5] -= round($h * 0.1);
            $bbox[7] -= round($h * 0.1);

            if ($angle > 90) {
                // For angles > 90 we also need to extend the height further down
                // by the baseline since that is also one more problem
                $bbox[1] += round($h * 0.15);
                $bbox[3] += round($h * 0.15);

                // and also make it slighty less height
                $bbox[7] += round($h * 0.05);
                $bbox[5] += round($h * 0.05);

                // And we need to move the box slightly top the rright (from a tetx perspective)
                $bbox[0] += round($w * 0.02);
                $bbox[6] += round($w * 0.02);

                if ($angle > 180) {
                    // And we need to move the box slightly to the left (from a text perspective)
                    $bbox[0] -= round($w * 0.02);
                    $bbox[6] -= round($w * 0.02);
                    $bbox[2] -= round($w * 0.02);
                    $bbox[4] -= round($w * 0.02);

                }

            }
            for ($i = 0; $i < 7; $i += 2) {
                $ret[$i] = round($bbox[$i] * $ca + $bbox[$i + 1] * $sa);
                $ret[$i + 1] = round($bbox[$i + 1] * $ca - $bbox[$i] * $sa);
            }
            $this->bbox_cache = $ret;
            return $ret;
        } else {
            $this->bbox_cache = $bbox;
            return $bbox;
        }
    }

    // Deprecated
    public function GetTTFBBox($aTxt, $aAngle = 0)
    {
        $bbox = $this->imagettfbbox_fixed($this->font_size, $aAngle, $this->font_file, $aTxt);
        return $bbox;
    }

    public function GetBBoxTTF($aTxt, $aAngle = 0)
    {
        // Normalize the bounding box to become a minimum
        // enscribing rectangle

        $aTxt = $this->AddTxtCR($aTxt);

        if (!is_readable($this->font_file)) {
            Util\JpGraphError::RaiseL(25093, $this->font_file);
            //('Can not read font file ('.$this->font_file.') in call to Image::GetBBoxTTF. Please make sure that you have set a font before calling this method and that the font is installed in the TTF directory.');
        }
        $bbox = $this->imagettfbbox_fixed($this->font_size, $aAngle, $this->font_file, $aTxt);

        if ($aAngle == 0) {
            return $bbox;
        }

        if ($aAngle >= 0) {
            if ($aAngle <= 90) {
                //<=0
                $bbox = array($bbox[6], $bbox[1], $bbox[2], $bbox[1],
                    $bbox[2], $bbox[5], $bbox[6], $bbox[5]);
            } elseif ($aAngle <= 180) {
                //<= 2
                $bbox = array($bbox[4], $bbox[7], $bbox[0], $bbox[7],
                    $bbox[0], $bbox[3], $bbox[4], $bbox[3]);
            } elseif ($aAngle <= 270) {
                //<= 3
                $bbox = array($bbox[2], $bbox[5], $bbox[6], $bbox[5],
                    $bbox[6], $bbox[1], $bbox[2], $bbox[1]);
            } else {
                $bbox = array($bbox[0], $bbox[3], $bbox[4], $bbox[3],
                    $bbox[4], $bbox[7], $bbox[0], $bbox[7]);
            }
        } elseif ($aAngle < 0) {
            if ($aAngle <= -270) {
                // <= -3
                $bbox = array($bbox[6], $bbox[1], $bbox[2], $bbox[1],
                    $bbox[2], $bbox[5], $bbox[6], $bbox[5]);
            } elseif ($aAngle <= -180) {
                // <= -2
                $bbox = array($bbox[0], $bbox[3], $bbox[4], $bbox[3],
                    $bbox[4], $bbox[7], $bbox[0], $bbox[7]);
            } elseif ($aAngle <= -90) {
                // <= -1
                $bbox = array($bbox[2], $bbox[5], $bbox[6], $bbox[5],
                    $bbox[6], $bbox[1], $bbox[2], $bbox[1]);
            } else {
                $bbox = array($bbox[0], $bbox[3], $bbox[4], $bbox[3],
                    $bbox[4], $bbox[7], $bbox[0], $bbox[7]);
            }
        }
        return $bbox;
    }

    public function GetBBoxHeight($aTxt, $aAngle = 0)
    {
        $box = $this->GetBBoxTTF($aTxt, $aAngle);
        return abs($box[7] - $box[1]);
    }

    public function GetBBoxWidth($aTxt, $aAngle = 0)
    {
        $box = $this->GetBBoxTTF($aTxt, $aAngle);
        return $box[2] - $box[0] + 1;
    }

    public function _StrokeTTF($x, $y, $txt, $dir, $paragraph_align, &$aBoundingBox, $debug = false)
    {

        // Setup default inter line margin for paragraphs to be
        // 3% of the font height.
        $ConstLineSpacing = 0.03;

        // Remember the anchor point before adjustment
        if ($debug) {
            $ox = $x;
            $oy = $y;
        }

        if (!preg_match('/\n/', $txt) || ($dir > 0 && preg_match('/\n/', $txt))) {
            // Format a single line

            $txt = $this->AddTxtCR($txt);
            $bbox = $this->GetBBoxTTF($txt, $dir);
            $width = $this->GetBBoxWidth($txt, $dir);
            $height = $this->GetBBoxHeight($txt, $dir);

            // The special alignment "basepoint" is mostly used internally
            // in the library. This will put the anchor position at the left
            // basepoint of the tetx. This is the default anchor point for
            // TTF text.

            if ($this->text_valign != 'basepoint') {
                // Align x,y ot lower left corner of bbox

                if ($this->text_halign == 'right') {
                    $x -= $width;
                    $x -= $bbox[0];
                } elseif ($this->text_halign == 'center') {
                    $x -= $width / 2;
                    $x -= $bbox[0];
                } elseif ($this->text_halign == 'baseline') {
                    // This is only support for text at 90 degree !!
                    // Do nothing the text is drawn at baseline by default
                }

                if ($this->text_valign == 'top') {
                    $y -= $bbox[1]; // Adjust to bottom of text
                    $y += $height;
                } elseif ($this->text_valign == 'center') {
                    $y -= $bbox[1]; // Adjust to bottom of text
                    $y += $height / 2;
                } elseif ($this->text_valign == 'baseline') {
                    // This is only support for text at 0 degree !!
                    // Do nothing the text is drawn at baseline by default
                }
            }
            ImageTTFText($this->img, $this->font_size, $dir, $x, $y,
                $this->current_color, $this->font_file, $txt);

            // Calculate and return the co-ordinates for the bounding box
            $box = $this->imagettfbbox_fixed($this->font_size, $dir, $this->font_file, $txt);
            $p1 = array();

            for ($i = 0; $i < 4; ++$i) {
                $p1[] = round($box[$i * 2] + $x);
                $p1[] = round($box[$i * 2 + 1] + $y);
            }
            $aBoundingBox = $p1;

            // Debugging code to highlight the bonding box and bounding rectangle
            // For text at 0 degrees the bounding box and bounding rectangle are the
            // same
            if ($debug) {
                // Draw the bounding rectangle and the bounding box

                $p = array();
                $p1 = array();

                for ($i = 0; $i < 4; ++$i) {
                    $p[] = $bbox[$i * 2] + $x;
                    $p[] = $bbox[$i * 2 + 1] + $y;
                    $p1[] = $box[$i * 2] + $x;
                    $p1[] = $box[$i * 2 + 1] + $y;
                }

                // Draw bounding box
                $this->PushColor('green');
                $this->Polygon($p1, true);
                $this->PopColor();

                // Draw bounding rectangle
                $this->PushColor('darkgreen');
                $this->Polygon($p, true);
                $this->PopColor();

                // Draw a cross at the anchor point
                $this->PushColor('red');
                $this->Line($ox - 15, $oy, $ox + 15, $oy);
                $this->Line($ox, $oy - 15, $ox, $oy + 15);
                $this->PopColor();
            }
        } else {
            // Format a text paragraph
            $fh = $this->GetFontHeight();

            // Line margin is 25% of font height
            $linemargin = round($fh * $ConstLineSpacing);
            $fh += $linemargin;
            $w = $this->GetTextWidth($txt);

            $y -= $linemargin / 2;
            $tmp = preg_split('/\n/', $txt);
            $nl = count($tmp);
            $h = $nl * $fh;

            if ($this->text_halign == 'right') {
                $x -= $dir == 0 ? $w : $h;
            } elseif ($this->text_halign == 'center') {
                $x -= $dir == 0 ? $w / 2 : $h / 2;
            }

            if ($this->text_valign == 'top') {
                $y += $dir == 0 ? $h : $w;
            } elseif ($this->text_valign == 'center') {
                $y += $dir == 0 ? $h / 2 : $w / 2;
            }

            // Here comes a tricky bit.
            // Since we have to give the position for the string at the
            // baseline this means thaht text will move slightly up
            // and down depending on any of it's character descend below
            // the baseline, for example a 'g'. To adjust the Y-position
            // we therefore adjust the text with the baseline Y-offset
            // as used for the current font and size. This will keep the
            // baseline at a fixed positoned disregarding the actual
            // characters in the string.
            $standardbox = $this->GetTTFBBox('Gg', $dir);
            $yadj = $standardbox[1];
            $xadj = $standardbox[0];
            $aBoundingBox = array();
            for ($i = 0; $i < $nl; ++$i) {
                $wl = $this->GetTextWidth($tmp[$i]);
                $bbox = $this->GetTTFBBox($tmp[$i], $dir);
                if ($paragraph_align == 'left') {
                    $xl = $x;
                } elseif ($paragraph_align == 'right') {
                    $xl = $x + ($w - $wl);
                } else {
                    // Center
                    $xl = $x + $w / 2 - $wl / 2;
                }

                // In theory we should adjust with full pre-lead to get the lines
                // lined up but this doesn't look good so therfore we only adjust with
                // half th pre-lead
                $xl -= $bbox[0] / 2;
                $yl = $y - $yadj;
                //$xl = $xl- $xadj;
                ImageTTFText($this->img, $this->font_size, $dir, $xl, $yl - ($h - $fh) + $fh * $i,
                    $this->current_color, $this->font_file, $tmp[$i]);

                // echo "xl=$xl,".$tmp[$i]." <br>";
                if ($debug) {
                    // Draw the bounding rectangle around each line
                    $box = @ImageTTFBBox($this->font_size, $dir, $this->font_file, $tmp[$i]);
                    $p = array();
                    for ($j = 0; $j < 4; ++$j) {
                        $p[] = $bbox[$j * 2] + $xl;
                        $p[] = $bbox[$j * 2 + 1] + $yl - ($h - $fh) + $fh * $i;
                    }

                    // Draw bounding rectangle
                    $this->PushColor('darkgreen');
                    $this->Polygon($p, true);
                    $this->PopColor();
                }
            }

            // Get the bounding box
            $bbox = $this->GetBBoxTTF($txt, $dir);
            for ($j = 0; $j < 4; ++$j) {
                $bbox[$j * 2] += round($x);
                $bbox[$j * 2 + 1] += round($y - ($h - $fh) - $yadj);
            }
            $aBoundingBox = $bbox;

            if ($debug) {
                // Draw a cross at the anchor point
                $this->PushColor('red');
                $this->Line($ox - 25, $oy, $ox + 25, $oy);
                $this->Line($ox, $oy - 25, $ox, $oy + 25);
                $this->PopColor();
            }

        }
    }

    public function StrokeText($x, $y, $txt, $dir = 0, $paragraph_align = "left", $debug = false)
    {

        $x = round($x);
        $y = round($y);

        // Do special language encoding
        $txt = $this->langconv->Convert($txt, $this->font_family);

        if (!is_numeric($dir)) {
            Util\JpGraphError::RaiseL(25094); //(" Direction for text most be given as an angle between 0 and 90.");
        }

        if ($this->font_family >= FF_FONT0 && $this->font_family <= FF_FONT2 + 1) {
            $this->_StrokeBuiltinFont($x, $y, $txt, $dir, $paragraph_align, $boundingbox, $debug);
        } elseif ($this->font_family >= _FIRST_FONT && $this->font_family <= _LAST_FONT) {
            $this->_StrokeTTF($x, $y, $txt, $dir, $paragraph_align, $boundingbox, $debug);
        } else {
            Util\JpGraphError::RaiseL(25095); //(" Unknown font font family specification. ");
        }
        return $boundingbox;
    }

    public function SetMargin($lm, $rm, $tm, $bm)
    {

        $this->left_margin = $lm;
        $this->right_margin = $rm;
        $this->top_margin = $tm;
        $this->bottom_margin = $bm;

        $this->plotwidth = $this->width - $this->left_margin - $this->right_margin;
        $this->plotheight = $this->height - $this->top_margin - $this->bottom_margin;

        if ($this->width > 0 && $this->height > 0) {
            if ($this->plotwidth < 0 || $this->plotheight < 0) {
                Util\JpGraphError::RaiseL(25130, $this->plotwidth, $this->plotheight);
                //Util\JpGraphError::raise("To small plot area. ($lm,$rm,$tm,$bm : $this->plotwidth x $this->plotheight). With the given image size and margins there is to little space left for the plot. Increase the plot size or reduce the margins.");
            }
        }
    }

    public function SetTransparent($color)
    {
        imagecolortransparent($this->img, $this->rgb->allocate($color));
    }

    public function SetColor($color, $aAlpha = 0)
    {
        $this->current_color_name = $color;
        $this->current_color = $this->rgb->allocate($color, $aAlpha);
        if ($this->current_color == -1) {
            $tc = imagecolorstotal($this->img);
            Util\JpGraphError::RaiseL(25096);
            //("Can't allocate any more colors. Image has already allocated maximum of <b>$tc colors</b>. This might happen if you have anti-aliasing turned on together with a background image or perhaps gradient fill since this requires many, many colors. Try to turn off anti-aliasing. If there is still a problem try downgrading the quality of the background image to use a smaller pallete to leave some entries for your graphs. You should try to limit the number of colors in your background image to 64. If there is still problem set the constant DEFINE(\"USE_APPROX_COLORS\",true); in jpgraph.php This will use approximative colors when the palette is full. Unfortunately there is not much JpGraph can do about this since the palette size is a limitation of current graphic format and what the underlying GD library suppports.");
        }
        return $this->current_color;
    }

    public function PushColor($color)
    {
        if ($color != "") {
            $this->colorstack[$this->colorstackidx] = $this->current_color_name;
            $this->colorstack[$this->colorstackidx + 1] = $this->current_color;
            $this->colorstackidx += 2;
            $this->SetColor($color);
        } else {
            Util\JpGraphError::RaiseL(25097); //("Color specified as empty string in PushColor().");
        }
    }

    public function PopColor()
    {
        if ($this->colorstackidx < 1) {
            Util\JpGraphError::RaiseL(25098); //(" Negative Color stack index. Unmatched call to PopColor()");
        }
        $this->current_color = $this->colorstack[--$this->colorstackidx];
        $this->current_color_name = $this->colorstack[--$this->colorstackidx];
    }

    public function SetLineWeight($weight)
    {
        $old = $this->line_weight;
        imagesetthickness($this->img, $weight);
        $this->line_weight = $weight;
        return $old;
    }

    public function SetStartPoint($x, $y)
    {
        $this->lastx = round($x);
        $this->lasty = round($y);
    }

    public function Arc($cx, $cy, $w, $h, $s, $e)
    {
        // GD Arc doesn't like negative angles
        while ($s < 0) {
            $s += 360;
        }

        while ($e < 0) {
            $e += 360;
        }

        imagearc($this->img, round($cx), round($cy), round($w), round($h), $s, $e, $this->current_color);
    }

    public function FilledArc($xc, $yc, $w, $h, $s, $e, $style = '')
    {
        $s = round($s);
        $e = round($e);
        while ($s < 0) {
            $s += 360;
        }

        while ($e < 0) {
            $e += 360;
        }

        if ($style == '') {
            $style = IMG_ARC_PIE;
        }

        if (abs($s - $e) > 0) {
            imagefilledarc($this->img, round($xc), round($yc), round($w), round($h), $s, $e, $this->current_color, $style);
            //            $this->DrawImageSmoothArc($this->img,round($xc),round($yc),round($w),round($h),$s,$e,$this->current_color,$style);
        }
    }

    public function FilledCakeSlice($cx, $cy, $w, $h, $s, $e)
    {
        $this->CakeSlice($cx, $cy, $w, $h, $s, $e, $this->current_color_name);
    }

    public function CakeSlice($xc, $yc, $w, $h, $s, $e, $fillcolor = "", $arccolor = "")
    {
        $s = round($s);
        $e = round($e);
        $w = round($w);
        $h = round($h);
        $xc = round($xc);
        $yc = round($yc);
        if ($s == $e) {
            // A full circle. We draw this a plain circle
            $this->PushColor($fillcolor);
            imagefilledellipse($this->img, $xc, $yc, 2 * $w, 2 * $h, $this->current_color);

            // If antialiasing is used then we often don't have any color no the surrounding
            // arc. So, we need to check for this special case so we don't send an empty
            // color to the push function. In this case we use the fill color for the arc as well
            if ($arccolor != '') {
                $this->PopColor();
                $this->PushColor($arccolor);
            }
            imageellipse($this->img, $xc, $yc, 2 * $w, 2 * $h, $this->current_color);
            $this->Line($xc, $yc, cos($s * M_PI / 180) * $w + $xc, $yc + sin($s * M_PI / 180) * $h);
            $this->PopColor();
        } else {
            $this->PushColor($fillcolor);
            $this->FilledArc($xc, $yc, 2 * $w, 2 * $h, $s, $e);
            $this->PopColor();
            if ($arccolor != "") {
                $this->PushColor($arccolor);
                // We add 2 pixels to make the Arc() better aligned with
                // the filled arc.
                imagefilledarc($this->img, $xc, $yc, 2 * $w, 2 * $h, $s, $e, $this->current_color, IMG_ARC_NOFILL | IMG_ARC_EDGED);
                $this->PopColor();
            }
        }
    }

    public function Ellipse($xc, $yc, $w, $h)
    {
        $this->Arc($xc, $yc, $w, $h, 0, 360);
    }

    public function Circle($xc, $yc, $r)
    {
        imageellipse($this->img, round($xc), round($yc), $r * 2, $r * 2, $this->current_color);
        //        $this->DrawImageSmoothArc($this->img,round($xc),round($yc),$r*2+1,$r*2+1,0,360,$this->current_color);
        //        $this->imageSmoothCircle($this->img, round($xc),round($yc), $r*2+1, $this->current_color);
    }

    public function FilledCircle($xc, $yc, $r)
    {
        imagefilledellipse($this->img, round($xc), round($yc), 2 * $r, 2 * $r, $this->current_color);
        //        $this->DrawImageSmoothArc($this->img, round($xc), round($yc), 2*$r, 2*$r, 0, 360, $this->current_color);
    }

    // Linear Color InterPolation
    public function lip($f, $t, $p)
    {
        $p = round($p, 1);
        $r = $f[0] + ($t[0] - $f[0]) * $p;
        $g = $f[1] + ($t[1] - $f[1]) * $p;
        $b = $f[2] + ($t[2] - $f[2]) * $p;
        return array($r, $g, $b);
    }

    // Set line style dashed, dotted etc
    public function SetLineStyle($s)
    {
        if (is_numeric($s)) {
            if ($s < 1 || $s > 4) {
                Util\JpGraphError::RaiseL(25101, $s); //(" Illegal numeric argument to SetLineStyle(): ($s)");
            }
        } elseif (is_string($s)) {
            if ($s == "solid") {
                $s = 1;
            } elseif ($s == "dotted") {
                $s = 2;
            } elseif ($s == "dashed") {
                $s = 3;
            } elseif ($s == "longdashed") {
                $s = 4;
            } else {
                Util\JpGraphError::RaiseL(25102, $s); //(" Illegal string argument to SetLineStyle(): $s");
            }
        } else {
            Util\JpGraphError::RaiseL(25103, $s); //(" Illegal argument to SetLineStyle $s");
        }
        $old = $this->line_style;
        $this->line_style = $s;
        return $old;
    }

    // Same as Line but take the line_style into account
    public function StyleLine($x1, $y1, $x2, $y2, $aStyle = '', $from_grid_class = false)
    {
        if ($this->line_weight <= 0) {
            return;
        }

        if ($aStyle === '') {
            $aStyle = $this->line_style;
        }

        $dashed_line_method = 'DashedLine';
        if ($from_grid_class) {
            $dashed_line_method = 'DashedLineForGrid';
        }

        // Add error check since dashed line will only work if anti-alias is disabled
        // this is a limitation in GD

        if ($aStyle == 1) {
            // Solid style. We can handle anti-aliasing for this
            $this->Line($x1, $y1, $x2, $y2);
        } else {
            // Since the GD routines doesn't handle AA for styled line
            // we have no option than to turn it off to get any lines at
            // all if the weight > 1
            $oldaa = $this->GetAntiAliasing();
            if ($oldaa && $this->line_weight > 1) {
                $this->SetAntiAliasing(false);
            }

            switch ($aStyle) {
                case 2: // Dotted
                    $this->$dashed_line_method($x1, $y1, $x2, $y2, 2, 6);
                    break;
                case 3: // Dashed
                    $this->$dashed_line_method($x1, $y1, $x2, $y2, 5, 9);
                    break;
                case 4: // Longdashes
                    $this->$dashed_line_method($x1, $y1, $x2, $y2, 9, 13);
                    break;
                default:
                    Util\JpGraphError::RaiseL(25104, $this->line_style); //(" Unknown line style: $this->line_style ");
                    break;
            }
            if ($oldaa) {
                $this->SetAntiAliasing(true);
            }
        }
    }

    public function DashedLine($x1, $y1, $x2, $y2, $dash_length = 1, $dash_space = 4)
    {

        if ($this->line_weight <= 0) {
            return;
        }

        // Add error check to make sure anti-alias is not enabled.
        // Dashed line does not work with anti-alias enabled. This
        // is a limitation in GD.
        if ($this->use_anti_aliasing) {
            //            Util\JpGraphError::RaiseL(25129); // Anti-alias can not be used with dashed lines. Please disable anti-alias or use solid lines.
        }

        $x1 = round($x1);
        $x2 = round($x2);
        $y1 = round($y1);
        $y2 = round($y2);

        $dash_length *= SUPERSAMPLING_SCALE;
        $dash_space *= SUPERSAMPLING_SCALE;

        $style = array_fill(0, $dash_length, $this->current_color);
        $style = array_pad($style, $dash_space, IMG_COLOR_TRANSPARENT);
        imagesetstyle($this->img, $style);
        imageline($this->img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);

        $this->lastx = $x2;
        $this->lasty = $y2;
    }

    public function DashedLineForGrid($x1, $y1, $x2, $y2, $dash_length = 1, $dash_space = 4)
    {

        if ($this->line_weight <= 0) {
            return;
        }

        // Add error check to make sure anti-alias is not enabled.
        // Dashed line does not work with anti-alias enabled. This
        // is a limitation in GD.
        if ($this->use_anti_aliasing) {
            //            Util\JpGraphError::RaiseL(25129); // Anti-alias can not be used with dashed lines. Please disable anti-alias or use solid lines.
        }

        $x1 = round($x1);
        $x2 = round($x2);
        $y1 = round($y1);
        $y2 = round($y2);

        /*
        $dash_length *= $this->scale;
        $dash_space  *= $this->scale;
         */

        $dash_length = 2;
        $dash_length = 4;
        imagesetthickness($this->img, 1);
        $style = array_fill(0, $dash_length, $this->current_color); //hexdec('CCCCCC'));
        $style = array_pad($style, $dash_space, IMG_COLOR_TRANSPARENT);
        imagesetstyle($this->img, $style);
        imageline($this->img, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);

        $this->lastx = $x2;
        $this->lasty = $y2;
    }

    public function Line($x1, $y1, $x2, $y2)
    {

        if ($this->line_weight <= 0) {
            return;
        }

        $x1 = round($x1);
        $x2 = round($x2);
        $y1 = round($y1);
        $y2 = round($y2);

        imageline($this->img, $x1, $y1, $x2, $y2, $this->current_color);
        //        $this->DrawLine($this->img, $x1, $y1, $x2, $y2, $this->line_weight, $this->current_color);
        $this->lastx = $x2;
        $this->lasty = $y2;
    }

    public function Polygon($p, $closed = false, $fast = false)
    {

        if ($this->line_weight <= 0) {
            return;
        }

        $n = count($p);
        $oldx = $p[0];
        $oldy = $p[1];
        if ($fast) {
            for ($i = 2; $i < $n; $i += 2) {
                imageline($this->img, $oldx, $oldy, $p[$i], $p[$i + 1], $this->current_color);
                $oldx = $p[$i];
                $oldy = $p[$i + 1];
            }
            if ($closed) {
                imageline($this->img, $p[$n * 2 - 2], $p[$n * 2 - 1], $p[0], $p[1], $this->current_color);
            }
        } else {
            for ($i = 2; $i < $n; $i += 2) {
                $this->StyleLine($oldx, $oldy, $p[$i], $p[$i + 1]);
                $oldx = $p[$i];
                $oldy = $p[$i + 1];
            }
            if ($closed) {
                $this->StyleLine($oldx, $oldy, $p[0], $p[1]);
            }
        }
    }

    public function FilledPolygon($pts)
    {
        $n = count($pts);
        if ($n == 0) {
            Util\JpGraphError::RaiseL(25105); //('NULL data specified for a filled polygon. Check that your data is not NULL.');
        }
        for ($i = 0; $i < $n; ++$i) {
            $pts[$i] = round($pts[$i]);
        }
        $old = $this->line_weight;
        imagesetthickness($this->img, 1);
        imagefilledpolygon($this->img, $pts, count($pts) / 2, $this->current_color);
        $this->line_weight = $old;
        imagesetthickness($this->img, $old);
    }

    public function Rectangle($xl, $yu, $xr, $yl)
    {
        $this->Polygon(array($xl, $yu, $xr, $yu, $xr, $yl, $xl, $yl, $xl, $yu));
    }

    public function FilledRectangle($xl, $yu, $xr, $yl)
    {
        $this->FilledPolygon(array($xl, $yu, $xr, $yu, $xr, $yl, $xl, $yl));
    }

    public function FilledRectangle2($xl, $yu, $xr, $yl, $color1, $color2, $style = 1)
    {
        // Fill a rectangle with lines of two colors
        if ($style === 1) {
            // Horizontal stripe
            if ($yl < $yu) {
                $t = $yl;
                $yl = $yu;
                $yu = $t;
            }
            for ($y = $yu; $y <= $yl; ++$y) {
                $this->SetColor($color1);
                $this->Line($xl, $y, $xr, $y);
                ++$y;
                $this->SetColor($color2);
                $this->Line($xl, $y, $xr, $y);
            }
        } else {
            if ($xl < $xl) {
                $t = $xl;
                $xl = $xr;
                $xr = $t;
            }
            for ($x = $xl; $x <= $xr; ++$x) {
                $this->SetColor($color1);
                $this->Line($x, $yu, $x, $yl);
                ++$x;
                $this->SetColor($color2);
                $this->Line($x, $yu, $x, $yl);
            }
        }
    }

    public function ShadowRectangle($xl, $yu, $xr, $yl, $fcolor = false, $shadow_width = 4, $shadow_color = 'darkgray', $useAlpha = true)
    {
        // This is complicated by the fact that we must also handle the case where
        // the reactangle has no fill color
        $xl = floor($xl);
        $yu = floor($yu);
        $xr = floor($xr);
        $yl = floor($yl);
        $this->PushColor($shadow_color);
        $shadowAlpha = 0;
        $this->SetLineWeight(1);
        $this->SetLineStyle('solid');
        $basecolor = $this->rgb->Color($shadow_color);
        $shadow_color = array($basecolor[0], $basecolor[1], $basecolor[2]);
        for ($i = 0; $i < $shadow_width; ++$i) {
            $this->SetColor($shadow_color, $shadowAlpha);
            $this->Line($xr - $shadow_width + $i, $yu + $shadow_width,
                $xr - $shadow_width + $i, $yl - $shadow_width - 1 + $i);
            $this->Line($xl + $shadow_width, $yl - $shadow_width + $i,
                $xr - $shadow_width + $i, $yl - $shadow_width + $i);
            if ($useAlpha) {
                $shadowAlpha += 1.0 / $shadow_width;
            }

        }

        $this->PopColor();
        if ($fcolor == false) {
            $this->Rectangle($xl, $yu, $xr - $shadow_width - 1, $yl - $shadow_width - 1);
        } else {
            $this->PushColor($fcolor);
            $this->FilledRectangle($xl, $yu, $xr - $shadow_width - 1, $yl - $shadow_width - 1);
            $this->PopColor();
            $this->Rectangle($xl, $yu, $xr - $shadow_width - 1, $yl - $shadow_width - 1);
        }
    }

    public function FilledRoundedRectangle($xt, $yt, $xr, $yl, $r = 5)
    {
        if ($r == 0) {
            $this->FilledRectangle($xt, $yt, $xr, $yl);
            return;
        }

        // To avoid overlapping fillings (which will look strange
        // when alphablending is enabled) we have no choice but
        // to fill the five distinct areas one by one.

        // Center square
        $this->FilledRectangle($xt + $r, $yt + $r, $xr - $r, $yl - $r);
        // Top band
        $this->FilledRectangle($xt + $r, $yt, $xr - $r, $yt + $r);
        // Bottom band
        $this->FilledRectangle($xt + $r, $yl - $r, $xr - $r, $yl);
        // Left band
        $this->FilledRectangle($xt, $yt + $r, $xt + $r, $yl - $r);
        // Right band
        $this->FilledRectangle($xr - $r, $yt + $r, $xr, $yl - $r);

        // Topleft & Topright arc
        $this->FilledArc($xt + $r, $yt + $r, $r * 2, $r * 2, 180, 270);
        $this->FilledArc($xr - $r, $yt + $r, $r * 2, $r * 2, 270, 360);

        // Bottomleft & Bottom right arc
        $this->FilledArc($xt + $r, $yl - $r, $r * 2, $r * 2, 90, 180);
        $this->FilledArc($xr - $r, $yl - $r, $r * 2, $r * 2, 0, 90);

    }

    public function RoundedRectangle($xt, $yt, $xr, $yl, $r = 5)
    {

        if ($r == 0) {
            $this->Rectangle($xt, $yt, $xr, $yl);
            return;
        }

        // Top & Bottom line
        $this->Line($xt + $r, $yt, $xr - $r, $yt);
        $this->Line($xt + $r, $yl, $xr - $r, $yl);

        // Left & Right line
        $this->Line($xt, $yt + $r, $xt, $yl - $r);
        $this->Line($xr, $yt + $r, $xr, $yl - $r);

        // Topleft & Topright arc
        $this->Arc($xt + $r, $yt + $r, $r * 2, $r * 2, 180, 270);
        $this->Arc($xr - $r, $yt + $r, $r * 2, $r * 2, 270, 360);

        // Bottomleft & Bottomright arc
        $this->Arc($xt + $r, $yl - $r, $r * 2, $r * 2, 90, 180);
        $this->Arc($xr - $r, $yl - $r, $r * 2, $r * 2, 0, 90);
    }

    public function FilledBevel($x1, $y1, $x2, $y2, $depth = 2, $color1 = 'white@0.4', $color2 = 'darkgray@0.4')
    {
        $this->FilledRectangle($x1, $y1, $x2, $y2);
        $this->Bevel($x1, $y1, $x2, $y2, $depth, $color1, $color2);
    }

    public function Bevel($x1, $y1, $x2, $y2, $depth = 2, $color1 = 'white@0.4', $color2 = 'black@0.5')
    {
        $this->PushColor($color1);
        for ($i = 0; $i < $depth; ++$i) {
            $this->Line($x1 + $i, $y1 + $i, $x1 + $i, $y2 - $i);
            $this->Line($x1 + $i, $y1 + $i, $x2 - $i, $y1 + $i);
        }
        $this->PopColor();

        $this->PushColor($color2);
        for ($i = 0; $i < $depth; ++$i) {
            $this->Line($x1 + $i, $y2 - $i, $x2 - $i, $y2 - $i);
            $this->Line($x2 - $i, $y1 + $i, $x2 - $i, $y2 - $i - 1);
        }
        $this->PopColor();
    }

    public function StyleLineTo($x, $y)
    {
        $this->StyleLine($this->lastx, $this->lasty, $x, $y);
        $this->lastx = $x;
        $this->lasty = $y;
    }

    public function LineTo($x, $y)
    {
        $this->Line($this->lastx, $this->lasty, $x, $y);
        $this->lastx = $x;
        $this->lasty = $y;
    }

    public function Point($x, $y)
    {
        imagesetpixel($this->img, round($x), round($y), $this->current_color);
    }

    public function Fill($x, $y)
    {
        imagefill($this->img, round($x), round($y), $this->current_color);
    }

    public function FillToBorder($x, $y, $aBordColor)
    {
        $bc = $this->rgb->allocate($aBordColor);
        if ($bc == -1) {
            Util\JpGraphError::RaiseL(25106); //('Image::FillToBorder : Can not allocate more colors');
        }
        imagefilltoborder($this->img, round($x), round($y), $bc, $this->current_color);
    }

    public function SetExpired($aFlg = true)
    {
        $this->expired = $aFlg;
    }

    // Generate image header
    public function Headers()
    {

        // In case we are running from the command line with the client version of
        // PHP we can't send any headers.
        $sapi = php_sapi_name();
        if ($sapi == 'cli') {
            return;
        }

        // These parameters are set by headers_sent() but they might cause
        // an undefined variable error unless they are initilized
        $file = '';
        $lineno = '';
        if (headers_sent($file, $lineno)) {
            $file = basename($file);
            $t = new ErrMsgText();
            $msg = $t->Get(10, $file, $lineno);
            die($msg);
        }

        if ($this->expired) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }
        header("Content-type: image/$this->img_format");
    }

    // Adjust image quality for formats that allow this
    public function SetQuality($q)
    {
        $this->quality = $q;
    }

    // Stream image to browser or to file
    public function Stream($aFile = "")
    {
        $this->DoSupersampling();

        $func = "image" . $this->img_format;
        if ($this->img_format == "jpeg" && $this->quality != null) {
            $res = @$func($this->img, $aFile, $this->quality);
        } else {
            if ($aFile != "") {
                $res = @$func($this->img, $aFile);
                if (!$res) {
                    Util\JpGraphError::RaiseL(25107, $aFile); //("Can't write to file '$aFile'. Check that the process running PHP has enough permission.");
                }
            } else {
                $res = @$func($this->img);
                if (!$res) {
                    Util\JpGraphError::RaiseL(25108); //("Can't stream image. This is most likely due to a faulty PHP/GD setup. Try to recompile PHP and use the built-in GD library that comes with PHP.");
                }

            }
        }
    }

    // Do SuperSampling using $scale
    public function DoSupersampling()
    {
        if (SUPERSAMPLING_SCALE <= 1) {
            return $this->img;
        }

        $dst_img = @imagecreatetruecolor($this->original_width, $this->original_height);
        imagecopyresampled($dst_img, $this->img, 0, 0, 0, 0, $this->original_width, $this->original_height, $this->width, $this->height);
        $this->Destroy();
        return $this->img = $dst_img;
    }

    // Clear resources used by image (this is normally not used since all resources are/should be
    // returned when the script terminates
    public function Destroy()
    {
        imagedestroy($this->img);
    }

    // Specify image format. Note depending on your installation
    // of PHP not all formats may be supported.
    public function SetImgFormat($aFormat, $aQuality = 75)
    {
        $this->quality = $aQuality;
        $aFormat = strtolower($aFormat);
        $tst = true;
        $supported = imagetypes();
        if ($aFormat == "auto") {
            if ($supported & IMG_PNG) {
                $this->img_format = "png";
            } elseif ($supported & IMG_JPG) {
                $this->img_format = "jpeg";
            } elseif ($supported & IMG_GIF) {
                $this->img_format = "gif";
            } elseif ($supported & IMG_WBMP) {
                $this->img_format = "wbmp";
            } elseif ($supported & IMG_XPM) {
                $this->img_format = "xpm";
            } else {
                Util\JpGraphError::RaiseL(25109); //("Your PHP (and GD-lib) installation does not appear to support any known graphic formats. You need to first make sure GD is compiled as a module to PHP. If you also want to use JPEG images you must get the JPEG library. Please see the PHP docs for details.");
            }
            return true;
        } else {
            if ($aFormat == "jpeg" || $aFormat == "png" || $aFormat == "gif") {
                if ($aFormat == "jpeg" && !($supported & IMG_JPG)) {
                    $tst = false;
                } elseif ($aFormat == "png" && !($supported & IMG_PNG)) {
                    $tst = false;
                } elseif ($aFormat == "gif" && !($supported & IMG_GIF)) {
                    $tst = false;
                } elseif ($aFormat == "wbmp" && !($supported & IMG_WBMP)) {
                    $tst = false;
                } elseif ($aFormat == "xpm" && !($supported & IMG_XPM)) {
                    $tst = false;
                } else {
                    $this->img_format = $aFormat;
                    return true;
                }
            } else {
                $tst = false;
            }
            if (!$tst) {
                Util\JpGraphError::RaiseL(25110, $aFormat); //(" Your PHP installation does not support the chosen graphic format: $aFormat");
            }
        }
    }

    /**
     * Draw Line
     */
    public function DrawLine($im, $x1, $y1, $x2, $y2, $weight, $color)
    {
        if ($weight == 1) {
            return imageline($im, $x1, $y1, $x2, $y2, $color);
        }

        $angle = (atan2(($y1 - $y2), ($x2 - $x1)));

        $dist_x = $weight * (sin($angle)) / 2;
        $dist_y = $weight * (cos($angle)) / 2;

        $p1x = ceil(($x1 + $dist_x));
        $p1y = ceil(($y1 + $dist_y));
        $p2x = ceil(($x2 + $dist_x));
        $p2y = ceil(($y2 + $dist_y));
        $p3x = ceil(($x2 - $dist_x));
        $p3y = ceil(($y2 - $dist_y));
        $p4x = ceil(($x1 - $dist_x));
        $p4y = ceil(($y1 - $dist_y));

        $array = array($p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y);
        imagefilledpolygon($im, $array, (count($array) / 2), $color);

        // for antialias
        imageline($im, $p1x, $p1y, $p2x, $p2y, $color);
        imageline($im, $p3x, $p3y, $p4x, $p4y, $color);
        return;

        return imageline($this->img, $x1, $y1, $x2, $y2, $this->current_color);
        $weight = 8;
        if ($weight <= 1) {
            return imageline($this->img, $x1, $y1, $x2, $y2, $this->current_color);
        }

        $pts = array();

        $weight /= 2;

        if ($y2 - $y1 == 0) {
            // x line
            $pts = array();
            $pts[] = $x1;
            $pts[] = $y1 - $weight;
            $pts[] = $x1;
            $pts[] = $y1 + $weight;
            $pts[] = $x2;
            $pts[] = $y2 + $weight;
            $pts[] = $x2;
            $pts[] = $y2 - $weight;

        } elseif ($x2 - $x1 == 0) {
            // y line
            $pts = array();
            $pts[] = $x1 - $weight;
            $pts[] = $y1;
            $pts[] = $x1 + $weight;
            $pts[] = $y1;
            $pts[] = $x2 + $weight;
            $pts[] = $y2;
            $pts[] = $x2 - $weight;
            $pts[] = $y2;

        } else {

            var_dump($x1, $x2, $y1, $y2);
            $length = sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
            var_dump($length);exit;
            exit;

            /*
        $lean = ($y2 - $y1) / ($x2 - $x1);
        $lean2 = -1 / $lean;
        $sin = $lean / ($y2 - $y1);
        $cos = $lean / ($x2 - $x1);

        $pts[] = $x1 + (-$weight * $sin); $pts[] = $y1 + (-$weight * $cos);
        $pts[] = $x1 + (+$weight * $sin); $pts[] = $y1 + (+$weight * $cos);
        $pts[] = $x2 + (+$weight * $sin); $pts[] = $y2 + (+$weight * $cos);
        $pts[] = $x2 + (-$weight * $sin); $pts[] = $y2 + (-$weight * $cos);
         */
        }

        //print_r($pts);exit;
        if (count($pts) / 2 < 3) {
            return;
        }

        imagesetthickness($im, 1);
        imagefilledpolygon($im, $pts, count($pts) / 2, $color);

        $weight *= 2;

        //        $this->DrawImageSmoothArc($im, $x1, $y1, $weight, $weight, 0, 360, $color);
        //        $this->DrawImageSmoothArc($im, $x2, $y2, $weight, $weight, 0, 360, $color);
    }

    public function DrawImageSmoothArc($im, $xc, $yc, $w, $h, $s, $e, $color, $style = null)
    {
        $tmp = $s;
        $s = (360 - $e) / 180 * M_PI;
        $e = (360 - $tmp) / 180 * M_PI;
        return imageSmoothArc($im, round($xc), round($yc), round($w), round($h), $this->CreateColorForImageSmoothArc($color), $s, $e);
    }

    public function CreateColorForImageSmoothArc($color)
    {
        $alpha = $color >> 24 & 0xFF;
        $red = $color >> 16 & 0xFF;
        $green = $color >> 8 & 0xFF;
        $blue = $color & 0xFF;

        //var_dump($alpha, $red, $green, $blue);exit;

        return array($red, $green, $blue, $alpha);
    }

    public function imageSmoothCircle(&$img, $cx, $cy, $cr, $color)
    {
        $ir = $cr;
        $ix = 0;
        $iy = $ir;
        $ig = 2 * $ir - 3;
        $idgr = -6;
        $idgd = 4 * $ir - 10;
        $fill = imageColorExactAlpha($img, $color['R'], $color['G'], $color['B'], 0);
        imageLine($img, $cx + $cr - 1, $cy, $cx, $cy, $fill);
        imageLine($img, $cx - $cr + 1, $cy, $cx - 1, $cy, $fill);
        imageLine($img, $cx, $cy + $cr - 1, $cx, $cy + 1, $fill);
        imageLine($img, $cx, $cy - $cr + 1, $cx, $cy - 1, $fill);
        $draw = imageColorExactAlpha($img, $color['R'], $color['G'], $color['B'], 42);
        imageSetPixel($img, $cx + $cr, $cy, $draw);
        imageSetPixel($img, $cx - $cr, $cy, $draw);
        imageSetPixel($img, $cx, $cy + $cr, $draw);
        imageSetPixel($img, $cx, $cy - $cr, $draw);
        while ($ix <= $iy - 2) {
            if ($ig < 0) {
                $ig += $idgd;
                $idgd -= 8;
                $iy--;
            } else {
                $ig += $idgr;
                $idgd -= 4;
            }
            $idgr -= 4;
            $ix++;
            imageLine($img, $cx + $ix, $cy + $iy - 1, $cx + $ix, $cy + $ix, $fill);
            imageLine($img, $cx + $ix, $cy - $iy + 1, $cx + $ix, $cy - $ix, $fill);
            imageLine($img, $cx - $ix, $cy + $iy - 1, $cx - $ix, $cy + $ix, $fill);
            imageLine($img, $cx - $ix, $cy - $iy + 1, $cx - $ix, $cy - $ix, $fill);
            imageLine($img, $cx + $iy - 1, $cy + $ix, $cx + $ix, $cy + $ix, $fill);
            imageLine($img, $cx + $iy - 1, $cy - $ix, $cx + $ix, $cy - $ix, $fill);
            imageLine($img, $cx - $iy + 1, $cy + $ix, $cx - $ix, $cy + $ix, $fill);
            imageLine($img, $cx - $iy + 1, $cy - $ix, $cx - $ix, $cy - $ix, $fill);
            $filled = 0;
            for ($xx = $ix - 0.45; $xx < $ix + 0.5; $xx += 0.2) {
                for ($yy = $iy - 0.45; $yy < $iy + 0.5; $yy += 0.2) {
                    if (sqrt(pow($xx, 2) + pow($yy, 2)) < $cr) {
                        $filled += 4;
                    }

                }
            }
            $draw = imageColorExactAlpha($img, $color['R'], $color['G'], $color['B'], (100 - $filled));
            imageSetPixel($img, $cx + $ix, $cy + $iy, $draw);
            imageSetPixel($img, $cx + $ix, $cy - $iy, $draw);
            imageSetPixel($img, $cx - $ix, $cy + $iy, $draw);
            imageSetPixel($img, $cx - $ix, $cy - $iy, $draw);
            imageSetPixel($img, $cx + $iy, $cy + $ix, $draw);
            imageSetPixel($img, $cx + $iy, $cy - $ix, $draw);
            imageSetPixel($img, $cx - $iy, $cy + $ix, $draw);
            imageSetPixel($img, $cx - $iy, $cy - $ix, $draw);
        }
    }

    public function __get($name)
    {

        if (strpos($name, 'raw_') !== false) {
            // if $name == 'raw_left_margin' , return $this->_left_margin;
            $variable_name = '_' . str_replace('raw_', '', $name);
            return $this->$variable_name;
        }

        $variable_name = '_' . $name;

        if (isset($this->$variable_name)) {
            return $this->$variable_name * SUPERSAMPLING_SCALE;
        } else {
            Util\JpGraphError::RaiseL('25132', $name);
        }
    }

    public function __set($name, $value)
    {
        $this->{'_' . $name} = $value;
    }

} // CLASS
