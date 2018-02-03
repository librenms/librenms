<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

class PolarGraph extends Graph
{
    public $scale;
    public $axis;
    public $iType       = POLAR_360;
    private $iClockwise = false;

    public function __construct($aWidth = 300, $aHeight = 200, $aCachedName = "", $aTimeOut = 0, $aInline = true)
    {
        parent::__construct($aWidth, $aHeight, $aCachedName, $aTimeOut, $aInline);
        $this->SetDensity(TICKD_DENSE);
        $this->SetBox();
        $this->SetMarginColor('white');
    }

    public function SetDensity($aDense)
    {
        $this->SetTickDensity(TICKD_NORMAL, $aDense);
    }

    public function SetClockwise($aFlg)
    {
        $this->scale->SetClockwise($aFlg);
    }

    public function Set90AndMargin($lm = 0, $rm = 0, $tm = 0, $bm = 0)
    {
        $adj = ($this->img->height - $this->img->width) / 2;
        $this->SetAngle(90);
        $lm2 = -$adj + ($lm - $rm + $tm + $bm) / 2;
        $rm2 = -$adj + (-$lm + $rm + $tm + $bm) / 2;
        $tm2 = $adj + ($tm - $bm + $lm + $rm) / 2;
        $bm2 = $adj + (-$tm + $bm + $lm + $rm) / 2;
        $this->SetMargin($lm2, $rm2, $tm2, $bm2);
        $this->axis->SetLabelAlign('right', 'center');
    }

    public function SetScale($aScale, $rmax = 0, $dummy1 = 1, $dummy2 = 1, $dummy3 = 1)
    {
        if ($aScale == 'lin') {
            $this->scale = new PolarScale($rmax, $this, $this->iClockwise);
        } elseif ($aScale == 'log') {
            $this->scale = new PolarLogScale($rmax, $this, $this->iClockwise);
        } else {
            Util\JpGraphError::RaiseL(17004); //('Unknown scale type for polar graph. Must be "lin" or "log"');
        }

        $this->axis = new PolarAxis($this->img, $this->scale);
        $this->SetMargin(40, 40, 50, 40);
    }

    public function SetType($aType)
    {
        $this->iType = $aType;
    }

    public function SetPlotSize($w, $h)
    {
        $this->SetMargin(($this->img->width - $w) / 2, ($this->img->width - $w) / 2,
            ($this->img->height - $h) / 2, ($this->img->height - $h) / 2);
    }

    // Private methods
    public function GetPlotsMax()
    {
        $n = count($this->plots);
        $m = $this->plots[0]->Max();
        $i = 1;
        while ($i < $n) {
            $m = max($this->plots[$i]->Max(), $m);
            ++$i;
        }
        return $m;
    }

    public function Stroke($aStrokeFileName = "")
    {

        // Start by adjusting the margin so that potential titles will fit.
        $this->AdjustMarginsForTitles();

        // If the filename is the predefined value = '_csim_special_'
        // we assume that the call to stroke only needs to do enough
        // to correctly generate the CSIM maps.
        // We use this variable to skip things we don't strictly need
        // to do to generate the image map to improve performance
        // a best we can. Therefor you will see a lot of tests !$_csim in the
        // code below.
        $_csim = ($aStrokeFileName === _CSIM_SPECIALFILE);

        // We need to know if we have stroked the plot in the
        // GetCSIMareas. Otherwise the CSIM hasn't been generated
        // and in the case of GetCSIM called before stroke to generate
        // CSIM without storing an image to disk GetCSIM must call Stroke.
        $this->iHasStroked = true;

        //Check if we should autoscale axis
        if (!$this->scale->IsSpecified() && count($this->plots) > 0) {
            $max = $this->GetPlotsMax();
            $t1  = $this->img->plotwidth;
            $this->img->plotwidth /= 2;
            $t2 = $this->img->left_margin;
            $this->img->left_margin += $this->img->plotwidth + 1;
            $this->scale->AutoScale($this->img, 0, $max,
                $this->img->plotwidth / $this->xtick_factor / 2);
            $this->img->plotwidth   = $t1;
            $this->img->left_margin = $t2;
        } else {
            // The tick calculation will use the user suplied min/max values to determine
            // the ticks. If auto_ticks is false the exact user specifed min and max
            // values will be used for the scale.
            // If auto_ticks is true then the scale might be slightly adjusted
            // so that the min and max values falls on an even major step.
            //$min = 0;
            $max = $this->scale->scale[1];
            $t1  = $this->img->plotwidth;
            $this->img->plotwidth /= 2;
            $t2 = $this->img->left_margin;
            $this->img->left_margin += $this->img->plotwidth + 1;
            $this->scale->AutoScale($this->img, 0, $max,
                $this->img->plotwidth / $this->xtick_factor / 2);
            $this->img->plotwidth   = $t1;
            $this->img->left_margin = $t2;
        }

        if ($this->iType == POLAR_180) {
            $pos = $this->img->height - $this->img->bottom_margin;
        } else {
            $pos = $this->img->plotheight / 2 + $this->img->top_margin;
        }

        if (!$_csim) {
            $this->StrokePlotArea();
        }

        $this->iDoClipping = true;

        if ($this->iDoClipping) {
            $oldimage = $this->img->CloneCanvasH();
        }

        if (!$_csim) {
            $this->axis->StrokeGrid($pos);
        }

        // Stroke all plots for Y1 axis
        for ($i = 0; $i < count($this->plots); ++$i) {
            $this->plots[$i]->Stroke($this->img, $this->scale);
        }

        if ($this->iDoClipping) {
            // Clipping only supports graphs at 0 and 90 degrees
            if ($this->img->a == 0) {
                $this->img->CopyCanvasH($oldimage, $this->img->img,
                    $this->img->left_margin, $this->img->top_margin,
                    $this->img->left_margin, $this->img->top_margin,
                    $this->img->plotwidth + 1, $this->img->plotheight + 1);
            } elseif ($this->img->a == 90) {
                $adj1 = round(($this->img->height - $this->img->width) / 2);
                $adj2 = round(($this->img->width - $this->img->height) / 2);
                $lm   = $this->img->left_margin;
                $rm   = $this->img->right_margin;
                $tm   = $this->img->top_margin;
                $bm   = $this->img->bottom_margin;
                $this->img->CopyCanvasH($oldimage, $this->img->img,
                    $adj2 + round(($lm - $rm + $tm + $bm) / 2),
                    $adj1 + round(($tm - $bm + $lm + $rm) / 2),
                    $adj2 + round(($lm - $rm + $tm + $bm) / 2),
                    $adj1 + round(($tm - $bm + $lm + $rm) / 2),
                    $this->img->plotheight + 1,
                    $this->img->plotwidth + 1);
            }
            $this->img->Destroy();
            $this->img->SetCanvasH($oldimage);
        }

        if (!$_csim) {
            $this->axis->Stroke($pos);
            $this->axis->StrokeAngleLabels($pos, $this->iType);
        }

        if (!$_csim) {
            $this->StrokePlotBox();
            $this->footer->Stroke($this->img);

            // The titles and legends never gets rotated so make sure
            // that the angle is 0 before stroking them
            $aa = $this->img->SetAngle(0);
            $this->StrokeTitles();
        }

        for ($i = 0; $i < count($this->plots); ++$i) {
            $this->plots[$i]->Legend($this);
        }

        $this->legend->Stroke($this->img);

        if (!$_csim) {
            $this->StrokeTexts();
            $this->img->SetAngle($aa);

            // Draw an outline around the image map
            if (_JPG_DEBUG) {
                $this->DisplayClientSideaImageMapAreas();
            }

            // If the filename is given as the special "__handle"
            // then the image handler is returned and the image is NOT
            // streamed back
            if ($aStrokeFileName == _IMG_HANDLER) {
                return $this->img->img;
            } else {
                // Finally stream the generated picture
                $this->cache->PutAndStream($this->img, $this->cache_name, $this->inline, $aStrokeFileName);
            }
        }
    }
}
