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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;
use LibreNMS\Enum\PowerState;

class Html
{
    /**
     * return icon and color for application state
     * @param string $app_state
     * @return array
     */
    public static function appStateIcon($app_state)
    {
        switch ($app_state) {
            case 'OK':
                $icon = '';
                $color = '';
                $hover_text = 'OK';
                break;
            case 'ERROR':
                $icon = 'fa-close';
                $color = '#cc1122';
                $hover_text = 'Error';
                break;
            case 'LEGACY':
                $icon = 'fa-warning';
                $color = '#eebb00';
                $hover_text = 'legacy Agent Script';
                break;
            case 'UNSUPPORTED':
                $icon = 'fa-flash';
                $color = '#ff9900';
                $hover_text = 'Unsupported Agent Script Version';
                break;
            default:
                $icon = 'fa-question';
                $color = '#777777';
                $hover_text = 'Unknown State';
                break;
        }

        return ['icon' => $icon, 'color' => $color, 'hover_text' => $hover_text];
    }

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
            if (! array_key_exists('height', $graph_array)) {
                $graph_array['height'] = '110';
            }

            if (! array_key_exists('width', $graph_array)) {
                $graph_array['width'] = '215';
            }

            $periods = Config::get('graphs.mini.widescreen');
        } else {
            if (! array_key_exists('height', $graph_array)) {
                $graph_array['height'] = '100';
            }

            if (! array_key_exists('width', $graph_array)) {
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

            $full_link = Url::overlibLink($link, Url::lazyGraphTag($graph_array), Url::graphTag($graph_array_zoom));
            $graph_data[] = $full_link;

            if ($print) {
                echo "<div class='col-md-3'>$full_link</div>";
            }
        }

        return $graph_data;
    }

    public static function percentageBar($width, $height, $percent, $left_text = '', $right_text = '', $warn = null, $shadow = null, $colors = null)
    {
        $percent = min($percent, 100);
        if ($colors === null) {
            $colors = Colors::percentage($percent, $warn ?: null);
        }
        $default = Colors::percentage(0);
        $left_text_color = $colors['left_text'] ?? 'ffffff';
        $right_text_color = $colors['right_text'] ?? 'ffffff';
        $left_color = $colors['left'] ?? $default['left'];
        $right_color = $colors['right'] ?? $default['right'];

        $output = '<div style="width:' . $width . 'px; height:' . $height . 'px; position: relative;">
        <div class="progress" style="background-color:#' . $right_color . '; height:' . $height . 'px;margin-bottom:-' . $height . 'px;">';

        if ($shadow !== null) {
            $shadow = min($shadow, 100);
            $middle_color = $colors['middle'] ?? $default['middle'];
            $output .= '<div class="progress-bar" role="progressbar" aria-valuenow="' . $shadow . '" aria-valuemin="0" aria-valuemax="100" style="width:' . $shadow . '%; background-color: #' . $middle_color . ';">';
        }

        $output .= '<div class="progress-bar" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percent . '%; background-color: #' . $left_color . ';">
        </div></div>
        <b style="padding-left: 2%; position: absolute; top: 0; left: 0;color:#' . $left_text_color . ';">' . $left_text . '</b>
        <b style="padding-right: 2%; position: absolute; top: 0; right: 0;color:#' . $right_text_color . ';">' . $right_text . '</b>
        </div>';

        return $output;
    }

    /**
     * @param int|string $state
     */
    public static function powerStateLabel($state): array
    {
        $state = is_string($state) ? PowerState::STATES[$state] : $state;

        switch ($state) {
            case PowerState::OFF:
                return ['OFF', 'label-default'];
            case PowerState::ON:
                return ['ON', 'label-success'];
            case PowerState::SUSPENDED:
                return ['SUSPENDED', 'label-warning'];
            default:
                return ['UNKNOWN', 'label-default'];
        }
    }
}
