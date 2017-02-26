<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

// Constants for types of static bands in plot area
define("BAND_RDIAG", 1); // Right diagonal lines
define("BAND_LDIAG", 2); // Left diagonal lines
define("BAND_SOLID", 3); // Solid one color
define("BAND_VLINE", 4); // Vertical lines
define("BAND_HLINE", 5); // Horizontal lines
define("BAND_3DPLANE", 6); // "3D" Plane
define("BAND_HVCROSS", 7); // Vertical/Hor crosses
define("BAND_DIAGCROSS", 8); // Diagonal crosses

//=====================================================================
// Class RectPatternFactory
// Factory class for rectangular pattern
//=====================================================================
class RectPatternFactory
{
    public function __construct()
    {
        // Empty
    }

    public function Create($aPattern, $aColor, $aWeight = 1)
    {
        switch ($aPattern) {
            case BAND_RDIAG:
                $obj = new RectPatternRDiag($aColor, $aWeight);
                break;
            case BAND_LDIAG:
                $obj = new RectPatternLDiag($aColor, $aWeight);
                break;
            case BAND_SOLID:
                $obj = new RectPatternSolid($aColor, $aWeight);
                break;
            case BAND_VLINE:
                $obj = new RectPatternVert($aColor, $aWeight);
                break;
            case BAND_HLINE:
                $obj = new RectPatternHor($aColor, $aWeight);
                break;
            case BAND_3DPLANE:
                $obj = new RectPattern3DPlane($aColor, $aWeight);
                break;
            case BAND_HVCROSS:
                $obj = new RectPatternCross($aColor, $aWeight);
                break;
            case BAND_DIAGCROSS:
                $obj = new RectPatternDiagCross($aColor, $aWeight);
                break;
            default:
                Util\JpGraphError::RaiseL(16003, $aPattern);
                //(" Unknown pattern specification ($aPattern)");
        }
        return $obj;
    }
}
