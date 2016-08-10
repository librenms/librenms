<?php
/*=======================================================================
 // File:  		 JPGRAPH_LINE.PHP
 // Description: Line plot extension for JpGraph
 // Created:  	 2001-01-08
 // Ver:  		 $Id: jpgraph_line.php 1921 2009-12-11 11:46:39Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

require_once ('jpgraph_plotmark.inc.php');

// constants for the (filled) area
DEFINE("LP_AREA_FILLED", true);
DEFINE("LP_AREA_NOT_FILLED", false);
DEFINE("LP_AREA_BORDER",false);
DEFINE("LP_AREA_NO_BORDER",true);

//===================================================
// CLASS LinePlot
// Description:
//===================================================
class LinePlot extends Plot{
    public $mark=null;
    protected $filled=false;
    protected $fill_color='blue';
    protected $step_style=false, $center=false;
    protected $line_style=1; // Default to solid
    protected $filledAreas = array(); // array of arrays(with min,max,col,filled in them)
    public $barcenter=false;  // When we mix line and bar. Should we center the line in the bar.
    protected $fillFromMin = false, $fillFromMax = false;
    protected $fillgrad=false,$fillgrad_fromcolor='navy',$fillgrad_tocolor='silver',$fillgrad_numcolors=100;
    protected $iFastStroke=false;

    //---------------
    // CONSTRUCTOR
    function LinePlot($datay,$datax=false) {
        parent::__construct($datay,$datax);
        $this->mark = new PlotMark() ;
        $this->color = ColorFactory::getColor();
        $this->fill_color = $this->color;
    }
    //---------------
    // PUBLIC METHODS

    function SetFilled($aFlg=true) {
		$this->filled = $aFlg;
    }

    function SetBarCenter($aFlag=true) {
        $this->barcenter=$aFlag;
    }

    function SetStyle($aStyle) {
        $this->line_style=$aStyle;
    }

    function SetStepStyle($aFlag=true) {
        $this->step_style = $aFlag;
    }

    function SetColor($aColor) {
        parent::SetColor($aColor);
    }

    function SetFillFromYMin($f=true) {
        $this->fillFromMin = $f ;
    }

    function SetFillFromYMax($f=true) {
        $this->fillFromMax = $f ;
    }

    function SetFillColor($aColor,$aFilled=true) {
    	//$this->color = $aColor;
        $this->fill_color=$aColor;
        $this->filled=$aFilled;
    }

    function SetFillGradient($aFromColor,$aToColor,$aNumColors=100,$aFilled=true) {
        $this->fillgrad_fromcolor = $aFromColor;
        $this->fillgrad_tocolor   = $aToColor;
        $this->fillgrad_numcolors = $aNumColors;
        $this->filled = $aFilled;
        $this->fillgrad = true;
    }

    function Legend($graph) {
        if( $this->legend!="" ) {
            if( $this->filled && !$this->fillgrad ) {
                $graph->legend->Add($this->legend,
                $this->fill_color,$this->mark,0,
                $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
            }
            elseif( $this->fillgrad ) {
                $color=array($this->fillgrad_fromcolor,$this->fillgrad_tocolor);
                // In order to differentiate between gradients and cooors specified as an RGB triple
                $graph->legend->Add($this->legend,$color,"",-2 /* -GRAD_HOR */,
                $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
            } else {
                $graph->legend->Add($this->legend,
                $this->color,$this->mark,$this->line_style,
                $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
            }
        }
    }

    function AddArea($aMin=0,$aMax=0,$aFilled=LP_AREA_NOT_FILLED,$aColor="gray9",$aBorder=LP_AREA_BORDER) {
        if($aMin > $aMax) {
            // swap
            $tmp = $aMin;
            $aMin = $aMax;
            $aMax = $tmp;
        }
        $this->filledAreas[] = array($aMin,$aMax,$aColor,$aFilled,$aBorder);
    }

    // Gets called before any axis are stroked
    function PreStrokeAdjust($graph) {

        // If another plot type have already adjusted the
        // offset we don't touch it.
        // (We check for empty in case the scale is  a log scale
        // and hence doesn't contain any xlabel_offset)
        if( empty($graph->xaxis->scale->ticks->xlabel_offset) || $graph->xaxis->scale->ticks->xlabel_offset == 0 ) {
            if( $this->center ) {
                ++$this->numpoints;
                $a=0.5; $b=0.5;
            } else {
                $a=0; $b=0;
            }
            $graph->xaxis->scale->ticks->SetXLabelOffset($a);
            $graph->SetTextScaleOff($b);
            //$graph->xaxis->scale->ticks->SupressMinorTickMarks();
        }
    }

    function SetFastStroke($aFlg=true) {
        $this->iFastStroke = $aFlg;
    }

    function FastStroke($img,$xscale,$yscale,$aStartPoint=0,$exist_x=true) {
        // An optimized stroke for many data points with no extra
        // features but 60% faster. You can't have values or line styles, or null
        // values in plots.
        $numpoints=count($this->coords[0]);
        if( $this->barcenter ) {
            $textadj = 0.5-$xscale->text_scale_off;
        }
        else {
            $textadj = 0;
        }

        $img->SetColor($this->color);
        $img->SetLineWeight($this->weight);
        $pnts=$aStartPoint;
        while( $pnts < $numpoints ) {
            if( $exist_x ) {
                $x=$this->coords[1][$pnts];
            }
            else {
                $x=$pnts+$textadj;
            }
            $xt = $xscale->Translate($x);
            $y=$this->coords[0][$pnts];
            $yt = $yscale->Translate($y);
            if( is_numeric($y) ) {
                $cord[] = $xt;
                $cord[] = $yt;
            }
            elseif( $y == '-' && $pnts > 0 ) {
                // Just ignore
            }
            else {
                JpGraphError::RaiseL(10002);//('Plot too complicated for fast line Stroke. Use standard Stroke()');
            }
            ++$pnts;
        } // WHILE

        $img->Polygon($cord,false,true);
    }

    function Stroke($img,$xscale,$yscale) {
        $idx=0;
        $numpoints=count($this->coords[0]);
        if( isset($this->coords[1]) ) {
            if( count($this->coords[1])!=$numpoints ) {
                JpGraphError::RaiseL(2003,count($this->coords[1]),$numpoints);
            //("Number of X and Y points are not equal. Number of X-points:".count($this->coords[1])." Number of Y-points:$numpoints");
            }
            else {
                $exist_x = true;
            }
        }
        else {
            $exist_x = false;
        }

        if( $this->barcenter ) {
            $textadj = 0.5-$xscale->text_scale_off;
        }
        else {
            $textadj = 0;
        }

        // Find the first numeric data point
        $startpoint=0;
        while( $startpoint < $numpoints && !is_numeric($this->coords[0][$startpoint]) ) {
            ++$startpoint;
        }

        // Bail out if no data points
        if( $startpoint == $numpoints ) return;

        if( $this->iFastStroke ) {
            $this->FastStroke($img,$xscale,$yscale,$startpoint,$exist_x);
            return;
        }

        if( $exist_x ) {
            $xs=$this->coords[1][$startpoint];
        }
        else {
            $xs= $textadj+$startpoint;
        }

        $img->SetStartPoint($xscale->Translate($xs),
        $yscale->Translate($this->coords[0][$startpoint]));

        if( $this->filled ) {
            if( $this->fillFromMax ) {
                //$max = $yscale->GetMaxVal();
                $cord[$idx++] = $xscale->Translate($xs);
                $cord[$idx++] = $yscale->scale_abs[1];
            }
            else {
                $min = $yscale->GetMinVal();
                if( $min > 0 || $this->fillFromMin ) {
                    $fillmin = $yscale->scale_abs[0];//Translate($min);
                }
                else {
                    $fillmin = $yscale->Translate(0);
                }

                $cord[$idx++] = $xscale->Translate($xs);
                $cord[$idx++] = $fillmin;
            }
        }
        $xt = $xscale->Translate($xs);
        $yt = $yscale->Translate($this->coords[0][$startpoint]);
        $cord[$idx++] = $xt;
        $cord[$idx++] = $yt;
        $yt_old = $yt;
        $xt_old = $xt;
        $y_old = $this->coords[0][$startpoint];

        $this->value->Stroke($img,$this->coords[0][$startpoint],$xt,$yt);

        $img->SetColor($this->color);
        $img->SetLineWeight($this->weight);
        $img->SetLineStyle($this->line_style);
        $pnts=$startpoint+1;
        $firstnonumeric = false;


        while( $pnts < $numpoints ) {

            if( $exist_x ) {
                $x=$this->coords[1][$pnts];
            }
            else {
                $x=$pnts+$textadj;
            }
            $xt = $xscale->Translate($x);
            $yt = $yscale->Translate($this->coords[0][$pnts]);

            $y=$this->coords[0][$pnts];
            if( $this->step_style ) {
                // To handle null values within step style we need to record the
                // first non numeric value so we know from where to start if the
                // non value is '-'.
                if( is_numeric($y) ) {
                    $firstnonumeric = false;
                    if( is_numeric($y_old) ) {
                        $img->StyleLine($xt_old,$yt_old,$xt,$yt_old);
                        $img->StyleLine($xt,$yt_old,$xt,$yt);
                    }
                    elseif( $y_old == '-' ) {
                        $img->StyleLine($xt_first,$yt_first,$xt,$yt_first);
                        $img->StyleLine($xt,$yt_first,$xt,$yt);
                    }
                    else {
                        $yt_old = $yt;
                        $xt_old = $xt;
                    }
                    $cord[$idx++] = $xt;
                    $cord[$idx++] = $yt_old;
                    $cord[$idx++] = $xt;
                    $cord[$idx++] = $yt;
                }
                elseif( $firstnonumeric==false ) {
                    $firstnonumeric = true;
                    $yt_first = $yt_old;
                    $xt_first = $xt_old;
                }
            }
            else {
                $tmp1=$y;
                $prev=$this->coords[0][$pnts-1];
                if( $tmp1==='' || $tmp1===NULL || $tmp1==='X' ) $tmp1 = 'x';
                if( $prev==='' || $prev===null || $prev==='X' ) $prev = 'x';

                if( is_numeric($y) || (is_string($y) && $y != '-') ) {
                    if( is_numeric($y) && (is_numeric($prev) || $prev === '-' ) ) {
                        $img->StyleLineTo($xt,$yt);
                    }
                    else {
                        $img->SetStartPoint($xt,$yt);
                    }
                }
                if( $this->filled && $tmp1 !== '-' ) {
                    if( $tmp1 === 'x' ) {
                        $cord[$idx++] = $cord[$idx-3];
                        $cord[$idx++] = $fillmin;
                    }
                    elseif( $prev === 'x' ) {
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $fillmin;
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $yt;
                    }
                    else {
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $yt;
                    }
                }
                else {
                    if( is_numeric($tmp1)  && (is_numeric($prev) || $prev === '-' ) ) {
                        $cord[$idx++] = $xt;
                        $cord[$idx++] = $yt;
                    }
                }
            }
            $yt_old = $yt;
            $xt_old = $xt;
            $y_old = $y;

            $this->StrokeDataValue($img,$this->coords[0][$pnts],$xt,$yt);

            ++$pnts;
        }

        if( $this->filled  ) {
            $cord[$idx++] = $xt;
            if( $this->fillFromMax ) {
                $cord[$idx++] = $yscale->scale_abs[1];
            }
            else {
                if( $min > 0 || $this->fillFromMin ) {
                    $cord[$idx++] = $yscale->Translate($min);
                }
                else {
                    $cord[$idx++] = $yscale->Translate(0);
                }
            }
            if( $this->fillgrad ) {
                $img->SetLineWeight(1);
                $grad = new Gradient($img);
                $grad->SetNumColors($this->fillgrad_numcolors);
                $grad->FilledFlatPolygon($cord,$this->fillgrad_fromcolor,$this->fillgrad_tocolor);
                $img->SetLineWeight($this->weight);
            }
            else {
                $img->SetColor($this->fill_color);
                $img->FilledPolygon($cord);
            }
            if( $this->weight > 0 ) { 
                $img->SetLineWeight($this->weight);
                $img->SetColor($this->color);
                // Remove first and last coordinate before drawing the line
                // sine we otherwise get the vertical start and end lines which
                // doesn't look appropriate
                $img->Polygon(array_slice($cord,2,count($cord)-4));
            }
        }

        if(!empty($this->filledAreas)) {

            $minY = $yscale->Translate($yscale->GetMinVal());
            $factor = ($this->step_style ? 4 : 2);

            for($i = 0; $i < sizeof($this->filledAreas); ++$i) {
                // go through all filled area elements ordered by insertion
                // fill polygon array
                $areaCoords[] = $cord[$this->filledAreas[$i][0] * $factor];
                $areaCoords[] = $minY;

                $areaCoords =
                array_merge($areaCoords,
                array_slice($cord,
                $this->filledAreas[$i][0] * $factor,
                ($this->filledAreas[$i][1] - $this->filledAreas[$i][0] + ($this->step_style ? 0 : 1))  * $factor));
                $areaCoords[] = $areaCoords[sizeof($areaCoords)-2]; // last x
                $areaCoords[] = $minY; // last y

                if($this->filledAreas[$i][3]) {
                    $img->SetColor($this->filledAreas[$i][2]);
                    $img->FilledPolygon($areaCoords);
                    $img->SetColor($this->color);
                }
                // Check if we should draw the frame.
                // If not we still re-draw the line since it might have been
                // partially overwritten by the filled area and it doesn't look
                // very good.
                if( $this->filledAreas[$i][4] ) {
                    $img->Polygon($areaCoords);
                }
                else {
                    $img->Polygon($cord);
                }

                $areaCoords = array();
            }
        }

        if( $this->mark->type == -1 || $this->mark->show == false )
        return;

        for( $pnts=0; $pnts<$numpoints; ++$pnts) {

            if( $exist_x ) {
                $x=$this->coords[1][$pnts];
            }
            else {
                $x=$pnts+$textadj;
            }
            $xt = $xscale->Translate($x);
            $yt = $yscale->Translate($this->coords[0][$pnts]);

            if( is_numeric($this->coords[0][$pnts]) ) {
                if( !empty($this->csimtargets[$pnts]) ) {
                    if( !empty($this->csimwintargets[$pnts]) ) {
                        $this->mark->SetCSIMTarget($this->csimtargets[$pnts],$this->csimwintargets[$pnts]);
                    }
                    else {
                        $this->mark->SetCSIMTarget($this->csimtargets[$pnts]);
                    }
                    $this->mark->SetCSIMAlt($this->csimalts[$pnts]);
                }
                if( $exist_x ) {
                    $x=$this->coords[1][$pnts];
                }
                else {
                    $x=$pnts;
                }
                $this->mark->SetCSIMAltVal($this->coords[0][$pnts],$x);
                $this->mark->Stroke($img,$xt,$yt);
                $this->csimareas .= $this->mark->GetCSIMAreas();
            }
        }
    }
} // Class


//===================================================
// CLASS AccLinePlot
// Description:
//===================================================
class AccLinePlot extends Plot {
    protected $plots=null,$nbrplots=0;
    private $iStartEndZero=true;
    //---------------
    // CONSTRUCTOR
    function __construct($plots) {
        $this->plots = $plots;
        $this->nbrplots = count($plots);
        $this->numpoints = $plots[0]->numpoints;

        // Verify that all plots have the same number of data points
        for( $i=1; $i < $this->nbrplots; ++$i ) {
            if( $plots[$i]->numpoints != $this->numpoints ) {
                JpGraphError::RaiseL(10003);//('Each plot in an accumulated lineplot must have the same number of data points',0)
            }
        }

        for($i=0; $i < $this->nbrplots; ++$i ) {
            $this->LineInterpolate($this->plots[$i]->coords[0]);
        }
    }

    //---------------
    // PUBLIC METHODS
    function Legend($graph) {
        foreach( $this->plots as $p ) {
            $p->DoLegend($graph);
        }
    }

    function Max() {
        list($xmax) = $this->plots[0]->Max();
        $nmax=0;
        $n = count($this->plots);
        for($i=0; $i < $n; ++$i) {
            $nc = count($this->plots[$i]->coords[0]);
            $nmax = max($nmax,$nc);
            list($x) = $this->plots[$i]->Max();
            $xmax = Max($xmax,$x);
        }
        for( $i = 0; $i < $nmax; $i++ ) {
            // Get y-value for line $i by adding the
            // individual bars from all the plots added.
            // It would be wrong to just add the
            // individual plots max y-value since that
            // would in most cases give to large y-value.
            $y=$this->plots[0]->coords[0][$i];
            for( $j = 1; $j < $this->nbrplots; $j++ ) {
                $y += $this->plots[ $j ]->coords[0][$i];
            }
            $ymax[$i] = $y;
        }
        $ymax = max($ymax);
        return array($xmax,$ymax);
    }

    function Min() {
        $nmax=0;
        list($xmin,$ysetmin) = $this->plots[0]->Min();
        $n = count($this->plots);
        for($i=0; $i < $n; ++$i) {
            $nc = count($this->plots[$i]->coords[0]);
            $nmax = max($nmax,$nc);
            list($x,$y) = $this->plots[$i]->Min();
            $xmin = Min($xmin,$x);
            $ysetmin = Min($y,$ysetmin);
        }
        for( $i = 0; $i < $nmax; $i++ ) {
            // Get y-value for line $i by adding the
            // individual bars from all the plots added.
            // It would be wrong to just add the
            // individual plots min y-value since that
            // would in most cases give to small y-value.
            $y=$this->plots[0]->coords[0][$i];
            for( $j = 1; $j < $this->nbrplots; $j++ ) {
                $y += $this->plots[ $j ]->coords[0][$i];
            }
            $ymin[$i] = $y;
        }
        $ymin = Min($ysetmin,Min($ymin));
        return array($xmin,$ymin);
    }

    // Gets called before any axis are stroked
    function PreStrokeAdjust($graph) {

        // If another plot type have already adjusted the
        // offset we don't touch it.
        // (We check for empty in case the scale is  a log scale
        // and hence doesn't contain any xlabel_offset)

        if( empty($graph->xaxis->scale->ticks->xlabel_offset) ||
        $graph->xaxis->scale->ticks->xlabel_offset == 0 ) {
            if( $this->center ) {
                ++$this->numpoints;
                $a=0.5; $b=0.5;
            } else {
                $a=0; $b=0;
            }
            $graph->xaxis->scale->ticks->SetXLabelOffset($a);
            $graph->SetTextScaleOff($b);
            $graph->xaxis->scale->ticks->SupressMinorTickMarks();
        }

    }

    function SetInterpolateMode($aIntMode) {
        $this->iStartEndZero=$aIntMode;
    }

    // Replace all '-' with an interpolated value. We use straightforward
    // linear interpolation. If the data starts with one or several '-' they
    // will be replaced by the the first valid data point
    function LineInterpolate(&$aData) {

        $n=count($aData);
        $i=0;

        // If first point is undefined we will set it to the same as the first
        // valid data
        if( $aData[$i]==='-' ) {
            // Find the first valid data
            while( $i < $n && $aData[$i]==='-' ) {
                ++$i;
            }
            if( $i < $n ) {
                for($j=0; $j < $i; ++$j ) {
                    if( $this->iStartEndZero )
                    $aData[$i] = 0;
                    else
                    $aData[$j] = $aData[$i];
                }
            }
            else {
                // All '-' => Error
                return false;
            }
        }

        while($i < $n) {
            while( $i < $n && $aData[$i] !== '-' ) {
                ++$i;
            }
            if( $i < $n ) {
                $pstart=$i-1;

                // Now see how long this segment of '-' are
                while( $i < $n && $aData[$i] === '-' ) {
                    ++$i;
                }
                if( $i < $n ) {
                    $pend=$i;
                    $size=$pend-$pstart;
                    $k=($aData[$pend]-$aData[$pstart])/$size;
                    // Replace the segment of '-' with a linear interpolated value.
                    for($j=1; $j < $size; ++$j ) {
                        $aData[$pstart+$j] = $aData[$pstart] + $j*$k ;
                    }
                }
                else {
                    // There are no valid end point. The '-' goes all the way to the end
                    // In that case we just set all the remaining values the the same as the
                    // last valid data point.
                    for( $j=$pstart+1; $j < $n; ++$j )
                    if( $this->iStartEndZero ) {
                        $aData[$j] = 0;
                    }
                    else {
                        $aData[$j] = $aData[$pstart] ;
                    }
                }
            }
        }
        return true;
    }

    // To avoid duplicate of line drawing code here we just
    // change the y-values for each plot and then restore it
    // after we have made the stroke. We must do this copy since
    // it wouldn't be possible to create an acc line plot
    // with the same graphs, i.e AccLinePlot(array($pl,$pl,$pl));
    // since this method would have a side effect.
    function Stroke($img,$xscale,$yscale) {
        $img->SetLineWeight($this->weight);
        $this->numpoints = count($this->plots[0]->coords[0]);
        // Allocate array
        $coords[$this->nbrplots][$this->numpoints]=0;
        for($i=0; $i<$this->numpoints; $i++) {
            $coords[0][$i]=$this->plots[0]->coords[0][$i];
            $accy=$coords[0][$i];
            for($j=1; $j<$this->nbrplots; ++$j ) {
                $coords[$j][$i] = $this->plots[$j]->coords[0][$i]+$accy;
                $accy = $coords[$j][$i];
            }
        }
        for($j=$this->nbrplots-1; $j>=0; --$j) {
            $p=$this->plots[$j];
            for( $i=0; $i<$this->numpoints; ++$i) {
                $tmp[$i]=$p->coords[0][$i];
                $p->coords[0][$i]=$coords[$j][$i];
            }
            $p->Stroke($img,$xscale,$yscale);
            for( $i=0; $i<$this->numpoints; ++$i) {
                $p->coords[0][$i]=$tmp[$i];
            }
            $p->coords[0][]=$tmp;
        }
    }
} // Class


/* EOF */
?>
