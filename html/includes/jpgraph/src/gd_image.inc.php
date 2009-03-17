<?php
//=======================================================================
// File:	GD_IMAGE.INC.PHP
// Description:	GD Instance of Image class
// Created: 	2006-05-06
// Ver:		$Id: gd_image.inc.php 1008 2008-06-13 23:20:44Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

//===================================================
// CLASS RGB
// Description: Color definitions as RGB triples
//===================================================
class RGB {
    var $rgb_table;
    var $img;
    function RGB(&$aImg) {
	$this->img = &$aImg;
		
	// Conversion array between color names and RGB
	$this->rgb_table = array(
	    "aqua"=> array(0,255,255),		
	    "lime"=> array(0,255,0),		
	    "teal"=> array(0,128,128),
	    "whitesmoke"=>array(245,245,245),
	    "gainsboro"=>array(220,220,220),
	    "oldlace"=>array(253,245,230),
	    "linen"=>array(250,240,230),
	    "antiquewhite"=>array(250,235,215),
	    "papayawhip"=>array(255,239,213),
	    "blanchedalmond"=>array(255,235,205),
	    "bisque"=>array(255,228,196),
	    "peachpuff"=>array(255,218,185),
	    "navajowhite"=>array(255,222,173),
	    "moccasin"=>array(255,228,181),
	    "cornsilk"=>array(255,248,220),
	    "ivory"=>array(255,255,240),
	    "lemonchiffon"=>array(255,250,205),
	    "seashell"=>array(255,245,238),
	    "mintcream"=>array(245,255,250),
	    "azure"=>array(240,255,255),
	    "aliceblue"=>array(240,248,255),
	    "lavender"=>array(230,230,250),
	    "lavenderblush"=>array(255,240,245),
	    "mistyrose"=>array(255,228,225),
	    "white"=>array(255,255,255),
	    "black"=>array(0,0,0),
	    "darkslategray"=>array(47,79,79),
	    "dimgray"=>array(105,105,105),
	    "slategray"=>array(112,128,144),
	    "lightslategray"=>array(119,136,153),
	    "gray"=>array(190,190,190),
	    "lightgray"=>array(211,211,211),
	    "midnightblue"=>array(25,25,112),
	    "navy"=>array(0,0,128),
	    "cornflowerblue"=>array(100,149,237),
	    "darkslateblue"=>array(72,61,139),
	    "slateblue"=>array(106,90,205),
	    "mediumslateblue"=>array(123,104,238),
	    "lightslateblue"=>array(132,112,255),
	    "mediumblue"=>array(0,0,205),
	    "royalblue"=>array(65,105,225),
	    "blue"=>array(0,0,255),
	    "dodgerblue"=>array(30,144,255),
	    "deepskyblue"=>array(0,191,255),
	    "skyblue"=>array(135,206,235),
	    "lightskyblue"=>array(135,206,250),
	    "steelblue"=>array(70,130,180),
	    "lightred"=>array(211,167,168),
	    "lightsteelblue"=>array(176,196,222),
	    "lightblue"=>array(173,216,230),
	    "powderblue"=>array(176,224,230),
	    "paleturquoise"=>array(175,238,238),
	    "darkturquoise"=>array(0,206,209),
	    "mediumturquoise"=>array(72,209,204),
	    "turquoise"=>array(64,224,208),
	    "cyan"=>array(0,255,255),
	    "lightcyan"=>array(224,255,255),
	    "cadetblue"=>array(95,158,160),
	    "mediumaquamarine"=>array(102,205,170),
	    "aquamarine"=>array(127,255,212),
	    "darkgreen"=>array(0,100,0),
	    "darkolivegreen"=>array(85,107,47),
	    "darkseagreen"=>array(143,188,143),
	    "seagreen"=>array(46,139,87),
	    "mediumseagreen"=>array(60,179,113),
	    "lightseagreen"=>array(32,178,170),
	    "palegreen"=>array(152,251,152),
	    "springgreen"=>array(0,255,127),
	    "lawngreen"=>array(124,252,0),
	    "green"=>array(0,255,0),
	    "chartreuse"=>array(127,255,0),
	    "mediumspringgreen"=>array(0,250,154),
	    "greenyellow"=>array(173,255,47),
	    "limegreen"=>array(50,205,50),
	    "yellowgreen"=>array(154,205,50),
	    "forestgreen"=>array(34,139,34),
	    "olivedrab"=>array(107,142,35),
	    "darkkhaki"=>array(189,183,107),
	    "khaki"=>array(240,230,140),
	    "palegoldenrod"=>array(238,232,170),
	    "lightgoldenrodyellow"=>array(250,250,210),
	    "lightyellow"=>array(255,255,200),
	    "yellow"=>array(255,255,0),
	    "gold"=>array(255,215,0),
	    "lightgoldenrod"=>array(238,221,130),
	    "goldenrod"=>array(218,165,32),
	    "darkgoldenrod"=>array(184,134,11),
	    "rosybrown"=>array(188,143,143),
	    "indianred"=>array(205,92,92),
	    "saddlebrown"=>array(139,69,19),
	    "sienna"=>array(160,82,45),
	    "peru"=>array(205,133,63),
	    "burlywood"=>array(222,184,135),
	    "beige"=>array(245,245,220),
	    "wheat"=>array(245,222,179),
	    "sandybrown"=>array(244,164,96),
	    "tan"=>array(210,180,140),
	    "chocolate"=>array(210,105,30),
	    "firebrick"=>array(178,34,34),
	    "brown"=>array(165,42,42),
	    "darksalmon"=>array(233,150,122),
	    "salmon"=>array(250,128,114),
	    "lightsalmon"=>array(255,160,122),
	    "orange"=>array(255,165,0),
	    "darkorange"=>array(255,140,0),
	    "coral"=>array(255,127,80),
	    "lightcoral"=>array(240,128,128),
	    "tomato"=>array(255,99,71),
	    "orangered"=>array(255,69,0),
	    "red"=>array(255,0,0),
	    "hotpink"=>array(255,105,180),
	    "deeppink"=>array(255,20,147),
	    "pink"=>array(255,192,203),
	    "lightpink"=>array(255,182,193),
	    "palevioletred"=>array(219,112,147),
	    "maroon"=>array(176,48,96),
	    "mediumvioletred"=>array(199,21,133),
	    "violetred"=>array(208,32,144),
	    "magenta"=>array(255,0,255),
	    "violet"=>array(238,130,238),
	    "plum"=>array(221,160,221),
	    "orchid"=>array(218,112,214),
	    "mediumorchid"=>array(186,85,211),
	    "darkorchid"=>array(153,50,204),
	    "darkviolet"=>array(148,0,211),
	    "blueviolet"=>array(138,43,226),
	    "purple"=>array(160,32,240),
	    "mediumpurple"=>array(147,112,219),
	    "thistle"=>array(216,191,216),
	    "snow1"=>array(255,250,250),
	    "snow2"=>array(238,233,233),
	    "snow3"=>array(205,201,201),
	    "snow4"=>array(139,137,137),
	    "seashell1"=>array(255,245,238),
	    "seashell2"=>array(238,229,222),
	    "seashell3"=>array(205,197,191),
	    "seashell4"=>array(139,134,130),
	    "AntiqueWhite1"=>array(255,239,219),
	    "AntiqueWhite2"=>array(238,223,204),
	    "AntiqueWhite3"=>array(205,192,176),
	    "AntiqueWhite4"=>array(139,131,120),
	    "bisque1"=>array(255,228,196),
	    "bisque2"=>array(238,213,183),
	    "bisque3"=>array(205,183,158),
	    "bisque4"=>array(139,125,107),
	    "peachPuff1"=>array(255,218,185),
	    "peachpuff2"=>array(238,203,173),
	    "peachpuff3"=>array(205,175,149),
	    "peachpuff4"=>array(139,119,101),
	    "navajowhite1"=>array(255,222,173),
	    "navajowhite2"=>array(238,207,161),
	    "navajowhite3"=>array(205,179,139),
	    "navajowhite4"=>array(139,121,94),
	    "lemonchiffon1"=>array(255,250,205),
	    "lemonchiffon2"=>array(238,233,191),
	    "lemonchiffon3"=>array(205,201,165),
	    "lemonchiffon4"=>array(139,137,112),
	    "ivory1"=>array(255,255,240),
	    "ivory2"=>array(238,238,224),
	    "ivory3"=>array(205,205,193),
	    "ivory4"=>array(139,139,131),
	    "honeydew"=>array(193,205,193),
	    "lavenderblush1"=>array(255,240,245),
	    "lavenderblush2"=>array(238,224,229),
	    "lavenderblush3"=>array(205,193,197),
	    "lavenderblush4"=>array(139,131,134),
	    "mistyrose1"=>array(255,228,225),
	    "mistyrose2"=>array(238,213,210),
	    "mistyrose3"=>array(205,183,181),
	    "mistyrose4"=>array(139,125,123),
	    "azure1"=>array(240,255,255),
	    "azure2"=>array(224,238,238),
	    "azure3"=>array(193,205,205),
	    "azure4"=>array(131,139,139),
	    "slateblue1"=>array(131,111,255),
	    "slateblue2"=>array(122,103,238),
	    "slateblue3"=>array(105,89,205),
	    "slateblue4"=>array(71,60,139),
	    "royalblue1"=>array(72,118,255),
	    "royalblue2"=>array(67,110,238),
	    "royalblue3"=>array(58,95,205),
	    "royalblue4"=>array(39,64,139),
	    "dodgerblue1"=>array(30,144,255),
	    "dodgerblue2"=>array(28,134,238),
	    "dodgerblue3"=>array(24,116,205),
	    "dodgerblue4"=>array(16,78,139),
	    "steelblue1"=>array(99,184,255),
	    "steelblue2"=>array(92,172,238),
	    "steelblue3"=>array(79,148,205),
	    "steelblue4"=>array(54,100,139),
	    "deepskyblue1"=>array(0,191,255),
	    "deepskyblue2"=>array(0,178,238),
	    "deepskyblue3"=>array(0,154,205),
	    "deepskyblue4"=>array(0,104,139),
	    "skyblue1"=>array(135,206,255),
	    "skyblue2"=>array(126,192,238),
	    "skyblue3"=>array(108,166,205),
	    "skyblue4"=>array(74,112,139),
	    "lightskyblue1"=>array(176,226,255),
	    "lightskyblue2"=>array(164,211,238),
	    "lightskyblue3"=>array(141,182,205),
	    "lightskyblue4"=>array(96,123,139),
	    "slategray1"=>array(198,226,255),
	    "slategray2"=>array(185,211,238),
	    "slategray3"=>array(159,182,205),
	    "slategray4"=>array(108,123,139),
	    "lightsteelblue1"=>array(202,225,255),
	    "lightsteelblue2"=>array(188,210,238),
	    "lightsteelblue3"=>array(162,181,205),
	    "lightsteelblue4"=>array(110,123,139),
	    "lightblue1"=>array(191,239,255),
	    "lightblue2"=>array(178,223,238),
	    "lightblue3"=>array(154,192,205),
	    "lightblue4"=>array(104,131,139),
	    "lightcyan1"=>array(224,255,255),
	    "lightcyan2"=>array(209,238,238),
	    "lightcyan3"=>array(180,205,205),
	    "lightcyan4"=>array(122,139,139),
	    "paleturquoise1"=>array(187,255,255),
	    "paleturquoise2"=>array(174,238,238),
	    "paleturquoise3"=>array(150,205,205),
	    "paleturquoise4"=>array(102,139,139),
	    "cadetblue1"=>array(152,245,255),
	    "cadetblue2"=>array(142,229,238),
	    "cadetblue3"=>array(122,197,205),
	    "cadetblue4"=>array(83,134,139),
	    "turquoise1"=>array(0,245,255),
	    "turquoise2"=>array(0,229,238),
	    "turquoise3"=>array(0,197,205),
	    "turquoise4"=>array(0,134,139),
	    "cyan1"=>array(0,255,255),
	    "cyan2"=>array(0,238,238),
	    "cyan3"=>array(0,205,205),
	    "cyan4"=>array(0,139,139),
	    "darkslategray1"=>array(151,255,255),
	    "darkslategray2"=>array(141,238,238),
	    "darkslategray3"=>array(121,205,205),
	    "darkslategray4"=>array(82,139,139),
	    "aquamarine1"=>array(127,255,212),
	    "aquamarine2"=>array(118,238,198),
	    "aquamarine3"=>array(102,205,170),
	    "aquamarine4"=>array(69,139,116),
	    "darkseagreen1"=>array(193,255,193),
	    "darkseagreen2"=>array(180,238,180),
	    "darkseagreen3"=>array(155,205,155),
	    "darkseagreen4"=>array(105,139,105),
	    "seagreen1"=>array(84,255,159),
	    "seagreen2"=>array(78,238,148),
	    "seagreen3"=>array(67,205,128),
	    "seagreen4"=>array(46,139,87),
	    "palegreen1"=>array(154,255,154),
	    "palegreen2"=>array(144,238,144),
	    "palegreen3"=>array(124,205,124),
	    "palegreen4"=>array(84,139,84),
	    "springgreen1"=>array(0,255,127),
	    "springgreen2"=>array(0,238,118),
	    "springgreen3"=>array(0,205,102),
	    "springgreen4"=>array(0,139,69),
	    "chartreuse1"=>array(127,255,0),
	    "chartreuse2"=>array(118,238,0),
	    "chartreuse3"=>array(102,205,0),
	    "chartreuse4"=>array(69,139,0),
	    "olivedrab1"=>array(192,255,62),
	    "olivedrab2"=>array(179,238,58),
	    "olivedrab3"=>array(154,205,50),
	    "olivedrab4"=>array(105,139,34),
	    "darkolivegreen1"=>array(202,255,112),
	    "darkolivegreen2"=>array(188,238,104),
	    "darkolivegreen3"=>array(162,205,90),
	    "darkolivegreen4"=>array(110,139,61),
	    "khaki1"=>array(255,246,143),
	    "khaki2"=>array(238,230,133),
	    "khaki3"=>array(205,198,115),
	    "khaki4"=>array(139,134,78),
	    "lightgoldenrod1"=>array(255,236,139),
	    "lightgoldenrod2"=>array(238,220,130),
	    "lightgoldenrod3"=>array(205,190,112),
	    "lightgoldenrod4"=>array(139,129,76),
	    "yellow1"=>array(255,255,0),
	    "yellow2"=>array(238,238,0),
	    "yellow3"=>array(205,205,0),
	    "yellow4"=>array(139,139,0),
	    "gold1"=>array(255,215,0),
	    "gold2"=>array(238,201,0),
	    "gold3"=>array(205,173,0),
	    "gold4"=>array(139,117,0),
	    "goldenrod1"=>array(255,193,37),
	    "goldenrod2"=>array(238,180,34),
	    "goldenrod3"=>array(205,155,29),
	    "goldenrod4"=>array(139,105,20),
	    "darkgoldenrod1"=>array(255,185,15),
	    "darkgoldenrod2"=>array(238,173,14),
	    "darkgoldenrod3"=>array(205,149,12),
	    "darkgoldenrod4"=>array(139,101,8),
	    "rosybrown1"=>array(255,193,193),
	    "rosybrown2"=>array(238,180,180),
	    "rosybrown3"=>array(205,155,155),
	    "rosybrown4"=>array(139,105,105),
	    "indianred1"=>array(255,106,106),
	    "indianred2"=>array(238,99,99),
	    "indianred3"=>array(205,85,85),
	    "indianred4"=>array(139,58,58),
	    "sienna1"=>array(255,130,71),
	    "sienna2"=>array(238,121,66),
	    "sienna3"=>array(205,104,57),
	    "sienna4"=>array(139,71,38),
	    "burlywood1"=>array(255,211,155),
	    "burlywood2"=>array(238,197,145),
	    "burlywood3"=>array(205,170,125),
	    "burlywood4"=>array(139,115,85),
	    "wheat1"=>array(255,231,186),
	    "wheat2"=>array(238,216,174),
	    "wheat3"=>array(205,186,150),
	    "wheat4"=>array(139,126,102),
	    "tan1"=>array(255,165,79),
	    "tan2"=>array(238,154,73),
	    "tan3"=>array(205,133,63),
	    "tan4"=>array(139,90,43),
	    "chocolate1"=>array(255,127,36),
	    "chocolate2"=>array(238,118,33),
	    "chocolate3"=>array(205,102,29),
	    "chocolate4"=>array(139,69,19),
	    "firebrick1"=>array(255,48,48),
	    "firebrick2"=>array(238,44,44),
	    "firebrick3"=>array(205,38,38),
	    "firebrick4"=>array(139,26,26),
	    "brown1"=>array(255,64,64),
	    "brown2"=>array(238,59,59),
	    "brown3"=>array(205,51,51),
	    "brown4"=>array(139,35,35),
	    "salmon1"=>array(255,140,105),
	    "salmon2"=>array(238,130,98),
	    "salmon3"=>array(205,112,84),
	    "salmon4"=>array(139,76,57),
	    "lightsalmon1"=>array(255,160,122),
	    "lightsalmon2"=>array(238,149,114),
	    "lightsalmon3"=>array(205,129,98),
	    "lightsalmon4"=>array(139,87,66),
	    "orange1"=>array(255,165,0),
	    "orange2"=>array(238,154,0),
	    "orange3"=>array(205,133,0),
	    "orange4"=>array(139,90,0),
	    "darkorange1"=>array(255,127,0),
	    "darkorange2"=>array(238,118,0),
	    "darkorange3"=>array(205,102,0),
	    "darkorange4"=>array(139,69,0),
	    "coral1"=>array(255,114,86),
	    "coral2"=>array(238,106,80),
	    "coral3"=>array(205,91,69),
	    "coral4"=>array(139,62,47),
	    "tomato1"=>array(255,99,71),
	    "tomato2"=>array(238,92,66),
	    "tomato3"=>array(205,79,57),
	    "tomato4"=>array(139,54,38),
	    "orangered1"=>array(255,69,0),
	    "orangered2"=>array(238,64,0),
	    "orangered3"=>array(205,55,0),
	    "orangered4"=>array(139,37,0),
	    "deeppink1"=>array(255,20,147),
	    "deeppink2"=>array(238,18,137),
	    "deeppink3"=>array(205,16,118),
	    "deeppink4"=>array(139,10,80),
	    "hotpink1"=>array(255,110,180),
	    "hotpink2"=>array(238,106,167),
	    "hotpink3"=>array(205,96,144),
	    "hotpink4"=>array(139,58,98),
	    "pink1"=>array(255,181,197),
	    "pink2"=>array(238,169,184),
	    "pink3"=>array(205,145,158),
	    "pink4"=>array(139,99,108),
	    "lightpink1"=>array(255,174,185),
	    "lightpink2"=>array(238,162,173),
	    "lightpink3"=>array(205,140,149),
	    "lightpink4"=>array(139,95,101),
	    "palevioletred1"=>array(255,130,171),
	    "palevioletred2"=>array(238,121,159),
	    "palevioletred3"=>array(205,104,137),
	    "palevioletred4"=>array(139,71,93),
	    "maroon1"=>array(255,52,179),
	    "maroon2"=>array(238,48,167),
	    "maroon3"=>array(205,41,144),
	    "maroon4"=>array(139,28,98),
	    "violetred1"=>array(255,62,150),
	    "violetred2"=>array(238,58,140),
	    "violetred3"=>array(205,50,120),
	    "violetred4"=>array(139,34,82),
	    "magenta1"=>array(255,0,255),
	    "magenta2"=>array(238,0,238),
	    "magenta3"=>array(205,0,205),
	    "magenta4"=>array(139,0,139),
	    "mediumred"=>array(140,34,34),         
	    "orchid1"=>array(255,131,250),
	    "orchid2"=>array(238,122,233),
	    "orchid3"=>array(205,105,201),
	    "orchid4"=>array(139,71,137),
	    "plum1"=>array(255,187,255),
	    "plum2"=>array(238,174,238),
	    "plum3"=>array(205,150,205),
	    "plum4"=>array(139,102,139),
	    "mediumorchid1"=>array(224,102,255),
	    "mediumorchid2"=>array(209,95,238),
	    "mediumorchid3"=>array(180,82,205),
	    "mediumorchid4"=>array(122,55,139),
	    "darkorchid1"=>array(191,62,255),
	    "darkorchid2"=>array(178,58,238),
	    "darkorchid3"=>array(154,50,205),
	    "darkorchid4"=>array(104,34,139),
	    "purple1"=>array(155,48,255),
	    "purple2"=>array(145,44,238),
	    "purple3"=>array(125,38,205),
	    "purple4"=>array(85,26,139),
	    "mediumpurple1"=>array(171,130,255),
	    "mediumpurple2"=>array(159,121,238),
	    "mediumpurple3"=>array(137,104,205),
	    "mediumpurple4"=>array(93,71,139),
	    "thistle1"=>array(255,225,255),
	    "thistle2"=>array(238,210,238),
	    "thistle3"=>array(205,181,205),
	    "thistle4"=>array(139,123,139),
	    "gray1"=>array(10,10,10),
	    "gray2"=>array(40,40,30),
	    "gray3"=>array(70,70,70),
	    "gray4"=>array(100,100,100),
	    "gray5"=>array(130,130,130),
	    "gray6"=>array(160,160,160),
	    "gray7"=>array(190,190,190),
	    "gray8"=>array(210,210,210),
	    "gray9"=>array(240,240,240),
	    "darkgray"=>array(100,100,100),
	    "darkblue"=>array(0,0,139),
	    "darkcyan"=>array(0,139,139),
	    "darkmagenta"=>array(139,0,139),
	    "darkred"=>array(139,0,0),
	    "silver"=>array(192, 192, 192),
	    "eggplant"=>array(144,176,168),
	    "lightgreen"=>array(144,238,144));		
    }
//----------------
// PUBLIC METHODS
    // Colors can be specified as either
    // 1. #xxxxxx			HTML style
    // 2. "colorname" 	as a named color
    // 3. array(r,g,b)	RGB triple
    // This function translates this to a native RGB format and returns an 
    // RGB triple.
    function Color($aColor) {
	if (is_string($aColor)) {
	    // Strip of any alpha factor
	    $pos = strpos($aColor,'@');
	    if( $pos === false ) {
		$alpha = 0;
	    }
	    else {
		$pos2 = strpos($aColor,':');
		if( $pos2===false ) 
		    $pos2 = $pos-1; // Sentinel
		if( $pos > $pos2 ) {
		    $alpha = str_replace(',','.',substr($aColor,$pos+1));
		    $aColor = substr($aColor,0,$pos);
		}
		else {
		    $alpha = substr($aColor,$pos+1,$pos2-$pos-1);
		    $aColor = substr($aColor,0,$pos).substr($aColor,$pos2);
		}
	    }

	    // Extract potential adjustment figure at end of color
	    // specification
	    $pos = strpos($aColor,":");
	    if( $pos === false ) {
		$adj = 1.0;
	    }
	    else {
		$adj = 0.0 + str_replace(',','.',substr($aColor,$pos+1));
		$aColor = substr($aColor,0,$pos);
	    }
	    if( $adj < 0 )
		JpGraphError::RaiseL(25077);//('Adjustment factor for color must be > 0');

	    if (substr($aColor, 0, 1) == "#") {
		$r = hexdec(substr($aColor, 1, 2));
		$g = hexdec(substr($aColor, 3, 2));
		$b = hexdec(substr($aColor, 5, 2));
	    } else {
      		if(!isset($this->rgb_table[$aColor]) )
		    JpGraphError::RaiseL(25078,$aColor);//(" Unknown color: $aColor");
		$tmp=$this->rgb_table[$aColor];
		$r = $tmp[0];
		$g = $tmp[1];
		$b = $tmp[2];
	    }
	    // Scale adj so that an adj=2 always
	    // makes the color 100% white (i.e. 255,255,255. 
	    // and adj=1 neutral and adj=0 black.
	    if( $adj > 1 ) {
		$m = ($adj-1.0)*(255-min(255,min($r,min($g,$b))));
		return array(min(255,$r+$m), min(255,$g+$m), min(255,$b+$m),$alpha);
	    }
	    elseif( $adj < 1 ) {
		$m = ($adj-1.0)*max(255,max($r,max($g,$b)));
		return array(max(0,$r+$m), max(0,$g+$m), max(0,$b+$m),$alpha);
	    }
	    else {
		return array($r,$g,$b,$alpha);
	    }

	} elseif( is_array($aColor) ) {
	    if( count($aColor)==3 ) {
		$aColor[3]=0;
		return $aColor;
	    }
	    else
		return $aColor;
	}
	else
	    JpGraphError::RaiseL(25079,$aColor,count($aColor));//(" Unknown color specification: $aColor , size=".count($aColor));
    }
	
    // Compare two colors
    // return true if equal
    function Equal($aCol1,$aCol2) {
	$c1 = $this->Color($aCol1);
	$c2 = $this->Color($aCol2);
	if( $c1[0]==$c2[0] && $c1[1]==$c2[1] && $c1[2]==$c2[2] )
	    return true;
	else
	    return false;
    }
	
    // Allocate a new color in the current image
    // Return new color index, -1 if no more colors could be allocated
    function Allocate($aColor,$aAlpha=0.0) {
	list ($r, $g, $b, $a) = $this->color($aColor);
	// If alpha is specified in the color string then this
	// takes precedence over the second argument
	if( $a > 0 )
	    $aAlpha = $a;
	if( $aAlpha < 0 || $aAlpha > 1 ) {
	    JpGraphError::RaiseL(25080);//('Alpha parameter for color must be between 0.0 and 1.0');
	}
	return imagecolorresolvealpha($this->img, $r, $g, $b, round($aAlpha * 127));
    }
} // Class

	
//===================================================
// CLASS Image
// Description: Wrapper class with some goodies to form the
// Interface to low level image drawing routines.
//===================================================
class Image {
    var $img_format;
    var $expired=true;
    var $img=null;
    var $left_margin=30,$right_margin=20,$top_margin=20,$bottom_margin=30;
    var $plotwidth=0,$plotheight=0;
    var $rgb=null;
    var $current_color,$current_color_name;
    var $lastx=0, $lasty=0;
    var $width=0, $height=0;
    var $line_weight=1;
    var $line_style=1;	// Default line style is solid
    var $obs_list=array();
    var $font_size=12,$font_family=FF_FONT1, $font_style=FS_NORMAL;
    var $font_file='';
    var $text_halign="left",$text_valign="bottom";
    var $ttf=null;
    var $use_anti_aliasing=false;
    var $quality=null;
    var $colorstack=array(),$colorstackidx=0;
    var $canvascolor = 'white' ;
    var $langconv = null ;

    //---------------
    // CONSTRUCTOR
    function Image($aWidth,$aHeight,$aFormat=DEFAULT_GFORMAT,$aSetAutoMargin=true) {
	$this->CreateImgCanvas($aWidth,$aHeight);
	if( $aSetAutoMargin ) 
	    $this->SetAutoMargin();		

	if( !$this->SetImgFormat($aFormat) ) {
	    JpGraphError::RaiseL(25081,$aFormat);//("JpGraph: Selected graphic format is either not supported or unknown [$aFormat]");
	}
	$this->ttf = new TTF();
	$this->langconv = new LanguageConv();
    }

    // Should we use anti-aliasing. Note: This really slows down graphics!
    function SetAntiAliasing() {
	$this->use_anti_aliasing=true;
    }

    function CreateRawCanvas($aWidth=0,$aHeight=0) {
	if( $aWidth <= 1 || $aHeight <= 1 ) {
	    JpGraphError::RaiseL(25082,$aWidth,$aHeight);//("Illegal sizes specified for width or height when creating an image, (width=$aWidth, height=$aHeight)");
	}
	$this->img = @imagecreatetruecolor($aWidth, $aHeight);
	if( $this->img < 1 ) {
	    JpGraphError::RaiseL(25126);
	    //die("Can't create truecolor image. Check that you really have GD2 library installed.");
	}
	$this->SetAlphaBlending();
	if( $this->rgb != null ) 
	    $this->rgb->img = $this->img ;
	else
	    $this->rgb = new RGB($this->img);				
    }

    function CloneCanvasH() {
	$oldimage = $this->img;
	$this->CreateRawCanvas($this->width,$this->height);
	imagecopy($this->img,$oldimage,0,0,0,0,$this->width,$this->height);
	return $oldimage;
    }
    
    function CreateImgCanvas($aWidth=0,$aHeight=0) {

	$old = array($this->img,$this->width,$this->height);
	
	$aWidth = round($aWidth);
	$aHeight = round($aHeight);

	$this->width=$aWidth;
	$this->height=$aHeight;		

	
	if( $aWidth==0 || $aHeight==0 ) {
	    // We will set the final size later. 
	    // Note: The size must be specified before any other
	    // img routines that stroke anything are called.
	    $this->img = null;
	    $this->rgb = null;
	    return $old;
	}
	
	$this->CreateRawCanvas($aWidth,$aHeight);
		
	// Set canvas color (will also be the background color for a 
	// a pallett image
	$this->SetColor($this->canvascolor);	
	$this->FilledRectangle(0,0,$aWidth,$aHeight);

	return $old ;
    }

    function CopyCanvasH($aToHdl,$aFromHdl,$aToX,$aToY,$aFromX,$aFromY,$aWidth,$aHeight,$aw=-1,$ah=-1) {
	if( $aw === -1 ) {
	    $aw = $aWidth;
	    $ah = $aHeight;
	    $f = 'imagecopyresized';
	}
	else {
	    $f = 'imagecopyresampled' ;
	}
	$f($aToHdl,$aFromHdl,
	   $aToX,$aToY,$aFromX,$aFromY, $aWidth,$aHeight,$aw,$ah);
    }

    function Copy($fromImg,$toX,$toY,$fromX,$fromY,$toWidth,$toHeight,$fromWidth=-1,$fromHeight=-1) {
	$this->CopyCanvasH($this->img,$fromImg,$toX,$toY,$fromX,$fromY,
			   $toWidth,$toHeight,$fromWidth,$fromHeight);
    }

    function CopyMerge($fromImg,$toX,$toY,$fromX,$fromY,$toWidth,$toHeight,$fromWidth=-1,$fromHeight=-1,$aMix=100) {
	if( $aMix == 100 ) {
	    $this->CopyCanvasH($this->img,$fromImg,
			       $toX,$toY,$fromX,$fromY,$toWidth,$toHeight,$fromWidth,$fromHeight);
	}
	else {
	    if( ($fromWidth  != -1 && ($fromWidth != $toWidth))  ||
		($fromHeight != -1 && ($fromHeight != $fromHeight)) ) {
		// Create a new canvas that will hold the re-scaled original from image
		if( $toWidth <= 1 || $toHeight <= 1 ) {
		    JpGraphError::RaiseL(25083);//('Illegal image size when copying image. Size for copied to image is 1 pixel or less.');
		}
		$tmpimg = @imagecreatetruecolor($toWidth, $toHeight);
		if( $tmpimg < 1 ) {
		    JpGraphError::RaiseL(25084);//('Failed to create temporary GD canvas. Out of memory ?');
		}
		$this->CopyCanvasH($tmpimg,$fromImg,0,0,0,0,
				   $toWidth,$toHeight,$fromWidth,$fromHeight);
		$fromImg = $tmpimg;
	    }
	    imagecopymerge($this->img,$fromImg,$toX,$toY,$fromX,$fromY,$toWidth,$toHeight,$aMix);
	}
    }

    function GetWidth($aImg=null) {
	if( $aImg === null ) 
	    $aImg = $this->img;
	return imagesx($aImg);
    }

    function GetHeight($aImg=null) {
	if( $aImg === null ) 
	    $aImg = $this->img;
	return imagesy($aImg);
    }
    
    function CreateFromString($aStr) {
	$img = @imagecreatefromstring($aStr);
	if( $img === false ) {
	    JpGraphError::RaiseL(25085);//('An image can not be created from the supplied string. It is either in a format not supported or the string is representing an corrupt image.');
	}
	return $img;
    }

    function SetCanvasH($aHdl) {
	$this->img = $aHdl;
	$this->rgb->img = $aHdl;
    }

    function SetCanvasColor($aColor) {
	$this->canvascolor = $aColor ;
    }

    function SetAlphaBlending($aFlg=true) {
	ImageAlphaBlending($this->img,$aFlg);
    }

	
    function SetAutoMargin() {	
	GLOBAL $gJpgBrandTiming;
	$min_bm=5;
	/*
	if( $gJpgBrandTiming )
	    $min_bm=15;		
	*/
	$lm = min(40,$this->width/7);
	$rm = min(20,$this->width/10);
	$tm = max(5,$this->height/7);
	$bm = max($min_bm,$this->height/7);
	
	$this->SetMargin($lm,$rm,$tm,$bm);		
    }

				
    //---------------
    // PUBLIC METHODS	
	
    function SetFont($family,$style=FS_NORMAL,$size=10) {
	$this->font_family=$family;
	$this->font_style=$style;
	$this->font_size=$size;
	$this->font_file='';
	if( ($this->font_family==FF_FONT1 || $this->font_family==FF_FONT2) && $this->font_style==FS_BOLD ){
	    ++$this->font_family;
	}
	if( $this->font_family > FF_FONT2+1 ) { // A TTF font so get the font file

	    // Check that this PHP has support for TTF fonts
	    if( !function_exists('imagettfbbox') ) {
		JpGraphError::RaiseL(25087);//('This PHP build has not been configured with TTF support. You need to recompile your PHP installation with FreeType support.');
	    }
	    $this->font_file = $this->ttf->File($this->font_family,$this->font_style);
	}
    }

    // Get the specific height for a text string
    function GetTextHeight($txt="",$angle=0) {
	$tmp = split("\n",$txt);
	$n = count($tmp);
	$m=0;
	for($i=0; $i< $n; ++$i)
	    $m = max($m,strlen($tmp[$i]));

	if( $this->font_family <= FF_FONT2+1 ) {
	    if( $angle==0 ) {
		$h = imagefontheight($this->font_family);
		if( $h === false ) {
		    JpGraphError::RaiseL(25088);//('You have a misconfigured GD font support. The call to imagefontwidth() fails.');
		}

		return $n*$h;
	    }
	    else {
		$w = @imagefontwidth($this->font_family);
		if( $w === false ) {
		    JpGraphError::RaiseL(25088);//('You have a misconfigured GD font support. The call to imagefontwidth() fails.');
		}

		return $m*$w;
	    }
	}
	else {
	    $bbox = $this->GetTTFBBox($txt,$angle);
	    return $bbox[1]-$bbox[5];
	}
    }
	
    // Estimate font height
    function GetFontHeight($angle=0) {
	$txt = "XOMg";
	return $this->GetTextHeight($txt,$angle);
    }
	
    // Approximate font width with width of letter "O"
    function GetFontWidth($angle=0) {
	$txt = 'O';
	return $this->GetTextWidth($txt,$angle);
    }
	
    // Get actual width of text in absolute pixels
    function GetTextWidth($txt,$angle=0) {

	$tmp = split("\n",$txt);
	$n = count($tmp);
	if( $this->font_family <= FF_FONT2+1 ) {

	    $m=0;
	    for($i=0; $i < $n; ++$i) {
		$l=strlen($tmp[$i]);
		if( $l > $m ) {
		    $m = $l;
		}
	    }

	    if( $angle==0 ) {
		$w = @imagefontwidth($this->font_family);
		if( $w === false ) {
		    JpGraphError::RaiseL(25088);//('You have a misconfigured GD font support. The call to imagefontwidth() fails.');
		}
		return $m*$w;
	    }
	    else {
		// 90 degrees internal so height becomes width
		$h = @imagefontheight($this->font_family); 
		if( $h === false ) {
		    JpGraphError::RaiseL(25089);//('You have a misconfigured GD font support. The call to imagefontheight() fails.');
		}
		return $n*$h;
	    }
	}
	else {
	    // For TTF fonts we must walk through a lines and find the 
	    // widest one which we use as the width of the multi-line
	    // paragraph
	    $m=0;
	    for( $i=0; $i < $n; ++$i ) {
		$bbox = $this->GetTTFBBox($tmp[$i],$angle);
		$mm =  $bbox[2] - $bbox[0];
		if( $mm > $m ) 
		    $m = $mm;
	    }
	    return $m;
	}
    }
	
    // Draw text with a box around it
    function StrokeBoxedText($x,$y,$txt,$dir=0,$fcolor="white",$bcolor="black",
			     $shadowcolor=false,$paragraph_align="left",
			     $xmarg=6,$ymarg=4,$cornerradius=0,$dropwidth=3) {

	if( !is_numeric($dir) ) {
	    if( $dir=="h" ) $dir=0;
	    elseif( $dir=="v" ) $dir=90;
	    else JpGraphError::RaiseL(25090,$dir);//(" Unknown direction specified in call to StrokeBoxedText() [$dir]");
	}
		
	if( $this->font_family >= FF_FONT0 && $this->font_family <= FF_FONT2+1) {	
	    $width=$this->GetTextWidth($txt,$dir) ;
	    $height=$this->GetTextHeight($txt,$dir) ;
	}
	else {
	    $width=$this->GetBBoxWidth($txt,$dir) ;
	    $height=$this->GetBBoxHeight($txt,$dir) ;
	}

	$height += 2*$ymarg;
	$width  += 2*$xmarg;

	if( $this->text_halign=="right" ) $x -= $width;
	elseif( $this->text_halign=="center" ) $x -= $width/2;
	if( $this->text_valign=="bottom" ) $y -= $height;
	elseif( $this->text_valign=="center" ) $y -= $height/2;
	
	$olda = $this->SetAngle(0);

	if( $shadowcolor ) {
	    $this->PushColor($shadowcolor);
	    $this->FilledRoundedRectangle($x-$xmarg+$dropwidth,$y-$ymarg+$dropwidth,
					  $x+$width+$dropwidth,$y+$height-$ymarg+$dropwidth,
					  $cornerradius);
	    $this->PopColor();
	    $this->PushColor($fcolor);
	    $this->FilledRoundedRectangle($x-$xmarg,$y-$ymarg,
					  $x+$width,$y+$height-$ymarg,
					  $cornerradius);		
	    $this->PopColor();
	    $this->PushColor($bcolor);
	    $this->RoundedRectangle($x-$xmarg,$y-$ymarg,
				    $x+$width,$y+$height-$ymarg,$cornerradius);
	    $this->PopColor();
	}
	else {
	    if( $fcolor ) {
		$oc=$this->current_color;
		$this->SetColor($fcolor);
		$this->FilledRoundedRectangle($x-$xmarg,$y-$ymarg,$x+$width,$y+$height-$ymarg,$cornerradius);
		$this->current_color=$oc;
	    }
	    if( $bcolor ) {
		$oc=$this->current_color;
		$this->SetColor($bcolor);			
		$this->RoundedRectangle($x-$xmarg,$y-$ymarg,$x+$width,$y+$height-$ymarg,$cornerradius);
		$this->current_color=$oc;			
	    }
	}
		
	$h=$this->text_halign;
	$v=$this->text_valign;
	$this->SetTextAlign("left","top");
	$this->StrokeText($x, $y, $txt, $dir, $paragraph_align);
	$bb = array($x-$xmarg,$y+$height-$ymarg,$x+$width,$y+$height-$ymarg,
		    $x+$width,$y-$ymarg,$x-$xmarg,$y-$ymarg);
	$this->SetTextAlign($h,$v);

	$this->SetAngle($olda);

	return $bb;
    }

    // Set text alignment	
    function SetTextAlign($halign,$valign="bottom") {
	$this->text_halign=$halign;
	$this->text_valign=$valign;
    }
	

    function _StrokeBuiltinFont($x,$y,$txt,$dir=0,$paragraph_align="left",&$aBoundingBox,$aDebug=false) {

	if( is_numeric($dir) && $dir!=90 && $dir!=0) 
	    JpGraphError::RaiseL(25091);//(" Internal font does not support drawing text at arbitrary angle. Use TTF fonts instead.");

	$h=$this->GetTextHeight($txt);
	$fh=$this->GetFontHeight();
	$w=$this->GetTextWidth($txt);
	
	if( $this->text_halign=="right") 				
	    $x -= $dir==0 ? $w : $h;
	elseif( $this->text_halign=="center" ) {
	    // For center we subtract 1 pixel since this makes the middle
	    // be prefectly in the middle
	    $x -= $dir==0 ? $w/2-1 : $h/2;
	}
	if( $this->text_valign=="top" )
	    $y += $dir==0 ? $h : $w;
	elseif( $this->text_valign=="center" ) 				
	    $y += $dir==0 ? $h/2 : $w/2;
	
	if( $dir==90 ) {
	    imagestringup($this->img,$this->font_family,$x,$y,$txt,$this->current_color);
	    $aBoundingBox = array(round($x),round($y),round($x),round($y-$w),round($x+$h),round($y-$w),round($x+$h),round($y));
            if( $aDebug ) {
		// Draw bounding box
		$this->PushColor('green');
		$this->Polygon($aBoundingBox,true);
		$this->PopColor();
	    }
	}
	else {
	    if( ereg("\n",$txt) ) { 
		$tmp = split("\n",$txt);
		for($i=0; $i < count($tmp); ++$i) {
		    $w1 = $this->GetTextWidth($tmp[$i]);
		    if( $paragraph_align=="left" ) {
			imagestring($this->img,$this->font_family,$x,$y-$h+1+$i*$fh,$tmp[$i],$this->current_color);
		    }
		    elseif( $paragraph_align=="right" ) {
			imagestring($this->img,$this->font_family,$x+($w-$w1),
				    $y-$h+1+$i*$fh,$tmp[$i],$this->current_color);
		    }
		    else {
			imagestring($this->img,$this->font_family,$x+$w/2-$w1/2,
				    $y-$h+1+$i*$fh,$tmp[$i],$this->current_color);
		    }
		}
	    } 
	    else {
		//Put the text
		imagestring($this->img,$this->font_family,$x,$y-$h+1,$txt,$this->current_color);
	    }
            if( $aDebug ) {
		// Draw the bounding rectangle and the bounding box
		$p1 = array(round($x),round($y),round($x),round($y-$h),round($x+$w),round($y-$h),round($x+$w),round($y));
		
		// Draw bounding box
		$this->PushColor('green');
		$this->Polygon($p1,true);
		$this->PopColor();

            }
	    $aBoundingBox=array(round($x),round($y),round($x),round($y-$h),round($x+$w),round($y-$h),round($x+$w),round($y));
	}
    }

    function AddTxtCR($aTxt) {
	// If the user has just specified a '\n'
	// instead of '\n\t' we have to add '\r' since
	// the width will be too muchy otherwise since when
	// we print we stroke the individually lines by hand.
	$e = explode("\n",$aTxt);
	$n = count($e);
	for($i=0; $i<$n; ++$i) {
	    $e[$i]=str_replace("\r","",$e[$i]);
	}
	return implode("\n\r",$e);
    }

    function GetTTFBBox($aTxt,$aAngle=0) {
	$bbox = @ImageTTFBBox($this->font_size,$aAngle,$this->font_file,$aTxt);
	if( $bbox === false ) {
	    JpGraphError::RaiseL(25092,$this->font_file);
//("There is either a configuration problem with TrueType or a problem reading font file (".$this->font_file."). Make sure file exists and is in a readable place for the HTTP process. (If 'basedir' restriction is enabled in PHP then the font file must be located in the document root.). It might also be a wrongly installed FreeType library. Try uppgrading to at least FreeType 2.1.13 and recompile GD with the correct setup so it can find the new FT library.");
	}
	return $bbox;
    }

    function GetBBoxTTF($aTxt,$aAngle=0) {
	// Normalize the bounding box to become a minimum
	// enscribing rectangle

	$aTxt = $this->AddTxtCR($aTxt);

	if( !is_readable($this->font_file) ) {
	    JpGraphError::RaiseL(25093,$this->font_file);
//('Can not read font file ('.$this->font_file.') in call to Image::GetBBoxTTF. Please make sure that you have set a font before calling this method and that the font is installed in the TTF directory.');
	}
	$bbox = $this->GetTTFBBox($aTxt,$aAngle);

	if( $aAngle==0 ) 
	    return $bbox;
	if( $aAngle >= 0 ) {
	    if(  $aAngle <= 90 ) { //<=0		
		$bbox = array($bbox[6],$bbox[1],$bbox[2],$bbox[1],
			      $bbox[2],$bbox[5],$bbox[6],$bbox[5]);
	    }
	    elseif(  $aAngle <= 180 ) { //<= 2
		$bbox = array($bbox[4],$bbox[7],$bbox[0],$bbox[7],
			      $bbox[0],$bbox[3],$bbox[4],$bbox[3]);
	    }
	    elseif(  $aAngle <= 270 )  { //<= 3
		$bbox = array($bbox[2],$bbox[5],$bbox[6],$bbox[5],
			      $bbox[6],$bbox[1],$bbox[2],$bbox[1]);
	    }
	    else {
		$bbox = array($bbox[0],$bbox[3],$bbox[4],$bbox[3],
			      $bbox[4],$bbox[7],$bbox[0],$bbox[7]);
	    }
	}
	elseif(  $aAngle < 0 ) {
	    if( $aAngle <= -270 ) { // <= -3
		$bbox = array($bbox[6],$bbox[1],$bbox[2],$bbox[1],
			      $bbox[2],$bbox[5],$bbox[6],$bbox[5]);
	    }
	    elseif( $aAngle <= -180 ) { // <= -2
		$bbox = array($bbox[0],$bbox[3],$bbox[4],$bbox[3],
			      $bbox[4],$bbox[7],$bbox[0],$bbox[7]);
	    }
	    elseif( $aAngle <= -90 ) { // <= -1
		$bbox = array($bbox[2],$bbox[5],$bbox[6],$bbox[5],
			      $bbox[6],$bbox[1],$bbox[2],$bbox[1]);
	    }
	    else {
		$bbox = array($bbox[0],$bbox[3],$bbox[4],$bbox[3],
			      $bbox[4],$bbox[7],$bbox[0],$bbox[7]);
	    }
	}	
	return $bbox;
    }

    function GetBBoxHeight($aTxt,$aAngle=0) {
	$box = $this->GetBBoxTTF($aTxt,$aAngle);
	return $box[1]-$box[7]+1;
    }

    function GetBBoxWidth($aTxt,$aAngle=0) {
	$box = $this->GetBBoxTTF($aTxt,$aAngle);
	return $box[2]-$box[0]+1;	
    }

    function _StrokeTTF($x,$y,$txt,$dir=0,$paragraph_align="left",&$aBoundingBox,$debug=false) {

	// Setupo default inter line margin for paragraphs to
	// 25% of the font height.
	$ConstLineSpacing = 0.25 ;

	// Remember the anchor point before adjustment
	if( $debug ) {
	    $ox=$x;
	    $oy=$y;
	}

	if( !ereg("\n",$txt) || ($dir>0 && ereg("\n",$txt)) ) {
	    // Format a single line

	    $txt = $this->AddTxtCR($txt);

	    $bbox=$this->GetBBoxTTF($txt,$dir);
	    
	    // Align x,y ot lower left corner of bbox
	    $x -= $bbox[0];
	    $y -= $bbox[1];

	    // Note to self: "topanchor" is deprecated after we changed the
	    // bopunding box stuff. 
	    if( $this->text_halign=="right" || $this->text_halign=="topanchor" ) 
		$x -= $bbox[2]-$bbox[0];
	    elseif( $this->text_halign=="center" ) $x -= ($bbox[2]-$bbox[0])/2; 
	    
	    if( $this->text_valign=="top" ) $y += abs($bbox[5])+$bbox[1];
	    elseif( $this->text_valign=="center" ) $y -= ($bbox[5]-$bbox[1])/2; 

	    ImageTTFText ($this->img, $this->font_size, $dir, $x, $y, 
			  $this->current_color,$this->font_file,$txt); 

	    // Calculate and return the co-ordinates for the bounding box
	    $box=@ImageTTFBBox($this->font_size,$dir,$this->font_file,$txt);
	    $p1 = array();


	    for($i=0; $i < 4; ++$i) {
		$p1[] = round($box[$i*2]+$x);
		$p1[] = round($box[$i*2+1]+$y);
	    }
	    $aBoundingBox = $p1;

	    // Debugging code to highlight the bonding box and bounding rectangle
	    // For text at 0 degrees the bounding box and bounding rectangle are the
	    // same
            if( $debug ) {
		// Draw the bounding rectangle and the bounding box
		$box=@ImageTTFBBox($this->font_size,$dir,$this->font_file,$txt);
		$p = array();
		$p1 = array();
		for($i=0; $i < 4; ++$i) {
		    $p[] = $bbox[$i*2]+$x;
		    $p[] = $bbox[$i*2+1]+$y;
		    $p1[] = $box[$i*2]+$x;
		    $p1[] = $box[$i*2+1]+$y;
		}

		// Draw bounding box
		$this->PushColor('green');
		$this->Polygon($p1,true);
		$this->PopColor();
		
		// Draw bounding rectangle
		$this->PushColor('darkgreen');
		$this->Polygon($p,true);
		$this->PopColor();
		
		// Draw a cross at the anchor point
		$this->PushColor('red');
		$this->Line($ox-15,$oy,$ox+15,$oy);
		$this->Line($ox,$oy-15,$ox,$oy+15);
		$this->PopColor();
            }
	}
	else {
	    // Format a text paragraph
	    $fh=$this->GetFontHeight();

	    // Line margin is 25% of font height
	    $linemargin=round($fh*$ConstLineSpacing);
	    $fh += $linemargin;
	    $w=$this->GetTextWidth($txt);

	    $y -= $linemargin/2;
	    $tmp = split("\n",$txt);
	    $nl = count($tmp);
	    $h = $nl * $fh;

	    if( $this->text_halign=="right") 				
		$x -= $dir==0 ? $w : $h;
	    elseif( $this->text_halign=="center" ) {
		$x -= $dir==0 ? $w/2 : $h/2;
	    }
	    
	    if( $this->text_valign=="top" )
		$y +=	$dir==0 ? $h : $w;
	    elseif( $this->text_valign=="center" ) 				
		$y +=	$dir==0 ? $h/2 : $w/2;

	    // Here comes a tricky bit. 
	    // Since we have to give the position for the string at the
	    // baseline this means thaht text will move slightly up
	    // and down depending on any of it's character descend below
	    // the baseline, for example a 'g'. To adjust the Y-position
	    // we therefore adjust the text with the baseline Y-offset
	    // as used for the current font and size. This will keep the
	    // baseline at a fixed positoned disregarding the actual 
	    // characters in the string. 
	    $standardbox = $this->GetTTFBBox('Gg',$dir);
	    $yadj = $standardbox[1];
	    $xadj = $standardbox[0];
	    $aBoundingBox = array();
	    for($i=0; $i < $nl; ++$i) {
		$wl = $this->GetTextWidth($tmp[$i]);
		$bbox = $this->GetTTFBBox($tmp[$i],$dir);
		if( $paragraph_align=="left" ) {
		    $xl = $x; 
		}
		elseif( $paragraph_align=="right" ) {
		    $xl = $x + ($w-$wl);
		}
		else {
		    // Center
		    $xl = $x + $w/2 - $wl/2 ;
		}

		$xl -= $bbox[0];
		$yl = $y - $yadj; 
		$xl = $xl - $xadj; 
		ImageTTFText ($this->img, $this->font_size, $dir, 
			      $xl, $yl-($h-$fh)+$fh*$i,
			      $this->current_color,$this->font_file,$tmp[$i]); 

		if( $debug  ) {
		    // Draw the bounding rectangle around each line
		    $box=@ImageTTFBBox($this->font_size,$dir,$this->font_file,$tmp[$i]);
		    $p = array();
		    for($j=0; $j < 4; ++$j) {
			$p[] = $bbox[$j*2]+$xl;
			$p[] = $bbox[$j*2+1]+$yl-($h-$fh)+$fh*$i;
		    }
		    
		    // Draw bounding rectangle
		    $this->PushColor('darkgreen');
		    $this->Polygon($p,true);
		    $this->PopColor();
		}
	    }

	    // Get the bounding box
	    $bbox = $this->GetBBoxTTF($txt,$dir);
	    for($j=0; $j < 4; ++$j) {
		$bbox[$j*2]+= round($x);
		$bbox[$j*2+1]+= round($y - ($h-$fh) - $yadj);
	    }
	    $aBoundingBox = $bbox;

	    if( $debug ) {	
		// Draw a cross at the anchor point
		$this->PushColor('red');
		$this->Line($ox-25,$oy,$ox+25,$oy);
		$this->Line($ox,$oy-25,$ox,$oy+25);
		$this->PopColor();
	    }

	}
    }
	
    function StrokeText($x,$y,$txt,$dir=0,$paragraph_align="left",$debug=false) {

	$x = round($x);
	$y = round($y);

	// Do special language encoding
	$txt = $this->langconv->Convert($txt,$this->font_family);

	if( !is_numeric($dir) )
	    JpGraphError::RaiseL(25094);//(" Direction for text most be given as an angle between 0 and 90.");
			
	if( $this->font_family >= FF_FONT0 && $this->font_family <= FF_FONT2+1) {	
	    $this->_StrokeBuiltinFont($x,$y,$txt,$dir,$paragraph_align,$boundingbox,$debug);
	}
	elseif($this->font_family >= _FF_FIRST && $this->font_family <= _FF_LAST)  {
	    $this->_StrokeTTF($x,$y,$txt,$dir,$paragraph_align,$boundingbox,$debug);
	}
	else
	    JpGraphError::RaiseL(25095);//(" Unknown font font family specification. ");
	return $boundingbox;
    }
	
    function SetMargin($lm,$rm,$tm,$bm) {
	$this->left_margin=$lm;
	$this->right_margin=$rm;
	$this->top_margin=$tm;
	$this->bottom_margin=$bm;
	$this->plotwidth=$this->width - $this->left_margin-$this->right_margin ; 
	$this->plotheight=$this->height - $this->top_margin-$this->bottom_margin ;
	if( $this->width  > 0 && $this->height > 0 ) {
	    if( $this->plotwidth < 0  || $this->plotheight < 0 )
		JpGraphError::raise("Too small plot area. ($lm,$rm,$tm,$bm : $this->plotwidth x $this->plotheight). With the given image size and margins there is to little space left for the plot. Increase the plot size or reduce the margins.");
	}
    }

    function SetTransparent($color) {
	imagecolortransparent ($this->img,$this->rgb->allocate($color));
    }
	
    function SetColor($color,$aAlpha=0) {
	$this->current_color_name = $color;
	$this->current_color=$this->rgb->allocate($color,$aAlpha);
	if( $this->current_color == -1 ) {
	    JpGraphError::RaiseL(25096);
//("Can't allocate any more colors."); 
	}
	return $this->current_color;
    }
	
    function PushColor($color) {
	if( $color != "" ) {
	    $this->colorstack[$this->colorstackidx]=$this->current_color_name;
	    $this->colorstack[$this->colorstackidx+1]=$this->current_color;
	    $this->colorstackidx+=2;
	    $this->SetColor($color);
	}
	else {
	    JpGraphError::RaiseL(25097);//("Color specified as empty string in PushColor().");
	}
    }
	
    function PopColor() {
	if($this->colorstackidx<1)
	    JpGraphError::RaiseL(25098);//(" Negative Color stack index. Unmatched call to PopColor()");
	$this->current_color=$this->colorstack[--$this->colorstackidx];
	$this->current_color_name=$this->colorstack[--$this->colorstackidx];
    }
	
	
    function SetLineWeight($weight) {
	$this->line_weight = $weight;
    }
	
    function SetStartPoint($x,$y) {
	$this->lastx=round($x);
	$this->lasty=round($y);
    }
	
    function Arc($cx,$cy,$w,$h,$s,$e) {
	// GD Arc doesn't like negative angles
	while( $s < 0) $s += 360;
	while( $e < 0) $e += 360;
    	
	imagearc($this->img,round($cx),round($cy),round($w),round($h),
		 $s,$e,$this->current_color);
    }
    
    function FilledArc($xc,$yc,$w,$h,$s,$e,$style="") {

	while( $s < 0 ) $s += 360;
	while( $e < 0 ) $e += 360;
	if( $style=="" ) 
	    $style=IMG_ARC_PIE;
	
	// Workaround for bug in 4.4.7 which will not draw a correct 360
	// degree slice with any other angles than 0,360
	if( 360-abs($s-$e) < 0.01 ) {
	    $s = 0;
	    $e = 360;
	}
	if( abs($s-$e) > 0.001 ) {
	    imagefilledarc($this->img,round($xc),round($yc),round($w),round($h),
			   round($s),round($e),$this->current_color,$style);
	}
    }

    function FilledCakeSlice($cx,$cy,$w,$h,$s,$e) {
	$this->CakeSlice($cx,$cy,$w,$h,$s,$e,$this->current_color_name);
    }

    function CakeSlice($xc,$yc,$w,$h,$s,$e,$fillcolor="",$arccolor="") {
	$s = round($s); $e = round($e);
	$w = round($w); $h = round($h);
	$xc = round($xc); $yc = round($yc);

	if( $s==$e ) { 
	    // A full circle. We draw this a plain circle
	    $this->PushColor($fillcolor);
	    imagefilledellipse($this->img,$xc,$yc,2*$w,2*$h,$this->current_color);
	    $this->PopColor();
	    $this->PushColor($arccolor);
	    imageellipse($this->img,$xc,$yc,2*$w,2*$h,$this->current_color);
	    $this->Line($xc,$yc,cos($s*M_PI/180)*$w+$xc,$yc+sin($s*M_PI/180)*$h);
	    $this->PopColor();
	}
	else {
	    $this->PushColor($fillcolor);
	    $this->FilledArc($xc,$yc,2*$w,2*$h,$s,$e);
	    $this->PopColor();
	    if( $arccolor != "" ) {
		$this->PushColor($arccolor);
		// We add 2 pixels to make the Arc() better aligned with the filled arc. 
		imagefilledarc($this->img,$xc,$yc,2*$w,2*$h,$s,$e,$this->current_color,IMG_ARC_NOFILL | IMG_ARC_EDGED ) ;

		// Workaround for bug in 4.4.7 which will not draw a correct 360
		// degree slice with any other angles than 0,360. Unfortunately we cannot just
		// adjust the angles since the interior ar edge is drawn correct but not the surrounding
		// circle. This workaround can only be used with perfect circle shaped arcs
		if( PHP_VERSION==='4.4.7' && (360-abs($s-$e) < 0.01 && $w==$h) ) {
		    $this->Circle($xc,$yc,$w);
		}
		$this->PopColor();
	    }
	}
    }

    function Ellipse($xc,$yc,$w,$h) {
	$this->Arc($xc,$yc,$w,$h,0,360);
    }
	
    // Breseham circle gives visually better result then using GD
    // built in arc(). It takes some more time but gives better
    // accuracy.
    function BresenhamCircle($xc,$yc,$r) {
	$d = 3-2*$r;
	$x = 0;
	$y = $r;
	while($x<=$y) {
	    $this->Point($xc+$x,$yc+$y);			
	    $this->Point($xc+$x,$yc-$y);
	    $this->Point($xc-$x,$yc+$y);
	    $this->Point($xc-$x,$yc-$y);
			
	    $this->Point($xc+$y,$yc+$x);
	    $this->Point($xc+$y,$yc-$x);
	    $this->Point($xc-$y,$yc+$x);
	    $this->Point($xc-$y,$yc-$x);
			
	    if( $d<0 ) $d += 4*$x+6;
	    else {
		$d += 4*($x-$y)+10;		
		--$y;
	    }
	    ++$x;
	}
    }
			
    function Circle($xc,$yc,$r) {
	if( USE_BRESENHAM )
	    $this->BresenhamCircle($xc,$yc,$r);
	else {

	    /*
            // Some experimental code snippet to see if we can get a decent 
	    // result doing a trig-circle
	    // Create an approximated circle with 0.05 rad resolution
	    $end = 2*M_PI;
	    $l = $r/10;
	    if( $l < 3 ) $l=3;
	    $step_size = 2*M_PI/(2*$r*M_PI/$l);
	    $pts = array();
	    $pts[] = $r + $xc;
	    $pts[] = $yc;
	    for( $a=$step_size; $a <= $end; $a += $step_size ) {
		$pts[] = round($xc + $r*cos($a));
		$pts[] = round($yc - $r*sin($a));
	    }
	    imagepolygon($this->img,$pts,count($pts)/2,$this->current_color);
	    */

	    $this->Arc($xc,$yc,$r*2,$r*2,0,360);		

	    // For some reason imageellipse() isn't in GD 2.0.1, PHP 4.1.1
	    //imageellipse($this->img,$xc,$yc,$r,$r,$this->current_color);
	}
    }
	
    function FilledCircle($xc,$yc,$r) {
	imagefilledellipse($this->img,round($xc),round($yc),2*$r,2*$r,$this->current_color);
    }
	
    // Linear Color InterPolation
    function lip($f,$t,$p) {
	$p = round($p,1);
	$r = $f[0] + ($t[0]-$f[0])*$p;
	$g = $f[1] + ($t[1]-$f[1])*$p;
	$b = $f[2] + ($t[2]-$f[2])*$p;
	return array($r,$g,$b);
    }

    // Anti-aliased line. 
    // Note that this is roughly 8 times slower then a normal line!
    function WuLine($x1,$y1,$x2,$y2) {
	// Get foreground line color
	$lc = imagecolorsforindex($this->img,$this->current_color);
	$lc = array($lc["red"],$lc["green"],$lc["blue"]);

	$dx = $x2-$x1;
	$dy = $y2-$y1;
	
	if( abs($dx) > abs($dy) ) {
	    if( $dx<0 ) {
		$dx = -$dx;$dy = -$dy;
		$tmp=$x2;$x2=$x1;$x1=$tmp;
		$tmp=$y2;$y2=$y1;$y1=$tmp;
	    }
	    $x=$x1<<16; $y=$y1<<16;
	    $yinc = ($dy*65535)/$dx;
	    $first=true;
	    while( ($x >> 16) < $x2 ) {
				
		$bc = @imagecolorsforindex($this->img,imagecolorat($this->img,$x>>16,$y>>16));
		if( $bc <= 0 ) {
		    JpGraphError::RaiseL(25100);//('Problem with color palette and your GD setup. Please disable anti-aliasing or use GD2 with true-color. If you have GD2 library installed please make sure that you have set the USE_GD2 constant to true and that truecolor is enabled.');
		}
		$bc=array($bc["red"],$bc["green"],$bc["blue"]);
				
		$this->SetColor($this->lip($lc,$bc,($y & 0xFFFF)/65535));
		imagesetpixel($this->img,$x>>16,$y>>16,$this->current_color);
		$this->SetColor($this->lip($lc,$bc,(~$y & 0xFFFF)/65535));
		if( !$first ) 
		    imagesetpixel($this->img,$x>>16,($y>>16)+1,$this->current_color);
		$x += 65536; $y += $yinc;
		$first=false;
	    }
	}
	else {
	    if( $dy<0 ) {
		$dx = -$dx;$dy = -$dy;
		$tmp=$x2;$x2=$x1;$x1=$tmp;
		$tmp=$y2;$y2=$y1;$y1=$tmp;
	    }
	    $x=$x1<<16; $y=$y1<<16;
	    $xinc = ($dx*65535)/$dy;	
	    $first = true;
	    while( ($y >> 16) < $y2 ) {
				
		$bc = @imagecolorsforindex($this->img,imagecolorat($this->img,$x>>16,$y>>16));
		if( $bc <= 0 ) {
		    JpGraphError::RaiseL(25100);//('Problem with color palette and your GD setup. Please disable anti-aliasing or use GD2 with true-color. If you have GD2 library installed please make sure that you have set the USE_GD2 constant to true and truecolor is enabled.');

		}

		$bc=array($bc["red"],$bc["green"],$bc["blue"]);				
				
		$this->SetColor($this->lip($lc,$bc,($x & 0xFFFF)/65535));
		imagesetpixel($this->img,$x>>16,$y>>16,$this->current_color);
		$this->SetColor($this->lip($lc,$bc,(~$x & 0xFFFF)/65535));
		if( !$first ) 
		    imagesetpixel($this->img,($x>>16)+1,$y>>16,$this->current_color);
		$y += 65536; $x += $xinc;
		$first = false;
	    }
	}
	$this->SetColor($lc);
	//imagesetpixel($this->img,$x2,$y2,$this->current_color);		
	//imagesetpixel($this->img,$x1,$y1,$this->current_color);			
    }

    // Set line style dashed, dotted etc
    function SetLineStyle($s) {
	if( is_numeric($s) ) {
	    if( $s<1 || $s>4 ) 
		JpGraphError::RaiseL(25101,$s);//(" Illegal numeric argument to SetLineStyle(): ($s)");
	}
	elseif( is_string($s) ) {
	    if( $s == "solid" ) $s=1;
	    elseif( $s == "dotted" ) $s=2;
	    elseif( $s == "dashed" ) $s=3;
	    elseif( $s == "longdashed" ) $s=4;
	    else JpGraphError::RaiseL(25102,$s);//(" Illegal string argument to SetLineStyle(): $s");
	}
	else {
	    JpGraphError::RaiseL(25103,$s);//(" Illegal argument to SetLineStyle $s");
	}
	$old = $this->line_style;
	$this->line_style=$s;
	return $old;
    }
	
    // Same as Line but take the line_style into account
    function StyleLine($x1,$y1,$x2,$y2) {
	switch( $this->line_style ) {
	    case 1:// Solid
		$this->Line($x1,$y1,$x2,$y2);
		break;
	    case 2: // Dotted
		$this->DashedLine($x1,$y1,$x2,$y2,1,6);
		break;
	    case 3: // Dashed
		$this->DashedLine($x1,$y1,$x2,$y2,2,4);
		break;
	    case 4: // Longdashes
		$this->DashedLine($x1,$y1,$x2,$y2,8,6);
		break;
	    default:
		JpGraphError::RaiseL(25104,$this->line_style);//(" Unknown line style: $this->line_style ");
		break;
	}
    }

    function Line($x1,$y1,$x2,$y2) {

	$x1 = round($x1);
	$x2 = round($x2);
	$y1 = round($y1);
	$y2 = round($y2);

	if( $this->line_weight==0 ) return;
	if( $this->use_anti_aliasing ) {
	    $dx = $x2-$x1;
	    $dy = $y2-$y1;
	    // Vertical, Horizontal or 45 lines don't need anti-aliasing
	    if( $dx!=0 && $dy!=0 && $dx!=$dy ) {
		$this->WuLine($x1,$y1,$x2,$y2);
		return;
	    }
	}
	if( $this->line_weight==1 ) {
	    imageline($this->img,$x1,$y1,$x2,$y2,$this->current_color);
	}
	elseif( $x1==$x2 ) {		// Special case for vertical lines
	    imageline($this->img,$x1,$y1,$x2,$y2,$this->current_color);
	    $w1=floor($this->line_weight/2);
	    $w2=floor(($this->line_weight-1)/2);
	    for($i=1; $i<=$w1; ++$i) 
		imageline($this->img,$x1+$i,$y1,$x2+$i,$y2,$this->current_color);
	    for($i=1; $i<=$w2; ++$i) 
		imageline($this->img,$x1-$i,$y1,$x2-$i,$y2,$this->current_color);
	}
	elseif( $y1==$y2 ) {		// Special case for horizontal lines
	    imageline($this->img,$x1,$y1,$x2,$y2,$this->current_color);
	    $w1=floor($this->line_weight/2);
	    $w2=floor(($this->line_weight-1)/2);
	    for($i=1; $i<=$w1; ++$i) 
		imageline($this->img,$x1,$y1+$i,$x2,$y2+$i,$this->current_color);
	    for($i=1; $i<=$w2; ++$i) 
		imageline($this->img,$x1,$y1-$i,$x2,$y2-$i,$this->current_color);		
	}
	else {	// General case with a line at an angle
	    $a = atan2($y1-$y2,$x2-$x1);
	    // Now establish some offsets from the center. This gets a little
	    // bit involved since we are dealing with integer functions and we
	    // want the apperance to be as smooth as possible and never be thicker
	    // then the specified width.
			
	    // We do the trig stuff to make sure that the endpoints of the line
	    // are perpendicular to the line itself.
	    $dx=(sin($a)*$this->line_weight/2);
	    $dy=(cos($a)*$this->line_weight/2);

	    $pnts = array(round($x2+$dx),round($y2+$dy),round($x2-$dx),round($y2-$dy),
			  round($x1-$dx),round($y1-$dy),round($x1+$dx),round($y1+$dy));
	    imagefilledpolygon($this->img,$pnts,count($pnts)/2,$this->current_color);
	}		
	$this->lastx=$x2; $this->lasty=$y2;		
    }

    function Polygon($p,$closed=FALSE,$fast=FALSE) {
	if( $this->line_weight==0 ) return;
	$n=count($p);
	$oldx = $p[0];
	$oldy = $p[1];
	if( $fast ) {
	    for( $i=2; $i < $n; $i+=2 ) {
		imageline($this->img,$oldx,$oldy,$p[$i],$p[$i+1],$this->current_color);
		$oldx = $p[$i];
		$oldy = $p[$i+1];
	    }
	    if( $closed ) {
		imageline($this->img,$p[$n*2-2],$p[$n*2-1],$p[0],$p[1],$this->current_color);
	    }
	}
	else {
	    for( $i=2; $i < $n; $i+=2 ) {
		$this->StyleLine($oldx,$oldy,$p[$i],$p[$i+1]);
		$oldx = $p[$i];
		$oldy = $p[$i+1];
	    }
	    if( $closed )
		$this->Line($oldx,$oldy,$p[0],$p[1]);
	}
    }
	
    function FilledPolygon($pts) {
	$n=count($pts);
	if( $n == 0 ) {
	    JpGraphError::RaiseL(25105);//('NULL data specified for a filled polygon. Check that your data is not NULL.');
	}
	for($i=0; $i < $n; ++$i) 
	    $pts[$i] = round($pts[$i]);
	imagefilledpolygon($this->img,$pts,count($pts)/2,$this->current_color);
    }
	
    function Rectangle($xl,$yu,$xr,$yl) {
	$this->Polygon(array($xl,$yu,$xr,$yu,$xr,$yl,$xl,$yl,$xl,$yu));
    }
	
    function FilledRectangle($xl,$yu,$xr,$yl) {
	$this->FilledPolygon(array($xl,$yu,$xr,$yu,$xr,$yl,$xl,$yl));
    }

    function FilledRectangle2($xl,$yu,$xr,$yl,$color1,$color2,$style=1) {
	// Fill a rectangle with lines of two colors
	if( $style===1 ) {
	    // Horizontal stripe
	    if( $yl < $yu ) {
		$t = $yl; $yl=$yu; $yu=$t;
	    }
	    for( $y=$yu; $y <= $yl; ++$y) {
		$this->SetColor($color1);
		$this->Line($xl,$y,$xr,$y);
		++$y;
		$this->SetColor($color2);
		$this->Line($xl,$y,$xr,$y);
	    }
	}
	else {
	    if( $xl < $xl ) {
		$t = $xl; $xl=$xr; $xr=$t;
	    }
	    for( $x=$xl; $x <= $xr; ++$x) {
		$this->SetColor($color1);
		$this->Line($x,$yu,$x,$yl);
		++$x;
		$this->SetColor($color2);
		$this->Line($x,$yu,$x,$yl);
	    }
	}
    }

    function ShadowRectangle($xl,$yu,$xr,$yl,$fcolor=false,$shadow_width=3,$shadow_color=array(102,102,102)) {
	// This is complicated by the fact that we must also handle the case where
        // the reactangle has no fill color
	$this->PushColor($shadow_color);
	$this->FilledRectangle($xr-$shadow_width,$yu+$shadow_width,$xr,$yl-$shadow_width-1);
	$this->FilledRectangle($xl+$shadow_width,$yl-$shadow_width,$xr,$yl);
	//$this->FilledRectangle($xl+$shadow_width,$yu+$shadow_width,$xr,$yl);
	$this->PopColor();
	if( $fcolor==false )
	    $this->Rectangle($xl,$yu,$xr-$shadow_width-1,$yl-$shadow_width-1);
	else {		
	    $this->PushColor($fcolor);
	    $this->FilledRectangle($xl,$yu,$xr-$shadow_width-1,$yl-$shadow_width-1);
	    $this->PopColor();
	    $this->Rectangle($xl,$yu,$xr-$shadow_width-1,$yl-$shadow_width-1);
	}
    }

    function FilledRoundedRectangle($xt,$yt,$xr,$yl,$r=5) {
	if( $r==0 ) {
	    $this->FilledRectangle($xt,$yt,$xr,$yl);
	    return;
	}

	// To avoid overlapping fillings (which will look strange
	// when alphablending is enabled) we have no choice but 
	// to fill the five distinct areas one by one.
	
	// Center square
	$this->FilledRectangle($xt+$r,$yt+$r,$xr-$r,$yl-$r);
	// Top band
	$this->FilledRectangle($xt+$r,$yt,$xr-$r,$yt+$r-1);
	// Bottom band
	$this->FilledRectangle($xt+$r,$yl-$r+1,$xr-$r,$yl);
	// Left band
	$this->FilledRectangle($xt,$yt+$r+1,$xt+$r-1,$yl-$r);
	// Right band
	$this->FilledRectangle($xr-$r+1,$yt+$r,$xr,$yl-$r);

	// Topleft & Topright arc
	$this->FilledArc($xt+$r,$yt+$r,$r*2,$r*2,180,270);
	$this->FilledArc($xr-$r,$yt+$r,$r*2,$r*2,270,360);

	// Bottomleft & Bottom right arc
	$this->FilledArc($xt+$r,$yl-$r,$r*2,$r*2,90,180);
	$this->FilledArc($xr-$r,$yl-$r,$r*2,$r*2,0,90);

    }

    function RoundedRectangle($xt,$yt,$xr,$yl,$r=5) {    

	if( $r==0 ) {
	    $this->Rectangle($xt,$yt,$xr,$yl);
	    return;
	}

	// Top & Bottom line
	$this->Line($xt+$r,$yt,$xr-$r,$yt);
	$this->Line($xt+$r,$yl,$xr-$r,$yl);

	// Left & Right line
	$this->Line($xt,$yt+$r,$xt,$yl-$r);
	$this->Line($xr,$yt+$r,$xr,$yl-$r);

	// Topleft & Topright arc
	$this->Arc($xt+$r,$yt+$r,$r*2,$r*2,180,270);
	$this->Arc($xr-$r,$yt+$r,$r*2,$r*2,270,360);

	// Bottomleft & Bottomright arc
	$this->Arc($xt+$r,$yl-$r,$r*2,$r*2,90,180);
	$this->Arc($xr-$r,$yl-$r,$r*2,$r*2,0,90);
    }

    function FilledBevel($x1,$y1,$x2,$y2,$depth=2,$color1='white@0.4',$color2='darkgray@0.4') {
	$this->FilledRectangle($x1,$y1,$x2,$y2);
	$this->Bevel($x1,$y1,$x2,$y2,$depth,$color1,$color2);
    }

    function Bevel($x1,$y1,$x2,$y2,$depth=2,$color1='white@0.4',$color2='black@0.5') {
	$this->PushColor($color1);
	for( $i=0; $i < $depth; ++$i ) {
	    $this->Line($x1+$i,$y1+$i,$x1+$i,$y2-$i);
	    $this->Line($x1+$i,$y1+$i,$x2-$i,$y1+$i);
	}
	$this->PopColor();
	
	$this->PushColor($color2);
	for( $i=0; $i < $depth; ++$i ) {
	    $this->Line($x1+$i,$y2-$i,$x2-$i,$y2-$i);
	    $this->Line($x2-$i,$y1+$i,$x2-$i,$y2-$i-1);
	}
	$this->PopColor();
    }

    function StyleLineTo($x,$y) {
	$this->StyleLine($this->lastx,$this->lasty,$x,$y);
	$this->lastx=$x;
	$this->lasty=$y;
    }
	
    function LineTo($x,$y) {
	$this->Line($this->lastx,$this->lasty,$x,$y);
	$this->lastx=$x;
	$this->lasty=$y;
    }
	
    function Point($x,$y) {
	imagesetpixel($this->img,round($x),round($y),$this->current_color);
    }
	
    function Fill($x,$y) {
	imagefill($this->img,round($x),round($y),$this->current_color);
    }

    function FillToBorder($x,$y,$aBordColor) {
	$bc = $this->rgb->allocate($aBordColor);
	if( $bc == -1 ) {
	    JpGraphError::RaiseL(25106);//('Image::FillToBorder : Can not allocate more colors');
	}
	imagefilltoborder($this->img,round($x),round($y),$bc,$this->current_color);
    }
	
    function DashedLine($x1,$y1,$x2,$y2,$dash_length=1,$dash_space=4) {

	$x1 = round($x1);
	$x2 = round($x2);
	$y1 = round($y1);
	$y2 = round($y2);

	// Code based on, but not identical to, work by Ariel Garza and James Pine
	$line_length = ceil (sqrt(pow(($x2 - $x1),2) + pow(($y2 - $y1),2)) );
	$dx = ($line_length) ? ($x2 - $x1) / $line_length : 0;
	$dy = ($line_length) ? ($y2 - $y1) / $line_length : 0;
	$lastx = $x1; $lasty = $y1;
	$xmax = max($x1,$x2);
	$xmin = min($x1,$x2);
	$ymax = max($y1,$y2);
	$ymin = min($y1,$y2);
	for ($i = 0; $i < $line_length; $i += ($dash_length + $dash_space)) {
	    $x = ($dash_length * $dx) + $lastx;
	    $y = ($dash_length * $dy) + $lasty;
			
	    // The last section might overshoot so we must take a computational hit
	    // and check this.
	    if( $x>$xmax ) $x=$xmax;
	    if( $y>$ymax ) $y=$ymax;
			
	    if( $x<$xmin ) $x=$xmin;
	    if( $y<$ymin ) $y=$ymin;

	    $this->Line($lastx,$lasty,$x,$y);
	    $lastx = $x + ($dash_space * $dx);
	    $lasty = $y + ($dash_space * $dy);
	} 
    } 

    function SetExpired($aFlg=true) {
	$this->expired = $aFlg;
    }
	
    // Generate image header
    function Headers() {
	
	// In case we are running from the command line with the client version of
	// PHP we can't send any headers.
	$sapi = php_sapi_name();
	if( $sapi == 'cli' )
	    return;

	// These parameters are set by headers_sent() but they might cause
	// an undefined variable error unless they are initilized
	$file='';
	$lineno='';
	if( headers_sent($file,$lineno) ) {
	    $file=basename($file);
	    $t = new ErrMsgText();
	    $msg = $t->Get(10,$file,$lineno);
	    die($msg);
	}	
	
	if ($this->expired) {
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
	    header("Cache-Control: no-cache, must-revalidate");
	    header("Pragma: no-cache");
	}
	header("Content-type: image/$this->img_format");
    }

    // Adjust image quality for formats that allow this
    function SetQuality($q) {
	$this->quality = $q;
    }
	
    // Stream image to browser or to file
    function Stream($aFile="") {
	$func="image".$this->img_format;
	if( $this->img_format=="jpeg" && $this->quality != null ) {
	    $res = @$func($this->img,$aFile,$this->quality);
	}
	else {
	    if( $aFile != "" ) {
		$res = @$func($this->img,$aFile);
		if( !$res )
		    JpGraphError::RaiseL(25107,$aFile);//("Can't write to file '$aFile'. Check that the process running PHP has enough permission.");
	    }
	    else {
		$res = @$func($this->img);
		if( !$res )
		    JpGraphError::RaiseL(25108);//("Can't stream image. This is most likely due to a faulty PHP/GD setup. Try to recompile PHP and use the built-in GD library that comes with PHP.");
		
	    }
	}
    }
		
    // Clear resource tide up by image
    function Destroy() {
	imagedestroy($this->img);
    }
	
    // Specify image format. Note depending on your installation
    // of PHP not all formats may be supported.
    function SetImgFormat($aFormat,$aQuality=75) {		
	$this->quality = $aQuality;
	$aFormat = strtolower($aFormat);
	$tst = true;
	$supported = imagetypes();
	if( $aFormat=="auto" ) {
	    if( $supported & IMG_PNG )
		$this->img_format="png";
	    elseif( $supported & IMG_JPG )
		$this->img_format="jpeg";
	    elseif( $supported & IMG_GIF )
		$this->img_format="gif";
	    elseif( $supported & IMG_WBMP )
		$this->img_format="wbmp";
	    elseif( $supported & IMG_XPM )
		$this->img_format="xpm";
	    else
		JpGraphError::RaiseL(25109);//("Your PHP (and GD-lib) installation does not appear to support any known graphic formats. You need to first make sure GD is compiled as a module to PHP. If you also want to use JPEG images you must get the JPEG library. Please see the PHP docs for details.");
				
	    return true;
	}
	else {
	    if( $aFormat=="jpeg" || $aFormat=="png" || $aFormat=="gif" || $aFormat=="wbmp" || $aFormat=="xpm") {
		if( $aFormat=="jpeg" && !($supported & IMG_JPG) )
		    $tst=false;
		elseif( $aFormat=="png" && !($supported & IMG_PNG) ) 
		    $tst=false;
		elseif( $aFormat=="gif" && !($supported & IMG_GIF) ) 	
		    $tst=false;
		elseif( $aFormat=="wbmp" && !($supported & IMG_WBMP) ) 	
		    $tst=false;
		elseif( $aFormat=="xpm" && !($supported & IMG_XPM) ) 	
		    $tst=false;
		else {
		    $this->img_format=$aFormat;
		    return true;
		}
	    }
	    else 
		$tst=false;
	    if( !$tst )
		JpGraphError::RaiseL(25110,$aFormat);//(" Your PHP installation does not support the chosen graphic format: $aFormat");
	}
    }	
} // CLASS

//===================================================
// CLASS RotImage
// Description: Exactly as Image but draws the image at
// a specified angle around a specified rotation point.
//===================================================
class RotImage extends Image {
    var $m=array();
    var $a=0;
    var $dx=0,$dy=0,$transx=0,$transy=0; 
	
    function RotImage($aWidth,$aHeight,$a=0,$aFormat=DEFAULT_GFORMAT,$aSetAutoMargin=true) {
	$this->Image($aWidth,$aHeight,$aFormat,$aSetAutoMargin);
	$this->dx=$this->left_margin+$this->plotwidth/2;
	$this->dy=$this->top_margin+$this->plotheight/2;
	$this->SetAngle($a);	
    }
	
    function SetCenter($dx,$dy) {
	$old_dx = $this->dx;
	$old_dy = $this->dy;
	$this->dx=$dx;
	$this->dy=$dy;
	$this->SetAngle($this->a);
	return array($old_dx,$old_dy);
    }
	
    function SetTranslation($dx,$dy) {
	$old = array($this->transx,$this->transy);
	$this->transx = $dx;
	$this->transy = $dy;
	return $old;
    }

    function UpdateRotMatrice()  {
	$a = $this->a;
	$a *= M_PI/180;
	$sa=sin($a); $ca=cos($a);		
	// Create the rotation matrix
	$this->m[0][0] = $ca;
	$this->m[0][1] = -$sa;
	$this->m[0][2] = $this->dx*(1-$ca) + $sa*$this->dy ;
	$this->m[1][0] = $sa;
	$this->m[1][1] = $ca;
	$this->m[1][2] = $this->dy*(1-$ca) - $sa*$this->dx ;
    }

    function SetAngle($a) {
	$tmp = $this->a;
	$this->a = $a;
	$this->UpdateRotMatrice();
	return $tmp;
    }

    function Circle($xc,$yc,$r) {
	// Circle get's rotated through the Arc() call
	// made in the parent class
	parent::Circle($xc,$yc,$r);
    }

    function FilledCircle($xc,$yc,$r) {
	list($xc,$yc) = $this->Rotate($xc,$yc);
	parent::FilledCircle($xc,$yc,$r);
    }
	
    function Arc($xc,$yc,$w,$h,$s,$e) {
	list($xc,$yc) = $this->Rotate($xc,$yc);
	$s += $this->a;
	$e += $this->a;
	parent::Arc($xc,$yc,$w,$h,$s,$e);
    }

    function FilledArc($xc,$yc,$w,$h,$s,$e) {
	list($xc,$yc) = $this->Rotate($xc,$yc);
	$s += $this->a;
	$e += $this->a;
	parent::FilledArc($xc,$yc,$w,$h,$s,$e);
    }

    function SetMargin($lm,$rm,$tm,$bm) {
	parent::SetMargin($lm,$rm,$tm,$bm);
	$this->dx=$this->left_margin+$this->plotwidth/2;
	$this->dy=$this->top_margin+$this->plotheight/2;
	$this->UpdateRotMatrice();
    }
	
    function Rotate($x,$y) {
	// Optimization. Ignore rotation if Angle==0 || ANgle==360
	if( $this->a == 0 || $this->a == 360 ) {
	    return array($x + $this->transx, $y + $this->transy );
	}
	else {
	    $x1=round($this->m[0][0]*$x + $this->m[0][1]*$y,1) + $this->m[0][2] + $this->transx;
	    $y1=round($this->m[1][0]*$x + $this->m[1][1]*$y,1) + $this->m[1][2] + $this->transy;
	    return array($x1,$y1);
	}
    }

    function CopyMerge($fromImg,$toX,$toY,$fromX,$fromY,$toWidth,$toHeight,$fromWidth=-1,$fromHeight=-1,$aMix=100) {
	list($toX,$toY) = $this->Rotate($toX,$toY);
	parent::CopyMerge($fromImg,$toX,$toY,$fromX,$fromY,$toWidth,$toHeight,$fromWidth,$fromHeight,$aMix);

    }
	
    function ArrRotate($pnts) {
	$n = count($pnts)-1;
	for($i=0; $i < $n; $i+=2) {
	    list ($x,$y) = $this->Rotate($pnts[$i],$pnts[$i+1]);
	    $pnts[$i] = $x; $pnts[$i+1] = $y;
	}
	return $pnts;
    }
	
    function Line($x1,$y1,$x2,$y2) {
	list($x1,$y1) = $this->Rotate($x1,$y1);
	list($x2,$y2) = $this->Rotate($x2,$y2);
	parent::Line($x1,$y1,$x2,$y2);
    }

    function Rectangle($x1,$y1,$x2,$y2) {
	// Rectangle uses Line() so it will be rotated through that call
	parent::Rectangle($x1,$y1,$x2,$y2);
    }
	
    function FilledRectangle($x1,$y1,$x2,$y2) {
	if( $y1==$y2 || $x1==$x2 )
	    $this->Line($x1,$y1,$x2,$y2);
	else 
	    $this->FilledPolygon(array($x1,$y1,$x2,$y1,$x2,$y2,$x1,$y2));
    }
	
    function Polygon($pnts,$closed=FALSE,$fast=false) {
	// Polygon uses Line() so it will be rotated through that call unless
	// fast drawing routines are used in which case a rotate is needed
	if( $fast ) {
	    parent::Polygon($this->ArrRotate($pnts));
	}
	else
	    parent::Polygon($pnts,$closed,$fast);
    }
	
    function FilledPolygon($pnts) {
	parent::FilledPolygon($this->ArrRotate($pnts));
    }
	
    function Point($x,$y) {
	list($xp,$yp) = $this->Rotate($x,$y);
	parent::Point($xp,$yp);
    }
	
    function StrokeText($x,$y,$txt,$dir=0,$paragraph_align="left",$debug=false) {
	list($xp,$yp) = $this->Rotate($x,$y);
	return parent::StrokeText($xp,$yp,$txt,$dir,$paragraph_align,$debug);
    }
}


?>
