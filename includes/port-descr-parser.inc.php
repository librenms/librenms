<?php

// Parser should return an array with type, descr, circuit, speed and notes

return function (string $ifAlias): array {
    if (! str_contains($ifAlias, ':')) {
        return [];
    }

    $pull = function (string $pattern, string &$str): string {
        if (preg_match($pattern, $str, $m)) {
            $str = str_replace($m[0], '', $str);

            return trim($m[1]);
        }

        return '';
    };

    [$type, $rest] = explode(':', $ifAlias, 2);

    $result = [
        'type' => strtolower(trim($type)),
        'circuit' => $pull('/\{([^}]*)\}/', $rest),
        'speed' => $pull('/\[([^\]]*)\]/', $rest),
        'notes' => $pull('/\(([^)]*)\)/', $rest),
        'descr' => trim($rest),
    ];

    return $result['type'] && $result['descr'] ? $result : [];
};
