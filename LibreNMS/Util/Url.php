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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use App\Models\Port;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL as LaravelUrl;
use Illuminate\Support\Str;
use LibreNMS\Config;
use Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class Url
{
    /**
     * @param  Device|null  $device
     * @param  string|null  $text
     * @param  array  $vars
     * @param  int  $start
     * @param  int  $end
     * @param  int  $escape_text
     * @param  int  $overlib
     * @return string
     */
    public static function deviceLink($device, $text = '', $vars = [], $start = 0, $end = 0, $escape_text = 1, $overlib = 1)
    {
        if (! $device instanceof Device || ! $device->hostname) {
            return (string) $text;
        }

        if (! $device->canAccess(Auth::user())) {
            return $device->displayName();
        }

        if (! $start) {
            $start = Carbon::now()->subDay()->timestamp;
        }

        if (! $end) {
            $end = Carbon::now()->timestamp;
        }

        if (! $text) {
            $text = $device->displayName();
        }

        if ($escape_text) {
            $text = htmlentities($text);
        }

        $class = self::deviceLinkDisplayClass($device);
        $graphs = Graph::getOverviewGraphsForDevice($device);
        $url = Url::deviceUrl($device, $vars);

        // beginning of overlib box contains large hostname followed by hardware & OS details
        $contents = '<div><span class="list-large">' . $device->displayName() . '</span>';
        $devinfo = '';
        if ($device->hardware) {
            $devinfo .= htmlentities($device->hardware);
        }

        if ($device->os) {
            $devinfo .= ($devinfo ? ' - ' : '') . htmlentities(Config::getOsSetting($device->os, 'text'));
        }

        if ($device->version) {
            $devinfo .= ($devinfo ? ' - ' : '') . htmlentities($device->version);
        }

        if ($device->features) {
            $devinfo .= ' (' . htmlentities($device->features) . ')';
        }

        if ($devinfo) {
            $contents .= '<br />' . $devinfo;
        }

        if ($device->location_id) {
            $contents .= '<br />' . htmlentities($device->location ?? '');
        }

        $contents .= '</div><br />';

        foreach ((array) $graphs as $entry) {
            $graph = isset($entry['graph']) ? $entry['graph'] : 'unknown';
            $graphhead = isset($entry['text']) ? $entry['text'] : 'unknown';
            $contents .= '<div class="overlib-box">';
            $contents .= '<span class="overlib-title">' . $graphhead . '</span><br />';
            $contents .= Url::minigraphImage($device, $start, $end, $graph);
            $contents .= Url::minigraphImage($device, Carbon::now()->subWeek()->timestamp, $end, $graph);
            $contents .= '</div>';
        }

        if ($overlib == 0) {
            $link = $contents;
        } else {
            $contents = self::escapeBothQuotes($contents);
            $link = Url::overlibLink($url, $text, $contents, $class);
        }

        return $link;
    }

    /**
     * @param  Port  $port
     * @param  string  $text
     * @param  string  $type
     * @param  bool  $overlib
     * @param  bool  $single_graph
     * @return mixed|string
     */
    public static function portLink($port, $text = null, $type = null, $overlib = true, $single_graph = false)
    {
        if ($port === null) {
            return $text;
        }

        $label = Rewrite::normalizeIfName($port->getLabel());
        if (! $text) {
            $text = $label;
        }

        $content = '<div class=list-large>' . addslashes(htmlentities($port->device?->displayName() . ' - ' . $label)) . '</div>';
        if ($description = $port->getDescription()) {
            $content .= addslashes(htmlentities($description)) . '<br />';
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
        if (! $single_graph) {
            $graph_array['from'] = Carbon::now()->subWeek()->timestamp;
            $content .= self::graphTag($graph_array);
            $graph_array['from'] = Carbon::now()->subMonth()->timestamp;
            $content .= self::graphTag($graph_array);
            $graph_array['from'] = Carbon::now()->subYear()->timestamp;
            $content .= self::graphTag($graph_array);
        }

        $content .= '</div>';

        if (! $overlib) {
            return $content;
        } elseif ($port->canAccess(Auth::user())) {
            return self::overlibLink(self::portUrl($port), $text, $content, self::portLinkDisplayClass($port));
        }

        return Rewrite::normalizeIfName($text);
    }

    /**
     * @param  \App\Models\Sensor  $sensor
     * @param  string  $text
     * @param  string  $type
     * @param  bool  $overlib
     * @param  bool  $single_graph
     * @return mixed|string
     */
    public static function sensorLink($sensor, $text = null, $type = null, $overlib = true, $single_graph = false)
    {
        $label = $sensor->sensor_descr;
        if (! $text) {
            $text = $label;
        }

        $content = '<div class=list-large>' . addslashes(htmlentities($sensor->device->displayName() . ' - ' . $label)) . '</div>';

        $content .= "<div style=\'width: 850px\'>";
        $graph_array = [
            'type' => $type ?: 'sensor_' . $sensor->sensor_class,
            'legend' => 'yes',
            'height' => 100,
            'width' => 340,
            'to' => Carbon::now()->timestamp,
            'from' => Carbon::now()->subDay()->timestamp,
            'id' => $sensor->sensor_id,
        ];

        $content .= self::graphTag($graph_array);
        if (! $single_graph) {
            $graph_array['from'] = Carbon::now()->subWeek()->timestamp;
            $content .= self::graphTag($graph_array);
            $graph_array['from'] = Carbon::now()->subMonth()->timestamp;
            $content .= self::graphTag($graph_array);
            $graph_array['from'] = Carbon::now()->subYear()->timestamp;
            $content .= self::graphTag($graph_array);
        }

        $content .= '</div>';

        if (! $overlib) {
            return $content;
        }

        return self::overlibLink(self::sensorUrl($sensor), $text, $content, self::sensorLinkDisplayClass($sensor));
    }

    /**
     * @param  int|Device  $device
     * @param  array  $vars
     * @return string
     */
    public static function deviceUrl($device, $vars = [])
    {
        $routeParams = [($device instanceof Device) ? $device->device_id : (int) $device];
        if (isset($vars['tab'])) {
            $routeParams[] = $vars['tab'];
            unset($vars['tab']);
        }

        return route('device', $routeParams) . self::urlParams($vars);
    }

    public static function portUrl($port, $vars = [])
    {
        return self::generate(['page' => 'device', 'device' => $port->device_id, 'tab' => 'port', 'port' => $port->port_id], $vars);
    }

    public static function sensorUrl($sensor, $vars = [])
    {
        return self::generate(['page' => 'device', 'device' => $sensor->device_id, 'tab' => 'health', 'metric' => $sensor->sensor_class], $vars);
    }

    /**
     * @param  Port  $port
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

    /**
     * @param  Port  $port
     * @return string
     */
    public static function portErrorsThumbnail($port)
    {
        $graph_array = [
            'port_id' => $port->port_id,
            'graph_type' => 'port_errors',
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

        $url = url(Config::get('base_url', true) . $vars['page'] . '');
        unset($vars['page']);

        return $url . self::urlParams($vars);
    }

    /**
     * Generate url parameters to append to url
     * $prefix will only be prepended if there are parameters
     *
     * @param  array  $vars
     * @param  string  $prefix
     * @return string
     */
    private static function urlParams($vars, $prefix = '/')
    {
        $url = empty($vars) ? '' : $prefix;
        foreach ($vars as $var => $value) {
            if ($value == '0' || $value != '' && ! Str::contains($var, 'opt') && ! is_numeric($var)) {
                $url .= urlencode($var) . '=' . urlencode($value) . '/';
            }
        }

        return $url;
    }

    /**
     * @param  array|string  $args
     */
    public static function forExternalGraph($args): string
    {
        // handle pasted string
        if (is_string($args)) {
            $path = str_replace(url('/') . '/', '', $args);
            $args = self::parseLegacyPathVars($path);
        }

        return LaravelUrl::signedRoute('graph', $args);
    }

    /**
     * @param  array  $args
     * @return string
     */
    public static function graphTag($args)
    {
        $urlargs = [];
        foreach ($args as $key => $arg) {
            $urlargs[] = $key . '=' . ($arg === null ? '' : urlencode($arg));
        }

        return '<img src="' . url('graph.php') . '?' . implode('&amp;', $urlargs) . '" style="border:0;" />';
    }

    public static function graphPopup($args, $content = null, $link = null)
    {
        // Take $args and print day,week,month,year graphs in overlib, hovered over graph
        $original_from = $args['from'];
        $now = CarbonImmutable::now();

        $graph = $content ?: self::graphTag($args);
        $popup = '<div class=list-large>' . $args['popup_title'] . '</div>';
        $popup .= '<div style="width: 850px">';
        $args['width'] = 340;
        $args['height'] = 100;
        $args['legend'] = 'yes';
        $args['from'] = $now->subDay()->timestamp;
        $popup .= self::graphTag($args);
        $args['from'] = $now->subWeek()->timestamp;
        $popup .= self::graphTag($args);
        $args['from'] = $now->subMonth()->timestamp;
        $popup .= self::graphTag($args);
        $args['from'] = $now->subYear()->timestamp;
        $popup .= self::graphTag($args);
        $popup .= '</div>';

        $args['from'] = $original_from;

        $args['link'] = $link ?: self::generate($args, ['page' => 'graphs', 'height' => null, 'width' => null, 'bg' => null]);

        return self::overlibLink($args['link'], $graph, $popup, null);
    }

    public static function lazyGraphTag($args)
    {
        $urlargs = [];

        foreach ($args as $key => $arg) {
            $urlargs[] = $key . '=' . ($arg === null ? '' : urlencode($arg));
        }

        $tag = '<img class="img-responsive" src="' . url('graph.php') . '?' . implode('&amp;', $urlargs) . '" style="border:0;"';

        if (Config::get('enable_lazy_load', true)) {
            return $tag . ' loading="lazy" />';
        }

        return $tag . ' />';
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
     * @param  Device  $device
     * @param  int  $start
     * @param  int  $end
     * @param  string  $type
     * @param  string  $legend
     * @param  int  $width
     * @param  int  $height
     * @param  string  $sep
     * @param  string  $class
     * @param  int  $absolute_size
     * @return string
     */
    public static function minigraphImage($device, $start, $end, $type, $legend = 'no', $width = 275, $height = 100, $sep = '&amp;', $class = 'minigraph-image', $absolute_size = 0)
    {
        $vars = ['device=' . $device->device_id, "from=$start", "to=$end", "width=$width", "height=$height", "type=$type", "legend=$legend", "absolute=$absolute_size"];

        return '<img class="' . $class . '" width="' . $width . '" height="' . $height . '" src="' . url('graph.php') . '?' . implode($sep, $vars) . '">';
    }

    /**
     * @param  Device  $device
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
     * @param  Port  $port
     * @return string
     */
    public static function portLinkDisplayClass($port)
    {
        if ($port->ifAdminStatus == 'down') {
            return 'interface-admindown';
        }

        if ($port->ifAdminStatus == 'up' && $port->ifOperStatus != 'up') {
            return 'interface-updown';
        }

        return 'interface-upup';
    }

    /**
     * Get html class for a sensor
     *
     * @param  \App\Models\Sensor  $sensor
     * @return string
     */
    public static function sensorLinkDisplayClass($sensor)
    {
        if ($sensor->sensor_current > $sensor->sensor_limit) {
            return 'sensor-high';
        }

        if ($sensor->sensor_current < $sensor->sensor_limit_low) {
            return 'sensor-low';
        }

        return 'sensor-ok';
    }

    /**
     * @param  string  $os
     * @param  string|null  $feature
     * @param  string  $icon
     * @param  string  $dir  directory to search in (images/os/ or images/logos)
     * @return string
     */
    public static function findOsImage($os, $feature, $icon = null, $dir = 'images/os/')
    {
        $possibilities = [$icon];

        if ($os) {
            if ($os == 'linux' && $feature) {
                // first, prefer the first word of $feature
                $distro = Str::before(strtolower(trim($feature)), ' ');
                $possibilities[] = "$distro.svg";
                $possibilities[] = "$distro.png";

                // second, prefer the first two words of $feature (i.e. 'Red Hat' becomes 'redhat')
                if (strpos($feature, ' ') !== false) {
                    $distro = Str::replaceFirst(' ', '', strtolower(trim($feature)));
                    $distro = Str::before($distro, ' ');
                    $possibilities[] = "$distro.svg";
                    $possibilities[] = "$distro.png";
                }
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

    /**
     * parse a legacy path (one without ? or &)
     *
     * @param  string  $path
     * @return ParameterBag
     */
    public static function parseLegacyPath($path)
    {
        $parts = array_filter(explode('/', $path), function ($part) {
            return Str::contains($part, '=');
        });

        $vars = [];
        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part);
            $vars[$key] = $value;
        }

        return new ParameterBag($vars);
    }

    /**
     * Parse options from the url including get/post parameters and any url segments containing an =
     *
     * @param  int|string|null  $key  Optional key to pull from the options
     * @param  mixed  $default  The default value to return when the given key does not exist
     * @return array|mixed|null
     */
    public static function parseOptions($key = null, $default = null)
    {
        $request = request();
        $options = $request->all();

        foreach (explode('/', $request->path()) as $segment) {
            $segment = urldecode($segment);
            if (Str::contains($segment, '=')) {
                [$name, $value] = explode('=', $segment, 2);
                $options[$name] = $value;
            }
        }

        return is_null($key) ? $options : $options[$key] ?? $default;
    }

    /**
     * Parse variables from legacy path /key=value/key=value or regular get/post variables
     */
    public static function parseLegacyPathVars(?string $path = null): array
    {
        $vars = [];
        $parsed_get_vars = [];
        if (empty($path)) {
            $path = Request::path();
        } elseif (Str::startsWith($path, 'http') || str_contains($path, '?')) {
            $parsed_url = parse_url($path);
            $path = $parsed_url['path'] ?? '';
            parse_str($parsed_url['query'] ?? '', $parsed_get_vars);
        }

        // don't parse the subdirectory, if there is one in the path
        $base_url = parse_url(Config::get('base_url'))['path'] ?? '';
        if (strlen($base_url) > 1) {
            $segments = explode('/', trim(str_replace($base_url, '', $path), '/'));
        } else {
            $segments = explode('/', trim($path, '/'));
        }

        // parse the path
        foreach ($segments as $pos => $segment) {
            $segment = urldecode($segment);
            if ($pos === 0) {
                $vars['page'] = $segment;
            } else {
                [$name, $value] = array_pad(explode('=', $segment), 2, null);
                if ($value === null) {
                    if ($vars['page'] == 'device' && $pos < 3) {
                        // translate laravel device routes properly
                        $vars[$pos === 1 ? 'device' : 'tab'] = $name;
                    } elseif ($name) {
                        $vars[$name] = 'yes';
                    }
                } else {
                    $vars[$name] = $value;
                }
            }
        }

        $vars = array_merge($vars, $parsed_get_vars);

        // don't leak login data
        unset($vars['username'], $vars['password']);

        return $vars;
    }

    private static function escapeBothQuotes($string)
    {
        return str_replace(["'", '"'], "\'", $string);
    }
}
