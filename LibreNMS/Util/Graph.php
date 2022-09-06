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

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Exceptions\RrdGraphException;
use Rrd;

class Graph
{
    const BASE64_OUTPUT = 1; // BASE64 encoded image data
    const INLINE_BASE64 = 2; // img src inline base64 image
    const COMMAND_ONLY = 4; // just print the command

    /**
     * Fetch a graph image (as string) based on the given $vars
     * Optionally, override the output format to base64
     *
     * @param  array|string  $vars
     * @param  int  $flags  Flags for controlling graph generating options.
     * @return string
     *
     * @throws \LibreNMS\Exceptions\RrdGraphException
     */
    public static function get($vars, int $flags = 0): string
    {
        define('IGNORE_ERRORS', true);
        chdir(base_path());

        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        // handle possible graph url input
        if (is_string($vars)) {
            $vars = Url::parseLegacyPathVars($vars);
        }

        [$type, $subtype] = extract_graph_type($vars['type']);

        $graph_title = '';
        if (isset($vars['device'])) {
            $device = device_by_id_cache(is_numeric($vars['device']) ? $vars['device'] : getidbyname($vars['device']));
            DeviceCache::setPrimary($device['device_id']);

            //set default graph title
            $graph_title = DeviceCache::getPrimary()->displayName();
        }

        // variables for included graphs
        $width = $vars['width'] ?? 400;
        $height = $vars['height'] ?? $width / 3;
        $title = $vars['title'] ?? '';
        $vertical = $vars['vertical'] ?? '';
        $legend = $vars['legend'] ?? false;
        $output = $vars['output'] ?? 'default';
        $from = parse_at_time($vars['from'] ?? '-1d');
        $to = empty($vars['to']) ? time() : parse_at_time($vars['to']);
        $period = ($to - $from);
        $prev_from = ($from - $period);
        $graph_image_type = $vars['graph_type'] ?? Config::get('webui.graph_type');
        Config::set('webui.graph_type', $graph_image_type); // set in case accessed elsewhere
        $rrd_options = '';
        $rrd_filename = null;

        $auth = Auth::guest(); // if user not logged in, assume we authenticated via signed url, allow_unauth_graphs or allow_unauth_graphs_cidr
        require base_path("/includes/html/graphs/$type/auth.inc.php");
        if (! $auth) {
            // We are unauthenticated :(
            throw new RrdGraphException('No Authorization', 'No Auth', $width, $height);
        }

        if (is_customoid_graph($type, $subtype)) {
            $unit = $vars['unit'];
            require base_path('/includes/html/graphs/customoid/customoid.inc.php');
        } elseif (is_file(base_path("/includes/html/graphs/$type/$subtype.inc.php"))) {
            require base_path("/includes/html/graphs/$type/$subtype.inc.php");
        } else {
            throw new RrdGraphException("{$type}_$subtype template missing", "{$type}_$subtype missing", $width, $height);
        }

        if ($graph_image_type === 'svg') {
            $rrd_options .= ' --imgformat=SVG';
            if ($width < 350) {
                $rrd_options .= ' -m 0.75 -R light';
            }
        }

        // command output requested
        if ($flags & self::COMMAND_ONLY) {
            $cmd_output = "<div class='infobox'>";
            $cmd_output .= "<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>";
            $cmd_output .= "<pre class='rrd-pre'>";
            $cmd_output .= escapeshellcmd('rrdtool ' . Rrd::buildCommand('graph', Config::get('temp_dir') . '/' . strgen(), $rrd_options));
            $cmd_output .= '</pre>';
            try {
                $cmd_output .= Rrd::graph($rrd_options);
            } catch (RrdGraphException $e) {
                $cmd_output .= "<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>";
                $cmd_output .= "<pre class='rrd-pre'>";
                $cmd_output .= $e->getMessage();
                $cmd_output .= '</pre>';
            }
            $cmd_output .= '</div>';

            return $cmd_output;
        }

        if (empty($rrd_options)) {
            throw new RrdGraphException('Graph Definition Error', 'Def Error', $width, $height);
        }

        // Generating the graph!
        try {
            $image_data = Rrd::graph($rrd_options);

            // output the graph int the desired format
            if (Debug::isEnabled()) {
                return '<img src="data:' . self::imageType($graph_image_type) . ';base64,' . base64_encode($image_data) . '" alt="graph" />';
            } elseif ($flags & self::BASE64_OUTPUT || $output == 'base64') {
                return base64_encode($image_data);
            } elseif ($flags & self::INLINE_BASE64 || $output == 'inline-base64') {
                return 'data:' . self::imageType($graph_image_type) . ';base64,' . base64_encode($image_data);
            }

            return $image_data; // raw data
        } catch (RrdGraphException $e) {
            // preserve original error if debug is enabled, otherwise make it a little more user friendly
            if (Debug::isEnabled()) {
                throw $e;
            }

            if (isset($rrd_filename) && ! Rrd::checkRrdExists($rrd_filename)) {
                throw new RrdGraphException('No Data file' . basename($rrd_filename), 'No Data', $width, $height, $e->getCode(), $e->getImage());
            }

            throw new RrdGraphException('Error: ' . $e->getMessage(), 'Draw Error', $width, $height, $e->getCode(), $e->getImage());
        }
    }

    public static function getTypes(): array
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
    public static function getSubtypes($type, $device = null): array
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
    public static function isMibGraph($type, $subtype): bool
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
     * Get the http content type of the image
     *
     * @param  string  $type  svg or png
     * @return string
     */
    public static function imageType(string $type): string
    {
        return $type === 'svg' ? 'image/svg+xml' : 'image/png';
    }

    /**
     * Create image to output text instead of a graph.
     *
     * @param  string  $text  Error message to display
     * @param  string|null  $short_text  Error message for smaller graph images
     * @param  int  $width  Width of graph image (defaults to 300)
     * @param  int|null  $height  Height of graph image (defaults to width / 3)
     * @param  int[]  $color  Color of text, defaults to dark red
     * @return string the generated image
     */
    public static function error(string $text, ?string $short_text, int $width = 300, ?int $height = null, array $color = [128, 0, 0]): string
    {
        $type = Config::get('webui.graph_type');
        $height = $height ?? $width / 3;

        if ($short_text !== null && $width < 200) {
            $text = $short_text;
        }

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

        $px = (int) ((imagesx($img) - 7.5 * strlen($text)) / 2);
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
