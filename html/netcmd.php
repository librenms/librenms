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

use LibreNMS\Authentication\LegacyAuth;

ini_set('allow_url_fopen', 0);

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

set_debug($_GET['debug']);

if (!LegacyAuth::check()) {
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
                if (!LegacyAuth::user()->isAdmin()) {
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
