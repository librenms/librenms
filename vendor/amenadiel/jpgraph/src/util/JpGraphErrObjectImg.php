<?php
namespace Amenadiel\JpGraph\Util;

use Amenadiel\JpGraph\Image;
use Amenadiel\JpGraph\Text;

//==============================================================
// An image based error handler
//==============================================================
class JpGraphErrObjectImg extends JpGraphErrObject
{

    public function __construct()
    {
        parent::__construct();
        // Empty. Reserved for future use
    }

    public function Raise($aMsg, $aHalt = true)
    {
        $img_iconerror =
        'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAAaV' .
        'BMVEX//////2Xy8mLl5V/Z2VvMzFi/v1WyslKlpU+ZmUyMjEh/' .
        'f0VyckJlZT9YWDxMTDjAwMDy8sLl5bnY2K/MzKW/v5yyspKlpY' .
        'iYmH+MjHY/PzV/f2xycmJlZVlZWU9MTEXY2Ms/PzwyMjLFTjea' .
        'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACx' .
        'IAAAsSAdLdfvwAAAAHdElNRQfTBgISOCqusfs5AAABLUlEQVR4' .
        '2tWV3XKCMBBGWfkranCIVClKLd/7P2Q3QsgCxjDTq+6FE2cPH+' .
        'xJ0Ogn2lQbsT+Wrs+buAZAV4W5T6Bs0YXBBwpKgEuIu+JERAX6' .
        'wM2rHjmDdEITmsQEEmWADgZm6rAjhXsoMGY9B/NZBwJzBvn+e3' .
        'wHntCAJdGu9SviwIwoZVDxPB9+Rc0TSEbQr0j3SA1gwdSn6Db0' .
        '6Tm1KfV6yzWGQO7zdpvyKLKBDmRFjzeB3LYgK7r6A/noDAfjtS' .
        'IXaIzbJSv6WgUebTMV4EoRB8a2mQiQjgtF91HdKDKZ1gtFtQjk' .
        'YcWaR5OKOhkYt+ZsTFdJRfPAApOpQYJTNHvCRSJR6SJngQadfc' .
        'vd69OLMddVOPCGVnmrFD8bVYd3JXfxXPtLR/+mtv59/ALWiiMx' .
        'qL72fwAAAABJRU5ErkJggg==';

        if (function_exists("imagetypes")) {
            $supported = imagetypes();
        } else {
            $supported = 0;
        }

        if (!function_exists('imagecreatefromstring')) {
            $supported = 0;
        }

        if (ob_get_length() || headers_sent() || !($supported & IMG_PNG)) {
            // Special case for headers already sent or that the installation doesn't support
            // the PNG format (which the error icon is encoded in).
            // Dont return an image since it can't be displayed
            die($this->iTitle . ' ' . $aMsg);
        }

        $aMsg = wordwrap($aMsg, 55);
        $lines = substr_count($aMsg, "\n");

        // Create the error icon GD
        $erricon = Image\Image::CreateFromString(base64_decode($img_iconerror));

        // Create an image that contains the error text.
        $w = 400;
        $h = 100 + 15 * max(0, $lines - 3);

        $img = new Image\Image($w, $h);

        // Drop shadow
        $img->SetColor("gray");
        $img->FilledRectangle(5, 5, $w - 1, $h - 1, 10);
        $img->SetColor("gray:0.7");
        $img->FilledRectangle(5, 5, $w - 3, $h - 3, 10);

        // Window background
        $img->SetColor("lightblue");
        $img->FilledRectangle(1, 1, $w - 5, $h - 5);
        $img->CopyCanvasH($img->img, $erricon, 5, 30, 0, 0, 40, 40);

        // Window border
        $img->SetColor("black");
        $img->Rectangle(1, 1, $w - 5, $h - 5);
        $img->Rectangle(0, 0, $w - 4, $h - 4);

        // Window top row
        $img->SetColor("darkred");
        for ($y = 3; $y < 18; $y += 2) {
            $img->Line(1, $y, $w - 6, $y);
        }

        // "White shadow"
        $img->SetColor("white");

        // Left window edge
        $img->Line(2, 2, 2, $h - 5);
        $img->Line(2, 2, $w - 6, 2);

        // "Gray button shadow"
        $img->SetColor("darkgray");

        // Gray window shadow
        $img->Line(2, $h - 6, $w - 5, $h - 6);
        $img->Line(3, $h - 7, $w - 5, $h - 7);

        // Window title
        $m = floor($w / 2 - 5);
        $l = 110;
        $img->SetColor("lightgray:1.3");
        $img->FilledRectangle($m - $l, 2, $m + $l, 16);

        // Stroke text
        $img->SetColor("darkred");
        $img->SetFont(FF_FONT2, FS_BOLD);
        $img->StrokeText($m - 90, 15, $this->iTitle);
        $img->SetColor("black");
        $img->SetFont(FF_FONT1, FS_NORMAL);
        $txt = new Text\Text($aMsg, 52, 25);
        $txt->SetFont(FF_FONT1);
        $txt->Align("left", "top");
        $txt->Stroke($img);
        if ($this->iDest) {
            $img->Stream($this->iDest);
        } else {
            $img->Headers();
            $img->Stream();
        }
        if ($aHalt) {
            die();
        }

    }
}
