<?php
/*
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

require 'includes/html/collectd/CollectdColor.php';

use LibreNMS\CollectdColor;
use LibreNMS\Config;

define('REGEXP_HOST', '/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/');
define('REGEXP_PLUGIN', '/^[a-zA-Z0-9_.-]+$/');

/*
 * Read input variable from GET, POST or COOKIE taking
 * care of magic quotes
 *
 * @param string $name Name of value to return
 * @param array $array User-input array ($_GET, $_POST or $_COOKIE)
 * @param string $default Default value
 * @return string $default if name in unknown in $array, otherwise
 *         input value with magic quotes stripped off
 */
function read_var($name, &$array, $default = null)
{
    if (isset($array[$name])) {
        if (is_array($array[$name])) {
            if (get_magic_quotes_gpc()) {
                $ret = [];
                foreach ($array[$name] as $k => $v) {
                    $ret[stripslashes($k)] = stripslashes($v);
                }

                return $ret;
            } else {
                return $array[$name];
            }
        } elseif (is_string($array[$name]) && get_magic_quotes_gpc()) {
            return stripslashes($array[$name]);
        } else {
            return $array[$name];
        }
    } else {
        return $default;
    }
}//end read_var()

/*
 * Alphabetically compare host names, comparing label
 * from tld to node name
 */
function collectd_compare_host($a, $b)
{
    $ea = explode('.', $a);
    $eb = explode('.', $b);
    $i = (count($ea) - 1);
    $j = (count($eb) - 1);
    while ($i >= 0 && $j >= 0) {
        if (($r = strcmp($ea[$i--], $eb[$j--])) != 0) {
            return $r;
        }
    }

    return 0;
}//end collectd_compare_host()

/**
 * Fetch list of hosts found in collectd's datadirs.
 * @return array Sorted list of hosts (sorted by label from rigth to left)
 */
function collectd_list_hosts()
{
    $hosts = [];
    foreach (Config::get('datadirs') as $datadir) {
        if ($d = @opendir($datadir)) {
            while (($dent = readdir($d)) !== false) {
                if ($dent != '.' && $dent != '..' && is_dir($datadir . '/' . $dent) && preg_match(REGEXP_HOST, $dent)) {
                    $hosts[] = $dent;
                }
            }
            closedir($d);
        } else {
            error_log('Failed to open datadir: ' . $datadir);
        }
    }
    $hosts = array_unique($hosts);
    usort($hosts, 'collectd_compare_host');

    return $hosts;
}

/**
 * Fetch list of plugins found in collectd's datadirs for given host.
 *
 * @param string $arg_host Name of host for which to return plugins
 * @return array Sorted list of plugins (sorted alphabetically)
 */
function collectd_list_plugins($arg_host)
{
    $plugins = [];
    foreach (Config::get('datadirs') as $datadir) {
        if (preg_match(REGEXP_HOST, $arg_host) && ($d = @opendir($datadir . '/' . $arg_host))) {
            while (($dent = readdir($d)) !== false) {
                if ($dent != '.' && $dent != '..' && is_dir($datadir . '/' . $arg_host . '/' . $dent)) {
                    if ($i = strpos($dent, '-')) {
                        $plugins[] = substr($dent, 0, $i);
                    } else {
                        $plugins[] = $dent;
                    }
                }
            }

            closedir($d);
        }
    }

    $plugins = array_unique($plugins);
    sort($plugins);

    return $plugins;
}//end collectd_list_plugins()

/**
 * Fetch list of plugin instances found in collectd's datadirs for given host+plugin
 *
 * @param string $arg_host Name of host
 * @param string $arg_plugin Name of plugin
 * @return array Sorted list of plugin instances (sorted alphabetically)
 */
function collectd_list_pinsts($arg_host, $arg_plugin)
{
    $pinsts = [];
    foreach (Config::get('datadirs') as $datadir) {
        if (preg_match(REGEXP_HOST, $arg_host) && ($d = opendir($datadir . '/' . $arg_host))) {
            while (($dent = readdir($d)) !== false) {
                if ($dent != '.' && $dent != '..' && is_dir($datadir . '/' . $arg_host . '/' . $dent)) {
                    if ($i = strpos($dent, '-')) {
                        $plugin = substr($dent, 0, $i);
                        $pinst = substr($dent, ($i + 1));
                    } else {
                        $plugin = $dent;
                        $pinst = '';
                    }

                    if ($plugin == $arg_plugin) {
                        $pinsts[] = $pinst;
                    }
                }
            }

            closedir($d);
        }
    }//end foreach

    $pinsts = array_unique($pinsts);
    sort($pinsts);

    return $pinsts;
}//end collectd_list_pinsts()

/**
 * Fetch list of types found in collectd's datadirs for given host+plugin+instance
 * @arg_host Name of host
 * @arg_plugin Name of plugin
 * @arg_pinst Plugin instance
 * @return array Sorted list of types (sorted alphabetically)
 */
function collectd_list_types($arg_host, $arg_plugin, $arg_pinst)
{
    $types = [];
    $my_plugin = $arg_plugin . (strlen($arg_pinst) ? '-' . $arg_pinst : '');
    if (! preg_match(REGEXP_PLUGIN, $my_plugin)) {
        return $types;
    }

    foreach (Config::get('datadirs') as $datadir) {
        if (preg_match(REGEXP_HOST, $arg_host) && ($d = @opendir($datadir . '/' . $arg_host . '/' . $my_plugin))) {
            while (($dent = readdir($d)) !== false) {
                if ($dent != '.' && $dent != '..' && is_file($datadir . '/' . $arg_host . '/' . $my_plugin . '/' . $dent) && substr($dent, (strlen($dent) - 4)) == '.rrd') {
                    $dent = substr($dent, 0, (strlen($dent) - 4));
                    if ($i = strpos($dent, '-')) {
                        $types[] = substr($dent, 0, $i);
                    } else {
                        $types[] = $dent;
                    }
                }
            }

            closedir($d);
        }
    }

    $types = array_unique($types);
    sort($types);

    return $types;
}//end collectd_list_types()

/**
 * Fetch list of type instances found in collectd's datadirs for given host+plugin+instance+type
 * @arg_host Name of host
 * @arg_plugin Name of plugin
 * @arg_pinst Plugin instance
 * @arg_type Type
 * @return array Sorted list of type instances (sorted alphabetically)
 */
function collectd_list_tinsts($arg_host, $arg_plugin, $arg_pinst, $arg_type)
{
    $tinsts = [];
    $my_plugin = $arg_plugin . (strlen($arg_pinst) ? '-' . $arg_pinst : '');
    if (! preg_match(REGEXP_PLUGIN, $my_plugin)) {
        return $tinsts;
    }

    foreach (Config::get('datadirs') as $datadir) {
        if (preg_match(REGEXP_HOST, $arg_host) && ($d = @opendir($datadir . '/' . $arg_host . '/' . $my_plugin))) {
            while (($dent = readdir($d)) !== false) {
                if ($dent != '.' && $dent != '..' && is_file($datadir . '/' . $arg_host . '/' . $my_plugin . '/' . $dent) && substr($dent, (strlen($dent) - 4)) == '.rrd') {
                    $dent = substr($dent, 0, (strlen($dent) - 4));
                    if ($i = strpos($dent, '-')) {
                        $type = substr($dent, 0, $i);
                        $tinst = substr($dent, ($i + 1));
                    } else {
                        $type = $dent;
                        $tinst = '';
                    }

                    if ($type == $arg_type) {
                        $tinsts[] = $tinst;
                    }
                }
            }

            closedir($d);
        }
    }//end foreach

    $tinsts = array_unique($tinsts);
    sort($tinsts);

    return $tinsts;
}//end collectd_list_tinsts()

/**
 * Parse symlinks in order to get an identifier that collectd understands
 * (e.g. virtualisation is collected on host for individual VMs and can be
 *  symlinked to the VM's hostname, support FLUSH for these by flushing
 *  on the host-identifier instead of VM-identifier)
 *
 * @param string $host Hostname
 * @param string $plugin Plugin name
 * @param string $type
 * @param string $pinst Plugin instance
 * @param string $tinst Type instance
 * @return string Identifier that collectd's FLUSH command understands
 */
function collectd_identifier($host, $plugin, $type, $pinst, $tinst)
{
    $rrd_realpath = null;
    $orig_identifier = sprintf('%s/%s%s%s/%s%s%s', $host, $plugin, strlen($pinst) ? '-' : '', $pinst, $type, strlen($tinst) ? '-' : '', $tinst);
    $identifier = null;
    foreach (Config::get('datadirs') as $datadir) {
        if (is_file($datadir . '/' . $orig_identifier . '.rrd')) {
            $rrd_realpath = realpath($datadir . '/' . $orig_identifier . '.rrd');
            break;
        }
    }

    if ($rrd_realpath) {
        $identifier = basename($rrd_realpath);
        $identifier = substr($identifier, 0, (strlen($identifier) - 4));
        $rrd_realpath = dirname($rrd_realpath);
        $identifier = basename($rrd_realpath) . '/' . $identifier;
        $rrd_realpath = dirname($rrd_realpath);
        $identifier = basename($rrd_realpath) . '/' . $identifier;
    }

    if (is_null($identifier)) {
        return $orig_identifier;
    } else {
        return $identifier;
    }
}//end collectd_identifier()

/**
 * Tell collectd that it should FLUSH all data it has regarding the
 * graph we are about to generate.
 *
 * @param string $identifier
 * @return bool
 */
function collectd_flush($identifier)
{
    if (! Config::get('collectd_sock')) {
        return false;
    }

    if (is_null($identifier) || (is_array($identifier) && count($identifier) == 0) || ! (is_string($identifier) || is_array($identifier))) {
        return false;
    }

    $u_errno = 0;
    $u_errmsg = '';
    if ($socket = @fsockopen(Config::get('collectd_sock'), 0, $u_errno, $u_errmsg)) {
        $cmd = 'FLUSH plugin=rrdtool';
        if (is_array($identifier)) {
            foreach ($identifier as $val) {
                $cmd .= sprintf(' identifier="%s"', $val);
            }
        } else {
            $cmd .= sprintf(' identifier="%s"', $identifier);
        }

        $cmd .= "\n";

        $r = fwrite($socket, $cmd, strlen($cmd));
        if ($r === false || $r != strlen($cmd)) {
            error_log(sprintf('graph.php: Failed to write whole command to unix-socket: %d out of %d written', $r === false ? (-1) : $r, strlen($cmd)));
        }

        $resp = fgets($socket);
        if ($resp === false) {
            error_log(sprintf('graph.php: Failed to read response from collectd for command: %s', trim($cmd)));
        }

        $n = (int) $resp;
        while ($n-- > 0) {
            fgets($socket);
        }

        fclose($socket);
    } else {
        error_log(sprintf('graph.php: Failed to open unix-socket to collectd: %d: %s', $u_errno, $u_errmsg));
    }

    return true;
}//end collectd_flush()

/**
 * Helper function to strip quotes from RRD output
 *
 * @param string $str RRD-Info generated string
 * @return string String with one surrounding pair of quotes stripped
 */
function rrd_strip_quotes($str)
{
    if ($str[0] == '"' && $str[(strlen($str) - 1)] == '"') {
        return substr($str, 1, (strlen($str) - 2));
    } else {
        return $str;
    }
}//end rrd_strip_quotes()

/**
 * Determine useful information about RRD file
 *
 * @param string $file Name of RRD file to analyse
 * @return array Array describing the RRD file
 */
function _rrd_info($file)
{
    $info = ['filename' => $file];

    $rrd = popen(RRDTOOL . ' info ' . escapeshellarg($file), 'r');
    if ($rrd) {
        while (($s = fgets($rrd)) !== false) {
            $p = strpos($s, '=');
            if ($p === false) {
                continue;
            }

            $key = trim(substr($s, 0, $p));
            $value = trim(substr($s, ($p + 1)));
            if (strncmp($key, 'ds[', 3) == 0) {
                // DS definition
                $p = strpos($key, ']');
                $ds = substr($key, 3, ($p - 3));
                if (! isset($info['DS'])) {
                    $info['DS'] = [];
                }

                $ds_key = substr($key, ($p + 2));

                if (strpos($ds_key, '[') === false) {
                    if (! isset($info['DS']["$ds"])) {
                        $info['DS']["$ds"] = [];
                    }

                    $info['DS']["$ds"]["$ds_key"] = rrd_strip_quotes($value);
                }
            } elseif (strncmp($key, 'rra[', 4) == 0) {
                // RRD definition
                $p = strpos($key, ']');
                $rra = substr($key, 4, ($p - 4));
                if (! isset($info['RRA'])) {
                    $info['RRA'] = [];
                }

                $rra_key = substr($key, ($p + 2));

                if (strpos($rra_key, '[') === false) {
                    if (! isset($info['RRA']["$rra"])) {
                        $info['RRA']["$rra"] = [];
                    }

                    $info['RRA']["$rra"]["$rra_key"] = rrd_strip_quotes($value);
                }
            } elseif (strpos($key, '[') === false) {
                $info[$key] = rrd_strip_quotes($value);
            }//end if
        }//end while

        pclose($rrd);
    }//end if

    return $info;
}//end _rrd_info()

function rrd_get_color($code, $line = true)
{
    $name = ($line ? 'f_' : 'h_') . $code;
    if (! Config::has("rrd_colors.$name")) {
        $c_f = new CollectdColor('random');
        $c_h = new CollectdColor($c_f);
        $c_h->fade();
        Config::set("rrd_colors.f_$code", $c_f->toString());
        Config::set("rrd_colors.h_$code", $c_h->toString());
    }

    return Config::get("rrd_colors.$name");
}//end rrd_get_color()

/**
 * Draw RRD file based on it's structure
 *
 * @param $host
 * @param $plugin
 * @param $type
 * @param null $pinst
 * @param null $tinst
 * @param array $opts
 * @return string|false Commandline to call RRDGraph in order to generate the final graph* @internal param $
 */
function collectd_draw_rrd($host, $plugin, $type, $pinst = null, $tinst = null, $opts = [])
{
    $timespan_def = null;
    $timespans = Config::get('timespan');
    if (! isset($opts['timespan'])) {
        $timespan_def = reset($timespans);
    } else {
        foreach ($timespans as &$ts) {
            if ($ts['name'] == $opts['timespan']) {
                $timespan_def = $ts;
            }
        }
    }

    if (! isset($opts['rrd_opts'])) {
        $opts['rrd_opts'] = [];
    }

    if (isset($opts['logarithmic']) && $opts['logarithmic']) {
        array_unshift($opts['rrd_opts'], '-o');
    }

    $rrdinfo = null;
    $rrdfile = sprintf('%s/%s%s%s/%s%s%s', $host, $plugin, is_null($pinst) ? '' : '-', $pinst, $type, is_null($tinst) ? '' : '-', $tinst);
    foreach (Config::get('datadirs') as $datadir) {
        if (is_file($datadir . '/' . $rrdfile . '.rrd')) {
            $rrdinfo = _rrd_info($datadir . '/' . $rrdfile . '.rrd');
            if (isset($rrdinfo['RRA']) && is_array($rrdinfo['RRA'])) {
                break;
            } else {
                $rrdinfo = null;
            }
        }
    }

    if (is_null($rrdinfo)) {
        return false;
    }

    $graph = [];
    $has_avg = false;
    $has_max = false;
    $has_min = false;
    reset($rrdinfo['RRA']);
    $l_max = 0;
    foreach ($rrdinfo['RRA'] as $k => $v) {
        if ($v['cf'] == 'MAX') {
            $has_max = true;
        } elseif ($v['cf'] == 'AVERAGE') {
            $has_avg = true;
        } elseif ($v['cf'] == 'MIN') {
            $has_min = true;
        }
    }

    // Build legend. This may not work for all RRDs, i don't know :)
    if ($has_avg) {
        $graph[] = 'COMMENT:           Last';
    }

    if ($has_min) {
        $graph[] = 'COMMENT:   Min';
    }

    if ($has_max) {
        $graph[] = 'COMMENT:   Max';
    }

    if ($has_avg) {
        $graph[] = 'COMMENT:   Avg\\n';
    }

    reset($rrdinfo['DS']);
    foreach ($rrdinfo['DS'] as $k => $v) {
        if (strlen($k) > $l_max) {
            $l_max = strlen($k);
        }

        if ($has_min) {
            $graph[] = sprintf('DEF:%s_min=%s:%s:MIN', $k, $rrdinfo['filename'], $k);
        }

        if ($has_avg) {
            $graph[] = sprintf('DEF:%s_avg=%s:%s:AVERAGE', $k, $rrdinfo['filename'], $k);
        }

        if ($has_max) {
            $graph[] = sprintf('DEF:%s_max=%s:%s:MAX', $k, $rrdinfo['filename'], $k);
        }
    }

    if ($has_min && $has_max || $has_min && $has_avg || $has_avg && $has_max) {
        $n = 1;
        reset($rrdinfo['DS']);
        foreach ($rrdinfo['DS'] as $k => $v) {
            $graph[] = sprintf('LINE:%s_%s', $k, $has_min ? 'min' : 'avg');
            $graph[] = sprintf('CDEF:%s_var=%s_%s,%s_%s,-', $k, $k, $has_max ? 'max' : 'avg', $k, $has_min ? 'min' : 'avg');
            $graph[] = sprintf('AREA:%s_var#%s::STACK', $k, rrd_get_color($n++, false));
        }
    }

    reset($rrdinfo['DS']);
    $n = 1;
    foreach ($rrdinfo['DS'] as $k => $v) {
        $graph[] = sprintf('LINE1:%s_avg#%s:%s ', $k, rrd_get_color($n++, true), $k . substr('                  ', 0, ($l_max - strlen($k))));
        if (isset($opts['tinylegend']) && $opts['tinylegend']) {
            continue;
        }

        if ($has_avg) {
            $graph[] = sprintf('GPRINT:%s_avg:AVERAGE:%%5.1lf%%s', $k, $has_max || $has_min || $has_avg ? ',' : '\\l');
        }

        if ($has_min) {
            $graph[] = sprintf('GPRINT:%s_min:MIN:%%5.1lf%%s', $k, $has_max || $has_avg ? ',' : '\\l');
        }

        if ($has_max) {
            $graph[] = sprintf('GPRINT:%s_max:MAX:%%5.1lf%%s', $k, $has_avg ? ',' : '\\l');
        }

        if ($has_avg) {
            $graph[] = sprintf('GPRINT:%s_avg:LAST:%%5.1lf%%s\\l', $k);
        }
    }//end while

    // $rrd_cmd = array(RRDTOOL, 'graph', '-', '-E', '-a', 'PNG', '-w', Config::get('rrd_width'), '-h', Config::get('rrd_height'), '-t', $rrdfile);
    $rrd_cmd = [
        RRDTOOL,
        'graph',
        '-',
        '-E',
        '-a',
        'PNG',
        '-w',
        Config::get('rrd_width'),
        '-h',
        Config::get('rrd_height'),
    ];
    if (Config::get('rrd_width') <= '300') {
        $small_opts = [
            '--font',
            'LEGEND:7:mono',
            '--font',
            'AXIS:6:mono',
            '--font-render-mode',
            'normal',
        ];
        $rrd_cmd = array_merge($rrd_cmd, $small_opts);
    }

    $rrd_cmd = array_merge($rrd_cmd, Config::get('rrd_opts_array'), $opts['rrd_opts'], $graph);

    $cmd = RRDTOOL;
    $count_rrd_cmd = count($rrd_cmd);
    for ($i = 1; $i < $count_rrd_cmd; $i++) {
        $cmd .= ' ' . escapeshellarg($rrd_cmd[$i]);
    }

    return $cmd;
}//end collectd_draw_rrd()

/**
 * Draw RRD file based on it's structure
 *
 * @param $timespan
 * @param $host
 * @param $plugin
 * @param $type
 * @param null $pinst
 * @param null $tinst
 * @return false|string Commandline to call RRDGraph in order to generate the final graph* @internal param $
 */
function collectd_draw_generic($timespan, $host, $plugin, $type, $pinst = null, $tinst = null)
{
    global $GraphDefs;
    $timespan_def = null;
    $timespans = Config::get('timespan');
    foreach ($timespans as &$ts) {
        if ($ts['name'] == $timespan) {
            $timespan_def = $ts;
        }
    }

    if (is_null($timespan_def)) {
        $timespan_def = reset($timespans);
    }

    if (! isset($GraphDefs[$type])) {
        return false;
    }

    $rrd_file = sprintf('%s/%s%s%s/%s%s%s', $host, $plugin, is_null($pinst) ? '' : '-', $pinst, $type, is_null($tinst) ? '' : '-', $tinst);
    // $rrd_cmd  = array(RRDTOOL, 'graph', '-', '-E', '-a', 'PNG', '-w', Config::get('rrd_width'), '-h', Config::get('rrd_height'), '-t', $rrd_file);
    $rrd_cmd = [
        RRDTOOL,
        'graph',
        '-',
        '-E',
        '-a',
        'PNG',
        '-w',
        Config::get('rrd_width'),
        '-h',
        Config::get('rrd_height'),
    ];

    if (Config::get('rrd_width') <= '300') {
        $small_opts = [
            '--font',
            'LEGEND:7:mono',
            '--font',
            'AXIS:6:mono',
            '--font-render-mode',
            'normal',
        ];
        $rrd_cmd = array_merge($rrd_cmd, $small_opts);
    }

    $rrd_cmd = array_merge($rrd_cmd, Config::get('rrd_opts_array'));
    $rrd_args = $GraphDefs[$type];

    foreach (Config::get('datadirs') as $datadir) {
        $file = $datadir . '/' . $rrd_file . '.rrd';
        if (! is_file($file)) {
            continue;
        }

        $file = str_replace(':', '\\:', $file);
        $rrd_args = str_replace('{file}', $file, $rrd_args);

        $rrdgraph = array_merge($rrd_cmd, $rrd_args);
        $cmd = RRDTOOL;
        $count_rrdgraph = count($rrdgraph);
        for ($i = 1; $i < $count_rrdgraph; $i++) {
            $cmd .= ' ' . escapeshellarg($rrdgraph[$i]);
        }

        return $cmd;
    }

    return false;
}//end collectd_draw_generic()

/**
 * Draw stack-graph for set of RRD files
 * @param array $opts Graph options like colors
 * @param array $sources List of array(name, file, ds)
 * @return string Commandline to call RRDGraph in order to generate the final graph
 */
function collectd_draw_meta_stack(&$opts, &$sources)
{
    $timespan_def = null;
    $timespans = Config::get('timespan');
    if (! isset($opts['timespan'])) {
        $timespan_def = reset($timespans);
    } else {
        foreach ($timespans as &$ts) {
            if ($ts['name'] == $opts['timespan']) {
                $timespan_def = $ts;
            }
        }
    }

    if (! isset($opts['title'])) {
        $opts['title'] = 'Unknown title';
    }

    if (! isset($opts['rrd_opts'])) {
        $opts['rrd_opts'] = [];
    }

    if (! isset($opts['colors'])) {
        $opts['colors'] = [];
    }

    if (isset($opts['logarithmic']) && $opts['logarithmic']) {
        array_unshift($opts['rrd_opts'], '-o');
    }

    // $cmd = array(RRDTOOL, 'graph', '-', '-E', '-a', 'PNG', '-w', Config::get('rrd_width'), '-h', Config::get('rrd_height'),
    // '-t', $opts['title']);
    $cmd = [
        RRDTOOL,
        'graph',
        '-',
        '-E',
        '-a',
        'PNG',
        '-w',
        Config::get('rrd_width'),
        '-h',
        Config::get('rrd_height'),
    ];

    if (Config::get('rrd_width') <= '300') {
        $small_opts = [
            '--font',
            'LEGEND:7:mono',
            '--font',
            'AXIS:6:mono',
            '--font-render-mode',
            'normal',
        ];
        $cmd = array_merge($cmd, $small_opts);
    }

    $cmd = array_merge($cmd, Config::get('rrd_opts_array'), $opts['rrd_opts']);
    $max_inst_name = 0;

    foreach ($sources as &$inst_data) {
        $inst_name = $inst_data['name'];
        $file = $inst_data['file'];
        $ds = isset($inst_data['ds']) ? $inst_data['ds'] : 'value';

        if (strlen($inst_name) > $max_inst_name) {
            $max_inst_name = strlen($inst_name);
        }

        if (! is_file($file)) {
            continue;
        }

        $cmd[] = 'DEF:' . $inst_name . '_min=' . $file . ':' . $ds . ':MIN';
        $cmd[] = 'DEF:' . $inst_name . '_avg=' . $file . ':' . $ds . ':AVERAGE';
        $cmd[] = 'DEF:' . $inst_name . '_max=' . $file . ':' . $ds . ':MAX';
        $cmd[] = 'CDEF:' . $inst_name . '_nnl=' . $inst_name . '_avg,UN,0,' . $inst_name . '_avg,IF';
    }

    $inst_data = end($sources);
    $inst_name = $inst_data['name'];
    $cmd[] = 'CDEF:' . $inst_name . '_stk=' . $inst_name . '_nnl';

    $inst_data1 = end($sources);
    while (($inst_data0 = prev($sources)) !== false) {
        $inst_name0 = $inst_data0['name'];
        $inst_name1 = $inst_data1['name'];

        $cmd[] = 'CDEF:' . $inst_name0 . '_stk=' . $inst_name0 . '_nnl,' . $inst_name1 . '_stk,+';
        $inst_data1 = $inst_data0;
    }

    foreach ($sources as &$inst_data) {
        $inst_name = $inst_data['name'];
        // $legend = sprintf('%s', $inst_name);
        $legend = $inst_name;
        while (strlen($legend) < $max_inst_name) {
            $legend .= ' ';
        }

        $number_format = isset($opts['number_format']) ? $opts['number_format'] : '%6.1lf';

        if (isset($opts['colors'][$inst_name])) {
            $line_color = new CollectdColor($opts['colors'][$inst_name]);
        } else {
            $line_color = new CollectdColor('random');
        }

        $area_color = new CollectdColor($line_color);
        $area_color->fade();

        $cmd[] = 'AREA:' . $inst_name . '_stk#' . $area_color->toString();
        $cmd[] = 'LINE1:' . $inst_name . '_stk#' . $line_color->toString() . ':' . $legend;
        if (! (isset($opts['tinylegend']) && $opts['tinylegend'])) {
            $cmd[] = 'GPRINT:' . $inst_name . '_avg:LAST:' . $number_format . '';
            $cmd[] = 'GPRINT:' . $inst_name . '_avg:AVERAGE:' . $number_format . '';
            $cmd[] = 'GPRINT:' . $inst_name . '_min:MIN:' . $number_format . '';
            $cmd[] = 'GPRINT:' . $inst_name . '_max:MAX:' . $number_format . '\\l';
        }
    }//end foreach

    $rrdcmd = RRDTOOL;
    $count_cmd = count($cmd);
    for ($i = 1; $i < $count_cmd; $i++) {
        $rrdcmd .= ' ' . escapeshellarg($cmd[$i]);
    }

    return $rrdcmd;
}//end collectd_draw_meta_stack()

/**
 * Draw stack-graph for set of RRD files
 * @param array $opts Graph options like colors
 * @param array $sources List of array(name, file, ds)
 * @return string Commandline to call RRDGraph in order to generate the final graph
 */
function collectd_draw_meta_line(&$opts, &$sources)
{
    $timespan_def = null;
    $timespans = Config::get('timespan');
    if (! isset($opts['timespan'])) {
        $timespan_def = reset($timespans);
    } else {
        foreach ($timespans as &$ts) {
            if ($ts['name'] == $opts['timespan']) {
                $timespan_def = $ts;
            }
        }
    }

    if (! isset($opts['title'])) {
        $opts['title'] = 'Unknown title';
    }

    if (! isset($opts['rrd_opts'])) {
        $opts['rrd_opts'] = [];
    }

    if (! isset($opts['colors'])) {
        $opts['colors'] = [];
    }

    if (isset($opts['logarithmic']) && $opts['logarithmic']) {
        array_unshift($opts['rrd_opts'], '-o');
    }

    // $cmd = array(RRDTOOL, 'graph', '-', '-E', '-a', 'PNG', '-w', Config::get('rrd_width'), '-h', Config::get('rrd_height'), '-t', $opts['title']);
    // $cmd = array_merge($cmd, Config::get('rrd_opts_array'), $opts['rrd_opts']);
    $cmd = [
        RRDTOOL,
        'graph',
        '-',
        '-E',
        '-a',
        'PNG',
        '-w',
        Config::get('rrd_width'),
        '-h',
        Config::get('rrd_height'),
    ];

    if (Config::get('rrd_width') <= '300') {
        $small_opts = [
            '--font',
            'LEGEND:7:mono',
            '--font',
            'AXIS:6:mono',
            '--font-render-mode',
            'normal',
        ];
        $cmd = array_merge($cmd, $small_opts);
    }

    $max_inst_name = 0;

    foreach ($sources as &$inst_data) {
        $inst_name = $inst_data['name'];
        $file = $inst_data['file'];
        $ds = isset($inst_data['ds']) ? $inst_data['ds'] : 'value';

        if (strlen($inst_name) > $max_inst_name) {
            $max_inst_name = strlen($inst_name);
        }

        if (! is_file($file)) {
            continue;
        }

        $cmd[] = 'DEF:' . $inst_name . '_min=' . $file . ':' . $ds . ':MIN';
        $cmd[] = 'DEF:' . $inst_name . '_avg=' . $file . ':' . $ds . ':AVERAGE';
        $cmd[] = 'DEF:' . $inst_name . '_max=' . $file . ':' . $ds . ':MAX';
    }

    foreach ($sources as &$inst_data) {
        $inst_name = $inst_data['name'];
        $legend = sprintf('%s', $inst_name);
        while (strlen($legend) < $max_inst_name) {
            $legend .= ' ';
        }

        $number_format = isset($opts['number_format']) ? $opts['number_format'] : '%6.1lf';

        if (isset($opts['colors'][$inst_name])) {
            $line_color = new CollectdColor($opts['colors'][$inst_name]);
        } else {
            $line_color = new CollectdColor('random');
        }

        $cmd[] = 'LINE1:' . $inst_name . '_avg#' . $line_color->toString() . ':' . $legend;
        if (! (isset($opts['tinylegend']) && $opts['tinylegend'])) {
            $cmd[] = 'GPRINT:' . $inst_name . '_min:MIN:' . $number_format . '';
            $cmd[] = 'GPRINT:' . $inst_name . '_avg:AVERAGE:' . $number_format . '';
            $cmd[] = 'GPRINT:' . $inst_name . '_max:MAX:' . $number_format . '';
            $cmd[] = 'GPRINT:' . $inst_name . '_avg:LAST:' . $number_format . '\\l';
        }
    }//end foreach

    $rrdcmd = RRDTOOL;
    $count_cmd = count($cmd);
    for ($i = 1; $i < $count_cmd; $i++) {
        $rrdcmd .= ' ' . escapeshellarg($cmd[$i]);
    }

    return $rrdcmd;
}//end collectd_draw_meta_line()
