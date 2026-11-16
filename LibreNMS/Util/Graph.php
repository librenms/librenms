<?php

namespace LibreNMS\Util;

use App\Data\Graphing\GraphImage;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphAccess;
use App\Graphs\GraphFactory;
use App\Models\Device;
use Illuminate\Support\Arr;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;

class Graph
{
    // output flags for getImageData(), used by the @graphImage directive in alert templates
    const BASE64_OUTPUT = 1; // base64 encoded image data
    const INLINE_BASE64 = 2; // inline base64 data uri for img src
    const IMAGE_PNG = 4;
    const IMAGE_SVG = 8;

    /**
     * Image data in the format selected by the output flags
     *
     * @param  array<string, mixed>|string  $vars  graph vars or a graph url
     * @param  int  $flags  bitmask of the output flag constants
     */
    public static function getImageData(array|string $vars, int $flags = 0): string
    {
        $vars = is_string($vars) ? Url::parseLegacyPathVars($vars) : $vars;

        if ($flags & self::IMAGE_PNG) {
            $vars['graph_type'] = 'png';
        }

        if ($flags & self::IMAGE_SVG) {
            $vars['graph_type'] = 'svg';
        }

        $image = self::getImage($vars);

        if ($flags & self::INLINE_BASE64) {
            return $image->inline();
        }

        if ($flags & self::BASE64_OUTPUT) {
            return $image->base64();
        }

        return $image->data;
    }

    /**
     * Fetch a GraphImage based on the given $vars, for server side rendering of alerts:
     * the graphs in alert templates and transports are chosen by whoever configured the alert,
     * so a guest (the alert runner) is trusted, a logged-in user still needs permission.
     * Catches errors generated and always returns GraphImage
     *
     * @param  array<string, mixed>|string  $vars  graph vars or a graph url
     */
    public static function getImage(array|string $vars): GraphImage
    {
        $params = new GraphParameters(is_string($vars) ? Url::parseLegacyPathVars($vars) : $vars);

        try {
            return app(GraphAccess::class)->trusted(fn () => app(GraphFactory::class)->graphFor($params)->render());
        } catch (GraphException $e) {
            if (Debug::isEnabled()) {
                throw $e;
            }

            return self::errorImage($params, $e);
        }
    }

    /**
     * An image with the error text, sized and formatted as the requested graph
     */
    public static function errorImage(GraphParameters $params, GraphException $e): GraphImage
    {
        $style = $params->style ?: session('applied_site_style');
        $foreground = $style === 'dark' ? [255, 255, 255] : [0, 0, 0];
        $color = $e instanceof NoGraphData ? $foreground : [128, 0, 0];
        $title = $params->visible('title') ? ($params->user_title ?? $e->graphTitle) : null;

        return new GraphImage($params->imageFormat, $title ?? 'Error', self::error($e->getMessage(), $e->shortText, $params->width, $params->height, $color, $params->imageFormat, $title, $foreground));
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

            foreach (array_keys(LibrenmsConfig::get("graph_types.$type", [])) as $subtype) {
                if ($graphs->contains($subtype)) {
                    $types[] = $subtype;
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
     * @param  string|null  $title  Original graph title, when enabled
     * @param  int[]  $titleColor  Color of the graph title
     * @return string the generated image
     */
    public static function error(string $text, ?string $short_text, int $width = 300, ?int $height = null, array $color = [128, 0, 0], ?ImageFormat $format = null, ?string $title = null, array $titleColor = [0, 0, 0]): string
    {
        $format ??= ImageFormat::forGraph();
        $height ??= $width / 3;

        if ($short_text !== null && $width < 200) {
            $text = $short_text;
        }

        if ($format === ImageFormat::Svg) {
            $rgb = implode(', ', $color);
            $titleRgb = implode(', ', $titleColor);
            $text = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $titleHtml = $title === null ? '' : '<xhtml:div style="position:absolute; top:5px; width:100%; color:rgb(' . $titleRgb . '); text-align:center; font-family:sans-serif; font-size:0.7em; font-weight:bold;">' . htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8') . '</xhtml:div>';

            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg"
xmlns:xhtml="http://www.w3.org/1999/xhtml"
viewBox="0 0 $width $height"
preserveAspectRatio="xMinYMin">
<foreignObject x="0" y="0" width="$width" height="$height" transform="translate(0,0)">
      <xhtml:div style="position:relative; display:table; width:{$width}px; height:{$height}px; overflow:hidden;">
         $titleHtml
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

        if ($title !== null) {
            $titleX = max(0, (int) (($width - imagefontwidth(3) * strlen($title)) / 2));
            imagestring($img, 3, $titleX, 5, $title, imagecolorallocate($img, ...$titleColor));
        }

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
