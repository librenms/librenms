<?php
//=======================================================================
// File:        JPGRAPH_RGB.INC.PHP
// Description: Class to handle RGb color space specification and
//              named colors
// Created:     2001-01-08 (Refactored to separate file 2008-08-01)
// Ver:         $Id: jpgraph_rgb.inc.php 1893 2009-10-02 23:15:25Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================


/*===================================================
// CLASS RGB
// Description: Color definitions as RGB triples
//===================================================
*/

class RGB {
    public $rgb_table;
    public $img;

    function __construct($aImg=null) {
        $this->img = $aImg;

        // Conversion array between color names and RGB
        $this->rgb_table = array(
            'aqua'=> array(0,255,255),
            'lime'=> array(0,255,0),
            'teal'=> array(0,128,128),
            'whitesmoke'=>array(245,245,245),
            'gainsboro'=>array(220,220,220),
            'oldlace'=>array(253,245,230),
            'linen'=>array(250,240,230),
            'antiquewhite'=>array(250,235,215),
            'papayawhip'=>array(255,239,213),
            'blanchedalmond'=>array(255,235,205),
            'bisque'=>array(255,228,196),
            'peachpuff'=>array(255,218,185),
            'navajowhite'=>array(255,222,173),
            'moccasin'=>array(255,228,181),
            'cornsilk'=>array(255,248,220),
            'ivory'=>array(255,255,240),
            'lemonchiffon'=>array(255,250,205),
            'seashell'=>array(255,245,238),
            'mintcream'=>array(245,255,250),
            'azure'=>array(240,255,255),
            'aliceblue'=>array(240,248,255),
            'lavender'=>array(230,230,250),
            'lavenderblush'=>array(255,240,245),
            'mistyrose'=>array(255,228,225),
            'white'=>array(255,255,255),
            'black'=>array(0,0,0),
            'darkslategray'=>array(47,79,79),
            'dimgray'=>array(105,105,105),
            'slategray'=>array(112,128,144),
            'lightslategray'=>array(119,136,153),
            'gray'=>array(190,190,190),
            'lightgray'=>array(211,211,211),
            'midnightblue'=>array(25,25,112),
            'navy'=>array(0,0,128),
            'indigo'=>array(75,0,130),
            'electricindigo'=>array(102,0,255),
            'deepindigo'=>array(138,43,226),
            'pigmentindigo'=>array(75,0,130),
            'indigodye'=>array(0,65,106),
            'cornflowerblue'=>array(100,149,237),
            'darkslateblue'=>array(72,61,139),
            'slateblue'=>array(106,90,205),
            'mediumslateblue'=>array(123,104,238),
            'lightslateblue'=>array(132,112,255),
            'mediumblue'=>array(0,0,205),
            'royalblue'=>array(65,105,225),
            'blue'=>array(0,0,255),
            'dodgerblue'=>array(30,144,255),
            'deepskyblue'=>array(0,191,255),
            'skyblue'=>array(135,206,235),
            'lightskyblue'=>array(135,206,250),
            'steelblue'=>array(70,130,180),
            'lightred'=>array(211,167,168),
            'lightsteelblue'=>array(176,196,222),
            'lightblue'=>array(173,216,230),
            'powderblue'=>array(176,224,230),
            'paleturquoise'=>array(175,238,238),
            'darkturquoise'=>array(0,206,209),
            'mediumturquoise'=>array(72,209,204),
            'turquoise'=>array(64,224,208),
            'cyan'=>array(0,255,255),
            'lightcyan'=>array(224,255,255),
            'cadetblue'=>array(95,158,160),
            'mediumaquamarine'=>array(102,205,170),
            'aquamarine'=>array(127,255,212),
            'darkgreen'=>array(0,100,0),
            'darkolivegreen'=>array(85,107,47),
            'darkseagreen'=>array(143,188,143),
            'seagreen'=>array(46,139,87),
            'mediumseagreen'=>array(60,179,113),
            'lightseagreen'=>array(32,178,170),
            'palegreen'=>array(152,251,152),
            'springgreen'=>array(0,255,127),
            'lawngreen'=>array(124,252,0),
            'green'=>array(0,255,0),
            'chartreuse'=>array(127,255,0),
            'mediumspringgreen'=>array(0,250,154),
            'greenyellow'=>array(173,255,47),
            'limegreen'=>array(50,205,50),
            'yellowgreen'=>array(154,205,50),
            'forestgreen'=>array(34,139,34),
            'olivedrab'=>array(107,142,35),
            'darkkhaki'=>array(189,183,107),
            'khaki'=>array(240,230,140),
            'palegoldenrod'=>array(238,232,170),
            'lightgoldenrodyellow'=>array(250,250,210),
            'lightyellow'=>array(255,255,200),
            'yellow'=>array(255,255,0),
            'gold'=>array(255,215,0),
            'lightgoldenrod'=>array(238,221,130),
            'goldenrod'=>array(218,165,32),
            'darkgoldenrod'=>array(184,134,11),
            'rosybrown'=>array(188,143,143),
            'indianred'=>array(205,92,92),
            'saddlebrown'=>array(139,69,19),
            'sienna'=>array(160,82,45),
            'peru'=>array(205,133,63),
            'burlywood'=>array(222,184,135),
            'beige'=>array(245,245,220),
            'wheat'=>array(245,222,179),
            'sandybrown'=>array(244,164,96),
            'tan'=>array(210,180,140),
            'chocolate'=>array(210,105,30),
            'firebrick'=>array(178,34,34),
            'brown'=>array(165,42,42),
            'darksalmon'=>array(233,150,122),
            'salmon'=>array(250,128,114),
            'lightsalmon'=>array(255,160,122),
            'orange'=>array(255,165,0),
            'darkorange'=>array(255,140,0),
            'coral'=>array(255,127,80),
            'lightcoral'=>array(240,128,128),
            'tomato'=>array(255,99,71),
            'orangered'=>array(255,69,0),
            'red'=>array(255,0,0),
            'hotpink'=>array(255,105,180),
            'deeppink'=>array(255,20,147),
            'pink'=>array(255,192,203),
            'lightpink'=>array(255,182,193),
            'palevioletred'=>array(219,112,147),
            'maroon'=>array(176,48,96),
            'mediumvioletred'=>array(199,21,133),
            'violetred'=>array(208,32,144),
            'magenta'=>array(255,0,255),
            'violet'=>array(238,130,238),
            'plum'=>array(221,160,221),
            'orchid'=>array(218,112,214),
            'mediumorchid'=>array(186,85,211),
            'darkorchid'=>array(153,50,204),
            'darkviolet'=>array(148,0,211),
            'blueviolet'=>array(138,43,226),
            'purple'=>array(160,32,240),
            'mediumpurple'=>array(147,112,219),
            'thistle'=>array(216,191,216),
            'snow1'=>array(255,250,250),
            'snow2'=>array(238,233,233),
            'snow3'=>array(205,201,201),
            'snow4'=>array(139,137,137),
            'seashell1'=>array(255,245,238),
            'seashell2'=>array(238,229,222),
            'seashell3'=>array(205,197,191),
            'seashell4'=>array(139,134,130),
            'AntiqueWhite1'=>array(255,239,219),
            'AntiqueWhite2'=>array(238,223,204),
            'AntiqueWhite3'=>array(205,192,176),
            'AntiqueWhite4'=>array(139,131,120),
            'bisque1'=>array(255,228,196),
            'bisque2'=>array(238,213,183),
            'bisque3'=>array(205,183,158),
            'bisque4'=>array(139,125,107),
            'peachPuff1'=>array(255,218,185),
            'peachpuff2'=>array(238,203,173),
            'peachpuff3'=>array(205,175,149),
            'peachpuff4'=>array(139,119,101),
            'navajowhite1'=>array(255,222,173),
            'navajowhite2'=>array(238,207,161),
            'navajowhite3'=>array(205,179,139),
            'navajowhite4'=>array(139,121,94),
            'lemonchiffon1'=>array(255,250,205),
            'lemonchiffon2'=>array(238,233,191),
            'lemonchiffon3'=>array(205,201,165),
            'lemonchiffon4'=>array(139,137,112),
            'ivory1'=>array(255,255,240),
            'ivory2'=>array(238,238,224),
            'ivory3'=>array(205,205,193),
            'ivory4'=>array(139,139,131),
            'honeydew'=>array(193,205,193),
            'lavenderblush1'=>array(255,240,245),
            'lavenderblush2'=>array(238,224,229),
            'lavenderblush3'=>array(205,193,197),
            'lavenderblush4'=>array(139,131,134),
            'mistyrose1'=>array(255,228,225),
            'mistyrose2'=>array(238,213,210),
            'mistyrose3'=>array(205,183,181),
            'mistyrose4'=>array(139,125,123),
            'azure1'=>array(240,255,255),
            'azure2'=>array(224,238,238),
            'azure3'=>array(193,205,205),
            'azure4'=>array(131,139,139),
            'slateblue1'=>array(131,111,255),
            'slateblue2'=>array(122,103,238),
            'slateblue3'=>array(105,89,205),
            'slateblue4'=>array(71,60,139),
            'royalblue1'=>array(72,118,255),
            'royalblue2'=>array(67,110,238),
            'royalblue3'=>array(58,95,205),
            'royalblue4'=>array(39,64,139),
            'dodgerblue1'=>array(30,144,255),
            'dodgerblue2'=>array(28,134,238),
            'dodgerblue3'=>array(24,116,205),
            'dodgerblue4'=>array(16,78,139),
            'steelblue1'=>array(99,184,255),
            'steelblue2'=>array(92,172,238),
            'steelblue3'=>array(79,148,205),
            'steelblue4'=>array(54,100,139),
            'deepskyblue1'=>array(0,191,255),
            'deepskyblue2'=>array(0,178,238),
            'deepskyblue3'=>array(0,154,205),
            'deepskyblue4'=>array(0,104,139),
            'skyblue1'=>array(135,206,255),
            'skyblue2'=>array(126,192,238),
            'skyblue3'=>array(108,166,205),
            'skyblue4'=>array(74,112,139),
            'lightskyblue1'=>array(176,226,255),
            'lightskyblue2'=>array(164,211,238),
            'lightskyblue3'=>array(141,182,205),
            'lightskyblue4'=>array(96,123,139),
            'slategray1'=>array(198,226,255),
            'slategray2'=>array(185,211,238),
            'slategray3'=>array(159,182,205),
            'slategray4'=>array(108,123,139),
            'lightsteelblue1'=>array(202,225,255),
            'lightsteelblue2'=>array(188,210,238),
            'lightsteelblue3'=>array(162,181,205),
            'lightsteelblue4'=>array(110,123,139),
            'lightblue1'=>array(191,239,255),
            'lightblue2'=>array(178,223,238),
            'lightblue3'=>array(154,192,205),
            'lightblue4'=>array(104,131,139),
            'lightcyan1'=>array(224,255,255),
            'lightcyan2'=>array(209,238,238),
            'lightcyan3'=>array(180,205,205),
            'lightcyan4'=>array(122,139,139),
            'paleturquoise1'=>array(187,255,255),
            'paleturquoise2'=>array(174,238,238),
            'paleturquoise3'=>array(150,205,205),
            'paleturquoise4'=>array(102,139,139),
            'cadetblue1'=>array(152,245,255),
            'cadetblue2'=>array(142,229,238),
            'cadetblue3'=>array(122,197,205),
            'cadetblue4'=>array(83,134,139),
            'turquoise1'=>array(0,245,255),
            'turquoise2'=>array(0,229,238),
            'turquoise3'=>array(0,197,205),
            'turquoise4'=>array(0,134,139),
            'cyan1'=>array(0,255,255),
            'cyan2'=>array(0,238,238),
            'cyan3'=>array(0,205,205),
            'cyan4'=>array(0,139,139),
            'darkslategray1'=>array(151,255,255),
            'darkslategray2'=>array(141,238,238),
            'darkslategray3'=>array(121,205,205),
            'darkslategray4'=>array(82,139,139),
            'aquamarine1'=>array(127,255,212),
            'aquamarine2'=>array(118,238,198),
            'aquamarine3'=>array(102,205,170),
            'aquamarine4'=>array(69,139,116),
            'darkseagreen1'=>array(193,255,193),
            'darkseagreen2'=>array(180,238,180),
            'darkseagreen3'=>array(155,205,155),
            'darkseagreen4'=>array(105,139,105),
            'seagreen1'=>array(84,255,159),
            'seagreen2'=>array(78,238,148),
            'seagreen3'=>array(67,205,128),
            'seagreen4'=>array(46,139,87),
            'palegreen1'=>array(154,255,154),
            'palegreen2'=>array(144,238,144),
            'palegreen3'=>array(124,205,124),
            'palegreen4'=>array(84,139,84),
            'springgreen1'=>array(0,255,127),
            'springgreen2'=>array(0,238,118),
            'springgreen3'=>array(0,205,102),
            'springgreen4'=>array(0,139,69),
            'chartreuse1'=>array(127,255,0),
            'chartreuse2'=>array(118,238,0),
            'chartreuse3'=>array(102,205,0),
            'chartreuse4'=>array(69,139,0),
            'olivedrab1'=>array(192,255,62),
            'olivedrab2'=>array(179,238,58),
            'olivedrab3'=>array(154,205,50),
            'olivedrab4'=>array(105,139,34),
            'darkolivegreen1'=>array(202,255,112),
            'darkolivegreen2'=>array(188,238,104),
            'darkolivegreen3'=>array(162,205,90),
            'darkolivegreen4'=>array(110,139,61),
            'khaki1'=>array(255,246,143),
            'khaki2'=>array(238,230,133),
            'khaki3'=>array(205,198,115),
            'khaki4'=>array(139,134,78),
            'lightgoldenrod1'=>array(255,236,139),
            'lightgoldenrod2'=>array(238,220,130),
            'lightgoldenrod3'=>array(205,190,112),
            'lightgoldenrod4'=>array(139,129,76),
            'yellow1'=>array(255,255,0),
            'yellow2'=>array(238,238,0),
            'yellow3'=>array(205,205,0),
            'yellow4'=>array(139,139,0),
            'gold1'=>array(255,215,0),
            'gold2'=>array(238,201,0),
            'gold3'=>array(205,173,0),
            'gold4'=>array(139,117,0),
            'goldenrod1'=>array(255,193,37),
            'goldenrod2'=>array(238,180,34),
            'goldenrod3'=>array(205,155,29),
            'goldenrod4'=>array(139,105,20),
            'darkgoldenrod1'=>array(255,185,15),
            'darkgoldenrod2'=>array(238,173,14),
            'darkgoldenrod3'=>array(205,149,12),
            'darkgoldenrod4'=>array(139,101,8),
            'rosybrown1'=>array(255,193,193),
            'rosybrown2'=>array(238,180,180),
            'rosybrown3'=>array(205,155,155),
            'rosybrown4'=>array(139,105,105),
            'indianred1'=>array(255,106,106),
            'indianred2'=>array(238,99,99),
            'indianred3'=>array(205,85,85),
            'indianred4'=>array(139,58,58),
            'sienna1'=>array(255,130,71),
            'sienna2'=>array(238,121,66),
            'sienna3'=>array(205,104,57),
            'sienna4'=>array(139,71,38),
            'burlywood1'=>array(255,211,155),
            'burlywood2'=>array(238,197,145),
            'burlywood3'=>array(205,170,125),
            'burlywood4'=>array(139,115,85),
            'wheat1'=>array(255,231,186),
            'wheat2'=>array(238,216,174),
            'wheat3'=>array(205,186,150),
            'wheat4'=>array(139,126,102),
            'tan1'=>array(255,165,79),
            'tan2'=>array(238,154,73),
            'tan3'=>array(205,133,63),
            'tan4'=>array(139,90,43),
            'chocolate1'=>array(255,127,36),
            'chocolate2'=>array(238,118,33),
            'chocolate3'=>array(205,102,29),
            'chocolate4'=>array(139,69,19),
            'firebrick1'=>array(255,48,48),
            'firebrick2'=>array(238,44,44),
            'firebrick3'=>array(205,38,38),
            'firebrick4'=>array(139,26,26),
            'brown1'=>array(255,64,64),
            'brown2'=>array(238,59,59),
            'brown3'=>array(205,51,51),
            'brown4'=>array(139,35,35),
            'salmon1'=>array(255,140,105),
            'salmon2'=>array(238,130,98),
            'salmon3'=>array(205,112,84),
            'salmon4'=>array(139,76,57),
            'lightsalmon1'=>array(255,160,122),
            'lightsalmon2'=>array(238,149,114),
            'lightsalmon3'=>array(205,129,98),
            'lightsalmon4'=>array(139,87,66),
            'orange1'=>array(255,165,0),
            'orange2'=>array(238,154,0),
            'orange3'=>array(205,133,0),
            'orange4'=>array(139,90,0),
            'darkorange1'=>array(255,127,0),
            'darkorange2'=>array(238,118,0),
            'darkorange3'=>array(205,102,0),
            'darkorange4'=>array(139,69,0),
            'coral1'=>array(255,114,86),
            'coral2'=>array(238,106,80),
            'coral3'=>array(205,91,69),
            'coral4'=>array(139,62,47),
            'tomato1'=>array(255,99,71),
            'tomato2'=>array(238,92,66),
            'tomato3'=>array(205,79,57),
            'tomato4'=>array(139,54,38),
            'orangered1'=>array(255,69,0),
            'orangered2'=>array(238,64,0),
            'orangered3'=>array(205,55,0),
            'orangered4'=>array(139,37,0),
            'deeppink1'=>array(255,20,147),
            'deeppink2'=>array(238,18,137),
            'deeppink3'=>array(205,16,118),
            'deeppink4'=>array(139,10,80),
            'hotpink1'=>array(255,110,180),
            'hotpink2'=>array(238,106,167),
            'hotpink3'=>array(205,96,144),
            'hotpink4'=>array(139,58,98),
            'pink1'=>array(255,181,197),
            'pink2'=>array(238,169,184),
            'pink3'=>array(205,145,158),
            'pink4'=>array(139,99,108),
            'lightpink1'=>array(255,174,185),
            'lightpink2'=>array(238,162,173),
            'lightpink3'=>array(205,140,149),
            'lightpink4'=>array(139,95,101),
            'palevioletred1'=>array(255,130,171),
            'palevioletred2'=>array(238,121,159),
            'palevioletred3'=>array(205,104,137),
            'palevioletred4'=>array(139,71,93),
            'maroon1'=>array(255,52,179),
            'maroon2'=>array(238,48,167),
            'maroon3'=>array(205,41,144),
            'maroon4'=>array(139,28,98),
            'violetred1'=>array(255,62,150),
            'violetred2'=>array(238,58,140),
            'violetred3'=>array(205,50,120),
            'violetred4'=>array(139,34,82),
            'magenta1'=>array(255,0,255),
            'magenta2'=>array(238,0,238),
            'magenta3'=>array(205,0,205),
            'magenta4'=>array(139,0,139),
            'mediumred'=>array(140,34,34),
            'orchid1'=>array(255,131,250),
            'orchid2'=>array(238,122,233),
            'orchid3'=>array(205,105,201),
            'orchid4'=>array(139,71,137),
            'plum1'=>array(255,187,255),
            'plum2'=>array(238,174,238),
            'plum3'=>array(205,150,205),
            'plum4'=>array(139,102,139),
            'mediumorchid1'=>array(224,102,255),
            'mediumorchid2'=>array(209,95,238),
            'mediumorchid3'=>array(180,82,205),
            'mediumorchid4'=>array(122,55,139),
            'darkorchid1'=>array(191,62,255),
            'darkorchid2'=>array(178,58,238),
            'darkorchid3'=>array(154,50,205),
            'darkorchid4'=>array(104,34,139),
            'purple1'=>array(155,48,255),
            'purple2'=>array(145,44,238),
            'purple3'=>array(125,38,205),
            'purple4'=>array(85,26,139),
            'mediumpurple1'=>array(171,130,255),
            'mediumpurple2'=>array(159,121,238),
            'mediumpurple3'=>array(137,104,205),
            'mediumpurple4'=>array(93,71,139),
            'thistle1'=>array(255,225,255),
            'thistle2'=>array(238,210,238),
            'thistle3'=>array(205,181,205),
            'thistle4'=>array(139,123,139),
            'gray1'=>array(10,10,10),
            'gray2'=>array(40,40,30),
            'gray3'=>array(70,70,70),
            'gray4'=>array(100,100,100),
            'gray5'=>array(130,130,130),
            'gray6'=>array(160,160,160),
            'gray7'=>array(190,190,190),
            'gray8'=>array(210,210,210),
            'gray9'=>array(240,240,240),
            'darkgray'=>array(100,100,100),
            'darkblue'=>array(0,0,139),
            'darkcyan'=>array(0,139,139),
            'darkmagenta'=>array(139,0,139),
            'darkred'=>array(139,0,0),
            'silver'=>array(192, 192, 192),
            'eggplant'=>array(144,176,168),
            'lightgreen'=>array(144,238,144));
    }


    //----------------
    // PUBLIC METHODS
    // Colors can be specified as either
    // 1. #xxxxxx   HTML style
    // 2. "colorname"  as a named color
    // 3. array(r,g,b) RGB triple
    // This function translates this to a native RGB format and returns an
    // RGB triple.

    function Color($aColor) {
        if (is_string($aColor)) {
            $matches = array();
            // this regex will parse a color string and fill the $matches array as such:
            // 0: the full match if any
            // 1: a hex string preceded by a hash, can be 3 characters (#fff) or 6 (#ffffff) (4 or 5 also accepted but...)
            // 2,3,4: r,g,b values in hex if the first character of the string is #
            // 5: all alpha-numeric characters at the beginning of the string if string does not start with #
            // 6: alpha value prefixed by @ if supplied
            // 7: alpha value with @ stripped
            // 8: adjust value prefixed with : if supplied
            // 9: adjust value with : stripped
            $regex = '/(#([0-9a-fA-F]{1,2})([0-9a-fA-F]{1,2})([0-9a-fA-F]{1,2}))?([\w]+)?(@([\d\.,]+))?(:([\d\.,]+))?/';
            if(!preg_match($regex, $aColor, $matches)) {
                JpGraphError::RaiseL(25078,$aColor);//(" Unknown color: $aColor");
            }
            if(empty($matches[5])) {
                $r = strlen($matches[2]) == 1 ? $matches[2].$matches[2] : $matches[2];
                $g = strlen($matches[3]) == 1 ? $matches[3].$matches[3] : $matches[3];
                $b = strlen($matches[4]) == 1 ? $matches[4].$matches[4] : $matches[4];
                $r = hexdec($r);
                $g = hexdec($g);
                $b = hexdec($b);
            }else {
                if(!isset($this->rgb_table[$matches[5]]) ) {
                    JpGraphError::RaiseL(25078,$aColor);//(" Unknown color: $aColor");
                }
                $r = $this->rgb_table[$matches[5]][0];
                $g = $this->rgb_table[$matches[5]][1];
                $b = $this->rgb_table[$matches[5]][2];
            }
            $alpha	= isset($matches[7]) ? str_replace(',','.',$matches[7]) : 0;
            $adj	= isset($matches[9]) ? str_replace(',','.',$matches[9]) : 1.0;

            if( $adj < 0 ) {
                JpGraphError::RaiseL(25077);//('Adjustment factor for color must be > 0');
            }

            // Scale adj so that an adj=2 always
            // makes the color 100% white (i.e. 255,255,255.
            // and adj=1 neutral and adj=0 black.
            if( $adj == 1) {
                return array($r,$g,$b,$alpha);
            }
            elseif( $adj > 1 ) {
                $m = ($adj-1.0)*(255-min(255,min($r,min($g,$b))));
                return array(min(255,$r+$m), min(255,$g+$m), min(255,$b+$m),$alpha);
            }
            elseif( $adj < 1 ) {
                $m = ($adj-1.0)*max(255,max($r,max($g,$b)));
                return array(max(0,$r+$m), max(0,$g+$m), max(0,$b+$m),$alpha);
            }
        } elseif( is_array($aColor) ) {
            if(!isset($aColor[3])) $aColor[3] = 0;
            return $aColor;
        }
        else {
            JpGraphError::RaiseL(25079,$aColor,count($aColor));//(" Unknown color specification: $aColor , size=".count($aColor));
        }
    }

    // Compare two colors
    // return true if equal
    function Equal($aCol1,$aCol2) {
        $c1 = $this->Color($aCol1);
        $c2 = $this->Color($aCol2);
        return $c1[0]==$c2[0] && $c1[1]==$c2[1] && $c1[2]==$c2[2] ;
    }

    // Allocate a new color in the current image
    // Return new color index, -1 if no more colors could be allocated
    function Allocate($aColor,$aAlpha=0.0) {
        list ($r, $g, $b, $a) = $this->color($aColor);
        // If alpha is specified in the color string then this
        // takes precedence over the second argument
        if( $a > 0 ) {
            $aAlpha = $a;
        }
        if( $aAlpha < 0 || $aAlpha > 1 ) {
            JpGraphError::RaiseL(25080);//('Alpha parameter for color must be between 0.0 and 1.0');
        }
        return imagecolorresolvealpha($this->img, $r, $g, $b, round($aAlpha * 127));
    }

    // Try to convert an array with three valid numbers to the corresponding hex array
    // This is currenly only used in processing the colors for barplots in order to be able
    // to handle the case where the color might be specified as an array of colros as well.
    // In that case we must be able to find out if an array of values should be interpretated as
    // a single color (specifeid as an RGB triple)
    static function tryHexConversion($aColor) {
        if( is_array( $aColor ) ) {
            if( count( $aColor ) == 3 ) {
                if( is_numeric($aColor[0]) && is_numeric($aColor[1]) && is_numeric($aColor[2]) ) {
                    if( ($aColor[0] >= 0 && $aColor[0] <= 255) &&
                        ($aColor[1] >= 0 && $aColor[1] <= 255) &&
                        ($aColor[2] >= 0 && $aColor[2] <= 255) ) {
                        return sprintf('#%02x%02x%02x',$aColor[0],$aColor[1],$aColor[2]);
                    }
                }
            }
        }
        return $aColor;
    }

    // Return a RGB tripple corresponding to a position in the normal light spectrum
    // The argumen values is in the range [0, 1] where a value of 0 correponds to blue and
    // a value of 1 corresponds to red. Values in betwen is mapped to a linear interpolation
    // of the constituting colors in the visible color spectra.
    // The $aDynamicRange specified how much of the dynamic range we shold use
    // a value of 1.0 give the full dyanmic range and a lower value give more dark
    // colors. In the extreme of 0.0 then all colors will be black.
    static function GetSpectrum($aVal,$aDynamicRange=1.0) {
        if( $aVal < 0 || $aVal > 1.0001 ) {
            return array(0,0,0); // Invalid case - just return black
        }

        $sat = round(255*$aDynamicRange);
        $a = 0.25;
        if( $aVal <= 0.25 ) {
            return array(0, round($sat*$aVal/$a), $sat);
        }
        elseif( $aVal <= 0.5 ) {
            return array(0, $sat, round($sat-$sat*($aVal-0.25)/$a));
        }
        elseif( $aVal <= 0.75 ) {
            return array(round($sat*($aVal-0.5)/$a), $sat, 0);
        }
        else {
            return array($sat, round($sat-$sat*($aVal-0.75)/$a), 0);
        }
    }

} // Class

?>
