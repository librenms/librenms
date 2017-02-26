<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS WindrosePlotScale
//===================================================
class WindrosePlotScale
{
    private $iMax, $iDelta = 5;
    private $iNumCirc = 3;
    public $iMaxNum = 0;
    private $iLblFmt = '%.0f%%';
    public $iFontFamily = FF_VERDANA, $iFontStyle = FS_NORMAL, $iFontSize = 10;
    public $iZFontFamily = FF_ARIAL, $iZFontStyle = FS_NORMAL, $iZFontSize = 10;
    public $iFontColor = 'black', $iZFontColor = 'black';
    private $iFontFrameColor = false, $iFontBkgColor = false;
    private $iLblZeroTxt = null;
    private $iLblAlign = LBLALIGN_CENTER;
    public $iAngle = 'auto';
    private $iManualScale = false;
    private $iHideLabels = false;

    public function __construct($aData)
    {
        $max = 0;
        $totlegsum = 0;
        $maxnum = 0;
        $this->iZeroSum = 0;
        foreach ($aData as $idx => $legdata) {
            $legsum = array_sum($legdata);
            $maxnum = max($maxnum, count($legdata) - 1);
            $max = max($legsum - $legdata[0], $max);
            $totlegsum += $legsum;
            $this->iZeroSum += $legdata[0];
        }
        if (round($totlegsum) > 100) {
            Util\JpGraphError::RaiseL(22001, $legsum);
            //("Total percentage for all windrose legs in a windrose plot can not exceed  100% !\n(Current max is: ".$legsum.')');
        }
        $this->iMax = $max;
        $this->iMaxNum = $maxnum;
        $this->iNumCirc = $this->GetNumCirc();
        $this->iMaxVal = $this->iNumCirc * $this->iDelta;
    }

    // Return number of grid circles
    public function GetNumCirc()
    {
        // Never return less than 1 circles
        $num = ceil($this->iMax / $this->iDelta);
        return max(1, $num);
    }

    public function SetMaxValue($aMax)
    {
        $this->iMax = $aMax;
        $this->iNumCirc = $this->GetNumCirc();
        $this->iMaxVal = $this->iNumCirc * $this->iDelta;
    }

    // Set step size for circular grid
    public function Set($aMax, $aDelta = null)
    {
        if ($aDelta == null) {
            $this->SetMaxValue($aMax);
            return;
        }
        $this->iDelta = $aDelta;
        $this->iNumCirc = ceil($aMax / $aDelta); //$this->GetNumCirc();
        $this->iMaxVal = $this->iNumCirc * $this->iDelta;
        $this->iMax = $aMax;
        // Remember that user has specified interval so don't
        // do autoscaling
        $this->iManualScale = true;
    }

    public function AutoScale($aRadius, $aMinDist = 30)
    {

        if ($this->iManualScale) {
            return;
        }

        // Make sure distance (in pixels) between two circles
        // is never less than $aMinDist pixels
        $tst = ceil($aRadius / $this->iNumCirc);

        while ($tst <= $aMinDist && $this->iDelta < 100) {
            $this->iDelta += 5;
            $tst = ceil($aRadius / $this->GetNumCirc());
        }

        if ($this->iDelta >= 100) {
            Util\JpGraphError::RaiseL(22002); //('Graph is too small to have a scale. Please make the graph larger.');
        }

        // If the distance is to large try with multiples of 2 instead
        if ($tst > $aMinDist * 3) {
            $this->iDelta = 2;
            $tst = ceil($aRadius / $this->iNumCirc);

            while ($tst <= $aMinDist && $this->iDelta < 100) {
                $this->iDelta += 2;
                $tst = ceil($aRadius / $this->GetNumCirc());
            }

            if ($this->iDelta >= 100) {
                Util\JpGraphError::RaiseL(22002); //('Graph is too small to have a scale. Please make the graph larger.');
            }
        }

        $this->iNumCirc = $this->GetNumCirc();
        $this->iMaxVal = $this->iNumCirc * $this->iDelta;
    }

    // Return max of all leg values
    public function GetMax()
    {
        return $this->iMax;
    }

    public function Hide($aFlg = true)
    {
        $this->iHideLabels = $aFlg;
    }

    public function SetAngle($aAngle)
    {
        $this->iAngle = $aAngle;
    }

    // Translate a Leg value to radius distance
    public function RelTranslate($aVal, $r, $ri)
    {
        $tv = round($aVal / $this->iMaxVal * ($r - $ri));
        return $tv;
    }

    public function SetLabelAlign($aAlign)
    {
        $this->iLblAlign = $aAlign;
    }

    public function SetLabelFormat($aFmt)
    {
        $this->iLblFmt = $aFmt;
    }

    public function SetLabelFillColor($aBkgColor, $aBorderColor = false)
    {

        $this->iFontBkgColor = $aBkgColor;
        if ($aBorderColor === false) {
            $this->iFontFrameColor = $aBkgColor;
        } else {
            $this->iFontFrameColor = $aBorderColor;
        }
    }

    public function SetFontColor($aColor)
    {
        $this->iFontColor = $aColor;
        $this->iZFontColor = $aColor;
    }

    public function SetFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iFontFamily = $aFontFamily;
        $this->iFontStyle = $aFontStyle;
        $this->iFontSize = $aFontSize;
        $this->SetZFont($aFontFamily, $aFontStyle, $aFontSize);
    }

    public function SetZFont($aFontFamily, $aFontStyle = FS_NORMAL, $aFontSize = 10)
    {
        $this->iZFontFamily = $aFontFamily;
        $this->iZFontStyle = $aFontStyle;
        $this->iZFontSize = $aFontSize;
    }

    public function SetZeroLabel($aTxt)
    {
        $this->iLblZeroTxt = $aTxt;
    }

    public function SetZFontColor($aColor)
    {
        $this->iZFontColor = $aColor;
    }

    public function StrokeLabels($aImg, $xc, $yc, $ri, $rr)
    {

        if ($this->iHideLabels) {
            return;
        }

        // Setup some convinient vairables
        $a = $this->iAngle * M_PI / 180.0;
        $n = $this->iNumCirc;
        $d = $this->iDelta;

        // Setup the font and font color
        $val = new Text();
        $val->SetFont($this->iFontFamily, $this->iFontStyle, $this->iFontSize);
        $val->SetColor($this->iFontColor);

        if ($this->iFontBkgColor !== false) {
            $val->SetBox($this->iFontBkgColor, $this->iFontFrameColor);
        }

        // Position the labels relative to the radiant circles
        if ($this->iLblAlign == LBLALIGN_TOP) {
            if ($a > 0 && $a <= M_PI / 2) {
                $val->SetAlign('left', 'bottom');
            } elseif ($a > M_PI / 2 && $a <= M_PI) {
                $val->SetAlign('right', 'bottom');
            }
        } elseif ($this->iLblAlign == LBLALIGN_CENTER) {
            $val->SetAlign('center', 'center');
        }

        // Stroke the labels close to each circle
        $v = $d;
        $si = sin($a);
        $co = cos($a);
        for ($i = 0; $i < $n; ++$i, $v += $d) {
            $r = $ri + ($i + 1) * $rr;
            $x = $xc + $co * $r;
            $y = $yc - $si * $r;
            $val->Set(sprintf($this->iLblFmt, $v));
            $val->Stroke($aImg, $x, $y);
        }

        // Print the text in the zero circle
        if ($this->iLblZeroTxt === null) {
            $this->iLblZeroTxt = sprintf($this->iLblFmt, $this->iZeroSum);
        } else {
            $this->iLblZeroTxt = sprintf($this->iLblZeroTxt, $this->iZeroSum);
        }

        $val->Set($this->iLblZeroTxt);
        $val->SetAlign('center', 'center');
        $val->SetParagraphAlign('center');
        $val->SetColor($this->iZFontColor);
        $val->SetFont($this->iZFontFamily, $this->iZFontStyle, $this->iZFontSize);
        $val->Stroke($aImg, $xc, $yc);
    }
}
