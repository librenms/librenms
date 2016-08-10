<?php
//=======================================================================
// File:        IMGDATA_STARS.INC
// Description: Base64 encoded images for stars
// Created:     2003-03-20
// Ver:         $Id: imgdata_stars.inc.php 1106 2009-02-22 20:16:35Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================


class ImgData_Stars extends ImgData {
    protected $name = 'Stars';
    protected $an = array(MARK_IMG_STAR => 'imgdata');

    protected $colors = array('bluegreen','lightblue','purple','blue','green','pink','red','yellow');
    protected $index  = array('bluegreen'=>3,'lightblue'=>4,'purple'=>1,
   'blue'=>5,'green'=>0,'pink'=>7,'red'=>2,'yellow'=>6);
    protected $maxidx = 7 ;
    protected $imgdata ;

    function __construct() {
        //==========================================================
        // File: bstar_green_001.png
        //==========================================================
        $this->imgdata[0][0]= 329 ;
        $this->imgdata[0][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAAUV'.
     'BMVEX///////+/v7+83rqcyY2Q/4R7/15y/1tp/05p/0lg/zdX'.
     '/zdX/zVV/zdO/zFJ9TFJvDFD4yg+8Bw+3iU68hwurhYotxYosx'.
     'YokBoTfwANgQFUp7DWAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgF'.
     'HUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJj'.
     'CRyxgTAAAAcUlEQVR4nH3MSw6AIAwEUBL/IKBWwXL/g0pLojUS'.
     'ZzGLl8ko9Zumhr5iy66/GH0dp49llNPB5sTotDY5PVuLG6tnM9'.
     'CVKSIe1joSgPsAKSuANNaENFQvTAGzmheSkUpMBWeJZwqBT8wo'.
     'hmysD4bnnPsC/x8ItUdGPfAAAAAASUVORK5CYII=' ; 
        //==========================================================
        // File: bstar_blred.png
        //==========================================================
        $this->imgdata[1][0]= 325 ;
        $this->imgdata[1][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v79uRJ6jWPOSUtKrb+ejWO+gWPaGTruJTr6rZvF2'.
     'RqC2ocqdVuCeV+egV/GsnLuIXL66rMSpcOyATbipY/OdWOp+VK'.
     'aTU9WhV+yJKBoLAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJwynv1'.
     'XVAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bstar_red_001.png
        //==========================================================
        $this->imgdata[2][0]= 325 ;
        $this->imgdata[2][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v7+eRFHzWG3SUmHnb37vWGr2WHG7Tlm+TljxZneg'.
     'Rk3KoaXgVmXnV2nxV227nJ++XGzErK3scIS4TVzzY3fqWG2mVF'.
     'zVU2PsV2rJFw9VAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJzCI0C'.
     'lSAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bstar_blgr_001.png
        //==========================================================
        $this->imgdata[3][0]= 325 ;
        $this->imgdata[3][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v79Ehp5Yx/NSq9Jvw+dYwu9YzfZOmbtOmb5myPFG'.
     'gqChvcpWteBXvedXxvGcsbtcpb6su8RwzOxNmrhjyvNYwupUjK'.
     'ZTr9VXwOyJhmWNAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJTC65k'.
     'vQAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bstar_blgr_002.png
        //==========================================================
        $this->imgdata[4][0]= 325 ;
        $this->imgdata[4][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v79EnpxY8/FS0dJv5+dY7+9Y9vBOubtOur5m8fFG'.
     'nKChycpW3uBX5+ZX8e2curtcvrqswsRw7OdNuLZj8/BY6udUpK'.
     'ZT1dRX7OtNkrW5AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJgXHeN'.
     'wwAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bstar_blue_001.png
        //==========================================================
        $this->imgdata[5][0]= 325 ;
        $this->imgdata[5][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v79EY55Yi/NSetJvledYiO9YkPZOb7tObr5mkvFG'.
     'X6ChrcpWgOBXhedXi/Gcpbtcf76sssRwnOxNcbhjk/NYiepUbK'.
     'ZTfdVXh+ynNEzzAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJhStyP'.
     'zCAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bstar_oy_007.png
        //==========================================================
        $this->imgdata[6][0]= 325 ;
        $this->imgdata[6][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v7+ejUTz11jSvVLn02/v1lj21li7q06+r07x2mag'.
     'lUbKxKHgy1bnz1fx1Ve7t5y+qlzEwqzs03C4pE3z2WPqz1imml'.
     'TVv1Ps01dGRjeyAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJjsGGc'.
     'GbAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bstar_lred.png
        //==========================================================
        $this->imgdata[7][0]= 325 ;
        $this->imgdata[7][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABQAAAASCAMAAABsDg4iAAAATl'.
     'BMVEX///+/v7+eRJPzWN3SUr7nb9TvWNj2WOS7Tqi+TqnxZtyg'.
     'Ro/KocPgVsjnV9LxV927nLa+XLTErL7scN24TarzY9/qWNemVJ'.
     'jVU8LsV9VCwcc9AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAxYTJxi9ZY'.
     'GoAAAAcElEQVR4nH3MyQ6AIAwEUFIqiwju2///qLQmWiJxDnN4'.
     'mYxSv5lqGCs2nvaLLtZx/VhGOW1MjnPJWp0zsw2wsUY2jd09BY'.
     'DFmESC+BwA5UCUxhqAhqrA4CGrLpCMVGK4sZe4B+/5RLdiyMb6'.
     'on/PuS9CdQNC7yBXEQAAAABJRU5ErkJggg==' ; 
    }
}

?>