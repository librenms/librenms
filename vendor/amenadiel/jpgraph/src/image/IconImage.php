<?php
namespace Amenadiel\JpGraph\Image;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS IconImage
// Description: Holds properties for an icon image
//===================================================
class IconImage
{
    private $iGDImage = null;
    private $iWidth;
    private $iHeight;
    private $ixalign = 'left';
    private $iyalign = 'center';
    private $iScale  = 1.0;

    public function __construct($aIcon, $aScale = 1)
    {
        global $_gPredefIcons;
        if (is_string($aIcon)) {
            $this->iGDImage = Graph::LoadBkgImage('', $aIcon);
        } elseif (is_integer($aIcon)) {
            // Builtin image
            $this->iGDImage = $_gPredefIcons->GetImg($aIcon);
        } else {
            Util\JpGraphError::RaiseL(6011);
            //('Argument to IconImage must be string or integer');
        }
        $this->iScale  = $aScale;
        $this->iWidth  = Image::GetWidth($this->iGDImage);
        $this->iHeight = Image::GetHeight($this->iGDImage);
    }

    public function GetWidth()
    {
        return round($this->iScale * $this->iWidth);
    }

    public function GetHeight()
    {
        return round($this->iScale * $this->iHeight);
    }

    public function SetAlign($aX = 'left', $aY = 'center')
    {
        $this->ixalign = $aX;
        $this->iyalign = $aY;
    }

    public function Stroke($aImg, $x, $y)
    {
        if ($this->ixalign == 'right') {
            $x -= $this->iWidth;
        } elseif ($this->ixalign == 'center') {
            $x -= round($this->iWidth / 2 * $this->iScale);
        }

        if ($this->iyalign == 'bottom') {
            $y -= $this->iHeight;
        } elseif ($this->iyalign == 'center') {
            $y -= round($this->iHeight / 2 * $this->iScale);
        }

        $aImg->Copy($this->iGDImage,
            $x, $y, 0, 0,
            round($this->iWidth * $this->iScale), round($this->iHeight * $this->iScale),
            $this->iWidth, $this->iHeight);
    }
}
