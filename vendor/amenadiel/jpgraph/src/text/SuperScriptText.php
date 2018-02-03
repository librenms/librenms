<?php
namespace Amenadiel\JpGraph\Text;

//===================================================
// CLASS SuperScriptText
// Description: Format a superscript text
//===================================================
class SuperScriptText extends Text
{
    private $iSuper       = '';
    private $sfont_family = '';
    private $sfont_style  = '';
    private $sfont_size   = 8;
    private $iSuperMargin = 2;
    private $iVertOverlap = 4;
    private $iSuperScale  = 0.65;
    private $iSDir        = 0;
    private $iSimple      = false;

    public function __construct($aTxt = '', $aSuper = '', $aXAbsPos = 0, $aYAbsPos = 0)
    {
        parent::__construct($aTxt, $aXAbsPos, $aYAbsPos);
        $this->iSuper = $aSuper;
    }

    public function FromReal($aVal, $aPrecision = 2)
    {
        // Convert a floating point number to scientific notation
        $neg = 1.0;
        if ($aVal < 0) {
            $neg  = -1.0;
            $aVal = -$aVal;
        }

        $l = floor(log10($aVal));
        $a = sprintf("%0." . $aPrecision . "f", round($aVal / pow(10, $l), $aPrecision));
        $a *= $neg;
        if ($this->iSimple && ($a == 1 || $a == -1)) {
            $a = '';
        }

        if ($a != '') {
            $this->t = $a . ' * 10';
        } else {
            if ($neg == 1) {
                $this->t = '10';
            } else {
                $this->t = '-10';
            }
        }
        $this->iSuper = $l;
    }

    public function Set($aTxt, $aSuper = '')
    {
        $this->t      = $aTxt;
        $this->iSuper = $aSuper;
    }

    public function SetSuperFont($aFontFam, $aFontStyle = FS_NORMAL, $aFontSize = 8)
    {
        $this->sfont_family = $aFontFam;
        $this->sfont_style  = $aFontStyle;
        $this->sfont_size   = $aFontSize;
    }

    // Total width of text
    public function GetWidth($aImg)
    {
        $aImg->SetFont($this->font_family, $this->font_style, $this->font_size);
        $w = $aImg->GetTextWidth($this->t);
        $aImg->SetFont($this->sfont_family, $this->sfont_style, $this->sfont_size);
        $w += $aImg->GetTextWidth($this->iSuper);
        $w += $this->iSuperMargin;
        return $w;
    }

    // Hight of font (approximate the height of the text)
    public function GetFontHeight($aImg)
    {
        $aImg->SetFont($this->font_family, $this->font_style, $this->font_size);
        $h = $aImg->GetFontHeight();
        $aImg->SetFont($this->sfont_family, $this->sfont_style, $this->sfont_size);
        $h += $aImg->GetFontHeight();
        return $h;
    }

    // Hight of text
    public function GetTextHeight($aImg)
    {
        $aImg->SetFont($this->font_family, $this->font_style, $this->font_size);
        $h = $aImg->GetTextHeight($this->t);
        $aImg->SetFont($this->sfont_family, $this->sfont_style, $this->sfont_size);
        $h += $aImg->GetTextHeight($this->iSuper);
        return $h;
    }

    public function Stroke($aImg, $ax = -1, $ay = -1)
    {

        // To position the super script correctly we need different
        // cases to handle the alignmewnt specified since that will
        // determine how we can interpret the x,y coordinates

        $w = parent::GetWidth($aImg);
        $h = parent::GetTextHeight($aImg);
        switch ($this->valign) {
            case 'top':
                $sy = $this->y;
                break;
            case 'center':
                $sy = $this->y - $h / 2;
                break;
            case 'bottom':
                $sy = $this->y - $h;
                break;
            default:
                JpGraphError::RaiseL(25052); //('PANIC: Internal error in SuperScript::Stroke(). Unknown vertical alignment for text');
                break;
        }

        switch ($this->halign) {
            case 'left':
                $sx = $this->x + $w;
                break;
            case 'center':
                $sx = $this->x + $w / 2;
                break;
            case 'right':
                $sx = $this->x;
                break;
            default:
                JpGraphError::RaiseL(25053); //('PANIC: Internal error in SuperScript::Stroke(). Unknown horizontal alignment for text');
                break;
        }

        $sx += $this->iSuperMargin;
        $sy += $this->iVertOverlap;

        // Should we automatically determine the font or
        // has the user specified it explicetly?
        if ($this->sfont_family == '') {
            if ($this->font_family <= FF_FONT2) {
                if ($this->font_family == FF_FONT0) {
                    $sff = FF_FONT0;
                } elseif ($this->font_family == FF_FONT1) {
                    if ($this->font_style == FS_NORMAL) {
                        $sff = FF_FONT0;
                    } else {
                        $sff = FF_FONT1;
                    }
                } else {
                    $sff = FF_FONT1;
                }
                $sfs = $this->font_style;
                $sfz = $this->font_size;
            } else {
                // TTF fonts
                $sff = $this->font_family;
                $sfs = $this->font_style;
                $sfz = floor($this->font_size * $this->iSuperScale);
                if ($sfz < 8) {
                    $sfz = 8;
                }
            }
            $this->sfont_family = $sff;
            $this->sfont_style  = $sfs;
            $this->sfont_size   = $sfz;
        } else {
            $sff = $this->sfont_family;
            $sfs = $this->sfont_style;
            $sfz = $this->sfont_size;
        }

        parent::Stroke($aImg, $ax, $ay);

        // For the builtin fonts we need to reduce the margins
        // since the bounding bx reported for the builtin fonts
        // are much larger than for the TTF fonts.
        if ($sff <= FF_FONT2) {
            $sx -= 2;
            $sy += 3;
        }

        $aImg->SetTextAlign('left', 'bottom');
        $aImg->SetFont($sff, $sfs, $sfz);
        $aImg->PushColor($this->color);
        $aImg->StrokeText($sx, $sy, $this->iSuper, $this->iSDir, 'left');
        $aImg->PopColor();
    }
}
