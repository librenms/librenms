<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class GroupedSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search'));
        if ($search === '') {
            return response()->json(['groups' => []]);
        }

        $groups = $this->groups(
            $search,
            '%' . $search . '%',
            (int) LibrenmsConfig::get('webui.global_search_result_limit') ?: 8,
            $request->user(),
        );

        return response()->json(['groups' => array_values(array_filter($groups))]);
    }

    /**
     * @return array<array{type: string, label: string, results: Collection}|null>
     */
    abstract protected function groups(string $search, string $like, int $limit, ?User $user): array;

    /**
     * @param  Collection<int, array<string, mixed>>  $results
     * @return array{type: string, label: string, results: Collection}|null
     */
    protected function group(string $type, string $label, Collection $results): ?array
    {
        return $results->isEmpty() ? null : ['type' => $type, 'label' => $label, 'results' => $results];
    }
}
