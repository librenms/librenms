<?php
/*=======================================================================
 // File:        JPGRAPH_RADAR.PHP
 // Description: Radar plot extension for JpGraph
 // Created:     2001-02-04
 // Ver:         $Id: jpgraph_radar.php 1783 2009-08-25 11:41:01Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

require_once('jpgraph_plotmark.inc.php');

//===================================================
// CLASS RadarLogTicks
// Description: Logarithmic ticks
//===================================================
class RadarLogTicks extends Ticks {

    function __construct() {
            // Empty
    }

    function Stroke($aImg,&$grid,$aPos,$aAxisAngle,$aScale,&$aMajPos,&$aMajLabel) {
        $start = $aScale->GetMinVal();
        $limit = $aScale->GetMaxVal();
        $nextMajor = 10*$start;
        $step = $nextMajor / 10.0;
        $count=1;

        $ticklen_maj=5;
        $dx_maj=round(sin($aAxisAngle)*$ticklen_maj);
        $dy_maj=round(cos($aAxisAngle)*$ticklen_maj);
        $ticklen_min=3;
        $dx_min=round(sin($aAxisAngle)*$ticklen_min);
        $dy_min=round(cos($aAxisAngle)*$ticklen_min);

        $aMajPos=array();
        $aMajLabel=array();

        if( $this->supress_first ) {
            $aMajLabel[] = '';
        }
        else {
            $aMajLabel[]=$start;
        }

        $yr=$aScale->RelTranslate($start);
        $xt=round($yr*cos($aAxisAngle))+$aScale->scale_abs[0];
        $yt=$aPos-round($yr*sin($aAxisAngle));
        $aMajPos[]=$xt+2*$dx_maj;
        $aMajPos[]=$yt-$aImg->GetFontheight()/2;
        $grid[]=$xt;
        $grid[]=$yt;

        $aImg->SetLineWeight($this->weight);

        for($y=$start; $y<=$limit; $y+=$step,++$count  ) {
            $yr=$aScale->RelTranslate($y);
            $xt=round($yr*cos($aAxisAngle))+$aScale->scale_abs[0];
            $yt=$aPos-round($yr*sin($aAxisAngle));
            if( $count % 10 == 0 ) {
                $grid[]=$xt;
                $grid[]=$yt;
                $aMajPos[]=$xt+2*$dx_maj;
                $aMajPos[]=$yt-$aImg->GetFontheight()/2;
                if( !$this->supress_tickmarks ) {
                    if( $this->majcolor != '' ) {
                        $aImg->PushColor($this->majcolor);
                    }
                    $aImg->Line($xt+$dx_maj,$yt+$dy_maj,$xt-$dx_maj,$yt-$dy_maj);
                    if( $this->majcolor != '' ) {
                        $aImg->PopColor();
                    }
                }
                if( $this->label_formfunc != '' ) {
                    $f=$this->label_formfunc;
                    $l = call_user_func($f,$nextMajor);
                }
                else {
                    $l = $nextMajor;
                }

                $aMajLabel[]=$l;
                $nextMajor *= 10;
                $step *= 10;
                $count=1;
            }
            else {
                if( !$this->supress_minor_tickmarks ) {
                    if( $this->mincolor != '' ) {
                        $aImg->PushColor($this->mincolor);
                    }
                    $aImg->Line($xt+$dx_min,$yt+$dy_min,$xt-$dx_min,$yt-$dy_min);
                    if( $this->mincolor != '' ) {
                        $aImg->PopColor();
                    }
                }
            }
        }
    }
}

//===================================================
// CLASS RadarLinear
// Description: Linear ticks
//===================================================
class RadarLinearTicks extends Ticks {

    private $minor_step=1, $major_step=2;
    private $xlabel_offset=0,$xtick_offset=0;

    function __construct() {
        // Empty
    }

    // Return major step size in world coordinates
    function GetMajor() {
        return $this->major_step;
    }

    // Return minor step size in world coordinates
    function GetMinor() {
        return $this->minor_step;
    }

    // Set Minor and Major ticks (in world coordinates)
    function Set($aMajStep,$aMinStep=false) {
        if( $aMinStep==false ) {
            $aMinStep=$aMajStep;
        }

        if( $aMajStep <= 0 || $aMinStep <= 0 ) {
            JpGraphError::RaiseL(25064);
            //JpGraphError::Raise(" Minor or major step size is 0. Check that you haven't got an accidental SetTextTicks(0) in your code. If this is not the case you might have stumbled upon a bug in JpGraph. Please report this and if possible include the data that caused the problem.");
        }

        $this->major_step=$aMajStep;
        $this->minor_step=$aMinStep;
        $this->is_set = true;
    }

    function Stroke($aImg,&$grid,$aPos,$aAxisAngle,$aScale,&$aMajPos,&$aMajLabel) {
        // Prepare to draw linear ticks
        $maj_step_abs = abs($aScale->scale_factor*$this->major_step);
        $min_step_abs = abs($aScale->scale_factor*$this->minor_step);
        $nbrmaj = round($aScale->world_abs_size/$maj_step_abs);
        $nbrmin = round($aScale->world_abs_size/$min_step_abs);
        $skip = round($nbrmin/$nbrmaj); // Don't draw minor on top of major

        // Draw major ticks
        $ticklen2=$this->major_abs_size;
        $dx=round(sin($aAxisAngle)*$ticklen2);
        $dy=round(cos($aAxisAngle)*$ticklen2);
        $label=$aScale->scale[0]+$this->major_step;

        $aImg->SetLineWeight($this->weight);

        $aMajPos = array();
        $aMajLabel = array();

        for($i=1; $i<=$nbrmaj; ++$i) {
            $xt=round($i*$maj_step_abs*cos($aAxisAngle))+$aScale->scale_abs[0];
            $yt=$aPos-round($i*$maj_step_abs*sin($aAxisAngle));

            if( $this->label_formfunc != '' ) {
                $f=$this->label_formfunc;
                $l = call_user_func($f,$label);
            }
            else {
                $l = $label;
            }

            $aMajLabel[]=$l;
            $label += $this->major_step;
            $grid[]=$xt;
            $grid[]=$yt;
            $aMajPos[($i-1)*2]=$xt+2*$dx;
            $aMajPos[($i-1)*2+1]=$yt-$aImg->GetFontheight()/2;
            if( !$this->supress_tickmarks ) {
                if( $this->majcolor != '' ) {
                    $aImg->PushColor($this->majcolor);
                }
                $aImg->Line($xt+$dx,$yt+$dy,$xt-$dx,$yt-$dy);
                if( $this->majcolor != '' ) {
                    $aImg->PopColor();
                }
            }
        }

        // Draw minor ticks
        $ticklen2=$this->minor_abs_size;
        $dx=round(sin($aAxisAngle)*$ticklen2);
        $dy=round(cos($aAxisAngle)*$ticklen2);
        if( !$this->supress_tickmarks && !$this->supress_minor_tickmarks) {
            if( $this->mincolor != '' ) {
                $aImg->PushColor($this->mincolor);
            }
            for($i=1; $i<=$nbrmin; ++$i) {
                if( ($i % $skip) == 0 ) {
                    continue;
                }
                $xt=round($i*$min_step_abs*cos($aAxisAngle))+$aScale->scale_abs[0];
                $yt=$aPos-round($i*$min_step_abs*sin($aAxisAngle));
                $aImg->Line($xt+$dx,$yt+$dy,$xt-$dx,$yt-$dy);
            }
            if( $this->mincolor != '' ) {
                $aImg->PopColor();
            }
        }
    }
}


//===================================================
// CLASS RadarAxis
// Description: Implements axis for the radar graph
//===================================================
class RadarAxis extends AxisPrototype {
    public $title=null;
    private $title_color='navy';
    private $len=0;

    function __construct($img,$aScale,$color=array(0,0,0)) {
        parent::__construct($img,$aScale,$color);
        $this->len = $img->plotheight;
        $this->title = new Text();
        $this->title->SetFont(FF_FONT1,FS_BOLD);
        $this->color = array(0,0,0);
    }

    // Stroke the axis
    // $pos    = Vertical position of axis
    // $aAxisAngle = Axis angle
    // $grid   = Returns an array with positions used to draw the grid
    // $lf   = Label flag, TRUE if the axis should have labels
    function Stroke($pos,$aAxisAngle,&$grid,$title,$lf) {
        $this->img->SetColor($this->color);

        // Determine end points for the axis
        $x=round($this->scale->world_abs_size*cos($aAxisAngle)+$this->scale->scale_abs[0]);
        $y=round($pos-$this->scale->world_abs_size*sin($aAxisAngle));

        // Draw axis
        $this->img->SetColor($this->color);
        $this->img->SetLineWeight($this->weight);
        if( !$this->hide ) {
            $this->img->Line($this->scale->scale_abs[0],$pos,$x,$y);
        }

        $this->scale->ticks->Stroke($this->img,$grid,$pos,$aAxisAngle,$this->scale,$majpos,$majlabel);
        $ncolor=0;
        if( isset($this->ticks_label_colors) ) {
            $ncolor=count($this->ticks_label_colors);
        }

        // Draw labels
        if( $lf && !$this->hide ) {
            $this->img->SetFont($this->font_family,$this->font_style,$this->font_size);
            $this->img->SetTextAlign('left','top');
            $this->img->SetColor($this->label_color);

            // majpos contains (x,y) coordinates for labels
            if( ! $this->hide_labels ) {
                $n = floor(count($majpos)/2);
                for($i=0; $i < $n; ++$i) {
                    // Set specific label color if specified
                    if( $ncolor > 0 ) {
                        $this->img->SetColor($this->ticks_label_colors[$i % $ncolor]);
                    }

                    if( $this->ticks_label != null && isset($this->ticks_label[$i]) ) {
                        $this->img->StrokeText($majpos[$i*2],$majpos[$i*2+1],$this->ticks_label[$i]);
                    }
                    else {
                        $this->img->StrokeText($majpos[$i*2],$majpos[$i*2+1],$majlabel[$i]);
                    }
                }
            }
        }
        $this->_StrokeAxisTitle($pos,$aAxisAngle,$title);
    }

    function _StrokeAxisTitle($pos,$aAxisAngle,$title) {
        $this->title->Set($title);
        $marg=6+$this->title->margin;
        $xt=round(($this->scale->world_abs_size+$marg)*cos($aAxisAngle)+$this->scale->scale_abs[0]);
        $yt=round($pos-($this->scale->world_abs_size+$marg)*sin($aAxisAngle));

        // Position the axis title.
        // dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
        // that intersects with the extension of the corresponding axis. The code looks a little
        // bit messy but this is really the only way of having a reasonable position of the
        // axis titles.
        if( $this->title->iWordwrap > 0 ) {
            $title = wordwrap($title,$this->title->iWordwrap,"\n");
        }

        $h=$this->img->GetTextHeight($title)*1.2;
        $w=$this->img->GetTextWidth($title)*1.2;

        while( $aAxisAngle > 2*M_PI )
            $aAxisAngle -= 2*M_PI;

        // Around 3 a'clock
        if( $aAxisAngle>=7*M_PI/4 || $aAxisAngle <= M_PI/4 ) $dx=-0.15; // Small trimming to make the dist to the axis more even

        // Around 12 a'clock
        if( $aAxisAngle>=M_PI/4 && $aAxisAngle <= 3*M_PI/4 ) $dx=($aAxisAngle-M_PI/4)*2/M_PI;

        // Around 9 a'clock
        if( $aAxisAngle>=3*M_PI/4 && $aAxisAngle <= 5*M_PI/4 ) $dx=1;

        // Around 6 a'clock
        if( $aAxisAngle>=5*M_PI/4 && $aAxisAngle <= 7*M_PI/4 ) $dx=(1-($aAxisAngle-M_PI*5/4)*2/M_PI);

        if( $aAxisAngle>=7*M_PI/4 ) $dy=(($aAxisAngle-M_PI)-3*M_PI/4)*2/M_PI;
        if( $aAxisAngle<=M_PI/12 ) $dy=(0.5-$aAxisAngle*2/M_PI);
        if( $aAxisAngle<=M_PI/4 && $aAxisAngle > M_PI/12) $dy=(1-$aAxisAngle*2/M_PI);
        if( $aAxisAngle>=M_PI/4 && $aAxisAngle <= 3*M_PI/4 ) $dy=1;
        if( $aAxisAngle>=3*M_PI/4 && $aAxisAngle <= 5*M_PI/4 ) $dy=(1-($aAxisAngle-3*M_PI/4)*2/M_PI);
        if( $aAxisAngle>=5*M_PI/4 && $aAxisAngle <= 7*M_PI/4 ) $dy=0;

        if( !$this->hide ) {
            $this->title->Stroke($this->img,$xt-$dx*$w,$yt-$dy*$h,$title);
        }
    }

} // Class


//===================================================
// CLASS RadarGrid
// Description: Draws grid for the radar graph
//===================================================
class RadarGrid { //extends Grid {
    private $type='solid';
    private $grid_color='#DDDDDD';
    private $show=false, $weight=1;

    function __construct() {
        // Empty
    }

    function SetColor($aMajColor) {
        $this->grid_color = $aMajColor;
    }

    function SetWeight($aWeight) {
        $this->weight=$aWeight;
    }

    // Specify if grid should be dashed, dotted or solid
    function SetLineStyle($aType) {
        $this->type = $aType;
    }

    // Decide if both major and minor grid should be displayed
    function Show($aShowMajor=true) {
        $this->show=$aShowMajor;
    }

    function Stroke($img,$grid) {
        if( !$this->show ) {
            return;
        }

        $nbrticks = count($grid[0])/2;
        $nbrpnts = count($grid);
        $img->SetColor($this->grid_color);
        $img->SetLineWeight($this->weight);

        for($i=0; $i<$nbrticks; ++$i) {
            for($j=0; $j<$nbrpnts; ++$j) {
                $pnts[$j*2]=$grid[$j][$i*2];
                $pnts[$j*2+1]=$grid[$j][$i*2+1];
            }
            for($k=0; $k<$nbrpnts; ++$k ){
                $l=($k+1)%$nbrpnts;
                if( $this->type == 'solid' )
                    $img->Line($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1]);
                elseif( $this->type == 'dotted' )
                    $img->DashedLine($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1],1,6);
                elseif( $this->type == 'dashed' )
                    $img->DashedLine($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1],2,4);
                elseif( $this->type == 'longdashed' )
                    $img->DashedLine($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1],8,6);
            }
            $pnts=array();
        }
    }
} // Class


//===================================================
// CLASS RadarPlot
// Description: Plot a radarplot
//===================================================
class RadarPlot {
    public $mark=null;
    public $legend='';
    public $legendcsimtarget='';
    public $legendcsimalt='';
    public $csimtargets=array(); // Array of targets for CSIM
    public $csimareas="";   // Resultant CSIM area tags
    public $csimalts=null;   // ALT:s for corresponding target
    private $data=array();
    private $fill=false, $fill_color=array(200,170,180);
    private $color=array(0,0,0);
    private $weight=1;
    private $linestyle='solid';

    //---------------
    // CONSTRUCTOR
    function __construct($data) {
        $this->data = $data;
        $this->mark = new PlotMark();
    }

    function Min() {
        return Min($this->data);
    }

    function Max() {
        return Max($this->data);
    }

    function SetLegend($legend) {
        $this->legend=$legend;
    }

    function SetLineStyle($aStyle) {
        $this->linestyle=$aStyle;
    }

    function SetLineWeight($w) {
        $this->weight=$w;
    }

    function SetFillColor($aColor) {
        $this->fill_color = $aColor;
        $this->fill = true;
    }

    function SetFill($f=true) {
        $this->fill = $f;
    }

    function SetColor($aColor,$aFillColor=false) {
        $this->color = $aColor;
        if( $aFillColor ) {
            $this->SetFillColor($aFillColor);
            $this->fill = true;
        }
    }

    // Set href targets for CSIM
    function SetCSIMTargets($aTargets,$aAlts=null) {
        $this->csimtargets=$aTargets;
        $this->csimalts=$aAlts;
    }

    // Get all created areas
    function GetCSIMareas() {
        return $this->csimareas;
    }

    function Stroke($img, $pos, $scale, $startangle) {
        $nbrpnts = count($this->data);
        $astep=2*M_PI/$nbrpnts;
        $a=$startangle;

        for($i=0; $i<$nbrpnts; ++$i) {

            // Rotate each non null point to the correct axis-angle
            $cs=$scale->RelTranslate($this->data[$i]);
            $x=round($cs*cos($a)+$scale->scale_abs[0]);
            $y=round($pos-$cs*sin($a));

            $pnts[$i*2]=$x;
            $pnts[$i*2+1]=$y;

            // If the next point is null then we draw this polygon segment
            // to the center, skip the next and draw the next segment from
            // the center up to the point on the axis with the first non-null
            // value and continues from that point. Some additoinal logic is necessary
            // to handle the boundary conditions
            if( $i < $nbrpnts-1 ) {
                if( is_null($this->data[$i+1]) ) {
                    $cs = 0;
                    $x=round($cs*cos($a)+$scale->scale_abs[0]);
                    $y=round($pos-$cs*sin($a));
                    $pnts[$i*2]=$x;
                    $pnts[$i*2+1]=$y;
                    $a += $astep;
                }
            }

            $a += $astep;
        }

        if( $this->fill ) {
            $img->SetColor($this->fill_color);
            $img->FilledPolygon($pnts);
        }

        $img->SetLineWeight($this->weight);
        $img->SetColor($this->color);
        $img->SetLineStyle($this->linestyle);
        $pnts[] = $pnts[0];
        $pnts[] = $pnts[1];
        $img->Polygon($pnts);
        $img->SetLineStyle('solid'); // Reset line style to default

        // Add plotmarks on top
        if( $this->mark->show ) {
			for($i=0; $i < $nbrpnts; ++$i) {
	            if( isset($this->csimtargets[$i]) ) {
	                $this->mark->SetCSIMTarget($this->csimtargets[$i]);
	                $this->mark->SetCSIMAlt($this->csimalts[$i]);
	                $this->mark->SetCSIMAltVal($pnts[$i*2], $pnts[$i*2+1]);
	                $this->mark->Stroke($img, $pnts[$i*2], $pnts[$i*2+1]);
	                $this->csimareas .= $this->mark->GetCSIMAreas();
	            }
	            else {
					$this->mark->Stroke($img,$pnts[$i*2],$pnts[$i*2+1]);
	            }
            }
        }

    }

    function GetCount() {
        return count($this->data);
    }

    function Legend($graph) {
        if( $this->legend == '' ) {
            return;
        }
        if( $this->fill ) {
            $graph->legend->Add($this->legend,$this->fill_color,$this->mark);
        } else {
            $graph->legend->Add($this->legend,$this->color,$this->mark);
        }
    }

} // Class

//===================================================
// CLASS RadarGraph
// Description: Main container for a radar graph
//===================================================
class RadarGraph extends Graph {
    public $grid,$axis=null;
    private $posx,$posy;
    private $len;
    private $axis_title=null;

    function __construct($width=300,$height=200,$cachedName="",$timeout=0,$inline=1) {
        parent::__construct($width,$height,$cachedName,$timeout,$inline);
        $this->posx = $width/2;
        $this->posy = $height/2;
        $this->len = min($width,$height)*0.35;
        $this->SetColor(array(255,255,255));
        $this->SetTickDensity(TICKD_NORMAL);
        $this->SetScale('lin');
        $this->SetGridDepth(DEPTH_FRONT);
    }

    function HideTickMarks($aFlag=true) {
        $this->axis->scale->ticks->SupressTickMarks($aFlag);
    }

    function ShowMinorTickmarks($aFlag=true) {
        $this->yscale->ticks->SupressMinorTickMarks(!$aFlag);
    }

    function SetScale($axtype,$ymin=1,$ymax=1,$dummy1=null,$dumy2=null) {
        if( $axtype != 'lin' && $axtype != 'log' ) {
            JpGraphError::RaiseL(18003,$axtype);
            //("Illegal scale for radarplot ($axtype). Must be \"lin\" or \"log\"");
        }
        if( $axtype == 'lin' ) {
            $this->yscale = new LinearScale($ymin,$ymax);
            $this->yscale->ticks = new RadarLinearTicks();
            $this->yscale->ticks->SupressMinorTickMarks();
        }
        elseif( $axtype == 'log' ) {
            $this->yscale = new LogScale($ymin,$ymax);
            $this->yscale->ticks = new RadarLogTicks();
        }

        $this->axis = new RadarAxis($this->img,$this->yscale);
        $this->grid = new RadarGrid();
    }

    function SetSize($aSize) {
        if( $aSize < 0.1 || $aSize>1 ) {
            JpGraphError::RaiseL(18004,$aSize);
            //("Radar Plot size must be between 0.1 and 1. (Your value=$s)");
        }
        $this->len=min($this->img->width,$this->img->height)*$aSize/2;
    }

    function SetPlotSize($aSize) {
        $this->SetSize($aSize);
    }

    function SetTickDensity($densy=TICKD_NORMAL,$dummy1=null) {
        $this->ytick_factor=25;
        switch( $densy ) {
            case TICKD_DENSE:
                $this->ytick_factor=12;
                break;
            case TICKD_NORMAL:
                $this->ytick_factor=25;
                break;
            case TICKD_SPARSE:
                $this->ytick_factor=40;
                break;
            case TICKD_VERYSPARSE:
                $this->ytick_factor=70;
                break;
            default:
                JpGraphError::RaiseL(18005,$densy);
                //("RadarPlot Unsupported Tick density: $densy");
        }
    }

    function SetPos($px,$py=0.5) {
        $this->SetCenter($px,$py);
    }

    function SetCenter($px,$py=0.5) {
        if( $px >= 0 && $px <= 1 ) {
        	$this->posx = $this->img->width*$px;
        }
        else {
        	$this->posx = $px;
        }
        if( $py >= 0 && $py <= 1 ) {
        	$this->posy = $this->img->height*$py;
        }
        else {
        	$this->posy = $py;
        }
    }

    function SetColor($aColor) {
        $this->SetMarginColor($aColor);
    }

    function SetTitles($aTitleArray) {
        $this->axis_title = $aTitleArray;
    }

    function Add($aPlot) {
    	if( $aPlot == null ) {
            JpGraphError::RaiseL(25010);//("Graph::Add() You tried to add a null plot to the graph.");
        }
        if( is_array($aPlot) && count($aPlot) > 0 ) {
            $cl = $aPlot[0];
        }
        else {
            $cl = $aPlot;
        }

        if( $cl instanceof Text ) $this->AddText($aPlot);
        elseif( class_exists('IconPlot',false) && ($cl instanceof IconPlot) ) $this->AddIcon($aPlot);
        else {
            $this->plots[] = $aPlot;
        }
    }

    function GetPlotsYMinMax($aPlots) {
        $min=$aPlots[0]->Min();
        $max=$aPlots[0]->Max();
        foreach( $this->plots as $p ) {
            $max=max($max,$p->Max());
            $min=min($min,$p->Min());
        }
        if( $min < 0 ) {
            JpGraphError::RaiseL(18006,$min);
            //("Minimum data $min (Radar plots should only be used when all data points > 0)");
        }
        return array($min,$max);
    }

    function StrokeIcons() {
    	if( $this->iIcons != null ) {
        	$n = count($this->iIcons);
        	for( $i=0; $i < $n; ++$i ) {
            	$this->iIcons[$i]->Stroke($this->img);
        	}
    	}
    }

	function StrokeTexts() {
        if( $this->texts != null ) {
			$n = count($this->texts);
            for( $i=0; $i < $n; ++$i ) {
                $this->texts[$i]->Stroke($this->img);
            }
        }
    }

    // Stroke the Radar graph
    function Stroke($aStrokeFileName='') {

        // If the filename is the predefined value = '_csim_special_'
        // we assume that the call to stroke only needs to do enough
        // to correctly generate the CSIM maps.
        // We use this variable to skip things we don't strictly need
        // to do to generate the image map to improve performance
        // a best we can. Therefor you will see a lot of tests !$_csim in the
        // code below.
        $_csim = ( $aStrokeFileName === _CSIM_SPECIALFILE );

        // We need to know if we have stroked the plot in the
        // GetCSIMareas. Otherwise the CSIM hasn't been generated
        // and in the case of GetCSIM called before stroke to generate
        // CSIM without storing an image to disk GetCSIM must call Stroke.
        $this->iHasStroked = true;

        $n = count($this->plots);
        // Set Y-scale

        if( !$this->yscale->IsSpecified() && count($this->plots) > 0 ) {
            list($min,$max) = $this->GetPlotsYMinMax($this->plots);
            $this->yscale->AutoScale($this->img,0,$max,$this->len/$this->ytick_factor);
        }
        elseif( $this->yscale->IsSpecified() &&
                ( $this->yscale->auto_ticks || !$this->yscale->ticks->IsSpecified()) ) {

            // The tick calculation will use the user suplied min/max values to determine
            // the ticks. If auto_ticks is false the exact user specifed min and max
            // values will be used for the scale.
            // If auto_ticks is true then the scale might be slightly adjusted
            // so that the min and max values falls on an even major step.
            $min = $this->yscale->scale[0];
            $max = $this->yscale->scale[1];
            $this->yscale->AutoScale($this->img,$min,$max,
                                     $this->len/$this->ytick_factor,
                                     $this->yscale->auto_ticks);
        }

        // Set start position end length of scale (in absolute pixels)
        $this->yscale->SetConstants($this->posx,$this->len);

        // We need as many axis as there are data points
        $nbrpnts=$this->plots[0]->GetCount();

        // If we have no titles just number the axis 1,2,3,...
        if( $this->axis_title==null ) {
            for($i=0; $i < $nbrpnts; ++$i ) {
                $this->axis_title[$i] = $i+1;
            }
        }
        elseif( count($this->axis_title) < $nbrpnts) {
            JpGraphError::RaiseL(18007);
            // ("Number of titles does not match number of points in plot.");
        }
        for( $i=0; $i < $n; ++$i ) {
            if( $nbrpnts != $this->plots[$i]->GetCount() ) {
                JpGraphError::RaiseL(18008);
                //("Each radar plot must have the same number of data points.");
            }
        }

        if( !$_csim ) {
        	if( $this->background_image != '' ) {
            	$this->StrokeFrameBackground();
        	}
        	else {
            	$this->StrokeFrame();
            	$this->StrokeBackgroundGrad();
        	}
        }
        $astep=2*M_PI/$nbrpnts;

		if( !$_csim ) {
     		if( $this->iIconDepth == DEPTH_BACK ) {
        		$this->StrokeIcons();
        	}


	        // Prepare legends
    	    for($i=0; $i < $n; ++$i) {
        	    $this->plots[$i]->Legend($this);
	        }
    	    $this->legend->Stroke($this->img);
        	$this->footer->Stroke($this->img);
		}

		if( !$_csim ) {
	        if( $this->grid_depth == DEPTH_BACK ) {
	            // Draw axis and grid
	            for( $i=0,$a=M_PI/2; $i < $nbrpnts; ++$i, $a += $astep ) {
	                $this->axis->Stroke($this->posy,$a,$grid[$i],$this->axis_title[$i],$i==0);
	            }
                $this->grid->Stroke($this->img,$grid);
	        }
            if( $this->iIconDepth == DEPTH_BACK ) {
                $this->StrokeIcons();
            }

		}

        // Plot points
        $a=M_PI/2;
        for($i=0; $i < $n; ++$i ) {
            $this->plots[$i]->Stroke($this->img, $this->posy, $this->yscale, $a);
        }

        if( !$_csim ) {
            if( $this->grid_depth != DEPTH_BACK ) {
                // Draw axis and grid
                for( $i=0,$a=M_PI/2; $i < $nbrpnts; ++$i, $a += $astep ) {
                   $this->axis->Stroke($this->posy,$a,$grid[$i],$this->axis_title[$i],$i==0);
                }
                $this->grid->Stroke($this->img,$grid);
            }

        	$this->StrokeTitles();
       		$this->StrokeTexts();
       		if( $this->iIconDepth == DEPTH_FRONT ) {
        		$this->StrokeIcons();
        	}
		}

        // Should we do any final image transformation
        if( $this->iImgTrans && !$_csim ) {
            if( !class_exists('ImgTrans',false) ) {
                require_once('jpgraph_imgtrans.php');
            }

            $tform = new ImgTrans($this->img->img);
            $this->img->img = $tform->Skew3D($this->iImgTransHorizon,$this->iImgTransSkewDist,
            $this->iImgTransDirection,$this->iImgTransHighQ,
            $this->iImgTransMinSize,$this->iImgTransFillColor,
            $this->iImgTransBorder);
        }

		if( !$_csim ) {
	        // If the filename is given as the special "__handle"
	        // then the image handler is returned and the image is NOT
	        // streamed back
	        if( $aStrokeFileName == _IMG_HANDLER ) {
	            return $this->img->img;
	        }
	        else {
	            // Finally stream the generated picture
	            $this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,$aStrokeFileName);
	        }
		}
    }
} // Class

/* EOF */
?>
