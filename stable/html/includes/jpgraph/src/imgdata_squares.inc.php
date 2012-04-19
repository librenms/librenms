<?php
//=======================================================================
// File:        IMGDATA_SQUARES.INC
// Description: Base64 encoded images for squares
// Created:     2003-03-20
// Ver:         $Id: imgdata_squares.inc.php 1106 2009-02-22 20:16:35Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class ImgData_Squares extends ImgData {
    protected $name = 'Squares';
    protected $an = array(MARK_IMG_SQUARE =>'imgdata');

    protected $colors = array('bluegreen','blue','green',
   'lightblue','orange','purple','red','yellow');
    protected $index  = array('bluegreen' =>2,'blue'=>5,'green'=>6,
   'lightblue'=>0,'orange'=>7,'purple'=>4,'red'=>3,'yellow'=>1);
    protected $maxidx = 7 ;
    protected $imgdata ;

    function ImgData_Squares () {
        //==========================================================
        //sq_lblue.png
        //==========================================================
        $this->imgdata[0][0]= 362 ;
        $this->imgdata[0][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAIAAADZrBkAAAAABm'.
     'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
     'B3RJTUUH0wMLFgojiPx/ygAAAPdJREFUeNpj/P377+kzHx89/c'.
     'VAHNBQ5VBX52HavPWWjg6nnDQbkXoUFTnnL7zD9PPXrz17HxCj'.
     'E6Jn6fL7H7/+ZWJgYCBGJ7IeBgYGJogofp1oehDa8OjE1IOiDa'.
     'tOrHoYGBhY0NwD0enirMDAwMDFxYRVD7ptyDrNTAU0NXix6sGu'.
     'jYGBgZOT9e/f/0xMjFyczFgVsGAKCfBza2kKzpl3hIuT1c9Xb/'.
     'PW58/foKchJqx6tmy98vbjj8cvPm/afMnXW1JShA2fNmQ9EBFc'.
     'Opnw6MGjkwm/Hlw6mQjqwaqTiRg9mDoZv//4M2/+UYJ64EBWgj'.
     'cm2hwA8l24oNDl+DMAAAAASUVORK5CYII=' ; 

        //==========================================================
        //sq_yellow.png
        //==========================================================
        $this->imgdata[1][0]= 338 ;
        $this->imgdata[1][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAAWl'.
     'BMVEX////+/+H+/9/9/9v8/8P8/8H8/7v8/7n6/4P5/335/3n5'.
     '/3X4/1f4/1P3/031/w30/wn0/wPt+ADp9ADm8ADk7gDc5gDa5A'.
     'DL1ADFzgCwuACqsgClrABzeAC9M0MzAAAAAWJLR0QAiAUdSAAA'.
     'AAlwSFlzAAALEgAACxIB0t1+/AAAAAd0SU1FB9MDCxYEDlOgDj'.
     'EAAAB+SURBVHjaVcpbCsQgDEDRGERGKopjDa2a/W9zfLWj9/Nw'.
     'Ac21ZRBOtZlRN9ApzSYFaDUj79KIorRDbJNO9bN/GUSh2ZRJFJ'.
     'S18iorURBiyksO8buT0zkfYaUqzI91ckfhWhoGXTLzsDjI68Sz'.
     'pGMjrzPzauA/iXk1AtykmvgBC8UcWUdc9HkAAAAASUVORK5CYI'.
     'I=' ; 

        //==========================================================
        //sq_blgr.png
        //==========================================================
        $this->imgdata[2][0]= 347 ;
        $this->imgdata[2][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAAZl'.
     'BMVEX////0+vv0+vrz+fry+frv+Png7e/d7e/a6+zY6+250tSz'.
     '0tSyztCtztGM0NWIz9SDzdNfsLVcrrRZrbJOp61MpqtIr7dHn6'.
     'RErrZArLQ6q7M2g4kygYcsp68npa4ctr8QZ20JnqepKsl4AAAA'.
     'AWJLR0QAiAUdSAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAAd0SU'.
     '1FB9MDCxYEByp8tpUAAAB7SURBVHjaVcjRFoIgDADQWZpWJpjY'.
     'MsnG//9kzIFn3McLzfArDA3MndFjrhvgfDHFBEB9pt0CVzwrY3'.
     'n2yicjhY4vTSp0nbXtN+hCV53SHDWe61dZY+/9463r2XuifHAM'.
     '0SoH+6xEcovUlCfefeFSIwfTTQ3fB+pi4lV/bTIgvmaA7a0AAA'.
     'AASUVORK5CYII=' ; 

        //==========================================================
        //sq_red.png
        //==========================================================
        $this->imgdata[3][0]= 324 ;
        $this->imgdata[3][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAAXV'.
     'BMVEX////++Pn99/j99ff99fb98/X98/T98PL55uj43+P24+bw'.
     'kKPvjaHviJ3teJHpxMnoL2Pjs73WW3rWNljVWXnUVnbUK1DTJk'.
     '3SUHPOBz/KQmmxPVmuOFasNFOeIkWVka/fAAAAAWJLR0QAiAUd'.
     'SAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAAd0SU1FB9MDCxYEHd'.
     'ceT+8AAABtSURBVHjaVchbAkMwEAXQq6i3VrQiQfa/zDYTw8z5'.
     'PCjGt9JVWFt1XWPh1fWNdfDy+tq6WPfRUPENNKnSnXNWPB4uv2'.
     'b54nSZ8jHrMtOxvWZZZtpD4KP6xLkO9/AhzhaCOMhJh68cOjzV'.
     '/K/4Ac2cG+nBcaRuAAAAAElFTkSuQmCC' ; 

        //==========================================================
        //sq_pink.png
        //==========================================================
        $this->imgdata[4][0]= 445 ;
        $this->imgdata[4][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAApV'.
     'BMVEX////6+Pz69/v49Pr38/r17/jr4+/l3Onj2efh1ua/L+i+'.
     'q8m+Lue9Lua8qsS8LuW8LeS7pca5LOG4LN+2Y9O2YNW1ZdO1Kt'.
     'y0atC0aNGzb82zbc6zKtuzKdqycsuwa8qtJtOISZ2GRpuFN6GE'.
     'NqCDQpmCMZ+BPpd/LJ1/K519S5B9Jpx9Jpt9JZt6RY11BJZ1BJ'.
     'V0BJV0BJRzBJNvNoRtIoJUEmdZ/XbrAAAAAWJLR0QAiAUdSAAA'.
     'AAlwSFlzAAALEgAACxIB0t1+/AAAAAd0SU1FB9MDCxYDF3iKMD'.
     'YAAACeSURBVHjaVczbEoIgGARgCiMtrexoWpaa2FHUgvd/tH4Y'.
     'BnEvv9ldhNPradPnnGBUTtPDzMRPSIF46SaBoR25dYjz3I20Lb'.
     'ek6BgQz73Il7KKpSgCO0pTHU0886J1sCe0ZYbALjGhjFnEM2es'.
     'VhZVI4d+B1QtfnV47ywCEaKeP/p7JdLejSYt0j6NIiOq1wJZIs'.
     'QTDA0ELHwhPBCwyR/Cni9cOmzJtwAAAABJRU5ErkJggg==' ; 

        //==========================================================
        //sq_blue.png
        //==========================================================
        $this->imgdata[5][0]= 283 ;
        $this->imgdata[5][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAAQl'.
     'BMVEX////4+fz39/z19vvy8vru7/ni4+7g4fHW1ue8vteXmt6B'.
     'hdhiZ7FQVaZETcxCSJo1Oq4zNoMjKakhJHcKFaMEC2jRVYdWAA'.
     'AAAWJLR0QAiAUdSAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAAd0'.
     'SU1FB9MDCxYDN0PkEP4AAABfSURBVHjaVchHAoAgDATAVcCCIF'.
     'j4/1elJEjmOFDHKVgDv4iz640gLs+LMF6ZUv/VqcXXplU7Gqpy'.
     'PFzBT5qml9NzlOX259riWHlS4kOffviHD8PQYZx2EFMPRkw+9Q'.
     'FSnRPeWEDzKAAAAABJRU5ErkJggg==' ; 

        //==========================================================
        //sq_green.png
        //==========================================================
        $this->imgdata[6][0]= 325 ;
        $this->imgdata[6][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAAXV'.
     'BMVEX////2+vX1+vX1+fT0+fPz+PPx9/Dv9u7u9e3h7uHe697a'.
     '6dnO2s3I1sa10LOvza2ay5aEwYBWlE9TqE5Tkk1RkEpMrUJMg0'.
     'hKiUNGpEFBojw8oTcsbScaYBMWlwmMT0NtAAAAAWJLR0QAiAUd'.
     'SAAAAAlwSFlzAAALEgAACxIB0t1+/AAAAAd0SU1FB9MDCxYEFd'.
     'nFx90AAABuSURBVHjaVc9HAoAgDADB2HuJWLDx/2cKBITscW4L'.
     '5byzMIWtZobNDZIZtrcCGZsRQ8GwvRSRNxIiMuysODKG3alikl'.
     'ueOPlpKTLBaRmOZxQxaXlfb5ZWI9om4WntrXiDSJzp7SBkwMQa'.
     'FEy0VR/NAB2kNuj7rgAAAABJRU5ErkJggg==' ; 

        //==========================================================
        //sq_orange.png
        //==========================================================
        $this->imgdata[7][0]= 321 ;
        $this->imgdata[7][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAMAAABhEH5lAAAAUV'.
     'BMVEX/////8+n/8uf/8OP/59H/5Mv/zqH/zJ3/ypv/yJf/vYH/'.
     'u33/uXn/n0n/nUX/m0H/lzn/ljf/lDP/kS3/kCv/iR//hxv/fg'.
     'n/fAX/eQDYZgDW6ia5AAAAAWJLR0QAiAUdSAAAAAlwSFlzAAAL'.
     'EgAACxIB0t1+/AAAAAd0SU1FB9MDCxYEJIgbx+cAAAB2SURBVH'.
     'jaVczRCoQwDETRbLAWLZSGUA35/w/dVI0283i4DODew3YESmWW'.
     'kg5gWkoQAe6TleUQI/66Sy7i56+kLk7cht2N0+hcnJgQu0SqiC'.
     '1SzSIbzWSi6gavqJ63wSduRi2f+kwyD5rEukwCdZ1kGAMGMfv9'.
     'AbWuGMOr5COSAAAAAElFTkSuQmCC' ; 
    }
}

?>