<?php
//=======================================================================
// File:	IMGDATA_BEVELS.INC
// Description:	Base64 encoded images for round bevels
// Created: 	2003-03-20
// Ver:		$Id: imgdata_bevels.inc.php 860 2007-03-23 19:16:19Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class ImgData_Bevels extends ImgData {
    protected $name = 'Round Bevels';
    protected $an = array(MARK_IMG_BEVEL => 'imgdata');
    
    protected $colors = array('green','purple','orange','red','yellow');
    protected $index  = array('green'=>1,'purple'=>4,'orange'=>2,'red'=>0,'yellow'=>3);
    protected $maxidx = 4 ;

    protected $imgdata ;

    function ImgData_Bevels() {
//==========================================================
// File: bullets_balls_red_013.png
//==========================================================
	$this->imgdata[0][0]= 337 ;
	$this->imgdata[0][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAM1'.
	    'BMVEX////////27t/f3+LFwcmNxMuxm62DmqKth1VpZmIWg6fv'.
	    'HCa7K0BwMEytCjFnIyUlEBg9vhQvAAAAAXRSTlMAQObYZgAAAA'.
	    'FiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElN'.
	    'RQfTAxcBNhk+pYJVAAAAl0lEQVR4nE2Q2xLDIAgFHUWBKJf//9'.
	    'oekmbafVDZARRbK/pYTKP9WNcNv64zzUdd9BjmrgnsVXRNSzO3'.
	    'CJ5ahdhy0XKQkxld1kxb45j7dp0x2lBNOyVgQpMaoadX7Hs7zr'.
	    'P1yKj47DKBnKaBKiSAkNss7O6PkMx6kIgYXISQJpcZCqdY6KR+'.
	    'J1PkS5Xob/h7MNz8x6D3fz5DKQjpkZOBYAAAAABJRU5ErkJggg'.
	    '==' ; 

//==========================================================
// File: bullets_balls_green_013.png
//==========================================================
	$this->imgdata[1][0]= 344 ;
	$this->imgdata[1][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAM1'.
	    'BMVEX////////27t/e3+K3vriUub/Dm18j4xc3ob10k0ItqQlU'.
	    'e5JBmwpxY1ENaKBgUh0iHgwsSre9AAAAAXRSTlMAQObYZgAAAA'.
	    'FiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElN'.
	    'RQfTAxcBNTfJXtxZAAAAnklEQVR4nE2QWY4EMQhDUVhSIRC4/2'.
	    'kbaqLp9p+f2AxAayAzDfiK9znPORuvH0x8Ss9z6I9sHp6tcxE9'.
	    'nLmWmebmt5F5p2AR0+C9AWpLBjXRaZsCAT3SqklVp0YkAWaGtd'.
	    'c5Z41/STYpPzW7BjyiRrwkVmQto/Cw9tNEMvsgcekyCyFPboIu'.
	    'IsuXiKffYB4NK4r/h6d4g9HPPwCR7i8+GscIiiaonUAAAAAASU'.
	    'VORK5CYII=' ; 

//==========================================================
// File: bullets_balls_oy_035.png
//==========================================================
	$this->imgdata[2][0]= 341 ;
	$this->imgdata[2][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAM1'.
	    'BMVEX////////27t/f3+K5tbqNwcjnkjXjbxR2i5anfEoNkbis'.
	    'PBxpU0sZbZejKgdqIRIlERIwYtkYAAAAAXRSTlMAQObYZgAAAA'.
	    'FiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElN'.
	    'RQfTAxcBNgK0wEu5AAAAm0lEQVR4nE3QVxIEIQgEUErAgTHA/U'.
	    '+7zbipf9RXgoGo0liMmX6RdSPLPtZM9F4LuuSIaZtZWffiU6Iz'.
	    'Y8SOMF0NogBj30ioGRGLZgiPvce1TbIRz6oBQEbOFGK0rIoxrn'.
	    '5hDomMA1cfGRCaRVhjS3gkzheM+4HtnlkXcvdZhWG4qZawewe6'.
	    '9Jnz/TKLB/ML6HUepn//QczazuwFO/0Ivpolhi4AAAAASUVORK'.
	    '5CYII=' ; 

//==========================================================
// File: bullets_balls_oy_036.png
//==========================================================
	$this->imgdata[3][0]= 340 ;
	$this->imgdata[3][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAM1'.
	    'BMVEX////////27t/e3+LO3hfYzz65ubiNwci6uQ12ipadgVGa'.
	    'fwsNkbhnVkcaZ5dwSA8lFg7CEepmAAAAAXRSTlMAQObYZgAAAA'.
	    'FiS0dEAIgFHUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElN'.
	    'RQfTAxcCBySi1nevAAAAjElEQVR4nFXPWw7EIAgFUNMoCMhj/6'.
	    'staKczc/2RkwjS2glQ+w3YytgXCXCZpRo8gJdGxZadJws13CUP'.
	    '4SZI4MYiUxypeiGGw1XShVBTNN9kLXP2GRrZPFvKgd7z/sqGGV'.
	    '7C7r7r3l09alYN3iA8Yn+ImdVrNoEeSRqJPAaHfhZzLYwXstdZ'.
	    'rP3n2bvdAI4INwtihiwAAAAASUVORK5CYII=' ;

//==========================================================
// File: bullets_balls_pp_019.png
//==========================================================
	$this->imgdata[4][0]= 334 ;
	$this->imgdata[4][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAM1'.
	    'BMVEX////+/v7i4eO/w8eHxcvKroNVormtfkjrMN2BeXQrepPc'.
	    'Esy4IL+OFaR7F25LHF8mFRh5XXtUAAAAAXRSTlMAQObYZgAAAA'.
	    'FiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElN'.
	    'RQfTAxcBNgkjEpIxAAAAlElEQVR4nE2QAQ7FIAhDDTAVndL7n3'.
	    'ZV/7JfEwMvFIWUlkTMVNInbVv5ZeJqG7Smh2QTBwJBpsdizAZP'.
	    '5NyW0awhK8kYodnZxS6ECvPRp2sI+y7PBv1mN02KH7h77QCJ8D'.
	    '4VvY5NUgEmCwj6ZMzHtJRgRSXwC1gfcqJJH0GBnSnK1kUQ72DY'.
	    'CPBv+MCS/e0jib77eQAJxwiEWm7hFwAAAABJRU5ErkJggg==' ; 

    }
}


?>