<?php
namespace Amenadiel\JpGraph\Plot;

use Amenadiel\JpGraph\Image;

/*=======================================================================
// File:        JPGRAPH_CONTOUR.PHP
// Description: Contour plot
// Created:     2009-03-08
// Ver:         $Id: jpgraph_contour.php 1870 2009-09-29 04:24:18Z ljp $
//
// Copyright (c) Asial Corporation. All rights reserved.
//========================================================================
 */

define('HORIZ_EDGE', 0);
define('VERT_EDGE', 1);

/**
 * This class encapsulates the core contour plot algorithm. It will find the path
 * of the specified isobars in the data matrix specified. It is assumed that the
 * data matrix models an equspaced X-Y mesh of datavalues corresponding to the Z
 * values.
 *
 */
class Contour
{

    private $dataPoints = array();
    private $nbrCols = 0, $nbrRows = 0;
    private $horizEdges = array(), $vertEdges = array();
    private $isobarValues = array();
    private $stack = null;
    private $isobarCoord = array();
    private $nbrIsobars = 10, $isobarColors = array();
    private $invert = true;
    private $highcontrast = false, $highcontrastbw = false;

    /**
     * Create a new contour level "algorithm machine".
     * @param $aMatrix    The values to find the contour from
     * @param $aIsobars Mixed. If integer it determines the number of isobars to be used. The levels are determined
     * automatically as equdistance between the min and max value of the matrice.
     * If $aIsobars is an array then this is interpretated as an array of values to be used as isobars in the
     * contour plot.
     * @return an instance of the contour algorithm
     */
    public function __construct($aMatrix, $aIsobars = 10, $aColors = null)
    {

        $this->nbrRows = count($aMatrix);
        $this->nbrCols = count($aMatrix[0]);
        $this->dataPoints = $aMatrix;

        if (is_array($aIsobars)) {
            // use the isobar values supplied
            $this->nbrIsobars = count($aIsobars);
            $this->isobarValues = $aIsobars;
        } else {
            // Determine the isobar values automatically
            $this->nbrIsobars = $aIsobars;
            list($min, $max) = $this->getMinMaxVal();
            $stepSize = ($max - $min) / $aIsobars;
            $isobar = $min + $stepSize / 2;
            for ($i = 0; $i < $aIsobars; $i++) {
                $this->isobarValues[$i] = $isobar;
                $isobar += $stepSize;
            }
        }

        if ($aColors !== null && count($aColors) > 0) {

            if (!is_array($aColors)) {
                Util\JpGraphError::RaiseL(28001);
                //'Third argument to Contour must be an array of colors.'
            }

            if (count($aColors) != count($this->isobarValues)) {
                Util\JpGraphError::RaiseL(28002);
                //'Number of colors must equal the number of isobar lines specified';
            }

            $this->isobarColors = $aColors;
        }
    }

    /**
     * Flip the plot around the Y-coordinate. This has the same affect as flipping the input
     * data matrice
     *
     * @param $aFlg If true the the vertice in input data matrice position (0,0) corresponds to the top left
     * corner of teh plot otherwise it will correspond to the bottom left corner (a horizontal flip)
     */
    public function SetInvert($aFlg = true)
    {
        $this->invert = $aFlg;
    }

    /**
     * Find the min and max values in the data matrice
     *
     * @return array(min_value,max_value)
     */
    public function getMinMaxVal()
    {
        $min = $this->dataPoints[0][0];
        $max = $this->dataPoints[0][0];
        for ($i = 0; $i < $this->nbrRows; $i++) {
            if (($mi = min($this->dataPoints[$i])) < $min) {
                $min = $mi;
            }

            if (($ma = max($this->dataPoints[$i])) > $max) {
                $max = $ma;
            }

        }
        return array($min, $max);
    }

    /**
     * Reset the two matrices that keeps track on where the isobars crosses the
     * horizontal and vertical edges
     */
    public function resetEdgeMatrices()
    {
        for ($k = 0; $k < 2; $k++) {
            for ($i = 0; $i <= $this->nbrRows; $i++) {
                for ($j = 0; $j <= $this->nbrCols; $j++) {
                    $this->edges[$k][$i][$j] = false;
                }
            }
        }
    }

    /**
     * Determine if the specified isobar crosses the horizontal edge specified by its row and column
     *
     * @param $aRow Row index of edge to be checked
     * @param $aCol Col index of edge to be checked
     * @param $aIsobar Isobar value
     * @return true if the isobar is crossing this edge
     */
    public function isobarHCrossing($aRow, $aCol, $aIsobar)
    {

        if ($aCol >= $this->nbrCols - 1) {
            Util\JpGraphError::RaiseL(28003, $aCol);
            //'ContourPlot Internal Error: isobarHCrossing: Coloumn index too large (%d)'
        }
        if ($aRow >= $this->nbrRows) {
            Util\JpGraphError::RaiseL(28004, $aRow);
            //'ContourPlot Internal Error: isobarHCrossing: Row index too large (%d)'
        }

        $v1 = $this->dataPoints[$aRow][$aCol];
        $v2 = $this->dataPoints[$aRow][$aCol + 1];

        return ($aIsobar - $v1) * ($aIsobar - $v2) < 0;

    }

    /**
     * Determine if the specified isobar crosses the vertical edge specified by its row and column
     *
     * @param $aRow Row index of edge to be checked
     * @param $aCol Col index of edge to be checked
     * @param $aIsobar Isobar value
     * @return true if the isobar is crossing this edge
     */
    public function isobarVCrossing($aRow, $aCol, $aIsobar)
    {

        if ($aRow >= $this->nbrRows - 1) {
            Util\JpGraphError::RaiseL(28005, $aRow);
            //'isobarVCrossing: Row index too large
        }
        if ($aCol >= $this->nbrCols) {
            Util\JpGraphError::RaiseL(28006, $aCol);
            //'isobarVCrossing: Col index too large
        }

        $v1 = $this->dataPoints[$aRow][$aCol];
        $v2 = $this->dataPoints[$aRow + 1][$aCol];

        return ($aIsobar - $v1) * ($aIsobar - $v2) < 0;

    }

    /**
     * Determine all edges, horizontal and vertical that the specified isobar crosses. The crossings
     * are recorded in the two edge matrices.
     *
     * @param $aIsobar The value of the isobar to be checked
     */
    public function determineIsobarEdgeCrossings($aIsobar)
    {

        $ib = $this->isobarValues[$aIsobar];

        for ($i = 0; $i < $this->nbrRows - 1; $i++) {
            for ($j = 0; $j < $this->nbrCols - 1; $j++) {
                $this->edges[HORIZ_EDGE][$i][$j] = $this->isobarHCrossing($i, $j, $ib);
                $this->edges[VERT_EDGE][$i][$j] = $this->isobarVCrossing($i, $j, $ib);
            }
        }

        // We now have the bottom and rightmost edges unsearched
        for ($i = 0; $i < $this->nbrRows - 1; $i++) {
            $this->edges[VERT_EDGE][$i][$j] = $this->isobarVCrossing($i, $this->nbrCols - 1, $ib);
        }
        for ($j = 0; $j < $this->nbrCols - 1; $j++) {
            $this->edges[HORIZ_EDGE][$i][$j] = $this->isobarHCrossing($this->nbrRows - 1, $j, $ib);
        }

    }

    /**
     * Return the normalized coordinates for the crossing of the specified edge with the specified
     * isobar- The crossing is simpy detrmined with a linear interpolation between the two vertices
     * on each side of the edge and the value of the isobar
     *
     * @param $aRow Row of edge
     * @param $aCol Column of edge
     * @param $aEdgeDir Determine if this is a horizontal or vertical edge
     * @param $ib The isobar value
     * @return unknown_type
     */
    public function getCrossingCoord($aRow, $aCol, $aEdgeDir, $aIsobarVal)
    {

        // In order to avoid numerical problem when two vertices are very close
        // we have to check and avoid dividing by close to zero denumerator.
        if ($aEdgeDir == HORIZ_EDGE) {
            $d = abs($this->dataPoints[$aRow][$aCol] - $this->dataPoints[$aRow][$aCol + 1]);
            if ($d > 0.001) {
                $xcoord = $aCol + abs($aIsobarVal - $this->dataPoints[$aRow][$aCol]) / $d;
            } else {
                $xcoord = $aCol;
            }
            $ycoord = $aRow;
        } else {
            $d = abs($this->dataPoints[$aRow][$aCol] - $this->dataPoints[$aRow + 1][$aCol]);
            if ($d > 0.001) {
                $ycoord = $aRow + abs($aIsobarVal - $this->dataPoints[$aRow][$aCol]) / $d;
            } else {
                $ycoord = $aRow;
            }
            $xcoord = $aCol;
        }
        if ($this->invert) {
            $ycoord = $this->nbrRows - 1 - $ycoord;
        }
        return array($xcoord, $ycoord);

    }

    /**
     * In order to avoid all kinds of unpleasent extra checks and complex boundary
     * controls for the degenerated case where the contour levels exactly crosses
     * one of the vertices we add a very small delta (0.1%) to the data point value.
     * This has no visible affect but it makes the code sooooo much cleaner.
     *
     */
    public function adjustDataPointValues()
    {

        $ni = count($this->isobarValues);
        for ($k = 0; $k < $ni; $k++) {
            $ib = $this->isobarValues[$k];
            for ($row = 0; $row < $this->nbrRows - 1; ++$row) {
                for ($col = 0; $col < $this->nbrCols - 1; ++$col) {
                    if (abs($this->dataPoints[$row][$col] - $ib) < 0.0001) {
                        $this->dataPoints[$row][$col] += $this->dataPoints[$row][$col] * 0.001;
                    }
                }
            }
        }

    }

    /**
     * @param $aFlg
     * @param $aBW
     * @return unknown_type
     */
    public function UseHighContrastColor($aFlg = true, $aBW = false)
    {
        $this->highcontrast = $aFlg;
        $this->highcontrastbw = $aBW;
    }

    /**
     * Calculate suitable colors for each defined isobar
     *
     */
    public function CalculateColors()
    {
        if ($this->highcontrast) {
            if ($this->highcontrastbw) {
                for ($ib = 0; $ib < $this->nbrIsobars; $ib++) {
                    $this->isobarColors[$ib] = 'black';
                }
            } else {
                // Use only blue/red scale
                $step = round(255 / ($this->nbrIsobars - 1));
                for ($ib = 0; $ib < $this->nbrIsobars; $ib++) {
                    $this->isobarColors[$ib] = array($ib * $step, 50, 255 - $ib * $step);
                }
            }
        } else {
            $n = $this->nbrIsobars;
            $v = 0;
            $step = 1 / ($this->nbrIsobars - 1);
            for ($ib = 0; $ib < $this->nbrIsobars; $ib++) {
                $this->isobarColors[$ib] = Image\RGB::GetSpectrum($v);
                $v += $step;
            }
        }
    }

    /**
     * This is where the main work is done. For each isobar the crossing of the edges are determined
     * and then each cell is analyzed to find the 0, 2 or 4 crossings. Then the normalized coordinate
     * for the crossings are determined and pushed on to the isobar stack. When the method is finished
     * the $isobarCoord will hold one arrayfor each isobar where all the line segments that makes
     * up the contour plot are stored.
     *
     * @return array( $isobarCoord, $isobarValues, $isobarColors )
     */
    public function getIsobars()
    {

        $this->adjustDataPointValues();

        for ($isobar = 0; $isobar < $this->nbrIsobars; $isobar++) {

            $ib = $this->isobarValues[$isobar];
            $this->resetEdgeMatrices();
            $this->determineIsobarEdgeCrossings($isobar);
            $this->isobarCoord[$isobar] = array();

            $ncoord = 0;

            for ($row = 0; $row < $this->nbrRows - 1; ++$row) {
                for ($col = 0; $col < $this->nbrCols - 1; ++$col) {

                    // Find out how many crossings around the edges
                    $n = 0;
                    if ($this->edges[HORIZ_EDGE][$row][$col]) {
                        $neigh[$n++] = array($row, $col, HORIZ_EDGE);
                    }

                    if ($this->edges[HORIZ_EDGE][$row + 1][$col]) {
                        $neigh[$n++] = array($row + 1, $col, HORIZ_EDGE);
                    }

                    if ($this->edges[VERT_EDGE][$row][$col]) {
                        $neigh[$n++] = array($row, $col, VERT_EDGE);
                    }

                    if ($this->edges[VERT_EDGE][$row][$col + 1]) {
                        $neigh[$n++] = array($row, $col + 1, VERT_EDGE);
                    }

                    if ($n == 2) {
                        $n1 = 0;
                        $n2 = 1;
                        $this->isobarCoord[$isobar][$ncoord++] = array(
                            $this->getCrossingCoord($neigh[$n1][0], $neigh[$n1][1], $neigh[$n1][2], $ib),
                            $this->getCrossingCoord($neigh[$n2][0], $neigh[$n2][1], $neigh[$n2][2], $ib));
                    } elseif ($n == 4) {
                        // We must determine how to connect the edges either northwest->southeast or
                        // northeast->southwest. We do that by calculating the imaginary middle value of
                        // the cell by averaging the for corners. This will compared with the value of the
                        // top left corner will help determine the orientation of the ridge/creek
                        $midval = ($this->dataPoints[$row][$col] + $this->dataPoints[$row][$col + 1] + $this->dataPoints[$row + 1][$col] + $this->dataPoints[$row + 1][$col + 1]) / 4;
                        $v = $this->dataPoints[$row][$col];
                        if ($midval == $ib) {
                            // Orientation "+"
                            $n1 = 0;
                            $n2 = 1;
                            $n3 = 2;
                            $n4 = 3;
                        } elseif (($midval > $ib && $v > $ib) || ($midval < $ib && $v < $ib)) {
                            // Orientation of ridge/valley = "\"
                            $n1 = 0;
                            $n2 = 3;
                            $n3 = 2;
                            $n4 = 1;
                        } elseif (($midval > $ib && $v < $ib) || ($midval < $ib && $v > $ib)) {
                            // Orientation of ridge/valley = "/"
                            $n1 = 0;
                            $n2 = 2;
                            $n3 = 3;
                            $n4 = 1;
                        }

                        $this->isobarCoord[$isobar][$ncoord++] = array(
                            $this->getCrossingCoord($neigh[$n1][0], $neigh[$n1][1], $neigh[$n1][2], $ib),
                            $this->getCrossingCoord($neigh[$n2][0], $neigh[$n2][1], $neigh[$n2][2], $ib));

                        $this->isobarCoord[$isobar][$ncoord++] = array(
                            $this->getCrossingCoord($neigh[$n3][0], $neigh[$n3][1], $neigh[$n3][2], $ib),
                            $this->getCrossingCoord($neigh[$n4][0], $neigh[$n4][1], $neigh[$n4][2], $ib));

                    }
                }
            }
        }

        if (count($this->isobarColors) == 0) {
            // No manually specified colors. Calculate them automatically.
            $this->CalculateColors();
        }
        return array($this->isobarCoord, $this->isobarValues, $this->isobarColors);
    }
}

// EOF
