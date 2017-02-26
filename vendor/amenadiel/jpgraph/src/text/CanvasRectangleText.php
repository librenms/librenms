<?php
namespace Amenadiel\JpGraph\Text;

//===================================================
// CLASS RectangleText
// Description: Draws a text paragraph inside a
// rounded, possible filled, rectangle.
//===================================================
class CanvasRectangleText
{
    private $ix, $iy, $iw, $ih, $ir = 4;
    private $iTxt, $iColor = 'black', $iFillColor = '', $iFontColor = 'black';
    private $iParaAlign = 'center';
    private $iAutoBoxMargin = 5;
    private $iShadowWidth = 3, $iShadowColor = '';

    public function __construct($aTxt = '', $xl = 0, $yt = 0, $w = 0, $h = 0)
    {
        $this->iTxt = new Text($aTxt);
        $this->ix = $xl;
        $this->iy = $yt;
        $this->iw = $w;
        $this->ih = $h;
    }

    public function SetShadow($aColor = 'gray', $aWidth = 3)
    {
        $this->iShadowColor = $aColor;
        $this->iShadowWidth = $aWidth;
    }

    public function SetFont($FontFam, $aFontStyle, $aFontSize = 12)
    {
        $this->iTxt->SetFont($FontFam, $aFontStyle, $aFontSize);
    }

    public function SetTxt($aTxt)
    {
        $this->iTxt->Set($aTxt);
    }

    public function ParagraphAlign($aParaAlign)
    {
        $this->iParaAlign = $aParaAlign;
    }

    public function SetFillColor($aFillColor)
    {
        $this->iFillColor = $aFillColor;
    }

    public function SetAutoMargin($aMargin)
    {
        $this->iAutoBoxMargin = $aMargin;
    }

    public function SetColor($aColor)
    {
        $this->iColor = $aColor;
    }

    public function SetFontColor($aColor)
    {
        $this->iFontColor = $aColor;
    }

    public function SetPos($xl = 0, $yt = 0, $w = 0, $h = 0)
    {
        $this->ix = $xl;
        $this->iy = $yt;
        $this->iw = $w;
        $this->ih = $h;
    }

    public function Pos($xl = 0, $yt = 0, $w = 0, $h = 0)
    {
        $this->ix = $xl;
        $this->iy = $yt;
        $this->iw = $w;
        $this->ih = $h;
    }

    public function Set($aTxt, $xl, $yt, $w = 0, $h = 0)
    {
        $this->iTxt->Set($aTxt);
        $this->ix = $xl;
        $this->iy = $yt;
        $this->iw = $w;
        $this->ih = $h;
    }

    public function SetCornerRadius($aRad = 5)
    {
        $this->ir = $aRad;
    }

    public function Stroke($aImg, $scale)
    {

        // If coordinates are specifed as negative this means we should
        // treat them as abolsute (pixels) coordinates
        if ($this->ix > 0) {
            $this->ix = $scale->TranslateX($this->ix);
        } else {
            $this->ix = -$this->ix;
        }

        if ($this->iy > 0) {
            $this->iy = $scale->TranslateY($this->iy);
        } else {
            $this->iy = -$this->iy;
        }

        list($this->iw, $this->ih) = $scale->Translate($this->iw, $this->ih);

        if ($this->iw == 0) {
            $this->iw = round($this->iTxt->GetWidth($aImg) + $this->iAutoBoxMargin);
        }

        if ($this->ih == 0) {
            $this->ih = round($this->iTxt->GetTextHeight($aImg) + $this->iAutoBoxMargin);
        }

        if ($this->iShadowColor != '') {
            $aImg->PushColor($this->iShadowColor);
            $aImg->FilledRoundedRectangle($this->ix + $this->iShadowWidth,
                $this->iy + $this->iShadowWidth,
                $this->ix + $this->iw - 1 + $this->iShadowWidth,
                $this->iy + $this->ih - 1 + $this->iShadowWidth,
                $this->ir);
            $aImg->PopColor();
        }

        if ($this->iFillColor != '') {
            $aImg->PushColor($this->iFillColor);
            $aImg->FilledRoundedRectangle($this->ix, $this->iy,
                $this->ix + $this->iw - 1,
                $this->iy + $this->ih - 1,
                $this->ir);
            $aImg->PopColor();
        }

        if ($this->iColor != '') {
            $aImg->PushColor($this->iColor);
            $aImg->RoundedRectangle($this->ix, $this->iy,
                $this->ix + $this->iw - 1,
                $this->iy + $this->ih - 1,
                $this->ir);
            $aImg->PopColor();
        }

        $this->iTxt->Align('center', 'center');
        $this->iTxt->ParagraphAlign($this->iParaAlign);
        $this->iTxt->SetColor($this->iFontColor);
        $this->iTxt->Stroke($aImg, $this->ix + $this->iw / 2, $this->iy + $this->ih / 2);

        return array($this->iw, $this->ih);

    }

}
