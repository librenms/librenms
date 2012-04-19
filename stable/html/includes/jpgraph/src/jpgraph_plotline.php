<?php
/*=======================================================================
 // File:  		 JPGRAPH_PLOTLINE.PHP
 // Description: PlotLine extension for JpGraph
 // Created:  	 2009-03-24
 // Ver:  		 $Id: jpgraph_plotline.php 1881 2009-10-01 10:28:12Z ljp $
 //
 // CLASS PlotLine
 // Data container class to hold properties for a static
 // line that is drawn directly in the plot area.
 // Useful to add static borders inside a plot to show for example set-values
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

class PlotLine {
    public $scaleposition, $direction=-1;
    protected $weight=1;
    protected $color = 'black';
    private $legend='',$hidelegend=false, $legendcsimtarget='', $legendcsimalt='',$legendcsimwintarget='';
    private $iLineStyle='solid';
    public $numpoints=0; // Needed since the framework expects this property

    function __construct($aDir=HORIZONTAL,$aPos=0,$aColor='black',$aWeight=1) {
        $this->direction = $aDir;
        $this->color=$aColor;
        $this->weight=$aWeight;
        $this->scaleposition=$aPos;
    }

    function SetLegend($aLegend,$aCSIM='',$aCSIMAlt='',$aCSIMWinTarget='') {
        $this->legend = $aLegend;
        $this->legendcsimtarget = $aCSIM;
        $this->legendcsimwintarget = $aCSIMWinTarget;
        $this->legendcsimalt = $aCSIMAlt;
    }

    function HideLegend($f=true) {
        $this->hidelegend = $f;
    }

    function SetPosition($aScalePosition) {
        $this->scaleposition=$aScalePosition;
    }

    function SetDirection($aDir) {
        $this->direction = $aDir;
    }

    function SetColor($aColor) {
        $this->color=$aColor;
    }

    function SetWeight($aWeight) {
        $this->weight=$aWeight;
    }

    function SetLineStyle($aStyle) {
        $this->iLineStyle = $aStyle;
    }

    //---------------
    // PRIVATE METHODS

    function DoLegend($graph) {
        if( !$this->hidelegend ) $this->Legend($graph);
    }

    // Framework function the chance for each plot class to set a legend
    function Legend($aGraph) {
        if( $this->legend != '' ) {
            $dummyPlotMark = new PlotMark();
            $lineStyle = 1;
            $aGraph->legend->Add($this->legend,$this->color,$dummyPlotMark,$lineStyle,
            $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
        }
    }

    function PreStrokeAdjust($aGraph) {
        // Nothing to do
    }

    // Called by framework to allow the object to draw
    // optional information in the margin area
    function StrokeMargin($aImg) {
        // Nothing to do
    }

    // Framework function to allow the object to adjust the scale
    function PrescaleSetup($aGraph) {
        // Nothing to do
    }

    function Min() {
        return array(null,null);
    }

    function Max() {
        return array(null,null);
    }

    function _Stroke($aImg,$aMinX,$aMinY,$aMaxX,$aMaxY,$aXPos,$aYPos) {
        $aImg->SetColor($this->color);
        $aImg->SetLineWeight($this->weight);
        $oldStyle = $aImg->SetLineStyle($this->iLineStyle);
        if( $this->direction == VERTICAL ) {
            $ymin_abs = $aMinY;
            $ymax_abs = $aMaxY;
            $xpos_abs = $aXPos;
            $aImg->StyleLine($xpos_abs, $ymin_abs, $xpos_abs, $ymax_abs);
        }
        elseif( $this->direction == HORIZONTAL ) {
            $xmin_abs = $aMinX;
            $xmax_abs = $aMaxX;
            $ypos_abs = $aYPos;
            $aImg->StyleLine($xmin_abs, $ypos_abs, $xmax_abs, $ypos_abs);
        }
        else {
            JpGraphError::RaiseL(25125);//(" Illegal direction for static line");
        }
        $aImg->SetLineStyle($oldStyle);
    }

    function Stroke($aImg,$aXScale,$aYScale) {
        $this->_Stroke($aImg,
            $aImg->left_margin,
            $aYScale->Translate($aYScale->GetMinVal()),
            $aImg->width-$aImg->right_margin,
            $aYScale->Translate($aYScale->GetMaxVal()),
            $aXScale->Translate($this->scaleposition),
            $aYScale->Translate($this->scaleposition)
        );
    }
}


?>