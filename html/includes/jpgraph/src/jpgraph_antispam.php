<?php
//=======================================================================
// File:	JPGRAPH_ANTISPAM.PHP
// Description:	Genarate anti-spam challenge
// Created: 	2004-10-07
// Ver:		$Id: jpgraph_antispam.php 808 2006-11-28 19:10:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class HandDigits {
    public $chars = array();
    public $iHeight=30, $iWidth=30;

    function HandDigits() {

//==========================================================
// lj-small.jpg
//==========================================================
$this->chars['j'][0]= 658 ;
$this->chars['j'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABUDASIAAhEBAxEB/8QAGAAAAwEBAAAAAAAAAAAAAAAAAAUGBAf/xAAsEAACAQMDAwMBCQAAAAAAAAAB'.
'AgMEBREAEjEGIUEUUXGBBxMVIiNSYWKC/8QAFgEBAQEAAAAAAAAAAAAAAAAAAwEC/8QAGhEAAwADAQAAAAAAAAAAAAAAAAECERIh'.
'Mv/aAAwDAQACEQMRAD8A6veK2st8zRWSyV1dUBfvHaGVI4hknsS7AFv4AyM57ayWbqeS+11xtT2etttwo4YqhEqnQs5bcAfyk4AZ'.
'SOeD441TKRTyingUBG4/ah8j684+dSFzh/BvtaslejMUu9DPQTDnLx4lQ/ONw1TGBm0jdRWqguEMghEisWilgDmNs4Ze+MEEEH40'.
'aUVFTa7JeLjRXu4GjhmnNbSfqFQVlA3rkckOjH/Q99Glmkl0C/Q06pvsvT9vttXHDF6T1KrWbs5gRgQJM+FDlQxPhjpF1XcVq+qe'.
'jEoKiOecXBqh2TDDYIXLKuP6549xk8auI6aJqV45oknWdNswkAIkGMYIxjGO2NR1F0LZY5qkWqkS1xrM0M8lMSJpY+TGrnJiQ577'.
'cEgeNHhi7D3qC3UN69M8tIakRhgrh9o748+eNGtcCiKjjpkQKlMTEg3ZwoxtHHtgfTRpYXArvp//2Q==' ; 

//==========================================================
// lf-small.jpg
//==========================================================
$this->chars['f'][0]= 633 ;
$this->chars['f'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAQFBgcC/8QAKxAAAgEDAwMCBQUAAAAAAAAA'.
'AQIDBBEhAAUGEjFBEyIHFFFhoRUzYnGS/8QAFQEBAQAAAAAAAAAAAAAAAAAAAQP/xAAaEQACAwEBAAAAAAAAAAAAAAAAAQIRMRIh'.
'/9oADAMBAAIRAxEAPwDcnmLoIkiSYsouC3tA++O2lU9WkqVjJ+YdhZLsQI/4/YfQm50kZP0vbmaCSU0SRNIH6sghb9INs3t38dvp'.
'akUuz8x5DwdN5peS1jV1dSipSiVUigIcdQjQ26lIB/c6r3F86SZpE/zCFJaqsihQNhRgdj3Jyfxo0jDSbXHt9Oph9RAoV3qJGltY'.
'HDOxyb/nRpV0D3RXle21m48XraOk3IUSemUaV4g4Zc9ShcDtgff+tQfwvjq34Dtku7buamFqeJKemCCMxKFsEJU+/FrX8d76sEHG'.
'aNItzr4usVNdG3S0rmRYAVwEUmyjyQLZ11x7aF4zs9DQOyzml29I2cLa/pixIHi99DFCtU9dFuLIaijo9qiYPmR2mZmB9thgAHOD'.
'4+mjUrURyrUNMZFEkkIOFuFAbsP9d/OjVIQ6Vh4tP//Z' ; 

//==========================================================
// lb-small.jpg
//==========================================================
$this->chars['b'][0]= 645 ;
$this->chars['b'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABUDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAYCAwUH/8QAKxAAAQMDAwMDAwUAAAAAAAAA'.
'AQIDBAAFEQYSIRMxUSJBYQcVI2JxgqHw/8QAFQEBAQAAAAAAAAAAAAAAAAAAAQL/xAAYEQEBAQEBAAAAAAAAAAAAAAAAATERYf/a'.
'AAwDAQACEQMRAD8A6H95mxNYwLXcX+pCuilSLXJ6YSplaUELjqxwe4IJ5PIPamJ2V0bPcS7+NxCX1cHggAnIP+xSd9RyzHh2m7FQ'.
'Q1CvMNQWTjCt+HFD+PB/Y1fI1PL1HFFt0zaGblFdJQ9cJjpZiqPJUlBAKnPcEpGB5NNRKdrOl1NlgiQol4R2w4Sc5VtGf7opZteo'.
'LhdorjUSM5FnQnlR50NeHQysYxtVxlJHIPgjtRRD3xkaghs6juumdHz4+Y7RVPnt59K2mk7W+fcKWsZ7djTXMkW+xMP3GRJjwIEN'.
'HTG/CWx5wPY8AADx2NYk3SL9wukvUjGobnBkORksIbjdMANozgEqSo8qJPGO/wAVO36IsjUmBIfZfuM7epZk3F9UhSSk5O0K9Kcq'.
'8AcU3UzFuhUSBFud6nRXoz96mqmJZWg7m2dqUNhWBwdqQSP1UU5c/FFCn//Z' ; 

//==========================================================
// d6-small.jpg
//==========================================================
$this->chars['6'][0]= 645 ;
$this->chars['6'][1]= 
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
// lx-small.jpg
//==========================================================
$this->chars['x'][0]= 650 ;
$this->chars['x'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABMDASIAAhEBAxEB/8QAGAAAAwEBAAAAAAAAAAAAAAAAAAUHBgj/xAApEAABAwMDAwQCAwAAAAAAAAAB'.
'AgMEBQYRACFBBxIxFCJRgRNxkcHw/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAH/xAAWEQEBAQAAAAAAAAAAAAAAAAAAEQH/2gAMAwEA'.
'AhEDEQA/AH9t3pKvO14UykVARa/HfAlxlDKXR24V2p3z7RlPwdtMep91uWdRGHWELjuTFFtLvcC4SNznnH+21O7ttiodOq1BvC0E'.
'p9I0lSX2kgqCSklK+5PKCMAng6zV2XRO6u3lSIURtbDRShltlZHa0tW7q/0MeTwnjxq1Jiw2xc9xTLbhSVU5iaXUFfqFFILgJOCd'.
'9Gt3SXabR6REpkL8yo0RpLCFNx1qBCRjOQMHxo0pEr6o3um2LVYpMEpTVqg25lHn08dfcB9kEgfZ1LIFDuawqZRb7aQlLTzqglsg'.
'9wQdveOEqBIB425xqhQuk8qo9UKlPrlRblw2ZBeCSVKW6CcoSrI2AGOT41SKzT4dYtmdS5bIXDZhNoWgbZJ94x8AYT/GkM03oNUc'.
'uKgwqtTZDTMOU0FttqRkoHggnPkEEHRrkJ6t1SlSHYUOc6zHaWrsbQrATk5/vRqK/9k=' ; 

//==========================================================
// d2-small.jpg
//==========================================================
$this->chars['2'][0]= 606 ;
$this->chars['2'][1]= 
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
// lm-small.jpg
//==========================================================
$this->chars['m'][0]= 649 ;
$this->chars['m'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGgAAAgMBAQAAAAAAAAAAAAAAAAcDBAUCBv/EAC0QAAICAQMCBAMJAAAAAAAA'.
'AAECAwQRAAUSBiETMVFhB2KhFSIyQVJxgZHB/8QAFgEBAQEAAAAAAAAAAAAAAAAAAgED/8QAGREBAQEAAwAAAAAAAAAAAAAAAQAR'.
'EiEx/9oADAMBAAIRAxEAPwB0MI2lIdgI0Cly3kFXLEn2zx1FDdp7rbpbjUtRWKio3hyxOGQllJzkegX66rQ2qW87Zuk9S5FNVmru'.
'iywyBhjDKTkeXfSr+GRfYtq2KAO32b1BGxAZu0dyJ2DKPTxY1wPddVszycUq2Golq8jRWbcnJWwCVGMjz+VQP50atxMtm2ZUOY4l'.
'4qfUnBP0x/Z0amy4jJm10Tt2yddWasFmfaRfdrlG3UcgArnxKzJ+Fu4DqCMkcgNem2DoWav8PLfTm+FPEkuSNTnqueS5bnHIv6CG'.
'LNjJwM99bm67NB1Ht89KSxNXnr2hNDbiUc47K4KyD2GQMfmMjUnS+7vuIktTqPCaaWCqAMMojPFyw8hyYMQBnAwNJHYGXPTsW9VN'.
'jg2zf50W9zk524GAEihuz+xbIOD82jW5TkjtRPZkTkJ+4VgDhQfuj/f3OjUxl1f/2Q==' ; 

//==========================================================
// lt-small.jpg
//==========================================================
$this->chars['t'][0]= 648 ;
$this->chars['t'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAQDBQYH/8QAJxAAAQMDAgYDAQEAAAAAAAAA'.
'AQIDBAUGEQASEyExQVFhIjJxFSP/xAAWAQEBAQAAAAAAAAAAAAAAAAABAAP/xAAZEQADAQEBAAAAAAAAAAAAAAAAAREhMUH/2gAM'.
'AwEAAhEDEQA/AO4BLEiEy7uG4IGxxs5IOOx76wd2XYidSp1HoD70240gcNNPbDyI6wQQpaz8E9MczkdhqtbsKYLieDk6WLKmZmmL'.
'Hk7AHVkbkLI+RQc7uRxgkfr1tx2rGu6VbToLVKkhU+kbugGf9WfaknCk5ycaX0zmaa+3JkqvW/CmzojsB9xoF6OoFK0r6HOcEDI0'.
'aefTuKX5ScMdC14HYq8n12zo1DEUcKTGg1Z+hyBwoPBVIiA/VQyOIgedhUCB4WMfXSV3UufVLcTUIqVf26K6mXDbPVRRzKT54iMg'.
'+zjtq6mtsyJjclxpKlUhSXEbkgkqWnBx4+J5e/zU0pZemPvJJQzEPDfQOrwwFY9AZ5eeYPLV6FwhoFYZuigxpkJeIjqAeIoAk9wA'.
'D46EnuD+6Nc1smDNrTlRkxqtMo1vzKhIdYgU9YDqVpISrLhHxSSd21I0aYyqP//Z' ; 

//==========================================================
// li-small.jpg
//==========================================================
$this->chars['i'][0]= 639 ;
$this->chars['i'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABYDASIAAhEBAxEB/8QAFwABAQEBAAAAAAAAAAAAAAAABwAGBP/EACcQAAEEAQMEAgIDAAAAAAAAAAEC'.
'AwQRBQAGEiExQVEHExSBFWFx/8QAFgEBAQEAAAAAAAAAAAAAAAAAAgMB/8QAGBEBAQEBAQAAAAAAAAAAAAAAAAECMRH/2gAMAwEA'.
'AhEDEQA/AE7c+5M9BeRG29t1WUfKFFYW+GvrI7WD3B9g140YD5T36rcErDjbUR6dCBdejsKUpxITXI2FUrooCh70yvxzHyIlMvuK'.
'eVSH7IKEpJoKqu/ahddLryR/aMiO187bsmrWShhp1AZS2XHHrWhNJrzdf7f7GiVcHk3sptmHkJcJ2DIftS2FrKlJPXudWuLGYeQp'.
't2fmEIckqIZaaKuSGG0lQ4gduRoFRHQ9AOgs2lOJbk9aSUlpjGvAWeSVH2VKq/2dFPw3IjyJe8s281ct3I9UoHJXGiQkD2STrSZ7'.
'Yf8AOl7JTdw5eOCz0jw3+LbYCfA9nz71msb8KMxoTGTw+5srjsipAdDqFBQBIuiOl6KrdYyJMyTCshlw2G3Fr/HiNqNNAqJJUoGl'.
'KND+h47km1bZwsvCbYYjycxIyK1qDv2yEi0hQviK8atKDcy9j//Z' ;
 

//==========================================================
// lp-small.jpg
//==========================================================
$this->chars['p'][0]= 700 ;
$this->chars['p'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGgAAAQUBAAAAAAAAAAAAAAAAAAECBAUGB//EAC8QAAEDAwMCBAMJAAAAAAAA'.
'AAECAwQFESEABhIiMRMVUWEHFEEWIzIzcYGRocH/xAAWAQEBAQAAAAAAAAAAAAAAAAADAgH/xAAcEQACAgIDAAAAAAAAAAAAAAAA'.
'AQIxAxESIUH/2gAMAwEAAhEDEQA/AOh703xG21DMeOyqoVNDjSzERiwU6Ep5qtZNycA97HTF13d33KWtmlt9xwkLl1NkXVxIuQgK'.
'wLj+hqBvel0qmbR8GnR22nJNZiLeeKr8nDIT1OLJucX+uPbWom7iocRpafOac5MX1ALltp/Cbi+cJH++utdh+WVNL3PNdNYpdWgx'.
'Y0qmLZSrwJJcQoOJ5XKlJFu4HbJOjVbt+V5nu7eopNRivqcdhK+bFnWwA1Y2AOcgjvj9dGlxy0g5y0xd+hNXoG24C4obizq3HZUh'.
'YHqtRHD06bG/8a0MbbG1mqekxaBSGmgkrcdcitlLfrckZIz7DUatbeFak0tyRLUwzT5vmiGm0cufEkFBJItfkD+59tKmiO12atFa'.
'eQukO3ejUxgENqTcfnE5WbkHiOnJ76N2IqI1DibabptS+zkZhtp90F2Y0S026EkAFK/qL46cXv65NVZDfxHmVCK4DE2/RX/lRFbA'.
'C5LwAyq2EtpHZI7mxPYDRqoctdESimz/2Q==' ; 

//==========================================================
// le-small.jpg
//==========================================================
$this->chars['e'][0]= 700 ;
$this->chars['e'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABgDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAYEBQcB/8QAKhAAAQMCBAUEAwEAAAAAAAAA'.
'AgEDBAURAAYSIQciMTJBE0JRYRQVFoH/xAAXAQEBAQEAAAAAAAAAAAAAAAAAAgED/8QAGREAAwEBAQAAAAAAAAAAAAAAAAERAjFB'.
'/9oADAMBAAIRAxEAPwDTszvhEYCoS80BTm2bCjQRwdAzVe2yopkpJtpRUVfjEIc4V2oMerByg5Ji30oMyS3GeMunK0upfnu09MdJ'.
'p2scTmWnnGfx6HThktgLfKj7xEOqyr7QBbL41LhBzpxbcOru0LKDLdSnOHoaltNqSC4qWL0x9xbJYum69caczSaHmGmTmpDUYn4l'.
'UiqjkynzAVtwV23Ud+X4Ibpa2DCPkjhfUaRO/p8yzpb+YHhUmhbev6ZEll1lvqK3jt2XrbBgp6HVwsK3THpfEubGSoOUyFMpbJmL'.
'Deh6SgOGKti57EuY6l62JMWdJy7k3hg1LkOozEbVm7suQSkTiKtkEfP1pH664Za/QItccgI4bseTHdNxiXHLQ8yVl7V32XyioqL5'.
'TGc1ng6eYs0idczXUZscBBABWgEhEtfKNuUezwPnBhEuj8X2M21z9BR6NUX211Kk/UKKAjuhkPhL7XVf8vtgw7UPJlEyrDWFSYLb'.
'LBNF6qrzG6t0spEu6+fpL7YMXhUndp//2Q==' ; 

//==========================================================
// la-small.jpg
//==========================================================
$this->chars['a'][0]= 730 ;
$this->chars['a'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABoDASIAAhEBAxEB/8QAGAABAAMBAAAAAAAAAAAAAAAABgMEBwX/xAAvEAABAwIFAQcCBwAAAAAAAAAB'.
'AgMEBREAEiExQQYHFBUiUXGBE2EyQkNSgpHh/8QAFwEBAQEBAAAAAAAAAAAAAAAAAAMBAv/EABkRAAMBAQEAAAAAAAAAAAAAAAAB'.
'IQIRMf/aAAwDAQACEQMRAD8AfdQ1pxjqZMSn0mRUZRYDaklJCE3OawO2ttTxY4hl07qFMVs1Ku02kpPnRGhsAqz8W9T9wDjozq6o'.
'Q1lDrcZLGVcmUoZg0obpufxK3Ftt9ccqB1GgBcmLSqtVEqOZcr6ARm/kbXHt7DEtc7WTJKTJqEWvRKfLqL9QplSjuPtGVYOJKBrm'.
't+U+n94WGStZzNypmRWqckUKTbixy6jAfxPxHtCgKqFNlU5huK6pLMndSlegG4J45N8aKmTMKQRBsCNMzwB+RbHWHGEAZlPZX2hx'.
'qZIC34ygZoYUbB50JSkFXFhZR9BrpheR4fIbQ6gvurJ7q02bIQTuAOAN8x40HAxRr3TrNRpBmSHVt1KMlTyJTCsqkKAPlSf28W+c'.
'UGaD1c9HSR1HFUh9tJU45EBcAtcC9+P9wqbg8IAto9o81yputrVGpiUkgHKkqUTZI32+cKm1z1tIUgPBBAKQ4UBQH3uL3xmXSXep'.
'HVDtXStE5K5jlPU7PF3Q41+okJFkjgC+3OuNSYiSzHaLtRcW4UDMpLYSCbakDW3thhum5p//2Q==' ;

//==========================================================
// d9-small.jpg
//==========================================================
$this->chars['9'][0]= 680 ;
$this->chars['9'][1]= 
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
$this->chars['5'][0]= 632 ;
$this->chars['5'][1]= 
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
$this->chars['1'][0]= 646 ;
$this->chars['1'][1]= 
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
// ll-small.jpg
//==========================================================
$this->chars['l'][0]= 626 ;
$this->chars['l'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGAAAAgMAAAAAAAAAAAAAAAAAAAYEBQf/xAArEAACAQIFAwIGAwAAAAAAAAAB'.
'AgMEEQAFBhIhFEFREzEHFSIyYcFxgZH/xAAXAQEAAwAAAAAAAAAAAAAAAAACAAED/8QAGhEAAwEAAwAAAAAAAAAAAAAAAAECMREh'.
'Qf/aAAwDAQACEQMRAD8A15Zfm1VURj1Fp5AqLKv3OARcL4W5Nzx+MLWjdRz5hqXU6TSb6OCr6WghiQbrJ91gOTy1yT5xZ55myZFk'.
'Gb5ozX6Ondm28XYqpQDwu7jEH4c5S2UaDy4xxrLmlUDWzk8XaQ3O49hbj+RiB85HNg8Ee3aqwIqhDuux7G/HHbvzgxEqaWOvy09R'.
'O0o3hjdQoUji20g+fY3wYSM6pJ4Ylr7V+Zz5PSaezHTlTRNWzxySSxt6q1MSkH6AOT2Fu3Aw7RfF/T9DEkLUeawuF2mKSgdWQj2/'.
'q3+fnDZDlqRZzQGaOGcpTOaeR1u8R+ncN3gj94so2jNWHeMNNKzorEX2qp9v3imNPoRE1zpjUtZ09HJmYq5lury0benZeTww23t3'.
'Ivgw+T0yRRyyxIqNfkLcA8jt7YMKcBWn/9k=' ;


//==========================================================
// ls-small.jpg
//==========================================================
$this->chars['s'][0]= 701 ;
$this->chars['s'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABQDASIAAhEBAxEB/8QAGgAAAgMBAQAAAAAAAAAAAAAAAAMCBAUGB//EACwQAAEEAQIFAgUFAAAAAAAA'.
'AAECAwQFEQAGEhMUITEiYQcjQVFxFRZCUoH/xAAWAQEBAQAAAAAAAAAAAAAAAAADAgH/xAAZEQADAQEBAAAAAAAAAAAAAAAAAQIR'.
'EiH/2gAMAwEAAhEDEQA/APWZMhmFXSJU+SGmWFiQtAWMJQAnJUr8Z+w/OuQk71uZnMsqnbjy9s8st9UMCQ6kZJdZaIHEkZ/JHceN'.
'N3HtizuY1JLrG48yLBSC9UTFKQiY4nACir+wAOOMEe2rm2bTbzlqtE1MyBuZAPybpw85KSfDRJ4Cg+Pl/wC61hJeGjV31VuuKqwr'.
'LGU+whZZK+Rw+oYJAyj3GjS4dZFpZVkqPLktdfMXNcaU2kBC1BIITkdx6c599GlnvPAa3TL2vNvU76n0063acr3YSLCEjpUpUQtW'.
'Dhf14SMEnOc57aZ8Tegm7dbrEQGZt1PeTDgc1PEW3FeXAvyAkZVkeMDOm2G3f3O7Cl/qEuqkQg4lp6CRxraWfUlRUD24kZA741Ko'.
'2k1HvlT3ri2sLOCgtsyJz6XEtBwZPAgJAGQMHUNPWKqWItsqh0UCFVyLeKhyLHQ2TMdHNVj+RKlAnJyfto1FW2ahgjrq6LYTFjjf'.
'lymUOLdWfJyoHA+gA7AAAaNPE3ysJdLT/9k=' ; 

//==========================================================
// lh-small.jpg
//==========================================================
$this->chars['h'][0]= 677 ;
$this->chars['h'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABUDASIAAhEBAxEB/8QAGgAAAQUBAAAAAAAAAAAAAAAAAAIDBAUGB//EACwQAAIBAwMCBQIHAAAAAAAA'.
'AAECAwQFEQAGEiExExQiQVEVggcyU2GRocH/xAAXAQADAQAAAAAAAAAAAAAAAAAAAwQB/8QAGhEBAQEAAwEAAAAAAAAAAAAAAQAC'.
'AyEyMf/aAAwDAQACEQMRAD8A6DZb95q9bmpK6ieOCzNHJTxmE+NMhQ5fr1fLq3Ejvkak2e7ipiFsqb3R0m4qkPPJRiRXenU9VjKE'.
'5JVcA9R7nWc3/BUbfoKTdO3VRXhpjbZ2D8Rwk6RyZH6chB+46m7i2hDYtgA2ePlV2VkuKysoLzzRnlIScZJZeeevvjtrX7LK2rp7'.
'tTwwJ9WjhILDrTKnIdMEDl2+P80aVdJZb1QW+vgqENLPH4sBCDLIwUgnOf4GjVvDnLgUk79T81voqjb8NnuUx8pVRCiEaYUSuynl'.
'jHU9mOfnOoOx6hqz8PrbNdfEkMUXg1LSM3rKOUywJ7YAJ1ZTWmSpvdvlaVTDSUzJAhH5ZJBgv0x2RSAPlz21WXqoet3ba9nuW8n4'.
'Jr6qTPqnUNxSM/f6mPvxA9zqJnExTbR+h0nkhVu1uE8j0UBRQ9PGxBKFjnkAScdsDp10a0lc7z0tI7Y5YYN+5GAf7GjVXF4Icj3f'.
'/9k=' ; 


//==========================================================
// ld-small.jpg
//==========================================================
$this->chars['d'][0]= 681 ;
$this->chars['d'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGAAAAwEBAAAAAAAAAAAAAAAAAAQFBgH/xAAsEAABAwMEAAQFBQAAAAAAAAAB'.
'AgMEBQYRABIhMQcTI0EUMlFhkRgicaGx/8QAFgEBAQEAAAAAAAAAAAAAAAAAAgEA/8QAGBEBAQEBAQAAAAAAAAAAAAAAAAECETH/'.
'2gAMAwEAAhEDEQA/ALUhp6h3W/X63UlypbhCY0WMjLqGzwDtPCfv/WtealNpVInuVBBqCogcdbU36YUkAkJWVHG8YPXBxxzxqPcN'.
'YtWyWnIlUeW05VEOAvrCnnSkftK1H5lKJPHsMDoDUWq+KdrSbIqsalVsImiEtLUZ2MU71bcYJWkhZ/36ayLHhi/IXZVOmzKqp5uU'.
'688hTyjuGVEFJKvoQesD86NL2jGZp1EoLDSmk+ZAQ8d7oPzp3YGesFWMfxo1YGvSzLsT9QExVX8phTlMaFOExAJIBGQjJwCcL+/e'.
'rd+W7GuO0Kw05CQ6+ww69Gfdb2kFIKk7DgEkjgnr86rXRa9HuyP8LV4SH0sIBbWFFDiFEgDaocgdkjo8ccay0qw7ut5nyrcviQqC'.
'slsRKo0HwlODkBRzxj2AGoXTtpzIdQ8MbffUChz4NCPRaClAo9Mn6c7T3o13wytmo0K05VIqkiPJbizFiMWs4CTgnIIHOST796NL'.
'Ia1JX//Z' ;

//==========================================================
// d8-small.jpg
//==========================================================
$this->chars['8'][0]= 694 ;
$this->chars['8'][1]= 
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
// lz-small.jpg
//==========================================================
$this->chars['z'][0]= 690 ;
$this->chars['z'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABYDASIAAhEBAxEB/8QAFwABAQEBAAAAAAAAAAAAAAAABgAHA//EACsQAAEDAwQBAwIHAAAAAAAAAAEC'.
'AwQFESEABhIxBxMiQVFxCCM0UmGRof/EABYBAQEBAAAAAAAAAAAAAAAAAAECAP/EABgRAAMBAQAAAAAAAAAAAAAAAAABEVEC/9oA'.
'DAMBAAIRAxEAPwBTWfLu1KXXZDbM4uewNvLajlwhaCbBAwDe5uehYd3xm6t6bi3jvulwqc7KgxXZZeYQLNLeF73WRg4HEdgfzrSa'.
'P45pNEkznITDc9ypLShtyWhJDJyXC2qxJHZvjoZOjyVv1v8AESt6FFS4ijxvTLbawEApSccrYHJf0+OtJMQ2rNXk7GZMufJgJjTH'.
'Un9M4qzxT7hyCiThIyRnPXWrRvyLElVBUF6vlhl0lwRYCFKcQhAtyWpVhyWTx+w++rUvp4EWjOvbniUOnVatcS43BYDbJSPZyIBw'.
'ejclIx+3Wa+J63T6DQanuGszI0eZVJJV60p0Jum5GEi6le7l0PjvSjyRsaTvJqI1BqhhR46ksuMrQVJcUSEoUbHNr/7o7C8L7eiz'.
'4lLlyJk2cEqW+6V+m0AE9ISLnsj5+O9UhsFK92bZZqb9SRu9p2c4A0OCEqDbYAJSlJwAVZv3fBvbFrg/462btlhuS1RG5nL8pYkq'.
'KrnsKH06I/rVrQKkf//Z' ;

//==========================================================
// d4-small.jpg
//==========================================================
$this->chars['4'][0]= 643 ;
$this->chars['4'][1]= 
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
// lv-small.jpg
//==========================================================
$this->chars['v'][0]= 648 ;
$this->chars['v'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABQDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAQDBQYH/8QAKBAAAQQBAwMEAgMAAAAAAAAA'.
'AQIDBBEFAAYhEzFBEhQiYQdRFTKB/8QAFgEBAQEAAAAAAAAAAAAAAAAAAAEC/8QAFxEBAQEBAAAAAAAAAAAAAAAAAAERIf/aAAwD'.
'AQACEQMRAD8A6Ngt1SZ4yrYgrecgTFsFJA9aGwAUrUaF2D2Avjzq6CIjiBPkB9bwQVIkIYIDae/wq+P9N+dY4SGMf+Txlev7KBmY'.
'PoadKRy4zxSgRxaTwO/x09u7KPYnasmHjlsyFZZXt4K23ezjvBpNGgLUrvXfVZyLLbWambiwEbKvvxYAkeotNlIJW2FEJWb7WBda'.
'NSQI0fHYyJjkrjKRDZQwnpQ1vgBIr+w8+a+9GocZr8iKkuY1eXhsKH8U8iZE9BHz6ZHUc48UfSPqzqH3kfeO9kTTDQYGGietpTaO'.
'shyW6AocpHNIrv8AvWzk9BUSdPdYS4BcRlomkhIV6KP0VE39V+tU2wdlRMHtZUB8NuTQ+51X27+Kr46ZPIAFV540D8zeLsJ5LMHa'.
'ubmMBCVJdjx0pRyLoWR4I8aNIQ8BvZMNtMTeUcsptKfc4tC1gAkCyFC+K0aJtf/Z' ;

//==========================================================
// lk-small.jpg
//==========================================================
$this->chars['k'][0]= 680 ;
$this->chars['k'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABUDASIAAhEBAxEB/8QAGQAAAwEBAQAAAAAAAAAAAAAAAAUGBAMH/8QALhAAAQMDAwIEBAcAAAAAAAAA'.
'AQIDBAUREgAGITFBEyIyYQcVUYEUIzNicZHx/8QAFgEBAQEAAAAAAAAAAAAAAAAAAwEE/8QAGxEAAwACAwAAAAAAAAAAAAAAAAEC'.
'AxESMeH/2gAMAwEAAhEDEQA/APVK/V36dU6NSJDTT8esPLiqfK8S2cCoeTkKvZQ6jm2ldSqKqbu+OgMOvSX3m4UBrLnDlbqiefKl'.
'Nzz2x1m+IwNP27CkJQ7JkR6rCkMJbP5jp8S2CPfkgD6H+dJ6Ca0nerr+64rTNSqMYrg+C9mmOwhVpDfsuxSbi97DmybaoZeQ5jTl'.
'PEp18JTIfeW3kq3ly4H26aNZqvTWZsjFcZTsVtSg0G8Rio+vr2vb7g6NLPRnuXy8F+8kl+obUh4KXJdqSJJQnohlkZqJPYBXh3P+'.
'a4b5Hyp6k1bO7sOotPyXkj9NlwFl0ewstJA9ifrqkVSmET4csoS7UTHXFQ+6SQlskKUMb/tH9ddLVUmS7DqdBqD7U6OsqfS46jzl'.
'hQ5bXb1K9Scuybdxo2OTu92dwSZkWn0Sb8viQWyn8Qq5D6ifSLd0BIv7q0arTBRSKPToMZbi2GWylsvLK148Wue/XRrRjxOpT2R2'.
'k9aP/9k=' ; 

//==========================================================
// lr-small.jpg
//==========================================================
$this->chars['r'][0]= 681 ;
$this->chars['r'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABYDASIAAhEBAxEB/8QAGgAAAgIDAAAAAAAAAAAAAAAAAAYCBQMEB//EAC4QAAICAQIFAgMJAQAAAAAA'.
'AAECAwQRBQYAEiExQQdRFGFxEyIyM0JSYoGC8P/EABYBAQEBAAAAAAAAAAAAAAAAAAEAAv/EABcRAQEBAQAAAAAAAAAAAAAAAAAB'.
'EUH/2gAMAwEAAhEDEQA/AOs0ZdETU54Gt1INSmlPJEsyo7J+jlXPUYBPY9c+eE/dO9tY0a7ren6BVrW7VJTZtW5kZkjXkBSIKveQ'.
'gHp0AAJ4w+q2hVdT2Md0h46+saS4mr3EUK0gWTAB+vQj2PboeL/ZVOqmhaZVjkFmxdC6tctt3tM2G5/7bAx4C4+qxiWwd3prWzKe'.
'r3IBAth5OYxozKsgc8y4GTgnJB9uncdTi6tXq2140rRVM13JMEMAVAg7sMdBjJB/18uDgRO9R2Oo6FX2vShkFzURFUq1whIj+8DI'.
'7EdAFjXv7MeNb0kuStsFEmIaajZaos2fy2Q4VGH7SGxn+Rzw9yMLOm/FzRhZazmOTkP4grYyD3B8j2PTyeFfZ+z7G3BeSS8lmprl'.
'2K2qcnK0Z5S8gPjrgAY8cNEWmq7u23pEos6/Zji+Kd0rLLGWwseA3joeZj/w4OET1g0vlmrWV+ydFnkUxSgsvM4V+YYIwfHz6cHB'.
'ZeKZ1//Z' ; 

//==========================================================
// lg-small.jpg
//==========================================================
$this->chars['g'][0]= 655 ;
$this->chars['g'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABQDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAQCBQYH/8QAJxAAAQQBAwQCAgMAAAAAAAAA'.
'AQIDBBEFAAYhBxIxQRNhcYEiQlH/xAAYAQACAwAAAAAAAAAAAAAAAAACAwABBP/EABkRAAMBAQEAAAAAAAAAAAAAAAABAhEhIv/a'.
'AAwDAQACEQMRAD8AayO4t6bq3hmMHtxyLi4OKeKH5jyASiiQCCQeTRNAeB61FrBb+jTGpLO+BMW24EFMhkhpQru8m7B/H70x09Yi'.
'q3nv/vLfwpnJ7UNkqSRbngf2ofWkpXV7brymC2malLfagurjW0aHk89xPJ9cX9aprURHWbYEaMHHEBfwpv8AnXPk+/8AdGqGJOxO'.
'4YbOSxK4y4boIStUWysgkEmxY54r60aOI8oTV9MHtjJwunPUbO46WWo0HLlD8KY4goboFVoquOVEVwLT963WdnxYfT6ZJyz0JvHm'.
'KvtaSkW4tYNVSqKiTwB+fw5n9sY/cuOXCzDDcluyW3Ckd7V+0n0eNZTH9DdouFalHIOJBUhtDki0pNV3UALo81ehG6IdKjPZ6d47'.
'4ywltanVJvuJI+RQs/sHRqy2r003JhsImEc/CUyhxRZBjKV2oJ8eRXNmufPnRo1WIz3DdNn/2Q==' ;

//==========================================================
// lc-small.jpg
//==========================================================
$this->chars['c'][0]= 629 ;
$this->chars['c'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGQAAAwEBAQAAAAAAAAAAAAAAAAUGBwID/8QALRAAAgICAQIEBAYDAAAAAAAA'.
'AQIDBAURACExBhIiQRMVUWEHMkJScYFykaH/xAAWAQEBAQAAAAAAAAAAAAAAAAABAgP/xAAXEQEBAQEAAAAAAAAAAAAAAAAAATER'.
'/9oADAMBAAIRAxEAPwDcoGkmiT4Q8kWvzuPU38D2/v8A1zwrCFayq1qTaFk2H7aJHt05MeMvENzC4upDWkjW9kJXiricAJCigvJN'.
'IB1IVQT5frrv24twPgunk6a288crbklUSJNNdnSTZ2STHHqOP/Eb17njdZtAoqwEvrEiGVyG117/AG6HhyV8H1sljMldoxXTksGC'.
'zV7M0oaWGQOVeGQ92I6EMR22D11w4LmEPjaOL51iL8ssc9Z69zHtZkYCGGeQK0ez2UEoU39wCeX1S/LLiEt+mPSbMLxsGVv2kEjR'.
'305xkaEV/GTULMUT1LD/AAGh8gIZS2jv+vpybb8NMIb0dVLWYWgiiU0vmMphOj6V0TvQI3rfsON1E6dYjGtisa0F1mAWR2NhG0WZ'.
'3Ls3TqNs5Hc9h23w49NWL9K+Q/VD5T/zhwPH/9k=' ; 

//==========================================================
// d7-small.jpg
//==========================================================
$this->chars['7'][0]= 658 ;
$this->chars['7'][1]= 
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
// ly-small.jpg
//==========================================================
$this->chars['y'][0]= 672 ;
$this->chars['y'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABQDASIAAhEBAxEB/8QAGAAAAwEBAAAAAAAAAAAAAAAAAAQGBQf/xAArEAABAwMEAQIFBQAAAAAAAAAB'.
'AgMEBREhAAYSEzEHIhQkQVGxQmFxgaH/xAAWAQEBAQAAAAAAAAAAAAAAAAADAQL/xAAeEQEAAgEEAwAAAAAAAAAAAAABABECAxIh'.
'MUGR8P/aAAwDAQACEQMRAD8Ar3tys07dVHohemz5dWQ7fk91MsA3IIRY8rkKFySceTqw3JVV0KhyKw+0C1CQp9aUOFSiAk4AIAvn'.
'76xtz0ioVvbcJ6msx2JtOfZmw1PKI5LQcJNh7UqBKcn6+NRfqPu6s1fYc6GxSJsRfWDUVSGA22ygEckJWSexRNgOP0udXzDKOJ0I'.
'yo62mHm25Sy80l1Z4lSgpQvZRGLgWwPGjTjbchyLH+Ejx22EtJSgO8kki3kADA/nOjWjGzv73CyQZjUWNVp7bNSrj7qJDqflqUlQ'.
'DMds24l3HvcNr3Pi9gME6T9WWVsemdYWswwC2lPta4m5WMA3OdUExCmozUJD6g84ntMjrHIFBTdQz5yLDx/WDNytpwW6nAkViqVe'.
'uvmXdlme6n4dCwlRBKEgA2tj99QG7Ilncp5QqpU31PMsJ6x7A32f6SPxo0hPVCD45oVyKf0MtgeT97/nRrO7UOCFla3tn//Z' ; 

//==========================================================
// d3-small.jpg
//==========================================================
$this->chars['3'][0]= 662 ;
$this->chars['3'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD//gAJSnBHcmFwaP/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsMGRITDxQdGh8eHRocHCAkLicg'.
'IiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0NGDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjIyMjIyMjIyMjIyMv/AABEIAB4AEgMBIgACEQEDEQH/xAAZAAACAwEAAAAAAAAAAAAAAAAABAUGBwL/xAArEAABBAED'.
'AwMDBQEAAAAAAAABAgMEBREABhIhMUEiMmETFZEHFkJDUdH/xAAWAQEBAQAAAAAAAAAAAAAAAAABAAL/xAAYEQEBAQEBAAAAAAAA'.
'AAAAAAAAEQExQf/aAAwDAQACEQMRAD8A0vclruBdk3VVLLUNssGRJsZSCtqOjlgJAHvcOD6c4HnOdIbcttw1W5P29cFEhuawqTXS'.
'VsJjnCMBxKkJJx7goAde+ceJfdNxU0UNlyymyXHi6kxWUNl1S3EnkAEIHX2nv86qtTuZr9Q9+1VhRsOoYpYcgSVyAE/TdewkJxnK'.
'sBCjkdPGpnOtFMd3PqsXgfOAgD8Y0aX+11H9rDDjn8lr9yj5J+dGqsqxaw6Cc9cQZU4Sp7zTJsIrKlcUEKwhSin1JABI45GUjqOu'.
'lbOvjbc3Ts9ynjGCy445UuFLYRzbWgrT6fhSCQSMDke+pew2zYVly/d7YchNqkMJZnQpgV9J8IzwWFJyUrAJHYgjvpLbu37G5nR7'.
'vck5C3YRKYEOEVJZj8kjKypXqWvirjk9h+dB9i4faa89TDZUfKlIyT8k+To10a6KTkpcJ/0vL/7o0TS//9k=' ; 

//==========================================================
// ln-small.jpg
//==========================================================
$this->chars['n'][0]= 643 ;
$this->chars['n'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABQDASIAAhEBAxEB/8QAGwAAAgEFAAAAAAAAAAAAAAAAAAYCAQMEBQf/xAAtEAACAQMCBAUCBwAAAAAA'.
'AAABAgMEBREAIQYSE0EHIjFRcWGRIzIzQoGCwf/EABYBAQEBAAAAAAAAAAAAAAAAAAMEAP/EABkRAQEBAQEBAAAAAAAAAAAAAAEA'.
'AhEhUf/aAAwDAQACEQMRAD8A6FR3p7v4oV9rlkMQsjL00RyOss0KkFxnDcrc2PbI1NOJKyTjW+W5OmKeA0UEJx5meRZS2/8AUfbS'.
'LVGS1+K16vCzfiR3GmoqqXGyxz06hWPsFlVMfOmq1iNvE69KjBYo3oJMZ3GKeYYPxg/fW+xzZX1FLQyxwSTcpWNceu4G3+aNSmpY'.
'qmQzzwh2k8yhv2r2H23/AJ0aoy+EWh7I1ntacR3PxDtEzhjWy0wkkIwYmanU5GO6sNh7rrU8AVdTceNbhDXxNHUQvS0tZ3DzwxVA'.
'fB7hj59/XJ08cPWaKj4gvlwSQiG7dCboqvLy9NOmQT9SM7ayJrBa6K5V91hjlWorp4JGUOAglRSiMMDb82/vgaBGTpVvtNUVtyJg'.
'5+WNAh5ZCu/r2+dGrgq0pi0DhmlRsSSAfqMd+b6ZyNu3po1Rk1yNBe3/2Q==' ; 

//==========================================================
// lu-small.jpg
//==========================================================
$this->chars['u'][0]= 671 ;
$this->chars['u'][1]= 
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAYDBAUH/8QAJRAAAQQBAwQDAQEAAAAAAAAA'.
'AQIDBBEFAAYhBxMxYRJBURSB/8QAFgEBAQEAAAAAAAAAAAAAAAAAAQAD/8QAGhEBAQEAAwEAAAAAAAAAAAAAAQARITFBAv/aAAwD'.
'AQACEQMRAD8A6dLkQmJzu3WVtHIqjf0duKFNuBr5UTQ45F1R8/XI1PMmsYoJyjhS9iI7BKHeKjkXZVXqhyLHP+rrHeR1pZlx1W1M'.
'wTiW0ukkrS28nn5fV2SPPFfurHUKQhzYG7pLYKEfyBhaSOS7dG/YCki/uvWn3LPDOJrwa4kyEzOYeakqkpC3Hk0bNePQHgDRpchY'.
'leIZwzUWauKtuPctTSUlCAUmrBHIKuAPV/ujQsmHdm7hya43UbbD3ZVElOQJsdTS6IQaQUqBHCk8E2Pocgam6oYwObHy0Zm0oi45'.
'T1KBPdpV2f0pom/1Ws7cmPazu98Ltvcq3VzRHfehz8a4pirFEKRZo8eQT+eCdWYfS/b+WYnxpbuVcDRMdHcyTqg2fiAfiLoi+Rf+'.
'jT7Xc74HtOYnHyUOh8yWUvKeHhy0CiPVUAPoDRrm+OeznTva6lzsyMjCYbbaiNJjJSWElagD5tRpNUSALFeNGoOCH7Bv/9k=' ; 

//==========================================================
// lw-small.jpg
//==========================================================
$this->chars['w'][0]= 673 ;
$this->chars['w'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABcDASIAAhEBAxEB/8QAGAAAAgMAAAAAAAAAAAAAAAAAAAYDBAX/xAAtEAACAQMDAgMHBQAAAAAAAAAB'.
'AgMEBREABhIhMRMUQRUiIzJRYZEWNIGx0f/EABYBAQEBAAAAAAAAAAAAAAAAAAABA//EABoRAAICAwAAAAAAAAAAAAAAAAABERIh'.
'MVH/2gAMAwEAAhEDEQA/AHXbV13ZLu6t2/uaa1JijWopVp4XUTKSAXRyc+6ehBGeoPbTSlwpql0K3GneqpZViqUhI5JzGMEZJGeh'.
'GlXfaFILDf7FQzXC426rDLTojs8sLqVkXBGcfKf40twWbdWzZY75R0s90ul3jPtKjVMJDNn4DDp8iEhW+wJ1WZG2KWt3Lv26U1tv'.
'92o7PaYkgYUbqVepYlmUBlIwqnB++O2jTDt/bBtth9jcpvEWNGqalZQryTlmeR8jPct6+mNGmRC4a1U13htzVFItB5nA/cyOUVfp'.
'7oz/ALqitJulYJKuqvFsppHALLFb3cp9FBaXr+O51bq0q6i38KK5PDVAAxSzU6SIpz3Kjjn8jUFoS7uFmut1gq17xLFQ+DxOccj8'.
'Rsn+tVpiyJnqv09YfOXu5AycgZZQEhBZjgDBOOgwO/po0sttWHdNzqLruioa4UwmdaC3kYp4IwSvJlBHKQ4OSe3po0qxM6P/2Q==' ;

//==========================================================
// lq-small.jpg
//==========================================================
$this->chars['q'][0]= 671 ;
$this->chars['q'][1]=
'/9j/4AAQSkZJRgABAQEASgBKAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
'MjIyMjIyMjL/wAARCAAeABQDASIAAhEBAxEB/8QAGQAAAgMBAAAAAAAAAAAAAAAAAAcDBAUG/8QAKRAAAQQBBAICAQQDAAAAAAAA'.
'AQIDBBEFAAYSIQcxIlETCBQVgSNBYf/EABUBAQEAAAAAAAAAAAAAAAAAAAAB/8QAFhEBAQEAAAAAAAAAAAAAAAAAAAER/9oADAMB'.
'AAIRAxEAPwDT3H5Qz+O3LN2vtrF/y86NYLzzVlAABJITQPv2a/17vXMboz3lDEYWPuafNx7CFrS03+2jpK2bs0CUkUa7pRvrUu63'.
'sr438yv7pLEo4XIK5Kcji0uJUkckm+uQUOVH6GsnyJv7A5vaJwuFdkONLmolgONFH4vioKRXYqyCADXvRMh0yspmZ4jyIEtDTK47'.
'aiA0lQUopBJBI/7X9aNT7amRo228e3a31iO3yUzCcdSPiKAIFdCho0TIswZ7GQlO/hlRxBooih1YXzAoKUkX0LPEBX110dJ7zbuv'.
'AORpO04cIpmxH23FSEIRwKuNnsdk0o31702XhFMKbuRUZJWP8LTQ6HBCuIB+iVWSR2BXuqK93/hDlvGzEphmG3Ml5JpDi1I7TzNA'.
'BYFlPafY+/7LBiv1CYDH4iFDOGySlMR22lFP4wCUpANfL11o1r4bxXlWMNEaE/bqlIbCFl/ANPK5Do/M0VDr2Rf3o0TX/9k=' ;



    } 
}

class AntiSpam {

    private $iData='';
    private $iDD=null;

    function AntiSpam($aData='') {
	$this->iData = $aData;
	$this->iDD = new HandDigits();	
    }

    function Set($aData) {
	$this->iData = $aData;
    }

    function Rand($aLen) {
	$d='';
	for($i=0; $i < $aLen; ++$i) {
	    if( rand(0,9) < 6 ) {
		// Digits
		$d .= chr( ord('1') + rand(0,8) );
	    }
	    else {
		// Letters
		do {
		    $offset = rand(0,25);
		} while ( $offset==14 );
		$d .= chr( ord('a') + $offset );
	    }
	}
	$this->iData = $d;
	return $d;
    }

    function Stroke() {

	$n=strlen($this->iData);
	if( $n==0 ) {
	    return false;
	}

	for($i=0; $i < $n; ++$i ) {
	    if( $this->iData[$i]==='0' || strtolower($this->iData[$i])==='o') {
		return false;
	    }
	}

	$img = @imagecreatetruecolor($n*$this->iDD->iWidth, $this->iDD->iHeight);
	if( $img < 1 ) {
	    return false;
	}

	$start=0;
	for($i=0; $i < $n; ++$i ) {
	    $dimg = imagecreatefromstring(base64_decode($this->iDD->chars[strtolower($this->iData[$i])][1]));
	    imagecopy($img,$dimg,$start,0,0,0,imagesx($dimg), $this->iDD->iHeight);
	    $start += imagesx($dimg);
	}
	$resimg = @imagecreatetruecolor($start+4, $this->iDD->iHeight+4);
	if( $resimg < 1 ) {
	    return false;
	}

	imagecopy($resimg,$img,2,2,0,0,$start, $this->iDD->iHeight);
	header("Content-type: image/jpeg");
	imagejpeg($resimg);
	return true;
    }
}

?>
