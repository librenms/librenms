<?php
/**
 * Snmp.php
 *
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
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */

namespace LibreNMS;

use LibreNMS\Config;

class SNMP
{
    private $debug = false;
    private $snmpStats;
    private $rrdStats;

    /**
     * Enable Debug Mode
     * @return void
     */
    public function enableDebug()
    {
        $this->debug = true;
    }

    /**
     * Disable Debug Mode
     * @return void
     */
    public function disableDebug()
    {
        $this->debug = false;
    }
    /**
     * Generate an snmpget command
     *
     * @param array $device the we will be connecting to
     * @param string $oids the oids to fetch, separated by spaces
     * @param string $options extra snmp command options, usually this is output options
     * @param string $mib an additional mib to add to this command
     * @param string $mibdir a mib directory to search for mibs, usually prepended with +
     * @return string the fully assembled command, ready to run
     */
    public function genSnmpgetCmd($device, $oids, $options = null, $mib = null, $mibdir = null)
    {
        $snmpcmd = Config::get('snmpget');
        return $this->genSnmpCmd($snmpcmd, $device, $oids, $options, $mib, $mibdir);
    } // end genSnmpgetCmd()
    
    /**
     * @param string $stat snmpget, snmpwalk
     * @param float $start_time The time the operation started with 'microtime(true)'
     * @return float  The calculated run time
     */
    public function recordSnmpStatistic($stat, $start_time)
    {
        $this->initStats();

        $runtime = microtime(true) - $start_time;
        $this->snmp_stats['ops'][$stat]++;
        $this->snmp_stats['time'][$stat] += $runtime;
        return $runtime;
    }

    /**
     * Generate an snmp command
     *
     * @param string $cmd the snmp command to run, like snmpget
     * @param array $device the we will be connecting to
     * @param string $oids the oids to fetch, separated by spaces
     * @param string $options extra snmp command options, usually this is output options
     * @param string $mib an additional mib to add to this command
     * @param string $mibdir a mib directory to search for mibs, usually prepended with +
     * @return string the fully assembled command, ready to run
     */
    public function genSnmpCmd($cmd, $device, $oids, $options = null, $mib = null, $mibdir = null)
    {
        // populate timeout & retries values from configuration
        $timeout = $this->prepSnmpSetting($device, 'timeout');
        $retries = $this->prepSnmpSetting($device, 'retries');

        if (!isset($device['transport'])) {
            $device['transport'] = 'udp';
        }

        $cmd .= $this->snmpGenAuth($device);
        $cmd .= " $options";
        $cmd .= $mib ? " -m $mib" : '';
        $cmd .= $this->mibdir($mibdir, $device);
        $cmd .= isset($timeout) ? " -t $timeout" : '';
        $cmd .= isset($retries) ? " -r $retries" : '';
        $cmd .= ' '.$device['transport'].':'.$device['hostname'].':'.$device['port'];
        $cmd .= " $oids";

        if (!$this->debug) {
            $cmd .= ' 2>/dev/null';
        }

        return $cmd;
    } // end genSnmpCmd()

    public function prepSnmpSetting($device, $setting)
    {
        if (isset($device[$setting]) && is_numeric($device[$setting]) && $device[$setting] > 0) {
            return $device[$setting];
        } elseif (Config::get("snmp.$setting") != null) {
            return Config::get("snmp.$setting");
        }
    }//end prepSnmpSetting()


    public static function get($device, $oid, $options = null, $mib = null, $mibdir = null)
    {
        $time_start = microtime(true);

        if (strstr($oid, ' ')) {
            echo report_this_text("snmpGet called for multiple OIDs: $oid");
        }

        $cmd = (new static)->genSnmpgetCmd($device, $oid, $options, $mib, $mibdir);
        $data = trim((new static)->externalExec($cmd), "\" \n\r");

        (new static)->recordSnmpStatistic('snmpget', $time_start);
        if (preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure)/i', $data)) {
            return false;
        } elseif ($data || $data === '0') {
            return $data;
        } else {
            return false;
        }
    }//end snmpGet()


    public function externalExec($command)
    {
        $vdebug = false; // Do actual verbose debugging
        if ($this->debug && !$vdebug) {
            $debug_command = preg_replace('/-c [\S]+/', '-c COMMUNITY', $command);
            $debug_command = preg_replace('/-u [\S]+/', '-u USER', $debug_command);
            $debug_command = preg_replace('/-U [\S]+/', '-u USER', $debug_command);
            $debug_command = preg_replace('/-A [\S]+/', '-A PASSWORD', $debug_command);
            $debug_command = preg_replace('/-X [\S]+/', '-X PASSWORD', $debug_command);
            $debug_command = preg_replace('/-P [\S]+/', '-P PASSWORD', $debug_command);
            $debug_command = preg_replace('/-H [\S]+/', '-H HOSTNAME', $debug_command);
            $debug_command = preg_replace('/(udp|udp6|tcp|tcp6):([^:]+):([\d]+)/', '\1:HOSTNAME:\3', $debug_command);
            c_echo('SNMP[%c' . $debug_command . "%n]\n");
        } elseif ($vdebug) {
            c_echo('SNMP[%c'.$command."%n]\n");
        }
    
        $output = shell_exec($command);
    
        if ($this->debug && !$vdebug) {
            $ip_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
            $debug_output = preg_replace($ip_regex, '*', $output);
            d_echo($debug_output . PHP_EOL);
        } elseif ($vdebug) {
            d_echo($output . PHP_EOL);
        }
    
        return $output;
    }

    protected function initStats()
    {
        if (!isset($this->snmp_stats, $this->rrd_stats)) {
            $this->snmp_stats = [
                'ops' => [
                    'snmpget' => 0,
                    'snmpgetnext' => 0,
                    'snmpwalk' => 0,
                ],
                'time' => [
                    'snmpget' => 0.0,
                    'snmpgetnext' => 0.0,
                    'snmpwalk' => 0.0,
                ]
            ];
    
            $this->rrd_stats = [
                'ops' => [
                    'update' => 0,
                    'create' => 0,
                    'other' => 0,
                ],
                'time' => [
                    'update' => 0.0,
                    'create' => 0.0,
                    'other' => 0.0,
                ],
            ];
        }
    }

    protected function snmpGenAuth(&$device)
    {
        $cmd = '';

        if ($device['snmpver'] === 'v3') {
            $cmd = " -v3 -n '' -l '".$device['authlevel']."'";

            //add context if exist context
            if (isset($device->context_name)) {
                $cmd = " -v3 -n '$device->context_name' -l '$device->authlevel'";
            }

            if ($device['authlevel'] === 'noAuthNoPriv') {
                // We have to provide a username anyway (see Net-SNMP doc)
                $username = !empty($device['authname']) ? $device['authname'] : 'root';
                $cmd .= " -u '".$username."'";
            } elseif ($device['authlevel'] === 'authNoPriv') {
                $cmd .= " -a '".$device['authalgo']."'";
                $cmd .= " -A '".$device['authpass']."'";
                $cmd .= " -u '".$device['authname']."'";
            } elseif ($device['authlevel'] === 'authPriv') {
                $cmd .= " -a '".$device['authalgo']."'";
                $cmd .= " -A '".$device['authpass']."'";
                $cmd .= " -u '".$device['authname']."'";
                $cmd .= " -x '".$device['cryptoalgo']."'";
                $cmd .= " -X '".$device['cryptopass']."'";
            } else {
                if ($this->debug) {
                    print 'DEBUG: '.$device['snmpver']." : Unsupported SNMPv3 AuthLevel (wtf have you done ?)\n";
                }
            }
        } elseif ($device['snmpver'] === 'v2c' or $device['snmpver'] === 'v1') {
            $cmd  = " -".$device['snmpver'];
            $cmd .= " -c '".$device['community']."'";
        } else {
            if ($this->debug) {
                print 'DEBUG: '.$device['snmpver']." : Unsupported SNMP Version (shouldn't be possible to get here)\n";
            }
        }//end if

        return $cmd;
    }
    
    /**
     * Generate the mib search directory argument for snmpcmd
     * If null return the default mib dir
     * If $mibdir is empty '', return an empty string
     *
     * @param string $mibdir should be the name of the directory within Config::get mib_dir
     * @param string $device
     * @return string The option string starting with -M
     */
    protected function mibdir($mibdir = null, $device = [])
    {
        $extra_dir = implode(':', $this->getMibDir($device));
        if (!empty($extra_dir)) {
            $extra_dir = ":".$extra_dir;
        }
        // dd($mibdir);
        if (is_null($mibdir)) {
            return " -M ".Config::get('mib_dir')."$extra_dir";
        }

        if (empty($mibdir)) {
            // use system mibs
            return '';
        }

        return " -M ".Config::get('mib_dir')."$extra_dir:".Config::get('mib_dir')."/$mibdir";
    }

    /**
     * @param $device
     * @return array $extra will contain a list of mib dirs
     */
    protected function getMibDir($device)
    {
        $extra = array();

        if (file_exists(Config::get('mib_dir') . '/' . $device->os)) {
            $extra[] = Config::get('mib_dir') . '/' . $device->os;
        }

        if (isset($device['os_group'])) {
            if (file_exists(Config::get('mib_dir') . '/' . $device['os_group'])) {
                $extra[] = Config::get('mib_dir') . '/' . $device['os_group'];
            }

            if (Config::get("os_groups.{$device['os_group']}.mib_dir") != null) {
                if (is_array(Config::get("os_groups.{$device['os_group']}.mib_dir"))) {
                    foreach (Config::get("os_groups.{$device['os_group']}.mib_dir") as $k => $dir) {
                        $extra[] = Config::get('mib_dir') . '/' . $dir;
                    }
                }
            }
        }

        if (Config::get("os.{$device['os']}.mib_dir") != null) {
            if (is_array(Config::get("os.{$device['os']}.mib_dir"))) {
                foreach (Config::get("os.{$device['os']}.mib_dir") as $k => $dir) {
                    $extra[] = Config::get('mib_dir') . '/' . $dir;
                }
            }
        }
        return $extra;
    }
}
