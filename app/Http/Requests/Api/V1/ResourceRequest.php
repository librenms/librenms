<?php

namespace App\Http\Requests\Api\V1;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\ValidationException;

/**
 * JSON:API query parameters for a v1 resource: include, fields, sort, filter and page.
 * Invalid or unsupported parameters are a 400 Bad Request, as JSON:API requires.
 *
 * Filters use the Filterable operators: filter[name][operator]=value,
 * filter[name]=value is short for the eq operator.
 */
class ResourceRequest extends FormRequest
{
    public const int DEFAULT_PAGE_SIZE = 50;
    public const int MAX_PAGE_SIZE = 500;

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filter');

        if (is_array($filters)) {
            $this->merge(['filter' => array_map(fn ($value) => is_array($value) ? $value : ['eq' => $value], $filters)]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'include' => ['sometimes', 'string'],
            'fields' => ['sometimes', 'array'],
            'fields.*' => ['string'],
            'sort' => ['sometimes', 'string'],
            'filter' => ['sometimes', 'array'],
            'page' => ['sometimes', 'array:number,size'],
            'page.number' => ['integer', 'min:1'],
            'page.size' => ['integer', 'min:1', 'max:' . self::MAX_PAGE_SIZE],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new ValidationException($validator)->status(400);
    }

    /**
     * Reject any include that is not listed.
     *
     * @param  list<string>  $includes  relationship names that may be included
     */
    public function allowIncludes(array $includes): static
    {
        $unsupported = array_diff($this->listParameter('include'), $includes);

        if ($unsupported) {
            $this->badParameter('include', 'Unsupported include: ' . implode(', ', $unsupported));
        }

        return $this;
    }

    /**
     * Filter, sort and paginate the query. The primary key always breaks ties, so pages are stable.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query  a query for a model using Filterable
     * @param  array<string, string>  $sorts  sort name => column
     * @param  array<string, string|array{0: string, 1: Closure(mixed): mixed}>  $filters  filter name => Filterable field, optionally with a value converter
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(Builder $query, array $sorts = [], array $filters = []): LengthAwarePaginator
    {
        $validator = ValidatorFacade::make($this->only('filter'), $query->getModel()::filterValidationRules()); // @phpstan-ignore staticMethod.notFound (Filterable)
        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $modelFilters = [];
        foreach ((array) $this->input('filter', []) as $name => $operators) {
            $filter = $filters[$name] ?? $this->badParameter("filter.$name", "Unsupported filter: $name");
            $field = is_array($filter) ? $filter[0] : $filter;
            $convert = is_array($filter) ? $filter[1] : null;

            foreach ($operators as $operator => $value) {
                $modelFilters[$field][$operator] = $convert === null ? $value : $this->convertFilterValue($name, $operator, $value, $convert);
            }
        }
        $query->applyFilters($modelFilters); // @phpstan-ignore method.notFound (Filterable)

        foreach ($this->listParameter('sort') as $sort) {
            $descending = str_starts_with($sort, '-');
            $name = $descending ? substr($sort, 1) : $sort;
            $column = $sorts[$name] ?? $this->badParameter('sort', "Unsupported sort: $name");
            $query->orderBy($query->qualifyColumn($column), $descending ? 'desc' : 'asc');
        }
        $query->orderBy($query->getModel()->getQualifiedKeyName());

        return $query->paginate(
            perPage: $this->integer('page.size', self::DEFAULT_PAGE_SIZE),
            page: $this->integer('page.number', 1),
        );
    }

    /**
     * Converts true or false filter values to the stored 1 or 0.
     *
     * @param  bool  $inverted  the column stores the opposite, such as disabled for isEnabled
     * @return Closure(mixed): (int|list<int>|null)
     */
    public static function booleanFilter(bool $inverted = false): Closure
    {
        $convert = function (mixed $value) use ($inverted): ?int {
            $bool = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            return $bool === null ? null : (int) ($bool !== $inverted);
        };

        return function (mixed $value) use ($convert): int|array|null {
            if (! is_array($value)) {
                return $convert($value);
            }

            $values = array_map($convert, array_values($value));

            return in_array(null, $values, true) ? null : $values;
        };
    }

    /**
     * Converted values are no longer strings, so split set operator lists first.
     *
     * @param  Closure(mixed): mixed  $convert
     */
    private function convertFilterValue(string $name, string $operator, mixed $value, Closure $convert): mixed
    {
        if (in_array($operator, ['in', 'not_in'], true) && is_string($value)) {
            $value = explode(',', $value);
        }

        return $convert($value) ?? $this->badParameter("filter.$name", "Invalid value for filter: $name");
    }

    /**
     * @return list<string>
     */
    private function listParameter(string $key): array
    {
        return array_values(array_filter(explode(',', $this->string($key)->toString())));
    }

    private function badParameter(string $parameter, string $message): never
    {
        throw ValidationException::withMessages([$parameter => $message])->status(400);
    }
}
