<?php

declare(strict_types=1);

namespace App\Services\Api\OpenApi;

use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Fields\EagerField;
use Binaryk\LaravelRestify\Fields\Field;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Filters\Filter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Pulls metadata off a Restify repository class without sending an HTTP request.
 */
class RepositoryIntrospector
{
    /**
     * @param  class-string<RestifyRepository>  $repositoryClass
     * @return Field[]
     */
    public function fields(string $repositoryClass): array
    {
        $repository = new $repositoryClass();
        $fields = $repository->fields(new RestifyRequest());

        return array_values(array_filter($fields, static fn ($field) => $field instanceof Field));
    }

    /**
     * @param  class-string<RestifyRepository>  $repositoryClass
     * @return array<string, Filter>
     */
    public function matches(string $repositoryClass): array
    {
        $matches = $repositoryClass::matches();

        return array_filter($matches, static fn ($match) => $match instanceof Filter);
    }

    /**
     * @param  class-string<RestifyRepository>  $repositoryClass
     * @return array<string, Filter>
     */
    public function searchables(string $repositoryClass): array
    {
        $searchables = $repositoryClass::searchables();

        return array_filter($searchables, static fn ($s) => $s instanceof Filter);
    }

    /**
     * @param  class-string<RestifyRepository>  $repositoryClass
     * @return array<string, Filter>
     */
    public function sorts(string $repositoryClass): array
    {
        $sorts = $repositoryClass::sorts();

        return array_filter($sorts, static fn ($s) => $s instanceof Filter);
    }

    /**
     * @param  class-string<RestifyRepository>  $repositoryClass
     * @return array<string, string>
     */
    public function modelCasts(string $repositoryClass): array
    {
        $modelClass = $repositoryClass::$model ?? null;
        if (! is_string($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return [];
        }

        return (new $modelClass())->getCasts();
    }

    public function isReadonly(Field $field): bool
    {
        return $field->readonlyCallback === true;
    }

    /**
     * @param  class-string<RestifyRepository>  $repositoryClass
     * @return array<string, array{cardinality: 'one'|'many', repository: class-string<RestifyRepository>}>
     */
    public function related(string $repositoryClass): array
    {
        $out = [];
        foreach ($repositoryClass::related() as $name => $field) {
            if (! $field instanceof EagerField) {
                continue;
            }
            $cardinality = match (true) {
                $field instanceof HasMany,
                $field instanceof BelongsToMany => 'many',
                $field instanceof BelongsTo => 'one',
                default => null,
            };
            if ($cardinality === null) {
                continue;
            }

            $out[(string) $name] = [
                'cardinality' => $cardinality,
                'repository' => $field->repositoryClass,
            ];
        }

        return $out;
    }
}
