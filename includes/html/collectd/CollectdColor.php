<?php
/**
 * CollectdColor.php
 *
 * Color functions class for Collectd
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
 */

namespace LibreNMS;

class CollectdColor
{
    private $r = 0;
    private $g = 0;
    private $b = 0;

    public function __construct($value = null)
    {
        if (is_null($value)) {
        } else {
            if (is_array($value)) {
                if (isset($value['r'])) {
                    $this->r = $value['r'] > 0 ? ($value['r'] > 1 ? 1 : $value['r']) : 0;
                }

                if (isset($value['g'])) {
                    $this->g = $value['g'] > 0 ? ($value['g'] > 1 ? 1 : $value['g']) : 0;
                }

                if (isset($value['b'])) {
                    $this->b = $value['b'] > 0 ? ($value['b'] > 1 ? 1 : $value['b']) : 0;
                }
            } else {
                if (is_string($value)) {
                    $matches = [];
                    if ($value == 'random') {
                        $this->randomize();
                    } else {
                        if (preg_match(
                            '/([0-9A-Fa-f][0-9A-Fa-f])([0-9A-Fa-f][0-9A-Fa-f])([0-9A-Fa-f][0-9A-Fa-f])/',
                            $value,
                            $matches
                        )) {
                            $this->r = (hexdec('0x' . $matches[1]) / 255.0);
                            $this->g = (hexdec('0x' . $matches[2]) / 255.0);
                            $this->b = (hexdec('0x' . $matches[3]) / 255.0);
                        }
                    }
                } else {
                    if (is_a($value, 'CollectdColor') ||
                        is_a($value, 'LibreNMS\CollectdColor')) {
                        $this->r = $value->r;
                        $this->g = $value->g;
                        $this->b = $value->b;
                    }
                }
            }
        }//end if
    }

    //end __construct()

    public function randomize()
    {
        $this->r = (rand(0, 255) / 255.0);
        $this->g = (rand(0, 255) / 255.0);
        $this->b = 0.0;
        $min = 0.0;
        $max = 1.0;

        if (($this->r + $this->g) < 1.0) {
            $min = (1.0 - ($this->r + $this->g));
        } else {
            $max = (2.0 - ($this->r + $this->g));
        }

        $this->b = ($min + ((rand(0, 255) / 255.0) * ($max - $min)));
    }

    //end randomize()

    public function fade($bkgnd = null, $alpha = 0.25)
    {
        if (is_null($bkgnd) || ! is_a($bkgnd, 'CollectdColor')) {
            $bg_r = 1.0;
            $bg_g = 1.0;
            $bg_b = 1.0;
        } else {
            $bg_r = $bkgnd->r;
            $bg_g = $bkgnd->g;
            $bg_b = $bkgnd->b;
        }

        $this->r = ($alpha * $this->r + ((1.0 - $alpha) * $bg_r));
        $this->g = ($alpha * $this->g + ((1.0 - $alpha) * $bg_g));
        $this->b = ($alpha * $this->b + ((1.0 - $alpha) * $bg_b));
    }

    //end fade()

    public function toArray()
    {
        return [
            'r' => $this->r,
            'g' => $this->g,
            'b' => $this->b,
        ];
    }

    //end as_array()

    public function toString()
    {
        $r = (int) ($this->r * 255);
        $g = (int) ($this->g * 255);
        $b = (int) ($this->b * 255);

        return sprintf('%02x%02x%02x', $r > 255 ? 255 : $r, $g > 255 ? 255 : $g, $b > 255 ? 255 : $b);
    }
}
