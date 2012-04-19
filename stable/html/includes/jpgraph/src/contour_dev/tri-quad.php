<?php
require_once '../jpgraph.php';
require_once '../jpgraph_canvas.php';
require_once '../jpgraph_canvtools.php';


class ContCanvas {
    public $g;
    public $shape,$scale;
    function __construct($xmax=5,$ymax=5,$width=350,$height=350) {

        $this->g = new CanvasGraph($width,$height);
        $this->scale = new CanvasScale($this->g, 0, $xmax, 0, $ymax);
        $this->shape = new Shape($this->g, $this->scale);

        //$this->g->SetFrame(true);
        $this->g->SetMargin(2,2,2,2);
        $this->g->SetMarginColor('white@1');
        $this->g->InitFrame();
    }

    function StrokeGrid() {
        list($xmin,$xmax,$ymin,$ymax) = $this->scale->Get();
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

// Calculate the area for a simple polygon. This will not work for
// non-simple polygons, i.e. self crossing.
function polygonArea($aX, $aY) {
    $n = count($aX);
    $area = 0 ;
    $j = 0 ;
    for ($i=0; $i < $n; $i++) {
        $j++;
        if ( $j == $n) {
            $j=0;
        }
        $area += ($aX[i]+$aX[j])*($aY[i]-$aY[j]);
    }
    return area*.5;
}

class SingleTestTriangle {
    const contval=5;
    static $maxdepth=2;
    static $cnt=0;
    static $t;
    public $g;
    public $shape,$scale;
    public $cont = array(2,4,5);
    public $contcolors = array('yellow','purple','seagreen','green','lightblue','blue','teal','orange','red','darkred','brown');
    public $dofill=false;
    public $showtriangulation=false,$triangulation_color="lightgray";
    public $showannotation=false;
    public $contlinecolor='black',$showcontlines=true;
    private $labels = array(), $showlabels=false;
    private $labelColor='black',$labelFF=FF_ARIAL,$labelFS=FS_BOLD,$labelFSize=9;

    function __construct($width,$height,$nx,$ny) {
        $xmax=$nx+0.1;$ymax=$ny+0.1;
        $this->g = new CanvasGraph($width,$height);
        $this->scale = new CanvasScale($this->g, -0.1, $xmax, -0.1, $ymax);
        $this->shape = new Shape($this->g, $this->scale);

        //$this->g->SetFrame(true);
        $this->g->SetMargin(2,2,2,2);
        $this->g->SetMarginColor('white@1');
        //$this->g->InitFrame();

        self::$t = new Text();
        self::$t->SetColor('black');        
        self::$t->SetFont(FF_ARIAL,FS_BOLD,9);
        self::$t->SetAlign('center','center');
    }

    function getPlotSize() {
        return array($this->g->img->width,$this->g->img->height);
    }

    function SetContours($c) {
        $this->cont = $c;
    }

    function ShowLabels($aFlg=true) {
        $this->showlabels = $aFlg;
    }

    function ShowLines($aFlg=true) {
        $this->showcontlines=$aFlg;
    }

    function SetFilled($f=true) {
        $this->dofill = $f;
    }

    function ShowTriangulation($f=true) {
        $this->showtriangulation = $f;
    }

    function Stroke() {
        $this->g->Stroke();
    }

    function FillPolygon($color,&$p) {
        self::$cnt++;
        if( $this->dofill ) {
            $this->shape->SetColor($color);
            $this->shape->FilledPolygon($p);
        }
        if( $this->showtriangulation ) {
            $this->shape->SetColor($this->triangulation_color);
            $this->shape->Polygon($p);
        }
    }
    
    function GetNextHigherContourIdx($val) {
        for( $i=0; $i < count($this->cont); ++$i ) {
            if( $val < $this->cont[$i] ) return $i;
        }
        return count($this->cont);
    }

    function GetContVal($v1) {
        for( $i=0; $i < count($this->cont); ++$i ) {
            if( $this->cont[$i] > $v1 ) {
                return $this->cont[$i];
            }
        }
        die('No contour value is larger or equal than : '.$v1);
    }
    
    function GetColor($v) {
        return $this->contcolors[$this->GetNextHigherContourIdx($v)];
    }

    function storeAnnotation($x1,$y1,$v1,$angle) {
        $this->labels[$this->GetNextHigherContourIdx($v1)][] = array($x1,$y1,$v1,$angle);
    }

    function labelProx($x1,$y1,$v1) {

        list($w,$h) = $this->getPlotSize();


        if( $x1 < 20 || $x1 > $w-20 )
            return true;

        if( $y1 < 20 || $y1 > $h-20 )
            return true;
            
        if( !isset ($this->labels[$this->GetNextHigherContourIdx($v1)]) ) {
            return false;
        }
        $p = $this->labels[$this->GetNextHigherContourIdx($v1)];
        $n = count($p);
        $d = 999999;
        for ($i = 0 ; $i < $n ; $i++) {
            $xp = $p[$i][0];
            $yp = $p[$i][1];
            $d = min($d, ($x1-$xp)*($x1-$xp) + ($y1-$yp)*($y1-$yp));
        }
        
        $limit = $w*$h/9;
        $limit = max(min($limit,20000),3500);
        if( $d < $limit ) return true;
        else return false;
    }

    function putLabel($x1,$y1,$x2,$y2,$v1) {

        $angle = 0;
        if( $x2 - $x1 != 0 ) {
            $grad = ($y2-$y1)/($x2-$x1);
            $angle = -(atan($grad) * 180/M_PI);
            self::$t->SetAngle($angle);
        }

        $x = $this->scale->TranslateX($x1);
        $y = $this->scale->TranslateY($y1);
        if( !$this->labelProx($x, $y, $v1) ) {
            $this->storeAnnotation($x, $y, $v1, $angle);
        }
    }

    function strokeLabels() {
        $t = new Text();
        $t->SetColor($this->labelColor);
        $t->SetFont($this->labelFF,$this->labelFS,$this->labelFSize);
        $t->SetAlign('center','center');

        foreach ($this->labels as $cont_idx => $pos) {
            if( $cont_idx >= 10 ) return;
            foreach ($pos as $idx => $coord) {
                $t->Set( sprintf("%.1f",$coord[2]) );
                $t->SetAngle($coord[3]);
                $t->Stroke($this->g->img,$coord[0],$coord[1]);
            }
        }
    }

    function annotate($x1,$y1,$x2,$y2,$x1p,$y1p,$v1,$v2,$v1p) {
        if( !$this->showannotation ) return;
        /*
        $this->g->img->SetColor('green');
        $this->g->img->FilledCircle($this->scale->TranslateX($x1),$this->scale->TranslateY($y1), 4);
        $this->g->img->FilledCircle($this->scale->TranslateX($x2),$this->scale->TranslateY($y2), 4);

        $this->g->img->SetColor('red');
        $this->g->img->FilledCircle($this->scale->TranslateX($x1p),$this->scale->TranslateY($y1p), 4);
*/
        //self::$t->Set(sprintf("%.1f",$v1,$this->VC($v1)));
        //self::$t->Stroke($this->g->img,$this->scale->TranslateX($x1),$this->scale->TranslateY($y1));
        //self::$t->Set(sprintf("%.1f",$v2,$this->VC($v2)));
        //self::$t->Stroke($this->g->img,$this->scale->TranslateX($x2),$this->scale->TranslateY($y2));

        $x = $this->scale->TranslateX($x1p);
        $y = $this->scale->TranslateY($y1p);
        if( !$this->labelProx($x, $y, $v1p) ) {
            $this->storeAnnotation($x, $y, $v1p);
            self::$t->Set(sprintf("%.1f",$v1p,$this->VC($v1p)));
            self::$t->Stroke($this->g->img,$x,$y);
        }
    }

    function Pertubate(&$v1,&$v2,&$v3,&$v4) {
        $pert = 0.9999;
        $n = count($this->cont);
        for($i=0; $i < $n; ++$i) {
            if( $v1==$this->cont[$i] ) {
                $v1 *= $pert;
                break;
            }
        }
        for($i=0; $i < $n; ++$i) {
            if( $v2==$this->cont[$i] ) {
                $v2 *= $pert;
                break;
            }
        }
        for($i=0; $i < $n; ++$i) {
            if( $v3==$this->cont[$i] ) {
                $v3 *= $pert;
                break;
            }
        }
        for($i=0; $i < $n; ++$i) {
            if( $v4==$this->cont[$i] ) {
                $v4 *= $pert;
                break;
            }
        }
    }

    function interp2($x1,$y1,$x2,$y2,$v1,$v2) {
        $cv = $this->GetContVal(min($v1,$v2));
        $alpha = ($v1-$cv)/($v1-$v2);
        $x1p = $x1*(1-$alpha) + $x2*$alpha;
        $y1p = $y1*(1-$alpha) + $y2*$alpha;
        $v1p = $v1 + $alpha*($v2-$v1);
        return array($x1p,$y1p,$v1p);
    }

    function RectFill($v1,$v2,$v3,$v4,$x1,$y1,$x2,$y2,$x3,$y3,$x4,$y4,$depth) {
         if( $depth >= self::$maxdepth ) {
            // Abort and just appoximate the color of this area
            // with the average of the three values
            $color = $this->GetColor(($v1+$v2+$v3+$v4)/4);
            $p = array($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4, $x1, $y1);
            $this->FillPolygon($color,$p) ;
        }
        else {

            $this->Pertubate($v1,$v2,$v3,$v4);

            $fcnt = 0 ;
            $vv1 = $this->GetNextHigherContourIdx($v1);
            $vv2 = $this->GetNextHigherContourIdx($v2);
            $vv3 = $this->GetNextHigherContourIdx($v3);
            $vv4 = $this->GetNextHigherContourIdx($v4);
            $eps = 0.0001;

           if( $vv1 == $vv2 && $vv2 == $vv3 && $vv3 == $vv4 ) {
                $color = $this->GetColor($v1);
                $p = array($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4, $x1, $y1);
                $this->FillPolygon($color,$p) ;
            }
            else {

                $dv1 = abs($vv1-$vv2);
                $dv2 = abs($vv2-$vv3);
                $dv3 = abs($vv3-$vv4);
                $dv4 = abs($vv1-$vv4);
                
                if( $dv1 == 1 ) {
                    list($x1p,$y1p,$v1p) = $this->interp2($x1,$y1,$x2,$y2,$v1,$v2);
                    $fcnt++;
                }
                
                if( $dv2 == 1 ) {
                    list($x2p,$y2p,$v2p) = $this->interp2($x2,$y2,$x3,$y3,$v2,$v3);
                    $fcnt++;
                }
                
                if( $dv3 == 1 ) {
                    list($x3p,$y3p,$v3p) = $this->interp2($x3,$y3,$x4,$y4,$v3,$v4);
                    $fcnt++;
                }               

                if( $dv4 == 1 ) {
                    list($x4p,$y4p,$v4p) = $this->interp2($x4,$y4,$x1,$y1,$v4,$v1);
                    $fcnt++;
                }

                $totdv = $dv1 + $dv2 + $dv3 + $dv4 ;
                
                if( ($fcnt == 2 && $totdv==2) || ($fcnt == 4 && $totdv==4) ) {

                    if( $fcnt == 2 && $totdv==2 ) {

                        if( $dv1 == 1 && $dv2 == 1) {
                            $color1 = $this->GetColor($v2);
                            $p1 = array($x1p,$y1p,$x2,$y2,$x2p,$y2p,$x1p,$y1p);
                            $color2 = $this->GetColor($v4);
                            $p2 = array($x1,$y1,$x1p,$y1p,$x2p,$y2p,$x3,$y3,$x4,$y4,$x1,$y1);

                            $color = $this->GetColor($v1p);
                            $p = array($x1p,$y1p,$x2p,$y2p);
                            $v = $v1p;
                        }
                        elseif( $dv1 == 1 && $dv3 == 1 ) {
                            $color1 = $this->GetColor($v2);
                            $p1 = array($x1p,$y1p,$x2,$y2,$x3,$y3,$x3p,$y3p,$x1p,$y1p);
                            $color2 = $this->GetColor($v4);
                            $p2 = array($x1,$y1,$x1p,$y1p,$x3p,$y3p,$x4,$y4,$x1,$y1);

                            $color = $this->GetColor($v1p);
                            $p = array($x1p,$y1p,$x3p,$y3p);
                            $v = $v1p;
                        }
                        elseif( $dv1 == 1 && $dv4 == 1 ) {
                            $color1 = $this->GetColor($v1);
                            $p1 = array($x1,$y1,$x1p,$y1p,$x4p,$y4p,$x1,$y1);
                            $color2 = $this->GetColor($v3);
                            $p2 = array($x1p,$y1p,$x2,$y2,$x3,$y3,$x4,$y4,$x4p,$y4p,$x1p,$y1p);

                            $color = $this->GetColor($v1p);
                            $p = array($x1p,$y1p,$x4p,$y4p);
                            $v = $v1p;
                        }
                        elseif( $dv2 == 1 && $dv4 == 1 ) {
                            $color1 = $this->GetColor($v1);
                            $p1 = array($x1,$y1,$x2,$y2,$x2p,$y2p,$x4p,$y4p,$x1,$y1);
                            $color2 = $this->GetColor($v3);
                            $p2 = array($x4p,$y4p,$x2p,$y2p,$x3,$y3,$x4,$y4,$x4p,$y4p);

                            $color = $this->GetColor($v2p);
                            $p = array($x2p,$y2p,$x4p,$y4p);
                            $v = $v2p;
                        }
                        elseif( $dv2 == 1 && $dv3 == 1 ) {
                            $color1 = $this->GetColor($v1);
                            $p1 = array($x1,$y1,$x2,$y2,$x2p,$y2p,$x3p,$y3p,$x4,$y4,$x1,$y1);
                            $color2 = $this->GetColor($v3);
                            $p2 = array($x2p,$y2p,$x3,$y3,$x3p,$y3p,$x2p,$y2p);

                            $color = $this->GetColor($v2p);
                            $p = array($x2p,$y2p,$x3p,$y3p);
                            $v = $v2p;
                        }
                        elseif( $dv3 == 1 && $dv4 == 1 ) {
                            $color1 = $this->GetColor($v1);
                            $p1 = array($x1,$y1,$x2,$y2,$x3,$y3,$x3p,$y3p,$x4p,$y4p,$x1,$y1);
                            $color2 = $this->GetColor($v4);
                            $p2 = array($x4p,$y4p,$x3p,$y3p,$x4,$y4,$x4p,$y4p);

                            $color = $this->GetColor($v4p);
                            $p = array($x4p,$y4p,$x3p,$y3p);
                            $v = $v4p;
                        }

                        $this->FillPolygon($color1,$p1);
                        $this->FillPolygon($color2,$p2);

                        if( $this->showcontlines ) {
                            if( $this->dofill ) {
                                $this->shape->SetColor($this->contlinecolor);
                            }
                            else {
                                $this->shape->SetColor($color);
                            }
                            $this->shape->Line($p[0],$p[1],$p[2],$p[3]);
                        }
                        if( $this->showlabels ) {
                            $this->putLabel( ($p[0]+$p[2])/2, ($p[1]+$p[3])/2, $p[2],$p[3] , $v);
                        }
                    }
                    elseif( $fcnt == 4 && $totdv==4 ) {
                        $vc = ($v1+$v2+$v3+$v4)/4;

                        if( $v1p == $v2p && $v2p == $v3p && $v3p == $v4p ) {
                            // Four edge crossings (saddle point) of the same contour
                            // so we first need to
                            // find out how the saddle is crossing "/" or "\"

                            if( $this->GetNextHigherContourIdx($vc) == $this->GetNextHigherContourIdx($v1) ) {
                                // "\"
                                $color1 = $this->GetColor($v1);
                                $p1 = array($x1,$y1,$x1p,$y1p,$x4p,$y4p,$x1,$y1);

                                $color2 = $this->GetColor($v2);
                                $p2 = array($x1p,$y1p,$x2,$y2,$x2p,$y2p,$x3p,$y3p,$x4,$y4,$x4p,$y4p,$x1p,$y1p);

                                $color3 = $color1;
                                $p3 = array($x2p,$y2p,$x3,$y3,$x3p,$y3p,$x2p,$y2p);

                                $colorl1 = $this->GetColor($v1p);
                                $pl1 = array($x1p,$y1p,$x4p,$y4p);
                                $colorl2 = $this->GetColor($v2p);
                                $pl2 = array($x2p,$y2p,$x3p,$y3p);
                                $vl1 = $v1p; $vl2 = $v2p;

                            }
                            else {
                                // "/"
                                $color1 = $this->GetColor($v2);
                                $p1 = array($x1p,$y1p,$x2,$y2,$x2p,$y2p,$x1p,$y1p);

                                $color2 = $this->GetColor($v3);
                                $p2 = array($x1p,$y1p,$x2p,$y2p,$x3,$y3,$x3p,$y3p,$x4p,$y4p,$x1,$y1,$x1p,$y1p);

                                $color3 = $color1;
                                $p3 = array($x4p,$y4p,$x3p,$y3p,$x4,$y4,$x4p,$y4p);

                                $colorl1 = $this->GetColor($v1p);
                                $pl1 = array($x1p,$y1p,$x2p,$y2p);
                                $colorl2 = $this->GetColor($v4p);
                                $pl2 = array($x4p,$y4p,$x3p,$y3p);
                                $vl1 = $v1p; $vl2 = $v4p;
                            }
                        }
                        else {
                            // There are two different contours crossing so we need to find
                            // out which belongs to which
                            if( $v1p == $v2p ) {
                                // "/"
                                $color1 = $this->GetColor($v2);
                                $p1 = array($x1p,$y1p,$x2,$y2,$x2p,$y2p,$x1p,$y1p);

                                $color2 = $this->GetColor($v3);
                                $p2 = array($x1p,$y1p,$x2p,$y2p,$x3,$y3,$x3p,$y3p,$x4p,$y4p,$x1,$y1,$x1p,$y1p);

                                $color3 = $this->GetColor($v4);
                                $p3 = array($x4p,$y4p,$x3p,$y3p,$x4,$y4,$x4p,$y4p);

                                $colorl1 = $this->GetColor($v1p);
                                $pl1 = array($x1p,$y1p,$x2p,$y2p);
                                $colorl2 = $this->GetColor($v4p);
                                $pl2 = array($x4p,$y4p,$x3p,$y3p);
                                $vl1 = $v1p; $vl2 = $v4p;
                            }
                            else { //( $v1p == $v4p )
                                // "\"
                                $color1 = $this->GetColor($v1);
                                $p1 = array($x1,$y1,$x1p,$y1p,$x4p,$y4p,$x1,$y1);

                                $color2 = $this->GetColor($v2);
                                $p2 = array($x1p,$y1p,$x2,$y2,$x2p,$y2p,$x3p,$y3p,$x4,$y4,$x4p,$y4p,$x1p,$y1p);

                                $color3 = $this->GetColor($v3);
                                $p3 = array($x2p,$y2p,$x3,$y3,$x3p,$y3p,$x2p,$y2p);

                                $colorl1 = $this->GetColor($v1p);
                                $pl1 = array($x1p,$y1p,$x4p,$y4p);
                                $colorl2 = $this->GetColor($v2p);
                                $pl2 = array($x2p,$y2p,$x3p,$y3p);
                                $vl1 = $v1p; $vl2 = $v2p;
                            }
                        }
                        $this->FillPolygon($color1,$p1);
                        $this->FillPolygon($color2,$p2);
                        $this->FillPolygon($color3,$p3);

                        if( $this->showcontlines ) {
                            if( $this->dofill ) {
                                $this->shape->SetColor($this->contlinecolor);
                                $this->shape->Line($pl1[0],$pl1[1],$pl1[2],$pl1[3]);
                                $this->shape->Line($pl2[0],$pl2[1],$pl2[2],$pl2[3]);
                            }
                            else {
                                $this->shape->SetColor($colorl1);
                                $this->shape->Line($pl1[0],$pl1[1],$pl1[2],$pl1[3]);
                                $this->shape->SetColor($colorl2);
                                $this->shape->Line($pl2[0],$pl2[1],$pl2[2],$pl2[3]);
                            }
                        }
                        if( $this->showlabels ) {
                            $this->putLabel( ($pl1[0]+$pl1[2])/2, ($pl1[1]+$pl1[3])/2, $pl1[2], $pl1[3], $vl1);
                            $this->putLabel( ($pl2[0]+$pl2[2])/2, ($pl2[1]+$pl2[3])/2, $pl2[2], $pl2[3],$vl2);
                        }
                    }
                }
                else {
                    $vc = ($v1+$v2+$v3+$v4)/4;
                    $xc = ($x1+$x4)/2;
                    $yc = ($y1+$y2)/2;

                    // Top left
                    $this->RectFill(($v1+$v2)/2, $v2, ($v2+$v3)/2, $vc,
                                    $x1,$yc, $x2,$y2, $xc,$y2, $xc,$yc, $depth+1);
                    // Top right
                    $this->RectFill($vc, ($v2+$v3)/2, $v3, ($v3+$v4)/2,
                                    $xc,$yc, $xc,$y2, $x3,$y3, $x3,$yc, $depth+1);

                    // Bottom left
                    $this->RectFill($v1, ($v1+$v2)/2, $vc, ($v1+$v4)/2,
                                    $x1,$y1, $x1,$yc, $xc,$yc, $xc,$y4, $depth+1);

                    // Bottom right
                    $this->RectFill(($v1+$v4)/2, $vc, ($v3+$v4)/2, $v4,
                                    $xc,$y1, $xc,$yc, $x3,$yc, $x4,$y4, $depth+1);

                }
            }
        }
    }

    function TriFill($v1,$v2,$v3,$x1,$y1,$x2,$y2,$x3,$y3,$depth) {
        if( $depth >= self::$maxdepth ) {
            // Abort and just appoximate the color of this area
            // with the average of the three values
            $color = $this->GetColor(($v1+$v2+$v3)/3);
            $p = array($x1, $y1, $x2, $y2, $x3, $y3, $x1, $y1);
            $this->FillPolygon($color,$p) ;
        }
        else {
            // In order to avoid some real unpleasentness in case a vertice is exactly
            // the same value as a contour we pertuberate them so that we do not end up
            // in udefined situation. This will only affect the calculations and not the
            // visual appearance

            $dummy=0;
            $this->Pertubate($v1,$v2,$v3,$dummy);

            $fcnt = 0 ;
            $vv1 = $this->GetNextHigherContourIdx($v1);
            $vv2 = $this->GetNextHigherContourIdx($v2);
            $vv3 = $this->GetNextHigherContourIdx($v3);
            $eps = 0.0001;

            if( $vv1 == $vv2 && $vv2 == $vv3 ) {
                $color = $this->GetColor($v1);
                $p = array($x1, $y1, $x2, $y2, $x3, $y3, $x1, $y1);
                $this->FillPolygon($color,$p) ;
            }             
            else {
                $dv1 = abs($vv1-$vv2);
                $dv2 = abs($vv2-$vv3);
                $dv3 = abs($vv1-$vv3);

                if( $dv1 == 1 ) {
                    list($x1p,$y1p,$v1p) = $this->interp2($x1,$y1,$x2,$y2,$v1,$v2);
                    $fcnt++;
                }
                else {
                    $x1p = ($x1+$x2)/2;
                    $y1p = ($y1+$y2)/2;
                    $v1p = ($v1+$v2)/2;
                }

                if( $dv2 == 1 ) {
                    list($x2p,$y2p,$v2p) = $this->interp2($x2,$y2,$x3,$y3,$v2,$v3);
                    $fcnt++;
                }
                else {
                    $x2p = ($x2+$x3)/2;
                    $y2p = ($y2+$y3)/2;
                    $v2p = ($v2+$v3)/2;
                }

                if( $dv3 == 1 ) {
                    list($x3p,$y3p,$v3p) = $this->interp2($x3,$y3,$x1,$y1,$v3,$v1);
                    $fcnt++;
                }
                else {
                    $x3p = ($x3+$x1)/2;
                    $y3p = ($y3+$y1)/2;
                    $v3p = ($v3+$v1)/2;
                }

                if( $fcnt == 2 &&
                    ((abs($v1p-$v2p) < $eps && $dv1 ==1 && $dv2==1 ) ||
                    (abs($v1p-$v3p) < $eps && $dv1 ==1 && $dv3==1 ) ||
                    (abs($v2p-$v3p) < $eps && $dv2 ==1 && $dv3==1 )) ) {

                    // This means that the contour line crosses exactly two sides
                    // and that the values of each vertice is such that only this
                    // contour line will cross this section.
                    // We can now be smart. The cotour line will simply divide the
                    // area in two polygons that we can fill and then return. There is no
                    // need to recurse.
                    
                    // First find out which two sides the contour is crossing
                    if( abs($v1p-$v2p) < $eps ) {
                        $p4 = array($x1,$y1,$x1p,$y1p,$x2p,$y2p,$x3,$y3,$x1,$y1);
                        $color4 = $this->GetColor($v1);
                        
                        $p3 = array($x1p,$y1p,$x2,$y2,$x2p,$y2p,$x1p,$y1p);
                        $color3 = $this->GetColor($v2);

                        $p = array($x1p,$y1p,$x2p,$y2p);
                        $color = $this->GetColor($v1p);
                        $v = $v1p;
                    }
                    elseif( abs($v1p-$v3p) < $eps ) { 
                        $p4 = array($x1p,$y1p,$x2,$y2,$x3,$y3,$x3p,$y3p,$x1p,$y1p);
                        $color4 = $this->GetColor($v2);
                        
                        $p3 = array($x1,$y1,$x1p,$y1p,$x3p,$y3p,$x1,$y1);
                        $color3 = $this->GetColor($v1);

                        $p = array($x1p,$y1p,$x3p,$y3p);
                        $color = $this->GetColor($v1p);
                        $v = $v1p;
                    }
                    else {
                        $p4 = array($x1,$y1,$x2,$y2,$x2p,$y2p,$x3p,$y3p,$x1,$y1);
                        $color4 = $this->GetColor($v2);

                        $p3 = array($x3p,$y3p,$x2p,$y2p,$x3,$y3,$x3p,$y3p);
                        $color3 = $this->GetColor($v3);

                        $p = array($x3p,$y3p,$x2p,$y2p);
                        $color = $this->GetColor($v3p);
                        $v = $v3p;
                    }                    
                    $this->FillPolygon($color4,$p4);
                    $this->FillPolygon($color3,$p3);

                    if( $this->showcontlines ) {
                        if( $this->dofill ) {
                            $this->shape->SetColor($this->contlinecolor);
                        }
                        else {
                            $this->shape->SetColor($color);
                        }
                        $this->shape->Line($p[0],$p[1],$p[2],$p[3]);
                    }
                    if( $this->showlabels ) {
                        $this->putLabel( ($p[0]+$p[2])/2, ($p[1]+$p[3])/2, $p[2], $p[3], $v);
                    }
                }
                else {
                    $this->TriFill($v1, $v1p, $v3p, $x1, $y1, $x1p, $y1p, $x3p, $y3p, $depth+1);
                    $this->TriFill($v1p, $v2, $v2p, $x1p, $y1p, $x2, $y2, $x2p, $y2p, $depth+1);
                    $this->TriFill($v3p, $v1p, $v2p, $x3p, $y3p, $x1p, $y1p, $x2p, $y2p, $depth+1);
                    $this->TriFill($v3p, $v2p, $v3, $x3p, $y3p, $x2p, $y2p, $x3, $y3, $depth+1);
                }
            }
        }
    }

    function Fill($v1,$v2,$v3,$maxdepth) {
        $x1=0; $y1=1;
        $x2=1; $y2=0;
        $x3=1; $y3=1;
        self::$maxdepth = $maxdepth;
        $this->TriFill($v1, $v2, $v3, $x1, $y1, $x2, $y2, $x3, $y3, 0);
    }

    function Fillmesh($meshdata,$maxdepth,$method='tri') {
        $nx = count($meshdata[0]);
        $ny = count($meshdata);
        self::$maxdepth = $maxdepth;
        for( $x=0; $x < $nx-1; ++$x ) {
            for( $y=0; $y < $ny-1; ++$y ) {
                $v1 = $meshdata[$y][$x];
                $v2 = $meshdata[$y][$x+1];
                $v3 = $meshdata[$y+1][$x+1];
                $v4 = $meshdata[$y+1][$x];

                if( $method == 'tri' ) {
                    // Fill upper and lower triangle
                    $this->TriFill($v4, $v1, $v2, $x, $y+1, $x, $y, $x+1, $y, 0);
                    $this->TriFill($v4, $v2, $v3, $x, $y+1, $x+1, $y, $x+1, $y+1, 0);
                }
                else {
                    $this->RectFill($v4, $v1, $v2, $v3, $x, $y+1, $x, $y, $x+1, $y, $x+1, $y+1, 0);
                }
            }
        }
        if( $this->showlabels ) {
            $this->strokeLabels();
        }
    }
}

$meshdata = array(
    array (12,12,10,10),
    array (10,10,8,14),
    array (7,7,13,17),
    array (4,5,8,12),
    array (10,8,7,8));

$tt = new SingleTestTriangle(400,400,count($meshdata[0])-1,count($meshdata)-1);
$tt->SetContours(array(4.7, 6.0, 7.2, 8.6, 9.9, 11.2, 12.5, 13.8, 15.1, 16.4));
$tt->SetFilled(true);

//$tt->ShowTriangulation(true);
$tt->ShowLines(true);

//$tt->ShowLabels(true);
$tt->Fillmesh($meshdata, 8, 'rect');

//$tt->Fill(4.0,3.0,7.0, 4);
//$tt->Fill(7,4,1,5);
//$tt->Fill(1,7,4,5);

$tt->Stroke();

?>
