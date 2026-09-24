<?php

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use Illuminate\Support\Str;
use LibreNMS\Enum\SecretType;

abstract class SecretDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    /**
     * This resolution is intentionally kept simple to avoid enumerating all secrets here.
     * More complex systems can be implemented later.
     */
    public static function for(SecretType|string|null $type): ?static
    {
        if ($type === null) {
            return null;
        }

        $name = $type instanceof SecretType ? $type->value : $type;
        $class = __NAMESPACE__ . '\\' . Str::studly($name) . 'SecretDefinition';

        if (! class_exists($class) || ! is_subclass_of($class, self::class)) {
            return null;
        }

        return resolve($class);
    }
}
