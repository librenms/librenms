<?php

/**
 * NetworkMapOptions.php
 *
 * Sane default configurations for Vis.js Network and Dependency maps.
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
 * @copyright  2026 LibreNMS
 * @author     LibreNMS Developers
 */

namespace LibreNMS\Util;

class NetworkMapOptions
{
    /**
     * Sane default options for Network Map.
     *
     * @return array<string, mixed>
     */
    public static function networkMap(): array
    {
        return [
            'layout' => [
                'randomSeed' => 2,
                'improvedLayout' => true,
            ],
            'nodes' => [
                'shape' => 'box',
                'margin' => 8,
                'font' => [
                    'size' => 20,
                    'face' => 'Segoe UI',
                    'color' => '#000000',
                    'strokeWidth' => 1,
                    'strokeColor' => '#ffffff',
                ],
                'shadow' => true,
            ],
            'edges' => [
                'width' => 2,
                'arrows' => [
                    'to' => [
                        'enabled' => true,
                        'scaleFactor' => 0.5,
                    ],
                ],
                'smooth' => [
                    'enabled' => false,
                ],
                'font' => [
                    'size' => 14,
                    'color' => '#ffffff',
                    'face' => 'sans',
                    'align' => 'middle',
                    'strokeWidth' => 2,
                    'strokeColor' => '#1f2937',
                    'background' => '#1f2937',
                ],
            ],
            'physics' => [
                'enabled' => true,
                'solver' => 'forceAtlas2Based',
                'forceAtlas2Based' => [
                    'gravitationalConstant' => -200,
                    'centralGravity' => 0.01,
                    'springLength' => 250,
                    'springConstant' => 0.04,
                    'damping' => 0.90,
                    'avoidOverlap' => 1,
                ],
                'maxVelocity' => 50,
                'minVelocity' => 0.4,
                'timestep' => 0.4,
                'stabilization' => [
                    'enabled' => true,
                    'iterations' => 1000,
                    'updateInterval' => 100,
                    'onlyDynamicEdges' => false,
                    'fit' => true,
                ],
            ],
        ];
    }

    /**
     * Sane default options for Device Dependency Map (hierarchical tree).
     *
     * @return array<string, mixed>
     */
    public static function dependencyMap(): array
    {
        return [
            'layout' => [
                'hierarchical' => [
                    'enabled' => true,
                    'direction' => 'UD',
                    'sortMethod' => 'directed',
                    'nodeSpacing' => 150,
                    'treeSpacing' => 200,
                    'levelSeparation' => 200,
                ],
            ],
            'nodes' => [
                'shape' => 'box',
                'margin' => 8,
                'font' => [
                    'size' => 20,
                    'face' => 'Segoe UI',
                    'color' => '#000000',
                    'strokeWidth' => 1,
                    'strokeColor' => '#ffffff',
                ],
                'shadow' => true,
            ],
            'edges' => [
                'width' => 2,
                'arrows' => [
                    'to' => [
                        'enabled' => true,
                        'scaleFactor' => 0.5,
                    ],
                ],
                'smooth' => [
                    'enabled' => false,
                ],
                'font' => [
                    'size' => 14,
                    'color' => '#ffffff',
                    'face' => 'sans',
                    'align' => 'middle',
                    'strokeWidth' => 2,
                    'strokeColor' => '#1f2937',
                    'background' => '#1f2937',
                ],
            ],
            'physics' => [
                'enabled' => false,
            ],
        ];
    }
}
