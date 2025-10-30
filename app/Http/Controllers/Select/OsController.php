<?php

namespace App\Http\Controllers\Select;

use App\Facades\LibrenmsConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OsController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'int',
            'page' => 'int',
            'term' => 'nullable|string',
        ]);

        $results = array_map(
            fn ($os) => ['id' => $os['os'], 'text' => $os['text']],
            LibrenmsConfig::get('os', [])
        );

        if ($term = $request->string('term')->trim()->value()) {
            $results = $this->sortAndFilterBySimilarity($term, $results);
        }

        return response()->json([
            'results' => array_values($results),
            'pagination' => ['more' => false],
        ]);
    }

    private function sortAndFilterBySimilarity(string $term, array $items): array
    {
        $term = strtolower($term);

        $scored = array_map(function ($item) use ($term) {
            $id = strtolower($item['id']);
            $text = strtolower($item['text']);

            return [
                'item' => $item,
                'hasPrefix' => str_starts_with($id, $term) || str_starts_with($text, $term),
                'distance' => min(
                    levenshtein($term, $id, 1, 10, 10),
                    levenshtein($term, $text, 1, 10, 10)
                ),
            ];
        }, $items);

        usort($scored, fn($a, $b) => ($b['hasPrefix'] <=> $a['hasPrefix'])
            ?: ($a['distance'] <=> $b['distance']));

        return array_column($scored, 'item');
    }
}
