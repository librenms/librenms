<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage webinterface
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

if ($_GET[debug]) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', E_ALL);
}

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

$output = '';
if ($_GET['query'] && $_GET['cmd']) {
    $host = clean($_GET['query']);
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var('http://'.$host, FILTER_VALIDATE_URL)) {
        switch ($_GET['cmd']) {
            case 'whois':
                $cmd = $config['whois']." $host | grep -v \%";
                break;

            case 'ping':
                $cmd = $config['ping']." -c 5 $host";
                break;

            case 'tracert':
                $cmd = $config['mtr']." -r -c 5 $host";
                break;

            case 'nmap':
                if ($_SESSION['userlevel'] != '10') {
                    echo 'insufficient privileges';
                } else {
                    $cmd = $config['nmap']." $host";
                }
                break;
        }//end switch

        if (!empty($cmd)) {
            $output = `$cmd`;
        }
    }//end if
}//end if

$output = htmlentities(trim($output), ENT_QUOTES);
echo "<pre>$output</pre>";
