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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
	$raw = "";
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
 * @param int|boolean $counter Optional Counter, Defaults to Timestamp
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
	if( oath_hotp($key,$counter) == $otp ) {
		return true;
    }
    else {
		if( $counter === false ) {
			//TimeBased HOTP requires lookbehind and lookahead.
			$counter   = floor(microtime(true)/keyInterval);
			$initcount = $counter-((otpWindow+1)*keyInterval);
			$endcount  = $counter+(otpWindow*keyInterval);
			$totp      = true;
        }
        else {
			//Counter based HOTP only has lookahead, not lookbehind.
			$initcount = $counter-1;
			$endcount  = $counter+otpWindow;
			$totp      = false;
		}
		while( ++$initcount <= $endcount ) {
			if( oath_hotp($key,$initcount) == $otp ) {
				if( !$totp ) {
					return $initcount;
                }
                else {
					return true;
				}
			}
		}
	}
	return false;
}

/**
 * Print TwoFactor Input-Form
 * @param boolean $form Include FORM-tags
 * @return void|string
 */
function twofactor_form($form=true){
	global $config;
	$ret = "";
	if( $form ) {
		$ret .= '
      <div class="row">
        <div class="col-md-offset-4 col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">
                <center>
                  <img src="images/librenms_logo_light.png">
                </center>
              </h3>
            </div>
            <div class="panel-body">
              <div class="container-fluid">
                  <form class="form-horizontal" role="form" action="" method="post" name="twofactorform">';
	}
	$ret .= '
        <div class="form-group">
          <div class="col-md-12">
            <input type="text" name="twofactor" id="twofactor" class="form-control" autocomplete="off" placeholder="Please enter auth token" />
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-12">
            <button type="submit" class="btn btn-default btn-block" name="submit" type="submit">Login</button>
          </div>
         </div>
        </div>';
	$ret .= '<script>document.twofactorform.twofactor.focus();</script>';
	if( $form ) {
		$ret .= '
      </form>';
	}
	return $ret;
}

/**
 * Authentication logic
 * @return void
 */
function twofactor_auth() {
	global $auth_message, $twofactorform, $config;
	$twofactor = dbFetchRow('SELECT twofactor FROM users WHERE username = ?', array($_SESSION['username']));
	if( empty($twofactor['twofactor']) ) {
		$_SESSION['twofactor'] = true;
    }
    else {
		$twofactor = json_decode($twofactor['twofactor'],true);
		if( $twofactor['fails'] >= 3 && (!$config['twofactor_lock'] || (time()-$twofactor['last']) < $config['twofactor_lock']) ) {
			$auth_message = "Too many failures, please ".($config['twofactor_lock'] ? "wait ".$config['twofactor_lock']." seconds" : "contact administrator").".";
        }
        else {
			if( !$_POST['twofactor'] ) {
				$twofactorform = true;
            }
            else {
				if( ($server_c = verify_hotp($twofactor['key'],$_POST['twofactor'],$twofactor['counter'])) === false ) {
					$twofactor['fails']++;
					$twofactor['last'] = time();
					$auth_message = "Wrong Two-Factor Token.";
                }
                else {
					if( $twofactor['counter'] !== false ) {
						if( $server_c !== true && $server_c !== $twofactor['counter'] ) {
							$twofactor['counter'] = $server_c+1;
                        }
                        else {
							$twofactor['counter']++;
						}
					}
					$twofactor['fails'] = 0;
					$_SESSION['twofactor'] = true;
				}
				dbUpdate(array('twofactor' => json_encode($twofactor)),'users','username = ?',array($_SESSION['username']));
			}
		}
	}
}
