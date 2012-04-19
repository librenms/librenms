<?php
/*=======================================================================
 // File:        JPGRAPH_SCATTER.PHP
 // Description: Scatter (and impuls) plot extension for JpGraph
 // Created:     2001-02-11
 // Ver:         $Id: jpgraph_scatter.php 1397 2009-06-27 21:34:14Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */
require_once ('jpgraph_plotmark.inc.php');

//===================================================
// CLASS FieldArrow
// Description: Draw an arrow at (x,y) with angle a
//===================================================
class FieldArrow {
    public $iColor='black';
    public $iSize=10;  // Length in pixels for  arrow
    public $iArrowSize = 2;
    private $isizespec = array(
    	array(2,1),array(3,2),array(4,3),array(6,4),array(7,4),array(8,5),array(10,6),array(12,7),array(16,8),array(20,10)
    	);
    function __construct() {
    	// Empty
    }

    function SetSize($aSize,$aArrowSize=2) {
        $this->iSize = $aSize;
        $this->iArrowSize = $aArrowSize;
    }

    function SetColor($aColor) {
        $this->iColor = $aColor;
    }

    function Stroke($aImg,$x,$y,$a) {
        // First rotate the center coordinates
        list($x,$y) = $aImg->Rotate($x,$y);

        $old_origin = $aImg->SetCenter($x,$y);
        $old_a = $aImg->a;
        $aImg->SetAngle(-$a+$old_a);

        $dx = round($this->iSize/2);
        $c = array($x-$dx,$y,$x+$dx,$y);
        $x += $dx;

        list($dx,$dy) = $this->isizespec[$this->iArrowSize];
        $ca = array($x,$y,$x-$dx,$y-$dy,$x-$dx,$y+$dy,$x,$y);

        $aImg->SetColor($this->iColor);
        $aImg->Polygon($c);
        $aImg->FilledPolygon($ca);

        $aImg->SetCenter($old_origin[0],$old_origin[1]);
        $aImg->SetAngle($old_a);
    }
}

//===================================================
// CLASS FieldPlot
// Description: Render a field plot
//===================================================
class FieldPlot extends Plot {
    public $arrow = '';
    private $iAngles = array();
    private $iCallback = '';

    function __construct($datay,$datax,$angles) {
        if( (count($datax) != count($datay)) )
        JpGraphError::RaiseL(20001);//("Fieldplots must have equal number of X and Y points.");
        if( (count($datax) != count($angles)) )
        JpGraphError::RaiseL(20002);//("Fieldplots must have an angle specified for each X and Y points.");

        $this->iAngles = $angles;

        parent::__construct($datay,$datax);
        $this->value->SetAlign('center','center');
        $this->value->SetMargin(15);

        $this->arrow = new FieldArrow();
    }

    function SetCallback($aFunc) {
        $this->iCallback = $aFunc;
    }

    function Stroke($img,$xscale,$yscale) {

        // Remeber base color and size
        $bc = $this->arrow->iColor;
        $bs = $this->arrow->iSize;
        $bas = $this->arrow->iArrowSize;

        for( $i=0; $i<$this->numpoints; ++$i ) {
            // Skip null values
            if( $this->coords[0][$i]==="" )
            continue;

            $f = $this->iCallback;
            if( $f != "" ) {
                list($cc,$cs,$cas) = call_user_func($f,$this->coords[1][$i],$this->coords[0][$i],$this->iAngles[$i]);
                // Fall back on global data if the callback isn't set
                if( $cc  == "" ) $cc = $bc;
                if( $cs  == "" ) $cs = $bs;
                if( $cas == "" ) $cas = $bas;
                $this->arrow->SetColor($cc);
                $this->arrow->SetSize($cs,$cas);
            }

            $xt = $xscale->Translate($this->coords[1][$i]);
            $yt = $yscale->Translate($this->coords[0][$i]);

            $this->arrow->Stroke($img,$xt,$yt,$this->iAngles[$i]);
            $this->value->Stroke($img,$this->coords[0][$i],$xt,$yt);
        }
    }

    // Framework function
    function Legend($aGraph) {
        if( $this->legend != "" ) {
            $aGraph->legend->Add($this->legend,$this->mark->fill_color,$this->mark,0,
            $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
        }
    }
}

//===================================================
// CLASS ScatterPlot
// Description: Render X and Y plots
//===================================================
class ScatterPlot extends Plot {
    public $mark,$link;
    private $impuls = false;
    //---------------
    // CONSTRUCTOR
    function __construct($datay,$datax=false) {
        if( (count($datax) != count($datay)) && is_array($datax)) {
        	JpGraphError::RaiseL(20003);//("Scatterplot must have equal number of X and Y points.");
        }
        parent::__construct($datay,$datax);
        $this->mark = new PlotMark();
        $this->mark->SetType(MARK_SQUARE);
        $this->mark->SetColor($this->color);
        $this->value->SetAlign('center','center');
        $this->value->SetMargin(0);
        $this->link = new LineProperty(1,'black','solid');
        $this->link->iShow = false;
    }

    //---------------
    // PUBLIC METHODS
    function SetImpuls($f=true) {
        $this->impuls = $f;
    }

    function SetStem($f=true) {
        $this->impuls = $f;
    }

    // Combine the scatter plot points with a line
    function SetLinkPoints($aFlag=true,$aColor="black",$aWeight=1,$aStyle='solid') {
    	$this->link->iShow = $aFlag;
    	$this->link->iColor = $aColor;
    	$this->link->iWeight = $aWeight;
    	$this->link->iStyle = $aStyle;
    }

    function Stroke($img,$xscale,$yscale) {

        $ymin=$yscale->scale_abs[0];
        if( $yscale->scale[0] < 0 )
        $yzero=$yscale->Translate(0);
        else
        $yzero=$yscale->scale_abs[0];

        $this->csimareas = '';
        for( $i=0; $i<$this->numpoints; ++$i ) {

            // Skip null values
            if( $this->coords[0][$i]==='' || $this->coords[0][$i]==='-' || $this->coords[0][$i]==='x')
            continue;

            if( isset($this->coords[1]) )
            $xt = $xscale->Translate($this->coords[1][$i]);
            else
            $xt = $xscale->Translate($i);
            $yt = $yscale->Translate($this->coords[0][$i]);


            if( $this->link->iShow && isset($yt_old) ) {
                $img->SetColor($this->link->iColor);
                $img->SetLineWeight($this->link->iWeight);
                $old = $img->SetLineStyle($this->link->iStyle);
                $img->StyleLine($xt_old,$yt_old,$xt,$yt);
                $img->SetLineStyle($old);
            }

            if( $this->impuls ) {
                $img->SetColor($this->color);
                $img->SetLineWeight($this->weight);
                $img->Line($xt,$yzero,$xt,$yt);
            }

            if( !empty($this->csimtargets[$i]) ) {
                if( !empty($this->csimwintargets[$i]) ) {
                    $this->mark->SetCSIMTarget($this->csimtargets[$i],$this->csimwintargets[$i]);
                }
                else {
                    $this->mark->SetCSIMTarget($this->csimtargets[$i]);
                }
                $this->mark->SetCSIMAlt($this->csimalts[$i]);
            }

            if( isset($this->coords[1]) ) {
                $this->mark->SetCSIMAltVal($this->coords[0][$i],$this->coords[1][$i]);
            }
            else {
                $this->mark->SetCSIMAltVal($this->coords[0][$i],$i);
            }

            $this->mark->Stroke($img,$xt,$yt);

            $this->csimareas .= $this->mark->GetCSIMAreas();
            $this->value->Stroke($img,$this->coords[0][$i],$xt,$yt);

            $xt_old = $xt;
            $yt_old = $yt;
        }
    }

    // Framework function
    function Legend($aGraph) {
        if( $this->legend != "" ) {
            $aGraph->legend->Add($this->legend,$this->mark->fill_color,$this->mark,0,
            $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
        }
    }
} // Class
/* EOF */
?>