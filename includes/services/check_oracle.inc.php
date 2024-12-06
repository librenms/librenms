<?php
/**
 * check_oracle Nagios Plugin
 * Docs: https://www.monitoring-plugins.org/doc/man/check_oracle.html
 *
 * If the plugin doesn't work, check that the ORACLE_HOME environment
 * variable is set, that ORACLE_HOME/bin is in your PATH, and the
 * tnsnames.ora file is locatable and is properly configured.
 *
 * This plugin does not accept the '-H' flag for hostname/IP address
 * that most other Nagios plugins do.
 */
$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_oracle ' . $service['service_param'];
