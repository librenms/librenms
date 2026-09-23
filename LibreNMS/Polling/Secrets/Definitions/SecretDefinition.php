<?php

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use Illuminate\Support\Str;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Data\SecretData;

abstract class SecretDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    /**
     * @param  array<string, mixed>  $data
     */
    abstract public function createData(array $data): SecretData;

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
