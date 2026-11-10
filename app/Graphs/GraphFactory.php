<?php

namespace App\Graphs;

use App\Data\Graphing\GraphParameters;
use App\Graphs\Legacy\LegacyGraph;
use App\Graphs\Legacy\LegacyGraphScript;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\InvalidGraph;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;
use ReflectionClass;

/**
 * Resolves graph types to graph classes, falling back to the legacy graph include files
 */
class GraphFactory
{
    /** Directories of App\Graphs that hold graph machinery instead of graph types */
    private const FRAMEWORK_DIRECTORIES = ['legacy', 'rrd'];

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
        // only plain names map to classes, so other spellings of a subtype do not resolve to the same class
        if (! preg_match('/^[a-zA-Z0-9]+$/', $type) || ! preg_match('/^[a-z0-9_]+$/', $subtype)
            || in_array(strtolower($type), self::FRAMEWORK_DIRECTORIES, true)) {
            return null;
        }

        $className = 'App\\Graphs\\' . ucfirst($type) . '\\' . Str::studly($subtype) . 'Graph';

        if (is_subclass_of($className, GraphInterface::class) && new ReflectionClass($className)->isInstantiable()) {
            return $className;
        }

        return null;
    }
}
