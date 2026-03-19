<?php

namespace App\Policies;

use Illuminate\Database\Eloquent\Model;

trait ResolvesPolicyTargets
{
    /**
     * @template TModel of Model
     *
     * @param  TModel|array  $target
     * @param  class-string<TModel>  $modelClass
     * @return TModel
     */
    protected function castToModel(Model|array $target, string $modelClass): Model
    {
        return $target instanceof $modelClass ? $target : new $modelClass($target);
    }

    /**
     * @param  array<string, mixed>|Model  $target
     * @param  string[]  $keys
     */
    protected function getNumericId(array|Model $target, array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = $target instanceof Model ? $target->getAttribute($key) : ($target[$key] ?? null);
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }
}
