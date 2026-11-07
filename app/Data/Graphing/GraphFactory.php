<?php

/**
 * GraphFactory.php
 *
 * Resolves graph types to graph classes, falling back to the legacy graph include files
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Data\Graphing;

use App\Data\Graphing\Legacy\LegacyGraph;
use App\Data\Graphing\Legacy\LegacyGraphScript;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\InvalidGraph;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;
use ReflectionClass;

class GraphFactory
{
    /**
     * @throws InvalidGraph
     */
    public function graphFor(GraphParameters $params): GraphInterface
    {
        if (empty($params->type) || empty($params->subtype)) {
            throw new InvalidGraph;
        }

        $className = $this->graphClass($params->type, $params->subtype);
        if ($className !== null) {
            return app($className, ['params' => $params]);
        }

        return new LegacyGraph($params);
    }

    /**
     * All subtypes of the given graph type
     *
     * @return string[]
     */
    public function subtypes(string $type): array
    {
        $type = basename($type);
        $subtypes = LegacyGraphScript::subtypes($type);

        foreach (glob(app_path('Graphs/' . ucfirst($type) . '/*Graph.php')) ?: [] as $file) {
            $subtype = Str::snake(basename($file, 'Graph.php'));
            if ($this->graphClass($type, $subtype) !== null) {
                $subtypes[] = $subtype;
            }
        }

        return $subtypes;
    }

    /**
     * Graph classes are named App\Graphs\{Type}\{Subtype}Graph, for example App\Graphs\Device\ProcessorGraph
     *
     * @return class-string<GraphInterface>|null
     */
    private function graphClass(string $type, string $subtype): ?string
    {
        $className = 'App\\Graphs\\' . ucfirst($type) . '\\' . Str::studly($subtype) . 'Graph';

        if (is_subclass_of($className, GraphInterface::class) && new ReflectionClass($className)->isInstantiable()) {
            return $className;
        }

        return null;
    }
}
