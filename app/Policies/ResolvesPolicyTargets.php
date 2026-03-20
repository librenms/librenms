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
}
