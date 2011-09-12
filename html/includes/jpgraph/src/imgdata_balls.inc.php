<?php
//=======================================================================
// File:        IMGDATA_ROUNDBALLS.INC
// Description: Base64 encoded images for small round markers
// Created:     2003-03-20
// Ver:         $Id: imgdata_balls.inc.php 1106 2009-02-22 20:16:35Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

class ImgData_Balls extends ImgData {
    protected $name = 'Round Balls';
    protected $an = array(MARK_IMG_LBALL => 'imgdata_large',
    MARK_IMG_MBALL => 'imgdata_small',
    MARK_IMG_SBALL => 'imgdata_xsmall',
    MARK_IMG_BALL => 'imgdata_xsmall');
    protected $colors,$index,$maxidx;
    private $colors_1 = array('blue','lightblue','brown','darkgreen',
         'green','purple','red','gray','yellow','silver','gray');
    private $index_1  = array('blue'=>9,'lightblue'=>1,'brown'=>6,'darkgreen'=>7,
         'green'=>8,'purple'=>4,'red'=>0,'gray'=>5,'silver'=>3,'yellow'=>2);
    private $maxidx_1 = 9 ;

    private $colors_2 = array('blue','bluegreen','brown','cyan',
     'darkgray','greengray','gray','green',
     'greenblue','lightblue','lightred',
     'purple','red','white','yellow');
     

    private $index_2 =  array('blue'=>9,'bluegreen'=>13,'brown'=>8,'cyan'=>12,
     'darkgray'=>5,'greengray'=>6,'gray'=>2,'green'=>10,
     'greenblue'=>3,'lightblue'=>1,'lightred'=>14,
     'purple'=>7,'red'=>0,'white'=>11,'yellow'=>4);
     
    private $maxidx_2 = 14 ;


    private $colors_3 = array('bluegreen','cyan','darkgray','greengray',
     'gray','graypurple','green','greenblue','lightblue',
     'lightred','navy','orange','purple','red','yellow');

    private $index_3 = array('bluegreen'=>1,'cyan'=>11,'darkgray'=>14,'greengray'=>10,
    'gray'=>3,'graypurple'=>4,'green'=>9,'greenblue'=>7,
    'lightblue'=>13,'lightred'=>0,'navy'=>2,'orange'=>12,
    'purple'=>8,'red'=>5,'yellow'=>6);
    private $maxidx_3 = 14 ;

    protected $imgdata_large, $imgdata_small, $imgdata_xsmall ;


    function GetImg($aMark,$aIdx) {
        switch( $aMark ) {
            case MARK_IMG_SBALL:
            case MARK_IMG_BALL:
                $this->colors = $this->colors_3;
                $this->index = $this->index_3 ;
                $this->maxidx = $this->maxidx_3 ;
                break;
            case MARK_IMG_MBALL:
                $this->colors = $this->colors_2;
                $this->index = $this->index_2 ;
                $this->maxidx = $this->maxidx_2 ;
                break;
            default:
                $this->colors = $this->colors_1;
                $this->index = $this->index_1 ;
                $this->maxidx = $this->maxidx_1 ;
                break;
        }
        return parent::GetImg($aMark,$aIdx);
    }

    function __construct() {

        //==========================================================
        // File: bl_red.png
        //==========================================================
        $this->imgdata_large[0][0]= 1072 ;
        $this->imgdata_large[0][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAMAAACelLz8AAAByF'.
     'BMVEX/////////xsb/vb3/lIz/hIT/e3v/c3P/c2v/a2v/Y2P/'.
     'UlL/Skr/SkL/Qjn/MTH/MSn/KSn/ISH/IRj/GBj/GBD/EBD/EA'.
     'j/CAj/CAD/AAD3QkL3MTH3KSn3KSH3GBj3EBD3CAj3AAD1zMzv'.
     'QkLvISHvIRjvGBjvEBDvEAjvAADnUlLnSkrnMTnnKSnnIRjnGB'.
     'DnEBDnCAjnAADec3PeSkreISHeGBjeGBDeEAjWhITWa2vWUlLW'.
     'SkrWISnWGBjWEBDWEAjWCAjWAADOnp7Oa2vOGCHOGBjOGBDOEB'.
     'DOCAjOAADJrq7Gt7fGGBjGEBDGCAjGAADEpKS/v7+9QkK9GBC9'.
     'EBC9CAi9AAC1e3u1a2u1Skq1KSm1EBC1CAi1AACtEBCtCBCtCA'.
     'itAACngYGlCAilAACghIScOTmcCAicAACYgYGUGAiUCAiUAAiU'.
     'AACMKSmMEACMAACEa2uEGAiEAAB7GBh7CAB7AABzOTlzGBBzCA'.
     'BzAABrSkprOTlrGBhrAABjOTljAABaQkJaOTlaCABaAABSKSlS'.
     'GBhSAABKKSlKGBhKAABCGBhCCABCAAA5CAA5AAAxCAAxAAApCA'.
     'ApAAAhAAAYAACc9eRyAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgF'.
     'HUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkRFD'.
     'UHLytKAAAB4UlEQVR4nGNgIAK4mGjrmNq6BmFIWMmISUpKSmk5'.
     'B8ZEokj4qoiLiQCBgqald3xaBpKMj6y4sLCQkJCIvIaFV0RaUR'.
     'lCSk5cWEiAn19ASN7QwisuraihHiajKyEixM/NwckjoKrvEACU'.
     'qumpg7pAUlREiJdNmZmLT9/cMzwps7Smc3I2WEpGUkxYkJuFiY'.
     'lTxszePzY1v7Shc2oX2D+K4iLCgjzsrOw8embuYUmZeTVtPVOn'.
     'gqSslYAOF+Ln4ZHWtXMPTcjMrWno7J82rRgoZWOsqaCgrqaqqm'.
     'fn5peQmlsK1DR52vRaoFSIs5GRoYG5ub27n19CYm5pdVPnxKnT'.
     'pjWDpLydnZwcHTz8QxMSEnJLgDL9U6dNnQ6Sio4PDAgICA+PTU'.
     'zNzSkph8hADIxKS46Pj0tKTc3MLSksqWrtmQySAjuDIT8rKy0r'.
     'Kz+vtLSmur6jb9JUIJgGdjxDQUVRUVFpaUVNQ1NrZ9+kKVOmTZ'.
     'k6vR0sldJUAwQNTU2dnX0TgOJTQLrSIYFY2dPW1NbW2TNxwtQp'.
     'U6ZMmjJt2rRGWNB3TO7vnzh5MsgSoB6gy7sREdY7bRrQEDAGOb'.
     'wXOQW0TJsOEpwClmxBTTbZ7UDVIPkp7dkYaYqhuLa5trYYUxwL'.
     'AADzm6uekAAcXAAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bl_bluegreen.png
        //==========================================================
        $this->imgdata_large[1][0]= 1368 ;
        $this->imgdata_large[1][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAABm'.
     'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
     'B3RJTUUH0wMMFi8hE9b2uAAABOVJREFUeNq9lk2sJFUVx3+3qv'.
     'tW95t57zFvhiFxmCFRUJRoNCQiJARMhiFx/Igxii5goTG6ZDAu'.
     '/EhcSCIrTAgLEiKsJ8ywABNZEMJXEDYCukAmjgjzBkK/j35V1d'.
     '333FtV97io97pfzwxfG86qcu/N+Z3zP+fcW/Apmfk4hx57+R/6'.
     'Rqmc9ykhsWjlsUngAA1fXIQ7b73pI/186IGHnn9dH/8frC8v4I'.
     'PiG53uaerR4GmKkv31mB8cyfjd946ZTwR66qVX9OTWIi8UKUv9'.
     'BOrZXpYZvFeiBvzI0fgSUSFKwbVG+Pl1V3HH0VvNR4KeeukV/f'.
     'PmMmdHhst76aXD64AbeVQ9bjNHaiGOC2o3wLrAb2/4LL/84ffn'.
     'fCdzkOdayKpLppBemrBsU5Y1Zdmm9LJdGU6E/t4M24Q26jRDRL'.
     'j3mdc49cSTekFsMzs5XuTsyLDUNSDQ25NwKOly9YIl22MYhJr/'.
     'uoDtBBoT0CxBRGYOAhibIaOCe//2MpfM6KHnX9cXipSlbkKWmS'.
     'nk9iv38J0jixw7vJfrTMYBOvhSoQHJBS09ANELloAGDxW8tfoW'.
     'J+5/UC8CPS0LU7r3SpYarr7M8rmFjMPLXT6/33L4si7Z2GCrQC'.
     '+0ctlOaNs9DReV8vSLr85ndPLpZ/WNvHW+01kAVFBOGvJx0wYg'.
     'Sp47RIQ4Emwa8FGJXlDxSCFo5YlVgAo2hwPue/hRndboTV3EW2'.
     'Wp3k6wBp8q56QiWzecW6vwQfnPRkAWhFgILnq08jQ+R2nlUzzN'.
     'uES9Q7Vd+9fba7NmWJW61db2247qACmcjxXr45psYphsFGSLBu'.
     'kIajxqtjNwHkvAjQt0sg3crhPA2+fPz0CuyNFOghsGsr19mnFg'.
     'DGwrRm8UoAtNmQPQtRXDgdC4HImCFEKcCE0oieUWUYq2LtbiGp'.
     'mBQmppfIkjw45DK0QNNkvQ0jMBtPL0UnDRM1rN+cxKwzvOo2NP'.
     'tykR9a1kfpZNDLMG6QDYJqCTBvUe1+uxs+YKyPoGrTwY2HhvC4'.
     'CDWQd5d4xNApNQEEMgjgLdUCLBQ5cprL/trwNwKG2IUmDqDFd5'.
     'sr5BWrlxuSdLDFEFlqAzXGc4zFjupqh6uqYihpxJcEgp026l2w'.
     '7wFUv7Z6AvrfRo/n0OYzPwIKE3HUKAJg2otMBiElnsF7wngis9'.
     '3ZDjNnLi7huCWUZfueZKTu/M0V3HvmkOFDVxVKDG04ScejSgW5'.
     'V0q5JYFEghuDLHlTmToqDeGOCKIVtrW9hsdmXufEcNLPSXuPHa'.
     'a+bvuh9df5AH/v5PDFmbWQC3Mx+TVvfGVTRB2CodNgT2JBX003'.
     'aANZAYS/BxCv32TV/l2C03G7jgmfjGiT/qmeEmibEYm7XzAO2k'.
     'A+pbgHhBgydqu54YO5eRiLCy7yDvPP6Xqf+5Z+Lu277OYuOpiw'.
     'H15oBmlNOMcmK5RbP+PrEscGU+DSAxdg4CICIkxnLP8aNz63Og'.
     'H3/rdvOb795GVhuaYo0oBc3GGrEsUPVTwO6a7LYd+X51x3Hu/t'.
     'lP5tS65FN+6okn9U+n/sqb596dTvhOF+02myXTmkQNrOw7yD3H'.
     'j14E+UDQjp24/0E9/eKrbA4HH3aMK1b2ccvXvswjv//1J/s5ud'.
     'Due/hRPfP+OmfOrk7vrn7a48ihA3zh8CH+8Iuffiw/n4r9H1ZZ'.
     '0zz7G56hAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bl_yellow.png
        //==========================================================
        $this->imgdata_large[2][0]= 1101 ;
        $this->imgdata_large[2][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAMAAACelLz8AAAB2l'.
     'BMVEX//////////+///+f//9b//8b//73//7X//63//6X//5T/'.
     '/4z//4T//3P//2v//1r//0r//0L//zH//yn//yH//xj//xD//w'.
     'j//wD/90L/9zn/9zH/9xj/9xD/9wj/9wD39yn37zn37zH37yH3'.
     '7xD37wj37wDv70Lv50rv50Lv5znv5yHv5xjv5wjv5wDn51Ln5x'.
     'Dn3jHn3iHn3hjn3hDn3gje3oze3nPe3lLe1oze1nPe1lLe1ine'.
     '1iHe1hje1hDe1gje1gDW1qXW1mvWzqXWzkLWzhjWzhDWzgjWzg'.
     'DOzrXOzq3OzpzOzgDOxkrOxinOxhjOxhDOxgjOxgDGxqXGxnvG'.
     'xmvGvRjGvRDGvQjGvQDFxbnAvr6/v7+9vaW9vZS9vQi9vQC9tR'.
     'C9tQi9tQC7u7W1tZS1tXu1tTG1tQi1rRC1rQi1rQCtrYytrSGt'.
     'rQitrQCtpYStpSGtpQitpQClpYSlpXulpQClnBClnAilnACcnG'.
     'ucnAicnACclAiclACUlFqUlCmUlAiUlACUjFKUjAiUjACMjFKM'.
     'jEqMjACMhACEhACEewB7ezF7exB7ewB7cwBzcylzcwBzaxBzaw'.
     'BraxhrawhrawBrYxBrYwBjYwBjWgBaWgBaUgCXBwRMAAAAAXRS'.
     'TlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAd'.
     'LdfvwAAAAHdElNRQfTAwkRFBKiJZ4hAAAB7ElEQVR4nI3S+1vS'.
     'UBgHcB67WJmIMWAVdDHEDLBC6Go0slj3Ft0m9RRBWQEmFZFDEM'.
     'Qgt0EMFBY7p/+198hj1kM/9N1+++x73rOd6XT/kStnTx4fPzd9'.
     'uwfOjFhomj7smAhwj/6Cm2O0xUwy6g7cCL99uCW3jtBmE7lsdr'.
     'fvejgpzP7uEDFRRoqy2k8xQPnypo2BUMP6waF9Vpf3ciiSzErL'.
     'XTkPc0zDe3bsHDAcc00yoVgqL3UWN2iENpspff+2vn6D0+NnZ9'.
     '6lC5K6RuSqBTZn1O/a3rd7v/MSez+WyIpVFX8GuuCA9SjD4N6B'.
     'oRNTfo5PCAVR0fBXoIuOQzab1XjwwNHx00GOj8/nKtV1DdeArk'.
     '24R+0ul9PjmbrHPYl+EipyU0OoQSjg8/m83kl/MMhx0fjCkqio'.
     'SMOE7t4JMAzDsizH81AqSdW2hroLPg4/CEF4PhKNx98vlevrbY'.
     'QQXgV6kXwVfjkTiSXmhYVcSa7DIE1DOENe7GM6lUym0l+EXKks'.
     'K20VAeH2M0JvVgrZfL5Qqkiy0lRVaMBd7H7EZUmsiJJcrTdVja'.
     'wGpdbTLj3/3qwrUOjAfGgg4LnNA5tdQx14Hm00QFBm65hfNzAm'.
     '+yIFhFtzuj+z2MI/MQn6Uez5pz4Ua41G7VumB/6RX4zMr1TKBr'.
     'SXAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bl_silver.png
        //==========================================================
        $this->imgdata_large[3][0]= 1481 ;
        $this->imgdata_large[3][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAMAAAAM7l6QAAADAF'.
     'BMVEUAAADOzs7Gxsa9vb21tbXOxsbOzsbGzsb3///O1ta1vb2c'.
     'paVSWlpKWlpSY2ve5+97hIze7/9aY2vO5/9zhJRaa3tSY3PGzt'.
     'aMlJxrc3tja3NKUlpCSlK1vcZze4RSWmPW5/+Upb3G3v9zhJxS'.
     'Y3t7jKVaa4TO3veltc6ElK1re5Rjc4ycpbV7hJRaY3M5QlLn7/'.
     '/Gzt6lrb2EjJzO3v9ja3vG1ve9zu+1xueltdacrc6UpcaMnL1C'.
     'SlqElLV7jK1zhKVre5zW3u/O1ue1vc6ttcaMlKVze4xrc4RSWm'.
     'tKUmPG1v+9zve1xu+tveeltd6crdbe5/+9xt6cpb17hJxaY3s5'.
     'QlrW3vfO1u/Gzue1vdattc6lrcaUnLWMlK2EjKVze5Rrc4xja4'.
     'RSWnNKUmtCSmO9xuecpcZ7hKVaY4TW3v/O1vfGzu+1vd6ttdal'.
     'rc69xu+UnL2MlLWEjK1ze5xrc5R7hK1ja4zO1v+1veettd6lrd'.
     'aMlL3Gzv/39//W1t7Gxs61tb29vcatrbWlpa2cnKWUlJyEhIx7'.
     'e4TW1ufGxta1tcZSUlqcnK3W1u+UlKW9vda1tc57e4ytrcalpb'.
     '1ra3vOzu9jY3OUlK29vd6MjKWEhJxaWmtSUmNzc4xKSlpjY3tK'.
     'SmNCQlqUjJzOxs7///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
     'AAAAAAAAAAAAAAAAAAAAAAAAD///9fnkWVAAAAAnRSTlP/AOW3'.
     'MEoAAAABYktHRP+lB/LFAAAACXBIWXMAAABFAAAARQBP+QatAA'.
     'AB/klEQVR42mNgxAsYqCdd3+lcb4hLmj8wMMvEu8DCMqYbU9op'.
     'UEFB2MTb26eyysomFl06XEEhUCHLpAKo2z/fujikEUVaXUFBMB'.
     'BouLePuV+VVWGRciIXknSEsImCQd3//xwmPr65llaFcSFJHkjS'.
     '3iYmWUDZ//8NfCr989NjNUMSUyTg0jneSiaCINn/gmlVQM12qg'.
     'lJnp5waTMTE5NAkCyHWZW/lXWNfUlikmdYK0zax7siS4EDKJtd'.
     'mQeU1XRwLBdLkRGASucWmGVnZ4dnhZvn5lmm29iVOWpnJqcuko'.
     'JKR1Wm5eTkRKYF5eblp9sU2ZeUJiV7zbfVg0pH56UFBQXNjIqK'.
     'jgkujItX1koKTVmYajsdKu2qETVhwgSXiUDZ2Bn9xqUeoZ5e0t'.
     'LzYYZ3B092ndjtOnmKTmycW1s7SHa+l5dtB8zlccE6RlN0dGbM'.
     'mDVbd5KupNBcL6+F82XgHouLj5vRP2PWLGNdd4+ppnxe8tJec6'.
     'XnNsKkm0uVQ5RDRHQTPTym68nPlZbvkfYCexsa5rpJ2qXa5Umm'.
     'ocmec3m8vHjmSs+fgxyhC5JDQ8WSPT2lvbzm8vDIe0nbtiBLN8'.
     '8BigNdu1B6Lsje+fPbUFMLi5TMfGmvHi/puUAv23q2YCTFNqH5'.
     'MvPnSwPh3HasCbm3XUpv+nS5VtrkEkwAANSTpGHdye9PAAAASn'.
     'RFWHRzaWduYXR1cmUANGJkODkyYmE4MWZhNTk4MTIyNDJjNjUx'.
     'NzZhY2UxMDAzOGFhZjdhZWIyNzliNTM2ZGFmZDlkM2RiNDU3Zm'.
     'NlNT9CliMAAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bl_purple.png
        //==========================================================
        $this->imgdata_large[4][0]= 1149 ;
        $this->imgdata_large[4][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAMAAACelLz8AAACAV'.
     'BMVEX/////////7///5///1v//xv//rf//pf//lP//jP//hP//'.
     'c///a///Wv//Wvf/Uv//Sv//Qv//Qvf/Off/Mf//Kf//If//If'.
     'f/GP//GPf/EP//EPf/CP//CPf/CO//AP//APf3Oe/3Kff3Ke/3'.
     'Ie/3GO/3EO/3AO/vSu/vSufvOefvMefvIefvGOfvEOfvCOfvAO'.
     'fnUufnSufnMd7nId7nGN7nGNbnEN7nCN7nAN7ejN7ejNbec97e'.
     'c9beUtbeQtbeIdbeGNbeENbeCNbeANbWpdbWa9bWQs7WGM7WEM'.
     '7WCM7WAM7Otc7Orc7OnM7OSsbOIb3OGMbOEMbOCMbOAM7OAMbG'.
     'pcbGnMbGe8bGa8bGKbXGEL3GCL3GAL3FucXBu73AvsC/v7+9pb'.
     '29Ka29GLW9ELW9CLW9AL29ALW5rrm1lLW1e7W1MbW1GKW1EK21'.
     'CLW1CK21AK2tjK2thKWtMaWtIaWtGJytCK2tCKWtAK2tAKWlhK'.
     'Wle6WlEJylCJylAKWlAJyca5ycGJScEJScCJScAJycAJSUWpSU'.
     'UoyUKZSUEIyUCIyUAJSUAIyMUoyMSoyMIYSMEISMCISMAIyMAI'.
     'SECHuEAISEAHt7MXt7EHt7CHt7AHt7AHNzKXNzEGtzAHNzAGtr'.
     'GGtrEGNrCGtrAGtrAGNjCFpjAGNjAFpaAFpaAFIpZn4bAAAAAX'.
     'RSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsS'.
     'AdLdfvwAAAAHdElNRQfTAwkRFB0ymoOwAAAB9UlEQVR4nGNgIA'.
     'K42hhqGtm5+WFIWClKycvLK6gbuARGoEj4aMjLSElISUir6Tt7'.
     'x+aEIWR8leQlwEBSTc/CK7awLguuR0lGQkJMVFRUTFJVzwko1d'.
     'oFk9OQl5IQE+Dh5hVR0TV3CkkvbJgyASJjDZIR5GBl5eRX0TH1'.
     'DEqrbJ2ypBEspSgvJSXKw8bMxMavbOLoGZNf1TZlybw4oIyfLN'.
     'BxotxsLEzsQiaOHkFpBQ2905esrAZK2SpIAaUEuDm5+LTNPAKj'.
     'C+pbps1evrIDKGWnLictKSkuLKyoZQyUya9o7Z2+YMXKGUApew'.
     'M9PTVdXR0TEwf3wOjUirruafOXL18xFyjl72Kpb25qaurg4REU'.
     'EFVe2zJ5zpLlK1aCpbydnZ2dnDwDA6NTopLLeiZNXbB8BcTAyP'.
     'TQ0JDg4KCY1NS83JKmiVOBepYvX9UPlAovzEiPSU/LLyior2vq'.
     'mjZr3vLlIF01IC+XVhUWFlZW1Lc290ycOGfxohVATSsXx4Oksn'.
     'vaWlsb2tq6J0+bM2/RohVA81asbIcEYueU3t7JU6ZNnwNyGkhm'.
     '+cp5CRCppJnzZ8+ZM3/JUogECBbBIixr8Yqly8FCy8F6ltUgoj'.
     'lz7sqVK2ByK+cVMSCDxoUrwWDVysXt8WhJKqG4Y8bcuTP6qrGk'.
     'QwwAABiMu7T4HMi4AAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bl_gray.png
        //==========================================================
        $this->imgdata_large[5][0]= 905 ;
        $this->imgdata_large[5][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAMAAACelLz8AAABO1'.
     'BMVEX////////3///39/fv7+/e5+fW3t7Wzs7WxsbG1tbGzsbG'.
     'xsbDxMS/v7++wMC+v7+9zsa9xsa9vb29tbW9ra29pa24uLi1xs'.
     'a1vb21tbWxtrattbWmpqalra2cra2cpaWcnJycjIyUpaWUnJyU'.
     'lJSUjIyMnJyMnJSMlJSMlIyMjJSMjIyElJSElIyEjIyEhIR7jI'.
     'x7hIR7hHt7e3t7e3N7e2tzhIRze3tze3Nzc3Nre3trc3Nrc2tr'.
     'a2tjc3Njc2tja3Nja2tjY2NjWlpaa2taY2taY2NaY1paWlpaUl'.
     'JSY2NSY1pSWlpSWlJSUlJSUkpKWlpKWlJKUlpKUlJKUkpKSkpK'.
     'SkJCUlJCUkJCSkpCSkJCQkI5Sko5QkI5Qjk5OUI5OTkxQkIxOT'.
     'kxMTkxMTEpMTEhMTEhKSkYISEpy7AFAAAAAXRSTlMAQObYZgAA'.
     'AAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdE'.
     'lNRQfTAwkRFQfW40uLAAABx0lEQVR4nI3SbXfSMBQA4NV3nce5'.
     'TecAHUywRMHSgFuBCFsQUqwBS1OsWQh0GTj//y8wZUzdwQ/efM'.
     'tzcm/uuXdj4z9ic/PR9k4qk1qDnf0X2/uZzKt8GaRvSubg4LVp'.
     'mkWzCGAT/i3Zsm2XNQHLsm2n2937LaaNnGoJFAEo27B50qN0ay'.
     'Wg26lXsw8fP8nmzcJb2CbsnF5JmmCE8ncN404KvLfsYwd7/MdV'.
     'Pdgl/VbKMIzbuwVgVZw2JlSKJTVJ3609vWUY957lgAUd1KNcqr'.
     'yWnOcOPn8q7d5/8PywAqsOOiVDrn42NFk+HQ7dVuXNYeFdBTpN'.
     'nY5JdZl8xI5Y+HXYaTVqEDp1hAnRohZM03EUjMdhn5wghOoNnD'.
     'wSK7KiiDPqEtz+iD4ctdyAifNYzUnScBSxwPd6GLfRURW7Ay5i'.
     'pS5bmrY8348C5vvUI+TLiIVSJrVA0heK/GDkJxYMRoyfCSmk4s'.
     'uWc3yic/oBo4yF374LGQs5Xw0GyQljI8bYmEsxVUoKxa6HMpAT'.
     'vgyhU2mR8uU1pXmsa8ezqb6U4mwWF/5MeY8uLtQ0nmmQ8UWYvb'.
     'EcJaYWar7QhztrO5Wr4Q4hDbAG/4hfTAF2iCiWrCEAAAAASUVO'.
     'RK5CYII=' ; 

        //==========================================================
        // File: bl_brown.png
        //==========================================================
        $this->imgdata_large[6][0]= 1053 ;
        $this->imgdata_large[6][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAMAAADzN3VRAAABoV'.
     'BMVEX////Gzs7GvbXGrZTGpXu9nHO1nHO1nIy9taXGxs7GtaXO'.
     'nHPGlFrGjEq9hEq1hEqte0Klczmcazmce1KtnIzGxsbGvb3OlF'.
     'LOlFq9hFKte0qcc0KUYzGEWimMc1K9ta3OnGvOnGPWnGO9jFq9'.
     'jFKlc0KUazmMYzl7UilzUjGtpZzGxr3GnGPWpWvepXO1hFJ7Wj'.
     'FrSiFjUjG1ra3GnHPvxpT/5733zpythFKUa0KEYzlzUilaOSF7'.
     'Wjm9jErvvYz/99b///f/78bnrYS1hFqle0p7UjFrSiljQiFCMR'.
     'iMhHO9lGvGjFLWnGv/3q3////erXuthEqlc0paQiFKMRhSQin/'.
     '1qX/997//++cc0pjSilaQilKORhCKRiclIy9pYzGlGPntYT33q'.
     '3vvZSEWjlSOSE5KRB7c2O1lHutczmthFqte1JrWkqtjGtCKRBa'.
     'SjmljGuca0KMYzGMaznOztaclISUYzmEWjFKOSF7a1qEYzFaSi'.
     'GUjISEa0pKOSm9vb2llIxaQhg5IQiEc2tzY0paORilnJy1raVS'.
     'OSljUkJjWkKTpvQWAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHU'.
     'gAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkREiei'.
     'zP2EAAAB9UlEQVR4nGWS/VfSUBjHL5QluhhBxtwyWcCus5Blpm'.
     'wDC4ONaWXCyBi7RMZmpQ2Bypm9W/byV3cHHo/W88s95/s5z/d5'.
     'uwCcCh/4L3zAf+bs0NC588On9QAYGSUuBINk6GI4cmnsBLk8Go'.
     '1SFEGMkzRzZeLq5JE8FvDHouw1lqXiCZJOcnCKnx4AcP0GBqmZ'.
     'mRgRT9MMB4Wbs7cGSXNRik3dnp9fiMUzNCNKgpzN9bsaWaQo9s'.
     '7dfH7pXiFTZCBU1JK27LmtBO8TDx7mV1eXHqXXyiIUFLWiVzHx'.
     'BxcJIvV4/cn6wkqmWOOwmVE3UQOAp6HxRKL5bGPj+VwhUhalFq'.
     '8alm5vAt+LlySZTsebzcKrraIIW4JqZC3N3ga+1+EQTZKZta1M'.
     'pCZCSeDViqVrThsEdsLJZLJYLpZrHVGScrKBvTQNtQHY6XIM02'.
     'E6Ik7odRW1Dzy3N28n3kGuB3tQagm7UMBFXI/sATAs7L5vdbEs'.
     '8Lycm923NB0j5wMe6KOsKIIyxcuqauxbrmlqyEWfPmPy5assY1'.
     'U1SvWKZWom9nK/HfQ3+v2HYZSMStayTNN0PYKqg11P1nWsWq7u'.
     '4gJeY8g9PLrddNXRdW8Iryv86I3ja/9s26gvukhDdvUQnIjlKr'.
     'IdZCNH+3Xw779qbG63f//ZOzb6C4+ofdbzERrSAAAAAElFTkSu'.
     'QmCC' ; 

        //==========================================================
        // File: bl_darkgreen.png
        //==========================================================
        $this->imgdata_large[7][0]= 1113 ;
        $this->imgdata_large[7][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAMAAACelLz8AAAB2l'.
     'BMVEX////////3///v///n/+/e99bW/+/W99bO786/v7++vr69'.
     '/96999a7wb24vbu1/9a1zqW1u7itxrWosq6l772l1qWlxrWlxq'.
     '2lva2cxpSU562U3q2UxqWUvaWUpZyM77WM57WMvYyMtZyMrZyM'.
     'pZSMnJSEvZyEtYyErZSElIx7zpR7xpx7xpR7vZR7jIRz1pRzxp'.
     'RzjIRrzpRrzoxrxoxrtYRrrYxrrXtrpYRrhHNjzoxjxoxjxoRj'.
     'vYRjtYRjrXtjpXtjlGNje2tazoxazoRaxoxaxoRavYRatYRatX'.
     'tarXtapXNanHNajFpae2tSzoRSxoRSvXtStXtSrXtSrXNSpXNS'.
     'nHNSnGtSlGtSlGNSjGtSjGNKvXtKtXNKrXNKpWtKnGtKlGNKjG'.
     'NKhGNKhFJKc1pKa1JCrWtCpWtCnGtClGNCjGNCjFpChFpCe1JC'.
     'a1JCY1I5pWs5nGM5lGM5jFo5hFo5e1o5c0o5WkoxjFoxhFoxhF'.
     'Ixe1Ixc1Ixc0oxa0ophFIpe0opc0opa0opa0IpY0IpWkIpWjkp'.
     'UkIpUjkhc0oha0IhY0IhWjkhWjEhUjkhUjEhSjEhSikhQjEhQi'.
     'kYWjkYSjEYSikYQjEYQikQSikQQikQQiEQOSExf8saAAAAAXRS'.
     'TlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAd'.
     'LdfvwAAAAHdElNRQfTAwkRFCaDkWqUAAAB+ElEQVR4nI3S+1vS'.
     'UBgHcGZlPV0ks/vFrmQWFimJjiwiYUJWjFBWFhClyZCy5hLrwA'.
     'x2EIwJC1w7zf2vnU0re+iHvs9++7x7zznvORbLf+TA6ct9fYMX'.
     'jrfAUYefpp+/iM1ykxf/lmuhUZ/PTwXC8dml5Wcd23o5H5Mk6b'.
     '5NUU8icXbhS67rNzn9JDnguOEYGQtEEtwC+Crs3RJ76P5A/znr'.
     'vsNX7wQnEiwHCtK7TTkW8rvdZ9uJtvZTLkxpHhSrP66bNEj7/P'.
     '3WNoLYeeSWQQCIpe9lQw7RNEU5rDsIYtcJ14Nocg7kRUlBNkxn'.
     'YmGKcp7cv3vPwR7XOJPmc0VYU3Sv0e9NOBAYG7Hbz/cMjTMveZ'.
     'CHkqxuTBv0PhYJB4N3XR6PJ5rMAPMnpGUxDX1IxSeMTEaZp1OZ'.
     'nGAIQiYtsalUIhFlmGTy3sO3AizJCKn6DKYryxzHsWyaneMzr6'.
     'cWxRVZVlFTe4SpE3zm+U/4+whyiwJcWVMQNr3XONirVWAklxcE'.
     'EdbqchPhjhVzGpeqhUKhWBQhLElr9fo3pDaQPrw5xOl1CGG1JE'.
     'k1uYEBIVkrb02+o6RItfq6rBhbw/tuINT96766KhuqYpY3UFPF'.
     'BbY/19yZ1XF1U0UNBa9T7rZsz80K0jWk6bpWGW55UzbvTHZ+3t'.
     'vbAv/IT+K1uCmhIrKJAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bl_green.png
        //==========================================================
        $this->imgdata_large[8][0]= 1484 ;
        $this->imgdata_large[8][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAABm'.
     'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
     'B3RJTUUH0wMMFjM4kcoDJQAABVlJREFUeNq9ll2MJFUVx3/11V'.
     'Vd/TE9vU0v4zLDwJIF16jBqLAPhsRXEiDqg0QTJiQSjcSNvCzw'.
     'sBEDDxizhvAAxBgf1oR9QF9NiE9ESFZkQyZB5WtddmdnZ3qqqr'.
     'uqbt367Cofqu3ZZpWVaDzJfbkf53//55z/PVdZXV3l/2H6f7Lp'.
     '5VdOV/4Nb+GmHpUeA7AdBNxc3kafNb73jRPK9Xwon8ToxVefqU'.
     'b91wibH5EkCQBCizFihTSviHUHR0hWws9xe3wvJ7/7nPKpgX5y'.
     '9oFqt3eOgWniRBoAbUBGGqZUibSYaeoT2B5bnkdaSA6793Cv/S'.
     'QPPbihXBfo5VdOV+8dfgnvwAU62YH5fCZ12sDujFkwyegCqTrB'.
     'iUOKTOJKj8jr88jS8zy6cXwBTP048nuHX0I0nDlIp7RpTG7kM0'.
     'sdyAYsTVukUuWGhlWHMq0ITL92lnUp9R1Obz/GmTNnqn9bDD8/'.
     '+0D1oX0O0zQZZDYCsK2j3Gl9jQqDfHiei8GfiKVLlsZkJaBAN1'.
     '0i6PgwUbB0GxG5/PrtE/xLRr959Znqw9452oVNI+jiJhnr1pe4'.
     'k29zB1/nFr5Kj7tpt1YYhJ0FJ7nUYbcJQBgahN2MzeCP/OipR6'.
     'prgN6Qr6ELFQFUWoRpNVjlKwxZB8DCpE+PtfEKqV1cUzxpVudu'.
     'GTBHA5Y1g99e+dUio9O/P1Vpq+/WE5GGjDSMoAtAQjrf3C52IP'.
     'QxpY4WK2hpReka9Gfrhqgz0bACRoCWjDh56kQ1z9FeuUUQxVhK'.
     'B92sD1VahM+bAJgcoJhGjP/6Ln8rAgDiRCVRKiIzxMkkodBJ85'.
     'im1IlEHbE4k1xyNveL4YP8HarmGJIOpqyjeQmfNHmTvnqZTWBt'.
     'vIJXpPwlukJSuSTKGK3pEwtJmiX00ZlInTyNscImO6XBITvH1c'.
     '8vVt2OucdKvIyeKRTNCivsEMgcpg6taYs30nfq0Gqg6hOSSFJ4'.
     'BSnJPht0IqEjWmOGocEI6F0J94F0qaL6BntTF0MtUfweKQKAPU'.
     'Wwp4OcVnQAmVb0p9DLOzjEhEKnGRmoRc7EzRGlwA6NujAKG4yP'.
     '6Sjwc4aVznZ7DK0xXdkDoJf0kGmFBniFBOBGcZSCCSKd0IwN0k'.
     'IS+QZWCGVZex4BnUxya3+Zt9iugQbcRFpIAtuHvAZulPUdLhUJ'.
     'RqegI3WcqaSXddlT3idsWMSRRGkEtNwmyTifAwyBo7LP+11J0e'.
     '7tM7pZOYblHkBLcqZ5LcYtw6Wbd4CM3SpE9foYZsIHoqDKCrbz'.
     'mLSQtPwmuhXgtBLs0GBdbXOhFGB7WBKO2F8GXt9/VO97Ya3atF'.
     '7nUHnwGjGGQqcPxFEdFqURkEidiZszAERoYIsGju1hq21kWee3'.
     'bw15+8WpsvAy3K1+i3JkkhZyPpxxjjPOsfOYiZ+TFhLPzQnHOU'.
     'tpzGB2dgA4tscIkKIx19Cxg/fPL7vQJu47eXt1VvsDK8pwPueZ'.
     'PuZoQMOqhRoJHSs0kKLBWjvjYinmeQGw1TaX1RFdfZ3LMzYLjA'.
     'C++dkn6AaH2Nobk6cxEzdnuG0TdC8zvdJkN0hqkFkO/jwL0fxa'.
     'so8sBcuFzQ+/+MRC+BeAHnpwQzn++ee5KT9Eshuy46dcKAXm32'.
     '0uzPQhS4GttkH2GQID2Wc0Y4LtAbDxhZ/x5A+e/uTG9+jGceXH'.
     '9/ySnnIXnUzOxXe1038mW3ZynNmam4yYWkO+f9cv+Oljz16/lV'.
     '9tDz/9nerc1hm8ZEScSRK7VvtYl1i1dklsOKyvc+zg/bzw1O8+'.
     '/efkajt56kR1ydlEJBc5H46xzbrJ3dY9wrB7hGcff+6/+279L+'.
     '0fHxyiE8XMLl4AAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bl_blue.png
        //==========================================================
        $this->imgdata_large[9][0]= 1169 ;
        $this->imgdata_large[9][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAMAAACelLz8AAACEF'.
     'BMVEX/////////7//35//v1v/exv/Wvf/Wrf/Wpf/Orf+/v7+9'.
     'tc69jP+9hP+5ucW1tc6tlP+rq7Wlpdalpcalpb2cnM6cnMacc/'.
     '+cWv+UlLWUjN6UjK2Uc/+Ma/+MUv+EhKWEa/+EQvd7e8Z7e7V7'.
     'e6V7c957Wv9za9Zza8ZzSv9ra5xrSv9rOf9rMe9jUudjQv9jOe'.
     '9aWpRaUt5aUpRaSu9aSudSUoxSSs5SSoxSMf9KQtZKOfdKMedK'.
     'Kf9KKe9CKf9CKb1CKa1CIfdCIedCId45MXs5Kfc5If85Iec5Id'.
     'Y5GP8xMbUxMXsxKc4xKZQxIf8xGP8xGO8xGN4xGNYxGL0xGK0p'.
     'KXMpIYwpGP8pGO8pGOcpGNYpGM4pEP8pEPcpEOcpEN4pENYpEM'.
     'YpEL0hGKUhEP8hEPchEO8hEOchEN4hENYhEM4hEMYhELUhCP8h'.
     'CO8hCN4YGJwYGGsYEL0YEK0YEHMYCN4YCM4YCMYYCL0YCKUYAP'.
     '8QEJQQEIwQEHsQEGsQCM4QCLUQCK0QCKUQCJwQCJQQCIwQCHMQ'.
     'CGsQAP8QAPcQAO8QAOcQAN4QANYQAM4QAMYQAL0QALUQAKUQAJ'.
     'QQAIQICGsICGMIAO8IANYIAL0IALUIAK0IAKUIAJwIAJQIAIwI'.
     'AIQIAHsIAHMIAGsIAGMAAN4AAMYAAK0AAJQAAIwAAIQAAHMAAG'.
     'sAAGMAAFrR1dDlAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgA'.
     'AAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkRFRPMOZ'.
     '/2AAAB+klEQVR4nGNgIAIIqeqZmBqpi2JISNml5lVXV3d198Yo'.
     'oUjwm1SnxsbGRsSm5ZfNXO4tjCTjVh0ABhFx6QV9E1Y0S8JkuN'.
     '3yAgLc7W3t/QPi4jPKJ8ye1yoIlTKpjvVy15eVUbN0i4zKLJ8w'.
     'ae6qcKgLqmMj3PUFWFl5NJ0CExLLJzbNW7BWCyxlXR0ba6/Axs'.
     'zELmfnkRBT0QiSKgXJCOflxUbYy3KyMHEoOrtEZ1c2TZ6/cMl6'.
     'eaCUamdsbIC7tjgPr4SBS3BMMVDTwkXr1hsDpYy6UmMj/O0tdX'.
     'QNbDxjknJLWqYsXLx0vStQynxGflpkZGCgs7Onp29SbtNkoMy6'.
     'pevCgFJWy3oyMuKjgoKCPWNCvEuqWhcsWrJ06XqQlPnMvrKyrM'.
     'TomJjkZAfHlNa2qdOWrlu63gcopbG8v7+hvLwip7g4JdSxsLZu'.
     '8dKlS9ettwBKic2eNXHChIkTG5tKqgpr2uo6loLAehWQx0LnzJ'.
     '49p6mpeXLLlNq6RUvqly6dvnR9Bx9ISnnlvLmT582bMr9t4aL2'.
     '+vrp60GaDCGB6Ld6wfwFCxYCJZYsXQ+SmL6+FBryInVrFi1atH'.
     'jJkqVQsH6pNCzCJNvXrQW6CmQJREYFEc2CYevXrwMLAyXXl0oz'.
     'IAOt0vVQUGSIkabkDV3DwlzNVDAksAAAfUbNQRCwr88AAAAASU'.
     'VORK5CYII=' ; 

        //==========================================================
        // File: bs_red.png
        //==========================================================
        $this->imgdata_small[0][0]= 437 ;
        $this->imgdata_small[0][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAk1'.
     'BMVEX////////GxsbGra3/xsbOhITWhIT/hIT/e3v/c3P/a2vG'.
     'UlK1SkrOUlL/Y2PWUlLGSkrnUlLeSkrnSkr/SkqEGBj/KSmlGB'.
     'jeGBjvGBj3GBj/EBD/CAj/AAD3AADvAADnAADeAADWAADOAADG'.
     'AAC9AAC1AACtAAClAACcAACUAACMAACEAAB7AABzAABrAABjAA'.
     'BuukXBAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ'.
     'cwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGDNEMgOYAAAAm0'.
     'lEQVR4nI3Q3RKCIBAFYGZMy9RKzX7MVUAUlQTe/+kS0K49d3wD'.
     '7JlFaG+CvIR3FvzPXgpLatxevVVS+Jzv0BDGk/UJwOkQ1ph2g/'.
     'Ct5ACX4wNT1o/zzUoJUFUGBiGfVnDTYGJgmrWy8iKEtp0Bpd2d'.
     'jLGu56MB7f4JOOfDJAwoNwslk/jOUi+Jts6RVNrC1hkhPy50Ef'.
     'u79/ADQMQSGQ8bBywAAAAASUVORK5CYII=' ; 


        //==========================================================
        // File: bs_lightblue.png
        //==========================================================
        $this->imgdata_small[1][0]= 657 ;
        $this->imgdata_small[1][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAABVl'.
     'BMVEX////////d///AwMC7wcS08P+y+P+xxdCwxM+uws2twMur'.
     'vsinzNynytylzuKhyN6e5v6d5P+d1fOcwNWcu8ub4f+at8iZ3v'.
     '+ZvdGY2/yW2f+VscGU1vuT1fqTr72Sx+SSxeKR0fWRz/GPz/OP'.
     'rr+OyeqMy+6Myu2LyeyKxueJudSGw+SGorGDvt+Cvd6CvN2Aud'.
     'p+uNd+t9Z9tdV8tdR8tNN6sc94r813rct2q8h0qcZ0qMVzp8Rx'.
     'o8Bwor5tn7ptnrptnrlsnbhqmbRpmbNpi51ol7Flkqtkkqtkka'.
     'pjj6hijaRhjaZgi6NfiqJfiaFdh55bhJtag5pZgphYgJZYf5VX'.
     'cn9Ve5FSeI1RdopRdYlQdYlPc4dPcoZPcoVNcINLboBLbH9GZn'.
     'hGZXdFZHZEY3RDYnJCXW4/W2s/WWg+Wmo7VmU7VGM7U2E6VGM6'.
     'VGI5UV82T1wGxheQAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHU'.
     'gAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGTok'.
     '9Yp9AAAAtElEQVR4nGNgIBaw8wkpKghzwvksPAKiUsraprYiLF'.
     'ARXkE2JiZ1PXMHXzGIAIekOFBE08TGLTCOCyzCLyvDxsZqZOnk'.
     'E56kAhaRV9NQUjW2tPcMjs9wBYsY6Oobmlk7egRGpxZmgkW0zC'.
     '2s7Jy9giKT8gohaiQcnVzc/UNjkrMLCyHmcHr7BYREJKTlFxbm'.
     'QOxiEIuKTUzJKgQCaZibpdOzQfwCOZibGRi4dcJyw3S4iQ4HAL'.
     'qvIlIAMH7YAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bs_gray.png
        //==========================================================
        $this->imgdata_small[2][0]= 550 ;
        $this->imgdata_small[2][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAAQCAMAAADH72RtAAABI1'.
     'BMVEX///8AAAD8EAD8IAD8NAD8RAD8VAAYGBi/v7+goKCCgoJk'.
     'ZGRGRkb8yAD83AD87AD8/AD4+ADo+ADY+ADI+AC0+ACk+ACU+A'.
     'CE+AB0/ABk/ABU/ABE/AAw/AAg/AAQ/AAA/AAA+AAA6BAA2CAA'.
     'yDQAtEQApFQAlGQAhHQAdIgAZJgAVKgARLgAMMgAINwAEOwAAP'.
     'wAAPgIAPAQAOgYAOAkANgsANA0AMg8AMBEALhMALBUAKhcAKBo'.
     'AJhwAJB4AIiAAID////4+Pjy8vLs7Ozm5ubg4ODa2trT09PNzc'.
     '3Hx8fBwcG7u7u1tbWurq6oqKiioqKcnJyWlpaQkJCJiYmDg4N9'.
     'fX13d3dxcXFra2tkZGReXl5YWFhSUlJMTExGRkZAQEA1BLn4AA'.
     'AAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIA'.
     'AAsSAdLdfvwAAAAHdElNRQfTAwkUGiIctEHoAAAAfElEQVR4nI'.
     '2N2xKDIAwF+bZ2kAa8cNFosBD//yvKWGh9dN+yk9kjxH28R7ze'.
     'wzBOYSX6CaNB927Z9qZ66KTSNmBM7UU9Hx2c5qjmJaWCaV5j4t'.
     'o1ANr40sn5a+x4biElrqHgrXMeac/c1nEpFHG0LSFoo/jO/BeF'.
     'lJnFbT58ayUf0BpA8wAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bs_greenblue.png
        //==========================================================
        $this->imgdata_small[3][0]= 503 ;
        $this->imgdata_small[3][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAxl'.
     'BMVEX///////+/v79znJQhSkJ7raU5hHtjraVKnJRCjIRClIyU'.
     '9++E595avbVaxr2/v7+ctbWcvb17nJxrjIx7paUxQkK9//+Mvb'.
     '17ra2Evb17tbVCY2MQGBiU5+ec9/eM5+d71tZanJxjra1rvb1j'.
     'tbVSnJxara1rzs5jxsZKlJRChIQpUlIhQkJatbVSpaU5c3MxY2'.
     'MYMTEQISFavb1Sra1KnJxCjIw5e3sxa2spWlpClJQhSkoYOTkp'.
     'Y2MhUlIQKSkIGBgQMTH+e30mAAAAAXRSTlMAQObYZgAAAAFiS0'.
     'dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfT'.
     'AwkUGTIqLgJPAAAAqklEQVR4nI2QVxOCMBCEM6Mi2OiCvSslJB'.
     'CUoqjn//9TYgCfubf9Zu9uZxFqO+rscO7b6l/LljMZX29J2pNr'.
     'YjmX4ZaIEs2NeiWO19NNacl8rHAyD4LR6jjw6PMRdTjZE0JOiU'.
     'dDv2ALTlzRvSdCCfAHGCc7yRPSrAQRQOWxKc3C/IUjBlDdUcM8'.
     '97vFGwBY9QsZGBc/A4DWZNbeXIPWZEZI0c2lqSute/gCO9MXGY'.
     '4/IOkAAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bs_yellow.png
        //==========================================================
        $this->imgdata_small[4][0]= 507 ;
        $this->imgdata_small[4][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAzF'.
     'BMVEX///////+/v79zYwCMewDOxoTWzoTezkr/5wj/5wDnzgDe'.
     'xgC1pQCtnACllACcjACUhABjWgDGvVK1rUrOxlLGvUqEexilnB'.
     'jv3hj35xj/7wj/7wD35wDv3gDn1gDezgDWxgDOvQDGtQC9rQCE'.
     'ewB7cwBzawBrYwDWzlLn3lLe1krn3kre1hi9tQC1rQCtpQClnA'.
     'CclACUjACMhAD/9wC/v7///8bOzoT//4T//3v//3P//2v//2Pn'.
     '50r//0r//yn39xj//xD//wBjYwDO8noaAAAAAXRSTlMAQObYZg'.
     'AAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAH'.
     'dElNRQfTAwkUGSDZl3MHAAAAqElEQVR4nI3QWRNDMBAA4My09E'.
     'IF1SME0VT1okXvM/3//6kEfbZv+81eswA0DfHxRpOV+M+zkDGG'.
     'rL63zCoJ2ef2RLZDIqNqYexyvFrY9ePkxGWdpvfzC7tEGtIRly'.
     'nqzboFKMlizAXbNnZyiFUKAy4bZ+B6W0lRaQDLmg4h/k7eFwDL'.
     'OWIky8qhXUBQ7gKGmsxpC+ah1TdriwByqG8GQNDNr6kLjf/wAx'.
     'KgEq+FpPbfAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bs_darkgray.png
        //==========================================================
        $this->imgdata_small[5][0]= 611 ;
        $this->imgdata_small[5][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAA8AAAAPCAMAAAAMCGV4AAABJl'.
     'BMVEX////////o8v/f6O7W4OnR3PXL1OTL0evEyLvCzePAwMC/'.
     'v7a8wsq7t7C1xum1vtS1q6GzopmyxeKsrsOqvNWoq7anvN+nsb'.
     'qhrcGgqbGfpq6cp7+bqMuVmJKRm7yPlKKMnL6FkKWFipOEkLSE'.
     'j6qEhoqAiaB+jqd8haF7hZR4iJt4g5l3hZl2gIt2cod1hJVzeY'.
     'VzboJvhp9sfJJsb41peY1pd5xpdoVod4xndI5lcHxka4BjcYVg'.
     'Z3BfboFbb4lbZnZbYntaZ4laZYVZV3JYYWpXX3JWWm5VX4RVW2'.
     'NUYX9SXHxPWn5OVFxNWWtNVXVMVWFKV3xHUGZGU3dGTldFSlxE'.
     'Sk9ESXBCRlNBS3k/SGs/RU4+R1k9R2U6RFU2PUg0PEQxNU0ECL'.
     'QWAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAA'.
     'CxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGQmbJetrAAAAtklEQV'.
     'R4nGNgwAK4JZTNNOWlYDxhMT4ZDTOzQE1uMF9CiJWVU0LbxDlS'.
     'G8QVF+FnZ2KRNHAIiPUHaZGSlmZj5lH19A1KjLUA8lXU5MWllF'.
     'yjo30TYr2BfG19G11b37CEeN84H38gX1HbwTUkOjo+zjfG3hLI'.
     'l1exCvCNCwnxjfMz0gTyRdXNHXx9fUNCQu2MwU6SN3ZwD42LCH'.
     'W30IK4T8vUJSAkNMhDiwPqYiktXWN9JZj7UQAAjWEfhlG+kScA'.
     'AAAASUVORK5CYII=' ; 


        //==========================================================
        // File: bs_darkgreen.png
        //==========================================================
        $this->imgdata_small[6][0]= 666 ;
        $this->imgdata_small[6][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAABX1'.
     'BMVEX////////l/+nAwMC86r+8wb28wby8wLy78sCzw7SywrSx'.
     'wLKwvrGuvK+syK+ryq2rx62n36ym3aumxKmk2qij0Keh16ahva'.
     'Og1aSguKKe06KeuaCetZ+d0KGdtZ+bz6Cay56ZyZ2Zwp2Zr5qZ'.
     'rpqYwJuXyZuXrJmVw5mUxZiTxJeTw5eTq5WRwJWPtJKOvZKKuI'.
     '6Kt42Kn4yJt42ItIuGsomFsYmEsIiEr4eDr4eBrIR/qoN+qIJ8'.
     'poB7pH56o356on14nnt2nXl0mndzmnZzmXZymHVwlXNvlHJukn'.
     'FtiHBqjm1qjW1oi2toiWpniWplh2hlhmdkhWdig2VggGNgf2Je'.
     'fmFdfGBde19bbl1aeFxXdFpWclhVclhVcVdUcFZTb1VSbVRQal'.
     'JPaVFKY0xKYkxJYUtIYEpHX0lEWkZCWERCV0NCVkM/U0A+U0A+'.
     'UUA+UEA9Uj89UT48Tj45TDvewfrHAAAAAXRSTlMAQObYZgAAAA'.
     'FiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElN'.
     'RQfTAwkUGRjxlcuZAAAAtElEQVR4nGNgIBZw8osqqIpzw/msfI'.
     'IiUmr6lo6SbFARASEOJiYtQ2uXADmIAJeEGFBE18LBMySBBywi'.
     'LC/LwcFiZuvmH5WiAxZR0tRW1DC3dfYJS8zyAouYGBibWtm7+o'.
     'TEpZfkgEX0rG3snNx9Q2NSCksgaqRd3Ty8gyLiU/NKSiDmcPsF'.
     'BodHJ2UUlZTkQ+xikIlNSE7LLgECZagL2VQyc0H8YnV2uD94jS'.
     'ILIo14iQ4HALarJBNwbJVNAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bs_purple.png
        //==========================================================
        $this->imgdata_small[7][0]= 447 ;
        $this->imgdata_small[7][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAnF'.
     'BMVEX///////+/v7/Gvca9rb3Grcb/xv+1hLWte629hL21e7XG'.
     'hMbWhNbOe87We9b/hP//e/97OXv/c///a///Y/+cOZz/Sv/WOd'.
     'bnOefvOe//Kf9jCGNrCGv/EP//CP/nCOf/AP/3APfvAO/nAOfe'.
     'AN7WANbOAM7GAMa9AL21ALWtAK2lAKWcAJyUAJSMAIyEAIR7AH'.
     'tzAHNrAGtjAGPP1sZnAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgF'.
     'HUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGS'.
     'o5QpoZAAAAnElEQVR4nI3Q2xJDMBAG4MyQokWrZz3oSkJISJH3'.
     'f7dK0Gv/Xb7J7vyzCK0NjtPsHuH/2wlhTE7LnTNLCO/TFQjjIp'.
     'hHAA6bY06LSqppMAY47x+04HXTba2kAFlmQKr+YuVDCGUG2k6/'.
     'rNwYK8rKwKCnPxHnVS0aA3rag4UQslUGhrlk0Kpv1+sx3tLZ6w'.
     'dtYemMkOsnz8R3V9/hB87DEu2Wos5+AAAAAElFTkSuQmCC' ; 


        //==========================================================
        // File: bs_brown.png
        //==========================================================
        $this->imgdata_small[8][0]= 677 ;
        $this->imgdata_small[8][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAABaF'.
     'BMVEX//////////8X/3oD/3nj/1HX/0Gr/xGP/rkv/gBf+iS/2'.
     'bAL1agDxaQDuZwDrZwLpZQDmZQLlZADjcx7gZATeYQDdZgraXw'.
     'DZXwHYXgDXiEvXZAvUjlfUXwXTjVfTbR7ShUvRbR7RWwDMWQDL'.
     'WADKooLKWADJoYLJgkvHWATGoILFn4LFgEvFVgDEZx7EVQDDt6'.
     '/DVQDCt6/CnoLChlfCVADAwMC+hFe+UgC8UgC6UQC4gVe4UAC3'.
     'gVe3UAC1gFe1eUu1TwC1TgCzTgCwTQKuTACrSgCqSgCpSgCpSQ'.
     'CodEulSACkRwCiRgCdRACcRACaQwCYQgCWQgKVQQCVQACUQACS'.
     'UR6RPwCOPgCNPQCLPACKPACJOwCEOQCBOAB+NwB9NgB8NgB7NQ'.
     'B6NwJ4NAB3RR52MwB0MgBuLwBtLwBsLwBqLgBpLQBkLQJiKgBh'.
     'KgBgKwRcKABbKQJbJwBaKQRaJwBYKAJVJQDZvdIYAAAAAXRSTl'.
     'MAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLd'.
     'fvwAAAAHdElNRQfTAwkUGho0tvl2AAAAtklEQVR4nGNgIBaoSg'.
     'mLKGpowfkGMty8AqJKpi4mRlAROR5ONg4JFUv3YHOIgDo/HwsT'.
     'q6yps29EsjZYREFIkJ2ZS9/OMzA20wEsIi8uKSZtaOPmH5WSFw'.
     'YW0VRW07Vw8vCLSMguLwCL6FlaObp6B0TGZxSXQ9TouHv6+IXG'.
     'JGYWlpdDzNEKCgmPjkvLKS0vL4LYxWAen5SelV8OBNZQFxrZ5h'.
     'aC+GX2MDczMBh7pZakehkTHQ4AA0Am/jsB5gkAAAAASUVORK5C'.
     'YII=' ; 

        //==========================================================
        // File: bs_blue.png
        //==========================================================
        $this->imgdata_small[9][0]= 436 ;
        $this->imgdata_small[9][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAk1'.
     'BMVEX///////+/v7+trcbGxv+EhM6EhNaEhP97e/9zc/9ra/9S'.
     'UsZKSrVSUs5jY/9SUtZKSsZSUudKSt5KSudKSv8YGIQpKf8YGK'.
     'UYGN4YGO8YGPcQEP8ICP8AAP8AAPcAAO8AAOcAAN4AANYAAM4A'.
     'AMYAAL0AALUAAK0AAKUAAJwAAJQAAIwAAIQAAHsAAHMAAGsAAG'.
     'ONFkFbAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ'.
     'cwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGhNNakHSAAAAmk'.
     'lEQVR4nI3P2xKCIBAGYGfM6SBWo1nauIqogaDA+z9dK9Lhrv47'.
     'vtl/2A2CfxNlJRRp9IETYGraJeEb7ocLNKznia8A7Db7umWDUG'.
     'sxAzhurxRHxok4KQGqCuEhlL45oU1D2w5BztY4KRhj/bCAsetM'.
     '2uObjwvY8/oX50JItYDxSyZSTrO2mNhvGMbaWAevnbFIcpuTr7'.
     't+5AkyfBIKSJHdSQAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bs_green.png
        //==========================================================
        $this->imgdata_small[10][0]= 452 ;
        $this->imgdata_small[10][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAn1'.
     'BMVEX///////+/v7+/v7/G/8aUxpSMvYyUzpSMzoyM1oxarVqE'.
     '/4R7/3tavVpKnEpaxlpz/3Nr/2tKtUpj/2Na51pKzkpK1kpK50'.
     'pK/0oYcxgp/ykYlBgY3hgY7xgY9xgQ/xAI/wgA/wAA9wAA7wAA'.
     '5wAA3gAA1gAAzgAAxgAAvQAAtQAArQAApQAAnAAAlAAAjAAAhA'.
     'AAewAAcwAAawAAYwA0tyxUAAAAAXRSTlMAQObYZgAAAAFiS0dE'.
     'AIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAw'.
     'kUGgW5vvSDAAAAnklEQVR4nI3QSxKCMAwA0M4gqCgoiiJ+kEAL'.
     'LQUq0PufzX7ENdnlJZNkgtDS2CYZvK6bf+7EoKLA9cH5SQzv6A'.
     'YloTywsAbYr44FrlgrXCMJwHl3xxVtuuFkJAPIcw2tGB9GcFli'.
     'oqEf5GTkSUhVMw2TtD0XSlnDOw3SznE5520vNEi7CwW9+Ayjyq'.
     'U/3+yPuq5gvhkhL0xlGnqL//AFf14UIh4mkEkAAAAASUVORK5C'.
     'YII=' ; 


        //==========================================================
        // File: bs_white.png
        //==========================================================
        $this->imgdata_small[11][0]= 480 ;
        $this->imgdata_small[11][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAAQCAYAAADwMZRfAAAABm'.
     'JLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsRAAALEQF/ZF+RAAAA'.
     'B3RJTUUH0wMLFTsY/ewvBQAAAW1JREFUeJytkz2u4jAUhT/jic'.
     'gfBUKiZhE0bIKeVbCWrIKenp6eDiGlCEEEBArIxvzGU4xeZjLk'.
     'jWb05lRXuvbx+exr4bouX1Xjyw7Atz81F4uFBYjjGIDhcCjq1o'.
     'k6nN1uZwFerxfP55Msy1itVmRZBsB4PK6YveHkeW5d18XzPIIg'.
     'wPd9Wq0WnU6HMAxJkoQoiuynOIfDwUopkVIihKAoCgAcx6Hdbm'.
     'OMIU1T5vN55eBKEikljUYDIX6kFUKU9e8aDAZlmjcca+1b7TgO'.
     '1+uVy+VS9nzfr8e53++VzdZaiqIgz3OMMWitOZ/PaK0JgqDeRC'.
     'mF53lIKYGfr3O73TDGoJQiTVO01nS73XqT4/FIs9kkCAIej0eZ'.
     'brPZEMcxSZKgtQZgMpmIWpN+vy+m06n1PK9yTx8Gy+WS/X5Pr9'.
     'er9GuHLYoiG4YhSilOpxPr9Zrtdlti/JriU5MPjUYjq7UuEWaz'.
     '2d+P/b/qv/zi75oetJcv7QQXAAAAAElFTkSuQmCC' ; 


        //==========================================================
        // File: bs_cyan.png
        //==========================================================
        $this->imgdata_small[12][0]= 633 ;
        $this->imgdata_small[12][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAABPl'.
     'BMVEX////////F///AwMCvxsaC1NSC0dGCz8+CzMyA//94//91'.
     '//9q//9j//9X4uJX09NXz89Xx8dXxMRL//9L5uZL3d1L2NhLxs'.
     'ZLt7cv//8e9fUe8fEe7u4e398epqYehoYX//8L+PgK//8F9fUE'.
     '/v4E5+cEb28EZ2cC//8C/v4C/f0CzMwCrq4Cjo4CdXUCaWkCZW'.
     'UB/PwA//8A/f0A+/sA8/MA7e0A7OwA6+sA5eUA5OQA4uIA4eEA'.
     '3NwA2toA2NgA1dUA09MA0tIA0NAAysoAxsYAxcUAxMQAv78Avr'.
     '4AvLwAtrYAtbUAs7MAsLAAra0Aq6sAqKgApaUApKQAoqIAoKAA'.
     'n58AmpoAlZUAk5MAkpIAkJAAj48AjIwAiYkAh4cAf38AfX0Ae3'.
     'sAenoAcnIAcHAAa2sAaWkAaGgAYmIUPEuTAAAAAXRSTlMAQObY'.
     'ZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAA'.
     'AHdElNRQfTAwkUGQDi+VPPAAAAtElEQVR4nGNgIBawikipyIiy'.
     'wfksfJpGRkamNtr8LFARPiMFHmFDcztXfwGoFi0jLiZuZRtnry'.
     'BddrCIiJEGL6eklYO7X3iCOFhE2thESdHawdUnJDZFDiyiamZh'.
     'aevk5h0UlZSpBhaRtbN3dPHwDY5MSM+EqBFzc/f0DgiLTkjLzI'.
     'SYw6bjHxgaEZeckZmpD7GLQSAqJj4xNRMIBGFuFtRLA/ENhGBu'.
     'ZmDgkJBXl5fgIDocAAKcINaFePT4AAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bs_bluegreen.png
        //==========================================================
        $this->imgdata_small[13][0]= 493 ;
        $this->imgdata_small[13][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAAvV'.
     'BMVEX///////+/v79j//855/8x3v851v9Spb1C1v8AOUqEtcZK'.
     'lK1StdYxzv8hxv8AY4QASmNSlK1KpcZKtd4YQlIYnM4YrecIvf'.
     '8AtfcAre8AjL0AhLUAc5wAa5QAWnsAQloAKTkAGCFKhJxKrdYY'.
     'jL0Ypd4Atf8ArfcApecAnN4AlM4AjMYAe60Ac6UAY4wAUnNSnL'.
     '0AlNYAWoQASmsAOVIAITGEtc4YWnsAUnsAMUqtvcaErcYAKUIA'.
     'GCkAECHUyVh/AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAA'.
     'AJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGxNUcXCT'.
     'AAAAqUlEQVR4nI2Q1xKCMBREM2NHLCCogAGCjd6SqLT8/2cZKT'.
     '6zb3tm987OBWCsXoejp8rC35fi4+l6gXFZlD0Rz6fZ1tdDmKR9'.
     'RdOmkzmP7DDpilfX3SzvRgQ/Vr1uiZplfsCBiVf03RJd140wgj'.
     'kmNqMtuYXcxyYmNWJdRoYwzpM9qRvGujuCmSR7q7ARY00/MiWk'.
     'sCnjkobNEm1+HknDZgAqR0GKU43+wxdu2hYzbsHU6AAAAABJRU'.
     '5ErkJggg==' ; 

        //==========================================================
        // File: bs_lightred.png
        //==========================================================
        $this->imgdata_small[14][0]= 532 ;
        $this->imgdata_small[14][1]=
     'iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAMAAAAMs7fIAAAA3l'.
     'BMVEX///////+/v7/Gvb0hGBj/5///3v//zu//1u//xucpGCG9'.
     'nK21lKVSQkp7Wms5KTExISlaOUpjQlIhEBj/tdbOhKXnrcbGjK'.
     'Wla4TetcbGnK2EWmv/rc73pcZ7UmOcY3vOpbW1jJzenLW9e5Rz'.
     'Slq1c4xrQlJSOULGhJz/pcb3nL2chIzOnK33rcbelK3WjKWMWm'.
     'vGe5SEUmM5ISnOtb3GrbXerb3vpb2ca3v/rcaUY3POhJxCKTF7'.
     'SlrWnK21e4ytc4TvnLXnlK2la3taOUK1lJxrSlLGhJRjQkpSMT'.
     'lw+q2nAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ'.
     'cwAACxIAAAsSAdLdfvwAAAAHdElNRQfTAwkUGjoP2Nm+AAAAr0'.
     'lEQVR4nGNgIBaYiOk62imYwPnMkiIyso76yhJSzFARMxkRNk49'.
     'a3t5OW6oFk1LVkYOfWUHKxUXiEYzLS12DnN3VXkjIRtFsIiSk5'.
     '6evqGqhYGKugAfWMRa1FpD2UHeQEXQRlgALCJur+rgbCUNFOAS'.
     'hqjRkZe3MpBTcwEKCEPMMTGSs3Xz8OQHCnBBHckt6OJpIyAMBD'.
     'wwN/MYc4H4LK4wNzMwmGrzcvFqmxIdDgDiHRT6VVQkrAAAAABJ'.
     'RU5ErkJggg==' ;

        //==========================================================
        // File: bxs_lightred.png
        //==========================================================
        $this->imgdata_xsmall[0][0]= 432 ;
        $this->imgdata_xsmall[0][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAA3l'.
     'BMVEX///////+/v7/Gvb0hGBj/5///3v//zu//1u//xucpGCG9'.
     'nK21lKVSQkp7Wms5KTExISlaOUpjQlIhEBj/tdbOhKXnrcbGjK'.
     'Wla4TetcbGnK2EWmv/rc73pcZ7UmOcY3vOpbW1jJzenLW9e5Rz'.
     'Slq1c4xrQlJSOULGhJz/pcb3nL2chIzOnK33rcbelK3WjKWMWm'.
     'vGe5SEUmM5ISnOtb3GrbXerb3vpb2ca3v/rcaUY3POhJxCKTF7'.
     'SlrWnK21e4ytc4TvnLXnlK2la3taOUK1lJxrSlLGhJRjQkpSMT'.
     'lw+q2nAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ'.
     'cwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAwkUKBOgGhWjAAAAS0'.
     'lEQVR4nGNgQAEmunYmEJaMCKe1vBxYzJKVQ9lKBSSupKdnaKGi'.
     'zgdkiqs6WKnYcIGYJnK2HvzCwmCNgi42wsLCECNMeXlNUY0HAL'.
     'DaB7Du8MiEAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bxs_bluegreen.png
        //==========================================================
        $this->imgdata_xsmall[1][0]= 397 ;
        $this->imgdata_xsmall[1][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAvV'.
     'BMVEX///////+/v79j//855/8x3v851v9Spb1C1v8AOUqEtcZK'.
     'lK1StdYxzv8hxv8AY4QASmNSlK1KpcZKtd4YQlIYnM4YrecIvf'.
     '8AtfcAre8AjL0AhLUAc5wAa5QAWnsAQloAKTkAGCFKhJxKrdYY'.
     'jL0Ypd4Atf8ArfcApecAnN4AlM4AjMYAe60Ac6UAY4wAUnNSnL'.
     '0AlNYAWoQASmsAOVIAITGEtc4YWnsAUnsAMUqtvcaErcYAKUIA'.
     'GCkAECHUyVh/AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAA'.
     'AJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAwkUKDVyF5Be'.
     'AAAASUlEQVR4nGNgQAFmYqJcEJaEOJ+UrD5YTJKFTZrfGCQuaq'.
     'glLWvMaQ5kqujo6hnbKIKYXPr68gp2dmCNJiZAlh3ECGsREWtU'.
     '4wF1kwdpAHfnSwAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bxs_navy.png
        //==========================================================
        $this->imgdata_xsmall[2][0]= 353 ;
        $this->imgdata_xsmall[2][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAk1'.
     'BMVEX///////+/v7+trcbGxv+EhM6EhNaEhP97e/9zc/9ra/9S'.
     'UsZKSrVSUs5jY/9SUtZKSsZSUudKSt5KSudKSv8YGIQpKf8YGK'.
     'UYGN4YGO8YGPcQEP8ICP8AAP8AAPcAAO8AAOcAAN4AANYAAM4A'.
     'AMYAAL0AALUAAK0AAKUAAJwAAJQAAIwAAIQAAHsAAHMAAGsAAG'.
     'ONFkFbAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ'.
     'cwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAwkUJxXO4axZAAAAR0'.
     'lEQVR4nGNgQAGskhKsEJaslIi8ijpYTJaDU1FVAyQuKSujoKKh'.
     'LQ5kSigpqWro6oOYrOoaWroGBmCNWiCWAdQwUVFWVOMBOp4GCJ'.
     's5S60AAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bxs_gray.png
        //==========================================================
        $this->imgdata_xsmall[3][0]= 492 ;
        $this->imgdata_xsmall[3][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAABI1'.
     'BMVEX///8AAAD8EAD8IAD8NAD8RAD8VAAYGBi/v7+goKCCgoJk'.
     'ZGRGRkb8yAD83AD87AD8/AD4+ADo+ADY+ADI+AC0+ACk+ACU+A'.
     'CE+AB0/ABk/ABU/ABE/AAw/AAg/AAQ/AAA/AAA+AAA6BAA2CAA'.
     'yDQAtEQApFQAlGQAhHQAdIgAZJgAVKgARLgAMMgAINwAEOwAAP'.
     'wAAPgIAPAQAOgYAOAkANgsANA0AMg8AMBEALhMALBUAKhcAKBo'.
     'AJhwAJB4AIiAAID////4+Pjy8vLs7Ozm5ubg4ODa2trT09PNzc'.
     '3Hx8fBwcG7u7u1tbWurq6oqKiioqKcnJyWlpaQkJCJiYmDg4N9'.
     'fX13d3dxcXFra2tkZGReXl5YWFhSUlJMTExGRkZAQEA1BLn4AA'.
     'AAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxEA'.
     'AAsRAX9kX5EAAAAHdElNRQfTAwkUKC74clmyAAAAQklEQVR4nG'.
     'NgQAVBYVCGt5dXYEQ0mOnp5h4QFgVmeri6+4dHxYMVeHoFRUTH'.
     'gTUFBIZBWAwMkZEx8bFQM2Lj0UwHANc/DV6yq/BiAAAAAElFTk'.
     'SuQmCC' ; 

        //==========================================================
        // File: bxs_graypurple.png
        //==========================================================
        $this->imgdata_xsmall[4][0]= 542 ;
        $this->imgdata_xsmall[4][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAABSl'.
     'BMVEX////////11P/MqdvKrNfAwMC+u7+9u7+4rr24lsi3rby3'.
     'lMe1rLq1o720q7i0oL20ksSzoryyqbaykMGxlb2wkL+vnbiujb'.
     '2sjLuri7qpl7GoirWoibenmK2mla6mjLKmhrSllauki7CjhrCj'.
     'hLGihLChg6+ggq2fkqadkKOcfqqai6Gag6WYe6WXeqSWeaOTd6'.
     'CTd5+Rdp6RdZ6RdZ2Qg5eOc5qMcpiLcZeJb5WIbpOHbZKGbJGE'.
     'a4+CaY2AZ4t/Z4p/Zop/Zol+Zol7ZIZ6Y4V5YoR1ZH11X391Xn'.
     '9zXX1yXXtxXHtvWnluWXhsV3VqVnNpVXJoVHFnU3BmUm9jUGth'.
     'VGdgTmheTGZcS2RcSmRaSWJYR19XRl5SQllRQlhQQVdPQFZOP1'.
     'VLPlFJO09IPE5IOk5FOEtEN0lDOEpDOElDNklCNkc/M0XhbrfD'.
     'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACx'.
     'EAAAsRAX9kX5EAAAAHdElNRQfTAwkUKCgREfyHAAAATUlEQVR4'.
     'nGNgQAEcIko8EBY3M5Ougy+IxSXMwmTsFsAHZMqrSRvZB0W7A5'.
     'k6FlYugXEZICaPr394Um4uSAFDRFRCbm4uxAihsDAhVOMBHT0L'.
     'hkeRpo8AAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bxs_red.png
        //==========================================================
        $this->imgdata_xsmall[5][0]= 357 ;
        $this->imgdata_xsmall[5][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAk1'.
     'BMVEX////////GxsbGra3/xsbOhITWhIT/hIT/e3v/c3P/a2vG'.
     'UlK1SkrOUlL/Y2PWUlLGSkrnUlLeSkrnSkr/SkqEGBj/KSmlGB'.
     'jeGBjvGBj3GBj/EBD/CAj/AAD3AADvAADnAADeAADWAADOAADG'.
     'AAC9AAC1AACtAAClAACcAACUAACMAACEAAB7AABzAABrAABjAA'.
     'BuukXBAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZ'.
     'cwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAwkUIyjy5SVMAAAAS0'.
     'lEQVR4nGNgQAFsUpJsEJastIi8ijpYTJaDU0FVgxXIlJKVUVDR'.
     '0BYHMiUUlVQ1dPVBTDZ1dS1dAwOQAgYtbSDLAGIEq6goK6rxAD'.
     'yXBg73lwGUAAAAAElFTkSuQmCC' ; 

        //==========================================================
        // File: bxs_yellow.png
        //==========================================================
        $this->imgdata_xsmall[6][0]= 414 ;
        $this->imgdata_xsmall[6][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAzF'.
     'BMVEX///////+/v79zYwCMewDOxoTWzoTezkr/5wj/5wDnzgDe'.
     'xgC1pQCtnACllACcjACUhABjWgDGvVK1rUrOxlLGvUqEexilnB'.
     'jv3hj35xj/7wj/7wD35wDv3gDn1gDezgDWxgDOvQDGtQC9rQCE'.
     'ewB7cwBzawBrYwDWzlLn3lLe1krn3kre1hi9tQC1rQCtpQClnA'.
     'CclACUjACMhAD/9wC/v7///8bOzoT//4T//3v//3P//2v//2Pn'.
     '50r//0r//yn39xj//xD//wBjYwDO8noaAAAAAXRSTlMAQObYZg'.
     'AAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAH'.
     'dElNRQfTAwkUIzoBXFQEAAAAS0lEQVR4nGNgQAFsDhJsEJaTo5'.
     '2skj5YzMnSSk7ZwBzIlOSUklPiMxYHMnW4FXT5VNVBTDZeXiNV'.
     'QUGQAgYBYyBLEGIEq5gYK6rxAH4kBmHBaMQQAAAAAElFTkSuQm'.
     'CC' ; 

        //==========================================================
        // File: bxs_greenblue.png
        //==========================================================
        $this->imgdata_xsmall[7][0]= 410 ;
        $this->imgdata_xsmall[7][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAxl'.
     'BMVEX///////+/v79znJQhSkJ7raU5hHtjraVKnJRCjIRClIyU'.
     '9++E595avbVaxr2/v7+ctbWcvb17nJxrjIx7paUxQkK9//+Mvb'.
     '17ra2Evb17tbVCY2MQGBiU5+ec9/eM5+d71tZanJxjra1rvb1j'.
     'tbVSnJxara1rzs5jxsZKlJRChIQpUlIhQkJatbVSpaU5c3MxY2'.
     'MYMTEQISFavb1Sra1KnJxCjIw5e3sxa2spWlpClJQhSkoYOTkp'.
     'Y2MhUlIQKSkIGBgQMTH+e30mAAAAAXRSTlMAQObYZgAAAAFiS0'.
     'dEAIgFHUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElNRQfT'.
     'AwkUJy5/6kV9AAAATUlEQVR4nGNgQAGCyuyCEJaGugKHviVYzF'.
     'hO3sxCWwDIVNLTM9PXtpEGMhW12Cy0DR1ATEFLSxZ7BweQAgYd'.
     'HUMHBweIEQKiogKoxgMAo/4H5AfSehsAAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bxs_purple.png
        //==========================================================
        $this->imgdata_xsmall[8][0]= 364 ;
        $this->imgdata_xsmall[8][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAnF'.
     'BMVEX///////+/v7/Gvca9rb3Grcb/xv+1hLWte629hL21e7XG'.
     'hMbWhNbOe87We9b/hP//e/97OXv/c///a///Y/+cOZz/Sv/WOd'.
     'bnOefvOe//Kf9jCGNrCGv/EP//CP/nCOf/AP/3APfvAO/nAOfe'.
     'AN7WANbOAM7GAMa9AL21ALWtAK2lAKWcAJyUAJSMAIyEAIR7AH'.
     'tzAHNrAGtjAGPP1sZnAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgF'.
     'HUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAwkUIj'.
     'mBTjT/AAAASUlEQVR4nGNgQAGskhKsEJaCrJiSuhZYTEFASFlD'.
     'GyQuqSCnrK6tJwpkiquoamgbGIGYrFpaugbGxmCNunpAljHECB'.
     'ZBQRZU4wFSMAZsXeM71AAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bxs_green.png
        //==========================================================
        $this->imgdata_xsmall[9][0]= 370 ;
        $this->imgdata_xsmall[9][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAn1'.
     'BMVEX///////+/v7+/v7/G/8aUxpSMvYyUzpSMzoyM1oxarVqE'.
     '/4R7/3tavVpKnEpaxlpz/3Nr/2tKtUpj/2Na51pKzkpK1kpK50'.
     'pK/0oYcxgp/ykYlBgY3hgY7xgY9xgQ/xAI/wgA/wAA9wAA7wAA'.
     '5wAA3gAA1gAAzgAAxgAAvQAAtQAArQAApQAAnAAAlAAAjAAAhA'.
     'AAewAAcwAAawAAYwA0tyxUAAAAAXRSTlMAQObYZgAAAAFiS0dE'.
     'AIgFHUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAw'.
     'kUKBrZxq0HAAAATElEQVR4nGNgQAGccrIcEJaivISyhjaIxa7I'.
     'I6CiqcMKZMopKqho6OhLA5kyqmqaOobGICartraeoYkJSAGDnj'.
     '6QZQIxgk1Skg3VeABlVgbItqEBUwAAAABJRU5ErkJggg==' ; 

        //==========================================================
        // File: bxs_darkgreen.png
        //==========================================================
        $this->imgdata_xsmall[10][0]= 563 ;
        $this->imgdata_xsmall[10][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAABX1'.
     'BMVEX////////l/+nAwMC86r+8wb28wby8wLy78sCzw7SywrSx'.
     'wLKwvrGuvK+syK+ryq2rx62n36ym3aumxKmk2qij0Keh16ahva'.
     'Og1aSguKKe06KeuaCetZ+d0KGdtZ+bz6Cay56ZyZ2Zwp2Zr5qZ'.
     'rpqYwJuXyZuXrJmVw5mUxZiTxJeTw5eTq5WRwJWPtJKOvZKKuI'.
     '6Kt42Kn4yJt42ItIuGsomFsYmEsIiEr4eDr4eBrIR/qoN+qIJ8'.
     'poB7pH56o356on14nnt2nXl0mndzmnZzmXZymHVwlXNvlHJukn'.
     'FtiHBqjm1qjW1oi2toiWpniWplh2hlhmdkhWdig2VggGNgf2Je'.
     'fmFdfGBde19bbl1aeFxXdFpWclhVclhVcVdUcFZTb1VSbVRQal'.
     'JPaVFKY0xKYkxJYUtIYEpHX0lEWkZCWERCV0NCVkM/U0A+U0A+'.
     'UUA+UEA9Uj89UT48Tj45TDvewfrHAAAAAXRSTlMAQObYZgAAAA'.
     'FiS0dEAIgFHUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElN'.
     'RQfTAwkUKCFozUQjAAAATUlEQVR4nGNgQAGcoqrcEJYQB5OhSw'.
     'CIxSXGwWThGcIDZCppK5o7hyV6AZl6NnbuoSmFICZ3YHB0RkkJ'.
     'SAFDbEJaSUkJxAjeyEheVOMBQj4MOEkWew4AAAAASUVORK5CYI'.
     'I=' ; 

        //==========================================================
        // File: bxs_cyan.png
        //==========================================================
        $this->imgdata_xsmall[11][0]= 530 ;
        $this->imgdata_xsmall[11][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAABPl'.
     'BMVEX////////F///AwMCvxsaC1NSC0dGCz8+CzMyA//94//91'.
     '//9q//9j//9X4uJX09NXz89Xx8dXxMRL//9L5uZL3d1L2NhLxs'.
     'ZLt7cv//8e9fUe8fEe7u4e398epqYehoYX//8L+PgK//8F9fUE'.
     '/v4E5+cEb28EZ2cC//8C/v4C/f0CzMwCrq4Cjo4CdXUCaWkCZW'.
     'UB/PwA//8A/f0A+/sA8/MA7e0A7OwA6+sA5eUA5OQA4uIA4eEA'.
     '3NwA2toA2NgA1dUA09MA0tIA0NAAysoAxsYAxcUAxMQAv78Avr'.
     '4AvLwAtrYAtbUAs7MAsLAAra0Aq6sAqKgApaUApKQAoqIAoKAA'.
     'n58AmpoAlZUAk5MAkpIAkJAAj48AjIwAiYkAh4cAf38AfX0Ae3'.
     'sAenoAcnIAcHAAa2sAaWkAaGgAYmIUPEuTAAAAAXRSTlMAQObY'.
     'ZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxEAAAsRAX9kX5EAAA'.
     'AHdElNRQfTAwkUKQFKuFWqAAAATUlEQVR4nGNgQAGsUjJsEJaR'.
     'grC5qz9YzIiL28YriB3IlDZRsnYNiZUDMmXtHT2CE9JBTDb/wI'.
     'jkzEyQAoaomMTMzEyIERzy8hyoxgMAN2MLVPW0f4gAAAAASUVO'.
     'RK5CYII=' ; 

        //==========================================================
        // File: bxs_orange.png
        //==========================================================
        $this->imgdata_xsmall[12][0]= 572 ;
        $this->imgdata_xsmall[12][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAABaF'.
     'BMVEX//////////8X/3oD/3nj/1HX/0Gr/xGP/rkv/gBf+iS/2'.
     'bAL1agDxaQDuZwDrZwLpZQDmZQLlZADjcx7gZATeYQDdZgraXw'.
     'DZXwHYXgDXiEvXZAvUjlfUXwXTjVfTbR7ShUvRbR7RWwDMWQDL'.
     'WADKooLKWADJoYLJgkvHWATGoILFn4LFgEvFVgDEZx7EVQDDt6'.
     '/DVQDCt6/CnoLChlfCVADAwMC+hFe+UgC8UgC6UQC4gVe4UAC3'.
     'gVe3UAC1gFe1eUu1TwC1TgCzTgCwTQKuTACrSgCqSgCpSgCpSQ'.
     'CodEulSACkRwCiRgCdRACcRACaQwCYQgCWQgKVQQCVQACUQACS'.
     'UR6RPwCOPgCNPQCLPACKPACJOwCEOQCBOAB+NwB9NgB8NgB7NQ'.
     'B6NwJ4NAB3RR52MwB0MgBuLwBtLwBsLwBqLgBpLQBkLQJiKgBh'.
     'KgBgKwRcKABbKQJbJwBaKQRaJwBYKAJVJQDZvdIYAAAAAXRSTl'.
     'MAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxEAAAsRAX9k'.
     'X5EAAAAHdElNRQfTAwkUJBSSy88MAAAATUlEQVR4nGNgQAGqwo'.
     'paEBYPJ4eKezCIpc7HwmrqG6ENZMpLihm6RaWEAZl6Vo7ekRnF'.
     'IKZWSHhcTnk5SAFDfFJWeXk5xAjj1FRjVOMBeFwNcWYSLjsAAA'.
     'AASUVORK5CYII=' ; 

        //==========================================================
        // File: bxs_lightblue.png
        //==========================================================
        $this->imgdata_xsmall[13][0]= 554 ;
        $this->imgdata_xsmall[13][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAABVl'.
     'BMVEX////////d///AwMC7wcS08P+y+P+xxdCwxM+uws2twMur'.
     'vsinzNynytylzuKhyN6e5v6d5P+d1fOcwNWcu8ub4f+at8iZ3v'.
     '+ZvdGY2/yW2f+VscGU1vuT1fqTr72Sx+SSxeKR0fWRz/GPz/OP'.
     'rr+OyeqMy+6Myu2LyeyKxueJudSGw+SGorGDvt+Cvd6CvN2Aud'.
     'p+uNd+t9Z9tdV8tdR8tNN6sc94r813rct2q8h0qcZ0qMVzp8Rx'.
     'o8Bwor5tn7ptnrptnrlsnbhqmbRpmbNpi51ol7Flkqtkkqtkka'.
     'pjj6hijaRhjaZgi6NfiqJfiaFdh55bhJtag5pZgphYgJZYf5VX'.
     'cn9Ve5FSeI1RdopRdYlQdYlPc4dPcoZPcoVNcINLboBLbH9GZn'.
     'hGZXdFZHZEY3RDYnJCXW4/W2s/WWg+Wmo7VmU7VGM7U2E6VGM6'.
     'VGI5UV82T1wGxheQAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHU'.
     'gAAAAJcEhZcwAACxEAAAsRAX9kX5EAAAAHdElNRQfTAwkUJziL'.
     'PvAsAAAATUlEQVR4nGNgQAHsQgqcEJYgG5Oegy+IxSHOxmTiFs'.
     'gFZMprKBnbB8e7AplaFlbOQUl5ICanX0BEWmEhSAFDVGxKYWEh'.
     'xAjusDBuVOMBJO8LrFHRAykAAAAASUVORK5CYII=' ; 

        //==========================================================
        // File: bxs_darkgray.png
        //==========================================================
        $this->imgdata_xsmall[14][0]= 574 ;
        $this->imgdata_xsmall[14][1]=
     'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABm'.
     'JLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsRAAALEQF/ZF+RAAAB'.
     'iElEQVR42k3QPU8TYRwA8P//ebkXrgdIColXRAOEkJqbaExMut'.
     'DBhE1GNjYHPg+DG6ODiU6QOLjVxITBcFKBYCstlAC2Bz17fe76'.
     'vLD6+wg/1FpTRFR5lpaub/u1eGBGaAT4HneD4OlXx7avtDYUjT'.
     'HQabd2Ti8e3vVSKzxrtHS32wIpFVldno22Nqvvg2Bhl0gp/aNm'.
     'vJ3qqXAtLIva+ks1H0wqlSXi4+d6+OFTfRsAfHJx2d1od24rZP'.
     'xP2HzopINr1mkesX7ccojqif0v9crxWXODZTno3+dNGA7uWLsd'.
     'mUYU4fHJCViMG9umLBmM4L6fagZGg9QKfjZ+Qfy3C3G/B3mugF'.
     'IHHNcDf64E3KJALApk2p8CSolUUqLjFkyxOGMsTtFyJ+Wz57NQ'.
     '8DghS4sLB0svioeZZo7nPhFoUKZDIVFbglkTTnl5/rC8snjAkJ'.
     'Bk/XV5LxHC/v7tR8jzTFPbg8LENK9WX0Vv31T2AEmCSmlKCCoh'.
     'ROnP1U1tPFYjJBRcbtzSf+GPsFTAQBq1n4AAAABKdEVYdHNpZ2'.
     '5hdHVyZQBiYzYyMDIyNjgwYThjODMyMmUxNjk0NWUzZjljOGFh'.
     'N2VmZWFhMjA4OTE2ZjkwOTdhZWE1MzYyMjk0MWRkM2I5EqaPDA'.
     'AAAABJRU5ErkJggg==' ; 
    }
}

?>