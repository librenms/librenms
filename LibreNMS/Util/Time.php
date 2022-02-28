<?php
/**
 * Time.php
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

class Time
{
    public static function legacyTimeSpecToSecs($description)
    {
        $conversion = [
            'now' => 0,
            'onehour' => 3600,
            'fourhour' => 14400,
            'sixhour' => 21600,
            'twelvehour' => 43200,
            'day' => 86400,
            'twoday' => 172800,
            'week' => 604800,
            'twoweek' => 1209600,
            'month' => 2678400,
            'twomonth' => 5356800,
            'threemonth' => 8035200,
            'year' => 31536000,
            'twoyear' => 63072000,
        ];

        return isset($conversion[$description]) ? $conversion[$description] : 0;
    }

    public static function formatInterval($seconds, $format = 'long') {
        $outfmt = '';
        $year = '';
        if ($seconds <> 0 ){
            $interval = (new \DateTime('@0'))->diff(new \DateTime("@$seconds"));
            if ($interval->y >= 1) {
                $year .= $interval->y; 
                if ($format == 'short' ) {
                    $year .= 'y ';
                } elseif ( $interval->y > 1 ) {
                    $year .= ' years ';
                } else {
                    $year .= ' year ';
                }
                $interval = (new \DateTime('@0'))->diff( (new \DateTime("@$seconds"))->sub(new \DateInterval('P'.$interval->y.'Y')) );
            }
            if ($format == 'short' && $interval->d >= 1) {
                $outfmt .= '%ad ';
            } elseif ( $interval->d > 1 ) {
                 $outfmt .= '%a days ';
            } elseif ( $interval->d == 1 ) {
                 $outfmt .= '%a day ';
            }
            if ( $interval->h > 0 || $interval->i > 0 || $interval->s > 0 ) {
                 $outfmt .= '%H:%I:%S';
            }
            return $year.$interval->format($outfmt);
        } else {
            return '';
        }
    }


    /*
     * @param integer seconds of a time period
     * @return string human readably time period
     */
    public static function humanTime($s)
    {
        $ret = [];

        if ($s >= 86400) {
            $d = floor($s / 86400);
            $s -= $d * 86400;
            if ($d == 1) {
                $ret[] = $d . ' day';
            } else {
                $ret[] = $d . ' days';
            }
        }

        if ($s >= 3600) {
            $h = floor($s / 3600);
            $s -= $h * 3600;
            if ($h == 1) {
                $ret[] = $h . ' hour';
            } else {
                $ret[] = $h . ' hours';
            }
        }

        if ($s >= 60) {
            $m = floor($s / 60);
            $s -= $m * 60;
            if ($m == 1) {
                $ret[] = $m . ' minute';
            } else {
                $ret[] = $m . ' minutes';
            }
        }

        if ($s > 0) {
            if ($s == 1) {
                $ret[] = $s . ' second';
            } else {
                $ret[] = $s . ' seconds';
            }
        }

        return implode(' ,', $ret);
    }
}
