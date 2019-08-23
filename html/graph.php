<?php
/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage graphing
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Config;

$start = microtime(true);

$init_modules = array('web', 'graphs', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

$auth = Auth::check() || is_client_authorized($_SERVER['REMOTE_ADDR']);

// Push $_GET into $vars to be compatible with web interface naming
foreach ($_GET as $name => $value) {
    $vars[$name] = $value;
}

// FIXME - other place?
function doerror ($msg, $type) {
    // Create a xxx*xxx image
    $im = imagecreate(200, 50);

    // White background and blue text
    $bg = imagecolorallocate($im, 255, 255, 255);
    $textcolor = imagecolorallocate($im, 0, 0, 255);

    // Write the string at the top left
    imagestring($im, 5, 10, 0, $msg, $textcolor);
    imagestring($im, 5, 10, 15, $msg, $textcolor);
    imagestring($im, 5, 10, 30, $msg, $textcolor);

    // Output the image
    header('Content-type: image/png');
    imagepng($im);
    imagedestroy($im);

    if (!empty(Config::get('allow_unauth_redirect_to')) && Config::get('allow_unauth_redirect') == '1') {
        die(Header("Location: ".Config::get('allow_unauth_redirect_to')."?type=".$type.""));
    } else {
        die(''); // The image for in link after expire
    }
}

if (!$auth) {
    //die('Unauthorized');
    doerror("Unauthorized", 10);
}

$referrer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
$own = parse_url(Config::get('base_url'), PHP_URL_HOST);

if (isset($vars['rule']) && isset($vars['alert']) && Config::get('allow_unauth_time_limit') == 1) {
    $query = "SELECT `time_logged` FROM `alert_log` WHERE device_id = ". $vars['device']  ." AND rule_id = ". $vars[rule]  ." AND id = ". $vars['alert']  ." AND state =! 0 AND details IS NOT NULL ORDER BY id DESC";
    if (empty(dbFetch($query))) {
        doerror("No Valid Request", 10);
    } else {
        $vars['from'] = floor(date_timestamp_get(date_create(dbFetchCell($query, [time_logged])))/300) * 300 - (Config::get('allow_unauth_time_befor') * 60);
        $vars['to'] = time();
        $query = "SELECT * FROM `alert_log` WHERE rule_id = ". $vars['rule'] ." AND device_id = ". $vars['device'] ." AND id >= ". $vars['alert'] ." AND state = 0 AND details IS NOT NULL ORDER BY id DESC";
        $res = dbFetch($query);
        $date = date_create($res[0]['time_logged']);
        if (empty($res)) {
            //this is ok :)
        } elseif (count($res) == 1 && time() - date_timestamp_get($date) > (Config::get('allow_unauth_time_after') * 60)) {
             doerror("Alert is closed", 1);
        }elseif (count($res) >= 2) {
            doerror("Alert is closed", 5);
        }
    }
} elseif (!in_array($referrer, Config::get('allow_unauth_domains')) || $own != $referrer) {
    doerror("Unauthorized", 8);
}


set_debug(isset($_GET['debug']));

rrdtool_initialize(false);

require \LibreNMS\Config::get('install_dir') . '/includes/html/graphs/graph.inc.php';

rrdtool_close();

if ($debug) {
    echo '<br />';
    printf("Runtime %.3fs", microtime(true) - $start);
    echo '<br />';
    printStats();
}
