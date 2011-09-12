<?php
require_once '../jpgraph.php';
require_once '../jpgraph_canvas.php';
require_once '../jpgraph_canvtools.php';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test_findpolygon
 *
 * @author ljp
 */
class Findpolygon {
    private $nbrContours=-1;
    public $contourCoord=array();
    private $scale = array(0,6,0,8);

    function flattenEdges($p) {
        $fp=array();
        for ($i = 0 ; $i < count($p) ; $i++) {
            $fp[] = $p[$i][0];
            $fp[] = $p[$i][1];
        }
        return $fp;
    }

    function SetupTestData() {
    //        for($i=0; $i<count($this->contourCoord[0]); ++$i) {
    //            echo '('.$this->contourCoord[0][$i][0][0].','.$this->contourCoord[0][$i][0][1].') -> '.
    //            '('.$this->contourCoord[0][$i][1][0].','.$this->contourCoord[0][$i][1][1].")\n";
    //        }
    //

        $c=0;
        $p[$c] = array(0.6,1, 1,0.5, 2,0.5, 3,0.5, 3.5,1, 3.5,2, 3,2.5, 2,2.5, 1,2.5, 0.5,2, 0.6,1);
        $c++;
        $p[$c] = array(6,0.5, 5.5,1, 5.5,2, 6,2.5);

        $this->nbrContours = $c+1;

        for ($c = 0 ; $c < count($p) ; $c++) {
            $n=count($p[$c]);

            $this->contourCoord[$c][0] = array(array($p[$c][0],$p[$c][1]),array($p[$c][2],$p[$c][3]));
            $k=1;
            for ($i = 0; $i < ($n-4)/2; $i++, $k++) {
                $this->contourCoord[$c][$k] = array($this->contourCoord[$c][$k-1][1], array($p[$c][2*$k+2],$p[$c][2*$k+1+2]));
            }

            // Swap edges order at random
            $n = count($this->contourCoord[$c]);
            for($i=0; $i < floor($n/2); ++$i) {
                $swap1 = rand(0,$n-1);
                $t = $this->contourCoord[$c][$swap1];
                while( $swap1 == ($swap2 = rand(0,$n-1)) )
                    ;
                $this->contourCoord[$c][$swap1] = $this->contourCoord[$c][$swap2];
                $this->contourCoord[$c][$swap2] = $t;
            }

            // Swap vector direction on 1/3 of the edges
            for ($i = 0 ; $i < floor(count($this->contourCoord[$c])/3) ; $i++) {
                $e = rand(0, count($this->contourCoord[$c])-1);
                $edge = $this->contourCoord[$c][$e];
                $v1 = $edge[0]; $v2 = $edge[1];
                $this->contourCoord[$c][$e][0] = $v2;
                $this->contourCoord[$c][$e][1] = $v1;
            }
        }

        $pp = array();
        for($j=0; $j < count($p); ++$j ) {
            for( $i=0; $i < count($p[$j])/2; ++$i ) {
                $pp[$j][$i] = array($p[$j][2*$i],$p[$j][2*$i+1]);
            }
        }
        return $pp;
    }

    function p_edges($v) {
        for ($i = 0 ; $i < count($v) ; $i++) {
            echo "(".$v[$i][0][0].",".$v[$i][0][1].") -> (".$v[$i][1][0].",".$v[$i][1][1].")\n";
        }
        echo "\n";
    }

    function CompareCyclic($a,$b,$forward=true) {

    // We assume disjoint vertices and if last==first this just means
    // that the polygon is closed. For this comparison it must be unique
    // elements
        if( $a[count($a)-1] == $a[0] ) {
            array_pop($a);
        }
        if( $b[count($b)-1] == $b[0] ) {
            array_pop($b);
        }

        $n1 = count($a); $n2 = count($b);
        if( $n1 != $n2 )
            return false;

        $i=0;
        while( ($i < $n2) && ($a[0] != $b[$i]) )
            ++$i;

        if( $i >= $n2 )
            return false;

        $j=0;
        if( $forward ) {
            while( ($j < $n1) && ($a[$j] == $b[$i]) ) {
                $i = ($i + 1) % $n2;
                ++$j;
            }
        }
        else {
            while( ($j < $n1) && ($a[$j] == $b[$i]) ) {
                --$i;
                if( $i < 0 ) {
                    $i = $n2-1;
                }
                ++$j;
            }
        }
        return $j >= $n1;
    }

    function dbg($s) {
    // echo $s."\n";
    }

    function IsVerticeOnBorder($x1,$y1) {
    // Check if the vertice lies on any of the four border
        if( $x1==$this->scale[0] || $x1==$this->scale[1] ) {
            return true;
        }
        if( $y1==$this->scale[2] || $y1==$this->scale[3] ) {
            return true;
        }
        return false;
    }

    function FindPolygons($debug=false) {

        $pol = 0;
        for ($c = 0; $c < $this->nbrContours; $c++) {

            $this->dbg("\n** Searching polygon chain $c ... ");
            $this->dbg("------------------------------------------\n");

            $edges = $this->contourCoord[$c];
            while( count($edges) > 0 ) {

                $edge = array_shift($edges);
                list($x1,$y1) = $edge[0];
                list($x2,$y2) = $edge[1];
                $polygons[$pol]=array(
                    array($x1,$y1),array($x2,$y2)
                );

                $this->dbg("Searching on second vertice.");

                $found=false;
                if( ! $this->IsVerticeOnBorder($x2,$y2) ) {
                    do {

                        $this->dbg(" --Searching on edge: ($x1,$y1)->($x2,$y2)");

                        $found=false;
                        $nn = count($edges);
                        for( $i=0; $i < $nn && !$found; ++$i ) {
                            $edge = $edges[$i];
                            if( $found = ($x2==$edge[0][0] && $y2==$edge[0][1]) ) {
                                $polygons[$pol][] = array($edge[1][0],$edge[1][1]);
                                $x1 = $x2; $y1 = $y2;
                                $x2 = $edge[1][0]; $y2 = $edge[1][1];
                            }
                            elseif( $found = ($x2==$edge[1][0] && $y2==$edge[1][1]) ) {
                                $polygons[$pol][] = array($edge[0][0],$edge[0][1]);
                                $x1 = $x2; $y1 = $y2;
                                $x2 = $edge[0][0]; $y2 = $edge[0][1];
                            }
                            if( $found ) {
                                $this->dbg("    --Found next edge: [i=$i], (%,%) -> ($x2,$y2)");
                                unset($edges[$i]);
                                $edges = array_values($edges);
                            }
                        }

                    } while( $found );
                }

                if( !$found && count($edges)>0 ) {
                    $this->dbg("Searching on first vertice.");
                    list($x1,$y1) = $polygons[$pol][0];
                    list($x2,$y2) = $polygons[$pol][1];

                    if( ! $this->IsVerticeOnBorder($x1,$y1) ) {
                        do {

                            $this->dbg(" --Searching on edge: ($x1,$y1)->($x2,$y2)");

                            $found=false;
                            $nn = count($edges);
                            for( $i=0; $i < $nn && !$found; ++$i ) {
                                $edge = $edges[$i];
                                if( $found = ($x1==$edge[0][0] && $y1==$edge[0][1]) ) {
                                    array_unshift($polygons[$pol],array($edge[1][0],$edge[1][1]));
                                    $x2 = $x1; $y2 = $y1;
                                    $x1 = $edge[1][0]; $y1 = $edge[1][1];
                                }
                                elseif( $found = ($x1==$edge[1][0] && $y1==$edge[1][1]) ) {
                                    array_unshift($polygons[$pol],array($edge[0][0],$edge[0][1]));
                                    $x2 = $x1; $y2 = $y1;
                                    $x1 = $edge[0][0]; $y1 = $edge[0][1];
                                }
                                if( $found ) {
                                    $this->dbg("    --Found next edge: [i=$i], ($x1,$y1) -> (%,%)");
                                    unset($edges[$i]);
                                    $edges = array_values($edges);
                                }
                            }

                        } while( $found );
                    }

                }

                $pol++;
            }
        }

        return $polygons;
    }

}
define('HORIZ_EDGE',0);
define('VERT_EDGE',1);

class FillGridRect {
    private $edges,$dataPoints,$colors,$isoBars;
    private $invert=false;

    function __construct(&$edges,&$dataPoints,$isoBars,$colors) {
        $this->edges = $edges;
        $this->dataPoints = $dataPoints;
        $this->colors = $colors;
        $this->isoBars = $isoBars;
    }

    function GetIsobarColor($val) {
        for ($i = 0 ; $i < count($this->isoBars) ; $i++) {
            if( $val <= $this->isoBars[$i] ) {
                return $this->colors[$i];
            }
        }
        return $this->colors[$i]; // The color for all values above the highest isobar
    }

    function GetIsobarVal($a,$b) {
    // Get the isobar that is between the values a and b
    // If there are more isobars then return the one with lowest index
        if( $b < $a ) {
            $t=$a; $a=$b; $b=$t;
        }
        $i = 0 ;
        $n = count($this->isoBars);
        while( $i < $n && $this->isoBars[$i] < $a ) {
            ++$i;
        }
        if( $i >= $n )
            die("Internal error. Cannot find isobar values for ($a,$b)");
        return $this->isoBars[$i];
    }

    function getCrossingCoord($aRow,$aCol,$aEdgeDir,$aIsobarVal) {
    // In order to avoid numerical problem when two vertices are very close
    // we have to check and avoid dividing by close to zero denumerator.
        if( $aEdgeDir == HORIZ_EDGE ) {
            $d = abs($this->dataPoints[$aRow][$aCol] - $this->dataPoints[$aRow][$aCol+1]);
            if( $d > 0.001 ) {
                $xcoord = $aCol + abs($aIsobarVal - $this->dataPoints[$aRow][$aCol]) / $d;
            }
            else {
                $xcoord = $aCol;
            }
            $ycoord = $aRow;
        }
        else {
            $d = abs($this->dataPoints[$aRow][$aCol] - $this->dataPoints[$aRow+1][$aCol]);
            if( $d > 0.001 ) {
                $ycoord = $aRow + abs($aIsobarVal - $this->dataPoints[$aRow][$aCol]) / $d;
            }
            else {
                $ycoord = $aRow;
            }
            $xcoord = $aCol;
        }
        if( $this->invert ) {
            $ycoord = $this->nbrRows-1 - $ycoord;
        }
        return array($xcoord,$ycoord);
    }

    function Fill(ContCanvas $canvas) {

        $nx_vertices = count($this->dataPoints[0]);
        $ny_vertices = count($this->dataPoints);

        // Loop through all squares in the grid
        for($col=0; $col < $nx_vertices-1; ++$col) {
            for($row=0; $row < $ny_vertices-1; ++$row) {

                $n = 0;$quad_edges=array();
                if ( $this->edges[VERT_EDGE][$row][$col] )    $quad_edges[$n++] = array($row,  $col,  VERT_EDGE);
                if ( $this->edges[VERT_EDGE][$row][$col+1] )  $quad_edges[$n++] = array($row,  $col+1,VERT_EDGE);
                if ( $this->edges[HORIZ_EDGE][$row][$col] )   $quad_edges[$n++] = array($row,  $col,  HORIZ_EDGE);
                if ( $this->edges[HORIZ_EDGE][$row+1][$col] ) $quad_edges[$n++] = array($row+1,$col,  HORIZ_EDGE);

                if( $n == 0 ) {
                // Easy, fill the entire quadrant with one color since we have no crossings
                // Select the top left datapoint as representing this quadrant
                // color for this quadrant
                    $color = $this->GetIsobarColor($this->dataPoints[$row][$col]);
                    $polygon = array($col,$row,$col,$row+1,$col+1,$row+1,$col+1,$row,$col,$row);
                    $canvas->FilledPolygon($polygon,$color);

                } elseif( $n==2 ) {

                // There is one isobar edge crossing this quadrant. In order to fill we need to
                // find out the orientation of the two areas this edge is separating in order to
                // construct the two polygons that define the two areas to be filled
                // There are six possible variants
                // 0) North-South
                // 1) West-East
                // 2) West-North
                // 3) East-North
                // 4) West-South
                // 5) East-South
                    $type=-1;
                    if( $this->edges[HORIZ_EDGE][$row][$col] ) {
                        if( $this->edges[HORIZ_EDGE][$row+1][$col] ) $type=0; // North-South
                        elseif( $this->edges[VERT_EDGE][$row][$col] ) $type=2;
                        elseif( $this->edges[VERT_EDGE][$row][$col+1] ) $type=3;
                    }
                    elseif( $this->edges[HORIZ_EDGE][$row+1][$col] ) {
                        if( $this->edges[VERT_EDGE][$row][$col] ) $type=4;
                        elseif( $this->edges[VERT_EDGE][$row][$col+1] ) $type=5;
                    }
                    else {
                        $type=1;
                    }
                    if( $type==-1 ) {
                        die('Internal error: n=2 but no edges in the quadrant was find to determine type.');
                    }

                    switch( $type ) {
                        case 0: //North-South

                        // North vertice
                            $v1 = $this->dataPoints[$row][$col];
                            $v2 = $this->dataPoints[$row][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x1,$y1) = $this->getCrossingCoord($row, $col,HORIZ_EDGE, $isobarValue);

                            // South vertice
                            $v1 = $this->dataPoints[$row+1][$col];
                            $v2 = $this->dataPoints[$row+1][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x2,$y2) = $this->getCrossingCoord($row+1, $col,HORIZ_EDGE, $isobarValue);

                            $polygon = array($col,$row,$x1,$y1,$x2,$y2,$col,$row+1,$col,$row);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v1));

                            $polygon = array($col+1,$row,$x1,$y1,$x2,$y2,$col+1,$row+1,$col+1,$row);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v2));

                            break;

                        case 1: // West-East

                        // West vertice
                            $v1 = $this->dataPoints[$row][$col];
                            $v2 = $this->dataPoints[$row+1][$col];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x1,$y1) = $this->getCrossingCoord($row, $col,VERT_EDGE, $isobarValue);

                            // East vertice
                            $v1 = $this->dataPoints[$row][$col+1];
                            $v2 = $this->dataPoints[$row+1][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x2,$y2) = $this->getCrossingCoord($row, $col+1,VERT_EDGE, $isobarValue);

                            $polygon = array($col,$row,$x1,$y1,$x2,$y2,$col+1,$row,$col,$row);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v1));

                            $polygon = array($col,$row+1,$x1,$y1,$x2,$y2,$col+1,$row+1,$col,$row+1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v2));
                            break;

                        case 2: // West-North

                        // West vertice
                            $v1 = $this->dataPoints[$row][$col];
                            $v2 = $this->dataPoints[$row+1][$col];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x1,$y1) = $this->getCrossingCoord($row, $col,VERT_EDGE, $isobarValue);

                            // North vertice
                            $v1 = $this->dataPoints[$row][$col];
                            $v2 = $this->dataPoints[$row][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x2,$y2) = $this->getCrossingCoord($row, $col,HORIZ_EDGE, $isobarValue);

                            $polygon = array($col,$row,$x1,$y1,$x2,$y2,$col,$row);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v1));

                            $polygon = array($x1,$y1,$x2,$y2,$col+1,$row,$col+1,$row+1,$col,$row+1,$x1,$y1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v2));

                            break;

                        case 3: // East-North

                        //                            if( $row==3 && $col==1 && $n==2 ) {
                        //                                echo " ** East-North<br>";
                        //                            }


                        // East vertice
                            $v1 = $this->dataPoints[$row][$col+1];
                            $v2 = $this->dataPoints[$row+1][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x1,$y1) = $this->getCrossingCoord($row, $col+1,VERT_EDGE, $isobarValue);
                            //
                            //                            if( $row==3 && $col==1 && $n==2 ) {
                            //                                echo "   ** E_val($v1,$v2), isobar=$isobarValue<br>";
                            //                                echo "   ** E($x1,$y1)<br>";
                            //                            }


                            // North vertice
                            $v1 = $this->dataPoints[$row][$col];
                            $v2 = $this->dataPoints[$row][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x2,$y2) = $this->getCrossingCoord($row, $col,HORIZ_EDGE, $isobarValue);

                            //                            if( $row==3 && $col==1 && $n==2 ) {
                            //                                echo "   ** N_val($v1,$v2), isobar=$isobarValue<br>";
                            //                                echo "   ** N($x2,$y2)<br>";
                            //                            }
                            //                            if( $row==3 && $col==1 && $n==2 )
                            //                                $canvas->Line($x1,$y1,$x2,$y2,'blue');

                            $polygon = array($x1,$y1,$x2,$y2,$col+1,$row,$x1,$y1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v2));

                            $polygon = array($col,$row,$x2,$y2,$x1,$y1,$col+1,$row+1,$col,$row+1,$col,$row);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v1));

                            break;

                        case 4: // West-South

                        // West vertice
                            $v1 = $this->dataPoints[$row][$col];
                            $v2 = $this->dataPoints[$row+1][$col];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x1,$y1) = $this->getCrossingCoord($row, $col,VERT_EDGE, $isobarValue);

                            // South vertice
                            $v1 = $this->dataPoints[$row+1][$col];
                            $v2 = $this->dataPoints[$row+1][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x2,$y2) = $this->getCrossingCoord($row+1, $col,HORIZ_EDGE, $isobarValue);

                            $polygon = array($col,$row+1,$x1,$y1,$x2,$y2,$col,$row+1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v1));

                            $polygon = array($x1,$y1,$x2,$y2,$col+1,$row+1,$col+1,$row,$col,$row,$x1,$y1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v2));

                            break;

                        case 5: // East-South

                        //
                        //                            if( $row==1 && $col==1 && $n==2 ) {
                        //                                echo " ** Sout-East<br>";
                        //                            }

                        // East vertice
                            $v1 = $this->dataPoints[$row][$col+1];
                            $v2 = $this->dataPoints[$row+1][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x1,$y1) = $this->getCrossingCoord($row, $col+1,VERT_EDGE, $isobarValue);

                            //                            if( $row==1 && $col==1 && $n==2 ) {
                            //                                echo "   ** E_val($v1,$v2), isobar=$isobarValue<br>";
                            //                                echo "   ** E($x1,$y1)<br>";
                            //                            }

                            // South vertice
                            $v1 = $this->dataPoints[$row+1][$col];
                            $v2 = $this->dataPoints[$row+1][$col+1];
                            $isobarValue = $this->GetIsobarVal($v1, $v2);
                            list($x2,$y2) = $this->getCrossingCoord($row+1, $col,HORIZ_EDGE, $isobarValue);

                            //                            if( $row==1 && $col==1 && $n==2 ) {
                            //                                echo "   ** S_val($v1,$v2), isobar=$isobarValue<br>";
                            //                                echo "   ** S($x2,$y2)<br>";
                            //                            }

                            $polygon = array($col+1,$row+1,$x1,$y1,$x2,$y2,$col+1,$row+1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v2));

                            $polygon = array($x1,$y1,$x2,$y2,$col,$row+1,$col,$row,$col+1,$row,$x1,$y1);
                            $canvas->FilledPolygon($polygon,$this->GetIsobarColor($v1));

                            break;

                    }

                }

            }
        }

    }
}


class ContCanvas {
    public $g;
    public $shape,$scale;
    function __construct($xmax=6,$ymax=6,$width=400,$height=400) {

        $this->g = new CanvasGraph($width,$height);
        $this->scale = new CanvasScale($this->g, 0, $xmax, 0, $ymax);
        $this->shape = new Shape($this->g, $this->scale);

        //$this->g->SetFrame(true);
        $this->g->SetMargin(5,5,5,5);
        $this->g->SetMarginColor('white@1');
        $this->g->InitFrame();


        $this->shape->SetColor('gray');
        for( $col=1; $col<$xmax; ++$col ) {
            $this->shape->Line($col, 0, $col, $ymax);
        }
        for( $row=1; $row<$ymax; ++$row ) {
            $this->shape->Line(0, $row, $xmax, $row);
        }
    }

    function SetDatapoints($datapoints) {
        $ny=count($datapoints);
        $nx=count($datapoints[0]);
        $t = new Text();
        $t->SetFont(FF_ARIAL,FS_NORMAL,8);
        for( $x=0; $x < $nx; ++$x ) {
            for( $y=0; $y < $ny; ++$y ) {
                list($x1,$y1) = $this->scale->Translate($x,$y);

                if( $datapoints[$y][$x] > 0 )
                    $t->SetColor('blue');
                else
                    $t->SetColor('black');
                $t->SetFont(FF_ARIAL,FS_BOLD,8);
                $t->Set($datapoints[$y][$x]);
                $t->Stroke($this->g->img,$x1,$y1);

                $t->SetColor('gray');
                $t->SetFont(FF_ARIAL,FS_NORMAL,8);
                $t->Set("($y,$x)");
                $t->Stroke($this->g->img,$x1+10,$y1);

            }
        }
    }

    function DrawLinePolygons($p,$color='red') {
        $this->shape->SetColor($color);
        for ($i = 0 ; $i < count($p) ; $i++) {
            $x1 = $p[$i][0][0]; $y1 = $p[$i][0][1];
            for ($j = 1 ; $j < count($p[$i]) ; $j++) {
                $x2=$p[$i][$j][0]; $y2 = $p[$i][$j][1];
                $this->shape->Line($x1, $y1, $x2, $y2);
                $x1=$x2; $y1=$y2;
            }
        }
    }

    function Line($x1,$y1,$x2,$y2,$color='red') {
        $this->shape->SetColor($color);
        $this->shape->Line($x1, $y1, $x2, $y2);
    }
    function Polygon($p,$color='blue') {
        $this->shape->SetColor($color);
        $this->shape->Polygon($p);
    }

    function FilledPolygon($p,$color='lightblue') {
        $this->shape->SetColor($color);
        $this->shape->FilledPolygon($p);
    }

    function Point($x,$y,$color) {
        list($x1,$y1) = $this->scale->Translate($x, $y);
        $this->shape->SetColor($color);
        $this->g->img->Point($x1,$y1);
    }

    function Stroke() {
        $this->g->Stroke();
    }

}


class PixelFill {

    private $edges,$dataPoints,$colors,$isoBars;

    function __construct(&$edges,&$dataPoints,$isoBars,$colors) {
        $this->edges = $edges;
        $this->dataPoints = $dataPoints;
        $this->colors = $colors;
        $this->isoBars = $isoBars;
    }

    function GetIsobarColor($val) {
        for ($i = 0 ; $i < count($this->isoBars) ; $i++) {
            if( $val <= $this->isoBars[$i] ) {
                return $this->colors[$i];
            }
        }
        return $this->colors[$i]; // The color for all values above the highest isobar
    }

    function Fill(ContCanvas $canvas) {

        $nx_vertices = count($this->dataPoints[0]);
        $ny_vertices = count($this->dataPoints);

        // Loop through all squares in the grid
        for($col=0; $col < $nx_vertices-1; ++$col) {
            for($row=0; $row < $ny_vertices-1; ++$row) {

                $v=array(
                    $this->dataPoints[$row][$col],
                    $this->dataPoints[$row][$col+1],
                    $this->dataPoints[$row+1][$col+1],
                    $this->dataPoints[$row+1][$col],
                );
                
                list($x1,$y1) = $canvas->scale->Translate($col, $row);
                list($x2,$y2) = $canvas->scale->Translate($col+1, $row+1);

                for( $x=$x1; $x < $x2; ++$x ) {
                    for( $y=$y1; $y < $y2; ++$y ) {

                        $v1 = $v[0] + ($v[1]-$v[0])*($x-$x1)/($x2-$x1);
                        $v2 = $v[3] + ($v[2]-$v[3])*($x-$x1)/($x2-$x1);
                        $val = $v1 + ($v2-$v1)*($y-$y1)/($y2-$y1);

                        if( $row==2 && $col==2 ) {
                            //echo " ($val ($x,$y)) (".$v[0].",".$v[1].",".$v[2].",".$v[3].")<br>";
                        }
                        $color = $this->GetIsobarColor($val);
                        $canvas->g->img->SetColor($color);
                        $canvas->g->img->Point($x, $y);
                    }
                }
            }
        }

    }

}

$edges=array(array(),array(),array());
$datapoints=array();
for($col=0; $col<6; $col++) {
    for($row=0; $row<6; $row++) {
        $datapoints[$row][$col]=0;
        $edges[VERT_EDGE][$row][$col] = false;
        $edges[HORIZ_EDGE][$row][$col] = false;
    }
}

$datapoints[1][2] = 2;
$datapoints[2][1] = 1;
$datapoints[2][2] = 7;
$datapoints[2][3] = 2;
$datapoints[3][1] = 2;
$datapoints[3][2] = 17;
$datapoints[3][3] = 4;
$datapoints[4][2] = 3;

$datapoints[1][4] = 12;

$edges[VERT_EDGE][1][2] = true;
$edges[VERT_EDGE][3][2] = true;

$edges[HORIZ_EDGE][2][1] = true;
$edges[HORIZ_EDGE][2][2] = true;
$edges[HORIZ_EDGE][3][1] = true;
$edges[HORIZ_EDGE][3][2] = true;



$isobars = array(5,10,15);
$colors = array('lightgray','lightblue','lightred','red');

$engine = new PixelFill($edges, $datapoints, $isobars, $colors);
$canvas = new ContCanvas();
$engine->Fill($canvas);
$canvas->SetDatapoints($datapoints);
$canvas->Stroke();
die();


//$tst = new Findpolygon();
//$p1 = $tst->SetupTestData();
//
//$canvas = new ContCanvas();
//for ($i = 0 ; $i < count($tst->contourCoord); $i++) {
//    $canvas->DrawLinePolygons($tst->contourCoord[$i]);
//}
//
//$p2 = $tst->FindPolygons();
//for ($i = 0 ; $i < count($p2) ; $i++) {
//    $canvas->FilledPolygon($tst->flattenEdges($p2[$i]));
//}
//
//for ($i = 0 ; $i < count($p2) ; $i++) {
//    $canvas->Polygon($tst->flattenEdges($p2[$i]));
//}
//
//$canvas->Stroke();
//die();


//for( $trial = 0; $trial < 1; ++$trial ) {
//    echo "\nTest $trial:\n";
//    echo "========================================\n";
//    $tst = new Findpolygon();
//    $p1 = $tst->SetupTestData();
//
//    //    for ($i = 0 ; $i < count($p1) ; $i++) {
//    //        echo "Test polygon $i:\n";
//    //        echo "---------------------\n";
//    //        $tst->p_edges($tst->contourCoord[$i]);
//    //        echo "\n";
//    //    }
//    //
//    $p2 = $tst->FindPolygons();
//    $npol = count($p2);
//    //echo "\n** Found $npol separate polygon chains.\n\n";
//
//    for( $i=0; $i<$npol; ++$i ) {
//
//        $res_forward = $tst->CompareCyclic($p1[$i], $p2[$i],true);
//        $res_backward = $tst->CompareCyclic($p1[$i], $p2[$i],false);
//        if( $res_backward || $res_forward ) {
//        //            if( $res_forward )
//        //                echo "Forward matches!\n";
//        //            else
//        //                echo "Backward matches!\n";
//        }
//        else {
//            echo "********** NO MATCH!!.\n\n";
//            echo "\nBefore find:\n";
//            for ($j = 0 ; $j < count($p1[$i]) ; $j++) {
//                echo "(".$p1[$i][$j][0].','.$p1[$i][$j][1]."), ";
//            }
//            echo "\n";
//
//            echo "\nAfter find:\n";
//            for ($j = 0 ; $j < count($p2[$i]) ; $j++) {
//                echo "(".$p2[$i][$j][0].','.$p2[$i][$j][1]."), ";
//            }
//            echo "\n";
//        }
//
//    }
//}
//
//echo "\n\nAll tests ready.\n\n";
//


?>
