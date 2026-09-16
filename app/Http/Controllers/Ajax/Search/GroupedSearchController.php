<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Return a list of groups, each ['type' => ..., 'label' => ..., 'results' => Collection], or null for empty groups.
     *
     * @return list<mixed>
     */
    abstract protected function groups(string $search, string $like, int $limit, ?User $user): array;
}
