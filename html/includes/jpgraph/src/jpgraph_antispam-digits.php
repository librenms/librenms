<?php
//=======================================================================
// File:	JPGRAPH_ANTISPAM.PHP
// Description:	Genarate anti-spam challenge
// Created: 	2004-10-07
// Ver:		$Id: jpgraph_antispam-digits.php 781 2006-10-08 08:07:47Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class HandDigits {
    public $digits = array();
    public $iHeight=30, $iWidth=30;

    function HandDigits() {
//==========================================================
// d6-small.jpg
//==========================================================
	$this->digits['6'][0]= 645 ;
	$this->digits['6'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAZAAEBAAMBAAAAAAAAAAAAAAAABgMEBwX/xAAvEAABAwMC'.
	    'BAQEBwAAAAAAAAABAgMEAAURBiESIjFRBxMUQRUWMmFTYnGRkrHC/8QAFgEBAQEAAAAAAAAAAAAAAAAAAAEC/8QAFhEBAQEAAAAA'.
	    'AAAAAAAAAAAAAAER/9oADAMBAAIRAxEAPwDslwiR3oDku8ONttsAvDiVyMcO/ET7ke5/aoOz6k1Vr5htNjW7a7M1yO3NTQU9JUDu'.
	    'GgrlSn8xyf6p4gXaHJvNps9/mKZtSkGdMjRwpfqAFBLLACRlZUrJONsI2717No1lbZ10kx7XGnRpKWQ/6GVGMfzEJ5VFIVtsOH6e'.
	    'wyKVhYsia0y22pLThSkJK1uniVgdThOM0ol+StIUhpopIyCFq3H8aUVCwnG3PGe4Rp6fLXJtMdyM0ojcIWvIz3HFnAPfrWTXb6GN'.
	    'WaLXDwZjVz8pKEfhuIUFg/bAz9sVJ61nt61mxJFslLtq7e5yPqiBT4UDklKw4MDpt+u+9bFiu9riXNu83R+fcr6tohuQ5HQhmK37'.
	    'paaC8DruScmg6X8KkjZEhbaB9KEyFYSOw26Uqd+e7Qerl5z74DY/1SomP//Z' ; 

//==========================================================
// d2-small.jpg
//==========================================================
	$this->digits['2'][0]= 606 ;
	$this->digits['2'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEQMBIgACEQEDEQH/xAAYAAEBAQEBAAAAAAAAAAAAAAAFAAQHAv/EACsQAAEDBAEC'.
	    'BAYDAAAAAAAAAAIBAwQABQYRIRIxQVFhcQcTFSJSU5GU0f/EABcBAAMBAAAAAAAAAAAAAAAAAAECAwT/xAAZEQACAwEAAAAAAAAA'.
	    'AAAAAAAAARESUUH/2gAMAwEAAhEDEQA/AOqXm/Q8dxmOL4PPSnCSNFixx6nXnkXgRT3Te17JWbGsveueSyLZdbPItNxOKLzTLjou'.
	    'gYCSoSoY8ISKSbFeUrzkdlnTL1YshskiErkQnFEZaF8kkdBBVdjyi6RNL5+9F486eS/ECVkcBtDt1vZcho5viS8ZCp9C9tAIAm/F'.
	    'VoPRU+HRtJ5JVRP1kP0PfwP+1VKrHBMliXG4Nw8VgE4xGkuqk2S1wTUNEVdIvgpL9iL6KtNxY7WOwo9tt0RCitj0sR2uCbFPPzH1'.
	    '7+6rRuSRcljMBMsUy2tky045KOawZk5xtEFBJEROO3hx61kh2rPCIX3MhsyC4QmfTbC6lH8dq5212qwkiG5H6Y/9R2qm+ofxqqsL'.
	    'DLZ6f//Z' ; 

//==========================================================
// d9-small.jpg
//==========================================================
	$this->digits['9'][0]= 680 ;
	$this->digits['9'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAZAAACAwEAAAAAAAAAAAAAAAAABAUGBwP/xAArEAABAwMD'.
	    'AgYBBQAAAAAAAAABAgMEBQYRABIhE1EUIjEzQUIHMlJhcdH/xAAWAQEBAQAAAAAAAAAAAAAAAAACAQD/xAAYEQEAAwEAAAAAAAAA'.
	    'AAAAAAAAAREhQf/aAAwDAQACEQMRAD8AkK7brF6X7XpMeGoKhFMLEeT4ZUheEhanF4OcZ2pTgDykk92bZpdCsi7aezLjxkIPUZiV'.
	    'RSCy8hah7EkZ27yM7V+iscal5bE22Lon1qNDmSKROd8Sl+Ix1lMOlIS4HGgQpbStoUCnlJz8HmsXtW3Lst2rmBAelLMRRekOwnYz'.
	    'Edls9QKKnOVLyk7UgcbzzrdBthqEJJwZbAI4x1U/7o1TaFa9lG36aXaZTy54VrcXUgrzsGdx+T30aNydweqVw1GS87T6Lb86Q4ha'.
	    'my/IAYjZBx+snKk99oOQMf1AViE65SY348hzFy6hPKnqtKz7DC1lbqyPrvJKUJ7H+M6Wrt3InP7o1brFNp4bCDGhxGAsqz69VSiQ'.
	    'ORwBxrrQ7itm1ac7Hp0WoGTIc3PSn0pccdcP2WorycfA1RaRHjxosZqOyhtDTSAhCf2gDAGjVHTd9sKSCumynFEZK1tIJUe58/ro'.
	    '1V1//9k=' ; 

//==========================================================
// d5-small.jpg
//==========================================================
	$this->digits['5'][0]= 632 ;
	$this->digits['5'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAZAAACAwEAAAAAAAAAAAAAAAAABgIFBwT/xAAoEAABAwME'.
	    'AQQCAwAAAAAAAAABAgMEBQYRABIhIkEUMVFhBxNCgaH/xAAVAQEBAAAAAAAAAAAAAAAAAAAAAv/EABcRAQEBAQAAAAAAAAAAAAAA'.
	    'AAABEUH/2gAMAwEAAhEDEQA/ANGvW4YVOeiRX5b4mv5Sin05IdlupPKdo/j2SO3+6TbPNQvOsTVz33KRT4csR3YUF7Dsh5OSFvug'.
	    'kqG4FPBxnjxpvvi4KZb1pTpU+QwxUi2Y7ZIAefUk5ATxnB9/gbtL/wCH1UpuhPUlZlMVaQ0mS8zJjqZOPfc2TwpIUonI9tw40R1r'.
	    'WNGq/wBdJR1XT3lqHBUnGCfkfWjRWs1ve249erQqQYjOtN1FqPUpCXQ4WIzQSsJwT0UpRwQPG0nzqyuNHobjsl9kBuWqoOoXtT1/'.
	    'WppZcA8lKRj64HxqU+3KpAr6plElRVKef3S4E0K9O8pLXVzKcqSsJAB9wSAca6bSoNXeuA1+5pEV+SGFNU1iKVFqI0Vdx2AJUeoz'.
	    '8DGlTDwG3CAf3q/pI0ah6MDhLz6U+EpXwPoaNMU//9k=' ; 

//==========================================================
// d1-small.jpg
//==========================================================
	$this->digits['1'][0]= 646 ;
	$this->digits['1'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEwMBIgACEQEDEQH/xAAZAAADAAMAAAAAAAAAAAAAAAAABQYCBAf/xAApEAACAQMD'.
	    'AwQBBQAAAAAAAAABAgMEBREABiESMUEHEyJRkSNCYXGB/8QAFgEBAQEAAAAAAAAAAAAAAAAAAAEC/8QAFxEBAQEBAAAAAAAAAAAA'.
	    'AAAAAAEREv/aAAwDAQACEQMRAD8A6jdd4WLbstILnc4Uq0VoWpkJknb6IjXLHJUePOlez923fcW4r1SxWlqC2UbdKirQif3Xw3yA'.
	    'OFAGT09/kO3OmV3a20MFRf6lIYPcpy7yRRAzgxjIy2M8YwcdiBzpX6d22VNvUlTXsFkuwkrKqNSfnK7F8OTzwrAY+l5zoxKskudN'.
	    'EgQPUT9PBkWF3DH+1GPxo1mLnRoAqF2VRgGOFmX/AAgY/GjRUP6hVMFv2FuFqUvUGrpDFJMBnpdyF5bsAQew7Hxzp6LZNT0yQ1DI'.
	    'wp0QCFBhD0jCsfLZHxbx5xxpTuvb1+v9PV7Ztk9roLPLCjmSSN3mX5ZwqjCgZX7PfWxDQb2in96pv9qq46aTE0bW4x9ceAWAYPwS'.
	    'PsYzoixgmheBGjIVcYCnjp/jHjHbRpe1JLn9OnopE/a0ykvjwDx47aNMXqP/2Q==' ; 

//==========================================================
// d8-small.jpg
//==========================================================
	$this->digits['8'][0]= 694 ;
	$this->digits['8'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AFQMBIgACEQEDEQH/xAAYAAADAQEAAAAAAAAAAAAAAAAABgcEBf/EACsQAAEDAwMD'.
	    'AwMFAAAAAAAAAAECAwQFBhEAEiEUMVEHE0EVYYEiIzJCsf/EABYBAQEBAAAAAAAAAAAAAAAAAAIAAf/EABcRAQEBAQAAAAAAAAAA'.
	    'AAAAAAABERL/2gAMAwEAAhEDEQA/AKL6gVVUa0i1T5QjvTprUJMlxW4R9zgQXe/AH+kaWrntqlWjaq7gpcmotXAw82ht9yY4tch8'.
	    'uAFC0k7VBXPGMY51ruiaue+bThIj+7NbWqS+7HDxajFf6AlB/k44o8ZOABk4xkL0X0tZiojKrlRuGRJjugqldSlKGf6t7BuUQe3J'.
	    '44xxxrA1a4KVJipLidri8uLHgqOcfjOPxo0o2hdDvS1CmV2Yl6fS5ioipIQR1CAlKkLKR2UUqAI8g6NRSwuuyHab6s1ufLI/Zai7'.
	    'UBJOxhTS0+6B32pWSFH4CidOdWU0ukLiN1BLr0zG5Sdm3GRvcPhIT858DvjXNrVsSLnm/VIdTXS6tTnFsxZTSN3jchaTwps+O/z9'.
	    'tcBVq3hIX0tYqlIiQHdy5CqRHKHXEjAOMgBKjnvyRk4xrQa7OiGt1K5biYZL8SoVEpjOqkFsONtJCNwASeCQrn7aNUKnQYtLp7EC'.
	    'EylmLHQltptPZKQOBo1FzH//2Q==' ; 

//==========================================================
// d4-small.jpg
//==========================================================
	$this->digits['4'][0]= 643 ;
	$this->digits['4'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAYAAADAQEAAAAAAAAAAAAAAAAABAYHAv/EAC0QAAIBAwQA'.
	    'BAMJAAAAAAAAAAECAwQFEQAGEiETFDFBUmGBByIjUVNxobHR/8QAFgEBAQEAAAAAAAAAAAAAAAAAAAIB/8QAGBEBAAMBAAAAAAAA'.
	    'AAAAAAAAAAERIVH/2gAMAwEAAhEDEQA/ANjM00Nxmt1xiWW31CZp5uJwoAAaOQ/n7qfcZHqO5my3q5XX7R6ijiqnNut9u4NyJ4yv'.
	    'JJyjYr8Xhrn5g599J7x3ulBNU7Zo7dXXXcLQ8kURYi4epYtkALjOePv1nUvbLvV7P3BZm3DR3eh88Kp7pVzBZI6iUhGWRRGWwE44'.
	    'HX3V+uiL1uHgt+vL/H+aNJQ3CSeCOaFqSaJ1DJKs/TqRkMOvQjvRorHE4pRDLNWLGlRHGUeYIORXs9e5B7OP31E0fmdyb/t0DJ4Q'.
	    '27bfx3YZzPUIoAAz7IpOD6cuxq0uNumqLfVNDOqXBoZEjnZcqhIPXH4c46+WkdoWOltu3IDDLLLVVR83UVcuPEmmcZZ2/rHoAANG'.
	    'GI7KIY1ijoLeEQBVCwIoAHpgY6Hy0aZe7mJ2jeHLKcEhusj6aNKgzr//2Q==' ; 

//==========================================================
// d7-small.jpg
//==========================================================
	$this->digits['7'][0]= 658 ;
	$this->digits['7'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAZAAACAwEAAAAAAAAAAAAAAAAABgEFBwT/xAAuEAABAwIE'.
	    'BAQGAwAAAAAAAAABAgMEBREABiExEhMiQSMyUXEHFBclVJFhk9L/xAAXAQADAQAAAAAAAAAAAAAAAAAAAQID/8QAGREBAQEAAwAA'.
	    'AAAAAAAAAAAAAAEREiFR/9oADAMBAAIRAxEAPwDXq9mCjZeQ05VZ5ZST4bfEpa3VdglCbqUe+g9MZ5Uq7V8415WXoMSdQ6etgSps'.
	    '19wpkCMDZKUpv0FZvbi1NzpYasMDLDUbMVXrtQdbeeU23xLWkj5RlLYK0J7anW9gbAjCzkOtsVSUJUdtc6dVZK51UeaFm4LKbhpC'.
	    'l7EhIFkDW974GbRI2XorUVls1OTdKAOqUpR0Hc3198GITQ6k+hLwrEpoODiDenRfW23bBicg78JXxPpD0mgVOW5PAivNNpahsPW5'.
	    '8xxQaSVkboQnhsnYm5OHqDGp1IpsalMKjMsMIC3+XZKbJFth62/QOEfMOZqZXp9JcKZTcGmTky3meSi7xQklI81vMR+sXIz/AEgp'.
	    'Q0qPNu6ea8Q2jqtbp8+2w9h/OKORc/cpHjt1dDSHOtLZ4ekHW23bBjj+o9H/AB539aP94MG0+L//2Q==' ; 

//==========================================================
// d3-small.jpg
//==========================================================
	$this->digits['3'][0]= 662 ;
	$this->digits['3'][1]= 
	    '/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
	    'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
	    'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAZAAACAwEAAAAAAAAAAAAAAAAABAUGBwL/xAArEAABBAED'.
	    'AwMDBQEAAAAAAAABAgMEBREABhIhMUEiMmETFZEHFkJDUdH/xAAWAQEBAQAAAAAAAAAAAAAAAAABAAL/xAAYEQEBAQEBAAAAAAAA'.
	    'AAAAAAAAEQExQf/aAAwDAQACEQMRAD8A0vclruBdk3VVLLUNssGRJsZSCtqOjlgJAHvcOD6c4HnOdIbcttw1W5P29cFEhuawqTXS'.
	    'VsJjnCMBxKkJJx7goAde+ceJfdNxU0UNlyymyXHi6kxWUNl1S3EnkAEIHX2nv86qtTuZr9Q9+1VhRsOoYpYcgSVyAE/TdewkJxnK'.
	    'sBCjkdPGpnOtFMd3PqsXgfOAgD8Y0aX+11H9rDDjn8lr9yj5J+dGqsqxaw6Cc9cQZU4Sp7zTJsIrKlcUEKwhSin1JABI45GUjqOu'.
	    'lbOvjbc3Ts9ynjGCy445UuFLYRzbWgrT6fhSCQSMDke+pew2zYVly/d7YchNqkMJZnQpgV9J8IzwWFJyUrAJHYgjvpLbu37G5nR7'.
	    'vck5C3YRKYEOEVJZj8kjKypXqWvirjk9h+dB9i4faa89TDZUfKlIyT8k+To10a6KTkpcJ/0vL/7o0TS//9k=' ; 
    } 
}

class AntiSpam {

    var $iNumber='';

    function AntiSpam($aNumber='') {
	$this->iNumber = $aNumber;
    }

    function Rand($aLen) {
	$d='';
	for($i=0; $i < $aLen; ++$i) {
	    $d .= rand(1,9);
	}
	$this->iNumber = $d;
	return $d;
    }

    function Stroke() {

	$n=strlen($this->iNumber);
	for($i=0; $i < $n; ++$i ) {
	    if( !is_numeric($this->iNumber[$i]) || $this->iNumber[$i]==0 ) {
		return false;
	    }
	}

	$dd = new HandDigits();
	$n = strlen($this->iNumber);
	$img = @imagecreatetruecolor($n*$dd->iWidth, $dd->iHeight);
	if( $img < 1 ) {
	    return false;
	}
	$start=0;
	for($i=0; $i < $n; ++$i ) {
	    $size = $dd->digits[$this->iNumber[$i]][0];
	    $dimg = imagecreatefromstring(base64_decode($dd->digits[$this->iNumber[$i]][1]));
	    imagecopy($img,$dimg,$start,0,0,0,imagesx($dimg), $dd->iHeight);
	    $start += imagesx($dimg);
	}
	$resimg = @imagecreatetruecolor($start+4, $dd->iHeight+4);
	if( $resimg < 1 ) {
	    return false;
	}
	imagecopy($resimg,$img,2,2,0,0,$start, $dd->iHeight);
	header("Content-type: image/jpeg");
	imagejpeg($resimg);
	return true;
    }
}

?>
