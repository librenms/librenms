<?php
namespace Amenadiel\JpGraph\Plot;

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Text;

define('WINDROSE_TYPE4', 1);
define('WINDROSE_TYPE8', 2);
define('WINDROSE_TYPE16', 3);
define('WINDROSE_TYPEFREE', 4);

//------------------------------------------------------------------------
// How should the labels for the circular grids be aligned
//------------------------------------------------------------------------
define('LBLALIGN_CENTER', 1);
define('LBLALIGN_TOP', 2);

//------------------------------------------------------------------------
// How should the labels around the plot be align
//------------------------------------------------------------------------
define('LBLPOSITION_CENTER', 1);
define('LBLPOSITION_EDGE', 2);

//------------------------------------------------------------------------
// Interpretation of ordinal values in the data
//------------------------------------------------------------------------
define('KEYENCODING_CLOCKWISE', 1);
define('KEYENCODING_ANTICLOCKWISE', 2);

// Internal debug flag
define('__DEBUG', false);
define('RANGE_OVERLAPPING', 0);
define('RANGE_DISCRETE', 1);

//===================================================
// CLASS WindrosePlot
//===================================================
class WindrosePlot
{
    private $iAntiAlias = true;
    private $iData = [];
    public $iX = 0.5, $iY = 0.5;
    public $iSize = 0.55;
    private $iGridColor1 = 'gray', $iGridColor2 = 'darkgreen';
    private $iRadialColorArray = [];
    private $iRadialWeightArray = [];
    private $iRadialStyleArray = [];
    private $iRanges = [1, 2, 3, 5, 6, 10, 13.5, 99.0];
    private $iRangeStyle = RANGE_OVERLAPPING;
    public $iCenterSize = 60;
    private $iType = WINDROSE_TYPE16;
    public $iFontFamily = FF_VERDANA, $iFontStyle = FS_NORMAL, $iFontSize = 10;
    public $iFontColor = 'darkgray';
    private $iRadialGridStyle = 'longdashed';
    private $iAllDirectionLabels = ['E', 'ENE', 'NE', 'NNE', 'N', 'NNW', 'NW', 'WNW', 'W', 'WSW', 'SW', 'SSW', 'S', 'SSE', 'SE', 'ESE'];
    private $iStandardDirections = [];
    private $iCircGridWeight = 3, $iRadialGridWeight = 1;
    private $iLabelMargin = 12;
    private $iLegweights = [2, 4, 6, 8, 10, 12, 14, 16, 18, 20];
    private $iLegColors = ['orange', 'black', 'blue', 'red', 'green', 'purple', 'navy', 'yellow', 'brown'];
    private $iLabelFormatString = '', $iLabels = [];
    private $iLabelPositioning = LBLPOSITION_EDGE;
    private $iColor = 'white';
    private $iShowBox = false, $iBoxColor = 'black', $iBoxWeight = 1, $iBoxStyle = 'solid';
    private $iOrdinalEncoding = KEYENCODING_ANTICLOCKWISE;
    public $legend = null;

    public function __construct($aData)
    {
        $this->iData = $aData;
        $this->legend = new LegendStyle();

        // Setup the scale
        $this->scale = new Graph\WindrosePlotScale($this->iData);

        // default label for free type i agle and a degree sign
        $this->iLabelFormatString = '%.1f' . Graph\SymChar::Get('degree');

        $delta = 2 * M_PI / 16;
        for ($i = 0, $a = 0; $i < 16; ++$i, $a += $delta) {
            $this->iStandardDirections[$this->iAllDirectionLabels[$i]] = $a;
        }
    }

    // Dummy method to make window plots have the same signature as the
    // layout classes since windrose plots are "leaf" classes in the hierarchy
    public function LayoutSize()
    {
        return 1;
    }

    public function SetSize($aSize)
    {
        $this->iSize = $aSize;
    }

    public function SetDataKeyEncoding($aEncoding)
    {
        $this->iOrdinalEncoding = $aEncoding;
    }

    public function SetColor($aColor)
    {
        $this->iColor = $aColor;
    }

    public function SetRadialColors($aColors)
    {
        $this->iRadialColorArray = $aColors;
    }

    public function SetRadialWeights($aWeights)
    {
        $this->iRadialWeightArray = $aWeights;
    }

    public function SetRadialStyles($aStyles)
    {
        $this->iRadialStyleArray = $aStyles;
    }

    public function SetBox($aColor = 'black', $aWeight = 1, $aStyle = 'solid', $aShow = true)
    {
        $this->iShowBox = $aShow;
        $this->iBoxColor = $aColor;
        $this->iBoxWeight = $aWeight;
        $this->iBoxStyle = $aStyle;
    }

    public function SetLabels($aLabels)
    {
        $this->iLabels = $aLabels;
    }

    public function SetLabelMargin($aMarg)
    {
        $this->iLabelMargin = $aMarg;
    }

    public function SetLabelFormat($aLblFormat)
    {
        $this->iLabelFormatString = $aLblFormat;
    }

    public function SetCompassLabels($aLabels)
    {
        if (count($aLabels) != 16) {
            Util\JpGraphError::RaiseL(22004); //('Label specification for windrose directions must have 16 values (one for each compass direction).');
        }
        $this->iAllDirectionLabels = $aLabels;

        $delta = 2 * M_PI / 16;
        for ($i = 0, $a = 0; $i < 16; ++$i, $a += $delta) {
            $this->iStandardDirections[$this->iAllDirectionLabels[$i]] = $a;
        }

    }

    public function SetCenterSize($aSize)
    {
        $this->iCenterSize = $aSize;
    }

    // Alias for SetCenterSize
    public function SetZCircleSize($aSize)
    {
        $this->iCenterSize = $aSize;
    }

    public function SetFont($aFFam, $aFStyle = FS_NORMAL, $aFSize = 10)
    {
        $this->iFontFamily = $aFFam;
        $this->iFontStyle = $aFStyle;
        $this->iFontSize = $aFSize;
    }

    public function SetFontColor($aColor)
    {
        $this->iFontColor = $aColor;
    }

    public function SetGridColor($aColor1, $aColor2)
    {
        $this->iGridColor1 = $aColor1;
        $this->iGridColor2 = $aColor2;
    }

    public function SetGridWeight($aGrid1 = 1, $aGrid2 = 2)
    {
        $this->iCircGridWeight = $aGrid1;
        $this->iRadialGridWeight = $aGrid2;
    }

    public function SetRadialGridStyle($aStyle)
    {
        $aStyle = strtolower($aStyle);
        if (!in_array($aStyle, ['solid', 'dotted', 'dashed', 'longdashed'])) {
            Util\JpGraphError::RaiseL(22005); //("Line style for radial lines must be on of ('solid','dotted','dashed','longdashed') ");
        }
        $this->iRadialGridStyle = $aStyle;
    }

    public function SetRanges($aRanges)
    {
        $this->iRanges = $aRanges;
    }

    public function SetRangeStyle($aStyle)
    {
        $this->iRangeStyle = $aStyle;
    }

    public function SetRangeColors($aLegColors)
    {
        $this->iLegColors = $aLegColors;
    }

    public function SetRangeWeights($aWeights)
    {
        $n = count($aWeights);
        for ($i = 0; $i < $n; ++$i) {
            $aWeights[$i] = floor($aWeights[$i] / 2);
        }
        $this->iLegweights = $aWeights;

    }

    public function SetType($aType)
    {
        if ($aType < WINDROSE_TYPE4 || $aType > WINDROSE_TYPEFREE) {
            Util\JpGraphError::RaiseL(22006); //('Illegal windrose type specified.');
        }
        $this->iType = $aType;
    }

    // Alias for SetPos()
    public function SetCenterPos($aX, $aY)
    {
        $this->iX = $aX;
        $this->iY = $aY;
    }

    public function SetPos($aX, $aY)
    {
        $this->iX = $aX;
        $this->iY = $aY;
    }

    public function SetAntiAlias($aFlag)
    {
        $this->iAntiAlias = $aFlag;
        if (!$aFlag) {
            $this->iCircGridWeight = 1;
        }

    }

    public function _ThickCircle($aImg, $aXC, $aYC, $aRad, $aWeight = 2, $aColor)
    {

        $aImg->SetColor($aColor);
        $aRad *= 2;
        $aImg->Ellipse($aXC, $aYC, $aRad, $aRad);
        if ($aWeight > 1) {
            $aImg->Ellipse($aXC, $aYC, $aRad + 1, $aRad + 1);
            $aImg->Ellipse($aXC, $aYC, $aRad + 2, $aRad + 2);
            if ($aWeight > 2) {
                $aImg->Ellipse($aXC, $aYC, $aRad + 3, $aRad + 3);
                $aImg->Ellipse($aXC, $aYC, $aRad + 3, $aRad + 4);
                $aImg->Ellipse($aXC, $aYC, $aRad + 4, $aRad + 3);
            }
        }
    }

    public function _StrokeWindLeg($aImg, $xc, $yc, $a, $ri, $r, $weight, $color)
    {

        // If less than 1 px long then we assume this has been caused by rounding problems
        // and should not be stroked
        if ($r < 1) {
            return;
        }

        $xt = $xc + cos($a) * $ri;
        $yt = $yc - sin($a) * $ri;
        $xxt = $xc + cos($a) * ($ri + $r);
        $yyt = $yc - sin($a) * ($ri + $r);

        $x1 = $xt - $weight * sin($a);
        $y1 = $yt - $weight * cos($a);
        $x2 = $xxt - $weight * sin($a);
        $y2 = $yyt - $weight * cos($a);

        $x3 = $xxt + $weight * sin($a);
        $y3 = $yyt + $weight * cos($a);
        $x4 = $xt + $weight * sin($a);
        $y4 = $yt + $weight * cos($a);

        $pts = [$x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4];
        $aImg->SetColor($color);
        $aImg->FilledPolygon($pts);

    }

    public function _StrokeLegend($aImg, $x, $y, $scaling = 1, $aReturnWidth = false)
    {

        if (!$this->legend->iShow) {
            return 0;
        }

        $nlc = count($this->iLegColors);
        $nlw = count($this->iLegweights);

        // Setup font for ranges
        $value = new Text\Text();
        $value->SetAlign('center', 'bottom');
        $value->SetFont($this->legend->iLblFontFamily,
            $this->legend->iLblFontStyle,
            $this->legend->iLblFontSize * $scaling);
        $value->SetColor($this->legend->iLblFontColor);

        // Remember x-center
        $xcenter = $x;

        // Construct format string
        $fmt = $this->legend->iFormatString . '-' . $this->legend->iFormatString;

        // Make sure that the length of each range is enough to cover the
        // size of the labels
        $tst = sprintf($fmt, $this->iRanges[0], $this->iRanges[1]);
        $value->Set($tst);
        $w = $value->GetWidth($aImg);
        $l = round(max($this->legend->iLength * $scaling, $w * 1.5));

        $r = $this->legend->iCircleRadius * $scaling;
        $len = 2 * $r + $this->scale->iMaxNum * $l;

        // We are called just to find out the width
        if ($aReturnWidth) {
            return $len;
        }

        $x -= round($len / 2);
        $x += $r;

        // 4 pixels extra vertical margin since the circle sometimes is +/- 1 pixel of the
        // theorethical radius due to imperfection in the GD library
        //$y -= round(max($r,$scaling*$this->iLegweights[($this->scale->iMaxNum-1) % $nlw])+4*$scaling);
        $y -= ($this->legend->iCircleRadius + 2) * $scaling + $this->legend->iBottomMargin * $scaling;

        // Adjust for bottom text
        if ($this->legend->iTxt != '') {
            // Setup font for text
            $value->Set($this->legend->iTxt);
            $y -= /*$this->legend->iTxtMargin + */$value->GetHeight($aImg);
        }

        // Stroke 0-circle
        $this->_ThickCircle($aImg, $x, $y, $r, $this->legend->iCircleWeight,
            $this->legend->iCircleColor);

        // Remember the center of the circe
        $xc = $x;
        $yc = $y;

        $value->SetAlign('center', 'bottom');
        $x += $r + 1;

        // Stroke all used ranges
        $txty = $y -
        round($this->iLegweights[($this->scale->iMaxNum - 1) % $nlw] * $scaling) - 4 * $scaling;
        if ($this->scale->iMaxNum >= count($this->iRanges)) {
            Util\JpGraphError::RaiseL(22007); //('To few values for the range legend.');
        }
        $i = 0;
        $idx = 0;
        while ($i < $this->scale->iMaxNum) {
            $y1 = $y - round($this->iLegweights[$i % $nlw] * $scaling);
            $y2 = $y + round($this->iLegweights[$i % $nlw] * $scaling);
            $x2 = $x + $l;
            $aImg->SetColor($this->iLegColors[$i % $nlc]);
            $aImg->FilledRectangle($x, $y1, $x2, $y2);
            if ($this->iRangeStyle == RANGE_OVERLAPPING) {
                $lbl = sprintf($fmt, $this->iRanges[$idx], $this->iRanges[$idx + 1]);
            } else {
                $lbl = sprintf($fmt, $this->iRanges[$idx], $this->iRanges[$idx + 1]);
                ++$idx;
            }
            $value->Set($lbl);
            $value->Stroke($aImg, $x + $l / 2, $txty);
            $x = $x2;
            ++$i; ++$idx;
        }

        // Setup circle font
        $value->SetFont($this->legend->iCircleFontFamily,
            $this->legend->iCircleFontStyle,
            $this->legend->iCircleFontSize * $scaling);
        $value->SetColor($this->legend->iCircleFontColor);

        // Stroke 0-circle text
        $value->Set($this->legend->iZCircleTxt);
        $value->SetAlign('center', 'center');
        $value->ParagraphAlign('center');
        $value->Stroke($aImg, $xc, $yc);

        // Setup circle font
        $value->SetFont($this->legend->iTxtFontFamily,
            $this->legend->iTxtFontStyle,
            $this->legend->iTxtFontSize * $scaling);
        $value->SetColor($this->legend->iTxtFontColor);

        // Draw the text under the legend
        $value->Set($this->legend->iTxt);
        $value->SetAlign('center', 'top');
        $value->SetParagraphAlign('center');
        $value->Stroke($aImg, $xcenter, $y2 + $this->legend->iTxtMargin * $scaling);
    }

    public function SetAutoScaleAngle($aIsRegRose = true)
    {

        // If the user already has manually set an angle don't
        // trye to find a position
        if (is_numeric($this->scale->iAngle)) {
            return;
        }

        if ($aIsRegRose) {

            // Create a complete data for all directions
            // and translate string directions to ordinal values.
            // This will much simplify the logic below
            for ($i = 0; $i < 16; ++$i) {
                $dtxt = $this->iAllDirectionLabels[$i];
                if (!empty($this->iData[$dtxt])) {
                    $data[$i] = $this->iData[$dtxt];
                } elseif (!empty($this->iData[strtolower($dtxt)])) {
                    $data[$i] = $this->iData[strtolower($dtxt)];
                } elseif (!empty($this->iData[$i])) {
                    $data[$i] = $this->iData[$i];
                } else {
                    $data[$i] = [];
                }
            }

            // Find the leg which has the lowest weighted sum of number of data around it
            $c0 = array_sum($data[0]);
            $c1 = array_sum($data[1]);
            $found = 1;
            $min = $c0 + $c1 * 100; // Initialize to a high value
            for ($i = 1; $i < 15; ++$i) {
                $c2 = array_sum($data[$i + 1]);

                // Weight the leg we will use more to give preference
                // to a short middle leg even if the 3 way sum is similair
                $w = $c0 + 3 * $c1 + $c2;
                if ($w < $min) {
                    $min = $w;
                    $found = $i;
                }
                $c0 = $c1;
                $c1 = $c2;
            }
            $this->scale->iAngle = $found * 22.5;
        } else {
            $n = count($this->iData);
            foreach ($this->iData as $dir => $leg) {
                if (!is_numeric($dir)) {
                    $pos = array_search(strtoupper($dir), $this->iAllDirectionLabels);
                    if ($pos !== false) {
                        $dir = $pos * 22.5;
                    }
                }
                $data[round($dir)] = $leg;
            }

            // Get all the angles for the data and sort it
            $keys = array_keys($data);
            sort($keys, SORT_NUMERIC);

            $n = count($data);
            $found = false;
            $max = 0;
            for ($i = 0; $i < 15; ++$i) {
                $try_a = round(22.5 * $i);

                if ($try_a > $keys[$n - 1]) {
                    break;
                }

                if (in_array($try_a, $keys)) {
                    continue;
                }

                // Find the angle just lower than this
                $j = 0;
                while ($j < $n && $keys[$j] <= $try_a) {
                    ++$j;
                }

                if ($j == 0) {
                    $kj = 0;
                    $keys[$n - 1];
                    $d1 = 0;
                    abs($kj - $try_a);
                } else {
                    --$j;
                    $kj = $keys[$j];
                    $d1 = abs($kj - $try_a);
                }

                // Find the angle just larger than this
                $l = $n - 1;
                while ($l >= 0 && $keys[$l] >= $try_a) {
                    --$l;
                }

                if ($l == $n - 1) {
                    $kl = $keys[0];
                    $d2 = abs($kl - $try_a);
                } else {
                    ++$l;
                    $kl = $keys[$l];
                    $d2 = abs($kl - $try_a);
                }

                // Weight the distance so that legs with large spread
                // gets a better weight
                $w = $d1 + $d2;
                if ($i == 0) {
                    $w = round(1.4 * $w);
                }
                $diff = abs($d1 - $d2);
                $w *= (360 - $diff);
                if ($w > $max) {
                    $found = $i;
                    $max = $w;
                }
            }

            $a = $found * 22.5;

            // Some heuristics to have some preferred positions
            if ($keys[$n - 1] < 25) {
                $a = 45;
            } elseif ($keys[0] > 60) {
                $a = 45;
            } elseif ($keys[0] > 25 && $keys[$n - 1] < 340) {
                $a = 0;
            } elseif ($keys[$n - 1] < 75) {
                $a = 90;
            } elseif ($keys[$n - 1] < 120) {
                $a = 135;
            } elseif ($keys[$n - 1] < 160) {
                $a = 180;
            }

            $this->scale->iAngle = $a;
        }
    }

    public function NormAngle($a)
    {
        while ($a > 360) {
            $a -= 360;
        }
        return $a;
    }

    public function SetLabelPosition($aPos)
    {
        $this->iLabelPositioning = $aPos;
    }

    public function _StrokeFreeRose($dblImg, $value, $scaling, $xc, $yc, $r, $ri)
    {

        // Plot radial grid lines and remember the end position
        // and the angle for later use when plotting the labels
        if ($this->iType != WINDROSE_TYPEFREE) {
            Util\JpGraphError::RaiseL(22008); //('Internal error: Trying to plot free Windrose even though type is not a free windorose');
        }

        // Check if we should auto-position the angle for the
        // labels. Basically we try to find a firection with smallest
        // (or none) data.
        $this->SetAutoScaleAngle(false);

        $nlc = count($this->iLegColors);
        $nlw = count($this->iLegweights);

        // Stroke grid lines for directions and remember the
        // position for the labels
        $txtpos = [];
        $num = count($this->iData);

        $keys = array_keys($this->iData);

        foreach ($this->iData as $dir => $legdata) {
            if (in_array($dir, $this->iAllDirectionLabels, true) === true) {
                $a = $this->iStandardDirections[strtoupper($dir)];
                if (in_array($a * 180 / M_PI, $keys)) {
                    Util\JpGraphError::RaiseL(22009, round($a * 180 / M_PI));
                    //('You have specified the same direction twice, once with an angle and once with a compass direction ('.$a*180/M_PI.' degrees.)');
                }
            } elseif (is_numeric($dir)) {
                $this->NormAngle($dir);

                if ($this->iOrdinalEncoding == KEYENCODING_CLOCKWISE) {
                    $dir = 360 - $dir;
                }

                $a = $dir * M_PI / 180;
            } else {
                Util\JpGraphError::RaiseL(22010); //('Direction must either be a numeric value or one of the 16 compass directions');
            }

            $xxc = round($xc + cos($a) * $ri);
            $yyc = round($yc - sin($a) * $ri);
            $x = round($xc + cos($a) * $r);
            $y = round($yc - sin($a) * $r);
            if (empty($this->iRadialColorArray[$dir])) {
                $dblImg->SetColor($this->iGridColor2);
            } else {
                $dblImg->SetColor($this->iRadialColorArray[$dir]);
            }
            if (empty($this->iRadialWeightArray[$dir])) {
                $dblImg->SetLineWeight($this->iRadialGridWeight);
            } else {
                $dblImg->SetLineWeight($this->iRadialWeightArray[$dir]);
            }
            if (empty($this->iRadialStyleArray[$dir])) {
                $dblImg->SetLineStyle($this->iRadialGridStyle);
            } else {
                $dblImg->SetLineStyle($this->iRadialStyleArray[$dir]);
            }
            $dblImg->StyleLine($xxc, $yyc, $x, $y);
            $txtpos[] = [$x, $y, $a];
        }
        $dblImg->SetLineWeight(1);

        // Setup labels
        $lr = $scaling * $this->iLabelMargin;

        if ($this->iLabelPositioning == LBLPOSITION_EDGE) {
            $value->SetAlign('left', 'top');
        } else {
            $value->SetAlign('center', 'center');
            $value->SetMargin(0);
        }

        for ($i = 0; $i < $num; ++$i) {

            list($x, $y, $a) = $txtpos[$i];

            // Determine the label

            $da = $a * 180 / M_PI;
            if ($this->iOrdinalEncoding == KEYENCODING_CLOCKWISE) {
                $da = 360 - $da;
            }

            //$da = 360-$da;

            if (!empty($this->iLabels[$keys[$i]])) {
                $lbl = $this->iLabels[$keys[$i]];
            } else {
                $lbl = sprintf($this->iLabelFormatString, $da);
            }

            if ($this->iLabelPositioning == LBLPOSITION_CENTER) {
                $dx = $dy = 0;
            } else {
                // LBLPOSIITON_EDGE
                if ($a >= 7 * M_PI / 4 || $a <= M_PI / 4) {
                    $dx = 0;
                }

                if ($a >= M_PI / 4 && $a <= 3 * M_PI / 4) {
                    $dx = ($a - M_PI / 4) * 2 / M_PI;
                }

                if ($a >= 3 * M_PI / 4 && $a <= 5 * M_PI / 4) {
                    $dx = 1;
                }

                if ($a >= 5 * M_PI / 4 && $a <= 7 * M_PI / 4) {
                    $dx = (1 - ($a - M_PI * 5 / 4) * 2 / M_PI);
                }

                if ($a >= 7 * M_PI / 4) {
                    $dy = (($a - M_PI) - 3 * M_PI / 4) * 2 / M_PI;
                }

                if ($a <= M_PI / 4) {
                    $dy = (0.5 + $a * 2 / M_PI);
                }

                if ($a >= M_PI / 4 && $a <= 3 * M_PI / 4) {
                    $dy = 1;
                }

                if ($a >= 3 * M_PI / 4 && $a <= 5 * M_PI / 4) {
                    $dy = (1 - ($a - 3 * M_PI / 4) * 2 / M_PI);
                }

                if ($a >= 5 * M_PI / 4 && $a <= 7 * M_PI / 4) {
                    $dy = 0;
                }

            }

            $value->Set($lbl);
            $th = $value->GetHeight($dblImg);
            $tw = $value->GetWidth($dblImg);
            $xt = round($lr * cos($a) + $x) - $dx * $tw;
            $yt = round($y - $lr * sin($a)) - $dy * $th;

            $value->Stroke($dblImg, $xt, $yt);
        }

        if (__DEBUG) {
            $dblImg->SetColor('red');
            $dblImg->Circle($xc, $yc, $lr + $r);
        }

        // Stroke all the legs
        reset($this->iData);
        $i = 0;
        foreach ($this->iData as $dir => $legdata) {
            $legdata = array_slice($legdata, 1);
            $nn = count($legdata);

            $a = $txtpos[$i][2];
            $rri = $ri / $scaling;
            for ($j = 0; $j < $nn; ++$j) {
                // We want the non scaled original radius
                $legr = $this->scale->RelTranslate($legdata[$j], $r / $scaling, $ri / $scaling);
                $this->_StrokeWindLeg($dblImg, $xc, $yc, $a,
                    $rri * $scaling,
                    $legr * $scaling,
                    $this->iLegweights[$j % $nlw] * $scaling,
                    $this->iLegColors[$j % $nlc]);
                $rri += $legr;
            }
            ++$i;
        }
    }

    // Translate potential string specified compass labels to their
    // corresponding index.
    public function FixupIndexes($aDataArray, $num)
    {
        $ret = [];
        $keys = array_keys($aDataArray);
        foreach ($aDataArray as $idx => $data) {
            if (is_string($idx)) {
                $idx = strtoupper($idx);
                $res = array_search($idx, $this->iAllDirectionLabels);
                if ($res === false) {
                    Util\JpGraphError::RaiseL(22011, $idx); //('Windrose index must be numeric or direction label. You have specified index='.$idx);
                }
                $idx = $res;
                if ($idx % (16 / $num) !== 0) {
                    Util\JpGraphError::RaiseL(22012); //('Windrose radial axis specification contains a direction which is not enabled.');
                }
                $idx /= (16 / $num);

                if (in_array($idx, $keys, 1)) {
                    Util\JpGraphError::RaiseL(22013, $idx); //('You have specified the look&feel for the same compass direction twice, once with text and once with index (Index='.$idx.')');
                }
            }
            if ($idx < 0 || $idx > 15) {
                Util\JpGraphError::RaiseL(22014); //('Index for copmass direction must be between 0 and 15.');
            }
            $ret[$idx] = $data;
        }
        return $ret;
    }

    public function _StrokeRegularRose($dblImg, $value, $scaling, $xc, $yc, $r, $ri)
    {
        // _StrokeRegularRose($dblImg,$xc,$yc,$r,$ri)
        // Plot radial grid lines and remember the end position
        // and the angle for later use when plotting the labels
        switch ($this->iType) {
            case WINDROSE_TYPE4:
                $num = 4;
                break;
            case WINDROSE_TYPE8:
                $num = 8;
                break;
            case WINDROSE_TYPE16:
                $num = 16;
                break;
            default:
                Util\JpGraphError::RaiseL(22015); //('You have specified an undefined Windrose plot type.');
        }

        // Check if we should auto-position the angle for the
        // labels. Basically we try to find a firection with smallest
        // (or none) data.
        $this->SetAutoScaleAngle(true);

        $nlc = count($this->iLegColors);
        $nlw = count($this->iLegweights);

        $this->iRadialColorArray = $this->FixupIndexes($this->iRadialColorArray, $num);
        $this->iRadialWeightArray = $this->FixupIndexes($this->iRadialWeightArray, $num);
        $this->iRadialStyleArray = $this->FixupIndexes($this->iRadialStyleArray, $num);

        $txtpos = [];
        $a = 2 * M_PI / $num;
        $dblImg->SetColor($this->iGridColor2);
        $dblImg->SetLineStyle($this->iRadialGridStyle);
        $dblImg->SetLineWeight($this->iRadialGridWeight);

        // Translate any name specified directions to the index
        // so we can easily use it in the loop below
        for ($i = 0; $i < $num; ++$i) {
            $xxc = round($xc + cos($a * $i) * $ri);
            $yyc = round($yc - sin($a * $i) * $ri);
            $x = round($xc + cos($a * $i) * $r);
            $y = round($yc - sin($a * $i) * $r);
            if (empty($this->iRadialColorArray[$i])) {
                $dblImg->SetColor($this->iGridColor2);
            } else {
                $dblImg->SetColor($this->iRadialColorArray[$i]);
            }
            if (empty($this->iRadialWeightArray[$i])) {
                $dblImg->SetLineWeight($this->iRadialGridWeight);
            } else {
                $dblImg->SetLineWeight($this->iRadialWeightArray[$i]);
            }
            if (empty($this->iRadialStyleArray[$i])) {
                $dblImg->SetLineStyle($this->iRadialGridStyle);
            } else {
                $dblImg->SetLineStyle($this->iRadialStyleArray[$i]);
            }

            $dblImg->StyleLine($xxc, $yyc, $x, $y);
            $txtpos[] = [$x, $y, $a * $i];
        }
        $dblImg->SetLineWeight(1);

        $lr = $scaling * $this->iLabelMargin;
        if ($this->iLabelPositioning == LBLPOSITION_CENTER) {
            $value->SetAlign('center', 'center');
        } else {
            $value->SetAlign('left', 'top');
            $value->SetMargin(0);
            $lr /= 2;
        }

        for ($i = 0; $i < $num; ++$i) {
            list($x, $y, $a) = $txtpos[$i];

            // Set the position of the label
            if ($this->iLabelPositioning == LBLPOSITION_CENTER) {
                $dx = $dy = 0;
            } else {
                // LBLPOSIITON_EDGE
                if ($a >= 7 * M_PI / 4 || $a <= M_PI / 4) {
                    $dx = 0;
                }

                if ($a >= M_PI / 4 && $a <= 3 * M_PI / 4) {
                    $dx = ($a - M_PI / 4) * 2 / M_PI;
                }

                if ($a >= 3 * M_PI / 4 && $a <= 5 * M_PI / 4) {
                    $dx = 1;
                }

                if ($a >= 5 * M_PI / 4 && $a <= 7 * M_PI / 4) {
                    $dx = (1 - ($a - M_PI * 5 / 4) * 2 / M_PI);
                }

                if ($a >= 7 * M_PI / 4) {
                    $dy = (($a - M_PI) - 3 * M_PI / 4) * 2 / M_PI;
                }

                if ($a <= M_PI / 4) {
                    $dy = (0.5 + $a * 2 / M_PI);
                }

                if ($a >= M_PI / 4 && $a <= 3 * M_PI / 4) {
                    $dy = 1;
                }

                if ($a >= 3 * M_PI / 4 && $a <= 5 * M_PI / 4) {
                    $dy = (1 - ($a - 3 * M_PI / 4) * 2 / M_PI);
                }

                if ($a >= 5 * M_PI / 4 && $a <= 7 * M_PI / 4) {
                    $dy = 0;
                }

            }

            $value->Set($this->iAllDirectionLabels[$i * (16 / $num)]);
            $th = $value->GetHeight($dblImg);
            $tw = $value->GetWidth($dblImg);
            $xt = round($lr * cos($a) + $x) - $dx * $tw;
            $yt = round($y - $lr * sin($a)) - $dy * $th;

            $value->Stroke($dblImg, $xt, $yt);
        }

        if (__DEBUG) {
            $dblImg->SetColor("red");
            $dblImg->Circle($xc, $yc, $lr + $r);
        }

        // Stroke all the legs
        reset($this->iData);
        $keys = array_keys($this->iData);
        foreach ($this->iData as $idx => $legdata) {
            $legdata = array_slice($legdata, 1);
            $nn = count($legdata);
            if (is_string($idx)) {
                $idx = strtoupper($idx);
                $idx = array_search($idx, $this->iAllDirectionLabels);
                if ($idx === false) {
                    Util\JpGraphError::RaiseL(22016); //('Windrose leg index must be numeric or direction label.');
                }
                if ($idx % (16 / $num) !== 0) {
                    Util\JpGraphError::RaiseL(22017); //('Windrose data contains a direction which is not enabled. Please adjust what labels are displayed.');
                }
                $idx /= (16 / $num);

                if (in_array($idx, $keys, 1)) {
                    Util\JpGraphError::RaiseL(22018, $idx); //('You have specified data for the same compass direction twice, once with text and once with index (Index='.$idx.')');

                }
            }
            if ($idx < 0 || $idx > 15) {
                Util\JpGraphError::RaiseL(22019); //('Index for direction must be between 0 and 15. You can\'t specify angles for a Regular Windplot, only index and compass directions.');
            }
            $a = $idx * (360 / $num);
            $a *= M_PI / 180.0;
            $rri = $ri / $scaling;
            for ($j = 0; $j < $nn; ++$j) {
                // We want the non scaled original radius
                $legr = $this->scale->RelTranslate($legdata[$j], $r / $scaling, $ri / $scaling);
                $this->_StrokeWindLeg($dblImg, $xc, $yc, $a,
                    $rri * $scaling,
                    $legr * $scaling,
                    $this->iLegweights[$j % $nlw] * $scaling,
                    $this->iLegColors[$j % $nlc]);
                $rri += $legr;
            }
        }
    }

    public function getWidth($aImg)
    {

        $scaling = 1; //$this->iAntiAlias ? 2 : 1 ;
        if ($this->iSize > 0 && $this->iSize < 1) {
            $this->iSize *= min($aImg->width, $aImg->height);
        }

        $value = new Text\Text();
        $value->SetFont($this->iFontFamily, $this->iFontStyle, $this->iFontSize * $scaling);
        $value->SetColor($this->iFontColor);
        // Setup extra size around the graph needed so that the labels
        // doesn't get cut. For this we need to find the largest label.
        // The code below gives a possible a little to large margin. The
        // really, really proper way would be to account for what angle
        // the label are at
        $n = count($this->iLabels);
        if ($n > 0) {
            $maxh = 0;
            $maxw = 0;
            foreach ($this->iLabels as $key => $lbl) {
                $value->Set($lbl);
                $maxw = max($maxw, $value->GetWidth($aImg));
            }
        } else {
            $value->Set('888.888'); // Dummy value to get width/height
            $maxw = $value->GetWidth($aImg);
        }
        // Add an extra margin of 50% the font size
        $maxw += round($this->iFontSize * $scaling * 0.4);

        $valxmarg = 1.5 * $maxw + 2 * $this->iLabelMargin * $scaling;
        $w = round($this->iSize * $scaling + $valxmarg);

        // Make sure that the width of the legend fits
        $legendwidth = $this->_StrokeLegend($aImg, 0, 0, $scaling, true) + 10 * $scaling;
        $w = max($w, $legendwidth);

        return $w;
    }

    public function getHeight($aImg)
    {

        $scaling = 1; //$this->iAntiAlias ? 2 : 1 ;
        if ($this->iSize > 0 && $this->iSize < 1) {
            $this->iSize *= min($aImg->width, $aImg->height);
        }

        $value = new Text\Text();
        $value->SetFont($this->iFontFamily, $this->iFontStyle, $this->iFontSize * $scaling);
        $value->SetColor($this->iFontColor);
        // Setup extra size around the graph needed so that the labels
        // doesn't get cut. For this we need to find the largest label.
        // The code below gives a possible a little to large margin. The
        // really, really proper way would be to account for what angle
        // the label are at
        $n = count($this->iLabels);
        if ($n > 0) {
            $maxh = 0;
            $maxw = 0;
            foreach ($this->iLabels as $key => $lbl) {
                $value->Set($lbl);
                $maxh = max($maxh, $value->GetHeight($aImg));
            }
        } else {
            $value->Set('180.8'); // Dummy value to get width/height
            $maxh = $value->GetHeight($aImg);
        }
        // Add an extra margin of 50% the font size
        //$maxh += round($this->iFontSize*$scaling * 0.5) ;
        $valymarg = 2 * $maxh + 2 * $this->iLabelMargin * $scaling;

        $legendheight = round($this->legend->iShow ? 1 : 0);
        $legendheight *= max($this->legend->iCircleRadius * 2, $this->legend->iTxtFontSize * 2) +
        $this->legend->iMargin + $this->legend->iBottomMargin + 2;
        $legendheight *= $scaling;
        $h = round($this->iSize * $scaling + $valymarg) + $legendheight;

        return $h;
    }

    public function Stroke($aGraph)
    {

        $aImg = $aGraph->img;

        if ($this->iX > 0 && $this->iX < 1) {
            $this->iX = round($aImg->width * $this->iX);
        }

        if ($this->iY > 0 && $this->iY < 1) {
            $this->iY = round($aImg->height * $this->iY);
        }

        if ($this->iSize > 0 && $this->iSize < 1) {
            $this->iSize *= min($aImg->width, $aImg->height);
        }

        if ($this->iCenterSize > 0 && $this->iCenterSize < 1) {
            $this->iCenterSize *= $this->iSize;
        }

        $this->scale->AutoScale(($this->iSize - $this->iCenterSize) / 2, round(2.5 * $this->scale->iFontSize));

        $scaling = $this->iAntiAlias ? 2 : 1;

        $value = new Text\Text();
        $value->SetFont($this->iFontFamily, $this->iFontStyle, $this->iFontSize * $scaling);
        $value->SetColor($this->iFontColor);

        $legendheight = round($this->legend->iShow ? 1 : 0);
        $legendheight *= max($this->legend->iCircleRadius * 2, $this->legend->iTxtFontSize * 2) +
        $this->legend->iMargin + $this->legend->iBottomMargin + 2;
        $legendheight *= $scaling;

        $w = $scaling * $this->getWidth($aImg);
        $h = $scaling * $this->getHeight($aImg);

        // Copy back the double buffered image to the proper canvas
        $ww = $w / $scaling;
        $hh = $h / $scaling;

        // Create the double buffer
        if ($this->iAntiAlias) {
            $dblImg = new RotImage($w, $h);
            // Set the background color
            $dblImg->SetColor($this->iColor);
            $dblImg->FilledRectangle(0, 0, $w, $h);
        } else {
            $dblImg = $aImg;
            // Make sure the ix and it coordinates correpond to the new top left center
            $dblImg->SetTranslation($this->iX - $w / 2, $this->iY - $h / 2);
        }

        if (__DEBUG) {
            $dblImg->SetColor('red');
            $dblImg->Rectangle(0, 0, $w - 1, $h - 1);
        }

        $dblImg->SetColor('black');

        if ($this->iShowBox) {
            $dblImg->SetColor($this->iBoxColor);
            $old = $dblImg->SetLineWeight($this->iBoxWeight);
            $dblImg->SetLineStyle($this->iBoxStyle);
            $dblImg->Rectangle(0, 0, $w - 1, $h - 1);
            $dblImg->SetLineWeight($old);
        }

        $xc = round($w / 2);
        $yc = round(($h - $legendheight) / 2);

        if (__DEBUG) {
            $dblImg->SetColor('red');
            $old = $dblImg->SetLineWeight(2);
            $dblImg->Line($xc - 5, $yc - 5, $xc + 5, $yc + 5);
            $dblImg->Line($xc + 5, $yc - 5, $xc - 5, $yc + 5);
            $dblImg->SetLineWeight($old);
        }

        $this->iSize *= $scaling;

        // Inner circle size
        $ri = $this->iCenterSize / 2;

        // Full circle radius
        $r = round($this->iSize / 2);

        // Get number of grid circles
        $n = $this->scale->GetNumCirc();

        // Plot circle grids
        $ri *= $scaling;
        $rr = round(($r - $ri) / $n);
        for ($i = 1; $i <= $n; ++$i) {
            $this->_ThickCircle($dblImg, $xc, $yc, $rr * $i + $ri,
                $this->iCircGridWeight, $this->iGridColor1);
        }

        $num = 0;

        if ($this->iType == WINDROSE_TYPEFREE) {
            $this->_StrokeFreeRose($dblImg, $value, $scaling, $xc, $yc, $r, $ri);
        } else {
            // Check if we need to re-code the interpretation of the ordinal
            // number in the data. Internally ordinal value 0 is East and then
            // counted anti-clockwise. The user might choose an encoding
            // that have 0 being the first axis to the right of the "N" axis and then
            // counted clock-wise
            if ($this->iOrdinalEncoding == KEYENCODING_CLOCKWISE) {
                if ($this->iType == WINDROSE_TYPE16) {
                    $const1 = 19;
                    $const2 = 16;
                } elseif ($this->iType == WINDROSE_TYPE8) {
                    $const1 = 9;
                    $const2 = 8;
                } else {
                    $const1 = 4;
                    $const2 = 4;
                }
                $tmp = [];
                $n = count($this->iData);
                foreach ($this->iData as $key => $val) {
                    if (is_numeric($key)) {
                        $key = ($const1 - $key) % $const2;
                    }
                    $tmp[$key] = $val;
                }
                $this->iData = $tmp;
            }
            $this->_StrokeRegularRose($dblImg, $value, $scaling, $xc, $yc, $r, $ri);
        }

        // Stroke the labels
        $this->scale->iFontSize *= $scaling;
        $this->scale->iZFontSize *= $scaling;
        $this->scale->StrokeLabels($dblImg, $xc, $yc, $ri, $rr);

        // Stroke the inner circle again since the legs
        // might have written over it
        $this->_ThickCircle($dblImg, $xc, $yc, $ri, $this->iCircGridWeight, $this->iGridColor1);

        if ($ww > $aImg->width) {
            Util\JpGraphError::RaiseL(22020);
            //('Windrose plot is too large to fit the specified Graph size. Please use WindrosePlot::SetSize() to make the plot smaller or increase the size of the Graph in the initial WindroseGraph() call.');
        }

        $x = $xc;
        $y = $h;
        $this->_StrokeLegend($dblImg, $x, $y, $scaling);

        if ($this->iAntiAlias) {
            $aImg->Copy($dblImg->img, $this->iX - $ww / 2, $this->iY - $hh / 2, 0, 0, $ww, $hh, $w, $h);
        }

        // We need to restore the translation matrix
        $aImg->SetTranslation(0, 0);

    }

}
