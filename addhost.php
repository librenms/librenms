#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

if (isset($argv[1]) && $argv[1])
{
    $host      = strtolower($argv[1]);
    $community = $argv[2];
    $snmpver   = strtolower($argv[3]);

    if (is_numeric($argv[4]))
    {
        $port = $argv[4];
    }
    else
    {
        $port = 161;
    }

    if (@!$argv[5])
    {
        $transport = 'udp';
    }
    else
    {
        $transport = $argv[5];
    }

    if (!$snmpver) $snmpver = "v2c";

    if ($community)
    {
        unset($config['snmp']['community']);
        $config['snmp']['community'][] = $community;
    }

    list($hostshort) = explode(".", $host);
    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '".mres($host)."'"), 0) == '0' )
    {
        if (isDomainResolves($argv[1]))
        {
            if (isPingable($argv[1]))
            {
                $added = 0;

                foreach ($config['snmp']['community'] as $community)
                {
                    $device = deviceArray($host, $community, $snmpver, $port, $transport);

                    if (isSNMPable($device))
                    {
                        $snmphost = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB");
                        if ($snmphost == "" || ($snmphost && ($snmphost == $host || $hostshort = $host)))
                        {
                            $added = createHost ($host, $community, $snmpver, $port, $transport);
                            if($added) { echo($added . "\n"); break; }

                        } else { echo("Given hostname does not match SNMP-read hostname ($snmphost)!\n"); }
                    }
                }

                if (!$added) { echo("Could not reach $host with given SNMP community\n"); }
            } else { echo("Could not ping $host\n"); }
        } else { echo("Could not resolve $host\n"); }
    } else { echo("Already got host $host\n"); }
} else { echo("Add Host Tool\nUsage: ./addhost.php <hostname> [community] [v1|v2c] [port] [" . join("|",$config['snmp']['transports']) . "]\n"); }

?>
