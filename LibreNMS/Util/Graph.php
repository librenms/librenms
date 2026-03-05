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
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Data\Graphing\GraphImage;
use LibreNMS\Data\Graphing\GraphParameters;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\RrdGraphException;
use Rrd;

class Graph
{
    const BASE64_OUTPUT = 1; // BASE64 encoded image data
    const INLINE_BASE64 = 2; // img src inline base64 image
    const IMAGE_PNG = 4; // img src inline base64 image
    const IMAGE_SVG = 8; // img src inline base64 image

    /**
     * Convenience helper to specify desired image output
     *
     * @param  array|string  $vars
     * @param  int  $flags
     * @return string
     */
    public static function getImageData($vars, int $flags = 0): string
    {
        if ($flags & self::IMAGE_PNG) {
            $vars['graph_type'] = 'png';
        }

        if ($flags & self::IMAGE_SVG) {
            $vars['graph_type'] = 'svg';
        }

        if ($flags & self::INLINE_BASE64) {
            return self::getImage($vars)->inline();
        }

        if ($flags & self::BASE64_OUTPUT) {
            return self::getImage($vars)->base64();
        }

        return self::getImage($vars)->data;
    }

    /**
     * Fetch a GraphImage based on the given $vars
     * Catches errors generated and always returns GraphImage
     *
     * @param  array|string  $vars
     * @return GraphImage
     */
    public static function getImage($vars): GraphImage
    {
        try {
            return self::get($vars);
        } catch (RrdGraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            return new GraphImage(ImageFormat::forGraph($vars['graph_type'] ?? null), 'Error', $e->generateErrorImage());
        }
    }

    /**
     * Fetch a GraphImage based on the given $vars
     *
     * @param  array|string  $vars
     * @return GraphImage
     *
     * @throws RrdGraphException
     */
    public static function get($vars): GraphImage
    {
        if (! defined('IGNORE_ERRORS')) {
            define('IGNORE_ERRORS', true);
        }

        chdir(base_path());

        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        // handle possible graph url input
        if (is_string($vars)) {
            $vars = Url::parseLegacyPathVars($vars);
        }

        if (isset($vars['device'])) {
            $device = device_by_id_cache(is_numeric($vars['device']) ? $vars['device'] : getidbyname($vars['device']));
            DeviceCache::setPrimary($device['device_id']);
        }

        // variables for included graphs
        $graph_params = new GraphParameters($vars);
        // set php variables for legacy graphs
        $type = $graph_params->type;
        $subtype = $graph_params->subtype;
        $height = $graph_params->height;
        $width = $graph_params->width;
        $from = $graph_params->from;
        $to = $graph_params->to;
        $period = $graph_params->period;
        $prev_from = $graph_params->prev_from;
        $inverse = $graph_params->inverse;
        $in = $graph_params->in;
        $out = $graph_params->out;
        $float_precision = $graph_params->float_precision;
        $title = $graph_params->visible('title');
        $nototal = ! $graph_params->visible('total');
        $nodetails = ! $graph_params->visible('details');
        $noagg = ! $graph_params->visible('aggregate');

        $rrd_options = [];
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
        } elseif (is_file(base_path("/includes/html/graphs/$type/generic.inc.php"))) {
            require base_path("/includes/html/graphs/$type/generic.inc.php");
        } else {
            throw new RrdGraphException("{$type}_$subtype template missing", "{$type}_$subtype missing", $width, $height);
        }

        if (empty($rrd_options)) { // @phpstan-ignore empty.variable ($rrd_options is populated by included graph templates)
            throw new RrdGraphException('Graph Definition Error', 'Def Error', $width, $height);
        }

        $rrd_options = [...$graph_params->toRrdOptions(), ...$rrd_options]; // @phpstan-ignore deadCode.unreachable

        // Generating the graph!
        try {
            $image_data = Rrd::graph($rrd_options);

            return new GraphImage($graph_params->imageFormat, $graph_params->getTitle(), $image_data);
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

            foreach (LibrenmsConfig::get('graph_types') as $type => $type_data) {
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
        return LibrenmsConfig::get("graph_types.$type.$subtype.section") == 'mib';
    }

    public static function getOverviewGraphsForDevice(Device $device): array
    {
        if ($device->snmp_disable) {
            return Arr::wrap(LibrenmsConfig::getOsSetting('ping', 'over'));
        }

        if ($graphs = LibrenmsConfig::getOsSetting($device->os, 'over')) {
            return Arr::wrap($graphs);
        }

        $os_group = LibrenmsConfig::getOsSetting($device->os, 'group');

        return Arr::wrap(LibrenmsConfig::get("os_group.$os_group.over", LibrenmsConfig::get('os.default.over')));
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
        $type = LibrenmsConfig::get('webui.graph_type');
        $height ??= $width / 3;

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
        imagestring($img, $font, $px, $height / 2 - 8, $text, imagecolorallocate($img, ...$color));

        // Output the image
        ob_start();
        imagepng($img);
        $output = ob_get_clean();
        ob_end_clean();
        imagedestroy($img);

        return $output;
    }
}
