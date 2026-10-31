<?php

namespace LibreNMS\Util;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Enum\GraphOutput;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\GraphException;

class Graph
{
    /**
     * Convenience helper to specify desired image output
     */
    public static function getImageData(array|string $vars, ?ImageFormat $format = null, ?GraphOutput $output = null): string
    {
        $vars = is_string($vars) ? Url::parseLegacyPathVars($vars) : $vars;

        if ($format !== null) {
            $vars['graph_type'] = $format->value;
        }

        $image = self::getImage($vars);

        return match ($output) {
            GraphOutput::Base64 => $image->base64(),
            GraphOutput::Inline => $image->inline(),
            default => $image->data,
        };
    }

    /**
     * Fetch a GraphImage based on the given $vars
     * Catches errors generated and always returns GraphImage
     */
    public static function getImage(array|string $vars): GraphImage
    {
        $vars = is_string($vars) ? Url::parseLegacyPathVars($vars) : $vars;

        try {
            return app(GraphFactory::class)->graphFor($vars['type'] ?? '', $vars)->render();
        } catch (GraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            return self::errorImage(new GraphParameters($vars), $e->getMessage(), $e->shortText);
        }
    }

    /**
     * An image with the error text, sized and formatted as the requested graph
     */
    public static function errorImage(GraphParameters $params, string $text, ?string $short_text = null): GraphImage
    {
        $width = $params->width > 0 ? $params->width : 300;
        $height = $params->height > 0 ? $params->height : null;

        return new GraphImage($params->imageFormat, 'Error', self::error($text, $short_text, $width, $height, format: $params->imageFormat));
    }

    public static function getTypes(): array
    {
        return ['device', 'port', 'application', 'munin', 'service'];
    }

    /**
     * Get an array of all graph subtypes for the given type
     *
     * @param  string  $type
     * @param  ?Device  $device
     * @return array
     */
    public static function getSubtypes(string $type, ?Device $device = null): array
    {
        $types = app(GraphFactory::class)->subtypes($type);

        if ($device?->graphs) {
            $graphs = $device->graphs->pluck('graph');

            foreach (LibrenmsConfig::get('graph_types') as $type_data) {
                foreach (array_keys($type_data) as $subtype) {
                    if ($graphs->contains($subtype)) {
                        $types[] = $subtype;
                    }
                }
            }
        }

        $types = array_unique($types);
        sort($types);

        return $types;
    }

    public static function getOverviewGraphsForDevice(Device $device): array
    {
        if (! $device->polling()->isEnabled(PollingMethodType::Snmp)) {
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
     * @param  ImageFormat|null  $format  defaults to the configured graph type
     * @return string the generated image
     */
    public static function error(string $text, ?string $short_text, int $width = 300, ?int $height = null, array $color = [128, 0, 0], ?ImageFormat $format = null): string
    {
        $format ??= ImageFormat::forGraph();
        $height ??= $width / 3;

        if ($short_text !== null && $width < 200) {
            $text = $short_text;
        }

        if ($format === ImageFormat::Svg) {
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
        $output = (string) ob_get_clean();
        imagedestroy($img);

        return $output;
    }
}
