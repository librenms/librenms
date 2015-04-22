<?php
/*=======================================================================
 // File:        JPGRAPH_REGSTAT.PHP
 // Description: Regression and statistical analysis helper classes
 // Created:     2002-12-01
 // Ver:         $Id: jpgraph_regstat.php 1131 2009-03-11 20:08:24Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

//------------------------------------------------------------------------
// CLASS Spline
// Create a new data array from an existing data array but with more points.
// The new points are interpolated using a cubic spline algorithm
//------------------------------------------------------------------------
class Spline {
    // 3:rd degree polynom approximation

    private $xdata,$ydata;   // Data vectors
    private $y2;   // 2:nd derivate of ydata
    private $n=0;

    function __construct($xdata,$ydata) {
        $this->y2 = array();
        $this->xdata = $xdata;
        $this->ydata = $ydata;

        $n = count($ydata);
        $this->n = $n;
        if( $this->n !== count($xdata) ) {
            JpGraphError::RaiseL(19001);
            //('Spline: Number of X and Y coordinates must be the same');
        }

        // Natural spline 2:derivate == 0 at endpoints
        $this->y2[0]    = 0.0;
        $this->y2[$n-1] = 0.0;
        $delta[0] = 0.0;

        // Calculate 2:nd derivate
        for($i=1; $i < $n-1; ++$i) {
            $d = ($xdata[$i+1]-$xdata[$i-1]);
            if( $d == 0  ) {
                JpGraphError::RaiseL(19002);
                //('Invalid input data for spline. Two or more consecutive input X-values are equal. Each input X-value must differ since from a mathematical point of view it must be a one-to-one mapping, i.e. each X-value must correspond to exactly one Y-value.');
            }
            $s = ($xdata[$i]-$xdata[$i-1])/$d;
            $p = $s*$this->y2[$i-1]+2.0;
            $this->y2[$i] = ($s-1.0)/$p;
            $delta[$i] = ($ydata[$i+1]-$ydata[$i])/($xdata[$i+1]-$xdata[$i]) -
            ($ydata[$i]-$ydata[$i-1])/($xdata[$i]-$xdata[$i-1]);
            $delta[$i] = (6.0*$delta[$i]/($xdata[$i+1]-$xdata[$i-1])-$s*$delta[$i-1])/$p;
        }

        // Backward substitution
        for( $j=$n-2; $j >= 0; --$j ) {
            $this->y2[$j] = $this->y2[$j]*$this->y2[$j+1] + $delta[$j];
        }
    }

    // Return the two new data vectors
    function Get($num=50) {
        $n = $this->n ;
        $step = ($this->xdata[$n-1]-$this->xdata[0]) / ($num-1);
        $xnew=array();
        $ynew=array();
        $xnew[0] = $this->xdata[0];
        $ynew[0] = $this->ydata[0];
        for( $j=1; $j < $num; ++$j ) {
            $xnew[$j] = $xnew[0]+$j*$step;
            $ynew[$j] = $this->Interpolate($xnew[$j]);
        }
        return array($xnew,$ynew);
    }

    // Return a single interpolated Y-value from an x value
    function Interpolate($xpoint) {

        $max = $this->n-1;
        $min = 0;

        // Binary search to find interval
        while( $max-$min > 1 ) {
            $k = ($max+$min) / 2;
            if( $this->xdata[$k] > $xpoint )
            $max=$k;
            else
            $min=$k;
        }

        // Each interval is interpolated by a 3:degree polynom function
        $h = $this->xdata[$max]-$this->xdata[$min];

        if( $h == 0  ) {
            JpGraphError::RaiseL(19002);
            //('Invalid input data for spline. Two or more consecutive input X-values are equal. Each input X-value must differ since from a mathematical point of view it must be a one-to-one mapping, i.e. each X-value must correspond to exactly one Y-value.');
        }


        $a = ($this->xdata[$max]-$xpoint)/$h;
        $b = ($xpoint-$this->xdata[$min])/$h;
        return $a*$this->ydata[$min]+$b*$this->ydata[$max]+
        (($a*$a*$a-$a)*$this->y2[$min]+($b*$b*$b-$b)*$this->y2[$max])*($h*$h)/6.0;
    }
}

//------------------------------------------------------------------------
// CLASS Bezier
// Create a new data array from a number of control points
//------------------------------------------------------------------------
class Bezier {
    /**
     * @author Thomas Despoix, openXtrem company
     * @license released under QPL
     * @abstract Bezier interoplated point generation,
     * computed from control points data sets, based on Paul Bourke algorithm :
     * http://local.wasp.uwa.edu.au/~pbourke/geometry/bezier/index2.html
     */
    private $datax = array();
    private $datay = array();
    private $n=0;

    function __construct($datax, $datay, $attraction_factor = 1) {
        // Adding control point multiple time will raise their attraction power over the curve
        $this->n = count($datax);
        if( $this->n !== count($datay) ) {
            JpGraphError::RaiseL(19003);
            //('Bezier: Number of X and Y coordinates must be the same');
        }
        $idx=0;
        foreach($datax as $datumx) {
            for ($i = 0; $i < $attraction_factor; $i++) {
                $this->datax[$idx++] = $datumx;
            }
        }
        $idx=0;
        foreach($datay as $datumy) {
            for ($i = 0; $i < $attraction_factor; $i++) {
                $this->datay[$idx++] = $datumy;
            }
        }
        $this->n *= $attraction_factor;
    }

    /**
     * Return a set of data points that specifies the bezier curve with $steps points
     * @param $steps Number of new points to return
     * @return array($datax, $datay)
     */
    function Get($steps) {
        $datax = array();
        $datay = array();
        for ($i = 0; $i < $steps; $i++) {
            list($datumx, $datumy) = $this->GetPoint((double) $i / (double) $steps);
            $datax[$i] = $datumx;
            $datay[$i] = $datumy;
        }
         
        $datax[] = end($this->datax);
        $datay[] = end($this->datay);
         
        return array($datax, $datay);
    }

    /**
     * Return one point on the bezier curve. $mu is the position on the curve where $mu is in the
     * range 0 $mu < 1 where 0 is tha start point and 1 is the end point. Note that every newly computed
     * point depends on all the existing points
     * 
     * @param $mu Position on the bezier curve
     * @return array($x, $y)
     */
    function GetPoint($mu) {
        $n = $this->n - 1;
        $k = 0;
        $kn = 0;
        $nn = 0;
        $nkn = 0;
        $blend = 0.0;
        $newx = 0.0;
        $newy = 0.0;

        $muk = 1.0;
        $munk = (double) pow(1-$mu,(double) $n);

        for ($k = 0; $k <= $n; $k++) {
            $nn = $n;
            $kn = $k;
            $nkn = $n - $k;
            $blend = $muk * $munk;
            $muk *= $mu;
            $munk /= (1-$mu);
            while ($nn >= 1) {
                $blend *= $nn;
                $nn--;
                if ($kn > 1) {
                    $blend /= (double) $kn;
                    $kn--;
                }
                if ($nkn > 1) {
                    $blend /= (double) $nkn;
                    $nkn--;
                }
            }
            $newx += $this->datax[$k] * $blend;
            $newy += $this->datay[$k] * $blend;
        }

        return array($newx, $newy);
    }
}

// EOF
?>
