<?php
/**
 * Graph.php
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
use LibreNMS\Config;
use Rrd;

class Graph
{
    public static function get($vars): string
    {
        global $debug;
        define('IGNORE_ERRORS', true);

        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        [$type, $subtype] = extract_graph_type($vars['type']);

        if (isset($vars['device'])) {
            $device = is_numeric($vars['device'])
                ? device_by_id_cache($vars['device'])
                : device_by_name($vars['device']);
        }

        // FIXME -- remove these
        $width = $vars['width'];
        $height = $vars['height'];
        $title = $vars['title'] ?? '';
        $vertical = $vars['vertical'] ?? '';
        $legend = $vars['legend'] ?? false;
        $output = (! empty($vars['output']) ? $vars['output'] : 'default');
        $from = empty($vars['from']) ? Config::get('time.day') : parse_at_time($vars['from']);
        $to = empty($vars['to']) ? Config::get('time.now') : parse_at_time($vars['to']);
        $period = ($to - $from);
        $prev_from = ($from - $period);

        $graph_image_type = $vars['graph_type'] ?? Config::get('webui.graph_type');
        $rrd_options = '';

        $auth = null; // FIXME correct?
        require base_path("/includes/html/graphs/$type/auth.inc.php");

        //set default graph title
        $graph_title = format_hostname($device);

        if ($auth && is_customoid_graph($type, $subtype)) {
            $unit = $vars['unit'];
            require base_path('/includes/html/graphs/customoid/customoid.inc.php');
        } elseif ($auth && is_file(base_path("/includes/html/graphs/$type/$subtype.inc.php"))) {
            require base_path("/includes/html/graphs/$type/$subtype.inc.php");
        } else {
            return self::error("$type*$subtype template missing", $vars);
        }

        if (! empty($error_msg)) {
            // We have an error :(
            return self::error($error_msg, $vars);
        }

        if ($auth === null) {
            // We are unauthenticated :(
            return self::error($width < 200 ? 'No Auth' : 'No Authorization', $vars);
        }

        if ($graph_image_type === 'svg') {
            $rrd_options .= ' --imgformat=SVG';
            if ($width < 350) {
                $rrd_options .= ' -m 0.75 -R light';
            }
        }

        // command output requested
        if (! empty($command_only)) {
            $cmd_output = "<div class='infobox'>";
            $cmd_output .= "<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>";
            $cmd_output .= "<pre class='rrd-pre'>";
            $cmd_output .= escapeshellcmd('rrdtool ' . Rrd::buildCommand('graph', Config::get('temp_dir') . '/' . strgen(), $rrd_options));
            $cmd_output .= '</pre>';
            try {
                $cmd_output .= Rrd::graph($rrd_options);
            } catch (\LibreNMS\Exceptions\RrdGraphException $e) {
                $cmd_output .= "<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>";
                $cmd_output .= "<pre class='rrd-pre'>";
                $cmd_output .= $e->getMessage();
                $cmd_output .= '</pre>';
            }
            $cmd_output .= '</div>';

            return $cmd_output;
        }

        // graph sent file not found flag
        if (! empty($no_file)) {
            return self::error($width < 200 ? 'No Data' : 'No Data file ' . $no_file, $vars);
        }

        if (empty($rrd_options)) {
            return self::error($width < 200 ? 'Def Error' : 'Graph Definition Error', $vars);
        }

        // Generating the graph!
        try {
            $image_data = Rrd::graph($rrd_options);

            // output the graph
            if (\LibreNMS\Util\Debug::isEnabled()) {
                return '<img src="data:' . get_image_type($graph_image_type) . ';base64,' . base64_encode($image_data) . '" alt="graph" />';
            } else {
                return $output === 'base64' ? base64_encode($image_data) : $image_data;
            }
        } catch (\LibreNMS\Exceptions\RrdGraphException $e) {
            if (\LibreNMS\Util\Debug::isEnabled()) {
                throw $e;
            }

            if (isset($rrd_filename) && ! Rrd::checkRrdExists($rrd_filename)) {
                return self::error($width < 200 ? 'No Data' : 'No Data file ' . basename($rrd_filename), $vars);
            }

            return self::error($width < 200 ? 'Draw Error' : 'Error Drawing Graph: ' . $e->getMessage(), $vars);
        }

    }

    public static function getTypes()
    {
        return ['device', 'port', 'application', 'munin', 'service'];
    }

    /**
     * Get an array of all graph subtypes for the given type
     *
     * @param  string  $type
     * @param  Device  $device
     * @return array
     */
    public static function getSubtypes($type, $device = null)
    {
        $types = [];

        // find the subtypes defined in files
        foreach (glob(base_path("/includes/html/graphs/$type/*.inc.php")) as $file) {
            $type = basename($file, '.inc.php');
            if ($type != 'auth') {
                $types[] = $type;
            }
        }

        if ($device != null) {
            // find the MIB subtypes
            $graphs = $device->graphs->pluck('graph');

            foreach (Config::get('graph_types') as $type => $type_data) {
                foreach (array_keys($type_data) as $subtype) {
                    if ($graphs->contains($subtype) && self::isMibGraph($type, $subtype)) {
                        $types[] = $subtype;
                    }
                }
            }
        }

        sort($types);

        return $types;
    }

    /**
     * Check if the given graph is a mib graph
     *
     * @param  string  $type
     * @param  string  $subtype
     * @return bool
     */
    public static function isMibGraph($type, $subtype)
    {
        return Config::get("graph_types.$type.$subtype.section") == 'mib';
    }

    public static function getOverviewGraphsForDevice($device)
    {
        if ($device->snmp_disable) {
            return Config::getOsSetting('ping', 'over');
        }

        if ($graphs = Config::getOsSetting($device->os, 'over')) {
            return $graphs;
        }

        $os_group = Config::getOsSetting($device->os, 'group');

        return Config::get("os_group.$os_group.over", Config::get('os.default.over'));
    }

    /**
     * Create image to output text instead of a graph.
     *
     * @param  string  $text
     * @param  array  $vars
     * @param  int[]  $color
     */
    public static function error($text, $vars = [], $color = [128, 0, 0]): string
    {
        $type = Config::get('webui.graph_type');

        $width = (int) ($vars['width'] ?? 150);
        $height = (int) ($vars['height'] ?? 60);

        if ($type === 'svg') {
            $rgb = implode(', ', $color);
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg"
xmlns:xhtml="http://www.w3.org/1999/xhtml"
viewBox="0 0 $width $height"
preserveAspectRatio="xMinYMin">
<foreignObject x="0" y="0" width="$width" height="$height" transform="translate(0,0)">
      <xhtml:div style="display:table; width:{$width}px; height:{$height}px; overflow:hidden;">
         <xhtml:div style="display:table-cell; vertical-align:middle;">
            <xhtml:div style="color:rgb($rgb); text-align:center; font-family:sans-serif; font-size:0.6em;">$text</xhtml:div>
         </xhtml:div>
      </xhtml:div>
   </foreignObject>
</svg>
SVG;
        }

        $img = imagecreate($width, $height);
        imagecolorallocatealpha($img, 255, 255, 255, 127); // transparent background

        $px = ((imagesx($img) - 7.5 * strlen($text)) / 2);
        $font = $width < 200 ? 3 : 5;
        imagestring($img, $font, $px, ($height / 2 - 8), $text, imagecolorallocate($img, ...$color));

        // Output the image
        ob_start();
        imagepng($img);
        $output = ob_get_clean();
        ob_end_clean();
        imagedestroy($img);

        return $output;
    }

}
