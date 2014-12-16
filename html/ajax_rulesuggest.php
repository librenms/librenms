<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Rule Suggestion-AJAX
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS/Alerts
 */

session_start();
if( !isset($_SESSION['authenticated']) ) {
	die("Unauthorized.");
}

require_once("../includes/defaults.inc.php");
require_once("../config.php");
require_once("../includes/definitions.inc.php");
require_once("../includes/functions.php");

/**
 * Levenshtein Sort
 * @param string $base Comparisson basis
 * @param array $obj Object to sort
 * @return array
 */
function levsort($base, $obj) {
	$ret = array();
	foreach( $obj as $elem ) {
		$lev = levenshtein($base, $elem, 1, 10, 10);
		if( $lev == 0 ) {
			return array(array('name'=>$elem));
		} else {
			while( isset($ret["$lev"]) ) {
				$lev += 0.1;
			}
			$ret["$lev"] = array('name'=>$elem);
		}
	}
	ksort($ret);
	return $ret;
}

$obj = array(array('name'=>'Error: No suggestions found.'));
$term = array();
if( isset($_GET['term'],$_GET['device_id']) ) {
	$chk = array();
	$_GET['term'] = mres($_GET['term']);
	$_GET['device_id'] = mres($_GET['device_id']);
        $current == false;
	if( strstr($_GET['term'],".") ) {
		$term = explode(".",$_GET['term']);
		if( $config['memcached']['enable'] ) {
			$chk = $memcache->get('rule-suggest_'.$term[0]);
		}
		if( !(sizeof($chk) > 0) || $chk === false ) {
			$tmp = dbFetchRows('SHOW COLUMNS FROM '.$term[0]);
			foreach( $tmp as $tst ) {
				if( isset($tst['Field']) ) {
					$chk[] = $term[0].'.'.$tst['Field'];
				}
			}
		}
		$current = true;
	} else {
		if( $config['memcached']['enable'] ) {
			$chk = $memcache->get('rule-suggest-toplvl');
		}
		if( !(sizeof($chk) > 0) || $chk === false ) {
			$tmp = dbFetchRows('SHOW TABLES');
			foreach( $tmp as $tst ) {
				$tbl = array_shift($tst);
				$chhk = dbFetchRows('SELECT device_id FROM '.$tbl.' LIMIT 1');
				if( isset($chhk[0]['device_id']) ) {
					$chk[] = $tbl.'.';
				}
			}
		}
	}
	if( sizeof($chk) > 0 ) {
		if( $config['memcached']['enable'] ) {
			$memcache->set('rule-suggest-'.$oterm,$chk,86400); //Cache for 24h
		}
		$obj = levsort($_GET['term'],$chk);
		$obj = array_chunk($obj,20,true);
		$obj = $obj[0];
                $flds = array();
		if( $current == true ) {
			foreach( $obj as $fld ) {
				$flds[] = $fld['name'];
			}
			$qry = dbFetchRows("SELECT ".implode(", ",$flds)." FROM ".$term[0]." WHERE device_id = ?", array($_GET['device_id']));
			foreach( $obj as $lev=>$fld ) {
				list($tbl, $chk) = explode(".",$fld['name']);
				$val = array();
				foreach( $qry as $row ) {
					$val[] = $row[$chk];
				}
				$ret[$lev] = array('name'=>$fld['name'],'current'=>$val);
			}
			$obj = $ret;
		}
	}
}
die(json_encode($obj));
?>
