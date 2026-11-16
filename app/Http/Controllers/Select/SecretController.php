<?php

namespace App\Http\Controllers\Select;

use App\Models\Secret;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Secret>
 */
class SecretController extends SelectController
{
    protected ?string $idField = 'id';
    protected ?string $textField = 'description';

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'type' => 'nullable|string',
            'secret_type' => 'nullable|string', // comma separated for several types
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function searchFields(Request $request): array
    {
        return ['description'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterFields(Request $request): array
    {
        $byTypes = fn (Builder $query, $value) => $query->whereIn('secret_type', explode(',', (string) $value));

        return [
            'secret_type' => $byTypes,
            'type' => $byTypes,
        ];
    }

    protected function baseQuery(Request $request): Builder|\Illuminate\Database\Query\Builder
    {
        $this->authorize('viewAny', Secret::class);

        return Secret::hasAccess($request->user());
    }
}
