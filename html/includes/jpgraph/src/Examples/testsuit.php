<?php
//=======================================================================
// File:	TESTSUIT.PHP
// Description:	Run all the example script in current directory
// Created: 	2002-07-11
// Ver:		$Id: testsuit.php,v 1.1.2.1 2004/03/27 12:43:07 aditus Exp $
//
// License:	This code is released under QPL 1.0 
// Copyright (C) 2001,2002 Johan Persson 
//========================================================================

//-------------------------------------------------------------------------
//
// Usage: testsuit.php[?type=1]    Generates all non image map scripts
//        testsuit.php?type=2      Generates client side image map scripts 
//       
//-------------------------------------------------------------------------
class TestDriver {
    
    var $iType;
    var $iDir;

    function TestDriver($aType=1,$aDir='') {
	$this->iType = $aType;
	if( $aDir == '' ) {
	    $aDir = getcwd();
	}
	if( !chdir($aDir) ) {
	    die("PANIC: Can't access directory : $aDir");
	}
	$this->iDir = $aDir;
    }

    function GetFiles() {
	$d = dir($this->iDir);
	$a = array();
	while( $entry=$d->Read() ) {
	    if( strstr($entry,".php") && strstr($entry,"x") && !strstr($entry,"show") && !strstr($entry,"csim") ) {
		$a[] = $entry;
	    }
	}
	$d->Close();
	if( count($a) == 0 ) {
	    die("PANIC: Apache/PHP does not have enough permission to read the scripts in directory: $this->iDir");	    
	}
	sort($a);
	return $a;
    }

    function GetCSIMFiles() {
	$d = dir($this->iDir);
	$a = array();
	while( $entry=$d->Read() ) {
	    if( strstr($entry,".php") && strstr($entry,"csim") ) {
		$a[] = $entry;
	    }
	}
	$d->Close();
	if( count($a) == 0 ) {
	    die("PANIC: Apache/PHP does not have enough permission to read the CSIM scripts in directory: $this->iDir");	    
	}
	sort($a);
	return $a;
    }

    
    function Run() {
	switch( $this->iType ) {
	    case 1:
		$files = $this->GetFiles();
		break;
	    case 2:
		$files = $this->GetCSIMFiles();
		break;
	    default:
		die('Panic: Unknown type of test');
		break;
	}
	$n = count($files);
	echo "<h2>Visual test suit for JpGraph</h2>";
	echo "Testtype: " . ($this->iType==1 ? ' Standard images ':' Image map tests ');
	echo "<br>Number of tests: $n<p>";
	echo "<ol>";
	
	for( $i=0; $i<$n; ++$i ) {
	    if( $this->iType ==1 ) {
	    echo '<li><a href="show-example.php?target='.urlencode($files[$i]).'"><img src="'.$files[$i].'" border=0 align=top></a><br><strong>Filename:</strong> <i>'.basename($files[$i])."</i>\n";
	    }
	    else {
		echo '<li><a href="show-example.php?target='.urlencode($files[$i]).'">'.$files[$i]."</a>\n";
	    }
	}
	echo "</ol>";

	echo "<p>Done.</p>";
    }
}

$type=@$HTTP_GET_VARS['t'];
if( empty($type) ) {
    $type=1;
}

$driver = new TestDriver($type);
$driver->Run();

?>
