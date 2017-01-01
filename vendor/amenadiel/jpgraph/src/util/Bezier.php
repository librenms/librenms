<?php
namespace Amenadiel\JpGraph\Util;

//------------------------------------------------------------------------
// CLASS Bezier
// Create a new data array from a number of control points
//------------------------------------------------------------------------
class Bezier
{
    /**
     * @author Thomas Despoix, openXtrem company
     * @license released under QPL
     * @abstract Bezier interoplated point generation,
     * computed from control points data sets, based on Paul Bourke algorithm :
     * http://local.wasp.uwa.edu.au/~pbourke/geometry/bezier/index2.html
     */
    private $datax = array();
    private $datay = array();
    private $n = 0;

    public function __construct($datax, $datay, $attraction_factor = 1)
    {
        // Adding control point multiple time will raise their attraction power over the curve
        $this->n = count($datax);
        if ($this->n !== count($datay)) {
            JpGraphError::RaiseL(19003);
            //('Bezier: Number of X and Y coordinates must be the same');
        }
        $idx = 0;
        foreach ($datax as $datumx) {
            for ($i = 0; $i < $attraction_factor; $i++) {
                $this->datax[$idx++] = $datumx;
            }
        }
        $idx = 0;
        foreach ($datay as $datumy) {
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
    public function Get($steps)
    {
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
    public function GetPoint($mu)
    {
        $n = $this->n - 1;
        $k = 0;
        $kn = 0;
        $nn = 0;
        $nkn = 0;
        $blend = 0.0;
        $newx = 0.0;
        $newy = 0.0;

        $muk = 1.0;
        $munk = (double) pow(1 - $mu, (double) $n);

        for ($k = 0; $k <= $n; $k++) {
            $nn = $n;
            $kn = $k;
            $nkn = $n - $k;
            $blend = $muk * $munk;
            $muk *= $mu;
            $munk /= (1 - $mu);
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
