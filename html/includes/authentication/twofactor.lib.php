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
 * Two-Factor Authentication Library
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Authentication
 */

/**
 * Key Interval in seconds.
 * Set to 30s due to Google-Authenticator limitation.
 * Sadly Google-Auth is the most used Non-Physical OTP app.
 */
const keyInterval = 30;

/**
 * Size of the OTP.
 * Set to 6 for the same reasons as above.
 */
const otpSize = 6;

/**
 * Window to honour whilest verifying OTP.
 */
const otpWindow = 4;

/**
 * Base32 Decoding dictionary
 */
$base32 = array(
	"A" => 0,	"B" => 1,	"C" => 2,	"D" => 3,
	"E" => 4,	"F" => 5,	"G" => 6,	"H" => 7,
	"I" => 8,	"J" => 9,	"K" => 10,	"L" => 11,
	"M" => 12,	"N" => 13,	"O" => 14,	"P" => 15,
	"Q" => 16,	"R" => 17,	"S" => 18,	"T" => 19,
	"U" => 20,	"V" => 21,	"W" => 22,	"X" => 23,
	"Y" => 24,	"Z" => 25,	"2" => 26,	"3" => 27,
	"4" => 28,	"5" => 29,	"6" => 30,	"7" => 31
);

/**
 * Base32 Encoding dictionary
 */
$base32_enc = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

/**
 * Generate Secret Key
 * @return string
 */
function twofactor_genkey() {
	global $base32_enc;
	// RFC 4226 recommends 160bits Secret Keys, that's 20 Bytes for the lazy ones.
	$crypto = false;
	$x = -1;
	while( $crypto == false || ++$x < 10 ) {
		$raw = openssl_random_pseudo_bytes(20,$crypto);
	}
	// RFC 4648 Base32 Encoding without padding
	$len = strlen($raw);
	$bin = "";
	$x = -1;
	while( ++$x < $len ) {
		$bin .= str_pad(base_convert(ord($raw[$x]), 10, 2), 8, '0', STR_PAD_LEFT);
	}
	$bin = str_split($bin, 5);
	$ret = "";
	$x = -1;
	while( ++$x < sizeof($bin) ) {
		$ret .= $base32_enc[base_convert(str_pad($bin[$x], 5, '0'), 2, 10)];
	}
	return $ret;
}

/**
 * Generate HOTP (RFC 4226)
 * @param string $key Secret Key
 * @param int $counter Counter, Defaults to Timestamp
 * @return int
 */
function oath_hotp($key, $counter=false) {
	global $base32;
	if( $counter === false ) {
		$counter = floor(microtime(true)/keyInterval);
	}
	$length = strlen($key);
	$x = -1;
	$y = $z = 0;
	$kbin = "";
	while( ++$x < $length ) {
		$y <<= 5;
		$y += $base32[$key[$x]];
		$z += 5;
		if( $z >= 8 ) {
			$z -= 8;
			$kbin .= chr(($y & (0xFF << $z)) >> $z);
		}
	}
	$hash = hash_hmac('sha1', pack('N*', 0).pack('N*', $counter), $kbin, true);
	$offset = ord($hash[19]) & 0xf;
	$truncated = (((ord($hash[$offset+0]) & 0x7f) << 24 ) | ((ord($hash[$offset+1]) & 0xff) << 16 ) | ((ord($hash[$offset+2]) & 0xff) << 8 ) | (ord($hash[$offset+3]) & 0xff)) % pow(10, otpSize);
	return str_pad($truncated, otpSize, '0', STR_PAD_LEFT);
}

/**
 * Verify HOTP token honouring window
 * @param string $key Secret Key
 * @param int $otp OTP supplied by user
 * @param int|boolean $counter Counter, if false timestamp is used
 * @return boolean|int
 */
function verify_hotp($key,$otp,$counter=false) {
	if( $counter === false ) {
		//TimeBased HOTP requires lookbehind and lookahead.
		$counter   = floor(microtime(true)/keyInterval);
		$initcount = $counter-((otpWindow+1)*keyInterval);
		$endcount  = $counter+(otpWindow*keyInterval);
		$totp      = true;
	} else {
		//Counter based HOTP only has lookahead, not lookbehind.
		$initcount = $counter-1;
		$endcount  = $counter+otpWindow;
		$totp      = false;
	}
	while( ++$initcount <= $endcount ) {
		if( oath_hotp($key,$initcount) == $otp ) {
			if( !$totp ) {
				return $initcount;
			} else {
				return true;
			}
		}
	}
	return false;
}

/**
 * Print TwoFactor Input-Form
 * @param boolean $html Include HTML-Headers and Footers, if set function will `die()`
 * @return void|string
 */
function twofactor_form($html=true){
	global $config;
	$ret = "";
	if( $html ) {
		$ret .= '<!DOCTYPE HTML>
<html>
<head>
  <title>'.$config['page_title_suffix'].' TwoFactor Authentication</title>
  <base href="'.$config['base_url'].'" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="'.$config['stylesheet'].'" rel="stylesheet" type="text/css" />
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
'.($config['favicon'] ? '  <link rel="shortcut icon" href="'.$config['favicon'].'" />' . "\n" : "").'
</head>
<body>

<br />
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <form class="form-horizontal" role="form" action="" method="post" name="twofactorform">';
	}
	$ret .= '
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <h3>Please Enter TwoFactor Token:</h3>
          </div>
        </div>
        <div class="form-group">
          <label for="twofactor" class="col-sm-2 control-label">Token</label>
          <div class="col-sm-6">
            <input type="text" name="twofactor" id="twofactor" class="form-control" placeholder="012345" />
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-6">
            <button type="submit" class="btn btn-default input-sm" name="submit" type="submit">Login</button>
          </div>
        </div>';
	if( $html ) {
		$ret .= '
      </form>
    </div>
  </div>
</div>
</body>';
		die($ret);
	}
	return $ret;
}
?>
