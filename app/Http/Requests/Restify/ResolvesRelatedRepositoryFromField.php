<?php

namespace App\Http\Requests\Restify;

use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Fields\EagerField;
use Binaryk\LaravelRestify\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Replaces Restify's default `Restify::repositoryForTable($urlSegment)` lookup
 * (which fails whenever the URL segment doesn't equal the related model's
 * actual database table name  e.g. `attach/device-groups` against the
 * `device_groups` table) with a field-driven lookup that uses the parent
 * repository's `related()` declaration.
 *
 * The URL segment is matched to the field's `attribute`. The target repository
 * is taken from the field's `repositoryClass`. This works for every attach/
 * sync/detach URL regardless of how it differs from the underlying table.
 */
trait ResolvesRelatedRepositoryFromField
{
    protected function resolveRelatedModels(): Collection
    {
        $segment = $this->relatedRepository;

        $field = collect($this->repository()::related())
            ->first(fn ($candidate) => $candidate instanceof EagerField && $candidate->attribute === $segment);

        if (! $field instanceof BelongsToMany) {
            abort(400, "Missing BelongsToMany relation for [{$segment}] on the parent repository.");
        }

        /** @var class-string<Repository> $relatedClass */
        $relatedClass = $field->repositoryClass;
        /** @var Model $model */
        $model = app($relatedClass::guessModelClassName());

        return collect(Arr::wrap($this->input($segment)))
            ->map(fn ($id) => $model->newModelQuery()->whereKey($id)->first());
    }
}
