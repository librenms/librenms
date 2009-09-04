<?php
//=======================================================================
// File:	IMGDATA_DIAMONDS.INC
// Description:	Base64 encoded images for diamonds
// Created: 	2003-03-20
// Ver:		$Id: imgdata_diamonds.inc.php 860 2007-03-23 19:16:19Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class ImgData_Diamonds extends ImgData {
    protected $name = 'Diamonds';
    protected $an = array(MARK_IMG_DIAMOND =>'imgdata');
    protected $colors = array('lightblue','darkblue','gray', 
			'blue','pink','purple','red','yellow');
    protected $index  = array('lightblue' =>7,'darkblue'=>2,'gray'=>6, 
			'blue'=>4,'pink'=>1,'purple'=>5,'red'=>0,'yellow'=>3);

    protected $maxidx = 7 ;
    protected $imgdata ;

    function ImgData_Diamonds() {
//==========================================================
// File: diam_red.png
//==========================================================
	$this->imgdata[0][0]= 668 ;
	$this->imgdata[0][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbCAMAAAC6CgRnAAAA/F'.
	    'BMVEX///////+cAAD/AADOAABjAABrAADWGBjOCAj/CAj/GBj/'.
	    'EBCcCAiMOTl7KSl7ISFzGBilGBjOEBBrCAjv5+eMQkK1QkKtMT'.
	    'GtKSnWKSn/KSlzEBCcEBDexsb/tbXOe3ucWlqcUlKUSkr/e3vn'.
	    'a2u9UlL/a2uEMTHeUlLeSkqtOTn/UlL/SkrWOTn/QkL/OTmlIS'.
	    'H/MTH/ISH39/f/9/f35+fezs7/5+fvzs7WtbXOra3nvb3/zs7G'.
	    'nJzvtbXGlJTepaW9jIy1hITWlJS1e3uta2ulY2P/lJTnhITne3'.
	    'vGY2O9Wlr/c3PeY2O1Skr/Y2P/WlreQkLWISGlEBCglEUaAAAA'.
	    'AXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAA'.
	    'sSAdLdfvwAAAAHdElNRQfTAwsWEw5WI4qnAAABGUlEQVR4nHXQ'.
	    '1XLDMBAFUKUCM1NiO8zcpIxpp8z0//9SWY7b2LHv6EU6s1qtAN'.
	    'iMBAojLPkigpJvogKC4pxDuQipjanlICXof1RQDkYEF21mKIfg'.
	    '/GGKtjAmOKt9oSyuCU7OhyiDCQnjowGfRnooCJIkiWJvv8NxnG'.
	    'nyNAwFcekvZpPP3mu7Vrp8fOq8DYbTyjdnAvBj7Jbd7nP95urs'.
	    '+MC2D6unF+Cu0VJULQBAlsOQuueN3Hrp2nGUvqppemBZ0aU7Se'.
	    'SXvYZFMKaLJn7MH3btJmZEMEmGSOreqy0SI/4ffo3uiUOYEACy'.
	    'OFopmNWlP5uZd9uPWmUoxvK9ilO9NtBo6mS7KkZD0fOJYqgGBU'.
	    'S/T7OKCAA9tfsFOicXcbxt29cAAAAASUVORK5CYII=' ; 

//==========================================================
// File: diam_pink.png
//==========================================================
	$this->imgdata[1][0]= 262 ;
	$this->imgdata[1][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbBAMAAAB/+ulmAAAAEl'.
	    'BMVEX///+AgID/M5n/Zpn/zMz/mZn1xELhAAAAAXRSTlMAQObY'.
	    'ZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAA'.
	    'AHdElNRQfTAwsWEi3tX8qUAAAAbUlEQVR4nFXJwQ3AMAhDUdRm'.
	    'kKojuCswABf2X6UEEiC+WF+PyDfoGEuvwXogq3Rk1Y6W0tBSG8'.
	    '6Uwpla6CmJnpoYKRsjjb/Y63vo9kIkLcZCCsbGYGwMRqIzEp1R'.
	    'OBmFk9HQGA2N0ZEIz5HX+h/jailYpfz4dAAAAABJRU5ErkJggg'.
	    '==' ; 

//==========================================================
// File: diam_blue.png
//==========================================================
	$this->imgdata[2][0]= 662 ;
	$this->imgdata[2][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbCAMAAAC6CgRnAAAA+V'.
	    'BMVEX///+AgIAAAJwAAP8AAM4AAGMAAGsQEP8YGHMQEHMYGP8Q'.
	    'EKUICJwICM5KSpQxMYQpKXsYGNYQEM4ICGsICP97e85aWpw5OY'.
	    'xSUv85ObVCQt4xMa0pKa0hIaUpKf+9vd6EhLVra+dzc/9SUr1r'.
	    'a/9aWt5SUt5CQrVaWv9KSv8hIXs5Of8xMf8pKdYhIdYYGKUhIf'.
	    '/Ozs739//v7/fn5+/v7//n5/fW1ufOzufOzu/W1v+trc69veel'.
	    'pc6trd6UlMa9vf+MjL21tfe1tf+UlNZzc61ra6Wlpf+EhOeMjP'.
	    '9ra8ZSUpyEhP9CQoxKSrVCQv85Od4xMdYQENZnJhlWAAAAAXRS'.
	    'TlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAd'.
	    'LdfvwAAAAHdElNRQfTAwsWEx3Snct5AAABFklEQVR4nHXR5XbD'.
	    'IBgGYM6AuHsaqbvOfeuknev9X8xISbplSd5/8JyXwwcA/I0AKm'.
	    'PFchVBdvKNKggKQx2VIoRwMZihMiQE49YUlWBCcPL0hYq4ITh+'.
	    'qKECUoLDZWqoQNA766F/mJHlHXblPJJNiyURhM5eU9cNw5BlmS'.
	    'IrLOLxhzfotF7vwO2j3ez2ap/TmW4AIM7DoN9+tu+vLk6Pdg9O'.
	    '6ufXjfXLm6pxPACSJIpRFAa+/26DhuK6qjbiON40k0N3skjOvm'.
	    'NijBmchF5mi+1jhQqDmWyIzPp1hUlrv8On5l+6mMm1tigFNyrt'.
	    '5R97g+FKKyGKkTNKesXPJTZXOFIrUoKiypcTQVHjK4g8H2dWEQ'.
	    'B8bvUDLSQXSr41rmEAAAAASUVORK5CYII=' ; 

//==========================================================
// File: diam_yellow.png
//==========================================================
	$this->imgdata[3][0]= 262 ;
	$this->imgdata[3][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbBAMAAAB/+ulmAAAAEl'.
	    'BMVEX///+AgIBmMwCZZgD/zADMmQD/QLMZAAAAAXRSTlMAQObY'.
	    'ZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAA'.
	    'AHdElNRQfTAwsWEwcv/zIDAAAAbUlEQVR4nFXJwQ3AMAhDUdRm'.
	    'kKojuCswABf2X6UEEiC+WF+PyDfoGEuvwXogq3Rk1Y6W0tBSG8'.
	    '6Uwpla6CmJnpoYKRsjjb/Y63vo9kIkLcZCCsbGYGwMRqIzEp1R'.
	    'OBmFk9HQGA2N0ZEIz5HX+h/jailYpfz4dAAAAABJRU5ErkJggg'.
	    '==' ; 

//==========================================================
// File: diam_lightblue.png
//==========================================================
	$this->imgdata[4][0]= 671 ;
	$this->imgdata[4][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbCAMAAAC6CgRnAAAA/1'.
	    'BMVEX///+AgIAAnP8A//8Azv8AY/8Aa/8I//8Y1v8Izv8Y//8Q'.
	    '//8InP8Qzv8Ypf85jP8he/8Yc/8Ia/8pe/8p//8p1v9Ctf8xrf'.
	    '8prf8QnP8Qc/9CjP+1//97//9r//9S//9K//9C//85//8x//8h'.
	    '//9r5/9K3v9S3v851v97zv9Svf85rf8hpf/G3v9SnP9anP9KlP'.
	    '8xhP/n7//v7+f3///n///O//+U//9z//9j//9a//975/9C3v8h'.
	    '1v+E5/+17/9j3v/O7//n9/+95/+l3v9jxv+U1v8Qpf9avf9Ktf'.
	    '+Uxv+11v97tf9rrf+cxv+Mvf9jpf+tzv+Etf/O3v/39/8Akkxr'.
	    'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACx'.
	    'IAAAsSAdLdfvwAAAAHdElNRQfTAwsWEiHk6Ya/AAABGUlEQVR4'.
	    'nHXQ13KDMBAF0J2o0E01GHDvJa7p3em95/+/JQJMYjDc0Yt0Zr'.
	    'VaAaxHgtxwbSGPkGQpOIeQ2ORxJiJmNWYZyAhZR0WcgQGhViU0'.
	    'nEGoedDHGxgRapRPcRpXhOr7XZzCmLjaXk9IIjvkOEmSRLG62+'.
	    'F5XlEElhA5sW21GvXj6mGlDBfnJ51lr9svnvEKwH1hu2QPbwd3'.
	    'N9eXVzuL7/Hn29frdKaamgcgy67L3HFG9gDefV+dm5qme4YRXL'.
	    'oVR374mRqUELZYosf84XAxISFRQuMh4rrH8YxGSP6HX6H97NNQ'.
	    'KEAaR08qCeuSnx2a8zIPWqUowtKHSRK91rAw0elmVYQFVc8mhq'.
	    '7p5RD7Ps3IIwA9sfsFxFUX6eZ4Zh4AAAAASUVORK5CYII=' ; 

//==========================================================
// File: diam_purple.png
//==========================================================
	$this->imgdata[5][0]= 657 ;
	$this->imgdata[5][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbCAMAAAC6CgRnAAAA/F'.
	    'BMVEX///////8xAP/OAP+cAP9jAP9rAP+cCP85CP/OEP9SKf/O'.
	    'CP9CEP9zGP9rCP+lGP/WOf/WIf9KIf9jOf+MQv+EMf97If9zEP'.
	    '+1Sv+lIf/ne//eUv/na//n5//Oxv/Wzv+chP9zUv97Wv9rQv9a'.
	    'Mf9KGP/v5/+te/97Kf+9Y/+tOf+tKf+lEP/vtf/WMf/WKf/v7+'.
	    'f39/+tnP+9rf9rSv9jQv9CGP+ljP+EY//Gtf+tlP+Ma/9zSv/e'.
	    'zv+UUv+9lP+cWv+lY/+cUv+MOf+EKf+UQv/Opf/OhP/Ga/+1Qv'.
	    '/Oe/+9Uv/ntf/eWv/eSv/WGP/3zv/vlP/WEP//9/+pL4oHAAAA'.
	    'AXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAA'.
	    'sSAdLdfvwAAAAHdElNRQfTAwsWEjX+M1LCAAABDklEQVR4nHXQ'.
	    '1bLDIBAGYFqIEW+ksbr7cXd3ff93OUCamdOE/Mxw882yywLwPz'.
	    '+gNKotlRFUVnNUQlCxTMRFCKEdE+MgpJaEiIOU4DKaoSIygtb3'.
	    'FBUQrm3xjPK4JvXjK0A5hFniYSBtIilQVYUm+X0KTVNiYah+2q'.
	    'ulFb8nUbSovD2+TCavwXQWmnMA6ro+di+uR5cPzfPhVqPV3N1p'.
	    'n3b3+rimAWAYhP3xnXd7P6oc9vadPsa1wYEs00dFQRAFehlX21'.
	    '25Sg9NOgwF5jeNTjVL9om0TjDc1lmeCKZ17nFPzhPtSRt6J06R'.
	    'WKUoeG3MoXRa/wjLHGLodwZcotPqjsYngnWslRBZH91hWTbpD2'.
	    'EdF1ECWW1SAAAAAElFTkSuQmCC' ; 

//==========================================================
// File: diam_gray.png
//==========================================================
	$this->imgdata[6][0]= 262 ;
	$this->imgdata[6][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbBAMAAAB/+ulmAAAAEl'.
	    'BMVEX//////wAzMzNmZmbMzMyZmZlq4Qo5AAAAAXRSTlMAQObY'.
	    'ZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAA'.
	    'AHdElNRQfTAwsWExZFTxLxAAAAbUlEQVR4nFXJwQ3AMAhDUdRm'.
	    'kKojuCswABf2X6UEEiC+WF+PyDfoGEuvwXogq3Rk1Y6W0tBSG8'.
	    '6Uwpla6CmJnpoYKRsjjb/Y63vo9kIkLcZCCsbGYGwMRqIzEp1R'.
	    'OBmFk9HQGA2N0ZEIz5HX+h/jailYpfz4dAAAAABJRU5ErkJggg'.
	    '==' ; 

//==========================================================
// File: diam_blgr.png
//==========================================================
	$this->imgdata[7][0]= 262 ;
	$this->imgdata[7][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABsAAAAbBAMAAAB/+ulmAAAAEl'.
	    'BMVEX///+AgIBmzP9m///M//+Z//8hMmBVAAAAAXRSTlMAQObY'.
	    'ZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAA'.
	    'AHdElNRQfTAwsWEwCxm6egAAAAbUlEQVR4nFXJwQ3AMAhDUdRm'.
	    'kKojuCswABf2X6UEEiC+WF+PyDfoGEuvwXogq3Rk1Y6W0tBSG8'.
	    '6Uwpla6CmJnpoYKRsjjb/Y63vo9kIkLcZCCsbGYGwMRqIzEp1R'.
	    'OBmFk9HQGA2N0ZEIz5HX+h/jailYpfz4dAAAAABJRU5ErkJggg'.
	    '==' ; 
    }
}

?>