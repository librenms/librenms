<?php
/*=======================================================================
// File:        JPGRAPH_MESHINTERPOLATE.INC.PHP
// Description: Utility class to do mesh linear interpolation of a matrix
// Created:     2009-03-09
// Ver:         $Id: jpgraph_meshinterpolate.inc.php 1709 2009-07-30 08:00:08Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================
*/
  
/**
* Utility function to do linear mesh interpolation
* @param $aDat Matrix to interpolate
* @param $aFactor Interpolation factor  
*/
function doMeshInterpolate( &$aData, $aFactor ) {
    $m = new MeshInterpolate();
    $aData = $m->Linear($aData,$aFactor);
}

/**
 * Utility class to interpolate a given data matrix
 *
 */
class MeshInterpolate {
    private $data = array();

   /**
    * Calculate the mid points of the given rectangle which has its top left
    * corner at $row,$col. The $aFactordecides how many spliots should be done.
    * i.e. how many more divisions should be done recursively
    *
    * @param $row Top left corner of square to work with
    * @param $col Top left corner of square to work with
    * $param $aFactor In how many subsquare should we split this square. A value of 1 indicates that no action
    */
    function IntSquare( $aRow, $aCol, $aFactor ) {
        if ( $aFactor <= 1 )
            return;

        $step = pow( 2, $aFactor-1 );

        $v0 = $this->data[$aRow][$aCol];
        $v1 = $this->data[$aRow][$aCol + $step];
        $v2 = $this->data[$aRow + $step][$aCol];
        $v3 = $this->data[$aRow + $step][$aCol + $step];

        $this->data[$aRow][$aCol + $step / 2] = ( $v0 + $v1 ) / 2;
        $this->data[$aRow + $step / 2][$aCol] = ( $v0 + $v2 ) / 2;
        $this->data[$aRow + $step][$aCol + $step / 2] = ( $v2 + $v3 ) / 2;
        $this->data[$aRow + $step / 2][$aCol + $step] = ( $v1 + $v3 ) / 2;
        $this->data[$aRow + $step / 2][$aCol + $step / 2] = ( $v0 + $v1 + $v2 + $v3 ) / 4;

        $this->IntSquare( $aRow, $aCol, $aFactor-1 );
        $this->IntSquare( $aRow, $aCol + $step / 2, $aFactor-1 );
        $this->IntSquare( $aRow + $step / 2, $aCol, $aFactor-1 );
        $this->IntSquare( $aRow + $step / 2, $aCol + $step / 2, $aFactor-1 );
    }

    /**
     * Interpolate values in a matrice so that the total number of data points
     * in vert and horizontal axis are $aIntNbr more. For example $aIntNbr=2 will
     * make the data matrice have tiwce as many vertical and horizontal dta points.
     *
     * Note: This will blow up the matrcide in memory size in the order of $aInNbr^2
     *
     * @param  $ &$aData The original data matricde
     * @param  $aInNbr Interpolation factor
     * @return the interpolated matrice
     */
    function Linear( &$aData, $aIntFactor ) {
        $step = pow( 2, $aIntFactor-1 );

        $orig_cols = count( $aData[0] );
        $orig_rows = count( $aData );
        // Number of new columns/rows
        // N = (a-1) * 2^(f-1) + 1
        $p = pow( 2, $aIntFactor-1 );
        $new_cols = $p * ( $orig_cols - 1 ) + 1;
        $new_rows = $p * ( $orig_rows - 1 ) + 1;

        $this->data = array_fill( 0, $new_rows, array_fill( 0, $new_cols, 0 ) );
        // Initialize the new matrix with the values that we know
        for ( $i = 0; $i < $new_rows; $i++ ) {
            for ( $j = 0; $j < $new_cols; $j++ ) {
                $v = 0 ;
                if ( ( $i % $step == 0 ) && ( $j % $step == 0 ) ) {
                    $v = $aData[$i / $step][$j / $step];
                }
                $this->data[$i][$j] = $v;
            }
        }

        for ( $i = 0; $i < $new_rows-1; $i += $step ) {
            for ( $j = 0; $j < $new_cols-1; $j += $step ) {
                $this->IntSquare( $i, $j, $aIntFactor );
            }
        }

        return $this->data;
    }
}
  
?>
