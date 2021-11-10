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

    protected function rules(): array
    {
        return [
            'type' => 'nullable|string',
            'secret_type' => 'nullable|string',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return ['description'];
    }

    protected function filterFields(Request $request): array
    {
        return [
            'secret_type' => 'secret_type',
            'type' => fn (Builder $query, $value) => $query->where('secret_type', $value),
        ];
    }

    protected function baseQuery(Request $request): Builder|\Illuminate\Database\Query\Builder
    {
        $this->authorize('viewAny', Secret::class);

        return Secret::hasAccess($request->user());
    }
}
