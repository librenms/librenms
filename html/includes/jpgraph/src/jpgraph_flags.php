<?php
//=======================================================================
// File:	JPGRAPH_FLAGS.PHP
// Description:	Class Jpfile. Handles plotmarks
// Created: 	2003-06-28
// Ver:		$Id: jpgraph_flags.php 957 2007-12-01 14:00:29Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

//------------------------------------------------------------
// Defines for the different basic sizes of flags
//------------------------------------------------------------
DEFINE('FLAGSIZE1',1);
DEFINE('FLAGSIZE2',2);
DEFINE('FLAGSIZE3',3);
DEFINE('FLAGSIZE4',4);

class FlagImages {

    public $iCountryNameMap = array(
    'Afghanistan' => 'afgh',
    'Republic of Angola' => 'agla',
    'Republic of Albania' => 'alba',
    'Alderney' => 'alde',
    'Democratic and Popular Republic of Algeria' => 'alge',
    'Territory of American Samoa' => 'amsa',
    'Principality of Andorra' => 'andr',
    'British Overseas Territory of Anguilla' => 'angu',
    'Antarctica' => 'anta',
    'Argentine Republic' => 'arge',
    'League of Arab States' => 'arle',
    'Republic of Armenia' => 'arme',
    'Aruba' => 'arub',
    'Commonwealth of Australia' => 'astl',
    'Republic of Austria' => 'aust',
    'Azerbaijani Republic' => 'azer',
    'Bangladesh' => 'bngl',
    'British Antarctic Territory' => 'bant',
    'Kingdom of Belgium' => 'belg',
    'British Overseas Territory of Bermuda' => 'berm',
    'Commonwealth of the Bahamas' => 'bhms',
    'Kingdom of Bahrain' => 'bhrn',
    'Republic of Belarus' => 'blru',
    'Republic of Bolivia' => 'blva',
    'Belize' => 'blze',
    'Republic of Benin' => 'bnin',
    'Republic of Botswana' => 'bots',
    'Federative Republic of Brazil' => 'braz',
    'Barbados' => 'brbd',
    'British Indian Ocean Territory' => 'brin',
    'Brunei Darussalam' => 'brun',
    'Republic of Burkina' => 'bufa',
    'Republic of Bulgaria' => 'bulg',
    'Republic of Burundi' => 'buru',
    'Overseas Territory of the British Virgin Islands' => 'bvis',
    'Central African Republic' => 'cafr',
    'Kingdom of Cambodia' => 'camb',
    'Republic of Cameroon' => 'came',
    'Dominion of Canada' => 'cana',
    'Caribbean Community' => 'cari',
    'Republic of Cape Verde' => 'cave',
    'Republic of Chad' => 'chad',
    'Republic of Chile' => 'chil',
    'Peoples Republic of China' => 'chin',
    'Territory of Christmas Island' => 'chms',
    'Commonwealth of Independent States' => 'cins',
    'Cook Islands' => 'ckis',
    'Republic of Colombia' => 'clmb',
    'Territory of Cocos Islands' => 'cois',
    'Commonwealth' => 'comn',
    'Union of the Comoros' => 'como',
    'Republic of the Congo' => 'cong',
    'Republic of Costa Rica' => 'corc',
    'Republic of Croatia' => 'croa',
    'Republic of Cuba' => 'cuba',
    'British Overseas Territory of the Cayman Islands' => 'cyis',
    'Republic of Cyprus' => 'cypr',
    'The Czech Republic' => 'czec',
    'Kingdom of Denmark' => 'denm',
    'Republic of Djibouti' => 'djib',
    'Commonwealth of Dominica' => 'domn',
    'Dominican Republic' => 'dore',
    'Republic of Ecuador' => 'ecua',
    'Arab Republic of Egypt' => 'egyp',
    'Republic of El Salvador' => 'elsa',
    'England' => 'engl',
    'Republic of Equatorial Guinea' => 'eqgu',
    'State of Eritrea' => 'erit',
    'Republic of Estonia' => 'estn',
    'Ethiopia' => 'ethp',
    'European Union' => 'euun',
    'British Overseas Territory of the Falkland Islands' => 'fais',
    'International Federation of Vexillological Associations' => 'fiav',
    'Republic of Fiji' => 'fiji',
    'Republic of Finland' => 'finl',
    'Territory of French Polynesia' => 'fpol',
    'French Republic' => 'fran',
    'Overseas Department of French Guiana' => 'frgu',
    'Gabonese Republic' => 'gabn',
    'Republic of the Gambia' => 'gamb',
    'Republic of Georgia' => 'geor',
    'Federal Republic of Germany' => 'germ',
    'Republic of Ghana' => 'ghan',
    'Gibraltar' => 'gibr',
    'Hellenic Republic' => 'grec',
    'State of Grenada' => 'gren',
    'Overseas Department of Guadeloupe' => 'guad',
    'Territory of Guam' => 'guam',
    'Republic of Guatemala' => 'guat',
    'The Bailiwick of Guernsey' => 'guer',
    'Republic of Guinea' => 'guin',
    'Republic of Haiti' => 'hait',
    'Hong Kong Special Administrative Region' => 'hokn',
    'Republic of Honduras' => 'hond',
    'Republic of Hungary' => 'hung',
    'Republic of Iceland' => 'icel',
    'International Committee of the Red Cross' => 'icrc',
    'Republic of India' => 'inda',
    'Republic of Indonesia' => 'indn',
    'Republic of Iraq' => 'iraq',
    'Republic of Ireland' => 'irel',
    'Organization of the Islamic Conference' => 'isco',
    'Isle of Man' => 'isma',
    'State of Israel' => 'isra',
    'Italian Republic' => 'ital',
    'Jamaica' => 'jama',
    'Japan' => 'japa',
    'The Bailiwick of Jersey' => 'jers',
    'Hashemite Kingdom of Jordan' => 'jord',
    'Republic of Kazakhstan' => 'kazk',
    'Republic of Kenya' => 'keny',
    'Republic of Kiribati' => 'kirb',
    'State of Kuwait' => 'kuwa',
    'Kyrgyz Republic' => 'kyrg',
    'Republic of Latvia' => 'latv',
    'Lebanese Republic' => 'leba',
    'Kingdom of Lesotho' => 'lest',
    'Republic of Liberia' => 'libe',
    'Principality of Liechtenstein' => 'liec',
    'Republic of Lithuania' => 'lith',
    'Grand Duchy of Luxembourg' => 'luxe',
    'Macao Special Administrative Region' => 'maca',
    'Republic of Macedonia' => 'mace',
    'Republic of Madagascar' => 'mada',
    'Republic of the Marshall Islands' => 'mais',
    'Republic of Mali' => 'mali',
    'Federation of Malaysia' => 'mals',
    'Republic of Malta' => 'malt',
    'Republic of Malawi' => 'malw',
    'Overseas Department of Martinique' => 'mart',
    'Islamic Republic of Mauritania' => 'maur',
    'Territorial Collectivity of Mayotte' => 'mayt',
    'United Mexican States' => 'mexc',
    'Federated States of Micronesia' => 'micr',
    'Midway Islands' => 'miis',
    'Republic of Moldova' => 'mold',
    'Principality of Monaco' => 'mona',
    'Republic of Mongolia' => 'mong',
    'British Overseas Territory of Montserrat' => 'mont',
    'Kingdom of Morocco' => 'morc',
    'Republic of Mozambique' => 'moza',
    'Republic of Mauritius' => 'mrts',
    'Union of Myanmar' => 'myan',
    'Republic of Namibia' => 'namb',
    'North Atlantic Treaty Organization' => 'nato',
    'Republic of Nauru' => 'naur',
    'Turkish Republic of Northern Cyprus' => 'ncyp',
    'Netherlands Antilles' => 'nean',
    'Kingdom of Nepal' => 'nepa',
    'Kingdom of the Netherlands' => 'neth',
    'Territory of Norfolk Island' => 'nfis',
    'Federal Republic of Nigeria' => 'ngra',
    'Republic of Nicaragua' => 'nica',
    'Republic of Niger' => 'nigr',
    'Niue' => 'niue',
    'Commonwealth of the Northern Mariana Islands' => 'nmar',
    'Province of Northern Ireland' => 'noir',
    'Nordic Council' => 'nord',
    'Kingdom of Norway' => 'norw',
    'Territory of New Caledonia and Dependencies' => 'nwca',
    'New Zealand' => 'nwze',
    'Organization of American States' => 'oast',
    'Organization of African Unity' => 'oaun',
    'International Olympic Committee' => 'olym',
    'Sultanate of Oman' => 'oman',
    'Islamic Republic of Pakistan' => 'paks',
    'Republic of Palau' => 'pala',
    'Independent State of Papua New Guinea' => 'pang',
    'Republic of Paraguay' => 'para',
    'Republic of Peru' => 'peru',
    'Republic of the Philippines' => 'phil',
    'British Overseas Territory of the Pitcairn Islands' => 'piis',
    'Republic of Poland' => 'pola',
    'Republic of Portugal' => 'port',
    'Commonwealth of Puerto Rico' => 'purc',
    'State of Qatar' => 'qata',
    'Russian Federation' => 'russ',
    'Romania' => 'rmna',
    'Republic of Rwanda' => 'rwan',
    'Kingdom of Saudi Arabia' => 'saar',
    'Republic of San Marino' => 'sama',
    'Nordic Sami Conference' => 'sami',
    'Sark' => 'sark',
    'Scotland' => 'scot',
    'Principality of Seborga' => 'sebo',
    'Republic of Serbia' => 'serb',
    'Republic of Sierra Leone' => 'sile',
    'Republic of Singapore' => 'sing',
    'Republic of Korea' => 'skor',
    'Republic of Slovenia' => 'slva',
    'Somali Republic' => 'smla',
    'Republic of Somaliland' => 'smld',
    'Republic of South Africa' => 'soaf',
    'Solomon Islands' => 'sois',
    'Kingdom of Spain' => 'span',
    'Secretariat of the Pacific Community' => 'spco',
    'Democratic Socialist Republic of Sri Lanka' => 'srla',
    'Saint Lucia' => 'stlu',
    'Republic of the Sudan' => 'suda',
    'Republic of Suriname' => 'surn',
    'Slovak Republic' => 'svka',
    'Kingdom of Sweden' => 'swdn',
    'Swiss Confederation' => 'swit',
    'Syrian Arab Republic' => 'syra',
    'Kingdom of Swaziland' => 'szld',
    'Republic of China' => 'taiw',
    'Taiwan' => 'taiw',
    'Republic of Tajikistan' => 'tajk',
    'United Republic of Tanzania' => 'tanz',
    'Kingdom of Thailand' => 'thal',
    'Autonomous Region of Tibet' => 'tibe',
    'Turkmenistan' => 'tkst',
    'Togolese Republic' => 'togo',
    'Tokelau' => 'toke',
    'Kingdom of Tonga' => 'tong',
    'Tristan da Cunha' => 'trdc',
    'Tromelin' => 'tris',
    'Republic of Tunisia' => 'tuns',
    'Republic of Turkey' => 'turk',
    'Tuvalu' => 'tuva',
    'United Arab Emirates' => 'uaem',
    'Republic of Uganda' => 'ugan',
    'Ukraine' => 'ukrn',
    'United Kingdom of Great Britain' => 'unkg',
    'United Nations' => 'unna',
    'United States of America' => 'unst',
    'Oriental Republic of Uruguay' => 'urgy',
    'Virgin Islands of the United States' => 'usvs',
    'Republic of Uzbekistan' => 'uzbk',
    'State of the Vatican City' => 'vacy',
    'Republic of Vanuatu' => 'vant',
    'Bolivarian Republic of Venezuela' => 'venz',
    'Republic of Yemen' => 'yemn',
    'Democratic Republic of Congo' => 'zare',
    'Republic of Zimbabwe' => 'zbwe' ) ;


    private $iFlagCount = -1;
    private $iFlagSetMap = array(
	FLAGSIZE1 => 'flags_thumb35x35',
	FLAGSIZE2 => 'flags_thumb60x60',
	FLAGSIZE3 => 'flags_thumb100x100',
	FLAGSIZE4 => 'flags'
	);

    private $iFlagData ;
    private $iOrdIdx=array();

    function FlagImages($aSize=FLAGSIZE1) {
	switch($aSize) {
	    case FLAGSIZE1 :
	    case FLAGSIZE2 :
	    case FLAGSIZE3 :
	    case FLAGSIZE4 :
		$file = dirname(__FILE__).'/'.$this->iFlagSetMap[$aSize].'.dat';
		$fp = fopen($file,'rb');
		$rawdata = fread($fp,filesize($file));
		$this->iFlagData = unserialize($rawdata);
	    break;
	    default:
		JpGraphError::RaiseL(5001,$aSize);
//('Unknown flag size. ('.$aSize.')');
	}
	$this->iFlagCount = count($this->iCountryNameMap);
    }

    function GetNum() {
	return $this->iFlagCount;
    }

    function GetImgByName($aName,&$outFullName) {
	$idx = $this->GetIdxByName($aName,$outFullName);
	return $this->GetImgByIdx($idx);
    }

    function GetImgByIdx($aIdx) {
	if( array_key_exists($aIdx,$this->iFlagData) ) {
	    $d = $this->iFlagData[$aIdx][1];   
	    return Image::CreateFromString($d);   
	}
	else {
	    JpGraphError::RaiseL(5002,$aIdx);
//("Flag index \" $aIdx\" does not exist.");
	}
    }

    function GetIdxByOrdinal($aOrd,&$outFullName) {
	$aOrd--;
	$n = count($this->iOrdIdx);
	if( $n == 0 ) {
	    reset($this->iCountryNameMap);
	    $this->iOrdIdx=array();
	    $i=0;
	    while( list($key,$val) = each($this->iCountryNameMap) ) {
		$this->iOrdIdx[$i++] = array($val,$key);
	    }
	    $tmp=$this->iOrdIdx[$aOrd];
	    $outFullName = $tmp[1];
	    return $tmp[0];
	    
	}
	elseif( $aOrd >= 0 && $aOrd < $n ) {
	    $tmp=$this->iOrdIdx[$aOrd];
	    $outFullName = $tmp[1];
	    return $tmp[0];
	}
	else {
	    JpGraphError::RaiseL(5003,$aOrd);
//('Invalid ordinal number specified for flag index.');
	}
    }

    function GetIdxByName($aName,&$outFullName) {

	if( is_integer($aName) ) {
	    $idx = $this->GetIdxByOrdinal($aName,$outFullName);
	    return $idx;
	}

	$found=false;
	$aName = strtolower($aName);
	$nlen = strlen($aName);
	reset($this->iCountryNameMap);
	// Start by trying to match exact index name
	while( list($key,$val) = each($this->iCountryNameMap) ) {
	    if( $nlen == strlen($val) && $val == $aName )  {
		$found=true;
		break;
	    }
	}
	if( !$found ) {
	    reset($this->iCountryNameMap);
	    // If the exact index doesn't work try a (partial) full name
	    while( list($key,$val) = each($this->iCountryNameMap) ) {
		if( strpos(strtolower($key), $aName) !== false ) {
		    $found=true;
		    break;
		}
	    }
	}
	if( $found ) {
	    $outFullName = $key;
	    return $val;   
	}
	else { 
	    JpGraphError::RaiseL(5004,$aName);
//("The (partial) country name \"$aName\" does not have a cooresponding flag image. The flag may still exist but under another name, e.g. insted of \"usa\" try \"united states\".");
	}
    }
}




?>