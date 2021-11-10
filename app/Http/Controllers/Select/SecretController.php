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

    protected function baseQuery(Request $request): Builder|\Illuminate\Database\Query\Builder
    {
        $this->authorize('viewAny', Secret::class);

        return Secret::hasAccess($request->user());
    }
}
