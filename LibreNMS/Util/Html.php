<?php
/**
 * Html.php
 *
 * Helper functions to generate html snippets
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

use HTMLPurifier;
use HTMLPurifier_Config;
use LibreNMS\Config;

class Html
{
    /**
     * Print or return a row of graphs
     *
     * @param array $graph_array
     * @param bool $print
     * @return array
     */
    public static function graphRow($graph_array, $print = false)
    {
        if (session('widescreen')) {
            if (!$graph_array['height']) {
                $graph_array['height'] = '110';
            }

            if (!$graph_array['width']) {
                $graph_array['width'] = '215';
            }

            $periods = Config::get('graphs.mini.widescreen');
        } else {
            if (!$graph_array['height']) {
                $graph_array['height'] = '100';
            }

            if (!$graph_array['width']) {
                $graph_array['width'] = '215';
            }

            $periods = Config::get('graphs.mini.normal');
        }

        $screen_width = session('screen_width');
        if ($screen_width) {
            if ($screen_width < 1024 && $screen_width > 700) {
                $graph_array['width'] = round(($screen_width - 90) / 2, 0);
            } elseif ($screen_width > 1024) {
                $graph_array['width'] = round(($screen_width - 90) / count($periods) + 1, 0);
            } else {
                $graph_array['width'] = $screen_width - 70;
            }
        }

        $graph_array['height'] = round($graph_array['width'] / 2.15);

        $graph_data = [];
        foreach ($periods as $period => $period_text) {
            $graph_array['from'] = Config::get("time.$period");
            $graph_array_zoom = $graph_array;
            $graph_array_zoom['height'] = '150';
            $graph_array_zoom['width'] = '400';

            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width']);
            $link = Url::generate($link_array);

            $full_link = Url::overlibLink($link, Url::lazyGraphTag($graph_array), Url::graphTag($graph_array_zoom), null);
            $graph_data[] = $full_link;

            if ($print) {
                echo "<div class='col-md-3'>$full_link</div>";
            }
        }

        return $graph_data;
    }

    public static function percentageBar($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background)
    {
        if ($percent > '100') {
            $size_percent = '100';
        } else {
            $size_percent = $percent;
        }

        $output = '
        <div style="width:'.$width.'px; height:'.$height.'px; position: relative;">
        <div class="progress" style="min-width: 2em; background-color:#'.$right_background.'; height:'.$height.'px;margin-bottom:-'.$height.'px;">
        <div class="progress-bar" role="progressbar" aria-valuenow="'.$size_percent.'" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width:'.$size_percent.'%; background-color: #'.$left_background.';">
        </div>
        </div>
        <b style="padding-left: 2%; position: absolute; top: 0; left: 0;color:#'.$left_colour.';">'.$left_text.'</b>
        <b style="padding-right: 2%; position: absolute; top: 0; right: 0;color:#'.$right_colour.';">'.$right_text.'</b>
        </div>
        ';

        return $output;
    }
}
