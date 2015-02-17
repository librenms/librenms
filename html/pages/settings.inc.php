<?php
/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 * Global Settings
 * @author f0o <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Page
 */

/**
 * Array-To-Table
 * @param array $a N-Dimensional, Associative Array
 * @return string
 */
function a2t($a) {
	$r = "<table class='table table-condensed table-hover'><tbody>";
	foreach( $a as $k=>$v ) {
		if( !empty($v) ) {
			$r .= "<tr><td class='col-md-2'><i><b>".$k."</b></i></td><td class='col-md-10'>".(is_array($v)?a2t($v):"<code>".wordwrap($v,75,"<br/>")."</code>")."</td></tr>";
		}
	}
	$r .= '</tbody></table>';
	return $r;
}

if( $_SESSION['userlevel'] >= 10 ) {
	echo "<div class='table-responsive'>".a2t($config)."</div>";
} else {
	include("includes/error-no-perm.inc.php");
}
?>
