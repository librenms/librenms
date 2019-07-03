<?php
/**
 * Url.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use App\Models\Port;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use LibreNMS\Config;

class Url
{
    /**
     * @param Device $device
     * @param string $text
     * @param array $vars
     * @param int $start
     * @param int $end
     * @param int $escape_text
     * @param int $overlib
     * @return string
     */
    public static function deviceLink($device, $text = null, $vars = [], $start = 0, $end = 0, $escape_text = 1, $overlib = 1)
    {
        if (!$start) {
            $start = Carbon::now()->subDay(1)->timestamp;
        }

        if (!$end) {
            $end = Carbon::now()->timestamp;
        }

        if (!$text) {
            $text = $device->displayName();
        }

        if ($escape_text) {
            $text = htmlentities($text);
        }

        $class = self::deviceLinkDisplayClass($device);
        $graphs = self::getOverviewGraphsForDevice($device);
        $url = Url::deviceUrl($device, $vars);

        // beginning of overlib box contains large hostname followed by hardware & OS details
        $contents = '<div><span class="list-large">' . $device->displayName() . '</span>';
        if ($device->hardware) {
            $contents .= ' - ' . htmlentities($device->hardware);
        }

        if ($device->os) {
            $contents .= ' - ' . htmlentities(Config::getOsSetting($device->os, 'text'));
        }

        if ($device->version) {
            $contents .= ' ' . htmlentities($device->version);
        }

        if ($device->features) {
            $contents .= ' (' . htmlentities($device->features) . ')';
        }

        if ($device->location_id) {
            $contents .= ' - ' . htmlentities($device->location);
        }

        $contents .= '</div>';

        foreach ((array)$graphs as $entry) {
            $graph = isset($entry['graph']) ? $entry['graph'] : 'unknown';
            $graphhead = isset($entry['text']) ? $entry['text'] : 'unknown';
            $contents .= '<div class="overlib-box">';
            $contents .= '<span class="overlib-title">' . $graphhead . '</span><br />';
            $contents .= Url::minigraphImage($device, $start, $end, $graph);
            $contents .= Url::minigraphImage($device, Carbon::now()->subWeek(1)->timestamp, $end, $graph);
            $contents .= '</div>';
        }

        if ($overlib == 0) {
            $link = $contents;
        } else {
            $contents = self::escapeBothQuotes($contents);
            $link = Url::overlibLink($url, $text, $contents, $class);
        }

        if ($device->canAccess(Auth::user())) {
            return $link;
        } else {
            return $device->displayName();
        }
    }

    /**
     * @param Port $port
     * @param string $text
     * @param string $type
     * @param boolean $overlib
     * @param boolean $single_graph
     * @return mixed|string
     */
    public static function portLink($port, $text = null, $type = null, $overlib = true, $single_graph = false)
    {

        $label = Rewrite::normalizeIfName($port->getLabel());
        if (!$text) {
            $text = $label;
        }

        $content = '<div class=list-large>' . addslashes(htmlentities($port->device->displayName() . ' - ' . $label)) . '</div>';
        if ($port->ifAlias) {
            $content .= addslashes(htmlentities($port->ifAlias)) . '<br />';
        }

        $content .= "<div style=\'width: 850px\'>";
        $graph_array = [
            'type' => $type ?: 'port_bits',
            'legend' => 'yes',
            'height' => 100,
            'width' => 340,
            'to' => Carbon::now()->timestamp,
            'from' => Carbon::now()->subDay()->timestamp,
            'id' => $port->port_id,
        ];

        $content .= self::graphTag($graph_array);
        if (!$single_graph) {
            $graph_array['from'] = Carbon::now()->subWeek()->timestamp;
            $content .= self::graphTag($graph_array);
            $graph_array['from'] = Carbon::now()->subMonth()->timestamp;
            $content .= self::graphTag($graph_array);
            $graph_array['from'] = Carbon::now()->subYear()->timestamp;
            $content .= self::graphTag($graph_array);
        }

        $content .= '</div>';

        if (!$overlib) {
            return $content;
        } elseif ($port->canAccess(Auth::user())) {
            return self::overlibLink(self::portUrl($port), $text, $content, self::portLinkDisplayClass($port));
        }

        return Rewrite::normalizeIfName($text);
    }

    public static function deviceUrl($device, $vars = [])
    {
        return self::generate(['page' => 'device', 'device' => $device->device_id], $vars);
    }

    public static function portUrl($port, $vars = [])
    {
        return self::generate(['page' => 'device', 'device' => $port->device_id, 'tab' => 'port', 'port' => $port->port_id], $vars);
    }

    /**
     * @param Port $port
     * @return string
     */
    public static function portThumbnail($port)
    {
        $graph_array = [
            'port_id' => $port->port_id,
            'graph_type' => 'port_bits',
            'from' => Carbon::now()->subDay()->timestamp,
            'to' => Carbon::now()->timestamp,
            'width' => 150,
            'height' => 21,
        ];

        return self::portImage($graph_array);
    }

    public static function portImage($args)
    {
        if (empty($args['bg'])) {
            $args['bg'] = 'FFFFFF00';
        }

        return '<img src="' . url('graph.php') . '?type=' . $args['graph_type'] . '&amp;id=' . $args['port_id'] . '&amp;from=' . $args['from'] . '&amp;to=' . $args['to'] . '&amp;width=' . $args['width'] . '&amp;height=' . $args['height'] . '&amp;bg=' . $args['bg'] . '">';
    }

    public static function generate($vars, $new_vars = [])
    {
        $vars = array_merge($vars, $new_vars);

        $url = url($vars['page']) . '/';
        unset($vars['page']);

        foreach ($vars as $var => $value) {
            if ($value == '0' || $value != '' && !str_contains($var, 'opt') && !is_numeric($var)) {
                $url .= $var . '=' . urlencode($value) . '/';
            }
        }

        return $url;
    }

    /**
     * @param array $args
     * @return string
     */
    public static function graphTag($args)
    {
        $urlargs = [];
        foreach ($args as $key => $arg) {
            $urlargs[] = $key . '=' . urlencode($arg);
        }

        return '<img src="' . url('graph.php') . '?' . implode('&amp;', $urlargs) . '" style="border:0;" />';
    }

    public static function lazyGraphTag($args)
    {
        $urlargs = [];

        foreach ($args as $key => $arg) {
            $urlargs[] = $key . "=" . urlencode($arg);
        }


        if (Config::get('enable_lazy_load', true)) {
            return '<img class="lazy img-responsive" data-original="' . url('graph.php') . '?' . implode('&amp;', $urlargs) . '" style="border:0;" />';
        }

        return '<img class="img-responsive" src="' . url('graph.php') . '?' . implode('&amp;', $urlargs) . '" style="border:0;" />';
    }

    public static function overlibLink($url, $text, $contents, $class = null)
    {
        $contents = "<div class=\'overlib-contents\'>" . $contents . '</div>';
        $contents = str_replace('"', "\'", $contents);
        if ($class === null) {
            $output = '<a href="' . $url . '"';
        } else {
            $output = '<a class="' . $class . '" href="' . $url . '"';
        }

        if (Config::get('web_mouseover', true)) {
            $defaults = Config::get('overlib_defaults', ",FGCOLOR,'#ffffff', BGCOLOR, '#e5e5e5', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#555555', TEXTCOLOR, '#3e3e3e'");
            $output .= " onmouseover=\"return overlib('$contents'$defaults,WRAP,HAUTO,VAUTO); \" onmouseout=\"return nd();\">";
        } else {
            $output .= '>';
        }

        $output .= $text . '</a>';

        return $output;
    }

    public static function overlibContent($graph_array, $text)
    {
        $overlib_content = '<div class=overlib><span class=overlib-text>' . $text . '</span><br />';

        $now = Carbon::now();

        foreach ([1, 7, 30, 365] as $days) {
            $graph_array['from'] = $now->subDays($days)->timestamp;
            $overlib_content .= self::escapeBothQuotes(self::graphTag($graph_array));
        }

        $overlib_content .= '</div>';

        return $overlib_content;
    }

    /**
     * Generate minigraph image url
     *
     * @param Device $device
     * @param int $start
     * @param int $end
     * @param string $type
     * @param string $legend
     * @param int $width
     * @param int $height
     * @param string $sep
     * @param string $class
     * @param int $absolute_size
     * @return string
     */
    public static function minigraphImage($device, $start, $end, $type, $legend = 'no', $width = 275, $height = 100, $sep = '&amp;', $class = 'minigraph-image', $absolute_size = 0)
    {
        $vars = ['device=' . $device->device_id, "from=$start", "to=$end", "width=$width", "height=$height", "type=$type", "legend=$legend", "absolute=$absolute_size"];
        return '<img class="' . $class . '" width="' . $width . '" height="' . $height . '" src="' . url('graph.php') . '?' . implode($sep, $vars) . '">';
    }

    private static function getOverviewGraphsForDevice($device)
    {
        if ($device->snmp_disable) {
            return Config::getOsSetting('ping', 'over');
        }

        if ($graphs = Config::getOsSetting($device->os, 'over')) {
            return $graphs;
        }

        if ($os_group = Config::getOsSetting($device->os, 'os_group')) {
            $name = key($os_group);
            if (isset($os_group[$name]['over'])) {
                return $os_group[$name]['over'];
            }
        }

        return Config::getOsSetting('default', 'over');
    }

    /**
     * @param Device $device
     * @return string
     */
    private static function deviceLinkDisplayClass($device)
    {
        if ($device->disabled) {
            return 'list-device-disabled';
        }

        if ($device->ignore) {
            return $device->status ? 'list-device-ignored-up' : 'list-device-ignored';
        }

        return $device->status ? 'list-device' : 'list-device-down';
    }

    /**
     * Get html class for a port using ifAdminStatus and ifOperStatus
     *
     * @param Port $port
     * @return string
     */
    public static function portLinkDisplayClass($port)
    {
        if ($port->ifAdminStatus == "down") {
            return "interface-admindown";
        }

        if ($port->ifAdminStatus == "up" && $port->ifOperStatus == "down") {
            return "interface-updown";
        }

        return "interface-upup";
    }

    /**
     * @param string $os
     * @param string $feature
     * @param string $icon
     * @param string $dir directory to search in (images/os/ or images/logos)
     * @return string
     */
    public static function findOsImage($os, $feature, $icon = null, $dir = 'images/os/')
    {
        $possibilities = [$icon];

        if ($os) {
            if ($os == "linux") {
                $distro = Str::before(strtolower(trim($feature)), ' ');
                $possibilities[] = "$distro.svg";
                $possibilities[] = "$distro.png";
            }
            $os_icon = Config::getOsSetting($os, 'icon', $os);
            $possibilities[] = "$os_icon.svg";
            $possibilities[] = "$os_icon.png";
        }

        foreach ($possibilities as $file) {
            if (is_file(Config::get('html_dir') . "/$dir" . $file)) {
                return $file;
            }
        }

        // fallback to the generic icon
        return 'generic.svg';
    }

    private static function escapeBothQuotes($string)
    {
        return str_replace(["'", '"'], "\'", $string);
    }
}
