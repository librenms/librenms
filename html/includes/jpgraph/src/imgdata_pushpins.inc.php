<?php
//=======================================================================
// File:	IMGDATA_PUSHPINS.INC
// Description:	Base64 encoded images for pushpins
// Created: 	2003-03-20
// Ver:		$Id: imgdata_pushpins.inc.php 860 2007-03-23 19:16:19Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class ImgData_PushPins extends ImgData {
    protected $name = 'Push pins';
    protected $an = array(MARK_IMG_PUSHPIN => 'imgdata_small',
		    MARK_IMG_SPUSHPIN => 'imgdata_small',
		    MARK_IMG_LPUSHPIN => 'imgdata_large');

    protected $colors = array('blue','green','orange','pink','red');
    protected $index  = array('red' => 0, 'orange' => 1, 'pink' => 2, 'blue' => 3, 'green' => 4 ) ;
    protected $maxidx = 4 ;
    protected $imgdata_large, $imgdata_small ;

    function ImgData_PushPins() {

	// The anchor should be where the needle "hits" the paper
	// (bottom left corner)
	$this->anchor_x = 0;
	$this->anchor_y = 1;

//==========================================================
// File: ppl_red.png
//==========================================================
	$this->imgdata_large[0][0]= 2490 ;
	$this->imgdata_large[0][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
	    'B3RJTUUH0wMKBh4Ryh89CgAACUdJREFUeJy9mNtTFFcexz+/7p'.
	    '4Lw1wZJKDGCAwmDAqUySamcCq1ed6k9mn3UfMP7F+1T3nYqn2J'.
	    'lZdoDEjpbq0KG8EBFBFBEJye6Zmenkv32Ydu5GYiUMmeqq6uqT'.
	    '6Xz3zP73aOcIKmAQkIFyD3N/jrBPwlKjLQEglVlJKyUjR3u7cc'.
	    'WLoP3/4dvv03LNrQ8I6x1rFbDML9kOmHvh7IRHU9JKmUSG8vpF'.
	    'IoXX/TV0AiEM5A5jT0noFMFMJHXUt/d5f9TUAbhtQ3cPFruDog'.
	    '8klHMnmO0dGYe/myOJGINEwTz3F2higFXgy8PpAkOC+h8hoaCt'.
	    '4ppHFcQAWSgOQlyI/p+lUjmRxWAwNJd3xca/f34yoFi4tgmjtD'.
	    'NIFkJ4xcgBCgVqEBFJ9DqcZea/gNAAVEg7AOGYnHe9XoaJd3+X'.
	    'LISSSwnz6lsbKCZ9sHh4UVdBkwdA6cPwNnIfJPmC3Ctgft3wwQ'.
	    'QPkvTZJJnbExzfvsM2nMzVG7e5fG48d4lnXwTwEYCjJxuHQBog'.
	    'BHUfKkgAIIhiGk06hTp/Dm5qS1uYlXLvtWd4gPgIiCrAEcVckT'.
	    'Ab5p7TaYJrK1hQaEenrwSiVfQdc91P0kSp7Ii89D5ksY/kAkLy'.
	    'IZXFdXkQjS1YUSEbdcRu168V6+HTUNIKJDRwdE+sBIQmP9Ld59'.
	    'bEBA3of4F/D+uXb7rGaaCSmXI3pPj64PDaHCYfEqFVSjgWo2D2'.
	    '73XlJNQTgCyQykIuBWoNKEeh1aLXBPBCggGdBOgxZVSjoajVhH'.
	    'o5HWlIpq4bCQSgm9vXhK4ZZKh5SUYygp4J1EQVUD9xlU18BJQD'.
	    'bUbJ5T5XJStyxN9fSI099P3baxV1dRloW2h2ivx/yakg2ot6F1'.
	    'EkCa4G1D+zVEq5ArKTWM42Q6HUczQV7U66w9e0ZpdRXlOIQ5vF'.
	    'VHUXILKify4jiEzkOqC3peQMoBQymFlMt4Dx6wUSxSsm2UZXEK'.
	    'P30QvOUt8/2Sd78CdWwFDTA+gsw3cOlPcPUD+CQB52oQ21RKXM'.
	    'eRhGXhOg7VoKrx8KuS4ygZhVg3ZI8FGIfwR9BVgAtfwxdXdP3L'.
	    '86nUR91dXelNXTeWWy10paQHX602YAP1ADASAL7LJvFtMpOCc0'.
	    'cG3FHuGlz6Gr4YEpnoTCbzsdHRbOzy5RCRiLRMk5rjyOtAimwA'.
	    'U4U3SurBN/0wnAASBCVDIKpB4kiAB5Ub0/UvO9LpPAMDGfn005'.
	    'AxPCzxep3Q6iqPLUseBoufCZRsAE6g5g5kKIDfKUj3wnpAG8QB'.
	    '/Z1OIqANQuI65AtwNScyYXR2XlAXL2YZHzcklRKWl5GVFXFtGx'.
	    'MoAiV/EQaAGH6BUQNWgQpwFngv+Ca8KUAQEBcwgTJHyMV7679R'.
	    'XS8YqdSI6u/PMD5ukMtJY3GR2uQkr5aXeWVZOEALmA8WsIAxfL'.
	    'd0goVLAdCOd+/YpgqeVtBv4yiA++q/RKKXixe7GB8PSyoljcVF'.
	    'yg8fyubyMpulEk2lyAIfAAvAC+B+oOQFoAt/+0rAejB/EzjNri'.
	    'vvqNnCd64jxcE39V8spnP+vMbAgDSePKE2NcXm06dslMuUlcID'.
	    'TuFvqwXMBU8N39bGgRR+ki0Dz4L5DSAe9NGD7zq+6kcN1L6H2b'.
	    'ao5WWaQHllRTafPmWrVMJUimoAQrBYJFjQwre7B6A8YAi8LCgD'.
	    '5DVo6/hbb/iHK1KggvFeD3hHziQKEMuiNTNDbXGRTdtmw7Iwla'.
	    'KGH0oqwbscLOoG46rAY6AOzRhY74PT6QuUKEN4PegXxd/yEDTT'.
	    'YMWOk+oEaLkuFdNk0zTZwjfkavDUArXWgGXgFb4dEShXhfYqlI'.
	    'ow3w9rg3B6ED60IOOA5oEYQBrcpG+mj9bg0VG8GMJhVDZLyzAo'.
	    'VSq8rFYxXXefcjVgG9+uisDrXUCApoKSBcUHMBmHhfcgNwhtD3'.
	    'q9IG6Lr15b4OUTmPwBJt8JqGuapp05o0mhoHnptLQfPsR+8IBK'.
	    'uYyNH3yr+B77LHheA3tK1Ta+IrMeTL2C6Xl48TOsNWDDgAz7s5'.
	    '/r+krP/eddCsbj8fDQ4GBm9MqVvvRXX2VULBayRGRzaYn1SoWa'.
	    'UjgB4PIB5QK4ZgBXBKaAHxQsrED1H7CRgCUPwgHZDqACmhWwXv'.
	    '2aDRqGYeRyufS169cvThQKV88PDuYbW1vJ5VRK+5euqxWlPMdX'.
	    'SRqgreHbZGN3ijfKBXBTAeh2Fdwi2MofshP/dvKwCmKhp4m83Y'.
	    'vj8Xg4l8tlCoXC0MTExMTFkZE/1m37wvLGRvKRacoD1209E7Fc'.
	    'pZwYREOQqEJ4z3HskHLsz4AoXykPIBSN0t3dTTQafROoHdumXC'.
	    '4fjoMiog0ODiauX7+eLxQKV3O53ETdti88nJnJ3rl505ifmWm3'.
	    'arWSodR8GNbycDoNHy5C5jFold1k8d+DyvELNwg93d18/vnn9P'.
	    'X1oes6nufx/Plz7t+/fxhQKSWJRCI5NjaWHxkZKdj1+sjSwkJm'.
	    '+uZN/dZ337VqCwullGUVdZjsgIUC5LqhrUPvCugWuApeApPAzY'.
	    'PKHWyaphGNRunt7WVwcBARwfM8Ojo6sCzrMKBhGLphGFEF2Wq1'.
	    '2jc7M5OZ/vHH0MPbt93awkJJmeZsC6ZaMK3DCwvWdNioQUb5B6'.
	    'AdBR+9SzkAz/NwHIeXL18iIui6TjgcJplMMjY2th8wHo+Hh4aG'.
	    'MsPDw6fddru7+Phxx51bt/RbN260qwsLpZhlFZsw9QJ+2Pbrga'.
	    'oJG2FY2oKwuTtVEz9uV34NbqdtbW0xPT1NNBoF4MyZM1y5coWu'.
	    'rq5dQBHRcrlc4tq1a/l8Pj9RMs38ndu3Ez//9JNXLRZNyuXZJk'.
	    'xVYKoExQpsK/+IaAuYb7no8zjC/R+A4zisrq7u+53NZjl16tQ+'.
	    'QIlEIslsNpuPRCJXZ2dnh2/duNFRW1oy07a96MKd575yxRqU1B'.
	    '5vPMpF5HHa1tYW9+7do7Ozc/eQpZTSQ6FQt1Lq8pMnT/5w7969'.
	    'nuLcXE1rNufO9fRMhlKpOyvt9qPtVmvb25fFfvvWbrepVCqHwo'.
	    'xaX19vff/996ZhGC8qlkW9Wt1Onz073fXxxz+6MB+9e9dUjuO+'.
	    '7ebq9wLdB9hoNCrr6+s/4wf3FCJW3fPmTZhXsNWCprjuW66Dfr'.
	    '928KAfBhJAEgiJSLuzs7OSTqctoFkqlZRt26j/I+L/AGjPTN4d'.
	    'Nqn4AAAAAElFTkSuQmCC' ; 

//==========================================================
// File: ppl_orange.png
//==========================================================
	$this->imgdata_large[1][0]= 2753 ;
	$this->imgdata_large[1][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
	    'B3RJTUUH0wMLFQ0VCkHCzQAACk5JREFUeJytmGtzG0d2hp8zNw'.
	    'AEcRdJ6EJK9FL0CqZUm9jWbkwq3vhDstl8dmLvz8rP2H8Q75ZT'.
	    'pkRfpLgqsS6WIFEKGYkiSBCDO+banQ8DUpRWEkklXQUUqlCDfv'.
	    'rp857pgfAOQ4AMOJdg4R/hX96Hf06bvDc5iT07i8yeg8ksiIAI'.
	    '4TBi/ds9/vivD/njapNHvRBfHXMu410AM+BUoVSF05NQsi1sO4'.
	    '8402AXwLQTuP31OAZO2aG0MEn14iSlnI1z3LnMk8IZYJyBwjIs'.
	    '/TWsVIWPJkvMFS4zMfMhUp5BsoCpAAEBLYKaMFGn00jBxnvu02'.
	    '35+JHmSJEnBpQEcPo38MmCxd/nS9Ry71Ga/g1W9a8gn0GsHkgA'.
	    '6DGjxkqb5CoO+YxF3A3p+jGjQUzoK+L/V0ADzFMwtSR8eLbAr8'.
	    'uXOTf9NzhTc0geSLUQcYHgYEH786RMg0zWJHV2Aitv4x/HpHVS'.
	    'QA2YBqTTGIUq5qkPMWaWkVwPnPtAA/BevmZcjxaaUtHh8pJJGu'.
	    'DpCB9FvT7A7YT7S3p5vFMNzmWo/O0MSx/Ms3TqI8r59zFTfUQe'.
	    'I7SBODE3tnfoIxYnNHligwik0zAzDdVpyKbA8sff5YAeMEwgkV'.
	    'cufQeTJzZoCsaFLKXPTnNpoUTNsSgJmNoGsuNQjIDwYD2HlnZy'.
	    'k++yxTKXZfKTU8zOpjhneeQYkorSmGERtIlICBKRbLX+y98YN3'.
	    'ADcNIm+bJD4U3pPnmbEaRgYVRTGBkDSSsmxKfY7ZLuDJA4hdjl'.
	    'JEgyBB2SJOvQ9RzTpNKoEwNq0CNFvOXR3/HxMgYVPObaz8kPmh'.
	    'hkEWMatAfRONGGvLizyOE9P8KkpwhPDAgQKJQbELUD0oOIhbbH'.
	    'JeVTmowxjAgZutB5AoOngA+2DdYrcTyOyYZP9+QpBvI29vwEhb'.
	    'It042BVQgDy9KTMfkwQG1A9ACCLlgBBGUwxxoc52WDh2ATyEPp'.
	    '1hoaPvrEBh0Dq5an9OUsl/9hylk5b5c+mowLc4E2Jtw4Eoljyf'.
	    'ogA/AGEAagNRjGyUxOmEycyVA5EWDBxrmUp3ytLIv/NJP69Goh'.
	    '+9mFydIvS5PZYkvH1oY/RFtKymlwBFQAgQd+kAA6qSQ8pvn2mp'.
	    'SkJkuVFHPHBnQMrEt5Sl+e4/Lvp51PF1PF5Xy6WMvOWZXMom8z'.
	    'OZTQ8+j5sbQiMEwopsCIwRtBGIJSCdzbTGo9NimkDcgdC7Bg49'.
	    'TG5n4/nfr0Si77WdYp1YzyZEkWPdteaEnB7pPqBTxuIf/VgciE'.
	    'SgasCPwh+GNIkaNNag1RiPge5pEhMQVjfoLcF+eoXSvbKxedwn'.
	    'LKzC3KWbOi5/sW5a44/SHFUSgVA7SCzRG0AvA9mPOgFIETgu4n'.
	    'Ww0wNQWFAqRSL6D2ZQYBdDrQ7R7jXiwgRcvIL02makuTmWtpM/'.
	    '+BlLMl5vuWzLVEuwH6oYnR1KS8kJINGXMM2YdfRlALoQoQQKeb'.
	    'bDVwoMdxQMaLCwLo96HZTF5HbrEhmOftianfZisfzueKv7ZmrX'.
	    'MsjhxKXZGBjzyeEHmSE3oWiggtyVGmE8DTIXTC5NxgAxOAGUM8'.
	    'fun9mnSSLQ/CxNzOTgJ3LIMgoGwkKBiiMyaVviHVkdCO4FEKNv'.
	    'LQzWBYHfITPa4UBVM0LR/WB7ARJsdDDTjA6deYFIFUOimJ3d0E'.
	    'sNdLavYYgBpthqKcjiiJRO8K6CK0CsJTjfQAGaJtD9vQFAxNNQ'.
	    '1FB0yBAfA8gdMAIagLoCVAen0M00zMOTYShNDtoHs9CAIUoI4E'.
	    '1IBihCdNhsMhsj6NuV7BCC2IBpBqQaaFOENCCeiEsO1BO4RQgy'.
	    'I5Hm4k4oIU9MrgZSAdBeTabZz+ODxKQRRBFBJo6IUc51anYRQo'.
	    'dto+24FNxYCiaWKkQsj00KkO4gxRRkAngJ868M0u3OkkM+hxQA'.
	    'cQ7YD7GO5XYSsPZybh/TCkFIYY+kWniTW4Q7jXgHvHMhiRpmuW'.
	    'ca08GZkkZ/nY6TZMNhCnf2CuPoDVJvxpB+q9BHA8Ag1uH+oP4c'.
	    'YEPCzDwmzSLquShHW/E0YRbG/BjZtw40hAy7aNzJlzRn75E6N0'.
	    'qiwTzafI7kOU3gWrhzZC2iHcbsPqLlxvJnCt4KC1RYAL3I5hzY'.
	    'Xv/huePYCtITQMKEnyB4KQvMURuJvw889HGSwUCs7CwkLpo6tX'.
	    'Ty/+7nel6VLGDn/8N9m+eZuo1UP8iNhLau6b3RfmOsHBGTUYw9'.
	    'WBNeDrGB4+h/4qNLKwTnLbHj9CJw/6GoIh9Jpvq0HHcayFhYXi'.
	    'l3/4w9LK8vLKexfma3G/mb/3n1njTivS7tNQaaU1grQDjJ868D'.
	    'Axx6vmxnBrY9C9IcSbSXbavNjb/S3eN6/0m1JcKBScixcvllZW'.
	    'Vi6uLC8v12q1v/M8b/HxVjP//YYr32yE4dYWvShO0ogi14xwxq'.
	    'F4rbnxZ3cMjtpvEEeMvwA0TdOYn5/PffHFF7Vr166tvPeLXyx7'.
	    'nrd4+/btyg/frFo//Xgncnd67qCn78earQqcmYD3fSi1wPCTSV'.
	    '3gzqvm9uFOMl5nUAqFQn5paal26dKla57vf7D+6FHph9VV88af'.
	    'vgq79bo70e3VT2l9A3hYg4UiRALVHTCHSZvYBm4A//6quf8zoG'.
	    '3bpuM4acMwKr1+//SDe/dK31+/bv90/Xrcq9fduNW6rbVeC+E7'.
	    'gWdD2DKg4UEpBmPcm10RuScida31ntb62HAigoigDw6Gh0axWH'.
	    'QWFhZKi4uLZ+I4PrVer2e+u37dXPvqq6hbr7tOp1NXWq89h6/b'.
	    '8FBB34WGBesdcPrj38lkMkGlUuml0+mu53nR3t4eo9HoSLhMJk'.
	    'OlUiGdTuN5Hq7rvgA0TdO4cOFC7vPPP6/VarXldqdTu7m2lrv7'.
	    '7beq++BBO263b/tKrfWSXlbvwJ6CuAtDgTYiaBFMw6BSqfDxxx'.
	    '+rarWqGo0GN2/eZGtrC6XenAkRoVKpcPXqVWZmZmg0Gty6desF'.
	    'oIhIOp3Ol8vlmmVZK3fv3Lm09uc/Zwbr653ccPgoNIzvnmn99Z'.
	    '7W9QG46lAaM5mM2l95GIYUi0VOnz7N7OwsWmsymQzyuse5Q8Mw'.
	    'DNLpNDMzM5w/f/7A6AGgUkoajYa9urpayOXzUz/fvZutr68Pim'.
	    'F4/2y1+n2o9Q/ru7uPesPhXnyo4A+vfHp6mmazybNnz9jZ2UFr'.
	    'TbPZJAhe+8/aS0Mphed5NBoNABqNBqPR6MWBVWstvu/nnj9/Pv'.
	    'vo0aPq5uZmPBgM/qcwPf39xV/9ajU1M3Nvq9PZaw8GoT50PjdN'.
	    'k6mpKa5cucL58+eJ45j19XWePHnCzs4OnudhmiaWZRGGIVH05r'.
	    'yEYYjrumxubrKxsfFyDQJ6NBp1Pc+7C4jWumBaVm+kVL2l1H2l'.
	    '1G6otS+H6V6z8u3tbVzXpdFooJRicXGRqakptre3uXXr1ltrcT'.
	    'Qa8ezZszemWAE9rfUdYBOwtVLRbrPZ+48ff+wDvuu6Sr3MB4Dr'.
	    'uty6desgfa1WC3iRyrNnz4pSSmezWUzTfGtYtNYcdvC/9sMlgP'.
	    'n5N4cAAAAASUVORK5CYII=' ; 

//==========================================================
// File: ppl_pink.png
//==========================================================
	$this->imgdata_large[2][0]= 2779 ;
	$this->imgdata_large[2][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
	    'B3RJTUUH0wMLFQolY9lkpgAACmhJREFUeJy9mOtzFNl5h5+3b9'.
	    'Mz0kzPBWmEVtIiWYhIiC0HCDhB8lb8ISk7nzdZ5+/zJ/8BTmpT'.
	    '660CZLwG1pVFgBkgGIHECEaa+/T9nHzQCCQuRpCNz6mp6g893U'.
	    '8/c37ve3qEjxiC4OA4n/Lp/EUu/tsMM/+aEWduVBx7WhdkShcY'.
	    'xUH2zo0Dwod/5N6vf8V//PoGdx8M8EOFPtK9jI8BdHCcMuVSmf'.
	    'LxHLmSZdm2U8xIbmKETDGDZZnIy4dBbCynyGhphurEDBOlHFnn'.
	    'qPcyPxTOwDCOccw7w5nlBRZWylI+ny/mZ6rL1dzUZ5/IWGZU3D'.
	    'ZIOMQDDaJcHDVGWUbJBi9odVr0QoVSPzigIEaZ8vgSS/8wZU3/'.
	    'k1fylipz5dLM2WlrZqHKaGCKbEbontq3KAKWQyZfZKTgYqc9Bp'.
	    '2I2PcJ4ogk/UEBQcwipbFZmT13vDBx8fhnE1Ofnp9yJopFyT3X'.
	    'yANfks0QHSQMDaL37pOxMLIu2UyVkjVKLjyKSeuD8dAYCFkso1'.
	    'gYMaeWJ40T56cl8yAi/O4FSa2P6kYczIDsgVpAqcDImZPMuAB1'.
	    'dkLQtcc8a/bwox8IUHAxZVxGZMouSLVYwKuMkD5IxN+JSdsRJB'.
	    'pexuTVgYYM6EoGmxkmg3/hEhNUMr/hd7dqbOzExMn/GRDAxWZc'.
	    'j3I8HiXfMjF2FQowKw7pjoN6E/Llw/GBJj8qxVOMlX4ipxc/lY'.
	    'kl2zBLkmrTcEzMkoNoRLVidLi/9g+Z3I+1xRHX5EcAihxnbPRv'.
	    'OTU9kZSmpKPy9FTGrLimPZ1H+UiyGaF67w6n7E1DwMngFDxGvc'.
	    'w70v0xZUby5IxjlIyMssUJrJwVWkXBdbXvSvwEibcSdKCAFI16'.
	    '4/sc0SRo9cGAGq1DwvQFzV6DVuBiV4zYnlEts6A2TSPcSiXoxo'.
	    'QqJCEEFMbQ2b69o5qMiOOPqIMQkagu/aSL7waE8101WFShLjk9'.
	    'yxgEvjRUiyYd+gwAjY2J9VpXfZ/JEXLhDp3OR6U4T97+hEnPwx'.
	    'tv4HsRjy2tTQSFzQgDUnwSLBQRI+x1ZgcH87Vcv4SF19Kt0ezS'.
	    '1h9s0Ma25pgr/YJfnLnEysok0+ezjM6EBLldGqKIJYuDRhOQEJ'.
	    'Oih8X9Q0xmcXNjlCofBJgn78wxVz7L2YWf8tPPz1hnfjbjzfxN'.
	    'qVwutq2etZXUQSXikcXGIgUiUkJSDIQMJgYGJsaB3c7b1qQ4GZ'.
	    'xSkdGZIwMeNLfK6uezMnvJK3pLxeVixfvMsyVjSNSO6IV9adPG'.
	    'AArkEEz8oUkFmBjYGO80qfd6pCWIayD59wIKcsjcKqufn7JO/S'.
	    'xfyi+5c24pey5rZ09mJRNkiDdT/tzbkBr3SYkpMYpgEaIJSYhI'.
	    'kSOY1GhilAQk5ntDIojxCZ/kf87Pl85xbuWEnLiUy+cW3NNuJX'.
	    'MmY5meKf6mT7wZS+THdOjxlG06tIlIOMZxchSxcFFEGAwAGGME'.
	    'jwyZYSnWL3cXWiIUbUI6hO/vxXuFOV84ycmlBWthNeflTjuzTi'.
	    'lzJmM5s46Ej0J63/ZoPmoy6PYxtYVNhmfs0mbAND1mmKVMBY1L'.
	    'mxA1LN7WgXQbCApNhKJHRIM+DQbv7yQGhjnJ5NgFuXBuxpu5mD'.
	    'udm3LPuY7pmZLUE6L1SIJaIPFuDAqyw9lnwDYv6NFHkWJh4ZDB'.
	    'wCBFD3uMxsTAwcBAiElpE/KcPg36dIiOvpsRxDCyhmlP2YY9ZU'.
	    'v8NMb/1id+FGO0DTztkSXLOONUqeITsMkW2zwnJEIDFhYGx+A1'.
	    'kwK4mASkvKDPc3p0iYhRRwYUhZLUTyV6Eu0t4s1Y4kcx6W6KaM'.
	    'EZThcXH59RRhGEgIAddnBwNEBKqqpUtWBIF22YDIhJsbEkJqFN'.
	    'qLtERHs7GnUkwISEQAf0uj30bY39PzbiC6qrDu2cExJ69Nhhhz'.
	    '59UlIUipCQOnVi4sjG7ubJBy6um0C+he/0iDHQKIQERYyKFLqr'.
	    'SI/W6kJCnvOcrWSLSquC1/Jw9Ks3R0FQKHr0uMc9bnCDGjX69A'.
	    'H0XlcJkibN5jOe/alCZStHbjJL9lSMLkXExvCXRiDV6GZEeGeX'.
	    '3TvvBVQoEjfBL/v0rT75Th7VU5C8gktI6NLlMY+5yU3WWGODDf'.
	    'r098tHpNFNH7/2lKdXXdz7efLzVaqJIBOCmK8AJUlI6g0aV+9y'.
	    '9+p7AR3bMQpTBWPy7yeN6fy0jNwewfpvC9Xe+3kFoUuXe9zj5n'.
	    'BusEGHjh6GIAGawC2FWuvSvbbF1maFylZAsC1ISZADBiVNSJrP'.
	    'eX73MY//skHP85z5+fnSxQsXj//4n39cmnPn7LbZlsajBmEnBL'.
	    '1nuEGDG9x4aa5Ldz+h0RCuBqwBv1Wo+7vs9r7n++0MmYeAM+zB'.
	    '+61EK1QUEnbbtN+9Bh3Hsebn54u//PdfLq9eWl2ZnZ1dSnaSwu'.
	    'Pin40b9g3doKE0WoNIl65xj3v75njd3BBubQi6ExKmDWkMRKSl'.
	    'tSbVKQcMao1Go5Ugb0+x53nOyZMnSysrKydXLq1cWlxa/McgCB'.
	    'Yev3hU+GPrD3I5/q94k3pXYQY58q6B5Bs0HB//neaGx00gyWaz'.
	    'VCoV7bquCoKAnZ0dfN/f03egLGj0m3XQNE1jdnY2/+WXXy6trq'.
	    '6uzP3oR5eCIFi4detW5feXL1vr679Let37zVB3/mQytjXJwmSB'.
	    'wikHp9ShY0RESqObwPrr5oBERKhUKly4cIFqtUq9XufmzZtsbW'.
	    '2hXvuDwTTNtxZq8TyvsLy8vLS4uLgahOHphw8elL69fNlc++qr'.
	    'uFOrNXPddm1cczVL5f5P+Lv5MuOJgTGxwYbZpZsCdeAq8M1Bcw'.
	    'CGYeC6LtVqlRMnTjAyMkKn0yGXyx0N0LZt03Ec1zCMSrfXO37v'.
	    'zp3S769csb+/ciXt1mrNdHf3ltZ6Lca8ZpJsduhtCdb2gEFJoQ'.
	    'xADYHuHDS3f32lFEEQUK/XGRkZoVAocP78eZaXl9FaI/Jq25Uk'.
	    'yWHAYrHozM/PlxYWFibTND32sFbLXrtyxVz76qukXas1M61WTW'.
	    'm99gx+20TdN9jqtfjP7QzOwwYNp037Zd0DukDnIByA1pqdnR2+'.
	    '++472u02Z8+eZWJiAsMwDsEBRNGBzYJpmsaJEyfyX3zxxdLS0t'.
	    'KlVqu1dP3q1cLta9ekU6u1dat1J9b6Sk9kraV1rYXegW7apDYw'.
	    'kFY6fPc4MNTw88bwfZ/NzU2UUnieRxAEiAiGcXiXfcigiIjruo'.
	    'VyubxkWdbK7fX1xWvffFMInjzBM82uMT5+p++6V1UUrSe7u03t'.
	    '+8lezlKt3gHyl0aSJDQaDa5fv876+vo+w6FzDq1BpZRsb2/bly'.
	    '9f9vL5/Njdu3fzG0+eMJHNxsfn532vXN5NPG/7abPZal6/Hvfe'.
	    'kroPHfsm98f7AHW9Xo+//vrrlmVZm71+37QNw3JnZ9PK4uJGpV'.
	    'pt4Dh+vLGhsrmcfv1iHzu01m89HjIdCon2fb8TBMHtvYeRUn50'.
	    '1Oj4vqp3Ok1f5LYSadfr9dQfDN642P/XeF2DA+SBAuA4jkOhUK'.
	    'BQKESO43S11p3BYBDt7u4y+CtB/i/q7jp1GMiw2AAAAABJRU5E'.
	    'rkJggg==' ; 

//==========================================================
// File: ppl_blue.png
//==========================================================
	$this->imgdata_large[3][0]= 2284 ;
	$this->imgdata_large[3][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
	    'B3RJTUUH0wMLFRAiTZAL3gAACHlJREFUeJy9mGtv29YZgJ9zKF'.
	    'F3y/Q9jh05tuQkarKgbYasde0UBdZgwNou/Vqga/sD9mP2B4a1'.
	    'BbZ9atFPxb5sqOtmXbI19bqsluPYiR3HN90vFEWRZx/IJI5zqa'.
	    'x0OwBBSgR5Hj7v+55zSEFXTUgIJyA9C6/9RsjMjAyFIxxJCDc7'.
	    'iBqKgyZACGg3G2x9+xXf/fG33P3mC9qNKsp1O+1JdkEnQTdgIO'.
	    'ttCSMUi8gj072MnugllAyB9G8rBGi6RsToJTF6iuRoFi1kHKZf'.
	    '7fB8Iggj0/Dy23D2dakNTR3JDsXPvzstxmZGRMER1EwHhQAEgE'.
	    'CLhIkPD6InY9S3djGLJVBtQP1Qb4HDAyoJYQOOZkPx49nhTH9i'.
	    '7MUBGT7egxkJgd70wZS/CUkoZtA/fRoE1DZ2ACiv52ibReCp4e'.
	    '7CIEHomxDiuVdGTqUnf/ZeOjR8fpiVXZul5ZrY3bWwbdcLr/dA'.
	    'AAIpAwQjUWIjQ+g9HZvswiCgBVF9/SI6OSLGzo0i+oLi6+Utbq'.
	    '+bKEftgwOE/0Ohocf66M+cBjo22U2RQLIHMhmYnvaOpR9S8bSU'.
	    'UqCURGpRkuMZMm9cIvPGJZLj0yBjT2LprkiSkykx9cuXIhOnUs'.
	    'm+QNC2XdG02ggBTcvFabsPWwTPpBAChSCgh4kYBpoeplWp47Qs'.
	    '7EYDt21xINzd5GCAxLExRl89Z+nHjpbKMmjbmkgfDzI0JEW53K'.
	    'Jaa6NcAOEX8v52uJzsBlAS6u0hcnTIccPRqhWPCUcLD+s1EaUp'.
	    'HCEhEMCyHNpt9SjgIU12A6iw6xb123vYhaaKjB9tlgMD5X+uBp'.
	    'zdkpg6azA8EaNQtKlVba+Xez4eCntnJrsDdFsW5nYFpxlFN846'.
	    'DXe8utkM4mhi+EgQmjYbS2WqexZKk6BpjwJ2YlK5VjeA3pNDiH'.
	    'YjRWPzPE7tmBo8EWwGhkXx+z3uXL7D3rU97LIF8RBEAl6lK/Uo'.
	    '6JNM1rZ2aTcr3eUgIQOGTgbdwXMGyRejenLYTvQGbAdRuetSud'.
	    'OivVuFZgtCEgICghICnZoMhmlVTPR49LCAEkQUhk/B7KXe0MWf'.
	    'nxj8xVR/cDheK14WZmtVMJSBnlGoN6FmQq0FLfdwJgORKPHRo/'.
	    'Snzx4G0F/FjJ4KiOdmjPCrrx8bffnMybMv9MQGNG3rzlVqtR1B'.
	    'sh/CYXCD4Aag1oCW7ZnUOjSp6WFi/QNEB8Y7BfTNjZyCmUvJ0I'.
	    'XXT47MTp98Ybon9VZCk8cVazfqlNargsY34G7ByAlIjkHd9CCr'.
	    'LbBdiHViUgiECuDKYCdz8b2cywREdiYZOj8zNnLuzOTzx6ODp+'.
	    'OaGaqwVzBFqz0Idhz2loE7YEwBLaAJLQcKbW8qjAcBF5Jh0AMP'.
	    'IOHe6kxgtb3UMO2OxkF//ffK28nQqxfvm3szrtnDVa799Qb/+v'.
	    'NtsbNSpm3tAv8B+w7Ub0FhAyoBcMPec9oK6raXk48ziQBXQcmC'.
	    'pT3YqHa0mpEBkTR6wz/Jjo2cy04+fzwxdDquNfQKO7sFUbpu0c'.
	    'wp3JoAYsA42Bbkl4GCryUNDEM7Avm6Z/CgSYBWG8pNuFuDu1Wo'.
	    'tjoxKIJGeHIiM/jmK9NnX5ycuJQMtUcqXPvLDTa+qIie4hAJ1U'.
	    'vdrmO2HaDfB931twJgAn1A4lGT96obPHPLBbhVgUoTHHWo9aAA'.
	    'JVAKpyKEmQNzWRENAsL18ycKjAFN/9gCNvzLB/390MMmE7pnDi'.
	    'Bvwt0K5Jv3O+0oB22nJ1Vvjb/UMhOpcKknqN1OiMB2DNHU2G5s'.
	    'sVndpGJVcZXjX1IAlvw9PmhRQcOFPhsSDkiBrQR1G7brgs0a7D'.
	    'ag3FK4rguqBXarI4Nt1SJv5gls7TEWtJDRBO2GwnIs8maevFnA'.
	    'Gx6awLZvzeTBu4kFbLigijC47pscpx0xyDfkvtUEnlarCDtrUC'.
	    't2HGIhvPHVdVwqjTIrxRU2a5uUrYoP0QZ2gMvACl7+3V/LuKDq'.
	    'sJuDy597516+CEezIHXv7vcgXQu2l+Bvn8He9Y4AE4kgk5P9DE'.
	    'R6aFdq5Et5Nit3yTf3m9sBcsAN3+D98c0Fit5JawE25r1zg1Fo'.
	    '5B8GFD7g+nVYnu8EUEop9XTa0N/9dUbqcphP/rDJzbUClVbpgR'.
	    'y2fXM3fND95qj75J8AC6BWPINfVSBieK+x+6cS5UCzCLu3oFV9'.
	    'GqCMx2NGOp2Znpv7aXZudsool3T5J/179sxVlHJ4kGPrP2COBX'.
	    '/7DmiApWCjxIMXpYNznYuXM+6TAKWUMppOZzLvv//ery5cuDCT'.
	    'SqVS336bCwr1JfAPB9r+2KAFwJS+OcETzZHz/7v3etl6ipz77X'.
	    'GAMh6PG+l0OjM3NzczOzs3k0pNnFlbW43+e/GKtMqrblSsF03V'.
	    'WHcJA0PjIAzvg9JTze2H67g9DjAwOTmZ+uCDD96anZ2dnZiYmF'.
	    '5dW41++Lvfa1fnr7qllVK9103mXNTnJgPA+YugsvB3HTaEl+Qs'.
	    'AZ/yeHPPDCiTyaRx5syZbGoilV1dW00szC9oV+avusuLy0Xd0X'.
	    'MgFkDM+zkYBZEHV8f7wwKu84zmngQoNU0LaZoWUa4K31y5qX/8'.
	    '4cfyyvwVN5/L10NOKNeg8UmDxoKF5Vfj1xXAgD0JrgAcvBDfel'.
	    'a4g4AykUgY6XR6emJiIru2ttZXq9S0K19eUcuLy8WQE8o5OAsN'.
	    'Ggsmpl+NpoL1g9X4UBU+C9xDgEKIwNTUVOqdd955M9mbnJ3/cj'.
	    '6Vu5aTheXCQXNdVeMzAwJSCGEA2XKpnF1cXIzlFnOVhJPIKdR+'.
	    'c88ctq4AlVKsrKzw0UcfKcC5uXqzXnNqSzb2pwLxOHP/l7Z/BN'.
	    'eB01LKt4HTrusKvGr8jB+hGn8MQAkYQMrfw4Nq/MFPtf+rdvDb'.
	    'k8QL+/5Z4Uepxm7bfwHuTAVUWpWaqAAAAABJRU5ErkJggg==' ; 

//==========================================================
// File: ppl_green.png
//==========================================================
	$this->imgdata_large[4][0]= 2854 ;
	$this->imgdata_large[4][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
	    'B3RJTUUH0wMLFQ4hANhluwAACrNJREFUeJytmF1zE1eagJ+3u9'.
	    'XdkvUty2AbmLEtEzDBgZ0UpDBOalNTUzU3czl7tct/2n+wt3M/'.
	    'NVM12SSTQQSyW2TA+QAJQogtYYFtyfrqL3WfvWj5g8AEjzfvhS'.
	    'SXjk8//Zz3Pf3qCMcJAWxMKlT4kH+jwu/FknnJSUItKFHzCrKA'.
	    'BggBQx5ziz/wn/yBz3hED4/oaJfSjgVoYjJJgTLTZCjohp7IGT'.
	    'k5aZ4kb+bRTR30Q7djj8f/kpPMUSCFedRL6W8e8qMQNE6S4xpv'.
	    'c5HrTPFubiJ3ZnlyOXV59rJYU5Z00h1c3d0brxAiUkScRijisk'.
	    '6XLTyiN3s8HuAJpniXa/q8/pt8Or+0kF8oXJm5YiydWcIpOrJu'.
	    'rjOQwd54AQwsMpTJYhPSoYuLQ58An/DnBQSdImXO8avsTPbqpc'.
	    'lLp67OXDVzMznZLGxSs2qyIRu4at8gKHQEC50kE1icxqCAdxST'.
	    'xjEA44tqaJlERl8uLWvvnX5PHuQfcCdxh5qq0aX76vj4WgWyXO'.
	    'QiNgBP8IAaddr08X8+wHFmJSQhBbPAZGoSZSt5wQs6qoNC7UEd'.
	    '4AEoLIQSCaCCy78Dv8Tiv1hjjW1CRj8XIAgEKqDtt9keboMJZa'.
	    'vMjuzQVd3Xr9prTJo+GF/jKZea95R25Lxs8jg5qFGiwDnOS0mW'.
	    'NE0rjNRIt3WbklUCA9mV3Zdz8OBT/JfCQLB0SKYVVjGFYSfx/E'.
	    '26ow4e6uDujlPFQpE0FU6P8qNTHdXJdEdda0qf0itWBVM3pa/3'.
	    'ccUlIECJet0cAJoeYk5EZCeS5IwEoerSxccJBwRqFFf38QCTaO'.
	    'TRVFKJm3NTbtLNSyh2IkhIXsvLCesEGNCWdmwyruSD/z9kUlRc'.
	    '3bqNlSxhJNJ43p5JITrOEis8Qtr0cXEpU/JT/pmO18n2vb42pU'.
	    '3JnDnHMBqyPlpnoAaxhr2llv1ZUBqEGlqYwDQMsskMOcMgVL3Y'.
	    'ZOQTHAcQQiIGjHCwCaiovjrv4hbcpKuJJjIcDHm685RGr4GLCx'.
	    'YHkAcrLoAoDSLBiAQrMkjqybHJCbxgh+7xAC1MpsgzwRwD3qHL'.
	    'WyTIBdlAa6u2rHfXaew06PV78ZZjAwleNnkolECoH5i090wOcY'.
	    '+TgwYzFHiPi1zkOkXexeAMASnVU+LiyiA1wFUuaqggACLizeWw'.
	    'ycMzyssmVYKkbpGyC5T+OUALk2mKLHKWf+ED/az+YW42d66YL+'.
	    'aNrmEEzQCFEnKw368EgEvcN1m80eTIQIt0TFOjMJHkzNEBBYPp'.
	    'sblf8QHzrORO5JaWZ5ZLl6cuJyyxpNPv4PZdoT+GyIxBfI5uUg'.
	    'eJMCwP2/bIHO1JEudcgUUWOceKNq99mCvnzs5PzRcuTV4y5mRO'.
	    'SMIjo47z5S7a94oQCNKgJsZwO7D/IDNg3/LLhRNXt4JohBb4aG'.
	    '82GLdXcf93mQ+Y43r2RHZp+cRy6cqJK4l8MS+tdItaqiYtc0Mm'.
	    'QpfJARh98HYh9IiXVcaAo58wGb+LBAjbSPgCOcoSa0wzxXtc08'.
	    '/pv8mfyL+9MLVQvDJ1JVHJV6SZbFI1qtTsB+KlehRtRTGE8Afo'.
	    'P4DRcAxiEudhAHjjzz+ubgX4oHowakHQOlqzICQwyVPITGVOXi'.
	    'xfLF6aumzmczl5lHzMff2+fCdPaGttEkXoLQAO9B7C6EugPYby'.
	    'gVPjGXc5eIbNAJPjGwiAbaAJUQv8wVG7GROkJFpyOqn/ovgLba'.
	    '44L0+sDaraXb6jzq7aBQWjBOyUoHcaopOgmaA3IRyNDZnA1HjO'.
	    'HSBkr7eEFDAEngHrQCf+/s2A8cSiSkqcKUeeTjwFy2Jd78t3+L'.
	    'TR4itIiBLwLQhzkJyB5Cx4HXDaENVQCBAQcRqFIHTRaBIvuYXg'.
	    'AdsouuNxEL0ZUBHnSQp66R73zYfUtQ6OytKT8RckQAJQoLtgO5'.
	    'BJgj0D/WfgdyHaAHx8THoUcbGx8ciwhUl3bDEiToURPooeI7pH'.
	    'MziK9Yd9nU5a6GgKjOH41vsgI4hAcyC5AZkapF+AoYNrjjsuhx'.
	    'FbtPmeB5ykyQQzTPAWAQWC8S9oAI0QRRuPb9jkmyMZNAOTklvC'.
	    'GGYZaFkGmkVAh8h4DtKFMIBunG+pB5B5AIkGBDsQ+qBiL20caj'.
	    'zhJknq5KlgMkLjJHJos4kYEbFJi5vc5eYbATVN02bNWe19+32t'.
	    'aJWlFm3wbf8Rz5NbDFJdlOFBF/g7cBf0JkrbBb+F6j1DOduEkU'.
	    '8bWCOiSofPWadBnSZDWmgUkEMGhZCINut8S/0NBtPptFlZrBSu'.
	    'vnt1+ndnflfIp9OJ/279Ubbbd+lP7KBKPoEBsgnqLph/BRzwdS'.
	    'LnBUFvHcfdpRsGPAGqwMco6jynz+e0SPKYCHMfLX5VKHwcenR+'.
	    'Igd1XTcqlUr+xn/cePv91fevzy8sLO2OtrOpWkqL7gXKSAVRdh'.
	    'ZFEmEXoYkwBNqovoc/3GHH3aUR+jwC1oD/AWrANi4hGwyBzqEG'.
	    'Vvb77Dgi0eT1VZzJZMxKpVJYXV1dXF1dXVm6sPSvruue3Xzcyj'.
	    '6/syvDzwj0lNazK6Fj5LFCRZouZpBABj6jXouu3+Np6HNvDHaf'.
	    'g91t74msbMuOJicnSSaTKKUQEUQEpRSO69But1/dB0VEm5uby9'.
	    'y4cWNpdXX1+sLCworrume//PuXpeqnVeOban0U1PW2kcx+O9L7'.
	    'Te9sUB4lWFR9SqNtNGcHx+/RDD2+Am4D94CnQA8OjjlEhMnyJC'.
	    'srK8zOzu7BiYioMAzZ2Njg9u3brwIqpSSXy2WXl5eXLly4sOo4'.
	    'zoV6vV6oflrVP/7Tx8Hmw1Zb6ydqmpWp7ha8h4O3gjOhzVANmF'.
	    'XPMNQWvdDnCXCXuHR+APqH4fbCtm2mp6eZn59H13WJuYXRaKSU'.
	    'UiSTyVcBdV3XDcOwRaTU7/en19bWCn/79G+JL/76RbhZ22y7u+'.
	    '6ahl71nPDz/nO17m7wAxlabFOihy4+DvAcqAMbPzZ3OFzX5dmz'.
	    'Z2iahoiosUUVhiGNRgPHcV4GzGQy5uLiYuH8+fMzo9FoslarJW'.
	    '9+elP75E+fBJu1zY7qqpqBUW3T/niohnVvy+1zm5aVtp+WE2XT'.
	    'nrHFzbjh1tYLz3XdPjD4R3BKKba2tqhWq4dzUO3noBPn4H5PKy'.
	    'LaO++8U7hx48byhQsXVne7u6tf3/v64t3P7mbq9+odt+OuaWi3'.
	    'PLxbW2ytubjbQCgiMnt6VlaurWgz0zM0m02q1WrUaDSUUuqI56'.
	    'ivDxE5MCgiYllWtlwuL5mmufLV/a/O/uXPf9Ff1F+80Lv6Yx29'.
	    '2qHzyZBh3cdvc7gaTZuZkzPh/Py8ACqVSv1/uPZDKXUAGEWRtF'.
	    'qtxEcffZTL5XLF+2v39fqjeivshA/TpP83JLwzYFBzcA4370Cc'.
	    'S81nTRBUs9lkOByi1GuOPI4Rh3+26JZlnSkWi781DOPXvV4v3+'.
	    '/2G0R8kSBxB/jew+tERK+c49m2TblcxrZtXNfl+fPneJ6HZVmU'.
	    'y2VJJpNyaJ9TSinlOA5bW1u4rntkQA0oAG8D54gb9W3ianxM3A'.
	    'e/cn73U3Hq1Cm5du2aPjs7a+ztcSIShmE4ajQa6tatWzQajZ+0'.
	    'fbiKI+It4SvijVUj7kL2qvGfgkskEqTTaZmcnDROnTplJhIJTU'.
	    'QiwPd9P/Q8T6XTaQzDIAiCfzjP/wFVfszuFqdHXgAAAABJRU5E'.
	    'rkJggg==' ; 


//==========================================================
// File: pp_red.png
//==========================================================
	$this->imgdata_small[0][0]= 384 ;
	$this->imgdata_small[0][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsSAAALEgHS3X78AAAA'.
	    'B3RJTUUH0wMJFhouFobZrQAAAQ1JREFUeJyV1dFtwyAQBuD/og'.
	    'xQdYxa8gRY6hJ0jK6QdohMkTEuE5wUj5ERen05IoLvID7Jkn2G'.
	    'j8MgTMyMXqRlUQBYq9ydmaL2h1cwqD7l30t+L1iwlbYFRegY7I'.
	    'SHjkEifGg4ww3aBa/l4+9AhxWWr/dLhEunXUGHq6yGniw3QkOw'.
	    '3jJ7UBd82n/VVAlAtvsfp98lAj2sAJOhU4AeQ7DC1ubVBODWDJ'.
	    'TtCsEWa6u5M1NeFs1NzgdtuhHGtj+9Q2IDppQUAL6Cyrlz0gDN'.
	    'ohSMiJCt861672EiAhEhESG3woJ9V9OKTkwRKbdqz4cHmFLSFg'.
	    's69+LvAZKdeZ/n89uLnd2g0S+gjd5g8zzjH5Y/eLLi+NPEAAAA'.
	    'AElFTkSuQmCC' ; 

//==========================================================
// File: pp_orange.png
//==========================================================
	$this->imgdata_small[1][0]= 403 ;
	$this->imgdata_small[1][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsSAAALEgHS3X78AAAA'.
	    'B3RJTUUH0wMJFhwAnApz5AAAASBJREFUeJyN1dFthDAMBuDf7S'.
	    '3BCm2VCRKpS4QxbhikW6IewzcBqm6Fm6JyH7iEEByCn5AJH38g'.
	    'BBIRHNUzBAWAGNfe/SrUGv92CtNt309BrfFdMGPjvt9CD8Fyml'.
	    'ZZaDchRgA/59FDMD18pvNoNyHxMnUmgLmPHoJ+CqqfMaNAH22C'.
	    'fgqKRwR+GRpxGjXBEiuXDBWQhTK3plxijyWWvtKVS5KNG1xM8I'.
	    'OBr7geV1WupDqpmTAPKjCqLhxk/z0PImQmjKrAuI6vMXlhFroD'.
	    'vfdqITXWqg2YMSJEAFcReoag6UXU2DzPG8w5t09YYsAyLWvHrL'.
	    'HUy6D3XmvMAAhAay8kAJpBosX4vt0G4+4Jam6s6Rz1fgFG0ncA'.
	    'f3XfOQcA+Acv5IUSdQw9hgAAAABJRU5ErkJggg==' ; 

//==========================================================
// File: pp_pink.png
//==========================================================
	$this->imgdata_small[2][0]= 419 ;
	$this->imgdata_small[2][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsSAAALEgHS3X78AAAA'.
	    'B3RJTUUH0wMJFhsQzvz1RwAAATBJREFUeJyd1MFthDAQheF/oi'.
	    'gF+JYWQKICkCJRA1vGtrDbxFbhGvY0HVjCLeS2BeTiHFgTB2wg'.
	    'eRISstCnmcG2qCpbuXf3ADBQzWsPfZfS9y9HsEu4/Fo33Wf4Fx'.
	    'gxL3a1XkI3wbTNXHLoboVeLFUYDqObYBy+Fw/Uh9DdCmtOwIjF'.
	    'YvG76CZoOhNGRmpO8zz30CJoOhMAqlDxFzQLppgXj2XaNlP7FF'.
	    'GLL7ccMYCBgZERgCvXLBrfi2DEclmiKZwFY4tp6sW26bVfnede'.
	    'e5Hc5dC2bUgrXGKqWrwcXnNYDjmCrcCIiQgDcFYV05kQ8SXmnB'.
	    'NgPiVN06wrTDGAhz5EWY/FOccTk+cTnHM/YNu2YYllgFxCWuUM'.
	    'ikzGx+2Gc+4N+CoJW8n+5a2UKm2aBoBvGA6L7wfl8aoAAAAASU'.
	    'VORK5CYII=' ; 


//==========================================================
// File: pp_blue.png
//==========================================================
	$this->imgdata_small[3][0]= 883 ;
	$this->imgdata_small[3][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAACi1'.
	    'BMVEX///8AAAAAADMAAGYAAJkAAMwAAP8zAAAzADMzAGYzAJkz'.
	    'AMwzAP9mAABmADNmAGZmAJlmAMxmAP+ZAACZADOZAGaZAJmZAM'.
	    'yZAP/MAADMADPMAGbMAJnMAMzMAP//AAD/ADP/AGb/AJn/AMz/'.
	    'AP8AMwAAMzMAM2YAM5kAM8wAM/8zMwAzMzMzM2YzM5kzM8wzM/'.
	    '9mMwBmMzNmM2ZmM5lmM8xmM/+ZMwCZMzOZM2aZM5mZM8yZM//M'.
	    'MwDMMzPMM2bMM5nMM8zMM///MwD/MzP/M2b/M5n/M8z/M/8AZg'.
	    'AAZjMAZmYAZpkAZswAZv8zZgAzZjMzZmYzZpkzZswzZv9mZgBm'.
	    'ZjNmZmZmZplmZsxmZv+ZZgCZZjOZZmaZZpmZZsyZZv/MZgDMZj'.
	    'PMZmbMZpnMZszMZv//ZgD/ZjP/Zmb/Zpn/Zsz/Zv8AmQAAmTMA'.
	    'mWYAmZkAmcwAmf8zmQAzmTMzmWYzmZkzmcwzmf9mmQBmmTNmmW'.
	    'ZmmZlmmcxmmf+ZmQCZmTOZmWaZmZmZmcyZmf/MmQDMmTPMmWbM'.
	    'mZnMmczMmf//mQD/mTP/mWb/mZn/mcz/mf8AzAAAzDMAzGYAzJ'.
	    'kAzMwAzP8zzAAzzDMzzGYzzJkzzMwzzP9mzABmzDNmzGZmzJlm'.
	    'zMxmzP+ZzACZzDOZzGaZzJmZzMyZzP/MzADMzDPMzGbMzJnMzM'.
	    'zMzP//zAD/zDP/zGb/zJn/zMz/zP8A/wAA/zMA/2YA/5kA/8wA'.
	    '//8z/wAz/zMz/2Yz/5kz/8wz//9m/wBm/zNm/2Zm/5lm/8xm//'.
	    '+Z/wCZ/zOZ/2aZ/5mZ/8yZ///M/wDM/zPM/2bM/5nM/8zM////'.
	    '/wD//zP//2b//5n//8z///9jJVUgAAAAAXRSTlMAQObYZgAAAA'.
	    'FiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElN'.
	    'RQfTAwkWGTNerea3AAAAYUlEQVR4nHXNwQ3AIAxDUUfyoROxRZ'.
	    'icARin0EBTIP3Hp1gBRqSqYo0seqjZpnngojlWBir5+b8o06lM'.
	    'ha5uFKEpDZulV8l52axhVzqaCdxQp32qVSSwC1wN3fYiw7b76w'.
	    'bN4SMue4/KbwAAAABJRU5ErkJggg==' ; 

//==========================================================
// File: pp_green.png
//==========================================================
	$this->imgdata_small[4][0]= 447 ;
	$this->imgdata_small[4][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABm'.
	    'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsSAAALEgHS3X78AAAA'.
	    'B3RJTUUH0wMJFhkLdq9eKQAAAUxJREFUeJyN1LFVwzAQxvH/8f'.
	    'IeDS0FLKABlN6eIwPYAzCHB0gWYI2jj+i1ABUTQN4TRSQ7iiWZ'.
	    'qxLn9Mt9ydmiqrSq930AYFiu6YdKrf/hP1gYQn6960PxwBaYMG'.
	    'E9UA3dBFtVQjdBOQmBakLennK0CapRwbZRZ3N0O/IeEsqp3HKL'.
	    'Smtt5pUZgTPg4gdDud+6xoS97wM2rsxxmRSoTgoVcMZsXJkBho'.
	    'SmKqCuOuEtls6nmGMFPTUmxBKx/MeyNfQGLoOOiC2ddsxb1Kzv'.
	    'ZzUqu5IXbGDvBJf+hDisi77qFSuhq7Xpuu66TyJLRGbsXVUPxV'.
	    'SxsgkzDMt0mKT3/RcjL8C5hHnvJToXY0xYRZ4xnVKsV/S+a8YA'.
	    'AvCb3s9g13UhYj+TTo93B3fApRV1FVlEAD6H42DjN9/WvzDYuJ'.
	    'dL5b1/ji+/IX8EGWP4AwRii8PdFHTqAAAAAElFTkSuQmCC' ; 
    }
}

?>